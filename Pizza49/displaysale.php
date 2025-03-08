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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
            color: #333;
        }

        .dashboard-container {
            /* width: 95%; */
            max-width: 800px;
            margin: 10px auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            padding: clamp(10px, 3vw, 25px);
        }

        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-container {
            flex: 1;
            min-width: 200px;
            max-width: 100%;
        }

        .search-input {
            width: 100%;
            padding: clamp(8px, 2vw, 12px);
            border: 2px solid #007bff;
            border-radius: 6px;
            font-size: clamp(14px, 2vw, 16px);
            outline: none;
            transition: border-color 0.3s ease;
        }

        .tab-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(5px, 1vw, 10px);
            width: 100%;
        }

        .tab-button {
            flex: 1;
            min-width: fit-content;
            padding: clamp(6px, 1.5vw, 12px) clamp(10px, 2vw, 15px);
            border: none;
            background-color: #6c757d;
            color: white;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: clamp(12px, 1.5vw, 14px);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .tab-button i {
            font-size: clamp(12px, 1.5vw, 14px);
        }

        .tab-button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .tab-button.active {
            background-color: #007bff;
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
            font-size: clamp(12px, 1.5vw, 14px);
        }

        .sales-table thead {
            position: sticky;
            top: 0;
            background-color: #007bff;
            color: white;
            z-index: 10;
        }

        .sales-table th {
            padding: clamp(8px, 1.5vw, 12px);
            text-align: left;
            white-space: nowrap;
            font-weight: 600;
        }

        .sales-table td {
            padding: clamp(6px, 1.5vw, 10px);
            border-bottom: 1px solid #e0e0e0;
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
            gap: clamp(4px, 1vw, 8px);
        }

        .delete-btn, .print-btn {
            padding: clamp(4px, 1vw, 8px) clamp(8px, 1.5vw, 12px);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: clamp(11px, 1.2vw, 13px);
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .print-btn {
            background-color: #28a745;
            color: white;
        }

        .delete-btn i, .print-btn i {
            font-size: clamp(11px, 1.2vw, 13px);
        }

        /* Responsive Adjustments */
        @media screen and (max-width: 1024px) {
            .dashboard-container {
                width: 98%;
                margin: 5px auto;
            }

            .tab-buttons {
                justify-content: center;
            }
        }

        @media screen and (max-width: 768px) {
            .header-controls {
                flex-direction: column;
            }

            .search-container {
                width: 100%;
            }

            .tab-button {
                padding: 8px 12px;
                font-size: 12px;
            }

            .tab-button i {
                font-size: 12px;
            }

            .delete-btn, .print-btn {
                padding: 6px 8px;
                font-size: 11px;
            }

            .delete-btn i, .print-btn i {
                font-size: 11px;
            }
        }

        @media screen and (max-width: 480px) {
            .dashboard-container {
                padding: 8px;
                margin: 5px;
            }

            .tab-buttons {
                flex-direction: column;
            }

            .tab-button {
                width: 100%;
                margin: 2px 0;
                padding: 6px 10px;
                font-size: 11px;
            }

            .sales-table th, 
            .sales-table td {
                padding: 6px;
                font-size: 11px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }

            .delete-btn, .print-btn {
                width: 100%;
                justify-content: center;
                padding: 4px 6px;
                font-size: 10px;
            }
        }

        /* Custom scrollbar for better mobile experience */
        .table-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: #555;
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