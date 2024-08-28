<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        $orders = Order::with(['items.product','customer','lastAddedItem'])
        ->withCount('items as items_count')
        ->latest('completed_at')
        ->paginate();

        $orderData = [];

        foreach ($orders->items() as $order) {
            $orderData[] = $this->mapOrderData($order);
        }

        return view('orders.index', ['orders' => $orderData]);
    }

    // we can define this method in an OrderRepository class
    private function mapOrderData(Order $order): array
    {
        $items = $order->items;
        $totalAmount = collect($items)->sum(fn ($item) => $item->price * $item->quantity);
        $lastAddedToCart = $order->lastAddedItem?->created_at;

        //instead of hard coded 'completed' we can define an Enum  OrderStatusEnum
        $completedOrderExists = $order->status == 'completed';

        return [
            'order_id' => $order->id,
            'customer_name' => $order->customer->name,
            'total_amount' => $totalAmount,
            'items_count' => $order->items_count,
            'last_added_to_cart' => $lastAddedToCart,
            'completed_order_exists' => $completedOrderExists,
            'created_at' => $order->created_at,
        ];
    }
}
