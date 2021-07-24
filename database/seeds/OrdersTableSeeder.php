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
                "customer_email" => "flavio.corradini@libero.com",
                "customer_name" => "Flavio",
                "customer_lastName" => "Corradini",
                "customer_address" => "via Visconti, 7",
                "customer_phone" => "333824572",
                "price" => 23.45,        
                
            ],
            [
                "restaurant_id" => "14",
                "customer_email" => "edoardo.anselmi@libero.com",
                "customer_name" => "Edoardo",
                "customer_lastName" => "Anselmi",
                "customer_address" => "via Turati, 25",
                "customer_phone" => "333824572",
                "price" => 37.22,          
                
            ],
            [
                "restaurant_id" => "14",
                "customer_email" => "giulio.bonomi@libero.com",
                "customer_name" => "Giulio",
                "customer_lastName" => "Bonomi",
                "customer_address" => "via Vespri Siciliani, 12",
                "customer_phone" => "333824572",
                "price" => 24.75,                    
            ],
            [
                "restaurant_id" => "14",
                "customer_email" => "carlo.Schicchi@yhaoo.com",
                "customer_name" => "Carlo",
                "customer_lastName" => "Schicchi",
                "customer_address" => "via Pastrengo, 45",
                "customer_phone" => "3478248972",
                "price" => 9.7,                    
            ],
            [
                "restaurant_id" => "14",
                "customer_email" => "marino.petacci@gmail.com",
                "customer_name" => "Marino",
                "customer_lastName" => "Petacci",
                "customer_address" => "via goito, 22",
                "customer_phone" => "32982498772",
                "price" => 22.3,                    
            ],
            [
                "restaurant_id" => "14",
                "customer_email" => "luca.fracci@gmail.com",
                "customer_name" => "Luca",
                "customer_lastName" => "Fracci",
                "customer_address" => "via Matteotti, 55",
                "customer_phone" => "32982498772",
                "price" => 41,                    
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
                "price" => 28,         
                
            ], 
            [
                "restaurant_id" => "14",
                "customer_email" => "giulia__malta@fastweb.com",
                "customer_name" => "Giulia",
                "customer_lastName" => "Maltagliati",
                "customer_address" => "via Lunga, 4",
                "customer_phone" => "329997202",
                "price" => 37.8,         
                
            ], 
            [
                "restaurant_id" => "14",
                "customer_email" => "sabrinaVigano@gmail.com",
                "customer_name" => "Sabrina",
                "customer_lastName" => "Vigano",
                "customer_address" => "via Pascoli, 13",
                "customer_phone" => "3998971892",
                "price" => 34.75,                    
            ], 
            [
                "restaurant_id" => "14",
                "customer_email" => "marta_linzari@gmail.com",
                "customer_name" => "Marta",
                "customer_lastName" => "Linzari",
                "customer_address" => "via Risorgimento, 36",
                "customer_phone" => "33389790392",
                "price" => 15.7,                    
            ], 
            [
                "restaurant_id" => "14",
                "customer_email" => "pinodonati@gmail.com",
                "customer_name" => "Pino",
                "customer_lastName" => "Donati",
                "customer_address" => "via Mazzini, 11",
                "customer_phone" => "32901798792",
                "price" => 15.7,                    
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

           $cristina_Dam = Order::find(1);
           $cristina_Dam ->plates()->sync([2,5,6,8]); 

           $flavio_corra = Order::find(2);
           $flavio_corra ->plates()->sync([1,3,16,18]); 

           $edo_anselmi = Order::find(3);
           $edo_anselmi ->plates()->sync([10,14,15]);

           $giulio_bonomi = Order::find(4);
           $giulio_bonomi ->plates()->sync([5,6,12]);

           $carlo_Schicchi= Order::find(5);
           $carlo_Schicchi->plates()->sync([3,4,15,18]);

           $marino_petacci= Order::find(6);
           $marino_petacci->plates()->sync([9,12,15,17]);

           $luca_fracci= Order::find(7);
           $luca_fracci->plates()->sync([6,9,13,16]);

           $matteo_mene= Order::find(8);
           $matteo_mene->plates()->sync([10,13,15]);

           $sarah_pro= Order::find(9);
           $sarah_pro->plates()->sync([1,4,9]);

           $marco_mi= Order::find(10);
           $marco_mi->plates()->sync([5,6,10,15]);

           $giulia_maltagliati= Order::find(11);
           $giulia_maltagliati->plates()->sync([3,7,13,14,16,18]);
     
           $sabrina_vigano= Order::find(12);
           $sabrina_vigano->plates()->sync([5,9,10,12]);

           $marta_linzari= Order::find(13);
           $marta_linzari->plates()->sync([4,10,13,18]);

           $pino_donati= Order::find(14);
           $pino_donati->plates()->sync([1,6,10]);
        }
}
