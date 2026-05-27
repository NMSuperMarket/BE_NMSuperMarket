<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Public: Danh sách sản phẩm (có filter)
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', true);
        }

        if ($request->has('flash_sale')) {
            $query->whereNotNull('sale_price');
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // Public: Chi tiết sản phẩm
    public function show($id)
    {
        $product = Product::with(['category', 'images', 'inventory'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    // Admin: Danh sách tất cả sản phẩm
    public function adminIndex()
    {
        $products = Product::with(['category', 'inventory'])->orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // Admin: Thêm sản phẩm
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:200',
            'slug' => 'required|string|unique:products,slug',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:30',
            'thumbnail' => 'nullable|url',
            'is_featured' => 'boolean'
        ]);

        $product = Product::create($validated);
        
        // Auto create inventory record
        $product->inventory()->create([
            'quantity' => $request->input('stock', 0)
        ]);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Sản phẩm đã được tạo'
        ], 201);
    }

    // Admin: Sửa sản phẩm
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:200',
            'slug' => 'string|unique:products,slug,' . $id,
            'sku' => 'string|unique:products,sku,' . $id,
            'price' => 'numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'unit' => 'string|max:30',
            'thumbnail' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $product->update($validated);
        
        if ($request->has('stock')) {
            $product->inventory()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $request->input('stock')]
            );
        }

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Cập nhật thành công'
        ]);
    }

    // Admin: Xóa sản phẩm
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Kiểm tra xem sản phẩm đã có trong đơn hàng nào chưa
        $hasOrders = \Illuminate\Support\Facades\DB::table('order_items')->where('product_id', $id)->exists();
        
        if ($hasOrders) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa sản phẩm đã phát sinh giao dịch. Gợi ý: Hãy chuyển trạng thái sản phẩm sang Ngừng hoạt động (is_active = false).'
            ], 400);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Xóa thủ công các dữ liệu liên kết vì DB thiếu foreign key cascade
            \Illuminate\Support\Facades\DB::table('inventory_logs')->where('product_id', $id)->delete();
            \Illuminate\Support\Facades\DB::table('inventory')->where('product_id', $id)->delete();
            \Illuminate\Support\Facades\DB::table('cart_items')->where('product_id', $id)->delete();
            
            $product->images()->delete(); // images relationship có thể delete
            
            $product->delete();

            \Illuminate\Support\Facades\DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xóa sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
}
