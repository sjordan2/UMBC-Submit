<?php

include 'db_sql.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

	// Check connection
	if ($conn->connect_error) {
  	die("Connection failed: " . $conn->connect_error);
	}
	
	$sql = "SHOW TABLES LIKE 'Students';";
	$result = $conn->query($sql);
	if($result->num_rows > 0) {
        echo "Already done!";
	} else {
		$newtable_sql = "CREATE TABLE Students (
                        umbc_name_id VARCHAR(30) PRIMARY KEY,
                        umbc_id VARCHAR(30) NOT NULL,
                        firstname VARCHAR(30) NOT NULL,
                        lastname VARCHAR(30) NOT NULL,
                        section INT(3) NOT NULL,
                        role VARCHAR(30) NOT NULL,
                        status VARCHAR(15) NOT NULL
                        )";
		if ($conn->query($newtable_sql) === TRUE) {
            echo "'Students' table successfully created!";
        } else {
            echo "ERROR: " . $conn->error;
        }

	}