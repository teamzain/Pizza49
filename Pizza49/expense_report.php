<?php
session_start();
include 'db/config.php';

// Initialize variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'daily';

// Display data only after a search
$showData = isset($_GET['start_date']) || isset($_GET['timeframe']);

// Set date condition based on timeframe
switch ($timeframe) {
    case 'daily':
        $dateCondition = "DATE(date) = '$start_date'";
        break;
    case 'monthly':
        $monthYear = date('Y-m', strtotime($start_date));
        $dateCondition = "DATE_FORMAT(date, '%Y-%m') = '$monthYear'";
        break;
    case 'yearly':
        $year = date('Y', strtotime($start_date));
        $dateCondition = "YEAR(date) = '$year'";
        break;
    default:
        $dateCondition = "DATE(date) = '$start_date'";
}

// Fetch expenses if data should be displayed
$total_expense = 0;
$staff_expenses = [];

if ($showData) {
    // Fetch total expenses
    $sql = "SELECT * FROM expenses WHERE $dateCondition";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $amount = (float)$row['amount'];
        $total_expense += $amount;
    }

    // Fetch staff-wise total expenses
    $sqlStaff = "SELECT employee_name, SUM(amount) as total_amount
                 FROM expenses
                 WHERE $dateCondition AND expense_type = 'staff'
                 GROUP BY employee_name";
    $staffResult = $conn->query($sqlStaff);

    while ($staffRow = $staffResult->fetch_assoc()) {
        $staff_expenses[$staffRow['employee_name']] = (float)$staffRow['total_amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }

        .filter-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .form-grid {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-control {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .submit-button {
            padding: 8px 15px;
            border: none;
            background-color: #667eea;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .submit-button:hover {
            background-color: #5a67d8;
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sales-table th, .sales-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .summary-card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Mobile responsiveness */
        @media (min-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <main class="main-content">
    <?php include 'topbar.php'; ?>

        <div class="dashboard-header">
            <h1>Expense Dashboard</h1>
        </div>

        <form method="GET" class="filter-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Timeframe</label>
                    <select name="timeframe" id="timeframe" class="form-control" onchange="updateDateInput()">
                        <option value="daily" <?= $timeframe === 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="monthly" <?= $timeframe === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="yearly" <?= $timeframe === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" class="form-control">
                </div>

                <div class="form-group">
                    <button type="submit" class="submit-button">Search</button>
                </div>
            </div>
        </form>

        <?php if ($showData): ?>
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Expense Type</th>
                        <th>Staff Name</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): 
                        $result->data_seek(0); // Reset pointer
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['expense_type']) ?></td>
                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= number_format($row['amount'], 2) ?></td>
                            </tr>
                        <?php endwhile; 
                    else: ?>
                        <tr>
                            <td colspan="5">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="summary-grid">
                <div class="summary-card">
                    <h3>Total Expense</h3>
                    <p><?= number_format($total_expense, 2) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script src="assets/script.js"></script>
    <script>
        function updateDateInput() {
            const timeframe = document.getElementById('timeframe').value;
            const dateInput = document.getElementById('start_date');

            if (timeframe === 'monthly') {
                dateInput.type = 'month';
            } else if (timeframe === 'yearly') {
                dateInput.type = 'year';
            } else {
                dateInput.type = 'date';
            }
        }
    </script>
</body>
</html>
