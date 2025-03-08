<?php
session_start();
include 'loader.html';
// Include database configuration
@include 'db/config.php';

// Check if sale_id is passed via GET
if (isset($_GET['sale_id'])) {
    $sale_id = $_GET['sale_id'];

    // Fetch sale details along with customer name, vehicle name, and vehicle expense based on sale_id
    $saleQuery = "
    SELECT s.sale_id, s.invoice_date, s.product_name, s.sale_quantity, s.sale_price, 
           s.total_amount, s.grand_total, s.paid_amount, s.due_amount, 
           s.payment_type, s.discount, c.customer_name, s.vehicle_name, s.vehicle_expense
    FROM sale s 
    JOIN customers c ON s.customer_id = c.customer_id 
    WHERE s.sale_id = ?";
   
    $stmt = $conn->prepare($saleQuery);
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $saleData = $result->fetch_assoc();

    if ($saleData) {
        // Decode product_name and quantities from JSON format
        $productNames = json_decode($saleData['product_name'], true);
        $quantities = json_decode($saleData['sale_quantity'], true);
        $prices = json_decode($saleData['sale_price'], true);

        // Fetch stock quantities for specific products
        $stockQuery = "
        SELECT name, total_quantity 
        FROM stock 
        WHERE name IN (" . implode(',', array_fill(0, count($productNames), '?')) . ")";
        
        $stockStmt = $conn->prepare($stockQuery);
        $stockStmt->bind_param(str_repeat('s', count($productNames)), ...$productNames);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();

        $stockData = [];
        while ($row = $stockResult->fetch_assoc()) {
            $stockData[$row['name']] = $row['total_quantity'];
        }

        // Now you have the stock quantities in $stockData, keyed by product name
        // You can access it like this:
        foreach ($productNames as $index => $productName) {
            $quantity = $quantities[$index];
            $price = $prices[$index];
            // Get the stock for the specific product
            $totalStock = isset($stockData[$productName]) ? $stockData[$productName] : 0;
        
            // Output the details
          
        }
        
    } else {
        echo "No data found for the selected sale.";
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Exchange Form</title>
 
</head>

<style>



body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="date"], input[type="number"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Popup Styles */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 4px;
            width: 400px;
        }

        .close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
        }

      
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="date"], input[type="number"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
   
<body>
    <h2>Return Exchange Form</h2>
    <form id="purchaseForm" method="POST" action="update_sale.php">

  
        <div class="form-group">
            <label for="invoiceDate">Invoice Date:</label>
            <input type="date" id="invoiceDate" name="invoiceDate" value="<?php echo $saleData['invoice_date']; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="customerName">Customer Name:</label>
            <input type="text" id="customerName" name="customerName" value="<?php echo htmlspecialchars($saleData['customer_name']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="vehicleName">Vehicle Name:</label>
            <input type="text" id="vehicleName" name="vehicleName" value="<?php echo htmlspecialchars($saleData['vehicle_name']); ?>" >
        </div>
        <div class="form-group">
            <label for="vehicleExpense">Vehicle Expense:</label>
            <input type="text" id="vehicleExpense" name="vehicleExpense" value="<?php echo htmlspecialchars($saleData['vehicle_expense']); ?>" >
        </div>
        <button type="button" id="nestedPopupButton" class="btn" onclick="openNestedPopup()">Add Product</button>


<!-- Product Popup -->
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
        <input type="hidden" name="sale_id" value="<?php echo isset($saleData['sale_id']) ? htmlspecialchars($saleData['sale_id']) : ''; ?>">
        <!-- New field for Total Available Stock -->
        <div class="form-group">
            <label for="totalStock">Total Available Stock:</label>
            <input type="text" class="totalStock form-control" id="totalStock" name="totalStock[]" >
        </div>

        <div class="form-group">
            <label for="rate">Unit Price:</label>
            <input type="text" class="rate form-control" id="rate" name="rate[]">
        </div>
        
        <div class="form-group">
    <label for="quantity">Sale Quantity:</label>
    <input type="number" step="0.01" name="quantity[]" id="quantity" class="quantity form-control" oninput="calculateTotalAmount()">
    <span id="quantityError" style="color:red; display:none;">Quantity exceeds available stock!</span>
</div>

        <div class="form-group">
            <label for="totalAmount">Total Amount:</label>
            <input type="text" class="totalAmount form-control" id="totalAmount"  name="totalAmount[]">
        </div>
        
        <button type="button" id="btn" onclick="AddRowAndClosePopup()" class="btn">Add</button>
    </div>
</div>
<table id="productTable">
    <tr>
        <th>Product Name</th>
        <th>Unit Price</th>
        <th>Received Quantity</th>
        <th>Total Amount</th>
        <th>Action</th>
            <th>Available Stock</th>
    </tr>

    <input type="hidden" id="productData" name="productData">
    <?php
$i = 0;
$displayedProducts = array(); // Array to keep track of displayed products

// Ensure $productNames, $quantities, and $prices are arrays
$productNames = is_array($productNames) ? $productNames : json_decode($productNames, true);
$quantities = is_array($quantities) ? $quantities : json_decode($quantities, true);
$prices = is_array($prices) ? $prices : json_decode($prices, true);

// Check if all arrays have the same length
if (count($productNames) !== count($quantities) || count($productNames) !== count($prices)) {
    echo "Error: Mismatch in product data arrays.";
    return;
}

foreach ($productNames as $index => $productName) {
    $quantity = $quantities[$index];
    $price = $prices[$index];
    // Get the stock for the specific product
    $totalStock = isset($stockData[$productName]) ? $stockData[$productName] : 0;
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($productName) . "</td>";
    echo "<td id='price_$i'>" . htmlspecialchars($price) . "</td>";
    echo "<td id='quantity_$i'>" . htmlspecialchars($quantity) . "</td>";
    echo "<td id='total_$i'>" . htmlspecialchars($price * $quantity) . "</td>";
    echo "<td>
        <input type='number' id='action_quantity_$i' min='1' placeholder='Enter quantity'>
        <button type='button' onclick='confirmAction(\"" . htmlspecialchars($productName) . "\", $i, \"decrement\")'>-</button>
        <button type='button' onclick='confirmAction(\"" . htmlspecialchars($productName) . "\", $i, \"increment\")'>+</button>
        <button type='button' onclick='confirmDelete(\"" . htmlspecialchars($productName) . "\", $i)'>Delete</button>
    </td>";
    echo "<td>" . htmlspecialchars($totalStock) . "</td>";
    echo "</tr>";
    
    $i++;
}
?>
</table>
        <div class="form-group">
            <label for="netTotal">Net Total:</label>
            <input type="text" id="netTotal" name="netTotal"  readonly>
        </div>
        <div class="form-group">
            <label for="discount">Discount:</label>
           <input type="text" id="discount" name="discount" value="<?php echo htmlspecialchars($saleData['discount'] ?? ''); ?>">

        </div>
        <div class="form-group">
            <label for="grandTotal">Grand Total (After Discount):</label>
            <input type="text" id="grandTotal" name="grandTotal" value="<?php echo htmlspecialchars($saleData['grand_total']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="paidAmount">Paid Amount:</label>
            <input type="text" id="paidAmount" name="paidAmount" value="<?php echo htmlspecialchars($saleData['paid_amount']); ?>" >
        </div>
        <div class="form-group">
            <label for="dueAmount">Due Amount:</label>
            <input type="text" id="dueAmount" name="dueAmount" value="<?php echo htmlspecialchars($saleData['due_amount']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="paymentType">Payment Type:</label>
            <select id="paymentType" name="paymentType">
                <option value="Cash" <?php echo $saleData['payment_type'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                <option value="Credit Card" <?php echo $saleData['payment_type'] == 'Credit Card' ? 'selected' : ''; ?>>Credit Card</option>
            </select>
        </div>
        <button type="submit" name="submit" class="btn">Submit Return/Exchange</button>
    </form>

    <script>
       function openNestedPopup() {
        document.getElementById("nestedPopup").style.display = "flex";
    }

    function closeNestedPopup() {
        document.getElementById("nestedPopup").style.display = "none";
        resetNestedFormFields();
    }

    function calculateTotalAmount() {
        const rate = parseFloat(document.getElementById("rate").value) || 0;
        const quantity = parseFloat(document.getElementById("quantity").value) || 0;
        const total = rate * quantity;
        document.getElementById("totalAmount").value = total.toFixed(2);
    }


    function updateStockOnAdd(productName, quantity) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_stock_add.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                alert("Stock updated successfully.");
            } else {
                console.error("Error updating stock:", xhr.statusText);
                alert("Failed to update stock. Please try again.");
            }
        }
    };

    // Send product name and quantity to the update_stock.php for stock update
    xhr.send(`productName=${encodeURIComponent(productName)}&quantity=${quantity}`);
}
function AddRowAndClosePopup() {
    console.log("AddRowAndClosePopup function called");

    const productName = document.getElementById("productName").value;
    const rate = parseFloat(document.getElementById("rate").value) || 0;
    const quantity = parseFloat(document.getElementById("quantity").value) || 0;
    const totalAmount = (rate * quantity).toFixed(2);
    const totalStock = parseFloat(document.getElementById("totalStock").value) || 0;

    // Show the "Are you OK?" alert
    const confirmation = confirm("Are you OK?");

    if (confirmation) {
        // Validate inputs
        if (!productName || quantity <= 0 || quantity > totalStock) {
            alert("Please fill in all fields correctly.");
            return;
        }

        // Update stock in the database (replace this with your logic)
        updateStockOnAdd(productName, quantity);

        // Get the table and insert a new row
        const table = document.getElementById("productTable");
        const newRow = table.insertRow(-1);
        
        const cell1 = newRow.insertCell(0);
        const cell2 = newRow.insertCell(1);
        const cell3 = newRow.insertCell(2);
        const cell4 = newRow.insertCell(3);
        const cell5 = newRow.insertCell(4);

        // Add data to the new row
        cell1.innerHTML = productName;
        cell2.innerHTML = rate.toFixed(2);
        cell3.innerHTML = quantity.toFixed(2);
        cell4.innerHTML = totalAmount;
        cell5.innerHTML = "<button onclick='deleteRow(this)'>Delete</button>";

        // Close the popup
        closeNestedPopup();

        // Recalculate and update the net total
        recalculateNetTotal();

        // Reset form fields after adding the row
        resetNestedFormFields();
    
    } else {
        // Handle cancel action
        alert("Operation canceled.");
    }
}

// Function to recalculate the net total based on all rows' total amounts
// Recalculate the net total, including the vehicle expense
function recalculateNetTotal() {
    let netTotal = 0;
    const table = document.getElementById("productTable");

    // Loop through all rows and add up the total amounts in the table
    for (let i = 1; i < table.rows.length; i++) {
        const rowTotal = parseFloat(table.rows[i].cells[3].innerText) || 0;
        netTotal += rowTotal;
    }

    // Get the value from the Vehicle Expense field and add it to the net total
    const vehicleExpense = parseFloat(document.getElementById("vehicleExpense").value) || 0;
    netTotal += vehicleExpense;

    // Update the net total field
    document.getElementById("netTotal").value = netTotal.toFixed(2);

    // Optionally recalculate the grand total and due amount
    recalculateGrandTotal();
}

// Event listener to recalculate net total when the Vehicle Expense changes
document.getElementById("vehicleExpense").addEventListener("input", function() {
    recalculateNetTotal();
});

// Optional: Function to delete a row and recalculate the net total after deletion
function deleteRow(button) {
    const row = button.parentNode.parentNode;
    row.parentNode.removeChild(row);

    // Recalculate the net total after row deletion
    recalculateNetTotal();
}

function resetNestedFormFields() {
    document.getElementById("productName").selectedIndex = 0;
    document.getElementById("totalStock").value = '';
    document.getElementById("rate").value = '';
    document.getElementById("quantity").value = '';
    document.getElementById("totalAmount").value = '';
    document.getElementById("barcode").value = ''; // Reset barcode field
}



    function updateNetTotal(newAmount) {
        const netTotalField = document.getElementById("netTotal");
        const currentNetTotal = parseFloat(netTotalField.value) || 0;
        const updatedNetTotal = currentNetTotal + newAmount;
        netTotalField.value = updatedNetTotal.toFixed(2);
    }

    function deleteRow(button) {
        const row = button.parentNode.parentNode;
        const totalAmount = parseFloat(row.cells[3].innerHTML) || 0;

        // Remove row
        row.remove();

        // Update net total
        updateNetTotal(-totalAmount);
    }

      

        function fetchProductByBarcode() {
    const barcode = document.getElementById('barcode').value;
    if (barcode.length > 0) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'Action/Fetch/fetch_product_saledata.php', true);
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
                    document.getElementById('rate').value = response.unit_price;
                    // Set the total available stock in the input field
                    document.getElementById('totalStock').value = response.available_stock;

                    // Set the price in the input field
                    document.getElementById('productPrice').value = response.price;

                } else {
                    document.getElementById('productName').selectedIndex = 0;
                    document.getElementById('totalStock').value = '';  // Clear total stock
                    
                    document.getElementById('rate').value = ''; // Clear price
                }
            }
        };
        xhr.send('barcode=' + barcode);
    } else {
        // Clear the fields if barcode is empty
        document.getElementById('productName').selectedIndex = 0;
        document.getElementById('totalStock').value = '';  // Clear total stock
        document.getElementById('rate').value = '';  // Clear price
    }
}

    </script>




<script>
     function confirmAction(productName, index, action) {
    const quantityInput = document.getElementById("action_quantity_" + index);
    const enteredQuantity = parseFloat(quantityInput.value) || 0;
    const currentQuantity = parseFloat(document.getElementById("quantity_" + index).innerText) || 0;

    let newQuantity;

    if (action === "increment") {
        newQuantity = currentQuantity + enteredQuantity;
    } else if (action === "decrement") {
        newQuantity = currentQuantity - enteredQuantity;
    }

    if (newQuantity < 0) {
        alert("Quantity cannot be negative.");
        return;
    }

    if (confirm(`Are you sure you want to ${action} the quantity by ${enteredQuantity} for ${productName}?`)) {
        const xhrStockUpdate = new XMLHttpRequest();
        xhrStockUpdate.open("POST", "update_stock.php", true);
        xhrStockUpdate.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhrStockUpdate.onreadystatechange = function() {
            if (xhrStockUpdate.readyState === 4) {
                if (xhrStockUpdate.status === 200) {
                    alert(xhrStockUpdate.responseText); // Stock update success

                    // Update quantity on the table
                    document.getElementById("quantity_" + index).innerText = newQuantity.toFixed(2);

                    // Update total amount for the row
                    updateRowTotal(index);
                } else {
                    console.error("Error updating stock:", xhrStockUpdate.statusText);
                    alert("Failed to update stock. Please try again.");
                }
            }
        };

        xhrStockUpdate.send(`productName=${encodeURIComponent(productName)}&newQuantity=${newQuantity}&saleQuantity=${encodeURIComponent(currentQuantity)}`);
    }
}

function updateRowTotal(index) {
    // Get price and quantity
    const price = parseFloat(document.getElementById("price_" + index).innerText) || 0;
    const quantity = parseFloat(document.getElementById("quantity_" + index).innerText) || 0;

    // Calculate total for the row
    const rowTotal = price * quantity;

    // Update total amount in the row
    document.getElementById("total_" + index).innerText = rowTotal.toFixed(2);

    // Update the net total
    recalculateNetTotal();
}


// Function to recalculate grand total (Net Total - Discount)
function recalculateGrandTotal() {
    const netTotal = parseFloat(document.getElementById("netTotal").value) || 0;
    const discount = parseFloat(document.getElementById("discount").value) || 0;

    // Calculate the grand total after applying discount
    const grandTotal = netTotal - discount;
    document.getElementById("grandTotal").value = grandTotal.toFixed(2);

    // Recalculate due amount after paid amount
    recalculateDueAmount();
}

// Function to recalculate due amount (Grand Total - Paid Amount)
function recalculateDueAmount() {
    const grandTotal = parseFloat(document.getElementById("grandTotal").value) || 0;
    const paidAmount = parseFloat(document.getElementById("paidAmount").value) || 0;

    // Calculate the due amount
    const dueAmount = grandTotal - paidAmount;
    document.getElementById("dueAmount").value = dueAmount.toFixed(2);
}

// Add event listeners to update values when discount or paid amount is entered
document.getElementById("discount").addEventListener("input", function() {
    recalculateGrandTotal();
});

document.getElementById("paidAmount").addEventListener("input", function() {
    recalculateDueAmount();
});

// Ensure vehicle expense also triggers recalculation of totals


// Add event listener to vehicle expense field to update net total when changed


function confirmDelete(productName, index) {
    if (confirm("Are you sure you want to delete " + productName + "?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_product.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById("productTable").deleteRow(index + 1); // Adjust for header row
                recalculateNetTotal(); // Update totals after deletion
            }
        };
        xhr.send("productName=" + encodeURIComponent(productName) + "&quantity=" + encodeURIComponent(document.getElementById("quantity_" + index).innerText));
    }
}
function updateTotals() {
    let total = 0;
    const table = document.getElementById("productTable");
    for (let i = 1; i < table.rows.length; i++) {
        const price = parseFloat(table.rows[i].cells[1].innerText); // Ensure price is a float
        const quantity = parseFloat(document.getElementById("quantity_" + (i - 1)).innerText); // Use parseFloat for quantity
        total += price * quantity;
    }
    document.getElementById("netTotal").value = total.toFixed(2); // Format total with two decimal places
}

  </script>
 <script>
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Prepare product data from the table
    const table = document.getElementById("productTable");
    const productNames = [];
    const unitPrices = [];
    const quantities = [];
    const totalAmounts = [];

    for (let i = 1; i < table.rows.length; i++) {
        const row = table.rows[i];
        productNames.push(row.cells[0].innerText);
        unitPrices.push(parseFloat(row.cells[1].innerText));
        quantities.push(parseFloat(row.cells[2].innerText));
        totalAmounts.push(parseFloat(row.cells[3].innerText));
    }

    const productData = {
        productNames: productNames,
        unitPrices: unitPrices,
        quantities: quantities,
        totalAmounts: totalAmounts
    };

    // Set the prepared product data to the hidden input
    document.getElementById("productData").value = JSON.stringify(productData);

    // Use AJAX to submit the form
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_sale.php", true);
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // If the form was submitted successfully, show the success message
                alert("Operation done successfully!");
                
                // Redirect to sale.php
                window.location.href = "sale.php";
            } else {
                console.error("Error submitting form:", xhr.statusText);
                alert("Error submitting form. Please try again.");
            }
        }
    };
    
    // Create FormData object
    const formData = new FormData(this);
    
    // Append the product data as a JSON string
    formData.append('productData', JSON.stringify(productData));
    
    // Send the form data
    xhr.send(formData);
});
</script>

</body>
</html>
