<?php
@include 'db/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['productName'];
    $quantity = (float)$_POST['quantity']; // Ensure quantity is treated as a float

    // Fetch current stock and sold quantity
    $query = "SELECT total_quantity, sold_quantity FROM stock WHERE name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $currentStock = (float)$row['total_quantity'];
        $currentSold = (float)$row['sold_quantity'];

        // Update stock and sold quantity
        $newStock = $currentStock - $quantity;
        $newSold = $currentSold + $quantity;

        // Update stock
        $updateQuery = "UPDATE stock SET total_quantity = ?, sold_quantity = ? WHERE name = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("dds", $newStock, $newSold, $productName);
        
        if ($updateStmt->execute()) {
            echo "Stock and sold quantity updated successfully.";
        } else {
            echo "Failed to update stock and sold quantity: " . $conn->error;
        }

        // Close the update statement
        $updateStmt->close();
    } else {
        echo "Product not found.";
    }

    // Close the fetch statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>