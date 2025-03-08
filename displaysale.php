<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza49</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    background-color: #f8f9fa;
    color: #333;
    font-size: 16px;
    min-width: 320px; /* Ensure minimum mobile width */
}

.main-content {
    width: 100%;
    padding: 0 clamp(10px, 3vw, 20px);
}

.dashboard-container {
    width: 100%;
    max-width: 1200px; /* Slightly increased max-width */
    margin: 15px auto;
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    padding: clamp(15px, 4vw, 30px);
    overflow-x: hidden; /* Prevent horizontal scrolling */
}

.header-controls {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

.tab-buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    width: 100%;
}

.tab-button {
    flex: 0 1 auto;
    padding: 10px 15px;
    border: none;
    background-color: #6c757d;
    color: white;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
    min-width: 120px; /* Ensure buttons have a minimum width */
}

.tab-button.active {
    background-color: #007bff;
}

.search-container {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
}

.search-input {
    width: 100%;
    padding: 12px;
    border: 2px solid #007bff;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.table-container {
    width: 100%;
    overflow-x: auto;
    max-height: 70vh;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    -webkit-overflow-scrolling: touch;
}

.sales-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 14px;
}

.sales-table thead {
    position: sticky;
    top: 0;
    background-color: #007bff;
    color: white;
    z-index: 10;
}

.sales-table th,
.sales-table td {
    padding: 12px;
    text-align: left;
    white-space: nowrap;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sales-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.sales-table tr:hover {
    background-color: #f1f3f5;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.delete-btn, 
.print-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    transition: transform 0.2s;
}

.delete-btn {
    background-color: #dc3545;
    color: white;
}

.print-btn {
    background-color: #28a745;
    color: white;
}

.delete-btn:hover,
.print-btn:hover {
    transform: scale(1.05);
}

/* Responsive Breakpoints */
@media screen and (max-width: 1024px) {
    .dashboard-container {
        margin: 10px;
        padding: 15px;
    }

    .tab-buttons {
        flex-direction: row;
        justify-content: center;
    }

    .tab-button {
        flex-grow: 1;
        min-width: 100px;
    }

    .sales-table {
        font-size: 13px;
    }
}

@media screen and (max-width: 768px) {
    body {
        font-size: 14px;
    }

    .header-controls {
        gap: 10px;
    }

    .tab-buttons {
        flex-direction: column;
        align-items: stretch;
    }

    .tab-button {
        padding: 10px;
        min-width: 100%;
    }

    .sales-table th,
    .sales-table td {
        padding: 8px;
        font-size: 12px;
    }

    .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }

    .delete-btn, 
    .print-btn {
        width: 100%;
        justify-content: center;
    }
}

@media screen and (max-width: 480px) {
    .dashboard-container {
        margin: 5px;
        padding: 10px;
    }

    .sales-table {
        font-size: 11px;
    }

    .sales-table th,
    .sales-table td {
        padding: 6px;
        font-size: 10px;
    }

    .tab-button {
        font-size: 12px;
        padding: 8px;
    }
}

/* Improved Scrollbar */
.table-container::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 5px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 5px;
    transition: background 0.3s;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Accessibility and Performance Enhancements */
@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
    }
}

/* Print Styles */
@media print {
    body {
        background: white;
    }

    .dashboard-container {
        box-shadow: none;
        margin: 0;
        padding: 0;
    }

    .tab-buttons,
    .search-container {
        display: none;
    }
}
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <main class="main-content">
    <?php include 'topbar.php'; ?>
    <div class="dashboard-container">
        <div class="header-controls">
            <div class="tab-buttons">
                <button class="tab-button" onclick="loadSales('dastak')">
                    <i class="fas fa-truck"></i> Dastak Sale
                </button>
                <button class="tab-button" onclick="loadSales('foodpanda')">
                    <i class="fas fa-utensils"></i> Foodpanda Sale
                </button>
                <button class="tab-button" onclick="loadSales('shop')">
                    <i class="fas fa-shopping-bag"></i> Shop Sale
                </button>
            </div>
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search by Order Code..." oninput="searchByOrderCode()">
            </div>
        </div>

        <div class="table-container">
            <table class="sales-table" id="salesTable">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Customer Name</th>
                        <th>Contact Number</th>
                        <th>Product Details</th>
                        <th>Total Amount</th>
                        <th>App Price</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                        <th>Grand Total</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
                    <!-- Sales data will be dynamically populated here -->
                </tbody>
            </table>
        </div>
    </div>
    </main>

    <script>
        function searchByOrderCode() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#salesTableBody tr');

            rows.forEach(row => {
                const orderCodeCell = row.cells[0];
                const orderCode = orderCodeCell.textContent.toLowerCase();
                row.style.display = orderCode.includes(searchText) ? '' : 'none';
            });
        }

        function loadSales(type) {
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.closest('.tab-button').classList.add('active');

            fetch(`get_sales.php?type=${type}`)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('salesTableBody');
                    tableBody.innerHTML = '';

                    data.forEach(sale => {
                        let productDetailsDisplay = '';
                        try {
                            const products = JSON.parse(sale.product_details);
                            productDetailsDisplay = products.map(p => 
                                `${p.productName} (${p.quantity})`
                            ).join(', ');
                        } catch (error) {
                            productDetailsDisplay = sale.product_details;
                        }

                        const row = `
                            <tr>
                                <td>${sale.order_code}</td>
                                <td>${sale.customer_name}</td>
                                <td>${sale.customer_contact_number}</td>
                                <td class="product-details" title="${productDetailsDisplay}">${productDetailsDisplay}</td>
                                <td>${sale.total_amount}</td>
                                <td>${sale.app_price}</td>
                                <td>${sale.paid_amount}</td>
                                <td>${sale.due_amount}</td>
                                <td>${sale.grandtotal}</td>
                                <td>${sale.date}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="delete-btn" onclick="deleteSale('${sale.order_code || 0}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button class="print-btn" onclick="printSale('${sale.invoice_number || 0}')">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });

                    if (document.getElementById('searchInput').value.trim() !== '') {
                        searchByOrderCode();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load sales data');
                });
        }

        function deleteSale(orderCode) {
            if (!orderCode || orderCode === '0') {
                alert('Invalid sale: Cannot delete this record');
                return;
            }

            if (confirm(`Are you sure you want to delete sale with Order Code ${orderCode}?`)) {
                fetch(`delete_sale.php?order_code=${encodeURIComponent(orderCode)}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Sale deleted successfully');
                        const activeButton = document.querySelector('.tab-button.active');
                        if (activeButton) {
                            activeButton.click();
                        }
                    } else {
                        alert('Failed to delete sale: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the sale');
                });
            }
        }

        function printSale(invoiceNumber) {
            window.open(`print_sale.php?invoice_number=${invoiceNumber}`, '_blank');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('.tab-button').click();
        });
    </script>
</body>
</html>