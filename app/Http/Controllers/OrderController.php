<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function getUserId()
    {
        return auth('api')->id();
    }

    // Customer: Tạo đơn hàng từ giỏ
    public function checkout(Request $request)
    {
        $request->validate([
            'shipping_name' => 'required|string|max:100',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'payment_method' => 'required|in:cod,vnpay,momo'
        ]);

        $userId = $this->getUserId();
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart || $cart->items()->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống'], 400);
        }

        $items = $cart->items()->with('product.inventory')->get();
        
        $subtotal = 0;
        foreach ($items as $item) {
            $stock = $item->product->inventory ? $item->product->inventory->quantity : 0;
            if ($item->quantity > $stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Sản phẩm {$item->product->name} chỉ còn {$stock} trong kho."
                ], 400);
            }
            $subtotal += $item->price * $item->quantity;
        }

        $shippingFee = $subtotal > 300000 ? 0 : 30000;
        $total = $subtotal + $shippingFee;

        DB::beginTransaction();
        try {
            $orderCode = 'NMS-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $order = Order::create([
                'order_code' => $orderCode,
                'user_id' => $userId,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'status' => 'pending'
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                    'thumbnail' => $item->product->thumbnail
                ]);

                // Trừ tồn kho
                if ($item->product->inventory) {
                    $item->product->inventory->decrement('quantity', $item->quantity);
                }
            }

            // Xóa giỏ hàng
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'data' => ['order_code' => $order->order_code, 'total' => $total]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi tạo đơn hàng: ' . $e->getMessage()], 500);
        }
    }

    // Customer: Danh sách đơn hàng cá nhân
    public function myOrders()
    {
        $userId = $this->getUserId();
        $orders = Order::where('user_id', $userId)->orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $orders]);
    }

    // Admin: Danh sách tất cả đơn hàng
    public function adminIndex()
    {
        $orders = Order::with('user')->orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $orders]);
    }

    // Admin: Cập nhật trạng thái đơn
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,confirmed,shipping,delivered,cancelled']);
        
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công', 'data' => $order]);
    }
}
