
<?php
@include 'db/config.php';
$stmtOrder = $stmtDetails = $stmtReturnDetails = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Retrieve form data
    $invoiceDate = $_POST['invoiceDate'];
    $vendorname = $_POST['vendor'];
    $grandTotal = $_POST['grandTotal'];
    $paidAmount = $_POST['paidAmount'];
    $dueAmount = $_POST['dueAmount'];
    $paymentType = $_POST['paymentType'];

    // Prepare the order insertion query using prepared statements
    $sqlOrder = "INSERT INTO purchase (invoice_date, supplier_id, grand_total, paid_amount, due_amount, payment_type) VALUES (?, ?, ?, ?, ?, ?)";
    
    // Prepare the statement
    $stmtOrder = $conn->prepare($sqlOrder);
    if (!$stmtOrder) {
        die('Error in SQL query: ' . $conn->error);
    }

    $stmtOrder->bind_param("siddds", $invoiceDate, $vendorname, $grandTotal, $paidAmount, $dueAmount, $paymentType);

    // Execute the statement
    if ($stmtOrder->execute()) {
        $lastOrderId = $conn->insert_id;

        // Insert data into the orderdetails table
        $productNames = $_POST['productName'];
        $quantities = $_POST['quantity'];
        $rates = $_POST['rate'];
        $amountes = $_POST['totalAmount'];  // Assuming $rates contains the rate information
        $stmtDetails = $conn->prepare("INSERT INTO orderdetails2 (purchase_id, name, quantity,unit_price, total_amount) VALUES (?, ?, ?, ?, ?)");
        
        // Prepare, bind, and execute the statement
        $stmtDetails->bind_param("issdd", $lastOrderId, $productName, $quantity, $rates,$amountes);
        
        foreach ($productNames as $index => $name) {
            $productName = $productNames[$index];
            $quantity = $quantities[$index];
            $rate = $rates[$index];
            $amount = $amountes[$index]; // This is where you retrieve the rate from the form data
            $stmtDetails->execute();
           
        }

        // Update the orderbooking row with the serialized product data
        $productDataJson = json_encode($_POST['productName']);
        $productDataJson2 = json_encode($_POST['quantity']);
        $productDataJson3 = json_encode($_POST['rate']);
        $productDataJson4 = json_encode($_POST['totalAmount']);


        $conn->query("UPDATE purchase SET product_name = '$productDataJson', received_quantity = '$productDataJson2',purchasing_price ='$productDataJson3',total_amount ='$productDataJson4' WHERE purchase_id = '$lastOrderId'");

        // Update stock_filled table
        foreach ($productNames as $index => $name) {
            $productName = $productNames[$index];
            $quantity = $quantities[$index];
        
            // Fetch current quantity from stock_empty table
            $fetchQuantityQuery = "SELECT total_quantity FROM stock WHERE name = ?";
        
            $stmtFetchQuantity = $conn->prepare($fetchQuantityQuery);
            $stmtFetchQuantity->bind_param("s", $productName);
            $stmtFetchQuantity->execute();
            $stmtFetchQuantity->bind_result($currentQuantity);
            $stmtFetchQuantity->fetch();
            $stmtFetchQuantity->close();
        
            // Check if the cylinder exists in stock_empty table
            if ($currentQuantity !== null) {
                // Cylinder exists, update the quantity
                $newQuantity = $currentQuantity + $quantity;
        
                // Update quantity in stock_empty table
                $updateQuantityQuery = "UPDATE stock SET total_quantity = ? WHERE name = ?";
                $stmtUpdateQuantity = $conn->prepare($updateQuantityQuery);
                $stmtUpdateQuantity->bind_param("is", $newQuantity, $productName);
                $stmtUpdateQuantity->execute();
                $stmtUpdateQuantity->close();
            } else {
                $fetchCylinderIdQuery = "SELECT id FROM products WHERE product_name = ?";
                $stmtFetchCylinderId = $conn->prepare($fetchCylinderIdQuery);
                $stmtFetchCylinderId->bind_param("s", $productName);
                $stmtFetchCylinderId->execute();
                $stmtFetchCylinderId->bind_result($cylinderId);
                $stmtFetchCylinderId->fetch();
                $stmtFetchCylinderId->close();
        
                // Use fetched cylinder ID to insert into stock_empty table
                if ($cylinderId !== null) {
                  $insertQuantityQuery = "INSERT INTO stock (product_id, name, total_quantity) VALUES (?, ?, ?)";
                    $stmtInsertQuantity = $conn->prepare($insertQuantityQuery);
                    $stmtInsertQuantity->bind_param("isi", $cylinderId, $productName, $quantity);
                    $stmtInsertQuantity->execute();
                    $stmtInsertQuantity->close();
        }
    }
        }
        header("Location: purchase.php");
        echo "Order booked successfully"; // Move echo after header
        
        exit();
}
     else {
        echo "Error: " . $sqlOrder . "<br>" . $conn->error;
    }
  }

    // Close the prepared statements
    if ($stmtOrder instanceof mysqli_stmt) {
      $stmtOrder->close();
  }
  if ($stmtDetails instanceof mysqli_stmt) {
      $stmtDetails->close();
  }
  if ($stmtReturnDetails instanceof mysqli_stmt) {
      $stmtReturnDetails->close();
  } // Close the return booking statement
   

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!----======== CSS ======== -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <!----===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    
<title>Purchase</title>
<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
  /* Popup Style */
  .popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 1px solid #ccc;
    background-color: #fff;
    padding: 20px;
    z-index: 1000;
  }

  /* Close Button Style */
  .close {
    position: absolute;
    top: 5px;
    right: 10px;
    cursor: pointer;
  }

  /* Button Style */
  button {
    padding: 10px 20px;
    cursor: pointer;
  }

  /* Overlay Style */
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
  }
  .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .input-container {
            margin-bottom: 15px;
        }
        .input-container label {
            margin-bottom: 5px;
            display: block;
        }
        .input-container input {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .submit {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }
        .submit:hover {
            background-color: #45a049;
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
                <input type="text" placeholder="Search...">
            </div>
            <div class="user-menu">
                <i class="fas fa-bell"></i>
                <div class="avatar-wrapper">
                    <img src="img/logo.jpg" alt="User Avatar" class="user-avatar">
                    
                    <!-- Dropdown Menu -->
                    <ul class="dropdown-menu">
                        <li><a href="#">Update Username</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>

    </div>
<button id="mainButton" class="btn btn-primary">Add Purchase</button>

<?php
// Include the configuration file
@include 'db/config.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the purchase table
$purchaseQuery = "SELECT p.purchase_id, p.invoice_date, s.supplier_name AS supplier_name, p.grand_total, p.paid_amount, p.due_amount, p.payment_type, p.product_name, p.received_quantity, p.purchasing_price, p.total_amount FROM purchase p INNER JOIN supplier s ON p.supplier_id = s.id";
$purchaseResult = $conn->query($purchaseQuery);

// Check if the query was successful
if ($purchaseResult) {
    if ($purchaseResult->num_rows > 0) {
        // Output table headers
        echo "<table id='purchaseTable' class='table' style='margin-top :10px;'>";
        echo "<thead class='thead-dark '><tr><th>Invoice Date</th><th>Supplier Name</th><th>Product Name</th><th>Received Quantity</th><th>Purchasing Price</th><th>Total Amount</th><th>Grand Total</th><th>Paid Amount</th><th>Due Amount</th><th>Payment Type</th><th>Action</th></tr></thead>";
        echo "<tbody>";
        
        // Output table data
        while ($purchase = $purchaseResult->fetch_assoc()) {
            echo "<tr>";
         
            echo "<td>" . $purchase['invoice_date'] . "</td>";
            echo "<td>" . $purchase['supplier_name'] . "</td>";

            echo "<td>" . str_replace(array('[', ']', '"'), '', $purchase["product_name"]) . "</td>";
            echo "<td>" . str_replace(array('[', ']', '"'), '', $purchase["received_quantity"]) . "</td>";
            echo "<td>" . str_replace(array('[', ']', '"'), '', $purchase["purchasing_price"]) . "</td>";
            echo "<td>" . str_replace(array('[', ']', '"'), '', $purchase["total_amount"]) . "</td>";
            echo "<td>" . $purchase['grand_total'] . "</td>";
            echo "<td>" . $purchase['paid_amount'] . "</td>";
            echo "<td>" . $purchase['due_amount'] . "</td>";
            echo "<td>" . $purchase['payment_type'] . "</td>";
            echo "<td>";
                
    
   


            echo"<br>";
            echo "<a href='#' class='edit-btn' data-purchase-id='{$purchase['purchase_id']}' data-paid-amount='{$purchase['paid_amount']}' data-due-amount='{$purchase['due_amount']}' data-grand-total='{$purchase['grand_total']}'>";
            echo "<i class='bx bxs-edit bx-sm'></i </a>";
            echo"<br>";
            
            echo "<a href='generate_invoice2.php?purchase_id={$purchase['purchase_id']}' onclick='printInvoice(event)'>";
            echo "<span class='blue'><i class='bx bx-printer bx-sm'></i></span>";
            echo "</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No data found";
    }

    // Free the result set
    $purchaseResult->free_result();
} else {
    echo "Error fetching data: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close">&times;</span>
            <h2>Edit Purchase</h2>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <input type="hidden" name="purchase_id" id="purchase_id">
                <input type="hidden" id="grand_total">
                <div class="input-container ic1">
                    <label for="paid_amount">Paid Amount</label>
                    <input type="number" name="paid_amount" id="paid_amount" step="0.01" class="input">
                </div>
                <div class="input-container ic1">
                    <label for="due_amount">Due Amount:</label>
                    <input type="number" name="due_amount" id="due_amount" class="input" readonly>
                </div>
                <button type="button" class="submit" id="saveButton">Save</button>
            </form>
        </div>
    </div>
</div>
<!-- Main Popup -->
<div id="mainPopup" class="popup" style="height: 70vh; overflow-y: auto;">
  <span class="close" onclick="closeMainPopup()">&times;</span>
  <div style="max-height: calc(100% - 50px); overflow-y: auto;">
  
  
  <!-- Purchase Form -->
  <section class="container">
    <header>Purchase form</header>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="form">
      <div class="form-group">
        <label for="invoiceDate">Date:</label>
        <input type="date" id="invoiceDate" name="invoiceDate" value="<?php echo date('Y-m-d'); ?>" readonly class="form-control">
      </div>
      <div class="form-group">
        <label for="vendor">Supplier:</label>
        <select id="vendor" class="form-control" name='vendor'>
          <?php
          @include "db/config.php";
          $vendorQuery = "SELECT id, supplier_name FROM supplier";
          $vendorResult = $conn->query($vendorQuery);
          while ($vendor = $vendorResult->fetch_assoc()) {
              echo "<option value='{$vendor['id']}'>{$vendor['supplier_name']}</option>";
          }
          ?>
        </select>
      </div>
      <button id="nestedPopupButton" type="button" onclick="openNestedPopup()" class="btn btn-primary">Add Product</button>
      <div class="recent-orders">
        <table id="show" class="table">
          <tr>
            <th>Product Name</th>
            <th>Unit Price</th>
            <th>Received Quantity</th>
            <th>Total Amount</th>
          </tr>
        </table>
      </div>
      <div class="form-group">
        <label for="grandTotal">Grand Total:</label>
        <input type="text" id="grandTotal" name="grandTotal" readonly class="form-control">
      </div>
      <div class="form-group">
        <label for="paidAmount">Paid Amount:</label>
        <input type="text" id="paidAmount" name="paidAmount" oninput="calculateDueAmount()" class="form-control">
      </div>
      <div class="form-group">
        <label for="dueAmount">Due Amount:</label>
        <input type="text" id="dueAmount" name="dueAmount" readonly class="form-control">
      </div>
      <div class="form-group">
        <label for="paymentType">Payment Type:</label>
        <select id="paymentType" name="paymentType" class="form-control">
          <option value="">Please select payment type</option>
          <option value="Cash">Cash</option>
          <option value="Credit Card">Credit Card</option>
        </select>
      </div>
      <br>
      <button type="submit" name="submit" class="btn btn-primary">Submit</button>
    </form>
  </section>
</div>

<!-- Nested Popup -->
<div id="nestedPopup" class="popup">
  <span class="close" onclick="closeNestedPopup()">&times;</span>
  <p>Nested Popup Content</p>
  <div class="form-group">
    <label for="productName">Product Name:</label>
    <select class="productName form-control" name="productName[]" id="productName">
      <option value="">Please select a product</option>
      <?php
      // Include your database connection
      @include 'db/config.php';
      $sql = 'SELECT product_name FROM products'; // Modify query according to your table structure
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<option value='" . $row["product_name"] . "'>" . $row["product_name"] . "</option>";
          }
      }
      $conn->close();
      ?>
    </select>
  </div>
  <div class="form-group">
    <label for="rate">Unit Price:</label>
    <input type="text" class="rate form-control" id="rate" name="rate[]">
  </div>
  <div class="form-group">
    <label for="quantity">Purchasing Quantity:</label>
    <input type="text" name="quantity[]" id="quantity" class="quantity form-control" oninput="calculateTotalAmount()">
  </div>
  <div class="form-group">
    <label for="totalAmount">Total Amount:</label>
    <input type="text" class="totalAmount form-control" id="totalAmount" readonly name="totalAmount[]">
  </div>
  <div class="form-group">
    <input type="button" name="button" id="btn" value="Add" onclick="AddRowAndClosePopup()" class="btn btn-primary">

  </div>
</div>
    </main>
<script>
    // Get the modal
    var modal = document.getElementById("editModal");

    // Get the button that opens the modal
    var editBtn = document.querySelector('.edit-btn');

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal 
    editBtn.onclick = function() {
        var purchaseId = this.getAttribute('data-purchase-id');
        var paidAmount = this.getAttribute('data-paid-amount');
        var dueAmount = this.getAttribute('data-due-amount');
        var grandTotal = this.getAttribute('data-grand-total');

        document.getElementById('purchase_id').value = purchaseId;
        document.getElementById('paid_amount').value = paidAmount;
        document.getElementById('due_amount').value = dueAmount;
        document.getElementById('grand_total').value = grandTotal;

        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // When the user clicks on the button, open the modal
    $(".edit-btn").on("click", function() {
        var purchase_id = $(this).data("purchase-id");
        var paid_amount = $(this).data("paid-amount");
        var due_amount = $(this).data("due-amount");
        var grand_total = $(this).data("grand-total");

        $("#purchase_id").val(purchase_id);
        $("#paid_amount").val(paid_amount);
        $("#due_amount").val(due_amount);
        $("#grand_total").val(grand_total);

        $("#editModal").css("display", "block");
    });

    // When the user clicks on <span> (x), close the modal
    $(".close").on("click", function() {
        $("#editModal").css("display", "none");
    });

    // When the user clicks anywhere outside of the modal, close it
    $(window).on("click", function(event) {
        if ($(event.target).is("#editModal")) {
            $("#editModal").css("display", "none");
        }
    });

    // Calculate the due amount when the paid amount changes
    $("#paid_amount").on("input", function() {
        var paid_amount = parseFloat($(this).val());
        var grand_total = parseFloat($("#grand_total").val());
        var due_amount = grand_total - paid_amount;
        $("#due_amount").val(due_amount.toFixed(2));
    });

    // When the user clicks on the save button, update the database
    $("#saveButton").on("click", function() {
        var purchase_id = $("#purchase_id").val();
        var paid_amount = $("#paid_amount").val();
        var due_amount = $("#due_amount").val();

        $.ajax({
            url: "update_purchase.php",
            type: "POST",
            data: {
                purchase_id: purchase_id,
                paid_amount: paid_amount,
                due_amount: due_amount
            },
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
});
</script>
<script src="js/jquery-3.5.1.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // When the user clicks on the button, open the modal
    $(".edit-btn").on("click", function() {
        var purchase_id = $(this).data("purchase-id");
        var paid_amount = $(this).data("paid-amount");
        var due_amount = $(this).data("due-amount");
        var grand_total = $(this).data("grand-total");

        $("#purchase_id").val(purchase_id);
        $("#paid_amount").val(paid_amount);
        $("#due_amount").val(due_amount);
        $("#grand_total").val(grand_total);

        $("#editModal").css("display", "block");
    });

    // When the user clicks on <span> (x), close the modal
    $(".close").on("click", function() {
        $("#editModal").css("display", "none");
    });

    // When the user clicks anywhere outside of the modal, close it
    $(window).on("click", function(event) {
        if ($(event.target).is("#editModal")) {
            $("#editModal").css("display", "none");
        }
    });

    // Calculate the due amount when the paid amount changes
    $("#paid_amount").on("input", function() {
        var paid_amount = parseFloat($(this).val());
        var grand_total = parseFloat($("#grand_total").val());
        var due_amount = grand_total - paid_amount;
        $("#due_amount").val(due_amount.toFixed(2));
    });

    // When the user clicks on the save button, update the database
    $("#saveButton").on("click", function() {
        var purchase_id = $("#purchase_id").val();
        var paid_amount = $("#paid_amount").val();
        var due_amount = $("#due_amount").val();

        $.ajax({
            url: "update_purchase.php",
            type: "POST",
            data: {
                purchase_id: purchase_id,
                paid_amount: paid_amount,
                due_amount: due_amount
            },
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
});
</script>

    <script src="script.js"></script>

    <script src="assets/bootstrap/jquery/jquery.min.js"></script>
    <!-- Popper JS -->
    <script src="assets/bootstrap/popper/popper.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
  // JavaScript function to close the booking form
  function closeBookingForm() {
    document.getElementById('booking-popup').style.display = 'none';
  }

  // Other JavaScript functions...
</script>
<script>
function printInvoice(event) {
    event.preventDefault(); // Prevent the default link behavior (opening a new tab/window)

    // Create a hidden iframe
    var iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = event.currentTarget.href; // Load generate_invoice2.php in the iframe

    // Append the iframe to the document body
    document.body.appendChild(iframe);

    // After the iframe has loaded, trigger print
    iframe.onload = function() {
        iframe.contentWindow.print(); // Trigger print dialog for the iframe content
        setTimeout(function() {
            document.body.removeChild(iframe); // Remove the iframe after printing
        }, 1000); // Adjust the timeout as needed to ensure the iframe is removed after printing
    };
}
</script>
     <script src="assets/script.js"></script>       
<script>
    function calculateTotalAmount() {
        var rate = parseFloat(document.getElementById('rate').value);
        var quantity = parseFloat(document.getElementById('quantity').value);
        var totalAmountField = document.getElementById('totalAmount');

        if (!isNaN(rate) && !isNaN(quantity)) {
            var totalAmount = rate * quantity;
            totalAmountField.value = isNaN(totalAmount) ? '' : totalAmount.toFixed(2);
        }
    }

    // Function to calculate grand total
    function calculateGrandTotal() {
        var table = document.getElementById('show');
        var rows = table.getElementsByTagName('tr');
        var grandTotal = 0;

        for (var i = 1; i < rows.length; i++) {
            var cells = rows[i].getElementsByTagName('td');

            // Check if cells are defined and have the expected length
            if (cells && cells.length >= 4) { // Assuming "Total Amount" is in the 4th column (index 3)
                var totalAmount = parseFloat(cells[3].innerHTML);

                if (!isNaN(totalAmount)) {
                    grandTotal += totalAmount;
                }
            }
        }

        document.getElementById('grandTotal').value = grandTotal.toFixed(2);
    }

    // Event listeners for changes in rate and received quantity
    document.getElementById('rate').addEventListener('input', calculateTotalAmount);
    document.getElementById('quantity').addEventListener('input', calculateTotalAmount);

    // Event listener for the "Add Product" button
    document.getElementById('addProduct').addEventListener('click', function() {
        calculateGrandTotal();
    });

    // Event listener for "Add" button in the popup
    document.getElementById('btn').addEventListener('click', function() {
        calculateGrandTotal();
    });

    // ... (your existing code) ...
</script>

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script>
  document.getElementById("cylindertype").value = "Empty";

 
  document.addEventListener("DOMContentLoaded", function() {
      document.getElementById("cylinderType").value = "Empty";
      checkRefreshCount();
      populateTableFromStorage();
    });

    var refreshCount = sessionStorage.getItem('refreshCount') || 0;

  function AddRowAndClosePopup() {
    var productName = document.getElementById("productName").value;
    var rate = document.getElementById("rate").value;
    var quantity = document.getElementById("quantity").value;
    var totalAmount = document.getElementById("totalAmount").value;

    var table = document.getElementById("show");
    var newRow = table.insertRow(-1);

    var cellProductName = newRow.insertCell(0);
    var cellRate = newRow.insertCell(1);
    var cellQuantity = newRow.insertCell(2);
    var cellTotalAmount = newRow.insertCell(3);

    cellProductName.innerHTML = productName;
    cellRate.innerHTML = rate;
    cellQuantity.innerHTML = quantity;
    cellTotalAmount.innerHTML = totalAmount;

    var hiddenProductNameInput = document.createElement("input");
    hiddenProductNameInput.type = "hidden";
    hiddenProductNameInput.name = "productName[]"; // Use an array for productName
    hiddenProductNameInput.value = productName;
    newRow.appendChild(hiddenProductNameInput);

    var hiddenQuantityInput = document.createElement("input");
    hiddenQuantityInput.type = "hidden";
    hiddenQuantityInput.name = "quantity[]"; // Use an array for quantity
    hiddenQuantityInput.value = quantity;
    newRow.appendChild(hiddenQuantityInput);

    var hiddenRateInput = document.createElement("input");
    hiddenRateInput.type = "hidden";
    hiddenRateInput.name = "rate[]"; // Use an array for quantity
    hiddenRateInput.value = rate;
    newRow.appendChild(hiddenRateInput);

    var hiddenTotalAmountInput = document.createElement("input");
    hiddenTotalAmountInput.type = "hidden";
    hiddenTotalAmountInput.name = "totalAmount[]"; // Use an array for totalAmount
    hiddenTotalAmountInput.value = totalAmount;
    newRow.appendChild(hiddenTotalAmountInput);

    document.getElementById("productName").value = "";
    document.getElementById("rate").value = "";
    document.getElementById("quantity").value = "";
    document.getElementById("totalAmount").value = "";

    calculateGrandTotal();
    checkRefreshCount();
    populateTableFromStorage();
    closeNestedPopup();
}
 


// Function to hide the popup
function hidePopup() {
    console.log("Hiding popup");
    document.getElementById("booking-popup").style.display = "none";

    // Close the popup window (if it was opened from another window)
    if (window.opener && !window.opener.closed) {
        console.log("Reloading parent window");
        window.opener.location.reload(); // Reload the parent window if needed
        console.log("Closing popup window");
        window.close(); // Close the popup window
    } else {
        console.log("Popup window not opened from another window");
    }
}

 

  

  function checkRefreshCount() {
    refreshCount++;
    sessionStorage.setItem('refreshCount', refreshCount);

    if (refreshCount >= 2) {
      sessionStorage.removeItem('tableData');
      sessionStorage.removeItem('refreshCount');
    }
  }

  function populateTableFromStorage() {
    var storedData = sessionStorage.getItem('tableData');
    if (storedData) {
      document.getElementById("show").innerHTML = storedData;
    }
  }
</script>

  <script>
// Event listener for "Okay" button click

    function showPopup() {
      document.getElementById("booking-popup").style.display = "flex";
    }
 
    // Add other JavaScript functions as needed
  </script>


<script>
  // Function to calculate the due amount
function calculateDueAmount() {
    var grandTotal = parseFloat(document.getElementById('grandTotal').value);
    var paidAmount = parseFloat(document.getElementById('paidAmount').value);
    var dueAmountField = document.getElementById('dueAmount');

    if (!isNaN(grandTotal) && !isNaN(paidAmount)) {
        var dueAmount = grandTotal - paidAmount;
        dueAmountField.value = isNaN(dueAmount) ? '' : dueAmount.toFixed(2);
    }
}

// Event listener for changes in the paid amount field
document.getElementById('paidAmount').addEventListener('input', calculateDueAmount);

</script>
<script>
  // Get the main button, main popup, nested popup button, and nested popup
  var mainButton = document.getElementById("mainButton");
  var mainPopup = document.getElementById("mainPopup");
  var nestedPopupButton = document.getElementById("nestedPopupButton");
  var nestedPopup = document.getElementById("nestedPopup");

  // Function to open the main popup
  function openMainPopup() {
    mainPopup.style.display = "block";
    document.body.classList.add("overlay");
  }

  // Function to close the main popup
  function closeMainPopup() {
    mainPopup.style.display = "none";
    document.body.classList.remove("overlay");
  }

  // Function to open the nested popup
  function openNestedPopup() {
    nestedPopup.style.display = "block";
  }

  // Function to close the nested popup
  function closeNestedPopup() {
    nestedPopup.style.display = "none";
  }

  // Event listener for main button click
  mainButton.addEventListener("click", openMainPopup);
</script>

</body>
</html>
