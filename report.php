<?php
session_start();
include 'db/config.php'; // Include database connection



// Initialize variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('monday this week'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('sunday this week'));
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'complete';
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'daily';

// Build SQL query based on timeframe and filter
switch ($timeframe) {
    case 'daily':
        $dateCondition = "DATE(date) = '" . date('Y-m-d', strtotime($start_date)) . "'";
        $dateInputType = 'date';
        $displayDateFormat = date('l, F j, Y', strtotime($start_date));
        break;
    case 'weekly':
        $dateCondition = "DATE(date) BETWEEN '$start_date' AND '$end_date'";
        $dateInputType = 'date';
        $displayDateFormat = "Week from " . date('F j', strtotime($start_date)) . " to " . date('F j, Y', strtotime($end_date));
        break;
    case 'monthly':
        $monthYear = date('Y-m', strtotime($start_date));
        $dateCondition = "DATE_FORMAT(date, '%Y-%m') = '$monthYear'";
        $dateInputType = 'month';
        $displayDateFormat = date('F Y', strtotime($start_date));
        break;
    case 'yearly':
        $year = date('Y', strtotime($start_date));
        $dateCondition = "YEAR(date) = '$year'";
        $dateInputType = 'year';
        $displayDateFormat = $year;
        break;
    default:
        $dateCondition = "DATE(date) = '" . date('Y-m-d') . "'";
        $dateInputType = 'date';
        $displayDateFormat = date('l, F j, Y');
}

// Build filter condition
$filterCondition = ($filter === 'complete') 
    ? '' 
    : "AND order_app = '$filter'";

// Combine conditions
$sql = "SELECT * FROM shopsale3 WHERE $dateCondition $filterCondition";

$result = $conn->query($sql);

// Initialize variables for calculations
$totalGrandTotal = 0;
$totalDueAmount = 0;
$totalQuantity = 0;
$totalAppPrice = 0;
$orderCount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza49</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Container Styles */
        /* .main-content {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        } */

        /* Dashboard Header */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }

        .dashboard-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .print-button {
            background-color: white;
            color: #764ba2;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .print-button i {
            margin-right: 8px;
        }

        /* Filter Form */
        .filter-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        @media (max-width: 768px) {
    .form-grid, .summary-grid {
        grid-template-columns: 1fr;
    }

    .main-content {
       /* Added margin-top for mobile screens */
        width: 95%; /* Optional: make container slightly wider on mobile */
        padding: 10px; /* Optional: reduce padding on mobile */
    }
}

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .submit-button {
            background-color: #764ba2;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            align-self: flex-end;
        }

        /* Table Styles */
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .sales-table th {
            background-color: #f1f3f5;
            color: #495057;
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .sales-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .sales-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .summary-card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .summary-card h3 {
            margin: 0 0 10px;
            font-size: 14px;
            opacity: 0.7;
        }

        .summary-card p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .form-grid, .summary-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #print-section, #print-section * {
                visibility: visible;
            }
            #print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                font-size: 10px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<?php
include 'navbar.php'; // Include before any HTML or output
?>
    <main class="main-content">
    <?php include 'topbar.php'; ?>
        <div style="background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <!-- Header -->
            <div class="dashboard-header">
                <div>
                    <h1>Sales Performance Dashboard</h1>
                    <p style="margin: 5px 0 0; opacity: 0.8;">Comprehensive Sales Insights</p>
                </div>
                <button onclick="window.print()" class="print-button">
                    <i class="fas fa-print"></i>Print Report
                </button>
            </div>

            <!-- Filter Form -->
            <form method="GET" class="filter-form">
                <div class="form-grid">
                    <!-- Timeframe Selection -->
                    <div class="form-group">
                        <label>Timeframe</label>
                        <select name="timeframe" id="timeframe" class="form-control">
                            <option value="daily" <?= $timeframe === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $timeframe === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $timeframe === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $timeframe === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                        </select>
                    </div>

                    <!-- Date Inputs -->
                    <div id="start-date-container" class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" id="start_date" 
                               value="<?= htmlspecialchars($start_date) ?>" 
                               class="form-control">
                    </div>

                    <div id="end-date-container" style="display: none;" class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" id="end_date" 
                               value="<?= htmlspecialchars($end_date) ?>" 
                               class="form-control">
                    </div>

                    <!-- Order Type Filter -->
                    <div class="form-group">
                        <label>Order Type</label>
                        <select name="filter" class="form-control">
                            <option value="complete" <?= $filter === 'complete' ? 'selected' : '' ?>>All Orders</option>
                            <option value="foodpanda" <?= $filter === 'Food Panda' ? 'selected' : '' ?>>Food Panda</option>
                            <option value="dastak" <?= $filter === 'Dastak' ? 'selected' : '' ?>>Dastak</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group" style="justify-content: flex-end; display: flex;">
                        <button type="submit" class="submit-button">
                            Generate Report
                        </button>
                    </div>
                </div>
            </form>

            <!-- Report Content -->
            <div style="padding: 20px;">
                <h2 style="font-size: 18px; color: #333; margin-bottom: 15px;">Report for <?= $displayDateFormat ?></h2>
                
                <div id="print-section">
                    <!-- Sales Table -->
                    <div style="overflow-x: auto;">
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th>Order Code</th>
                                    <th>Customer</th>
                                    <th>Order App</th>
                                    <th style="text-align: right;">Grand Total</th>
                                    <th style="text-align: right;">App Price</th>
                                    <th style="text-align: right;">Paid Amount</th>
                                    <th style="text-align: right;">Due Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['order_code']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($row['customer_name']) ?>
                                                <div style="font-size: 12px; color: #666;"><?= htmlspecialchars($row['customer_contact_number']) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($row['order_app']) ?></td>
                                            <td style="text-align: right;"><?= number_format($row['grandtotal'], 2) ?></td>
                                            <td style="text-align: right;"><?= number_format($row['app_price'], 2) ?></td>
                                            <td style="text-align: right;"><?= number_format($row['paid_amount'], 2) ?></td>
                                            <td style="text-align: right;"><?= number_format($row['due_amount'], 2) ?></td>
                                        </tr>
                                        <?php
                                        // Update totals
                                        $totalGrandTotal += $row['grandtotal'];
                                        $totalDueAmount += $row['due_amount'];
                                        $totalAppPrice += $row['app_price'];
                                        $orderCount++;
                                        ?>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 15px; color: #666;">No sales data found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Cards -->
                    <div class="summary-grid">
                        <div class="summary-card" style="background-color: #e6f2ff; color: #0056b3;">
                            <h3>Total Sales</h3>
                            <p><?= number_format($totalGrandTotal, 2) ?></p>
                        </div>
                        <div class="summary-card" style="background-color: #e6f3e6; color: #28a745;">
                            <h3>Total Orders</h3>
                            <p><?= $orderCount ?></p>
                        </div>
                        <div class="summary-card" style="background-color: #f0e6f3; color: #6f42c1;">
                            <h3>Total App Price</h3>
                            <p><?= number_format($totalAppPrice, 2) ?></p>
                        </div>
                        <div class="summary-card" style="background-color: #f8e6e6; color: #dc3545;">
                            <h3>Total Due Amount</h3>
                            <p><?= number_format($totalDueAmount, 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/script.js"></script>
  
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const timeframeSelect = document.getElementById('timeframe');
        const startDateContainer = document.getElementById('start-date-container');
        const endDateContainer = document.getElementById('end-date-container');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        function updateDateInputs() {
            switch(timeframeSelect.value) {
                case 'daily':
                    endDateContainer.style.display = 'none';
                    startDateInput.type = 'date';
                    startDateInput.value = '<?= date('Y-m-d') ?>';
                    break;
                case 'weekly':
                    endDateContainer.style.display = 'block';
                    startDateInput.type = 'date';
                    endDateInput.type = 'date';
                    
                    // Set default to current week
                    const today = new Date();
                    const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
                    const endOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 6));
                    
                    startDateInput.value = startOfWeek.toISOString().split('T')[0];
                    endDateInput.value = endOfWeek.toISOString().split('T')[0];
                    break;
                case 'monthly':
                    endDateContainer.style.display = 'none';
                    startDateInput.type = 'month';
                    startDateInput.value = '<?= date('Y-m') ?>';
                    break;
                case 'yearly':
                    endDateContainer.style.display = 'none';
                    startDateInput.type = 'number';
                    startDateInput.min = '2000';
                    startDateInput.max = '<?= date('Y') ?>';
                    startDateInput.value = '<?= date('Y') ?>';
                    break;
            }
        }

        // Initial setup
        updateDateInputs();

        // Add event listener for changes
        timeframeSelect.addEventListener('change', updateDateInputs);
    });
    </script>
</body>
</html