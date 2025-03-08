<?php
session_start();
include 'loader.html';
@include 'db/config.php';
$yearlyTotal = 0;
$salesCount = 0;
$totalQuantity = 0;
$averageSaleValue = 0;
$searchYear = '';
$salesData = [];
$monthlySummary = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchYear = $_POST["search_year"];
    
    // Query for summary data
    $summarySQL = "SELECT COUNT(*) as sales_count, SUM(grand_total) as yearly_total,
                   SUM(JSON_LENGTH(sale_quantity)) as total_quantity,
                   MONTH(invoice_date) as month,
                   SUM(grand_total) as monthly_total
                   FROM sale
                   WHERE YEAR(invoice_date) = ?
                   GROUP BY MONTH(invoice_date)
                   ORDER BY MONTH(invoice_date)";
    
    $summaryStmt = $conn->prepare($summarySQL);
    $summaryStmt->bind_param("s", $searchYear);
    $summaryStmt->execute();
    $summaryResult = $summaryStmt->get_result();
    
    while ($row = $summaryResult->fetch_assoc()) {
        $yearlyTotal += $row['monthly_total'];
        $salesCount += $row['sales_count'];
        $totalQuantity += $row['total_quantity'];
        $monthlySummary[$row['month']] = [
            'sales_count' => $row['sales_count'],
            'monthly_total' => $row['monthly_total'],
            'total_quantity' => $row['total_quantity']
        ];
    }
    
    $averageSaleValue = $salesCount > 0 ? $yearlyTotal / $salesCount : 0;
    
    $summaryStmt->close();

    // Query for detailed sale data
    $detailSQL = "SELECT s.*, c.customer_name 
                  FROM sale s
                  JOIN customers c ON s.customer_id = c.customer_id
                  WHERE YEAR(s.invoice_date) = ?
                  ORDER BY s.invoice_date";

    $detailStmt = $conn->prepare($detailSQL);
    $detailStmt->bind_param("s", $searchYear);
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
    <title>Pizza 49 - Yearly Sales Report</title>
    <style>
        .monthly-summary {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .monthly-card {
            width: calc(25% - 10px);
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    
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
        <h1>Yearly Sales Report</h1>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="no-print">
            <select name="search_year" required>
                <?php
                $currentYear = date('Y');
                for ($year = $currentYear; $year >= $currentYear - 10; $year--) {
                    echo "<option value=\"$year\"" . ($searchYear == $year ? " selected" : "") . ">$year</option>";
                }
                ?>
            </select>
            <input type="submit" value="Search">
        </form>
        <button id="printButton" onclick="window.print();" class="no-print"><i class='bx bx-printer bx-sm'></i></button>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <?php if ($salesCount > 0): ?>
                <h2>Sales Summary for <?php echo $searchYear; ?></h2>
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Sales</h3>
                        <p>Rs <?php echo number_format($yearlyTotal, 2); ?></p>
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

                <h2>Monthly Breakdown</h2>
                <div class="monthly-summary">
                    <?php
                    $monthNames = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    foreach ($monthNames as $monthNum => $monthName):
                        $monthData = $monthlySummary[$monthNum] ?? ['sales_count' => 0, 'monthly_total' => 0, 'total_quantity' => 0];
                    ?>
                    <div class="monthly-card">
                        <h4><?php echo $monthName; ?></h4>
                        <p>Sales: <?php echo $monthData['sales_count']; ?></p>
                        <p>Total: Rs <?php echo number_format($monthData['monthly_total'], 2); ?></p>
                        <p>Quantity: <?php echo $monthData['total_quantity']; ?></p>
                    </div>
                    <?php endforeach; ?>
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
                <p class="no-results">No sales recorded for the year <?php echo $searchYear; ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<script src="assets/script.js"></script>
</body>
</html>