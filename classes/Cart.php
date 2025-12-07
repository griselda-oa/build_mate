<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Cart model/service
 * Handles cart operations and business logic
 */
class Cart
{
    /**
     * Get cart items with product details
     */
    public static function getItemsWithProducts(array $cart): array
    {
        $productModel = new Product();
        $items = [];
        
        foreach ($cart as $item) {
            $product = $productModel->find($item['product_id']);
            if ($product) {
                $product['qty'] = $item['qty'];
                $items[] = $product;
            }
        }
        
        return $items;
    }
    
    /**
     * Calculate cart total
     */
    public static function calculateTotal(array $cart): int
    {
        $productModel = new Product();
        $total = 0;
        
        foreach ($cart as $item) {
            $product = $productModel->find($item['product_id']);
            if ($product) {
                $total += $product['price_cents'] * $item['qty'];
            }
        }
        
        return $total;
    }
    
    /**
     * Add item to cart
     */
    public static function addItem(array &$cart, int $productId, int $qty = 1): void
    {
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] === $productId) {
                $item['qty'] += $qty;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $cart[] = [
                'product_id' => $productId,
                'qty' => $qty
            ];
        }
    }
    
    /**
     * Update item quantity
     */
    public static function updateItem(array &$cart, int $productId, int $qty): void
    {
        foreach ($cart as &$item) {
            if ($item['product_id'] === $productId) {
                $item['qty'] = $qty;
                break;
            }
        }
    }
    
    /**
     * Remove item from cart
     */
    public static function removeItem(array &$cart, int $productId): void
    {
        $cart = array_filter($cart, function($item) use ($productId) {
            return $item['product_id'] !== $productId;
        });
        $cart = array_values($cart); // Re-index
    }
    
    /**
     * Validate cart items (check stock)
     */
    public static function validateStock(array $cart): array
    {
        $productModel = new Product();
        $errors = [];
        
        foreach ($cart as $item) {
            $product = $productModel->find($item['product_id']);
            if (!$product) {
                $errors[] = 'Product not found';
            } elseif ($product['stock'] < $item['qty']) {
                $errors[] = "Insufficient stock for {$product['name']}";
            }
        }
        
        return $errors;
    }
}



