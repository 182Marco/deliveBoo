<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderedMessage;
use App\Order;
use App\Payments\PaymentGateway;
use App\Plate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // validate the cart, charge it server side, save the order and send the confirmation mail
    public function store(Request $r, PaymentGateway $gateway): JsonResponse
    {
        // manual Validator on purpose: Payment.vue expects 200 + {errors}, not a 422
        $validator = Validator::make($r->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_lastName' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:255',
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'plates' => 'required|array|min:1|max:100',
            'plates.*' => 'integer',
            'payment_nonce' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // the amount is never taken from the client: it is the sum of the prices in the db.
        // The cart has one entry per portion, so the same id can appear more than once:
        // it becomes one order line with its quantity.
        $plateIds = array_map('intval', $r->plates);
        $quantities = array_count_values($plateIds);
        $plates = Plate::where('restaurant_id', $r->restaurant_id)
            ->whereIn('id', array_keys($quantities))
            ->get(['id', 'name', 'price', 'availability', 'available_from_month', 'available_to_month'])
            ->keyBy('id');
        if ($plates->count() !== count($quantities)) {
            return response()->json(['errors' => ['plates' => [__('api.order.notOnMenu')]]]);
        }
        $notOrderable = $plates->reject(fn (Plate $p) => $p->isOrderable())->pluck('name');
        if ($notOrderable->isNotEmpty()) {
            return response()->json(['errors' => ['plates' => [__('api.order.notAvailable', ['plates' => $notOrderable->implode(', ')])]]]);
        }
        $amount = round(collect($quantities)->sum(fn ($qty, $id) => $qty * $plates[$id]->price), 2);

        // the order exists before the charge: a failed or interrupted payment leaves a trace
        $order = DB::transaction(function () use ($r, $amount, $quantities, $plates, $gateway) {
            $order = new Order($r->only([
                'customer_name', 'customer_lastName', 'customer_email', 'customer_phone', 'customer_address', 'restaurant_id',
            ]));
            $order->price = $amount;
            $order->status = Order::PENDING;
            $order->payment_driver = $gateway->driver();
            $order->save();
            // one line per plate, with the unit price paid (the history survives later price changes)
            $order->plates()->attach(collect($quantities)->map(fn ($qty, $id) => [
                'quantity' => $qty,
                'unit_price' => $plates[$id]->price,
            ])->all());

            return $order;
        });

        try {
            $result = $gateway->charge($amount, $r->payment_nonce, 'DB-' . $order->id);
        } catch (\Throwable $e) {
            // provider unreachable: the order stays pending for a later reconciliation
            Log::error('Payment gateway error', ['order' => $order->id, 'exception' => $e]);

            return response()->json(['errors' => ['payment' => [__('api.payment.unavailable')]]]);
        }

        if (! $result->successful) {
            $order->forceFill(['status' => Order::FAILED, 'payment_error' => $result->message])->save();

            return response()->json(['errors' => ['payment' => [__('api.payment.declined', ['reason' => $result->message])]]]);
        }

        $order->forceFill(['status' => Order::PAID, 'transaction_id' => $result->transactionId, 'fulfillment_status' => Order::RECEIVED])->save();

        $data = [...$order->only(['id', 'restaurant_id', 'price', 'customer_name', 'customer_lastName', 'customer_email', 'customer_phone', 'customer_address', 'status', 'transaction_id']), 'plates' => $plateIds];

        // the payment is done: a mail failure must not turn it into an error for the customer
        try {
            Mail::to($order->customer_email)->send(new OrderedMessage($data));
        } catch (\Throwable $e) {
            Log::warning('Order mail not sent', ['order' => $order->id, 'exception' => $e]);
        }

        return response()->json($data);
    }
}
