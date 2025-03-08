<?php
// your_php_file.php

// Include the database configuration file
@include 'db/config.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a sale ID is provided in the POST request
if (isset($_POST['search_sale_id'])) {
    $saleId = $_POST['search_sale_id'];
    
    // Prepare the SQL statement
    $saleQuery = "SELECT p.sale_id, p.invoice_date, c.customer_name AS customer_name, p.grand_total, p.paid_amount, p.due_amount, p.payment_type, p.product_name, p.sale_quantity, p.sale_price, p.total_amount 
                  FROM sale p 
                  INNER JOIN customers c ON p.customer_id = c.customer_id 
                  WHERE p.sale_id = ?";
    
    // Prepare and bind
    $stmt = $conn->prepare($saleQuery);
    $stmt->bind_param("i", $saleId);
    
    // Execute the statement
    $stmt->execute();
    $saleResult = $stmt->get_result();

    // Check if the query was successful
    if ($saleResult) {
        if ($saleResult->num_rows > 0) {
            // Output table rows
            while ($sale = $saleResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td data-label='Invoice Date'>" . $sale['invoice_date'] . "</td>";
                echo "<td data-label='Customer Name'>" . $sale['customer_name'] . "</td>";
                echo "<td data-label='Product Name'>" . str_replace(array('[', ']', '"'), '', $sale["product_name"]) . "</td>";
                echo "<td data-label='Received Quantity'>" . str_replace(array('[', ']', '"'), '', $sale["sale_quantity"]) . "</td>";
                echo "<td data-label='Purchasing Price'>" . str_replace(array('[', ']', '"'), '', $sale["sale_price"]) . "</td>";
                echo "<td data-label='Total Amount'>" . str_replace(array('[', ']', '"'), '', $sale["total_amount"]) . "</td>";
                echo "<td data-label='Grand Total'>" . $sale['grand_total'] . "</td>";
                echo "<td data-label='Paid Amount'>" . $sale['paid_amount'] . "</td>";
                echo "<td data-label='Due Amount'>" . $sale['due_amount'] . "</td>";
                echo "<td data-label='Payment Type'>" . $sale['payment_type'] . "</td>";
                echo "<td data-label='Action'>";
                echo "<a href='return_exchange.php?sale_id={$sale['sale_id']}' class='edit-btn'><i class='bx bxs-edit bx-sm'></i></a>";
                echo "<a href='generate_invoice2.php?sale_id={$sale['sale_id']}' onclick='printInvoice(event)'><span class='blue'><i class='bx bx-printer bx-sm'></i></span></a>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='11'>No data found</td></tr>";
        }
    } else {
        echo "<tr><td colspan='11'>Error fetching data: " . $conn->error . "</td></tr>";
    }

    // Free the result set and close the statement
    $saleResult->free_result();
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
