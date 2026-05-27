<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Giả lập lấy user_id = 1 cho mục đích test nếu chưa tích hợp auth hoàn chỉnh
    private function getUserId()
    {
        return auth('api')->id();
    }

    public function getCart()
    {
        $userId = $this->getUserId();
        
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        $items = CartItem::with('product')->where('cart_id', $cart->id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'cart_id' => $cart->id,
                'items' => $items,
                'total_items' => $items->sum('quantity')
            ]
        ]);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $userId = $this->getUserId();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        
        $product = Product::with('inventory')->findOrFail($request->product_id);
        $price = $product->sale_price ?? $product->price;

        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $product->id)
                            ->first();

        $currentQuantity = $cartItem ? $cartItem->quantity : 0;
        $newQuantity = $currentQuantity + $request->quantity;

        // Check Inventory
        $stock = $product->inventory ? $product->inventory->quantity : 0;
        if ($newQuantity > $stock) {
            return response()->json([
                'success' => false,
                'message' => "Số lượng vượt quá tồn kho (Còn lại: {$stock})"
            ], 400);
        }

        if ($cartItem) {
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $price
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'data' => $cartItem
        ]);
    }

    public function updateCart(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::with('product.inventory')->findOrFail($id);
        
        // Check Inventory
        $stock = $cartItem->product->inventory ? $cartItem->product->inventory->quantity : 0;
        if ($request->quantity > $stock) {
            return response()->json([
                'success' => false,
                'message' => "Số lượng vượt quá tồn kho (Còn lại: {$stock})"
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật giỏ hàng'
        ]);
    }

    public function removeFromCart($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
        ]);
    }
}
