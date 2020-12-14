<?php

require_once 'db_sql.php';
function addUserToDatabase($nameID, $campusID, $firstName, $lastName, $discussion, $role, $conn, $verbose) {

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $newstudent_sql = "INSERT INTO Users (umbc_name_id, umbc_id, firstname, lastname, section, role, status)
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
function getFullNameFromCampusID($campusID, $conn) {
    $campusID_sql = "SELECT lastname, firstname FROM Users WHERE umbc_id = '$campusID'";
    $result = $conn->query($campusID_sql);
    if($result->num_rows != 1) {
        return "ERROR";
    } else {
        $row = $result->fetch_assoc();
        return $row['firstname'] . " " . $row['lastname'];
    }
}
function getEnrollment($campusID, $conn) {
    $campusID_sql = "SELECT role FROM Users WHERE umbc_id = '$campusID'";
    $result = $conn->query($campusID_sql);
    if($result->num_rows < 1) {
        return false;
    } else {
        $row = $result->fetch_assoc();
        return $row['role'];
    }
}

function ensureUsersTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Users';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Users (
                        umbc_id VARCHAR(30) PRIMARY KEY,
                        umbc_name_id VARCHAR(30) NOT NULL,
                        firstname VARCHAR(30) NOT NULL,
                        lastname VARCHAR(30) NOT NULL,
                        section INT(3) NOT NULL,
                        role VARCHAR(30) NOT NULL,
                        status VARCHAR(15) NOT NULL
                        )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureAssignmentsTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Assignments';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Assignments (
                        assignment_name VARCHAR(30) PRIMARY KEY,
                        date_assigned DATETIME NOT NULL,
                        date_due DATETIME NOT NULL,
                        max_points INT(4) NOT NULL,
                        extra_credit INT(4) NOT NULL
                          )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureExtensionsTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Extensions';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Extensions (
                        id_number INT PRIMARY KEY AUTO_INCREMENT,
                        student_id VARCHAR(30) NOT NULL,
                        CONSTRAINT student_in_database
                        FOREIGN KEY (student_id) 
                            REFERENCES Users (umbc_id)
                            ON DELETE CASCADE 
                            ON UPDATE CASCADE,
                        assignment VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_in_database
                        FOREIGN KEY (assignment) 
                            REFERENCES Assignments (assignment_name)
                            ON DELETE CASCADE 
                            ON UPDATE CASCADE,
                        date_granted DATETIME NOT NULL,
                        new_due_date DATETIME NOT NULL
                          )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}