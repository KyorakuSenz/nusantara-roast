<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin & Customer
        User::create([
            'name' => 'Admin Roastery',
            'email' => 'admin@nusantararoast.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@nusantararoast.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // 2. Data 6 Kopi Spesialti Nusantara
        $coffees = [
            [
                'name' => 'Aceh Gayo Wine Natural',
                'origin' => 'Aceh Gayo',
                'process' => 'Natural Anaerobic',
                'roast_level' => 'Medium',
                'tasting_notes' => 'Fermented Berry, Dark Cacao, Caramel',
                'description' => 'Biji arabika dataran tinggi Takengon dengan fermentasi anaerobik yang menghasilkan aroma buah beri intens dan body tebal.',
                'image_url' => 'https://images.unsplash.com/photo-1559525839-b184a4d698c7?auto=format&fit=crop&w=800&q=80',
                'base_price' => 95000,
            ],
            [
                'name' => 'Flores Bajawa Honey',
                'origin' => 'Flores Bajawa',
                'process' => 'Yellow Honey',
                'roast_level' => 'Light',
                'tasting_notes' => 'Sweet Citrus, Wild Honey, Floral',
                'description' => 'Kopi arabika vulkanik dari Ngada, Flores dengan karakter keasaman jeruk segar yang seimbang dengan manis madu alami.',
                'image_url' => 'https://images.unsplash.com/photo-1587734195503-904fca47e0e9?auto=format&fit=crop&w=800&q=80',
                'base_price' => 88000,
            ],
            [
                'name' => 'Toraja Sapan Washed',
                'origin' => 'Toraja Sapan',
                'process' => 'Fully Washed',
                'roast_level' => 'Medium',
                'tasting_notes' => 'Brown Sugar, Herbal Spice, Hazelnut',
                'description' => 'Diproses secara giling basah khas Sulawesi dengan profil rasa bersih, sentuhan rempah hangat, dan aftertaste kacang panggang.',
                'image_url' => 'https://images.unsplash.com/photo-1611854779393-1b2da9d400fe?auto=format&fit=crop&w=800&q=80',
                'base_price' => 92000,
            ],
            [
                'name' => 'Java Preanger Malabar',
                'origin' => 'Java Preanger',
                'process' => 'Natural',
                'roast_level' => 'Medium',
                'tasting_notes' => 'Jackfruit, Palm Sugar, Milk Chocolate',
                'description' => 'Arabika legendaris Gunung Malabar, Jawa Barat dengan aroma buah tropis manis dan tekstur lembut.',
                'image_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80',
                'base_price' => 85000,
            ],
            [
                'name' => 'Bali Kintamani Karana',
                'origin' => 'Bali Kintamani',
                'process' => 'Fully Washed',
                'roast_level' => 'Light',
                'tasting_notes' => 'Mandarin Orange, Jasmine, Cane Sugar',
                'description' => 'Tumbuh berdampingan dengan kebun jeruk di lereng Gunung Batur, menghasilkan seduhan filter yang cerah dan aromatik.',
                'image_url' => 'https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&w=800&q=80',
                'base_price' => 86000,
            ],
            [
                'name' => 'Mandheling Classic Espresso',
                'origin' => 'Sumatra Mandheling',
                'process' => 'Semi-Washed',
                'roast_level' => 'Dark',
                'tasting_notes' => 'Dark Chocolate, Roasted Almond, Cedar',
                'description' => 'Profil sangrai gelap dengan tingkat keasaman rendah dan krema tebal, dirancang khusus untuk sajian espresso maupun kopi susu.',
                'image_url' => 'https://images.unsplash.com/photo-1511537190424-bbbab87ac5eb?auto=format&fit=crop&w=800&q=80',
                'base_price' => 82000,
            ],
        ];

        $weights = [
            200 => 1.0,
            500 => 2.3,
            1000 => 4.3,
        ];
        $grinds = ['Whole Bean', 'Fine', 'Medium', 'Coarse'];

        foreach ($coffees as $index => $coffee) {
            $slug = Str::slug($coffee['name']);
            $product = Product::create([
                'name' => $coffee['name'],
                'slug' => $slug,
                'origin' => $coffee['origin'],
                'process' => $coffee['process'],
                'roast_level' => $coffee['roast_level'],
                'tasting_notes' => $coffee['tasting_notes'],
                'description' => $coffee['description'],
                'image_url' => $coffee['image_url'],
                'is_active' => true,
            ]);

            foreach ($weights as $weight => $multiplier) {
                foreach ($grinds as $grind) {
                    $skuPrefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $coffee['name']), 0, 4));
                    $grindCode = strtoupper(substr($grind, 0, 2));

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => "{$skuPrefix}-{$weight}-{$grindCode}-" . ($index + 1),
                        'weight_grams' => $weight,
                        'grind_size' => $grind,
                        'price' => round(($coffee['base_price'] * $multiplier) / 1000) * 1000,
                        'stock' => rand(5, 25),
                    ]);
                }
            }
        }
    }
}