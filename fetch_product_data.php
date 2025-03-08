<?php
 include 'db/config.php';

if (isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];
    
    // Query to fetch product based on barcode_number
    $sql = "SELECT product_name FROM products WHERE barcode_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $barcode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'product_name' => $row['product_name'],
     
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
    $conn->close();
}
