<?php

use Illuminate\Database\Seeder;
use App\Order;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
                // 'delivered','price','address','customer_name','customer_lastName','customer_phone','customer_address'
        $orders =[
            // Mc Downalds Plates
            [
                "restaurant_id" => "14",
                "customer_email" => "cristina.dami@gmail.com",
                "customer_name" => "Cristina",
                "customer_lastName" => "d'Amico",
                "customer_address" => "via Cerretani, 14",
                "customer_phone" => "334567882",
                "price" => 4,
                
            ],
            [
                "restaurant_id" => "14",
                "customer_email" => "matteo.mene@gmail.com",
                "customer_name" => "Matteo",
                "customer_lastName" => "Meneguzzi",
                "customer_address" => "via Rossi, 1",
                "customer_phone" => "332537892",
                "price" => 7,     
            ],
            [
                "restaurant_id" => "14",
                "customer_email" => "sarah.pro@gmail.com",
                "customer_name" => "Sarah",
                "customer_lastName" => "Proietti",
                "customer_address" => "via Gialla, 8",
                "customer_phone" => "332577982",
                "price" => 9,
                
            ],
            [
                "restaurant_id" => "14",
                "customer_email" => "marco.mi@gmail.com",
                "customer_name" => "Marco",
                "customer_lastName" => "Milza",
                "customer_address" => "via Lunga, 4",
                "customer_phone" => "334447892",
                "price" => 8,
                
            ], 
        ];

        foreach($orders as $order){
            // instance                               
            $new_order = new Order();
            // populate
            $new_order->restaurant_id = $order['restaurant_id'];
            $new_order->customer_email = $order['customer_email'];
            $new_order->customer_name = $order['customer_name'];
            $new_order->customer_lastName = $order['customer_lastName'];
            $new_order->customer_address = $order['customer_address'];
            $new_order->customer_phone = $order['customer_phone'];
            $new_order->price = $order['price'];
            
            // save
            $new_order->save();
        } 

        // seeding many to many with orders
        //    seedind the many to many with types

           $Crispy_McBacon = Order::find(1);
           $Crispy_McBacon ->plates()->attach(6); //crispy McBacon

           $My_Selection_BBQ = Order::find(2);
           $My_Selection_BBQ ->plates()->attach(1); //My_Selection_BBQ

           $Big_Mac = Order::find(3);
           $Big_Mac ->plates()->attach(3); //My_Selection_BBQ

           $Cheeseburger = Order::find(4);
           $Cheeseburger ->plates()->attach(4); //My_Selection_BBQ

        //    $Chicken_Mayo = Order::find(5);
        //    $Chicken_Mayo ->plates()->attach(2); //stellato  
    }
}
