<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include '../../db/config.php';// Adjust the path based on your directory structure

// Check if the ID is provided
if (isset($_GET['id'])) {
    $staff_id = intval($_GET['id']);

    // Prepare the delete statement
    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $staff_id);

        if ($stmt->execute()) {
            // Redirect after successful deletion
            header("Location: ../../staff_access_authentication.php"); // Assuming your staff list page is named staff_list.php
            exit();
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Failed to prepare statement.";
    }
} else {
    echo "No staff ID provided.";
}

$conn->close();
?>