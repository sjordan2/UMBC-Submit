<?php

$user_first_name = $_POST['fname'];
$user_last_name = $_POST['lname'];
$user_campus_id = $_POST['cID'];
$user_name_id = $_POST['nID'];
$user_role = $_POST['role'];
$user_discussion = $_POST['disc'];

require_once '../sql_functions.php';
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

addUserToDatabase($user_name_id, $user_campus_id, $user_first_name, $user_last_name, $user_discussion, $user_role, $conn, true);