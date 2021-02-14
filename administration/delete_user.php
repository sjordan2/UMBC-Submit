<?php
include '../sql_functions.php';

$user_to_delete = $_POST['user_id'];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$deluser_sql_check = "SELECT * FROM Users WHERE umbc_id = '$user_to_delete';";
$result = $conn->query($deluser_sql_check);
if($result->num_rows > 1) {
    echo "ERROR: There are multiple rows matching the same UMBC Name ID!";
} else {
    $deluser_sql = "DELETE FROM Users WHERE umbc_id = '$user_to_delete';";
    $result = $conn->query($deluser_sql);
    if($result === TRUE) {
        $successMessage = "SUCCESS: User '" . $user_to_delete . "' successfully deleted!";
        echo $successMessage;
    } else {
        $errorMessage = "ERROR: " . $conn->error;
        echo $errorMessage;
    }
}

