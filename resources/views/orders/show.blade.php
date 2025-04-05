@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            Order #{{ $order->id }}
        </div>
        <div class="card-body">
            <h5>Order Details</h5>
            <p>Date: {{ $order->created_at->format('M d, Y H:i') }}</p>
            <p>Status: <span class="badge bg-success">{{ $order->status }}</span></p>
            <p>Total: ${{ number_format($order->total_amount, 2) }}</p>

            <h5 class="mt-4">Products</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                        <td>
                            @php
                                $hasReview = $order->reviews ? $order->reviews->where('product_id', $item->product_id)->count() : false;
                            @endphp

                            @if(!$hasReview)
                                <a href="{{ route('reviews.create', ['order' => $order->id, 'product' => $item->product_id]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Leave Review
                                </a>
                            @else
                                @php
                                    $review = $order->reviews->where('product_id', $item->product_id)->first();
                                @endphp
                                @if($review->user_id == Auth::id())
                                    <a href="{{ route('reviews.edit', ['order' => $order->id, 'product' => $item->product_id, 'review' => $review->id]) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        Edit Review
                                    </a>
                                @else
                                    <span class="text-success">Reviewed</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
