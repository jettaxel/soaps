@extends('layouts.app')
<!-- Create Din ng Order -->
@section('content')
<div class="container">
    <!-- Add Back Button Here -->
    <div class="mb-4">
        <a href="{{ route('products.public.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    <div class="row">
        <div class="col-md-6">
            @if($product->images->isNotEmpty())
                <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" class="img-fluid" alt="{{ $product->name }}">
            @endif
        </div>
        <div class="col-md-6">
            <h1>{{ $product->name }}</h1>
            <div class="rating mb-3">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $product->average_rating)
                        <i class="fas fa-star text-warning"></i>
                    @else
                        <i class="far fa-star text-warning"></i>
                    @endif
                @endfor
                <span>({{ $product->reviews->count() }} reviews)</span>
            </div>
            <p>{{ $product->description }}</p>
            <h4>${{ number_format($product->price, 2) }}</h4>
            <p>Stock: {{ $product->stock }}</p>

            @auth
                @if($product->stock > 0)
                    <form action="{{ route('orders.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="input-group mb-3" style="max-width: 200px;">
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="{{ $product->stock }}">
                            <button type="submit" class="btn btn-primary">Buy Now</button>
                        </div>
                    </form>
                @else
                    <button class="btn btn-secondary" disabled>Out of Stock</button>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Login to Purchase</a>
            @endauth
        </div>
    </div>

    <div class="mt-5">
        <h3>Reviews</h3>
        @foreach($product->reviews as $review)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5>{{ $review->user->name }}</h5>
                        <div>
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <i class="fas fa-star text-warning"></i>
                                @else
                                    <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                            
                            @if(Auth::id() == $review->user_id)
                                <a href="{{ route('reviews.edit', ['order' => $review->order_id, 'product' => $product->id, 'review' => $review->id]) }}" 
                                   class="btn btn-sm btn-outline-secondary ms-2">
                                    Edit
                                </a>
                            @endif
                        </div>
                    </div>
                    <p class="text-muted">{{ $review->created_at->format('M d, Y') }}</p>
                    <p>{{ $review->comment }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection