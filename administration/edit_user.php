<?php
include '../sql_functions.php';


// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$new_last_name = $conn->real_escape_string($_POST["lname"]);
$new_first_name = $conn->real_escape_string($_POST["fname"]);
$new_name_id = $_POST["nameID"];
$new_disc_sect = $_POST["disc"];
$new_role = $_POST["role"];
$new_status = $_POST["status"];
$campus_id = $_POST["cID"];

if(!ctype_digit($new_disc_sect)) {
    echo "ERROR: Discussion Section must be an integer!";
} else {
    $edit_student_sql = "UPDATE Users 
                SET lastname = '$new_last_name', firstname = '$new_first_name', umbc_name_id = '$new_name_id', 
                    section = '$new_disc_sect', role = '$new_role', status = '$new_status'
                WHERE umbc_id = '$campus_id'";
    $result = $conn->query($edit_student_sql);

    if ($result === TRUE) {
        $successMessage = "SUCCESS: '" . $campus_id . "' successfully edited!";
        echo $successMessage;
    } else {
        $errorMessage = "ERROR: " . $conn->error;
        echo $errorMessage;
    }
}
