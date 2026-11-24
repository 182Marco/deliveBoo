<?php

namespace App\Http\Controllers\Api;

use App\Carts\CartException;
use App\Carts\CartService;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureCartToken;
use App\Plate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// The cart of the site (dev/PIANO_CARRELLO.md): of the logged-in account, otherwise of the guest cookie.
// Every answer carries the whole cart ({ cart, notices }), errors included, so the site can always
// realign. Unlike POST /api/orders this API uses the real HTTP statuses (404, 409, 422).
class CartController extends Controller
{
    public function __construct(private CartService $carts)
    {
    }

    // GET /api/cart: never creates a cart; the notices are shown once
    public function show(Request $request): JsonResponse
    {
        $cart = $this->carts->find($request->user(), $this->token($request));
        $notices = $this->carts->pullNotices($cart);

        return $this->answer($request, $cart, notices: $notices);
    }

    // PUT /api/cart/items/{plate} { quantity, replace? }: absolute quantity, 0 removes (add and update)
    public function update(Request $request, int $plate): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:' . config('cart.max_quantity')],
            'replace' => ['sometimes', 'boolean'],
        ]);
        $plate = Plate::withTrashed()->findOrFail($plate);
        $quantity = (int) $data['quantity'];

        if ($quantity === 0) {
            return $this->remove($request, $plate);
        }

        $cart = $this->carts->findOrCreate($request->user(), $this->token($request));
        try {
            $cart = $this->carts->setQuantity($cart, $plate, $quantity, (bool) ($data['replace'] ?? false));
        } catch (CartException $e) {
            return $this->answer($request, $cart->fresh(), $e);
        }

        return $this->answer($request, $cart);
    }

    // DELETE /api/cart/items/{plate}: idempotent, also for a plate deleted by the restaurant
    public function destroy(Request $request, int $plate): JsonResponse
    {
        return $this->remove($request, Plate::withTrashed()->findOrFail($plate));
    }

    // DELETE /api/cart: idempotent
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->carts->find($request->user(), $this->token($request));

        return $this->answer($request, $cart ? $this->carts->clear($cart) : null);
    }

    // POST /api/cart/import { items: [{ plate_id, quantity }] }: the old localStorage cart, only into an empty cart
    public function import(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.plate_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:' . config('cart.max_portions')],
        ]);
        $cart = $this->carts->findOrCreate($request->user(), $this->token($request));

        return $this->answer($request, $this->carts->import($cart, $data['items']));
    }

    private function remove(Request $request, Plate $plate): JsonResponse
    {
        $cart = $this->carts->find($request->user(), $this->token($request));

        return $this->answer($request, $cart ? $this->carts->setQuantity($cart, $plate, 0) : null);
    }

    private function token(Request $request): string
    {
        return $request->attributes->get(EnsureCartToken::ATTRIBUTE);
    }

    private function answer(Request $request, $cart, ?CartException $error = null, array $notices = []): JsonResponse
    {
        $body = ['cart' => $this->carts->snapshot($cart, $request->user() !== null), 'notices' => $notices];
        if ($error !== null) {
            $body = ['code' => $error->errorCode, 'message' => $error->getMessage(), ...$body];
        }

        return response()->json($body, $error?->status ?? 200);
    }
}
