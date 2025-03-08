<?php
session_start();
ob_start();
include 'loader.html';
@include "db/config.php"; // Include the database connection

if (isset($_POST['submit'])) {
    // Get all the values from the form
    $invoiceDate = $_POST['invoiceDate'] ?? null;
    $supplier = $_POST['vendor'] ?? null;
    $vehicle = $_POST['vehicle'] ?? null;
    $netTotal = floatval($_POST['netTotal'] ?? 0);
    $grandTotal = floatval($_POST['grandTotal'] ?? 0);
    $paidAmount = floatval($_POST['paidAmount'] ?? 0);
    $dueAmount = floatval($_POST['dueAmount'] ?? 0);
    $vehicleExpense = floatval($_POST['vehicleExpense'] ?? 0);
    $paymentType = $_POST['paymentType'] ?? null;

    // Convert arrays to floats
    $productNames = $_POST['productName'] ?? [];
    $receivedQuantities = array_map('floatval', $_POST['quantity'] ?? []);
    $purchasingPrices = array_map('floatval', $_POST['rate'] ?? []);
    $totalAmounts = array_map('floatval', $_POST['totalAmount'] ?? []);

    // Assign the results of json_encode to variables
    $productNamesJson = json_encode($productNames);
    $receivedQuantitiesJson = json_encode($receivedQuantities);
    $purchasingPricesJson = json_encode($purchasingPrices);
    $totalAmountsJson = json_encode($totalAmounts);

    // Check if product arrays are not empty and validate required fields
    if (!empty($productNames) && count($productNames) > 0 && !empty($productNames[0])) {
        try {
            // SQL query using placeholders for the non-JSON fields
            $sql = "INSERT INTO purchase (invoice_date, supplier_id, vehicle_name, net_total, grand_total, paid_amount, due_amount, payment_type, product_name, received_quantity, purchasing_price, total_amount, vehicle_expense) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Prepare and bind the statement for non-JSON values
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssddddsssssd", 
                $invoiceDate, 
                $supplier, 
                $vehicle, 
                $netTotal, 
                $grandTotal, 
                $paidAmount, 
                $dueAmount, 
                $paymentType,
                $productNamesJson,
                $receivedQuantitiesJson,
                $purchasingPricesJson,
                $totalAmountsJson,
                $vehicleExpense
            );

            $stmt->execute();
            echo "Purchase record inserted successfully!";
            
            foreach ($productNames as $index => $productName) {
                $quantity = $receivedQuantities[$index] ?? 0;  // Handle null or missing quantity by setting it to 0
            
                // Fetch current quantity from stock table
                $fetchQuantityQuery = "SELECT total_quantity FROM stock WHERE name = ?";
                $stmtFetchQuantity = $conn->prepare($fetchQuantityQuery);
                $stmtFetchQuantity->bind_param("s", $productName);
                $stmtFetchQuantity->execute();
                $stmtFetchQuantity->bind_result($currentQuantity);
                $stmtFetchQuantity->fetch();
                $stmtFetchQuantity->close();
            
                if ($currentQuantity !== null) {
                    // Product exists, update the total_quantity
                    $newQuantity = $currentQuantity + $quantity;
                    $updateQuantityQuery = "UPDATE stock SET total_quantity = ? WHERE name = ?";
                    $stmtUpdateQuantity = $conn->prepare($updateQuantityQuery);
                    $stmtUpdateQuantity->bind_param("is", $newQuantity, $productName);
                    $stmtUpdateQuantity->execute();
                    $stmtUpdateQuantity->close();
                } else {
                    // Product doesn't exist, check for product ID before inserting
                    $fetchProductIdQuery = "SELECT id FROM products WHERE product_name = ?";
                    $stmtFetchProductId = $conn->prepare($fetchProductIdQuery);
                    $stmtFetchProductId->bind_param("s", $productName);
                    $stmtFetchProductId->execute();
                    $stmtFetchProductId->bind_result($productId);
                    $stmtFetchProductId->fetch();
                    $stmtFetchProductId->close();
            
                    if ($productId !== null) {
                        // Insert new stock record with default sold_quantity = 0
                        $insertQuantityQuery = "INSERT INTO stock (product_id, name, total_quantity, sold_quantity) VALUES (?, ?, ?, 0)";
                        $stmtInsertQuantity = $conn->prepare($insertQuantityQuery);
                        $stmtInsertQuantity->bind_param("isi", $productId, $productName, $quantity);
                        $stmtInsertQuantity->execute();
                        $stmtInsertQuantity->close();
                    } else {
                        echo "Error: Product ID not found for $productName. Cannot insert into stock.";
                    }
                }
            }
            

            header("Location: purchase.php");
            exit();

        } catch (mysqli_sql_exception $e) {
            echo "Error inserting purchase record: " . $e->getMessage();
        }
    } else {
        echo "Error: Required fields are missing.";
    }
}
ob_end_flush();
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
    <link rel="stylesheet" href="assets/purchase.css">
</head>
    <title>Dani's Fabric</title>
    

<body>
<?php include 'navbar.php'; ?>
<main class="main-content">
<?php include 'topbar.php'; ?>
<button id="mainButton" class="btn">Add Purchase</button>
<br><br>
<div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Invoice Date</th>
                    <th>Supplier Name</th>
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
                        // Output table data
                        while ($purchase = $purchaseResult->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='step-number color-1' data-label='Invoice Date'>" . $purchase['invoice_date'] . "</td>";
                            echo "<td data-label='Supplier Name'>" . $purchase['supplier_name'] . "</td>";
                            echo "<td data-label='Product Name'>" . str_replace(array('[', ']', '"'), '', $purchase["product_name"]) . "</td>";
                            echo "<td data-label='Received Quantity'>" . str_replace(array('[', ']', '"'), '', $purchase["received_quantity"]) . "</td>";
                            echo "<td data-label='Purchasing Price'>" . str_replace(array('[', ']', '"'), '', $purchase["purchasing_price"]) . "</td>";
                            echo "<td data-label='Total Amount'>" . str_replace(array('[', ']', '"'), '', $purchase["total_amount"]) . "</td>";
                            echo "<td data-label='Grand Total'>" . $purchase['grand_total'] . "</td>";
                            echo "<td data-label='Paid Amount'>" . $purchase['paid_amount'] . "</td>";
                            echo "<td data-label='Due Amount'>" . $purchase['due_amount'] . "</td>";
                            echo "<td data-label='Payment Type'>" . $purchase['payment_type'] . "</td>";
                            echo "<td data-label='Action'>";
                            echo "<a href='#' class='edit-btn' data-purchase-id='{$purchase['purchase_id']}' data-paid-amount='{$purchase['paid_amount']}' data-due-amount='{$purchase['due_amount']}' data-grand-total='{$purchase['grand_total']}'>";
                            echo "<i class='bx bxs-edit bx-sm'></i></a>";
                            echo "<a href='generate_invoice2.php?purchase_id={$purchase['purchase_id']}' onclick='printInvoice(event)'>";
                            echo "<span class='blue'><i class='bx bx-printer bx-sm'></i></span>";
                            echo "</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11'>No data found</td></tr>";
                    }

                    // Free the result set
                    $purchaseResult->free_result();
                } else {
                    echo "<tr><td colspan='11'>Error fetching data: " . $conn->error . "</td></tr>";
                }

                // Close the database connection
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
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
    <div class="container">
   
        <div id="mainPopup" class="popup">
    <div class="popup-content">
        <span class="close" onclick="closeMainPopup()">&times;</span>
        <h2>Purchase Form</h2>
        <form id="purchaseForm" method="POST" action="">
            <div class="form-group">
                <label for="invoiceDate">Date:</label>
                <input type="date" id="invoiceDate" name="invoiceDate" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="vendor">Supplier:</label>
                <select id="vendor" class="form-control" name="vendor" style="width: 100%;">
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

            <button type="button" id="nestedPopupButton" class="btn" onclick="openNestedPopup()">Add Product</button>
            
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
                <label for="vehicle">Vehicle:</label>
                <select id="vehicle" class="form-control" name="vehicle" style="width: 100%;" onchange="toggleVehicleExpense()">
                    <option value="">-- Select Vehicle --</option>
                    <?php
                    @include "db/config.php";
                    $vendorQuery = "SELECT id, car_name FROM vehicle";
                    $vendorResult = $conn->query($vendorQuery);
                    while ($vendor = $vendorResult->fetch_assoc()) {
                        echo "<option value='{$vendor['id']}'>{$vendor['car_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Vehicle Expense Field (Initially Hidden) -->
            <div class="form-group" id="vehicleExpenseField" style="display: none;">
                <label for="vehicleExpense">Vehicle Expense:</label>
                <input type="text" id="vehicleExpense" name="vehicleExpense">
            </div>

            <div class="form-group">
                <label for="netTotal">Net Total:</label>
                <input type="text" id="netTotal" name="netTotal" readonly>
            </div>

            <div class="form-group">
                <label for="discount">Discount:</label>
                <input type="text" id="discount" name="discount" oninput="applyDiscount()">
            </div>

            <div class="form-group">
                <label for="grandTotal">Grand Total(Price After Discount):</label>
                <input type="text" id="grandTotal" name="grandTotal" readonly>
            </div>

            <div class="form-group">
                <label for="paidAmount">Paid Amount:</label>
                <input type="text" id="paidAmount" name="paidAmount" oninput="calculateDueAmount()">
            </div>

            <div class="form-group">
                <label for="dueAmount">Due Amount:</label>
                <input type="text" id="dueAmount" name="dueAmount" readonly>
            </div>

            <div class="form-group">
                <label for="paymentType">Payment Type:</label>
                <select id="paymentType" name="paymentType">
                    <option value="">Please select payment type</option>
                    <option value="Cash">Cash</option>
                    <option value="Credit Card">Credit Card</option>
                </select>
            </div>

            <button type="submit" name="submit" class="btn">Submit</button>
        </form>
    </div>
</div>




        <div id="nestedPopup" class="popup">
    <div class="popup-content">
        <span class="close" onclick="closeNestedPopup()">&times;</span>
        <h3>Add Product</h3>
        
       
        <div class="form-group">
            <label for="barcode">Barcode Number:</label>
            <input type="text" id="barcode" name="barcode" oninput="fetchProductByBarcode()">
        </div>
        
        <div class="form-group">
            <label for="productName">Product Name:</label>
            <select class="productName form-control" name="productName[]" id="productName">
                <option value="">Please select a product</option>
                <?php
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
        
        <button type="button" id="btn" onclick="AddRowAndClosePopup()" class="btn">Add</button>
    </div>
</div><!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Payment Details</h2>
        <form id="editForm">
            <input type="hidden" id="purchaseId" name="purchase_id">
            <div class="form-group">
                <label for="paidAmount">Paid Amount:</label>
                <input type="number" id="paidAmount" name="paid_amount" required>
            </div>
            <div class="form-group">
                <label for="dueAmount">Due Amount:</label>
                <input type="number" id="dueAmount" name="due_amount" required>
            </div>
            <button type="submit" class="update-btn">Update</button>
        </form>
    </div>
</div>

            </main>
            <script src="assets/script.js"></script>
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
            url: "Action/Update/update_purchase.php",
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
            url: "Action/Update/update_purchase.php",
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
<script>







function prepareFormData() {
    const table = document.getElementById('show'); // Get the table
    const rows = table.getElementsByTagName('tr'); // Get all rows
    const form = document.getElementById('purchaseForm'); // Get the form

    // Clear any previous hidden input fields for product data
    const existingProductFields = form.querySelectorAll('.product-data');
    existingProductFields.forEach(field => field.remove());

    // Loop through the rows to extract the product data
    for (let i = 1; i < rows.length; i++) { // Skip the first row (header)
        const cells = rows[i].getElementsByTagName('td');
        const productName = cells[0].innerText;
        const rate = cells[1].innerText;
        const quantity = cells[2].innerText;
        const totalAmount = cells[3].innerText;

        // Create hidden input fields for each product property
        const productNameInput = document.createElement('input');
        productNameInput.type = 'hidden';
        productNameInput.name = 'productName[]';
        productNameInput.value = productName;
        productNameInput.classList.add('product-data');
        form.appendChild(productNameInput);

        const rateInput = document.createElement('input');
        rateInput.type = 'hidden';
        rateInput.name = 'rate[]';
        rateInput.value = rate;
        rateInput.classList.add('product-data');
        form.appendChild(rateInput);

        const quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = 'quantity[]';
        quantityInput.value = quantity;
        quantityInput.classList.add('product-data');
        form.appendChild(quantityInput);

        const totalAmountInput = document.createElement('input');
        totalAmountInput.type = 'hidden';
        totalAmountInput.name = 'totalAmount[]';
        totalAmountInput.value = totalAmount;
        totalAmountInput.classList.add('product-data');
        form.appendChild(totalAmountInput);
    }
}

// Attach the function to the form submission event
document.getElementById('purchaseForm').addEventListener('submit', function (event) {
    prepareFormData(); // Call the function to prepare data before submission
});
</script>

<script>


    function toggleVehicleExpense() {
        var vehicleSelect = document.getElementById("vehicle");
        var vehicleExpenseField = document.getElementById("vehicleExpenseField");

        // Show the vehicle expense field if a vehicle is selected, otherwise hide it
        if (vehicleSelect.value !== "") {
            vehicleExpenseField.style.display = "block";
        } else {
            vehicleExpenseField.style.display = "none";
        }
    }
</script>
<script>
   function fetchProductByBarcode() {
    const barcode = document.getElementById('barcode').value;
    if (barcode.length > 0) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'fetch_product_data.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status === 200) {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    // Select the product from the dropdown by matching the name
                    const productNameDropdown = document.getElementById('productName');
                    const options = productNameDropdown.options;
                    
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value === response.product_name) {
                            productNameDropdown.selectedIndex = i;
                            break;
                        }
                    }

                   

                } else {
               
                    document.getElementById('productName').selectedIndex = 0;
               
                }
            }
        };
        xhr.send('barcode=' + barcode);
    } else {
        // Clear the fields if barcode is empty
        document.getElementById('productName').selectedIndex = 0;
        // document.getElementById('rate').value = ''; // Optional: clear rate
    }
}

</script>


    <script>
        $(document).ready(function() {
            $('#vendor').select2({
                placeholder: 'Search for a supplier',
                allowClear: true
            });
        });
    </script>
  
    <script>
        var mainButton = document.getElementById("mainButton");
        var mainPopup = document.getElementById("mainPopup");
        var nestedPopup = document.getElementById("nestedPopup");

        function openMainPopup() {
            mainPopup.style.display = "block";
        }

        function closeMainPopup() {
            mainPopup.style.display = "none";
        }

        function openNestedPopup() {
            nestedPopup.style.display = "block";
        }

        function closeNestedPopup() {
            nestedPopup.style.display = "none";
        }

        function calculateTotalAmount() {
            var rate = parseFloat(document.getElementById('rate').value);
            var quantity = parseFloat(document.getElementById('quantity').value);
            var totalAmountField = document.getElementById('totalAmount');

            if (!isNaN(rate) && !isNaN(quantity)) {
                var totalAmount = rate * quantity;
                totalAmountField.value = isNaN(totalAmount) ? '' : totalAmount.toFixed(2);
            }
        }

        function calculateGrandTotal() {
    var table = document.getElementById('show');
    var rows = table.getElementsByTagName('tr');
    var grandTotal = 0;

    // Calculate the total from the product table
    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName('td');
        if (cells && cells.length >= 4) {
            var totalAmount = parseFloat(cells[3].innerHTML);
            if (!isNaN(totalAmount)) {
                grandTotal += totalAmount;
            }
        }
    }

    // Get the vehicle expense if it's visible and filled
    var vehicleExpenseField = document.getElementById('vehicleExpense');
    var vehicleExpense = parseFloat(vehicleExpenseField.value);
    if (!isNaN(vehicleExpense)) {
        grandTotal += vehicleExpense; // Add vehicle expense to the total
    }

    // Update both Net Total and Grand Total initially
    document.getElementById('netTotal').value = grandTotal.toFixed(2);
    document.getElementById('grandTotal').value = grandTotal.toFixed(2);
}

function applyDiscount() {
    var netTotal = parseFloat(document.getElementById('netTotal').value);
    var discount = parseFloat(document.getElementById('discount').value);
    var grandTotalField = document.getElementById('grandTotal');

    // Apply the discount only to Grand Total
    var grandTotal = netTotal;

    if (!isNaN(discount)) {
        grandTotal -= discount;
    }

    grandTotalField.value = isNaN(grandTotal) ? netTotal.toFixed(2) : grandTotal.toFixed(2);
}

function calculateDueAmount() {
    var grandTotal = parseFloat(document.getElementById('grandTotal').value);
    var paidAmount = parseFloat(document.getElementById('paidAmount').value);
    var dueAmountField = document.getElementById('dueAmount');

    if (!isNaN(grandTotal) && !isNaN(paidAmount)) {
        var dueAmount = grandTotal - paidAmount;
        dueAmountField.value = isNaN(dueAmount) ? '' : dueAmount.toFixed(2);
    }
}

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

    document.getElementById("productName").value = "";
    document.getElementById("rate").value = "";
    document.getElementById("quantity").value = "";
    document.getElementById("totalAmount").value = "";

    calculateGrandTotal();
    closeNestedPopup();
}

// Event listener to recalculate grand total when vehicle expense changes
document.getElementById("vehicleExpense").addEventListener("input", calculateGrandTotal);

        mainButton.addEventListener("click", openMainPopup);
    </script>
</body>
</html>