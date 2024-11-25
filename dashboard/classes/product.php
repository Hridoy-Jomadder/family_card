<?php
class Product{

// Fetch Family Products
public function getFamilyProducts(Request $request)
{
    $user = auth()->user();

    // Check Access Permission
    if (!$user->hasPermission('view_family_products')) {
        return response()->json(['message' => 'Access Denied'], 403);
    }

    // Retrieve Products
    $products = FamilyProduct::where('family_card_number', $user->family_card_number)->get();

    if ($products->isEmpty()) {
        return response()->json(['message' => 'No products found.']);
    }

    return response()->json($products);
}

}
?>