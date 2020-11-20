<?php
session_start();

include 'db_sql.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SHOW TABLES LIKE 'Students';";
$result = $conn->query($sql);
if($result->num_rows == 0) {
    $_SESSION["DeleteTableMessage"] = "There is no 'Students' table to delete!";
    header('Location: main.php');
} else {
    $newtable_sql = "DROP TABLE Students";
    if ($conn->query($newtable_sql) === TRUE) {
        $_SESSION["DeleteTableMessage"] = "'Students' table successfully deleted!";
        header('Location: main.php');
    } else {
        $_SESSION["DeleteTableMessage"] = $conn->error;
        header('Location: main.php');
    }

}