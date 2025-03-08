<?php
session_start(); ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/purchase.css">
    <title>Dani's Fabric</title>
    <style>
        .recent-orders table {
            width: 100%;
            border-collapse: collapse;
        }
        .recent-orders th, .recent-orders td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .recent-orders th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="main-content">
    <div class="top-bar">
        <button class="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="search-sale-id" placeholder="Search Sale ID...">
            <button id="search-button">Search</button>
        </div>
        <div class="user-menu">
            <i class="fas fa-bell"></i>
            <div class="avatar-wrapper">
                <img src="img/logo.jpg" alt="User Avatar" class="user-avatar">
                <ul class="dropdown-menu">
                    <li><a href="#">Update Username</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
    <br><br>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Invoice Date</th>
                    <th>Customer Name</th>
                    <th>Product Name</th>
                    <th>Received Quantity</th>
                    <th>Purchasing Price</th>
                    <th>Total Amount</th>
                    <th>Grand Total</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Payment Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                @include 'db/config.php';

                // Check if the connection is successful
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Check if the search button was clicked
                if (isset($_POST['search_sale_id'])) {
                    $saleId = $_POST['search_sale_id'];
                    $saleQuery = "SELECT p.sale_id, p.invoice_date, c.customer_name AS customer_name, p.grand_total, p.paid_amount, p.due_amount, p.payment_type, p.product_name, p.sale_quantity, p.sale_price, p.total_amount FROM sale p INNER JOIN customers c ON p.customer_id = c.customer_id WHERE p.sale_id = ?";
                    $stmt = $conn->prepare($saleQuery);
                    $stmt->bind_param("i", $saleId);
                } else {
                    // Default query to fetch all sales if no search is performed
                    $saleQuery = "SELECT p.sale_id, p.invoice_date, c.customer_name AS customer_name, p.grand_total, p.paid_amount, p.due_amount, p.payment_type, p.product_name, p.sale_quantity, p.sale_price, p.total_amount FROM sale p INNER JOIN customers c ON p.customer_id = c.customer_id";
                    $stmt = $conn->prepare($saleQuery);
                }

                // Execute the query
                $stmt->execute();
                $saleResult = $stmt->get_result();

                // Check if the query was successful
                if ($saleResult) {
                    if ($saleResult->num_rows > 0) {
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

                    // Free the result set
                    $saleResult->free_result();
                } else {
                    echo "<tr><td colspan='11'>Error fetching data: " . $conn->error . "</td></tr>";
                }

                // Close the database connection
                $stmt->close();
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</main>
<script src="assets/script.js"></script>
<script>
    $(document).ready(function() {
        $('#search-button').on('click', function() {
            var saleId = $('#search-sale-id').val();
            $.ajax({
                url: 'fetch_sales.php', // Change to your PHP file
                type: 'POST',
                data: { search_sale_id: saleId },
                success: function(response) {
                    $('tbody').html(response);
                }
            });
        });
    });
</script>
</body>
</html>
