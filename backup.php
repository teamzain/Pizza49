<?php
$servername = "fdb1029.awardspace.net"; // Database host
$username = "4529643_dani"; // Database username
$password = "zain@786"; // Database password
$dbname = "4529643_dani"; // Database name

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Backup the database
$backup_file = $dbname . "_" . date("Y-m-d_H-i-s") . ".sql"; // Name of the backup file
$command = "mysqldump --opt -h $servername -u $username -p$password $dbname > $backup_file";

system($command, $output);

// Check if the backup was successful
if ($output === 0) {
    // Set headers to force download
    header('Content-Description: File Transfer');
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename=' . basename($backup_file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backup_file));
    
    // Read the file and send it to the user
    readfile($backup_file);
    
    // Optional: Delete the backup file after download
    unlink($backup_file);
} else {
    echo "Backup failed.";
}

$conn->close();
?>
