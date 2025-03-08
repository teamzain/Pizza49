<?php
session_start();
include 'loader.html';
@include "db/config.php"; // Include the database connection

if (isset($_POST['submit'])) {
    // Get all the values from the form
    $invoiceDate = $_POST['invoiceDate'] ?? null;
    $supplier = $_POST['vendor'] ?? null;
    $vehicle = $_POST['vehicle'] ?? null;
    $netTotal = floatval($_POST['netTotal'] ?? 0);
    $grandTotal = floatval($_POST['grandTotal'] ?? 0);
    $paidAmount = floatval($_POST['paidAmount'] ?? 0);
    $dueAmount = floatval($_POST['dueAmount'] ?? 0);
    $vehicleExpense = floatval($_POST['vehicleExpense'] ?? 0);
    $paymentType = $_POST['paymentType'] ?? null;

    // Convert arrays to floats
    $productNames = $_POST['productName'] ?? [];
    $receivedQuantities = array_map('floatval', $_POST['quantity'] ?? []);
    $purchasingPrices = array_map('floatval', $_POST['rate'] ?? []);
    $totalAmounts = array_map('floatval', $_POST['totalAmount'] ?? []);

    // Assign the results of json_encode to variables
    $productNamesJson = json_encode($productNames);
    $receivedQuantitiesJson = json_encode($receivedQuantities);
    $purchasingPricesJson = json_encode($purchasingPrices);
    $totalAmountsJson = json_encode($totalAmounts);

    // Check if product arrays are not empty and validate required fields
    if (!empty($productNames) && count($productNames) > 0 && !empty($productNames[0])) {
        try {
            // SQL query using placeholders for the non-JSON fields
            $sql = "INSERT INTO purchase (invoice_date, supplier_id, vehicle_name, net_total, grand_total, paid_amount, due_amount, payment_type, product_name, received_quantity, purchasing_price, total_amount, vehicle_expense) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Prepare and bind the statement for non-JSON values
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssddddsssssd", 
                $invoiceDate, 
                $supplier, 
                $vehicle, 
                $netTotal, 
                $grandTotal, 
                $paidAmount, 
                $dueAmount, 
                $paymentType,
                $productNamesJson,
                $receivedQuantitiesJson,
                $purchasingPricesJson,
                $totalAmountsJson,
                $vehicleExpense
            );

            $stmt->execute();
            echo "Purchase record inserted successfully!";
            
            foreach ($productNames as $index => $productName) {
                $quantity = $receivedQuantities[$index] ?? 0;  // Handle null or missing quantity by setting it to 0
            
                // Fetch current quantity from stock table
                $fetchQuantityQuery = "SELECT total_quantity FROM stock WHERE name = ?";
                $stmtFetchQuantity = $conn->prepare($fetchQuantityQuery);
                $stmtFetchQuantity->bind_param("s", $productName);
                $stmtFetchQuantity->execute();
                $stmtFetchQuantity->bind_result($currentQuantity);
                $stmtFetchQuantity->fetch();
                $stmtFetchQuantity->close();
            
                if ($currentQuantity !== null) {
                    // Product exists, update the total_quantity
                    $newQuantity = $currentQuantity + $quantity;
                    $updateQuantityQuery = "UPDATE stock SET total_quantity = ? WHERE name = ?";
                    $stmtUpdateQuantity = $conn->prepare($updateQuantityQuery);
                    $stmtUpdateQuantity->bind_param("is", $newQuantity, $productName);
                    $stmtUpdateQuantity->execute();
                    $stmtUpdateQuantity->close();
                } else {
                    // Product doesn't exist, check for product ID before inserting
                    $fetchProductIdQuery = "SELECT id FROM products WHERE product_name = ?";
                    $stmtFetchProductId = $conn->prepare($fetchProductIdQuery);
                    $stmtFetchProductId->bind_param("s", $productName);
                    $stmtFetchProductId->execute();
                    $stmtFetchProductId->bind_result($productId);
                    $stmtFetchProductId->fetch();
                    $stmtFetchProductId->close();
            
                    if ($productId !== null) {
                        // Insert new stock record with default sold_quantity = 0
                        $insertQuantityQuery = "INSERT INTO stock (product_id, name, total_quantity, sold_quantity) VALUES (?, ?, ?, 0)";
                        $stmtInsertQuantity = $conn->prepare($insertQuantityQuery);
                        $stmtInsertQuantity->bind_param("isi", $productId, $productName, $quantity);
                        $stmtInsertQuantity->execute();
                        $stmtInsertQuantity->close();
                    } else {
                        echo "Error: Product ID not found for $productName. Cannot insert into stock.";
                    }
                }
            }
            

            header("Location: purchase.php");
            exit();

        } catch (mysqli_sql_exception $e) {
            echo "Error inserting purchase record: " . $e->getMessage();
        }
    } else {
        echo "Error: Required fields are missing.";
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/purchase.css">
</head>
    <title>Dani's Fabric</title>
    <style>
         /* Keep the recent-orders table styles unchanged */
  .recent-orders table {
    width: 100%;
    border-collapse: collapse;
}
.recent-orders th, .recent-orders td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.recent-orders th {
    background-color: #f2f2f2;
}
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="main-content">
<?php include 'topbar.php'; ?>
<h1>Stock </h1>
<br><br>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Stock ID</th>
                <th>Product Name</th>
                <th>Available Stock</th>
                <th>Sold Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Include the configuration file
            @include 'db/config.php';

            // Check if the connection is successful
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch data from the stock table
            $stockQuery = "SELECT s.stock_id, s.total_quantity, s.sold_quantity, p.product_name AS product_name 
                           FROM stock s 
                           INNER JOIN products p ON s.product_id = p.id"; // Assuming there is a `products` table for product names.
            $stockResult = $conn->query($stockQuery);

            // Check if the query was successful
            if ($stockResult) {
                if ($stockResult->num_rows > 0) {
                    // Output table data
                    while ($stock = $stockResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='step-number color-1' data-label='Stock ID'>" . $stock['stock_id'] . "</td>";
                        echo "<td data-label='Product Name'>" . $stock['product_name'] . "</td>";
                        echo "<td data-label='Total Quantity'>" . $stock['total_quantity'] . "</td>";
                        echo "<td data-label='Sold Quantity'>" . $stock['sold_quantity'] . "</td>";
                        echo "<td data-label='Action'>";
              
                        echo "<a href='generate_stock_report.php?stock_id={$stock['stock_id']}' onclick='generateReport(event)'>";
                        echo "<span class='blue'><i class='bx bx-printer bx-sm'></i></span>";
                        echo "</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No data found</td></tr>";
                }

                // Free the result set
                $stockResult->free_result();
            } else {
                echo "<tr><td colspan='5'>Error fetching data: " . $conn->error . "</td></tr>";
            }

            // Close the database connection
            $conn->close();
            ?>
        </tbody>
    </table>
</div>

  

            </main>
            <script src="assets/script.js"></script>
           


</body>
</html>