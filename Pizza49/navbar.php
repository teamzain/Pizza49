<?php
ob_start();
// Ensure no output before session_start
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session before any output
}

// Ensure headers are sent before any output
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

@include 'db/config.php'; // Ensure config.php has no output

// Fetch user sections
$user_id = $_SESSION['user_id'];
$sql = "SELECT sections FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$sections = isset($user['sections']) ? $user['sections'] : ''; // Use an empty string if $user['sections'] is null

$allowed_sections = explode(',', $sections);

// Ensure allowed_sections array contains valid section files
$all_sections = [
    "dashboard.php", "staff_access_authentication.php", "customer_registration.php", 
    "vendor_registration.php", "vehicle_registration.php", "staff_registration.php",
    "products_data.php", "purchase.php", "sale.php", "return_exchange_page.php", "return_exchange_page.php", 
     "stock_management.php", "shop_expense.php", "customer_balance_sheet.php", 
    "vendor_balance_sheet.php", "daily_report.php", "monthly_report.php", "yearly_report.php","expense_report.php","report.php","shopsale2.php","displaysale.php"
];

// Only allow sections that are defined and match the available sections
$allowed_sections = array_filter($allowed_sections, function($section) use ($all_sections) {
    return in_array($section, $all_sections);
});

// Determine which sections to display
$sections_to_display = is_array($allowed_sections) && !empty($allowed_sections) ? $allowed_sections : $all_sections;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="sidebar">
        <div class="logo">
            <img src="img/logo.jpg" alt="Pizza 49 Logo">
            <span class="logo-text">Pizza 49</span>
        </div>
        <button class="collapse-btn">
            <i class="fas fa-angle-left"></i>
            <span>Collapse</span>
        </button>

        <!-- Dashboard Section -->
        <?php if (in_array("dashboard.php", $sections_to_display)): ?>
            <div class="menu-section">
                <div class="menu-title">Dashboard</div>
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Authentication Section -->
        <?php if (in_array("staff_access_authentication.php", $sections_to_display)): ?>
            <div class="menu-section">
                <div class="menu-title">Authentication</div>
                <a href="staff_access_authentication.php" class="menu-item">
                    <i class="fas fa-user-shield"></i>
                    <span>Staff Access Authentication</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Registration Section -->
        <?php if (in_array("customer_registration.php", $sections_to_display) || in_array("vendor_registration.php", $sections_to_display) || in_array("vehicle_registration.php", $sections_to_display) || in_array("staff_registration.php", $sections_to_display)): ?>
            <div class="menu-section">
                <div class="menu-title">Registration</div>
                <?php if (in_array("customer_registration.php", $sections_to_display)): ?>
                    <a href="customer_registration.php" class="menu-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Customer Registration</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("vendor_registration.php", $sections_to_display)): ?>
                    <a href="vendor_registration.php" class="menu-item">
                        <i class="fas fa-briefcase"></i>
                        <span>Vendor Registration</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("vehicle_registration.php", $sections_to_display)): ?>
                    <a href="vehicle_registration.php" class="menu-item">
                        <i class="fas fa-truck"></i>
                        <span>Vehicle Registration</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("staff_registration.php", $sections_to_display)): ?>
                    <a href="staff_registration.php" class="menu-item">
                        <i class="fas fa-id-badge"></i>
                        <span>Staff Registration</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Products & Transactions Section -->
        <?php if (in_array("products_data.php", $sections_to_display) || in_array("purchase.php", $sections_to_display) || in_array("sale.php", $sections_to_display) || in_array("return_exchange_page.php", $sections_to_display) || in_array("return_exchange_page.php", $sections_to_display) || in_array("shopsale2.php", $sections_to_display) || in_array("displaysale.php", $sections_to_display)): ?>
            <div class="menu-section">
                <div class="menu-title">Products & Transactions</div>
                <?php if (in_array("products_data.php", $sections_to_display)): ?>
                    <a href="products_data.php" class="menu-item">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("purchase.php", $sections_to_display)): ?>
                    <a href="purchase.php" class="menu-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Purchase</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("sale.php", $sections_to_display)): ?>
                    <a href="sale.php" class="menu-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Sale</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("shopsale2.php", $sections_to_display)): ?>
                    <a href="shopsale2.php" class="menu-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Pizza Sale</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("displaysale.php", $sections_to_display)): ?>
                    <a href="displaysale.php" class="menu-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Pizza Sales History</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("return_exchange_page.php", $sections_to_display)): ?>
                    <a href="return_exchange_page.php" class="menu-item">
                        <i class="fas fa-undo-alt"></i>
                        <span>Return</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("return_exchange_page.php", $sections_to_display)): ?>
                    <a href="return_exchange_page.php" class="menu-item">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Exchange</span>
                    </a>
                <?php endif; ?>
              
            </div>
        <?php endif; ?>

        <!-- Stock Management Section -->
        <?php if (in_array("stock_management.php", $sections_to_display)): ?>
            <div class="menu-section">
                <div class="menu-title">Stock Management</div>
                <a href="stock_management.php" class="menu-item">
                    <i class="fas fa-warehouse"></i>
                    <span>Stock Management</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Financials Section -->
        <?php if (in_array("shop_expense.php", $sections_to_display) || in_array("customer_balance_sheet.php", $sections_to_display) || in_array("vendor_balance_sheet.php", $sections_to_display)): ?>
            <div class="menu-section">
                <div class="menu-title">Financials</div>
                <?php if (in_array("shop_expense.php", $sections_to_display)): ?>
                    <a href="shop_expense.php" class="menu-item">
                        <i class="fas fa-money-bill-alt"></i>
                        <span>Shop Expense</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("customer_balance_sheet.php", $sections_to_display)): ?>
                    <a href="customer_balance_sheet.php" class="menu-item">
                        <i class="fas fa-balance-scale"></i>
                        <span>Customer Balance Sheet</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("vendor_balance_sheet.php", $sections_to_display)): ?>
                    <a href="vendor_balance_sheet.php" class="menu-item">
                        <i class="fas fa-balance-scale-left"></i>
                        <span>Vendor Balance Sheet</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

 <?php if (in_array("daily_report.php", $sections_to_display) || in_array("monthly_report.php", $sections_to_display) || in_array("yearly_report.php", $sections_to_display) || in_array("expense_report.php", $sections_to_display) || in_array("report.php", $sections_to_display)): ?>
            <div class="menu-section">
                <div class="menu-title">Reports</div>
                <?php if (in_array("daily_report.php", $sections_to_display)): ?>
                    <a href="daily_report.php" class="menu-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>Daily Report</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("monthly_report.php", $sections_to_display)): ?>
                    <a href="monthly_report.php" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>

                        <span>Monthly Report</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array("yearly_report.php", $sections_to_display)): ?>
                    <a href="yearly_report.php" class="menu-item">
                    <i class="fas fa-calendar-day"></i>
                        <span>Yearly Report</span> 
                    </a>
                <?php endif; ?>


                <?php if (in_array("expense_report.php", $sections_to_display)): ?>
                    <a href="expense_report.php" class="menu-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>Expense Report</span>
                    </a>
                <?php endif; ?>

                <?php if (in_array("report.php", $sections_to_display)): ?>
                    <a href="report.php" class="menu-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>Sales Report</span>
                    </a>
                <?php endif; ?>
            </div>
             <!-- Backup Button -->
            
            <!-- Backup Button Section -->
<div class="menu-section">
    <div class="menu-title">Backup</div>
    <form action="backup.php" method="post">
        <button type="submit" class="menu-item" style="display: flex; align-items: center;">
            <i class="fas fa-database"></i>
            <span style="margin-left: 5px;">Backup Database</span>
        </button>
    </form>
</div>

       
<?php endif; ?>

            
    </nav>
</body>
        </html>
<?php
   ob_end_flush();
?>