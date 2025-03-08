<?php
// Start the session at the very beginning
session_start();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Forms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
        } */

        .button-container {
         
            text-align: center;
        }

        .main-button {
            padding: 15px 30px;
            margin: 10px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .main-button:hover {
            background-color: #45a049;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5vh auto;
            padding: 30px;
            width: 90%;
            max-width: 900px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close {
            float: right;
            cursor: pointer;
            font-size: 28px;
            font-weight: bold;
            color: #666;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin: 15px 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .items-table {
            width: 100%;
            /* margin-top: 20px; */
            border-collapse: collapse;
            background-color: white;
        }

        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 12px 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f8f8f8;
        }

        .items-table input, .items-table select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .add-item {
            margin: 15px 0;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-item:hover {
            background-color: #45a049;
        }

        .remove-row {
            background-color: #ff4444;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
        }

        .remove-row:hover {
            background-color: #cc0000;
        }

        .totals-section {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f8f8;
            border-radius: 5px;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        @media screen and (max-width: 768px) {
            .modal-content {
                width: 95%;
                padding: 15px;
                margin: 2vh auto;
            }

            .items-table {
                display: block;
                overflow-x: auto;
            }

            .form-group {
                margin: 10px 0;
            }

            .totals-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php
include 'navbar.php'; // Include before any HTML or output
?>
<main class="main-content">
<?php include 'topbar.php'; ?>
    <div class="button-container">
        <button class="main-button" onclick="openModal('foodpanda')">Foodpanda</button>
        <button class="main-button" onclick="openModal('dastak')">Dastak</button>
        <button class="main-button" onclick="openModal('shopSale')">Shop Sale</button>
    </div>

    <!-- Foodpanda Modal -->
    <div id="foodpandaModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('foodpanda')">&times;</span>
            <h2>Foodpanda Order Form</h2>
            <form id="foodpandaForm">
                <div class="form-group">
                    <label>Order Code:</label>
                    <input type="text" name="orderCode" required>
                </div>
                <div class="form-group">
                    <label>Customer Number:</label>
                    <input type="text" name="customerNumber" required>
                </div>
                <div class="form-group">
                    <label>Contact Number:</label>
                    <input type="tel" name="contactNumber" required>
                </div>
                <button type="button" class="add-item" onclick="addItem('foodpanda')">+ Add Item</button>
                <table class="items-table" id="foodpandaItems">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="totals-section">
                    <div class="totals-grid">
                        <div class="form-group">
                            <label>Grand Total:</label>
                            <input type="number" id="foodpandaGrandTotalInput" readonly>
                        </div>
                        <div class="form-group">
                            <label>App Price:</label>
                            <input type="number" id="foodpandaAppPrice" readonly>
                        </div>
                        <div class="form-group">
                            <label>Paid Amount:</label>
                            <input type="number" id="foodpandaPaidAmount" oninput="updateDueAmount('foodpanda')">
                        </div>
                        <div class="form-group">
                            <label>Due Amount:</label>
                            <input type="number" id="foodpandaDueAmount" readonly>
                        </div>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Submit Order</button>
            </form>
        </div>
    </div>

    <!-- Dastak Modal -->
    <div id="dastakModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('dastak')">&times;</span>
            <h2>Dastak Order Form</h2>
            <form id="dastakForm">
                <div class="form-group">
                    <label>Order Code:</label>
                    <input type="text" name="orderCode" required>
                </div>
                <div class="form-group">
                    <label>Customer Number:</label>
                    <input type="text" name="customerNumber" required>
                </div>
                <div class="form-group">
                    <label>Contact Number:</label>
                    <input type="tel" name="contactNumber" required>
                </div>
                <button type="button" class="add-item" onclick="addItem('dastak')">+ Add Item</button>
                <table class="items-table" id="dastakItems">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="totals-section">
                    <div class="totals-grid">
                        <div class="form-group">
                            <label>Grand Total:</label>
                            <input type="number" id="dastakGrandTotalInput" readonly>
                        </div>
                          <div class="form-group">
                            <label>App Price:</label>
                            <input type="number" id="dastakaAppPrice" readonly>
                        </div>
                        <div class="form-group">
                            <label>Paid Amount:</label>
                            <input type="number" id="dastakPaidAmount" oninput="updateDueAmount('dastak')">
                        </div>
                        <div class="form-group">
                            <label>Due Amount:</label>
                            <input type="number" id="dastakDueAmount" readonly>
                        </div>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Submit Order</button>
            </form>
        </div>
    </div>

    <!-- Shop Sale Modal -->
    <div id="shopSaleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('shopSale')">&times;</span>
            <h2>Shop Sale Order Form</h2>
            <form id="shopSaleForm">
                <div class="form-group">
                    <label>Order Code:</label>
                    <input type="text" name="orderCode" required>
                </div>
                <div class="form-group">
                    <label>Customer Number:</label>
                    <input type="text" name="customerNumber" required>
                </div>
                <div class="form-group">
                    <label>Contact Number:</label>
                    <input type="tel" name="contactNumber" required>
                </div>
                <button type="button" class="add-item" onclick="addItem('shopSale')">+ Add Item</button>
                <table class="items-table" id="shopSaleItems">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="totals-section">
                    <div class="totals-grid">
                        <div class="form-group">
                            <label>Grand Total:</label>
                            <input type="number" id="shopSaleGrandTotalInput" readonly>
                        </div>
                        <div class="form-group">
                            <label>Paid Amount:</label>
                            <input type="number" id="shopSalePaidAmount" oninput="updateDueAmount('shopSale')">
                        </div>
                        <div class="form-group">
                            <label>Due Amount:</label>
                            <input type="number" id="shopSaleDueAmount" readonly>
                        </div>

                        <input type="hidden" id="shopSaleAppPrice">
                    </div>
                </div>
                <button type="submit" class="submit-btn">Submit Order</button>
            </form>
        </div>
    </div>
    </main>
    <script src="assets/script.js"></script>
    <script>
        // Hardcoded products for demonstration
        // const products = [
        //     { id: 1, name: 'Product A', price: 10 },
        //     { id: 2, name: 'Product B', price: 15 },
        //     { id: 3, name: 'Product C', price: 20 }
        // ];

        function openModal(type) {
            document.getElementById(type + 'Modal').style.display = 'block';
        }

        function closeModal(type) {
            document.getElementById(type + 'Modal').style.display = 'none';
        }
        function fetchProducts() {
    return fetch('get_products.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(products => {
            // Store products globally or in a way accessible to other functions
            window.availableProducts = products;
            return products;
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            return [];
        });
}

function validateForm(type) {
    const form = document.getElementById(`${type}Form`);
    const requiredFields = form.querySelectorAll('[required]');
    
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            alert(`Please fill in the ${field.name} field`);
            return false;
        }
    }

    const itemRows = document.querySelectorAll(`#${type}Items tbody tr`);
    if (itemRows.length === 0) {
        alert('Please add at least one item to the order');
        return false;
    }

    return true;
}

// Modify form submission event listeners
document.getElementById('shopSaleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (validateForm('shopSale')) {
        submitOrder('shopSale');
    }
});
function createProductSelect() {
        const select = document.createElement('select');
        select.innerHTML = '<option value="">Select Product</option>' +
            (window.availableProducts || []).map(product => 
                `<option value="${product.id}" data-price="${product.price}">${product.name}</option>`
            ).join('');
        return select;
    }

// Modify your existing script to fetch products on page load
document.addEventListener('DOMContentLoaded', () => {
    fetchProducts();
});
        function removeRow(btn) {
            const row = btn.closest('tr');
            const tableId = row.closest('table').id;
            const type = tableId.replace('Items', '');
            row.remove();
            updateGrandTotal(type);
        }

        function addItem(type) {
        const tbody = document.querySelector(`#${type}Items tbody`);
        const row = document.createElement('tr');
        
        const productCell = document.createElement('td');
        const productSelect = createProductSelect();
        productSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const price = option.dataset.price;
            priceInput.value = price;
            updateRowTotal(row);
        });
        productCell.appendChild(productSelect);

        const priceCell = document.createElement('td');
        const priceInput = document.createElement('input');
        priceInput.type = 'number';
        priceInput.readOnly = true;
        priceCell.appendChild(priceInput);

        const quantityCell = document.createElement('td');
        const quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.min = '1';
        quantityInput.value = '1';
        quantityInput.addEventListener('change', function() {
            updateRowTotal(row);
        });
        quantityCell.appendChild(quantityInput);

        const totalCell = document.createElement('td');
        const totalInput = document.createElement('input');
        totalInput.type = 'number';
        totalInput.readOnly = true;
        totalInput.classList.add('row-total');
        totalCell.appendChild(totalInput);

        const actionCell = document.createElement('td');
        const removeButton = document.createElement('button');
        removeButton.textContent = '×';
        removeButton.classList.add('remove-row');
        removeButton.addEventListener('click', function() {
            removeRow(this);
        });
        actionCell.appendChild(removeButton);

        row.appendChild(productCell);
        row.appendChild(priceCell);
        row.appendChild(quantityCell);
        row.appendChild(totalCell);
        row.appendChild(actionCell);

        tbody.appendChild(row);
    }

    function updateRowTotal(row) {
        const price = parseFloat(row.cells[1].querySelector('input').value) || 0;
        const quantity = parseFloat(row.cells[2].querySelector('input').value) || 0;
        const total = price * quantity;
        row.cells[3].querySelector('input').value = total.toFixed(2);
        
        updateGrandTotal(row.closest('table').id.replace('Items', ''));
    }


        function fetchCompanyPercentages() {
    return fetch('get_percentage_values.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(percentages => {
            // Store percentages globally
            window.companyPercentages = percentages;
            console.log('Company Percentages:', percentages);
            return percentages;
        })
        .catch(error => {
            console.error('Error fetching company percentages:', error);
            return [];
        });
}

function calculateAppPrice(type) {
        const grandTotalInput = document.getElementById(`${type}GrandTotalInput`);
        const grandTotal = parseFloat(grandTotalInput.value) || 0;

        // Special handling for Shop Sale
        if (type === 'shopSale') {
            const appPriceInput = document.getElementById(`${type}AppPrice`);
            if (appPriceInput) {
                appPriceInput.value = grandTotal.toFixed(2);
            }
            updateDueAmount(type);
            return;
        }

        // Existing logic for other types (Foodpanda, Dastak)
        const companyPercentages = window.companyPercentages || [];
        const companyData = companyPercentages.find(
            item => item.company_name.toLowerCase() === type.toLowerCase()
        );

        if (companyData) {
            let appPrice;
            const value = companyData.value.replace('%', '');
            const discountValue = parseFloat(value);
            
            // Correct app price input selection
            const appPriceInput = document.getElementById(
                type === 'dastak' ? 'dastakaAppPrice' : `${type}AppPrice`
            );

            switch(type) {
                case 'foodpanda':
                    // Calculate app price for Foodpanda (25% discount)
                    appPrice = grandTotal - (grandTotal * discountValue / 100);
                    break;
                case 'dastak':
                    // Calculate Dastak by subtracting fixed value multiplied by total quantity
                    const itemRows = document.querySelectorAll(`#${type}Items tbody tr`);
                    let totalQuantity = 0;

                    itemRows.forEach(row => {
                        const quantity = parseFloat(row.cells[2].querySelector('input').value) || 0;
                        totalQuantity += quantity;
                    });

                    // Subtract fixed value multiplied by total quantity
                    appPrice = grandTotal - (discountValue * totalQuantity);
                    break;
                default:
                    appPrice = grandTotal;
            }

            // Ensure app price is not negative
            appPrice = Math.max(appPrice, 0);

            // Only set value if appPriceInput exists
            if (appPriceInput) {
                appPriceInput.value = appPrice.toFixed(2);
            }
        } else {
            console.warn(`No percentage data found for ${type}`);
            // Find the app price input
            const appPriceInput = document.getElementById(`${type}AppPrice`);
            
            // Fallback to full grand total if no percentage found
            if (appPriceInput) {
                appPriceInput.value = grandTotal.toFixed(2);
            }
        }

        // Trigger due amount update
        updateDueAmount(type);
    }

function updateGrandTotal(type) {
    const totals = Array.from(document.querySelectorAll(`#${type}Items .row-total`))
        .map(input => parseFloat(input.value) || 0);
    const grandTotal = totals.reduce((sum, total) => sum + total, 0);
    document.getElementById(`${type}GrandTotalInput`).value = grandTotal.toFixed(2);
    
    // Calculate app price after updating grand total
    calculateAppPrice(type);
}

function updateDueAmount(type) {
        const grandTotal = parseFloat(document.getElementById(`${type}GrandTotalInput`).value) || 0;
        
        // Special handling for Dastak app price
        const appPriceElementId = type === 'dastak' ? 'dastakaAppPrice' : `${type}AppPrice`;
        const appPriceInput = document.getElementById(appPriceElementId);
        
        // Check if appPriceInput exists before accessing its value
        const appPrice = appPriceInput ? parseFloat(appPriceInput.value) || 0 : grandTotal;
        
        const paidAmountInput = document.getElementById(`${type}PaidAmount`);
        
        // Check if paidAmountInput exists before accessing its value
        const paidAmount = paidAmountInput ? parseFloat(paidAmountInput.value) || 0 : 0;
        
        // Calculate due amount based on app price
        const dueAmount = grandTotal - paidAmount;
        
        const dueAmountInput = document.getElementById(`${type}DueAmount`);
        
        // Only set the value if the input exists
        if (dueAmountInput) {
            dueAmountInput.value = dueAmount.toFixed(2);
        }
    }

    // Event listeners for Shop Sale
    document.getElementById('shopSalePaidAmount').addEventListener('input', function() {
        updateDueAmount('shopSale');
    });

    document.getElementById('shopSalePaidAmount').addEventListener('change', function() {
        updateDueAmount('shopSale');
    });

    // Form submission handler for Shop Sale
    document.getElementById('shopSaleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitOrder('shopSale');
    });
// Modify page load event to fetch both products and percentages
document.addEventListener('DOMContentLoaded', () => {
    Promise.all([
        fetchProducts(),
        fetchCompanyPercentages()
    ]).then(() => {
        console.log('Products and company percentages loaded');
    });
});

// Modify paid amount input to trigger due amount update for all forms
document.getElementById('foodpandaPaidAmount').addEventListener('change', function() {
    updateDueAmount('foodpanda');
});

document.getElementById('dastakPaidAmount').addEventListener('change', function() {
    updateDueAmount('dastak');
});

document.getElementById('shopSalePaidAmount').addEventListener('change', function() {
    updateDueAmount('shopSale');
});

        // Form submission handling
        document.getElementById('foodpandaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitOrder('foodpanda');
        });

        document.getElementById('dastakForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitOrder('dastak');
        });

        document.getElementById('shopSaleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitOrder('shopSale');
        });
        function submitOrder(type) {
    const form = document.getElementById(`${type}Form`);
    const submitButton = form.querySelector('button[type="submit"]');

    if (submitButton.disabled) {
        return;
    }

    submitButton.disabled = true;

    const formData = new FormData(form);

    // Collect order items
    const items = [];
    const itemRows = document.querySelectorAll(`#${type}Items tbody tr`);
    itemRows.forEach(row => {
        const product = row.cells[0].querySelector('select');
        const price = row.cells[1].querySelector('input');
        const quantity = row.cells[2].querySelector('input');

        // Additional validation
        if (!product.value || !price.value || !quantity.value) {
            alert('Please fill in all product details');
            submitButton.disabled = false;
            return;
        }

        items.push({
            productId: product.value,
            productName: product.options[product.selectedIndex].text,
            price: price.value,
            quantity: quantity.value
        });
    });

    const orderData = {
        type: type,
        orderCode: formData.get('orderCode'),
        customerNumber: formData.get('customerNumber'),
        contactNumber: formData.get('contactNumber'),
        grandTotal: document.getElementById(`${type}GrandTotalInput`).value,
        appPrice: type === 'dastak' 
            ? document.getElementById('dastakaAppPrice').value 
            : document.getElementById(`${type}AppPrice`).value,
        paidAmount: document.getElementById(`${type}PaidAmount`).value,
        dueAmount: document.getElementById(`${type}DueAmount`).value,
        items: items
    };

    // Send data to server
    fetch('insert_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        // Check if response is ok before parsing JSON
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(`Order submitted successfully! Invoice Number: ${data.invoice_number}`);
            form.reset();
            document.getElementById(`${type}Items`).querySelector('tbody').innerHTML = '';
            closeModal(type);
        } else {
            alert(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the order. Please check your inputs and try again.');
    })
    .finally(() => {
        // Re-enable the submit button
        submitButton.disabled = false;
    });
}
    </script>
    <script>

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        });


        
    </script>
</body>
</html>