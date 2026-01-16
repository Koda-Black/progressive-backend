<?php
/**
 * Order Input Validator
 */

declare(strict_types=1);

namespace ProgressiveBar\Validators;

class OrderValidator
{
    public function validate(array $data): array
    {
        $errors = [];
        
        // Table number validation
        if (!isset($data['tableNumber']) || empty($data['tableNumber'])) {
            $errors['tableNumber'] = 'Table number is required';
        } elseif (!preg_match('/^T\d{2}$/i', $data['tableNumber'])) {
            $errors['tableNumber'] = 'Invalid table number format';
        }
        
        // Items validation
        if (!isset($data['items']) || !is_array($data['items'])) {
            $errors['items'] = 'Order items are required';
        } elseif (empty($data['items'])) {
            $errors['items'] = 'At least one item is required';
        } else {
            foreach ($data['items'] as $index => $item) {
                $itemErrors = $this->validateItem($item, $index);
                $errors = array_merge($errors, $itemErrors);
            }
        }
        
        // Price validation
        if (!isset($data['subtotal']) || !is_numeric($data['subtotal'])) {
            $errors['subtotal'] = 'Subtotal is required and must be numeric';
        } elseif ($data['subtotal'] < 0) {
            $errors['subtotal'] = 'Subtotal cannot be negative';
        }
        
        if (!isset($data['tax']) || !is_numeric($data['tax'])) {
            $errors['tax'] = 'Tax is required and must be numeric';
        } elseif ($data['tax'] < 0) {
            $errors['tax'] = 'Tax cannot be negative';
        }
        
        if (!isset($data['total']) || !is_numeric($data['total'])) {
            $errors['total'] = 'Total is required and must be numeric';
        } elseif ($data['total'] < 0) {
            $errors['total'] = 'Total cannot be negative';
        }
        
        // Validate total matches subtotal + tax (with small tolerance for rounding)
        if (isset($data['subtotal'], $data['tax'], $data['total'])) {
            $expectedTotal = $data['subtotal'] + $data['tax'];
            if (abs($expectedTotal - $data['total']) > 0.01) {
                $errors['total'] = 'Total does not match subtotal + tax';
            }
        }
        
        // Notes validation (optional)
        if (isset($data['notes']) && strlen($data['notes']) > 500) {
            $errors['notes'] = 'Notes cannot exceed 500 characters';
        }
        
        return $errors;
    }

    private function validateItem(array $item, int $index): array
    {
        $errors = [];
        $prefix = "items[$index]";
        
        if (!isset($item['menuItemId']) || empty($item['menuItemId'])) {
            $errors["$prefix.menuItemId"] = 'Menu item ID is required';
        }
        
        if (!isset($item['name']) || empty($item['name'])) {
            $errors["$prefix.name"] = 'Item name is required';
        }
        
        if (!isset($item['price']) || !is_numeric($item['price'])) {
            $errors["$prefix.price"] = 'Item price is required';
        } elseif ($item['price'] < 0) {
            $errors["$prefix.price"] = 'Item price cannot be negative';
        }
        
        if (!isset($item['quantity']) || !is_numeric($item['quantity'])) {
            $errors["$prefix.quantity"] = 'Item quantity is required';
        } elseif ($item['quantity'] < 1) {
            $errors["$prefix.quantity"] = 'Item quantity must be at least 1';
        } elseif ($item['quantity'] > 50) {
            $errors["$prefix.quantity"] = 'Item quantity cannot exceed 50';
        }
        
        if (isset($item['specialInstructions']) && strlen($item['specialInstructions']) > 200) {
            $errors["$prefix.specialInstructions"] = 'Special instructions cannot exceed 200 characters';
        }
        
        return $errors;
    }
}
