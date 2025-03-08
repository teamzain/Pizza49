<?php
ob_start();
session_start();
include 'loader.html';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

@include 'db/config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
    $customer_name = $_POST['customer_name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $shop_name = $_POST['shop_name'];

    // If customer_id is provided, update the customer
    if (!empty($customer_id)) {
        $sql = "UPDATE customers SET customer_name='$customer_name', phone_number='$phone_number', address='$address', shop_name='$shop_name' WHERE customer_id='$customer_id'";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Customer updated successfully!'); window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
            exit(); // Important to prevent further code execution
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        // Otherwise, insert a new customer
        $sql = "INSERT INTO customers (customer_name, phone_number, address, shop_name) 
                VALUES ('$customer_name', '$phone_number', '$address', '$shop_name')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Customer added successfully!'); window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
            exit(); // Important to prevent form resubmission
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Fetch all customers
$sql = "SELECT * FROM customers";
$result = $conn->query($sql);
ob_end_flush();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
 
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    padding-top: 60px;
   
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%;
    max-width: 500px;
    border-radius: 10px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Button styles */
.btn {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    margin: 10px 0;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}

.btn:hover {
    background-color: #218838;
}

@media (max-width: 768px) {
    .btn {
        padding: 8px 15px;
        font-size: 14px;
    }
}

/* Form styles */
.customer-form input[type="text"], .customer-form input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.customer-form input[type="submit"] {
    background-color: #28a745;
    color: white;
    cursor: pointer;
}

@media (max-width: 768px) {
    .customer-form input[type="text"], .customer-form input[type="submit"] {
        font-size: 14px;
        padding: 8px;
    }
}
.table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    margin-top: 20px; /* Add some margin for better spacing */
}

/* Table styles */
table {
    width: 100%; /* Ensure table takes full width of container */
    border-collapse: collapse;
    table-layout: auto; /* Let columns adjust automatically */
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
    word-wrap: break-word; /* Allows content to wrap inside cells */
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}

/* Responsive table adjustments */
@media (max-width: 768px) {
    th, td {
        padding: 6px;
        font-size: 14px;
    }
}

@media (max-width: 600px) {
    th, td {
        padding: 5px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    th, td {
        padding: 4px;
        font-size: 11px;
    }
}

@media (max-width: 320px) {
    th, td {
        padding: 3px;
        font-size: 10px;
    }
}

/* Action buttons styles */
.btn-action {
    background: none;
    border: none;
    cursor: pointer;
    color: #007bff;
    font-size: 18px;
    margin: 0 5px;
}

.btn-action:hover {
    color: #0056b3;
}

@media (max-width: 768px) {
    .btn-action {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .btn-action {
        font-size: 14px;
    }
}
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>
<main class="main-content">
<?php include 'topbar.php'; ?>
<button class="btn" id="openModalBtn">Add New Customer</button>

<!-- The Modal -->
<!-- Add New Customer Modal -->
<div id="customerModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModalBtn">&times;</span>
        <h2>Add New Customer</h2>
        <form class="customer-form" action="customer_registration.php" method="POST">
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>

            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number">

            <label for="address">Address:</label>
            <input type="text" id="address" name="address">

            <label for="shop_name">Shop Name:</label>
            <input type="text" id="shop_name" name="shop_name">

            <input type="hidden" name="add_customer" value="1">
            <input type="submit" value="Add Customer">
        </form>
    </div>
</div>

<!-- Edit Customer Modal -->
<div id="editCustomerModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditModalBtn">&times;</span>
        <h2>Edit Customer</h2>
        <form class="customer-form" action="customer_registration.php" method="POST">
        <!-- <input type="hidden" id="customer_id" name="customer_id"> -->

            <input type="hidden" id="edit_customer_id" name="customer_id">
            
            <label for="edit_customer_name">Customer Name:</label>
            <input type="text" id="edit_customer_name" name="customer_name" required>

            <label for="edit_phone_number">Phone Number:</label>
            <input type="text" id="edit_phone_number" name="phone_number">

            <label for="edit_address">Address:</label>
            <input type="text" id="edit_address" name="address">

            <label for="edit_shop_name">Shop Name:</label>
            <input type="text" id="edit_shop_name" name="shop_name">

            <input type="hidden" name="edit_customer" value="1">
            <input type="submit" value="Update Customer">
        </form>
    </div>
</div>

<!-- Customer Table -->
<h2>Customers</h2>
<div class="table-container">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone Number</th>
            <th>Address</th>
            <th>Shop Name</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr data-id='" . $row["customer_id"] . "'>";
            echo "<td>" . $row["customer_id"] . "</td>";
            echo "<td class='customer_name'>" . $row["customer_name"] . "</td>";
            echo "<td class='phone_number'>" . $row["phone_number"] . "</td>";
            echo "<td class='address'>" . $row["address"] . "</td>";
            echo "<td class='shop_name'>" . $row["shop_name"] . "</td>";
            echo "<td>";
            echo "<button class='btn-action' onclick='editCustomer(" . $row["customer_id"] . ")'><i class='fas fa-edit'></i></button>";
            echo "<button class='btn-action' onclick='deleteCustomer(" . $row["customer_id"] . ")'><i class='fas fa-trash'></i></button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No customers found</td></tr>";
    }
    ?>
</tbody>

</table>

</div>

    </main>

<script src="assets\script.js"></script>

<?php $conn->close(); ?>
<script>
function editCustomer(customer_id) {
    // Fetch the customer row by customer ID
    const row = document.querySelector(`tr[data-id='${customer_id}']`);
    const customer_name = row.querySelector('.customer_name').innerText;
    const phone_number = row.querySelector('.phone_number').innerText;
    const address = row.querySelector('.address').innerText;
    const shop_name = row.querySelector('.shop_name').innerText;

    // Set the form values for editing
    document.getElementById('edit_customer_id').value = customer_id;
    document.getElementById('edit_customer_name').value = customer_name;
    document.getElementById('edit_phone_number').value = phone_number;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_shop_name').value = shop_name;

    // Show the modal for editing
    document.getElementById('editCustomerModal').style.display = 'block';
}

// Close the edit modal
var closeEditModalBtn = document.getElementById("closeEditModalBtn");
closeEditModalBtn.onclick = function() {
    document.getElementById('editCustomerModal').style.display = 'none';
}

</script>
<script>
   // Modal for adding a customer
var modal = document.getElementById("customerModal");
var openModalBtn = document.getElementById("openModalBtn");
var closeModalBtn = document.getElementById("closeModalBtn");

openModalBtn.onclick = function() {
    modal.style.display = "block";
}

closeModalBtn.onclick = function() {
    modal.style.display = "none";
}

// Modal for editing a customer
var editModal = document.getElementById("editCustomerModal");
var closeEditModalBtn = document.getElementById("closeEditModalBtn");

closeEditModalBtn.onclick = function() {
    editModal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    } else if (event.target == editModal) {
        editModal.style.display = "none";
    }
}

</script>

<script>
function deleteCustomer(id) {
    if (confirm('Are you sure you want to delete this customer?')) {
        // Corrected path with forward slashes
        window.location.href = 'Action/Delete/delete_customer.php?id=' + id;
    }
}
</script>

</script>

</body>
</html>
