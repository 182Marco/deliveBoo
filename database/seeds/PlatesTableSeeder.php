<?php

use Illuminate\Database\Seeder;
use App\Plate;

class PlatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plates =[
            // Mc Downalds Plates
            [
                "restaurant_id" => "14",
                "name" => "Crispy McBacon",
                "ingredients" => "Gluten (Wheat), Eggs, Milk, Celery, Mustard, Sesame Seeds",
                "description" => "Two 100% beef patties are combined with bacon, a slice of melted cheese and bacon sauce in a fresh bun, toasted for the right amount of crisp!",
                "visible" => true,
                "price" => 4,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "My Selection BBQ",
                "ingredients" => "Gluten (Wheat), Eggs, Milk, Celery, Mustard, Sesame Seeds",
                "description" => "This legendary American burger is a true all-rounder! A 100% beef patty, Emmental cheese, onions, juicy tomatoes and a unique smoky flavoured sauce all nested in a freshly toasted bun. Make it even better - add crispy bacon.",
                "visible" => true,
                "price" => 5,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Big Mac",
                "ingredients" => "Gluten (Wheat), Eggs, Milk, Celery, Mustard, Sesame Seeds, Sulphur Dioxide",
                "description" => "Two 100% beef patties, melted cheese, onions, pickles, lettuce and the signature Big Mac sauce make this an icon.",
                "visible" => true,
                "price" => 6,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Cheeseburger",
                "ingredients" => "Gluten (Wheat), Eggs, Milk, Mustard, Sesame Seeds, Sulphur Dioxide",
                "description" => "A classic combination of a 100% pure beef patty, pickles, onion, mustard and ketchup and of course a slice of melted cheese in a freshly toasted bun make our cheeseburger a classic delight.",
                "visible" => true,
                "price" => 6,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Chicken Mayo",
                "ingredients" => "Gluten (Wheat), Gluten (Rye),Gluten (Barley), Gluten (Spelt), Eggs, Soybeans, Milk, Celery, Mustard, Sesame Seeds, Sulphur Dioxide",
                "description" => "A crispy 100% chicken patty nestled inside a freshly toasted bun with lettuce and a mayo sauce.",
                "visible" => true,
                "price" => 3.8,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Fries",
                "ingredients" => "Gluten (Wheat), Gluten (Rye), Gluten (Barley), Gluten (Oats), Gluten (Spelt), Gluten (Kamut), Crustaceans, Eggs, Fish, Milk, Peanuts, Tree Nuts (Pistachio nuts), Tree Nuts (Walnuts), Tree Nuts (Almonds), Tree Nuts (Hazelnuts), Tree Nuts (Brazil nuts) , Tree Nuts (Cashews), Tree Nuts (Macadamia), Tree Nuts (Queensland nuts), Tree Nuts (Pecan nuts), Celery, Mustard, Sesame Seeds, Soybeans, Sulphur Dioxide, Lupins or Molluscs",
                "description" => "A portion of delicious fries, cooked to golden perfection and seasoned with just the right amount of salt.",
                "visible" => true,
                "price" => 3,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "McFlurry Bounty",
                "ingredients" => "Ice cream is a colloidal emulsion made with water, ice, milk fat, milk protein, sugar and air",
                "description" => "Ice cream is sweet, and is usually flavored with fruit, spices, or candy- the flavor often influences the color (that is, chocolate ice cream is brown, strawberry and cherry flavors are pink, pistachio is green)",
                "visible" => true,
                "price" => 3.4,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Happy Meal",
                "ingredients" => "main item (a hamburger, cheeseburger or small serving of Chicken McNuggets), a side item (French fries, apple slices, a Go-Gurt tube or a salad in some areas) and a drink (milk, juice or a soft drink).",
                "description" => "Doesn’t matters if you want to be Wonder Woman or Batman… Or maybe secretly you love the Joker, come to your nearest McDonald’s and find them inside your Happy Meal!",
                "visible" => true,
                "price" => 3.4,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "4 Piece Chicken McNuggets",
                "ingredients" => "100% chicken meat",
                "description" => "100% chicken meat in a deliciously crispy coating, just waiting to be dipped. A firm favourite with everyone. Chicken McNuggets are available in portions of 4 with the Happy Meal.",
                "visible" => true,
                "price" => 4.2,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "McToast with",
                "ingredients" => "bread cheese ham",
                "description" => "A freshly toasted bun served with a slice of melted cheese and two slices of juicy ham",
                "visible" => true,
                "price" => 2.5,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Fanta",
                "ingredients" => "orange, food coloring, water, phosphoric acid",
                "description" => "A tangy orange soda to complement all of our meals!",
                "visible" => true,
                "price" => 2,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Coca-Cola Zero",
                "ingredients" => "phosphoric acid",
                "description" => "Coca-Cola Zero",
                "visible" => true,
                "price" => 2,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Scrambled Eggs",
                "ingredients" => "eggs, bread",
                "description" => "Looking to start your day with a smile? Try our scrambled eggs, also available with a side of mouth-watering crispy bacon. This breakfast option is also served as a combo with a choice of two drinks or a drink and a hash brown.",
                "visible" => true,
                "price" => 4.7,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Chilli Cheese Nuggets",
                "ingredients" => "Chilli, Cheese,spices",
                "description" => "The ultimate side snack! Cheesy, chilli, goodness that melt in your mouth.",
                "visible" => true,
                "price" => 3.5,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Fruit Bag (Apple)",
                "ingredients" => "Apples",
                "description" => "Enjoy the healthy alternative with the Happy Meal! Our apples are fresh, sweet and incredibly crunchy keeping both you and your child happy thanks to their delicate flavour and high vitamin boosts!",
                "visible" => true,
                "price" => 2.2,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Vanilla Milkshake",
                "ingredients" => "Vanilla, milk",
                "description" => "A deliciously rich and creamy vanilla dairy milkshake that will leave you longing for more! Milkshake comes in regular size (25cl) and large size (40cl).",
                "visible" => true,
                "price" => 1.8,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Vanilla Cone",
                "ingredients" => "sugar, cream, vanilla",
                "description" => "Our signature creamy vanilla ice cream swirled to perfection into a crispy cone.",
                "visible" => true,
                "price" => 1.8,
                "img" => "",
            ],
            [
                "restaurant_id" => "14",
                "name" => "Hash Brown",
                "ingredients" => "meat, olive oil",
                "description" => "Our potato hash browns are crispy and toasty on the outside while soft and fluffy inside. These crispy delights are a perfect addition to start off your day with a smile.",
                "visible" => true,
                "price" => 1.8,
                "img" => "",
            ],
            // Mc Burger King Plates
            [
                "restaurant_id" => "13",
                "name" => "CRAZY CHEESE BBQ",
                "ingredients" => "2 slices of cheddar, 4 slices of bacon, crunchy onion, Cheddar sauce and a yummy Bull's Eye BBQ sauce. Crazy Cheese BBQ is here, only for true cheese lovers.",
                "description" => "A classic combination of a 100% pure beef patty, pickles, onion, mustard and ketchup and of course a slice of melted cheese in a freshly toasted bun make our cheeseburger a classic delight.",
                "visible" => true,
                "price" => 4.6,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "CRAZY CHEESE BBQ",
                "ingredients" => "bread, grilled meat, cheddar, bacon, BBQ sauce",
                "description" => "Two soft slices of corn bread contain 3 layers of grilled meat accompanied by American cheddar, 8 slices of bacon and the unmistakable smoky taste of the BBQ sauce. Great hunger? Epic sandwich.",
                "visible" => true,
                "price" => 4.9,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "BIG KING XXL",
                "ingredients" => "bread, cheese, onion, grilled meat, cheddar, bacon, BBQ sauce",
                "description" => "Extra-Extra-Large! It is not just a maxi size, but a real challenge! With its double serving of juicy grilled meat, unique taste of the special sauce and not one, not two, but four slices of tasty Cheddar cheese the BIG KING® XXL wants to be tamed. Simply irresistible.",
                "visible" => true,
                "price" => 5.3,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "TRIPLE WHOPPER",
                "ingredients" => " lettuce, tomato, bread, cheese, onion, grilled meat, bacon, BBQ sauce",
                "description" => "We know it's hard to get tired of our WHOPPER®, but for those who want more, we created the WHOPPER® Triple. Tomato, onion, cucumber and lettuce with our most famous burger, multiplied by three. A real treat with every bite!",
                "visible" => true,
                "price" => 4.7,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "DOUBLE CHEESE BACON",
                "ingredients" => "cucumber, bread, cheese, onion, grilled meat, bacon, mustard",
                "description" => "For the nonconformists by nature, this burger has it all. Double burger, cheddar cheese and bacon. What more can you ask for?",
                "visible" => true,
                "price" => 3.8,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "MIXED SALAD",
                "ingredients" => "corn, tomato, salad,",
                "description" => "Only 40 calories. This salad is a delight for everyone. Don't give up on taste and enjoy a blend of ingredients that explode in your mouth like fireworks.",
                "visible" => true,
                "price" => 2.1,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "KING FUSION PERUGINA",
                "ingredients" => "ice cream, chocolate, vanilla",
                "description" => "KING FUSION® Perugina®. Dolci praline al cioccolato nel nostro delizioso gelato con aroma di vaniglia.",
                "visible" => true,
                "price" => 4.3,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "ONION RINGS",
                "ingredients" => "onion, olive oil",
                "description" => "Here we come full circle for all onion ring fans. Dip them in a sauce and enjoy. Or munch on the golden breading first and then the onion. Exactly how you want!",
                "visible" => true,
                "price" => 4,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "PLANT BASED WHOPPER",
                "ingredients" => "burger grilled, tomato, lettuce, gherkins, ketchup and mayonnaise",
                "description" => "Juicy 100% vegetable burger grilled on our flame, fresh tomato, onion cut at the moment, lettuce, gherkins, ketchup and mayonnaise. Taste? 100% Whopper. Try it, but you won't believe it.",
                "visible" => true,
                "price" => 5,
                "img" => "",
            ],
            [
                "restaurant_id" => "13",
                "name" => "french frise",
                "ingredients" => "potatos, olive oil",
                "description" => "Our KING Chips are worthy of a king: delicious potatoes, golden color and crunchiness are the characteristics that make them unique.",
                "visible" => true,
                "price" => 3.5,
                "img" => "",
            ],
            // the garage plates
            [
                "restaurant_id" => "9",
                "name" => "Margherita Pizza",
                "ingredients" => "Manitoba flour 200 g 00 flour 300 g, Water 300 ml, Extra virgin olive oil 35 g, Salt up to 10 g, Fresh brewer's yeast 5 g, Tomato pulp 500 g, Fiordilatte 400 g, Dried oregano 1 tbsp, Salt up to 1 tsp, Basil to taste, Extra virgin olive oil to taste",
                "description" => "The Margherita pizza is the typical Neapolitan pizza, topped with tomato, mozzarella (traditionally the fior di latte is used, not that of buffalo), fresh basil, salt and oil; it is, together with pizza marinara, the most popular Italian pizza. ...",
                "visible" => true,
                "price" => 4.5,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "Marinara Pizza",
                "ingredients" => "Manitoba flour 200 g 00 flour 300 g, Water 300 ml, Extra virgin olive oil 35 g, Salt up to 10 g, Fresh brewer's yeast 5 g, Tomato pulp 500 g, Dried oregano 1 tbsp, Salt up to 1 tsp, Basil to taste, Extra virgin olive oil to taste",
                "description" => "also known as pizza alla marinara, is a style of Neapolitan pizza in Italian cuisine seasoned with only tomato sauce, extra virgin olive oil, oregano and garlic. The name has little to do with the marinara sauce, besides sharing a similar origin. It is supposedly the most ancient tomato-topped pizza",
                "visible" => true,
                "price" => 3.8,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "Diavola Pizza",
                "ingredients" => "400 gr of flour Tomato sauce to taste Oregano (optional), 200 ml of water, ½ stick of yeast, Salt, extra virgin olive oil to taste, Spicy Negronetto to taste, 200 gr of mozzarella, Tomato sauce to taste, Oregano (optional)",
                "description" => "In Italian, pizza diavola means “deviled” pizza. And in this case, deviled means one thing: spicy.",
                "visible" => true,
                "price" => 5,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "Tuna and Onion Pizza",
                "ingredients" => "1 (2.6 oz.) Pouch - Yellowfin Tuna in Extra Virgin Olive Oil. 1, 12″ whole wheat pizza crust, unbaked. ⁄4 cup pizza sauce. 10 Pearl onions, peeled. 1 Tbsp. fresh oregano, chopped (or 1 tsp. dried oregano) ⅓ cup mozzarella cheese. ⁄4 cup Parmesan cheese.",
                "description" => "The Tuna and Onion Pizza is a pizza rich in seasoning with a strong flavor, a crunchy pizza dough base stuffed with tomato sauce, onions, tuna and stringy mozzarella. The Pizza with tuna and onions is ideal when you want to enjoy a very tasty single dish excellent to bring to the table with a nice iced beer for a dinner with friends.",
                "visible" => true,
                "price" => 5.5,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "4 seasons pizza",
                "ingredients" => "Artichokes, tomatoes, basil, prosciutto, ham, mushrooms, olives",
                "description" => "Pizza quattro stagioni (four seasons pizza) is a variety of pizza in Italian cuisine that is prepared in four sections with diverse ingredients, with each section representing one season of the year. It is a very popular pizza in Italy, and has been described as a 'classic', 'famous' and 'renowned' Italian pizza",
                "visible" => true,
                "price" => 5.5,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "Roman Braised Beef sandwich(Picchiapò)",
                "ingredients" => "beef, brad, tomato, onion",
                "description" => "this simple, rustic dish of shredded beef braised in tomatoes and onions make a great stew and a fantastic sandwich filling.",
                "visible" => true,
                "price" => 6.7,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "Italian Pecorino, Parsley Sandwich",
                "ingredients" => "Pecorino, Parsley, brad, anchovy",
                "description" => "This crusty sandwich only calls for a handful of ingredients—pecorino, parsley, and anchovy—but has big flavors.",
                "visible" => true,
                "price" => 7.2,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "Softshell Crab Sandwich with Slaw",
                "ingredients" => "Crab, Parsley, Slaw",
                "description" => "A crisp collard slaw and tangy tartar and cocktail sauces top pan-fried softshell crabs in this classic sandwich.",
                "visible" => true,
                "price" => 7.6,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "Turkish Grilled Cheese (Kumru)",
                "ingredients" => "Cheese, brad, Kumru",
                "description" => "This specialty of Çesme, a small beach town in Turkey, is one of the world’s great grilled cheese sandwiches, in which the cheese itself is grilled before getting slipped into toasted bread with raw tomato and optional (but highly recommended) toppings of griddled sausage and pickled cucumbers and chiles",
                "visible" => true,
                "price" => 7.2,
                "img" => "",
            ],
            [
                "restaurant_id" => "9",
                "name" => "Pork Belly Gyro",
                "ingredients" => "Pork meet, brad, tomato, cucumbers",
                "description" => "By starting with a low cooking temperature and then switching to a high one, this pork belly with perfect texture",
                "visible" => true,
                "price" => 6.7,
                "img" => "",
            ],
           
        ];

        foreach($plates as $plate){
            // instance
            $new_plate = new Plate();
            // populate
            $new_plate->restaurant_id = $plate['restaurant_id'];
            $new_plate->name = $plate['name'];
            $new_plate->ingredients = $plate['ingredients'];
            $new_plate->description = $plate['description'];
            $new_plate->visible = $plate['visible'];
            $new_plate->price = $plate['price'];
            $new_plate->img = $plate['img'];
            // save
            $new_plate->save();
        } 
    }
}
