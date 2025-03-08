<?php


// Include database configuration
@include 'db/config.php';
include 'loader.html';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if sale_id is set
    if (!isset($_POST['sale_id']) || empty($_POST['sale_id'])) {
        die("Error: sale_id is not set or is empty in the POST data.");
    }

    $sale_id = $_POST['sale_id'];
    $vehicleExpense = $_POST['vehicleExpense'] ?? 0;
    $netTotal = $_POST['netTotal'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $grandTotal = $_POST['grandTotal'] ?? 0;
    $paidAmount = $_POST['paidAmount'] ?? 0;
    $dueAmount = $_POST['dueAmount'] ?? 0;
    $paymentType = $_POST['paymentType'] ?? '';
    
    // Decode the product data JSON
    $productData = json_decode($_POST['productData'] ?? '{}', true);

    // Debug: Print decoded product data
    echo "<h2>Decoded Product Data:</h2>";
    echo "<pre>";
    print_r($productData);
    echo "</pre>";

    // Check if product data is valid
    if (!is_array($productData) || empty($productData)) {
        die("Error: Invalid or empty product data. Please make sure the product table is not empty.");
    }

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Update the main sale record
        $updateSaleQuery = "UPDATE sale SET 
            vehicle_expense = ?, 
            total_amount = ?, 
            discount = ?, 
            grand_total = ?, 
            paid_amount = ?, 
            due_amount = ?, 
            payment_type = ?,
            product_name = ?,
            sale_quantity = ?,
            sale_price = ?,
            total_amount = ?
            WHERE sale_id = ?";
        
        $stmt = $conn->prepare($updateSaleQuery);

        $productNames = json_encode($productData['productNames']);
        $quantities = json_encode($productData['quantities']);
        $prices = json_encode($productData['unitPrices']);
        $totalAmounts = json_encode($productData['totalAmounts']);

        $stmt->bind_param("ddddddsssssi", 
            $vehicleExpense, 
            $netTotal, 
            $discount, 
            $grandTotal, 
            $paidAmount, 
            $dueAmount, 
            $paymentType, 
            $productNames, 
            $quantities, 
            $prices, 
            $totalAmounts,
            $sale_id
        );
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        // Display success message in JavaScript
        echo '<script type="text/javascript">';
        echo 'alert("Sale updated successfully!");';
        echo 'window.location.href = "sale.php";'; // Redirect to sale.php after success
        echo '</script>';

    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $conn->rollback();
        echo "Error updating sale: " . $e->getMessage();
    }

    // Close the database connection
    $conn->close();
} else {
    echo "Invalid request method.";
}
