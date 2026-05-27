<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Lấy danh sách danh mục (Public/Admin)
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Admin: Thêm danh mục
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:120|unique:categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'integer'
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Tạo danh mục thành công'
        ], 201);
    }

    // Admin: Sửa danh mục
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:100',
            'slug' => 'string|max:120|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'integer',
            'is_active' => 'boolean'
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Cập nhật thành công'
        ]);
    }
}
