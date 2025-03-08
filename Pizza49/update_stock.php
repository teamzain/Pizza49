<?php
// Include database configuration
@include 'db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['productName'];
    $newQuantity = (float) $_POST['newQuantity']; // Cast to float
    $saleQuantity = (float) $_POST['saleQuantity']; // Cast to float

    // Fetch product details from the database
    $getProductQuery = "SELECT total_quantity, sold_quantity FROM stock WHERE name = ?";
    $stmt = $conn->prepare($getProductQuery);
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $result = $stmt->get_result();
    $productData = $result->fetch_assoc();

    if ($productData) {
        $totalQuantity = (float) $productData['total_quantity']; // Cast to float
        $soldQuantity = (float) $productData['sold_quantity']; // Cast to float

        // Increment or decrement logic based on newQuantity and saleQuantity
        if ($newQuantity > $saleQuantity) {
            $quantityChange = $newQuantity - $saleQuantity;
            $newTotalQuantity = $totalQuantity - $quantityChange;
            $newSoldQuantity = $soldQuantity + $quantityChange;
        } else {
            $quantityChange = $saleQuantity - $newQuantity;
            $newTotalQuantity = $totalQuantity + $quantityChange;
            $newSoldQuantity = $soldQuantity - $quantityChange;
        }

        // Update stock table
        $updateStockQuery = "UPDATE stock SET total_quantity = ?, sold_quantity = ? WHERE name = ?";
        $updateStmt = $conn->prepare($updateStockQuery);
        $updateStmt->bind_param("dds", $newTotalQuantity, $newSoldQuantity, $productName); // Use "d" for floats in bind_param

        if ($updateStmt->execute()) {
            echo "Stock updated successfully.";
        } else {
            echo "Error updating stock.";
        }
    } else {
        echo "Product not found.";
    }
} else {
    echo "Invalid request method.";
}
?>