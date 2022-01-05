<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        /* $this->call([
            UserSeeder::class,
            ProductSeeder::class,
        ]); */

        $categoriesTable = 'categories';
        $productsTable = 'products';
        $data = [
            $categoriesTable => CategoryTable::populate(),
            $productsTable => ProductTable::populate(),
            'users' => UserTable::populate(),
        ];

        foreach($data as $table => $records) {
            foreach($records as $record) {
                DB::table($table)->insert((array)$record);
            }
        }
    }
}


class CategoryTable {
    function __construct($id, $title, $description, $parent_id = null) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->parent_id = $parent_id;
    }

    static function populate() {
        return [
            new CategoryTable(
                1, 
                'لوازم الکتریکی',
                'انواع لوازم الکتریکی با بهترین کیفیت',
                ),
            new CategoryTable(
                2, 
                'مواد غذایی',
                'انواع مواد غذایی تازه و با کیفیت',
            ),
            new CategoryTable(
                3,
                'لپ‌تاپ',
                'انواع لپتاپ‌های گیمینگ و اداری و خانگی', 
                1,
            ),
            new CategoryTable(
                4, 
                'لپ‌تاپ گیمینگ', 
                'بهترین لپتاپ‌های گیمینگ',
                3
            ),
            new CategoryTable(
                5, 
                'لپ‌تاپ اداری', 
                'بهترین لپتاپ‌های اداری',
                3,
            ),
        ];
    }
}

class ProductTable {
    function __construct($id, $title, $description, $quantity, $price, $category_id) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->category_id = $category_id;
    }

    static function populate() {
        return [
            new ProductTable(
                1,
                'لپتاپ ایسوس',
                'لپتاپ های مقاوم و معتبر',
                4,
                32000000,
                3
            ),
            new ProductTable(
                2,
                'لپتاپ سونی vio',
                'لپتاپ‌های مقاوم از شرکت سونی',
                15,
                50000000,
                5
            ),
            new ProductTable(
                3,
                'لپتا legion 5 lenovo',
                'لپتاپ گیمینگ از شرکت لنوو',
                10,
                65000000,
                4,
            ),
            new ProductTable(
                4,
                'لامپ هوشمند شیائومی',
                'لامپ‌های هوشمند شیائومی با دوام بالا و دارای ریموت کنترل',
                50,
                100000,
                1,
            ),
        ];
    }
}



class UserTable {
    public function __construct(
        public $id,
        public $name,
        public $email,
        public $password,
    )
    {
        
    }

    public static function populate() {
        return [
            new UserTable(1, 'jack', 'jack@gmail.com', 'password-1'),
            new UserTable(2, 'john', 'john@gmail.com', 'password-2'),
            new UserTable(3, 'joe', 'joe@gmail.com', 'password-3'),
        ];
    }
}