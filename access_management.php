<?php
session_start();
include 'db/config.php'; // Use your actual database config file

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$staffId = $_GET['id'] ?? null;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = $_POST['staff_id'];
    $sections = isset($_POST['sections']) ? implode(',', $_POST['sections']) : ''; // Convert array to comma-separated string

    // Update the user's sections in the database
    $sql = "UPDATE users SET sections = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $sections, $staffId);
    
    if ($stmt->execute()) {
        // Redirect to staff_access_authentication.php on successful update
        header("Location: staff_access_authentication.php");
        exit();
    } else {
        // Handle error (optional)
        echo "Error updating access: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Fetch current access for the staff member
    $sql = "SELECT sections FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentSections = [];

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Check if 'sections' is null or empty before exploding
        $currentSections = !empty($row['sections']) ? explode(',', $row['sections']) : []; 
    }

    // Define all sections
    $allSections = [
        'dashboard.php' => 'Dashboard',
        'staff_access_authentication.php' => 'Staff Access Authentication',
        'customer_registration.php' => 'Customer Registration',
        'vendor_registration.php' => 'Vendor Registration',
        'vehicle_registration.php' => 'Vehicle Registration',
        'staff_registration.php' => 'Staff Registration',
        'products_data.php' => 'Products',
        'purchase.php' => 'Purchase',
        'sale.php' => 'Sale',
        'return_exchange_page.php' => 'Return',
        'stock_management.php' => 'Stock Management',
        'shop_expense.php' => 'Shop Expense',
        'customer_balance_sheet.php' => 'Customer Balance Sheet',
        'vendor_balance_sheet.php' => 'Vendor Balance Sheet',
        'daily_report.php' => 'Daily Report',
        'monthly_report.php' => 'Monthly Report',
        'yearly_report.php' => 'Yearly Report',
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <main class="main-content">
        <h2>Manage Access for Staff ID: <?php echo htmlspecialchars($staffId); ?></h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo htmlspecialchars($staffId); ?>" method="POST">
            <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($staffId); ?>">
            <?php foreach ($allSections as $file => $title): ?>
                <div>
                    <label>
                        <input type="checkbox" name="sections[]" value="<?php echo htmlspecialchars($file); ?>" 
                        <?php echo in_array($file, $currentSections) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($title); ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <button type="submit">Update Access</button>
        </form>
    </main>
</body>
</html>
