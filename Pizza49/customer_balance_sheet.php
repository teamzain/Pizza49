<?php
session_start();
include 'loader.html';
include 'db/config.php';

$searchResults = [];
$totalGrandTotal = 0;
$totalDueAmount = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerName = $_POST["customer_name"];
    
    $sql = "SELECT s.sale_id, c.customer_name, s.grand_total, s.due_amount, s.paid_amount, s.payment_type, s.invoice_date, s.vehicle_name
            FROM sale s
            JOIN customers c ON s.customer_id = c.customer_id
            WHERE c.customer_name LIKE ?";
    
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $customerName . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
        $totalGrandTotal += $row['grand_total'];
        $totalDueAmount += $row['due_amount'];
    }
    
    $stmt->close();
}

$conn->close();
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
    <link rel="stylesheet" href="assets/balancesheet.css">
    <title>Dani's Fabricoo</title>
    <style>
      
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="main-content">
<?php include 'topbar.php'; ?>

    <div class="container">
        <h1>Sales Search</h1>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="text" name="customer_name" placeholder="Enter customer name" required>
            <input type="submit" value="Search">
        </form>

        <?php if (!empty($searchResults)): ?>
            <div class="summary-cards">
                <div class="card">
                    <h2>Total Grand Total</h2>
                    <p>Rs <?php echo number_format($totalGrandTotal, 2); ?></p>
                </div>
                <div class="card">
                    <h2>Total Due Amount</h2>
                    <p class="<?php echo $totalDueAmount > 0 ? 'positive' : ($totalDueAmount < 0 ? 'negative' : ''); ?>">
                        <?php
                        if ($totalDueAmount > 0) {
                            echo "Rs " . number_format($totalDueAmount, 2) . " (Customer owes)";
                        } elseif ($totalDueAmount < 0) {
                            echo "Rs " . number_format(abs($totalDueAmount), 2) . " (We owe customer)";
                        } else {
                            echo "All cleared";
                        }
                        ?>
                    </p>
                </div>
            </div>

            <div class="table-container">
                <table class='table'>
                    <tr class='tr'>
                        <th class='th'>Sale ID</th>
                               <th class='th'>Invoice Date</th>
                        <th class='th'>Customer Name</th>
                        <th class='th'>Grand Total</th>
                        <th class='th'>Due Amount</th>
                        <th class='th'>Paid Amount</th>
                        <th class='th'>Payment Type</th>
                     
                        <th class='th'>Vehicle Name</th>
                    </tr>
                    <?php foreach ($searchResults as $sale): ?>
                        <tr>
                            <td class='td'><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                                  <td class='td'><?php echo htmlspecialchars($sale['invoice_date']); ?></td>
                            <td class='td'><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                            <td class='td'>Rs <?php echo number_format($sale['grand_total'], 2); ?></td>
                            <td id='td' class="<?php echo $sale['due_amount'] > 0 ? 'positive' : ($sale['due_amount'] < 0 ? 'negative' : ''); ?>">
                                <?php
                                if ($sale['due_amount'] > 0) {
                                    echo "Rs " . number_format($sale['due_amount'], 2) . " (Customer owes)";
                                } elseif ($sale['due_amount'] < 0) {
                                    echo "Rs " . number_format(abs($sale['due_amount']), 2) . " (We owe customer)";
                                } else {
                                    echo "All cleared with " . htmlspecialchars($sale['customer_name']);
                                }
                                ?>
                            </td>
                            <td class='td'>Rs <?php echo number_format($sale['paid_amount'], 2); ?></td>
                            <td class='td'><?php echo htmlspecialchars($sale['payment_type']); ?></td>
                          
                            <td class='td'><?php echo htmlspecialchars($sale['vehicle_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
        </main>
        <script src="assets/script.js"></script>
</body>
</html>