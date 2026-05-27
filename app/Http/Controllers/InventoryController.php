<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Get inventory list
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->select('products.id', 'products.name', 'products.sku', 'products.thumbnail', 'products.is_active')
            ->leftJoin('inventory', 'products.id', '=', 'inventory.product_id')
            ->addSelect('inventory.quantity', 'inventory.reserved', 'inventory.low_stock_alert')
            ->orderBy('products.id', 'desc');

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        // Filter low stock
        if ($request->has('low_stock') && $request->low_stock == 'true') {
            $query->whereRaw('inventory.quantity <= inventory.low_stock_alert');
        }

        $perPage = $request->input('per_page', 20);
        $inventory = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $inventory
        ]);
    }

    /**
     * Update inventory quantity
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $id],
                ['quantity' => 0, 'reserved' => 0, 'low_stock_alert' => 10]
            );

            $oldQuantity = $inventory->quantity;
            $newQuantity = $request->quantity;
            $type = $newQuantity >= $oldQuantity ? 'in' : 'out';
            $diff = abs($newQuantity - $oldQuantity);

            $inventory->quantity = $newQuantity;
            $inventory->save();

            // Log if changed
            if ($diff > 0) {
                DB::table('inventory_logs')->insert([
                    'product_id' => $id,
                    'type' => $type,
                    'quantity' => $diff,
                    'note' => $request->note ?? 'Cập nhật kho từ Admin Dashboard',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật kho thành công',
                'data' => $inventory
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
