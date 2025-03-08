<?php
session_start();


if (!isset($_SESSION['user_id'])) {

    header("Location: index.php");
    exit();
}


?>


<?php
include 'loader.html';
@include 'db/config.php'; // Use forward slashes for directory separator

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : '';
    $supplier_name = $_POST['supplier_name'];
    $phone_number = $_POST['phone_number'];

    // If supplier_id is provided, update the supplier
    if (!empty($supplier_id)) {
        $sql = "UPDATE supplier SET supplier_name=?, phone_number=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $supplier_name, $phone_number, $supplier_id);
        
        if ($stmt->execute()) {
            // Use JavaScript to handle alert and redirect
            echo "<script>alert('Supplier updated successfully!'); window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
            exit(); // Important to prevent further code execution
        } else {
            echo "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insert a new supplier if supplier_id is not provided
        $sql = "INSERT INTO supplier (supplier_name, phone_number) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $supplier_name, $phone_number);
        
        if ($stmt->execute()) {
            // Use JavaScript to handle alert and redirect
            echo "<script>alert('Supplier added successfully!'); window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all suppliers
$sql = "SELECT * FROM supplier";
$result = $conn->query($sql);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>supplier Management</title>
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
.supplier-form input[type="text"], .supplier-form input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.supplier-form input[type="submit"] {
    background-color: #28a745;
    color: white;
    cursor: pointer;
}

@media (max-width: 768px) {
    .supplier-form input[type="text"], .supplier-form input[type="submit"] {
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
<button class="btn" id="openModalBtn">Add New supplier</button>

<!-- The Modal -->
<!-- Add New supplier Modal -->
<div id="supplierModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModalBtn">&times;</span>
        <h2>Add New supplier</h2>
        <form class="supplier-form" action="vendor_registration.php" method="POST">
            <label for="supplier_name">supplier Name:</label>
            <input type="text" id="supplier_name" name="supplier_name" required>
            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number">
            <input type="hidden" name="add_supplier" value="1">
            <input type="submit" value="Add supplier">
        </form>
    </div>
</div>

<!-- Edit supplier Modal -->
<div id="editsupplierModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditModalBtn">&times;</span>
        <h2>Edit supplier</h2>
        <form class="supplier-form" action="vendor_registration.php" method="POST">
        <!-- <input type="hidden" id="supplier_id" name="supplier_id"> -->

            <input type="hidden" id="edit_supplier_id" name="supplier_id">
            
            <label for="edit_supplier_name">supplier Name:</label>
            <input type="text" id="edit_supplier_name" name="supplier_name" required>

            <label for="edit_phone_number">Phone Number:</label>
            <input type="text" id="edit_phone_number" name="phone_number">


            <input type="hidden" name="edit_supplier" value="1">
            <input type="submit" value="Update supplier">
        </form>
    </div>
</div>

<!-- supplier Table -->
<h2>suppliers</h2>
<div class="table-container">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone Number</th>
        
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr data-id='" . $row["id"] . "'>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td class='supplier_name'>" . $row["supplier_name"] . "</td>";
            echo "<td class='phone_number'>" . $row["phone_number"] . "</td>";

            echo "<td>";
            echo "<button class='btn-action' onclick='editsupplier(" . $row["id"] . ")'><i class='fas fa-edit'></i></button>";
            echo "<button class='btn-action' onclick='deletesupplier(" . $row["id"] . ")'><i class='fas fa-trash'></i></button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No suppliers found</td></tr>";
    }
    ?>
</tbody>

</table>

</div>

    </main>

<script src="assets\script.js"></script>

<?php $conn->close(); ?>
<script>
function editsupplier(supplier_id) {
    const row = document.querySelector(`tr[data-id='${supplier_id}']`);
    const supplier_name = row.querySelector('.supplier_name').innerText;
    const phone_number = row.querySelector('.phone_number').innerText;

    // Set the form values for editing
    document.getElementById('edit_supplier_id').value = supplier_id;
    document.getElementById('edit_supplier_name').value = supplier_name;
    document.getElementById('edit_phone_number').value = phone_number;

    // Show the modal for editing
    document.getElementById('editsupplierModal').style.display = 'block';
}


// Close the edit modal
var closeEditModalBtn = document.getElementById("closeEditModalBtn");
closeEditModalBtn.onclick = function() {
    document.getElementById('editsupplierModal').style.display = 'none';
}

</script>
<script>
   // Modal for adding a supplier
var modal = document.getElementById("supplierModal");
var openModalBtn = document.getElementById("openModalBtn");
var closeModalBtn = document.getElementById("closeModalBtn");

openModalBtn.onclick = function() {
    modal.style.display = "block";
}

closeModalBtn.onclick = function() {
    modal.style.display = "none";
}

// Modal for editing a supplier
var editModal = document.getElementById("editsupplierModal");
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
function deletesupplier(id) {
    if (confirm('Are you sure you want to delete this supplier?')) {
        // Corrected path with forward slashes
        window.location.href = 'Action/Delete/delete_supplier.php?id=' + id;
    }
}
</script>

</script>

</body>
</html>
