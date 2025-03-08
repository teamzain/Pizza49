<?php
session_start();
include 'loader.html';
// Ensure no output before session_start
@include 'db/config.php'; // Ensure config.php has no output

// Function to validate CNIC format
function isValidCNIC($cnic) {
    return preg_match('/^\d{5}-\d{7}-\d{1}$/', $cnic);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = isset($_POST['staff_id']) ? $_POST['staff_id'] : '';
    $staff_name = $_POST['staff_name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $cnic_number = $_POST['cnic_number'];

    // Validate CNIC format
    if (!isValidCNIC($cnic_number)) {
        echo "<script>alert('Invalid CNIC format. Please use 00000-0000000-0 format.');</script>";
    } else {
        // If staff_id is provided, update the staff record
        if (!empty($staff_id)) {
            $sql = "UPDATE staff SET staff_name=?, phone_number=?, address=?, cnic_number=? WHERE staff_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $staff_name, $phone_number, $address, $cnic_number, $staff_id);
            
            if ($stmt->execute()) {
                echo "<script>alert('Staff updated successfully!');</script>";
            } else {
                echo "Error updating record: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Insert a new staff member
            $sql = "INSERT INTO staff (staff_name, phone_number, address, cnic_number) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $staff_name, $phone_number, $address, $cnic_number);
            
            if ($stmt->execute()) {
                echo "<script>alert('Staff added successfully!');</script>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all staff members
$sql = "SELECT * FROM staff";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
     

   /* Popup modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    padding-top: 60px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4); /* Modal background overlay */
}

.modal-content {
    background-color: #fff;
    margin: auto;
    padding: 30px;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    animation: fadeIn 0.5s; /* Smooth fade-in effect */
}

@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

.btn {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    margin: 10px 0;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

.btn:hover {
    background-color: #218838;
}

/* Form styles */
.staff-form label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
    color: #333;
}

.staff-form input[type="text"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    color: #555;
}

.staff-form input[type="submit"] {
    background-color: #28a745;
    color: white;
    padding: 12px;
    border: none;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
    border-radius: 4px;
    margin-top: 10px;
}

.staff-form input[type="submit"]:hover {
    background-color: #218838;
}

/* Table styles */
table {
    width: 100%;
    border-collapse: collapse;
}

table, th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Button action styles */
.btn-action {
    background-color: #007bff;
    color: white;
    padding: 5px 10px;
    border: none;
    cursor: pointer;
    border-radius: 3px;
}

.btn-action:hover {
    background-color: #0056b3;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-content {
        width: 90%;
    }

    table, th, td {
        font-size: 14px;
    }

    .staff-form input[type="text"], .staff-form input[type="submit"] {
        font-size: 14px;
    }
}


.error {
            color: red;
            font-size: 14px;
            margin-top: -15px;
            margin-bottom: 10px;
        }
        .hint {
            color: #888;
            font-size: 14px;
            margin-bottom: 10px;
        }
 
     </style>
</head>
<body>

<?php include 'navbar.php'; ?>
<main class="main-content">
<?php include 'topbar.php'; ?>
       

<button class="btn" id="openModalBtn">Add New Staff</button>

<div id="staffModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModalBtn">&times;</span>
        <h2>Add New Staff</h2>
        <form class="staff-form" action="staff_registration.php" method="POST">
            <label for="staff_name">Staff Name:</label>
            <input type="text" id="staff_name" name="staff_name" required>

           

            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number">

            <label for="address">Address:</label>
            <input type="text" id="address" name="address">

            <label for="cnic_number">CNIC Number:</label>
                <input type="text" id="cnic_number" name="cnic_number" placeholder="00000-0000000-0" >
                <div id="cnic_hint" class="hint">Format: 00000-0000000-0. Total 13 digits.</div>
                <div id="cnic_error" class="error"></div>

            <input type="submit" value="Add Staff">
        </form>
    </div>
</div>


<!-- Add New Staff Modal -->
<!-- Edit Staff Modal -->
<div id="editStaffModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditModalBtn">&times;</span>
        <h2>Edit Staff</h2>
        <form class="staff-form" id="editStaffForm" action="staff_registration.php" method="POST">
            <input type="hidden" id="edit_staff_id" name="staff_id">
            <label for="edit_staff_name">Staff Name:</label>
            <input type="text" id="edit_staff_name" name="staff_name" required>


            <label for="edit_phone_number">Phone Number:</label>
            <input type="text" id="edit_phone_number" name="phone_number">

            <label for="edit_address">Address:</label>
            <input type="text" id="edit_address" name="address">

            <label for="edit_cnic_number">CNIC Number:</label>
            <input type="text" id="edit_cnic_number" name="cnic_number" placeholder="00000-0000000-0">
            <div id="edit_cnic_hint" class="hint">Format: 00000-0000000-0. Total 13 digits.</div>
            <div id="edit_cnic_error" class="error"></div>

            <input type="submit" value="Update Staff">
        </form>
    </div>
</div>


<!-- Staff Table -->
<h2>Staff List</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
      
            <th>Phone</th>
            <th>Address</th>
            <th>CNIC</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr data-id='" . $row["staff_id"] . "'>";
            echo "<td>" . $row["staff_id"] . "</td>";
            echo "<td class='staff_name'>" . $row["staff_name"] . "</td>";
          
            echo "<td class='phone_number'>" . $row["phone_number"] . "</td>";
            echo "<td class='address'>" . $row["address"] . "</td>";
            echo "<td class='cnic_number'>" . $row["cnic_number"] . "</td>";
            echo "<td>";
            echo "<button class='btn-action' onclick='editStaff(" . $row["staff_id"] . ")'><i class='fas fa-edit'></i></button>";
            echo "<button class='btn-action' onclick='deleteStaff(" . $row["staff_id"] . ")'><i class='fas fa-trash'></i></button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No staff members found</td></tr>";
    }
    ?>
    </tbody>
</table>
</main>
<script src="assets/script.js"></script>
<script>
// Modal for adding staff
var modal = document.getElementById("staffModal");
var openModalBtn = document.getElementById("openModalBtn");
var closeModalBtn = document.getElementById("closeModalBtn");

openModalBtn.onclick = function() {
    modal.style.display = "block";
}

closeModalBtn.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

function deleteStaff(id) {
    if (confirm('Are you sure you want to delete this staff member?')) {
        window.location.href = 'Action/Delete/delete_staff.php?id=' + id;
    }
}


// Real-time CNIC validation
document.getElementById('cnic_number').addEventListener('input', function() {
    var cnic = this.value;
    var cnicPattern = /^\d{5}-\d{7}-\d{1}$/;
    var errorDiv = document.getElementById('cnic_error');
    var hintDiv = document.getElementById('cnic_hint');

    // Remove dashes for length check
    var cleanedCnic = cnic.replace(/-/g, '');
    if (cleanedCnic.length != 13) {
        errorDiv.textContent = 'CNIC must be exactly 13 digits.';
        this.style.borderColor = 'red';
    } else if (!cnicPattern.test(cnic)) {
        errorDiv.textContent = 'Invalid CNIC format. Please use 00000-0000000-0 format.';
        this.style.borderColor = 'red';
    } else {
        errorDiv.textContent = '';
        this.style.borderColor = '#ddd';
    }
});

// Form submission validation
document.querySelector('.staff-form').addEventListener('submit', function(event) {
    var cnic = document.getElementById('cnic_number').value;
    var cnicPattern = /^\d{5}-\d{7}-\d{1}$/;
    var errorDiv = document.getElementById('cnic_error');

    // Remove dashes for length check
    var cleanedCnic = cnic.replace(/-/g, '');
    if (cleanedCnic.length != 13) {
        errorDiv.textContent = 'CNIC must be exactly 13 digits.';
        event.preventDefault(); // Prevent form submission
    } else if (!cnicPattern.test(cnic)) {
        errorDiv.textContent = 'Invalid CNIC format. Please use 00000-0000000-0 format.';
        event.preventDefault(); // Prevent form submission
    }
});
</script>

<script>
 // Edit Staff Modal
var editStaffModal = document.getElementById("editStaffModal");
var closeEditModalBtn = document.getElementById("closeEditModalBtn");

// Function to edit staff
function editStaff(id) {
    var row = document.querySelector("tr[data-id='" + id + "']");
    var staffName = row.querySelector(".staff_name").textContent;
 
    var phoneNumber = row.querySelector(".phone_number").textContent;
    var address = row.querySelector(".address").textContent;
    var cnicNumber = row.querySelector(".cnic_number").textContent;

    // Populate the edit form
    document.getElementById("edit_staff_id").value = id;
    document.getElementById("edit_staff_name").value = staffName;

    document.getElementById("edit_phone_number").value = phoneNumber;
    document.getElementById("edit_address").value = address;
    document.getElementById("edit_cnic_number").value = cnicNumber;

    // Open the edit modal
    editStaffModal.style.display = "block";
}

// Close the modal
closeEditModalBtn.onclick = function() {
    editStaffModal.style.display = "none";
}

// Close modal on outside click
window.onclick = function(event) {
    if (event.target == editStaffModal) {
        editStaffModal.style.display = "none";
    }
}

// Real-time CNIC validation for the edit form
document.getElementById('edit_cnic_number').addEventListener('input', function() {
    var cnic = this.value;
    var cnicPattern = /^\d{5}-\d{7}-\d{1}$/;
    var errorDiv = document.getElementById('edit_cnic_error');

    // Remove dashes for length check
    var cleanedCnic = cnic.replace(/-/g, '');
    if (cleanedCnic.length != 13) {
        errorDiv.textContent = 'CNIC must be exactly 13 digits.';
        this.style.borderColor = 'red';
    } else if (!cnicPattern.test(cnic)) {
        errorDiv.textContent = 'Invalid CNIC format. Please use 00000-0000000-0 format.';
        this.style.borderColor = 'red';
    } else {
        errorDiv.textContent = '';
        this.style.borderColor = '#ddd';
    }
});

// Form submission validation
document.getElementById('editStaffForm').addEventListener('submit', function(event) {
    var cnic = document.getElementById('edit_cnic_number').value;
    var cnicPattern = /^\d{5}-\d{7}-\d{1}$/;
    var errorDiv = document.getElementById('edit_cnic_error');

    // Remove dashes for length check
    var cleanedCnic = cnic.replace(/-/g, '');
    if (cleanedCnic.length != 13) {
        errorDiv.textContent = 'CNIC must be exactly 13 digits.';
        event.preventDefault(); // Prevent form submission
    } else if (!cnicPattern.test(cnic)) {
        errorDiv.textContent = 'Invalid CNIC format. Please use 00000-0000000-0 format.';
        event.preventDefault(); // Prevent form submission
    }
});

</script>

<?php $conn->close(); ?>
</body>
</html>