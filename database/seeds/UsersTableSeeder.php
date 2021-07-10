<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users =[
            [
                "name" => "Domenico",
                "last_name" => "Politi",
                "email" => "domenicopoliti@gmail.com",
                "password" => "12345678",
                "vat_number" => "56482488796",
            ],
            [
                "name" => "Italo",
                "last_name" => "Maroni",
                "email" => "italo_maroni@hotmail.it",
                "password" => "48576347",
                "vat_number" => "95798126481",
            ],
            [
                "name" => "Carlo",
                "last_name" => "Moretti",
                "email" => "carlo.moretti@yahoo.com",
                "password" => "11893462",
                "vat_number" => "67926486281",
            ],
            [
                "name" => "Jhonatan",
                "last_name" => "Smith",
                "email" => "jhonatansmith@fastweb.it",
                "password" => "aynj2345",
                "vat_number" => "09567382639",
            ],
            [
                "name" => "Sebastian",
                "last_name" => "Palmioli",
                "email" => "SebastianPalmioli@gmail.com",
                "password" => "l3o6n7m5",
                "vat_number" => "67825067824",
            ],
            [
                "name" => "Thomas",
                "last_name" => "Ercolani",
                "email" => "thomas_ercolani@fastweb.com",
                "password" => "r46h8u9i",
                "vat_number" => "95673519367",
            ],
            [
                "name" => "Jenny",
                "last_name" => "Pozzato",
                "email" => "Jenny.Pozzato@gmail.com",
                "password" => "8dk39e0r",
                "vat_number" => "87690213656",
            ],
            [
                "name" => "Laura",
                "last_name" => "Loggia",
                "email" => "LauraL@yahoo.it",
                "password" => "23cv70bb",
                "vat_number" => "12785600091",
            ],
            [
                "name" => "Federico",
                "last_name" => "Romiti",
                "email" => "federico_romiti@gmail.com",
                "password" => "vth3458w",
                "vat_number" => "56712099077",
            ],
            [
                "name" => "Cristina",
                "last_name" => "Bossetti",
                "email" => "CristinaBos@hotmail.it",
                "password" => "8ty7dn2o9",
                "vat_number" => "26738911127",
            ],
            [
                "name" => "Iavana",
                "last_name" => "Stefanini",
                "email" => "Ivana_Stefanini@gmail.com",
                "password" => "3728usjk",
                "vat_number" => "67891209105",
            ],
            [
                "name" => "Alan",
                "last_name" => "Andreani",
                "email" => "alan.andre@gmail.com",
                "password" => "6yjw9dn3",
                "vat_number" => "66789244561",
            ],
        ];

        foreach($users as $user){
            // instance
            $new_user = new User();
            // populate
            $new_user->name = $user['name'];
            $new_user->last_name = $user['last_name'];
            $new_user->email = $user['email'];
            $new_user->password = $user['password'];
            $new_user->vat_number = $user['vat_number'];
            // save
            $new_user->save();
    }
}
}
