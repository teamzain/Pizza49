<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
include('db/config.php');  

// Get the invoice number
$invoice_number = isset($_GET['invoice_number']) ? $conn->real_escape_string($_GET['invoice_number']) : null;

// Check if invoice number is null or empty
if ($invoice_number === null) {
    die(json_encode(['error' => 'No invoice number provided']));
}

// Fetch sale details
$sql = "SELECT * FROM shopsale3 WHERE invoice_number = '$invoice_number'";
$result = $conn->query($sql);

// Check if query was successful
if ($result === false) {
    die(json_encode(['error' => 'Query error: ' . $conn->error]));
}

// Check if any rows were returned
if ($result->num_rows === 0) {
    die(json_encode(['error' => "No sale found with invoice number: $invoice_number"]));
}

// Fetch the sale details
$sale = $result->fetch_assoc();

// Safely parse product details
$products = [];
if (!empty($sale['product_details'])) {
    $products = json_decode($sale['product_details'], true);
    if ($products === null) {
        $products = [];
        error_log("Failed to parse product details JSON for invoice $invoice_number");
    }
}

// Default values to prevent undefined variable errors
$sale = $sale ?: [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pizza49 Invoice #<?php echo htmlspecialchars($invoice_number); ?></title>
    <style>
        @page {
            margin: 0;
        }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 12px; 
            width: 80mm; 
            margin: 0 auto; 
            padding: 5px;
            line-height: 1.5;
            color: #333;
        }
        .invoice-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
            margin-bottom: 10px;
        }
        .invoice-header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #222;
        }
        .invoice-header p {
            margin: 5px 0;
            font-size: 10px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px;
            font-size: 12px;
        }
        th, td { 
            border-bottom: 1px dotted #000; 
            padding: 5px; 
            text-align: left;
        }
        .section-header {
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-top: 10px;
            padding-bottom: 5px;
            text-transform: uppercase;
            font-size: 12px;
        }
        .totals {
            text-align: right;
            font-weight: bold;
        }
        .invoice-footer {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        @media print {
            body { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>PIZZA49</h1>
        <p>Fresh, Hot, Delicious Pizzas</p>
        <p>Invoice #<?php echo htmlspecialchars($invoice_number ?: 'N/A'); ?></p>
    </div>

    <div class="section-header">Customer Information</div>
    <table>
        <tr>
            <th>Name:</th>
            <td><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?></td>
        </tr>
        <tr>
            <th>Contact:</th>
            <td><?php echo htmlspecialchars($sale['customer_contact_number'] ?? 'N/A'); ?></td>
        </tr>
    </table>

    <div class="section-header">Order Details</div>
    <table>
        <tr>
            <th>Date:</th>
            <td><?php echo htmlspecialchars($sale['date'] ?? date('Y-m-d H:i:s')); ?></td>
        </tr>
        <tr>
            <th>Order Platform:</th>
            <td><?php echo htmlspecialchars($sale['order_app'] ?? 'Direct'); ?></td>
        </tr>
    </table>

    <div class="section-header">Order Items</div>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            if (!empty($products)) {
                foreach ($products as $product): 
                    $productName = $product['productName'] ?? 'Unknown';
                    $quantity = $product['quantity'] ?? 0;
                    $price = $product['price'] ?? 0;
                    $total = $product['productTotal'] ?? ($quantity * $price);
                    $subtotal += $total;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($productName); ?></td>
                <td><?php echo htmlspecialchars($quantity); ?></td>
                <td><?php echo number_format($price, 2); ?></td>
                <td><?php echo number_format($total, 2); ?></td>
            </tr>
            <?php 
                endforeach; 
            } else {
                echo '<tr><td colspan="4">No items</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="section-header">Payment Summary</div>
    <table>
        <tr>
            <th>Subtotal:</th>
            <td class="totals"><?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <tr>
            <th>Total:</th>
            <td class="totals"><?php echo number_format($sale['total_amount'] ?? $subtotal, 2); ?></td>
        </tr>
        <tr>
            <th>Paid:</th>
            <td class="totals"><?php echo number_format($sale['paid_amount'] ?? $subtotal, 2); ?></td>
        </tr>
        <tr>
            <th>Due:</th>
            <td class="totals"><?php echo number_format($sale['due_amount'] ?? 0, 2); ?></td>
        </tr>
    </table>

    <div class="invoice-footer">
        <p>THANK YOU FOR YOUR BUSINESS!</p>
        <p>www.pizza49.com | Support: (123) 456-7890</p>
        <p>GST# 12345 | Refunds within 24 hours</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>