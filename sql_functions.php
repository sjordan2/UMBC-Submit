<?php

require_once 'db_sql.php';
function addStudentToDatabase($nameID, $campusID, $firstName, $lastName, $discussion, $role, $conn, $verbose) {

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $newstudent_sql = "INSERT INTO Students (umbc_name_id, umbc_id, firstname, lastname, section, role, status)
                    VALUES ('$nameID', '$campusID', '$firstName',
                   '$lastName', '$discussion', '$role', 'Active')";
    if ($conn->query($newstudent_sql) === TRUE) {
        $success_message = "SUCCESS: " . $firstName . " " . $lastName
            . " (" . $nameID . ") has been added to the database!";
        if($verbose === true) {
            echo $success_message;
        }
    } else {
        $error_message = "ERROR: " . $conn->error;
        echo $error_message;
    }
}