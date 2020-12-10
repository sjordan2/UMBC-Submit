<?php
include 'sql_functions.php';

$new_last_name = $_POST["lname"];
$new_first_name = $_POST["fname"];
$new_name_id = $_POST["nameID"];
$new_disc_sect = $_POST["disc"];
$new_role = $_POST["role"];
$new_status = $_POST["status"];
$campus_id = $_POST["cID"];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$edit_student_sql = "UPDATE Students 
                    SET lastname = '$new_last_name', firstname = '$new_first_name', umbc_name_id = '$new_name_id', 
                        section = '$new_disc_sect', role = '$new_role', status = '$new_status'
                    WHERE umbc_id = '$campus_id'";
$result = $conn->query($edit_student_sql);

if($result === TRUE) {
    $successMessage = "SUCCESS: '" . $campus_id . "' successfully edited!";
    echo $successMessage;
} else {
    $errorMessage = "ERROR: " . $conn->error;
    echo $errorMessage;
}
