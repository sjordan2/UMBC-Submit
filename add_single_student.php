<?php

$student_first_name = $_POST['fname'];
$student_last_name = $_POST['lname'];
$student_campus_id = $_POST['cID'];
$student_name_id = $_POST['nID'];
$student_role = $_POST['role'];
$student_discussion = $_POST['disc'];

require_once 'sql_functions.php';
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

addStudentToDatabase($student_name_id, $student_campus_id, $student_first_name, $student_last_name, $student_discussion, $student_role, $conn, true);