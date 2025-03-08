<?php
// Include the database configuration file
include '../../db/config.php'; // Ensure the correct path to config.php

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $staff_id = intval($_GET['id']); // Get the ID from the URL and ensure it's an integer

    // Prepare a DELETE statement
    $sql = "DELETE FROM staff WHERE staff_id = ?";

    // Prepare and execute the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $staff_id);
        if ($stmt->execute()) {
            // Redirect to the main page with a success message
            header("Location: ../../staff_registration.php?delete=success");
        } else {
            // Redirect to the main page with an error message
            header("Location: ../../staff_registration.php?delete=error");
        }
        $stmt->close();
    } else {
        // Redirect to the main page with an error message
        header("Location: ../../staff_registration.php?delete=error");
    }
} else {
    // Redirect to the main page if no ID is provided
    header("Location: ../../staff_registration.php");
}

$conn->close();
?>