<?php

use Illuminate\Database\Seeder;
use App\Type;

use function PHPSTORM_META\type;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['Mexican','Vegan','Giapponese','Starred Restaurant','Fast Food','Pizzeria','Ethnic','Italian','Spanish'];

        foreach($types as $type){
            $new_type = new Type();
            $new_type->name = $type;
            $new_type->save();
        };
    }
}
