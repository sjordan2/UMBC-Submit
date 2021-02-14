<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email_students_sql = "SELECT umbc_name_id, umbc_id, role, firstname, lastname, section FROM Users WHERE email_sent = '0'"; // The list of students to email
$email_students_result = $conn->query($email_students_sql);
$number_students = $email_students_result->num_rows;

$file = fopen("register_email.txt", "r");
$register_email_base = fread($file, filesize("register_email.txt"));

$get_instructors_sql = "SELECT firstname, lastname, umbc_name_id FROM Users WHERE role = 'Instructor'";
$get_instructors_result = $conn->query($get_instructors_sql);
$instructor_array = [];
while($row = $get_instructors_result->fetch_assoc()) {
    $signature_string = $row['firstname'] . " " . $row['lastname'] . " <" . $row['umbc_name_id'] . "@umbc.edu>";
    array_push($instructor_array, $signature_string);
}

$error_array = [];
$error_occurred = false;
while($row = $email_students_result->fetch_assoc()) {
    $curr_register_email = $register_email_base;
    $curr_register_email = str_replace("{{firstname}}", $row["firstname"], $curr_register_email);
    $curr_register_email = str_replace("{{lastname}}", $row["lastname"], $curr_register_email);
    $curr_register_email = str_replace("{{weblink}}", "https://www.csee.umbc.edu/~sjordan2/", $curr_register_email);
    $curr_register_email = str_replace("{{umbcid}}", $row["umbc_id"], $curr_register_email);
    $curr_register_email = str_replace("{{umbcnameid}}", $row["umbc_name_id"], $curr_register_email);
    $curr_register_email = str_replace("{{role}}", $row["role"], $curr_register_email);
    $curr_register_email = str_replace("{{section}}", $row["section"], $curr_register_email);
    $curr_register_email .= "\n\n";
    foreach($instructor_array as $signature) {
        $curr_register_email .= $signature;
        $curr_register_email .= "\n";
    }
    $curr_register_email .= "\n";

    $email_recipient = $row["umbc_name_id"] . "@umbc.edu";
    $email_subject = "Welcome to CMSC 201!";
    $email_headers = "From: UMBC Submit System <umbc-submit@csee.umbc.edu>" . "\r\n" . 'X-Mailer: PHP/' . phpversion();

    $error_host = "-f" . $submit_system_admin . "@umbc.edu";
    $mail_status = mail($email_recipient, $email_subject, $curr_register_email, $email_headers, $error_host);
    if($mail_status === false) {
        $error_occurred = true;
        array_push($error_array, $row["umbc_name_id"]);
    } else {
        $curr_user = $row["umbc_id"];
        $update_email_sent_sql = "UPDATE Users SET email_sent = '1' WHERE umbc_id = '$curr_user'";
        $update_email_sent_result = $conn->query($update_email_sent_sql);
    }
}
if($error_occurred) {
    echo "An error occurred when emailing the following users: " . print_r($error_array);
} else {
    if($number_students === 0) {
        echo "ERROR: There are no new students to email!";
    } else {
        echo "SUCCESS: Sent emails to " . $number_students . " new users!";
    }
}