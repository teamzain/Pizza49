<?php
session_start();
include 'loader.html';
@include 'db/config.php';
$monthlyTotal = 0;
$salesCount = 0;
$totalQuantity = 0;
$averageSaleValue = 0;
$searchMonth = '';
$salesData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchMonth = $_POST["search_month"];
    
    // Query for summary data
    $summarySQL = "SELECT COUNT(*) as sales_count, SUM(grand_total) as monthly_total,
                   SUM(JSON_LENGTH(sale_quantity)) as total_quantity
                   FROM sale
                   WHERE DATE_FORMAT(invoice_date, '%Y-%m') = ?";
    
    $summaryStmt = $conn->prepare($summarySQL);
    $summaryStmt->bind_param("s", $searchMonth);
    $summaryStmt->execute();
    $summaryResult = $summaryStmt->get_result();
    
    if ($row = $summaryResult->fetch_assoc()) {
        $monthlyTotal = $row['monthly_total'] ?? 0;
        $salesCount = $row['sales_count'] ?? 0;
        $totalQuantity = $row['total_quantity'] ?? 0;
        $averageSaleValue = $salesCount > 0 ? $monthlyTotal / $salesCount : 0;
    }
    
    $summaryStmt->close();

    // Query for detailed sale data
    $detailSQL = "SELECT s.*, c.customer_name 
                  FROM sale s
                  JOIN customers c ON s.customer_id = c.customer_id
                  WHERE DATE_FORMAT(s.invoice_date, '%Y-%m') = ?
                  ORDER BY s.invoice_date";

    $detailStmt = $conn->prepare($detailSQL);
    $detailStmt->bind_param("s", $searchMonth);
    $detailStmt->execute();
    $detailResult = $detailStmt->get_result();

    while ($sale = $detailResult->fetch_assoc()) {
        $sale['product_name'] = json_decode($sale['product_name'], true);
        $sale['sale_quantity'] = json_decode($sale['sale_quantity'], true);
        $sale['sale_price'] = json_decode($sale['sale_price'], true);
        $sale['total_amount'] = json_decode($sale['total_amount'], true);
        $salesData[] = $sale;
    }

    $detailStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/reportstyle.css">
    <title>Dani's Fabricoo - Monthly Sales Report</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .table-container {
                overflow-x: visible;
            }
            table {
                font-size: 8pt;
                width: 100%;
                table-layout: fixed;
            }
            th, td {
                padding: 2px;
                word-wrap: break-word;
            }
        }
        #printButton {
            margin: 20px 0;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<div  class="no-print">
<?php include 'navbar.php'; ?>
</div>
<main class="main-content">
<div  class="no-print">
<?php include 'topbar.php'; ?>
    </div>
    <div class="container">
        <h1>Monthly Sales Report</h1>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="no-print">
            <input type="month" name="search_month" required value="<?php echo $searchMonth; ?>">
            <input type="submit" value="Search">
        </form>
        <button id="printButton" onclick="window.print();" class="no-print"><i class='bx bx-printer bx-sm'></i></button>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <?php if ($salesCount > 0): ?>
                <h2>Sales Summary for <?php echo date('F Y', strtotime($searchMonth . '-01')); ?></h2>
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Sales</h3>
                        <p>Rs <?php echo number_format($monthlyTotal, 2); ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Number of Sales</h3>
                        <p><?php echo $salesCount; ?></p>
                    </div>
              
                    <div class="summary-card">
                        <h3>Average Sale Value</h3>
                        <p>Rs <?php echo number_format($averageSaleValue, 2); ?></p>
                    </div>
                </div>

                <h2>Detailed Sales Report</h2>
                <div class="table-container">
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Sale ID</th>
                            <th>Customer Name</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total Amount</th>
                            <th>Grand Total</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Payment Type</th>
                        </tr>
                        <?php foreach ($salesData as $sale): ?>
                            <?php for ($i = 0; $i < count($sale['product_name']); $i++): ?>
                                <tr>
                                    <?php if ($i === 0): ?>
                                        <td rowspan="<?php echo count($sale['product_name']); ?>"><?php echo date('Y-m-d', strtotime($sale['invoice_date'])); ?></td>
                                        <td rowspan="<?php echo count($sale['product_name']); ?>"><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                                        <td rowspan="<?php echo count($sale['product_name']); ?>"><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($sale['product_name'][$i]); ?></td>
                                    <td><?php echo htmlspecialchars($sale['sale_quantity'][$i]); ?></td>
                                    <td>Rs <?php echo number_format($sale['sale_price'][$i], 2); ?></td>
                                    <td>Rs <?php echo number_format($sale['total_amount'][$i], 2); ?></td>
                                    <?php if ($i === 0): ?>
                                        <td rowspan="<?php echo count($sale['product_name']); ?>">Rs <?php echo number_format($sale['grand_total'], 2); ?></td>
                                        <td rowspan="<?php echo count($sale['product_name']); ?>">Rs <?php echo number_format($sale['paid_amount'], 2); ?></td>
                                        <td rowspan="<?php echo count($sale['product_name']); ?>">Rs <?php echo number_format($sale['due_amount'], 2); ?></td>
                                        <td rowspan="<?php echo count($sale['product_name']); ?>"><?php echo htmlspecialchars($sale['payment_type']); ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endfor; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-results">No sales recorded for <?php echo date('F Y', strtotime($searchMonth . '-01')); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<script src="assets/script.js"></script>
</body>
</html>