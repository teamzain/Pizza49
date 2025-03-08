<?php
session_start();
include 'loader.html';
@include 'db/config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $date = $_POST['date'];
    $expenseType = $_POST['expenseType'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // Prepare an SQL statement
    $sql = "INSERT INTO expenses (date, expense_type, amount, description) VALUES (?, ?, ?, ?)";
    
    // Create a prepared statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters to the prepared statement
    $stmt->bind_param("ssds", $date, $expenseType, $amount, $description);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "New expense added successfully";
        exit; // Stop further execution
    } else {
        echo "Error: " . $conn->error;
        exit; // Stop further execution
    }
    
    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
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
    <title>Dani's Fabric-Expense Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f4f8;
        }
        .button {
            background-color: #4a90e2;
            border: none;
            color: white;
            padding: 12px 24px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s, transform 0.1s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .button:hover {
            background-color: #357abd;
        }
        .button:active {
            transform: translateY(1px);
        }
        .popup {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .popup-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 500px;
            animation: slideDown 0.3s;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.3s;
        }
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }
        input, textarea {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74,144,226,0.2);
        }
        input[type="submit"] {
            background-color: #4a90e2;
            color: white;
            cursor: pointer;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #357abd;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .success-popup {
            display: none;
            position: fixed;
            z-index: 2;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s, slideUp 0.3s;
        }
        @keyframes slideUp {
            from { transform: translate(-50%, 20px); }
            to { transform: translate(-50%, -50%); }
        }
        @media screen and (max-width: 600px) {
            .popup-content {
                width: 95%;
                margin: 5% auto;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="main-content">
<?php include 'topbar.php'; ?>
    <button class="button" id="openPopup">Add Expense</button>
    <br><br>
    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Expense Type</th>
                <th>Expense Amount</th>
                <th>Expense Description</th>
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

            // Fetch data from the expenses table
            $expensesQuery = "SELECT * FROM expenses ORDER BY date DESC";
            $expensesResult = $conn->query($expensesQuery);

            // Check if the query was successful
            if ($expensesResult) {
                if ($expensesResult->num_rows > 0) {
                    // Output table data
                    while ($expense = $expensesResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='step-number color-1' data-label='Date'>" . $expense['date'] . "</td>";
                        echo "<td data-label='Expense Type'>" . $expense['expense_type'] . "</td>";
                        echo "<td data-label='Expense Amount'>Rs" . number_format($expense['amount'], 2) . "</td>";
                        echo "<td data-label='Expense Description'>" . $expense['description'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No expenses found</td></tr>";
                }

                // Free the result set
                $expensesResult->free_result();
            } else {
                echo "<tr><td colspan='4'>Error fetching data: " . $conn->error . "</td></tr>";
            }

            // Close the database connection
            $conn->close();
            ?>
        </tbody>
    </table>
</div>
    <div id="expensePopup" class="popup">
        <div class="popup-content">
            <span class="close">&times;</span>
            <h2>Add New Expense</h2>
            <form id="expenseForm">
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required>

                <label for="expenseType">Expense Type:</label>
                <input type="text" id="expenseType" name="expenseType" required>

                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.01" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="3"></textarea>

                <input type="submit" value="Submit">
            </form>
        </div>
    </div>

    <div id="successPopup" class="success-popup">
        Data inserted successfully!
    </div>
            </main>
        <script src="assets/script.js"></script>
    <script>
        const openPopup = document.getElementById('openPopup');
        const popup = document.getElementById('expensePopup');
        const closeBtn = document.getElementsByClassName('close')[0];
        const form = document.getElementById('expenseForm');

        openPopup.onclick = function() {
            popup.style.display = "block";
        }

        closeBtn.onclick = function() {
            popup.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == popup) {
                popup.style.display = "none";
            }
        }

        form.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            fetch('<?php echo $_SERVER["PHP_SELF"]; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log('Server response:', data);
                if (data.includes("New expense added successfully")) {
                    popup.style.display = "none";
                    form.reset();
                    showSuccessPopup();
                } else {
                    alert("Error: " + data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred. Please try again.");
            });
        }

        function showSuccessPopup() {
            const successPopup = document.getElementById('successPopup');
            successPopup.style.display = "block";
            setTimeout(() => {
                successPopup.style.display = "none";
            }, 3000); // Hide after 3 seconds
        }
    </script>
</body>
</html>