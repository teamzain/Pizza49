<?php
@include 'db/config.php';

// Check if the order ID is set in the URL
if (isset($_GET['sale_id'])) {
    $orderId = $_GET['sale_id'];

    // Retrieve order details from the database
    $sql = "SELECT * FROM sale WHERE sale_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Error preparing the statement: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $invoiceData = $result->fetch_assoc();

        $vendorId = $invoiceData['customer_id'];
        $sqlVendor = "SELECT customer_name FROM customers WHERE customer_id = ?";
        $stmtVendor = $conn->prepare($sqlVendor);
        $stmtVendor->bind_param("i", $vendorId);
        $stmtVendor->execute();
        $vendorResult = $stmtVendor->get_result();
        if ($vendorResult->num_rows > 0) {
            $vendorData = $vendorResult->fetch_assoc();
            $vendorName = $vendorData['customer_name'];

            // Decode JSON data
            $productNames = json_decode($invoiceData['product_name']);
            $receivedQuantities = json_decode($invoiceData['sale_quantity']);
            $purchasingPrices = json_decode($invoiceData['sale_price']);
            $totalAmounts = json_decode($invoiceData['total_amount']);

            // HTML content for the invoice
            echo "<!DOCTYPE html>
            <html lang='en'>
            <head>
                <title>Invoice Details</title>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <meta charset='UTF-8'>
                <style>
                    body {
                        font-family: 'Arial', sans-serif;
                        font-size: 12px;
                        width: 48mm;
                        margin: 0 auto;
                        padding: 0;
                        display: flex;
                        justify-content: center;
                    }
                    .container {
                        width: 100%;
                        max-width: 48mm;
                        padding: 10px;
                        box-sizing: border-box;
                    }
                    .header, .footer {
                        text-align: center;
                        margin-bottom: 10px;
                    }
                    .header h3 {
                        margin: 5px 0;
                        font-size: 18px;
                    }
                    .invoice-details, .item-details, .summary {
                        width: 100%;
                        margin-bottom: 10px;
                        text-align: center;
                    }
                    .invoice-details th, .invoice-details td,
                    .item-details th, .item-details td,
                    .summary th, .summary td {
                        text-align: center;
                        padding: 5px 0;
                    }
                    .item-details th, .item-details td {
                        border-bottom: 1px solid #000;
                    }
                    .item-details th {
                        border-top: 1px solid #000;
                    }
                    .summary th, .summary td {
                        padding: 5px 0;
                        font-weight: bold;
                    }
                    .barcode {
                        text-align: center;
                        margin-top: 10px;
                    }
                    @media print {
                        body, .container {
                            width: 100%;
                            height: auto;
                            margin: 0;
                            padding: 0;
                            -webkit-print-color-adjust: exact;
                        }
                        .header h3 {
                            font-size: 16px;
                        }
                        .summary th, .summary td {
                            font-size: 14px;
                        }
                        .item-details th, .item-details td {
                            font-size: 12px;
                        }
                    }
                </style>
            </head>
            <body>
            <div class='container'>
                <div class='header'>
                 <h3>Pizza 49</h3>
                    <p>Sargodha,Pakistan</p>
                    <p>Email: info@pizza49.com</p>
                    <p>Phone: +92 309 5422442</p>
                </div>
                <div class='invoice-details'>
                    <table>
                        <tr>
                            <th>Invoice Number:</th>
                            <td>{$orderId}</td>
                        </tr>
                        <tr>
                            <th>Invoice Date:</th>
                            <td>{$invoiceData['invoice_date']}</td>
                        </tr>
                        <tr>
                            <th>Invoice To:</th>
                            <td>{$vendorName}</td>
                        </tr>
                    </table>
                </div>
                <div class='item-details'>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>";

            for ($i = 0; $i < count($productNames); $i++) {
                echo "<tr>
                        <td>" . ($i + 1) . "</td>
                        <td>{$productNames[$i]}</td>
                        <td>{$receivedQuantities[$i]}</td>
                        <td>{$purchasingPrices[$i]}</td>
                        <td>{$totalAmounts[$i]}</td>
                      </tr>";
            }

            echo "      </tbody>
                    </table>
                </div>
                <div class='summary'>
                    <table>
                        <tr>
                            <th>Grand Total:</th>
                            <td>{$invoiceData['grand_total']}</td>
                        </tr>
                        <tr>
                            <th>Paid Amount:</th>
                            <td>{$invoiceData['paid_amount']}</td>
                        </tr>
                        <tr>
                            <th>Due Amount:</th>
                            <td>{$invoiceData['due_amount']}</td>
                        </tr>
                    </table>
                </div>
                <div class='barcode'>
                    <svg id='barcode'></svg>
                </div>
                <div class='footer'>
                    <p>Thank you for your Purchase!</p>
                     <p>Developed By ZY Dev's!</p>
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js'></script>
            <script>
                JsBarcode('#barcode', '{$orderId}', {
                    format: 'CODE128',
                    displayValue: true,
                    fontSize: 14
                });

                // Trigger print dialog
                window.onload = function() {
                    window.print();
                }
            </script>
            </body>
            </html>";

            // Close database connections
            $stmtVendor->close();
            $stmt->close();
            $conn->close();
        } else {
            echo "customer not found";
        }
    } else {
        echo "Order not found";
    }
} else {
    echo "Order ID not provided";
}
?>
