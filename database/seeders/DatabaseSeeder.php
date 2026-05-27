<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin SuperMarket',
            'username' => 'admin',
            'email' => 'admin@nmsupermarket.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customerId = DB::table('users')->insertGetId([
            'name' => 'Khách Hàng',
            'username' => 'customer',
            'email' => 'customer@nmsupermarket.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('carts')->insert([
            'user_id' => $customerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Categories (10 items)
        $categories = [
            ['name' => 'Thịt, Cá', 'slug' => 'thit-ca', 'icon' => '🥩', 'image' => 'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Rau Củ', 'slug' => 'rau-cu', 'icon' => '🥬', 'image' => 'https://images.unsplash.com/photo-1518843875459-f738682238a6?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Sữa, Trứng', 'slug' => 'sua-trung', 'icon' => '🥛', 'image' => 'https://images.unsplash.com/photo-1628088062854-d1870b4553da?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Đồ Uống', 'slug' => 'do-uong', 'icon' => '🥤', 'image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Ăn Vặt', 'slug' => 'an-vat', 'icon' => '🍟', 'image' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Gia Vị', 'slug' => 'gia-vi', 'icon' => '🧂', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Khô, Mì', 'slug' => 'kho-mi', 'icon' => '🍜', 'image' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Hải Sản', 'slug' => 'hai-san', 'icon' => '🦐', 'image' => 'https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Trái Cây', 'slug' => 'trai-cay', 'icon' => '🍎', 'image' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&q=80&w=400'],
            ['name' => 'Đồ Sinh Hoạt', 'slug' => 'do-sinh-hoat', 'icon' => '🧻', 'image' => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?auto=format&fit=crop&q=80&w=400'],
        ];

        $categoryIds = [];
        foreach ($categories as $index => $cat) {
            $categoryIds[] = DB::table('categories')->insertGetId([
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'icon' => $cat['icon'],
                'image' => $cat['image'],
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Dữ liệu sản phẩm thuần Việt theo Category
        $vietnameseProducts = [
            'Thịt, Cá' => [
                ['name' => 'Thịt heo ba rọi', 'unit' => 'kg', 'price' => 120000],
                ['name' => 'Thịt bò bít tết', 'unit' => 'kg', 'price' => 250000],
                ['name' => 'Cá hồi Na Uy phi lê', 'unit' => 'kg', 'price' => 450000],
                ['name' => 'Gà ta thả vườn nguyên con', 'unit' => 'kg', 'price' => 150000],
            ],
            'Rau Củ' => [
                ['name' => 'Rau muống VietGAP', 'unit' => 'kg', 'price' => 25000],
                ['name' => 'Cà chua Đà Lạt', 'unit' => 'kg', 'price' => 45000],
                ['name' => 'Dưa hấu Long An', 'unit' => 'kg', 'price' => 15000],
                ['name' => 'Bắp cải trắng', 'unit' => 'kg', 'price' => 35000],
            ],
            'Sữa, Trứng' => [
                ['name' => 'Lốc 4 Sữa tươi Vinamilk 100% không đường 180ml', 'unit' => 'lốc', 'price' => 32000],
                ['name' => 'Thùng 48 Hộp Sữa tươi TH True Milk có đường 180ml', 'unit' => 'thùng', 'price' => 370000],
                ['name' => 'Lốc 4 Sữa chua Vinamilk nha đam', 'unit' => 'lốc', 'price' => 28000],
                ['name' => 'Trứng gà Ba Huân 10 quả', 'unit' => 'hộp', 'price' => 35000],
            ],
            'Đồ Uống' => [
                ['name' => 'Lốc 6 lon Bia Tiger Bạc 330ml', 'unit' => 'lốc', 'price' => 110000],
                ['name' => 'Thùng 24 lon Nước ngọt Coca-Cola 320ml', 'unit' => 'thùng', 'price' => 195000],
                ['name' => 'Chai Nước khoáng Lavie 1.5L', 'unit' => 'chai', 'price' => 12000],
            ],
            'Ăn Vặt' => [
                ['name' => 'Bánh quy bơ Danisa 454g', 'unit' => 'hộp', 'price' => 135000],
                ['name' => 'Snack khoai tây Oishi vị tảo biển 68g', 'unit' => 'gói', 'price' => 15000],
                ['name' => 'Bánh ChocoPie hộp 12 cái', 'unit' => 'hộp', 'price' => 55000],
            ],
            'Gia Vị' => [
                ['name' => 'Nước mắm Nam Ngư 750ml', 'unit' => 'chai', 'price' => 42000],
                ['name' => 'Dầu ăn Simply 1L', 'unit' => 'chai', 'price' => 68000],
                ['name' => 'Hạt nêm Knorr thịt thăn xương ống 400g', 'unit' => 'gói', 'price' => 38000],
            ],
            'Khô, Mì' => [
                ['name' => 'Thùng 30 Gói Mì Hảo Hảo tôm chua cay', 'unit' => 'thùng', 'price' => 115000],
                ['name' => 'Gạo thơm ST25 Ông Cua 5kg', 'unit' => 'túi', 'price' => 180000],
                ['name' => 'Bún khô Vifon 500g', 'unit' => 'gói', 'price' => 25000],
            ],
            'Hải Sản' => [
                ['name' => 'Tôm sú biển tươi', 'unit' => 'kg', 'price' => 350000],
                ['name' => 'Mực lá tươi', 'unit' => 'kg', 'price' => 280000],
                ['name' => 'Cua Cà Mau', 'unit' => 'kg', 'price' => 450000],
            ],
            'Trái Cây' => [
                ['name' => 'Táo đỏ Mỹ', 'unit' => 'kg', 'price' => 90000],
                ['name' => 'Cam sành loại 1', 'unit' => 'kg', 'price' => 35000],
                ['name' => 'Nho xanh không hạt Úc', 'unit' => 'kg', 'price' => 180000],
            ],
            'Đồ Sinh Hoạt' => [
                ['name' => 'Dầu gội Clear men bạc hà 630g', 'unit' => 'chai', 'price' => 165000],
                ['name' => 'Sữa tắm Lifebuoy bảo vệ vượt trội 800g', 'unit' => 'chai', 'price' => 155000],
                ['name' => 'Nước giặt OMO Matic cửa trước 3.6kg', 'unit' => 'túi', 'price' => 195000],
            ],
        ];

        $products = [];
        
        foreach ($categoryIds as $index => $catId) {
            $catName = $categories[$index]['name'];
            $items = $vietnameseProducts[$catName] ?? [];
            
            foreach ($items as $item) {
                $name = $item['name'];
                $price = $item['price'];
                $sale_price = rand(0, 1) ? $price * (rand(80, 95) / 100) : null;
                
                $productId = DB::table('products')->insertGetId([
                    'category_id' => $catId,
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . uniqid(),
                    'sku' => 'NMS-' . strtoupper(Str::random(6)),
                    'description' => 'Sản phẩm ' . $name . ' chất lượng cao, an toàn vệ sinh thực phẩm.',
                    'price' => $price,
                    'sale_price' => $sale_price,
                    'unit' => $item['unit'],
                    'brand' => 'Đang cập nhật',
                    'origin' => 'Việt Nam',
                    'weight' => rand(100, 5000),
                    'is_active' => true,
                    'is_featured' => rand(0, 1) == 1,
                    'sold_count' => rand(0, 500),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $products[] = $productId;
                
                // Add Inventory
                DB::table('inventory')->insert([
                    'product_id' => $productId,
                    'quantity' => rand(50, 500),
                    'reserved' => rand(0, 20),
                    'low_stock_alert' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // Orders (10 dummy orders for the customer)
        $faker = \Faker\Factory::create('vi_VN');
        for ($i = 1; $i <= 10; $i++) {
            $total = rand(100, 2000) * 1000;
            $status = $faker->randomElement(['pending', 'confirmed', 'shipping', 'delivered', 'cancelled']);
            $orderId = DB::table('orders')->insertGetId([
                'order_code' => 'NMS-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'user_id' => $customerId,
                'status' => $status,
                'subtotal' => $total,
                'shipping_fee' => 30000,
                'discount' => 0,
                'total' => $total + 30000,
                'payment_method' => 'cod',
                'payment_status' => $status == 'delivered' ? 'paid' : 'unpaid',
                'shipping_name' => $faker->name(),
                'shipping_phone' => $faker->phoneNumber(),
                'shipping_address' => $faker->streetAddress(),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
            
            // Order items
            $itemCount = rand(1, 5);
            for ($j = 0; $j < $itemCount; $j++) {
                $pid = $faker->randomElement($products);
                $qty = rand(1, 5);
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $pid,
                    'product_name' => 'Sản phẩm demo ' . $pid,
                    'product_sku' => 'SKU-' . $pid,
                    'quantity' => $qty,
                    'unit_price' => 50000,
                    'total_price' => 50000 * $qty,
                ]);
            }
        }
    }
}
