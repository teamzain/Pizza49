<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'loader.html';
@include 'db/config.php'; // Use forward slashes for directory separator

// Fetch all staff users
$sql = "SELECT * FROM users WHERE role = 'staff'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Styles for table and modal removed as they are not needed */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .btn-action {
            background: none;
            border: none;
            cursor: pointer;
            color: #dc3545;
            font-size: 18px;
            margin: 0 5px;
        }

        .btn-action:hover {
            color: #c82333;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>
<main class="main-content">
<?php include 'topbar.php'; ?>
    <button class="btn" onclick="window.location.href='staffsignup.php'">Add New Staff</button>
    <h2>Staff List</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Access</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . $row["name"] . "</td>";
                    echo "<td>" . $row["email"] . "</td>";
                    echo "<td>" . $row["role"] . "</td>";
                    echo "<td>" . $row["sections"] . "</td>";
                    echo "<td>";
                    echo "<button class='btn-access' onclick='manageAccess(" . $row["id"] . ")'><i class='fas fa-key'></i></button>";
                    echo "<button class='btn-action' onclick='deleteStaff(" . $row["id"] . ")'><i class='fas fa-trash'></i></button>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No staff members found</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</main>

<script src="assets/script.js"></script>

<script>
function deleteStaff(staffId) {
    if (confirm('Are you sure you want to delete this staff member?')) {
        // Redirect to delete_staff_access.php with the staff ID as a query parameter
        window.location.href = 'Action/Delete/delete_staff_access.php?id=' + staffId;
    }
}

function manageAccess(staffId) {
    // Redirect to access_management.php with the staff ID as a query parameter
    window.location.href = 'access_management.php?id=' + staffId;
}
</script>

<?php $conn->close(); ?>

</body>
</html>
