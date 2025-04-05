<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Auth::user()->orders()
                    ->with(['items.product'])
                    ->latest()
                    ->get();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $cartItems = app(CartController::class)->getUserCart();
        
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }
        
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return view('orders.create', compact('cartItems', 'total'));
    }

    public function store(Request $request)
    {//bago
        $cartItems = app(CartController::class)->getUserCart();
    
    if (empty($cartItems)) {
        return redirect()->route('cart.index')->with('error', 'Your cart is empty');
    }//bago

        // Check if this is coming from cart or single product purchase
        if ($request->has('product_id')) {
            // Single product purchase
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $product = Product::findOrFail($request->product_id);

            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $product->price * $request->quantity,
                'status' => 'pending'
            ]);

            // Add order item
            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price
            ]);

            // Update product stock
            $product->decrement('stock', $request->quantity);

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order placed successfully!');
        } else {
            // Cart checkout
            $cartItems = session()->get('cart', []);
            
            if (empty($cartItems)) {
                return redirect()->route('cart.index')->with('error', 'Your cart is empty');
            }
            
            // Validate stock before creating order
            foreach ($cartItems as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->stock < $item['quantity']) {
                    return redirect()->route('cart.index')->with('error', "{$product->name} doesn't have enough stock");
                }
            }
            
            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => array_reduce($cartItems, function($carry, $item) {
                    return $carry + ($item['price'] * $item['quantity']);
                }, 0),
                'status' => 'pending'
            ]);
            
            // Create order items and update product stock
            foreach ($cartItems as $item) {
                $product = Product::find($item['product_id']);
                
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
                
                // Update product stock
                $product->decrement('stock', $item['quantity']);
            }
            
            // Clear cart
            session()->forget('cart');
            
            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully!');
        }
        $this->clearUserCart();
    }

    public function show(Order $order)
    {
        return view('orders.show', [
            'order' => $order->load(['items.product', 'reviews'])
        ]);
    }
}