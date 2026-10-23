<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\DB;

class CategoryUIConfigSeeder extends Seeder
{
    /**
     * Seed UI configuration for categories and subcategories
     *
     * This migrates hardcoded JavaScript rules into the database
     * for easier maintenance and scalability.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedCategoryConfigs();
            $this->seedSubcategoryConfigs();
        });

        $this->command->info('✅ Category UI configurations seeded successfully!');
    }

    /**
     * Seed category-level UI configurations
     */
    private function seedCategoryConfigs(): void
    {
        $configs = [
            // Category 1: Vehicles
            1 => [
                'show' => ['price'],
                'hide' => ['services', 'shipment', 'itemCondition', 'buyDirect', 'quantity'],
                'labels' => [
                    'brand' => 'Select Option:'
                ]
            ],

            // Category 3: Jobs
            3 => [
                'show' => ['salary'],
                'hide' => ['price', 'shipment', 'itemCondition', 'shipping', 'buyDirect', 'expectedSalary', 'quantity'],
                'labels' => [
                    'brand' => 'Select Job Type:'
                ]
            ],

            // Category 7: Real Estate
            7 => [
                'show' => ['price'],
                'hide' => ['services', 'shipment', 'itemCondition', 'buyDirect', 'quantity'],
                'labels' => [
                    'brand' => 'Select Option:'
                ]
            ],

            // Category 11: Services
            11 => [
                'show' => ['services', 'price'],
                'hide' => ['shipment', 'itemCondition', 'buyDirect', 'quantity'],
                'labels' => [
                    'brand' => 'Select Type:'
                ]
            ],

            // Category 18: Seeking Work CVs
            18 => [
                'show' => ['expectedSalary'],
                'hide' => ['price', 'salary', 'shipment', 'shipping', 'itemCondition', 'buyDirect', 'quantity'],
                'labels' => [
                    'brand' => 'Select Option:'
                ]
            ]
        ];

        foreach ($configs as $categoryId => $config) {
            Category::where('id', $categoryId)->update([
                'ui_config' => json_encode($config)
            ]);

            $this->command->info("  - Category {$categoryId} configured");
        }
    }

    /**
     * Seed subcategory-level UI configurations
     */
    private function seedSubcategoryConfigs(): void
    {
        $configs = [
            // Subcategory 2: Cars
            2 => [
                'show' => ['divCar', 'divModel'],
                'hide' => ['shipment', 'itemCondition', 'buyDirect'],
                'labels' => [
                    'brand' => 'Brand:'
                ],
                'required' => ['model']
            ],

            // Subcategory 6: Mobile Phones
            6 => [
                'show' => ['divPhone', 'divModel', 'shipment'],
                'hide' => ['itemCondition'],
                'labels' => [
                    'brand' => 'Select Option:'
                ],
                'required' => ['model']
            ],

            // Subcategories 16-19: Animals (Birds, Cats, Dogs, Fishes)
            16 => [
                'show' => ['shipment'],
                'hide' => ['itemCondition']
            ],
            17 => [
                'show' => ['shipment'],
                'hide' => ['itemCondition']
            ],
            18 => [
                'show' => ['shipment'],
                'hide' => ['itemCondition']
            ],
            19 => [
                'show' => ['shipment'],
                'hide' => ['itemCondition']
            ],

            // Subcategory 21: Buses & Minibuses
            21 => [
                'show' => ['divCar', 'divModel'],
                'hide' => ['shipment', 'itemCondition', 'buyDirect'],
                'labels' => [
                    'brand' => 'Brand:'
                ],
                'required' => ['model']
            ],

            // Subcategory 22: Motorcycles & Scooters
            22 => [
                'show' => ['itemCondition'],
                'hide' => ['shipment', 'buyDirect', 'divCar', 'divModel']
            ],

            // Subcategory 23: Trucks & Trailers
            23 => [
                'show' => ['divCar', 'divModel'],
                'hide' => ['shipment', 'itemCondition', 'buyDirect'],
                'labels' => [
                    'brand' => 'Brand:'
                ],
                'required' => ['model']
            ],

            // Subcategory 24: Vehicle Parts & Accessories
            24 => [
                'show' => ['itemCondition', 'shipment', 'buyDirect'],
                'hide' => ['divCar', 'divModel']
            ],

            // Subcategory 25: Watercraft & Boats
            25 => [
                'show' => ['itemCondition'],
                'hide' => ['shipment', 'buyDirect', 'divCar', 'divModel']
            ]
        ];

        foreach ($configs as $subcategoryId => $config) {
            SubCategory::where('id', $subcategoryId)->update([
                'ui_config' => json_encode($config)
            ]);

            $this->command->info("  - Subcategory {$subcategoryId} configured");
        }
    }
}
