<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleTags = [
            // Electronics
            'phone', 'laptop', 'tablet', 'earphones', 'headphones', 'charger', 'cable', 'power bank', 'watch', 'smartwatch',
            
            // Personal Items
            'wallet', 'purse', 'keys', 'backpack', 'bag', 'handbag', 'sunglasses', 'glasses', 'umbrella', 'jewelry',
            
            // Clothing
            'jacket', 'coat', 'hoodie', 'shirt', 'pants', 'shoes', 'sneakers', 'hat', 'cap', 'scarf',
            
            // Colors
            'black', 'white', 'red', 'blue', 'green', 'yellow', 'brown', 'gray', 'pink', 'purple', 'orange', 'silver', 'gold',
            
            // Materials
            'leather', 'fabric', 'metal', 'plastic', 'wood', 'canvas', 'nylon',
            
            // Common Items
            'book', 'notebook', 'pen', 'water bottle', 'bottle', 'card', 'id card', 'credit card', 'driver license',
            
            // Sports & Recreation
            'bicycle', 'bike', 'helmet', 'ball', 'sports equipment',
            
            // Pet Items
            'pet collar', 'pet leash', 'pet tag',
            
            // Documents
            'document', 'passport', 'certificate', 'diploma',
            
            // Other
            'toy', 'tool', 'accessory', 'gadget', 'device'
        ];

        foreach ($sampleTags as $tagName) {
            Tag::firstOrCreate(
                ['name' => strtolower(trim($tagName))],
                ['usage_count' => 0]
            );
        }
    }
}
