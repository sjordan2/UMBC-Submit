<?php
session_start();
include 'db_sql.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(empty($_POST["student_fname"])) {
    $_SESSION["StudentError"] = "The student's first name cannot be empty!";
    header('Location: main.php');
    exit();
}
if(empty($_POST["student_lname"])) {
    $_SESSION["StudentError"] = "The student's last name cannot be empty!";
    header('Location: main.php');
    exit();
}
if(empty($_POST["student_campus_id"])) {
    $_SESSION["StudentError"] = "The student's campus ID cannot be empty!";
    header('Location: main.php');
    exit();
}
if(empty($_POST["student_name_id"])) {
    $_SESSION["StudentError"] = "The student's name ID cannot be empty!";
    header('Location: main.php');
    exit();
}
if(empty($_POST["student_role"])) {
    $_SESSION["StudentError"] = "The student's role cannot be empty!";
    header('Location: main.php');
    exit();
}
if(empty($_POST["student_discussion"])) {
    $_SESSION["StudentError"] = "The student's discussion section cannot be empty!";
    header('Location: main.php');
    exit();
}
$student_nameid = $_POST["student_name_id"];
$student_campusid = $_POST["student_campus_id"];
$student_firstname = $_POST["student_fname"];
$student_lastname = $_POST["student_lname"];
$student_discussion = $_POST["student_discussion"];
$student_role = $_POST["student_role"];

$newstudent_sql = "INSERT INTO Students (umbc_name_id, umbc_id, firstname, lastname, section, role)
                    VALUES ('$student_nameid', '$student_campusid', '$student_firstname',
                   '$student_lastname', '$student_discussion', '$student_role')";

if ($conn->query($newstudent_sql) === TRUE) {
    $_SESSION["StudentObject"] = ["fname" => $_POST["student_fname"],
        "lname" => $_POST["student_lname"],
        "campus_id" => $_POST["student_campus_id"],
        "name_id" => $_POST["student_name_id"],
        "role" => $_POST["student_role"],
        "discussion" => $_POST["student_discussion"]];
} else {
    $_SESSION["StudentError"] = $conn->error;
}
header('Location: main.php');

?>