<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Start output buffering
ob_start();

require 'db/config.php'; // Ensure this file does not produce any output

if (!isset($conn)) {
    die("Database connection not available.");
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : ''; // Check product_id field
    $product_name = $_POST['product_name'];
    $barcode_number = $_POST['barcode_number'];
    $price = $_POST['price'];

    // Prepare SQL and handle errors
    if (!empty($product_id)) {
        $sql = "UPDATE products SET product_name=?, barcode_number=?, price=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", $product_name, $barcode_number, $price, $product_id);
        if ($stmt->execute()) {
            // Redirect after successful update
            header("Location: " . $_SERVER['PHP_SELF'] . "?message=updated");
            exit();
        } else {
            // Redirect with error message
            header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode("Error updating record: " . $conn->error));
            exit();
        }
        $stmt->close();
    } else {
        // Insert a new product
        $sql = "INSERT INTO products (product_name, barcode_number, price) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssd", $product_name, $barcode_number, $price);
        if ($stmt->execute()) {
            // Redirect after successful insertion
            header("Location: " . $_SERVER['PHP_SELF'] . "?message=added");
            exit();
        } else {
            // Redirect with error message
            header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode("Error: " . $conn->error));
            exit();
        }
        $stmt->close();
    }
}

// Fetch all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Display message if set
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Include the loader here, after handling the headers
include 'loader.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>product Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
         <script>
    // Show alert if message is present
    window.onload = function() {
        var message = "<?php echo addslashes($message); ?>";
        if (message === 'updated') {
            alert('Product updated successfully!');
        } else if (message === 'added') {
            alert('Product added successfully!');
        } else if (message) {
            alert(message);
        }
    }
    </script>
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
.product-form input[type="text"], .product-form input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.product-form input[type="submit"] {
    background-color: #28a745;
    color: white;
    cursor: pointer;
}

@media (max-width: 768px) {
    .product-form input[type="text"], .product-form input[type="submit"] {
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
<button class="btn" id="openModalBtn">Add New product</button>

<!-- The Modal -->
<!-- Add New product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModalBtn">&times;</span>
        <h2>Add New product</h2>
        <form class="product-form" action="products_data.php" method="POST">
            <label for="product_name">product Name:</label>
            <input type="text" id="product_name" name="product_name" required>

            <label for="barcode_number">Barcode Number:</label>
            <input type="text" id="barcode_number" name="barcode_number">

            <label for="price">price:</label>
            <input type="text" id="price" name="price">



            <input type="hidden" name="add_product" value="1">
            <input type="submit" value="Add product">
        </form>
    </div>
</div>

<!-- Edit product Modal -->
<div id="editproductModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditModalBtn">&times;</span>
        <h2>Edit product</h2>
        <form class="product-form" action="products_data.php" method="POST">
        <!-- <input type="hidden" id="product_id" name="product_id"> -->

            <input type="hidden" id="edit_product_id" name="product_id">
            
            <label for="edit_product_name">product Name:</label>
            <input type="text" id="edit_product_name" name="product_name" required>

            <label for="edit_barcode_number">Barcode Number:</label>
            <input type="text" id="edit_barcode_number" name="barcode_number">

            <label for="edit_price">price:</label>
            <input type="text" id="edit_price" name="price">

   

            <input type="hidden" name="edit_product" value="1">
            <input type="submit" value="Update product">
        </form>
    </div>
</div>

<!-- product Table -->
<h2>products</h2>
<div class="table-container">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Barcode Number</th>
            <th>price</th>
       
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr data-id='" . $row["id"] . "'>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td class='product_name'>" . $row["product_name"] . "</td>";
            echo "<td class='barcode_number'>" . $row["barcode_number"] . "</td>";
            echo "<td class='price'>" . $row["price"] . "</td>";
     
            echo "<td>";
            echo "<button class='btn-action' onclick='editproduct(" . $row["id"] . ")'><i class='fas fa-edit'></i></button>";
            echo "<button class='btn-action' onclick='deleteproduct(" . $row["id"] . ")'><i class='fas fa-trash'></i></button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No products found</td></tr>";
    }
    ?>
</tbody>

</table>

</div>

    </main>

<script src="assets\script.js"></script>

<?php $conn->close(); ?>
<script>
function editproduct(product_id) {
    // Fetch the product row by product ID
    const row = document.querySelector(`tr[data-id='${product_id}']`);
    const product_name = row.querySelector('.product_name').innerText;
    const barcode_number = row.querySelector('.barcode_number').innerText;
    const price = row.querySelector('.price').innerText;
   

    // Set the form values for editing
    document.getElementById('edit_product_id').value = product_id;
    document.getElementById('edit_product_name').value = product_name;
    document.getElementById('edit_barcode_number').value = barcode_number;
    document.getElementById('edit_price').value = price;


   
    document.getElementById('editproductModal').style.display = 'block';
}


var closeEditModalBtn = document.getElementById("closeEditModalBtn");
closeEditModalBtn.onclick = function() {
    document.getElementById('editproductModal').style.display = 'none';
}

</script>
<script>
 
var modal = document.getElementById("productModal");
var openModalBtn = document.getElementById("openModalBtn");
var closeModalBtn = document.getElementById("closeModalBtn");

openModalBtn.onclick = function() {
    modal.style.display = "block";
}

closeModalBtn.onclick = function() {
    modal.style.display = "none";
}


var editModal = document.getElementById("editproductModal");
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
function deleteproduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
    
        window.location.href = 'Action/Delete/delete_product.php?id=' + id;
    }
}
</script>

</script>

</body>
</html>

<?php
// End output buffering and flush output
ob_end_flush();
?>
