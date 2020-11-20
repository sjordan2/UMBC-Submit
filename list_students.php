<?php
session_start();

include 'db_sql.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

	// Check connection
	if ($conn->connect_error) {
  	die("Connection failed: " . $conn->connect_error);
	}
    $sql_check = "SHOW TABLES LIKE 'Students';";
    $result_check = $conn->query($sql_check);
    if($result_check->num_rows == 0) {
        $_SESSION["ListStudentsMessage"] = "There is no 'Students' table!";
        header('Location: main.php');
    } else {
        echo "<style>table, th, td {border: 1px solid #000000;border-collapse: collapse;}</style>";
        echo "<table>";
        echo "<tr><th>Last Name</th><th>First Name</th><th>Name ID</th><th>Campus ID</th><th>Role</th><th>Discussion Section</th></tr>";
        $sql_list = "SELECT lastname, firstname, umbc_name_id, umbc_id, role, section FROM Students";
        $result_list = $conn->query($sql_list);
        if ($result_list->num_rows > 0) {
            // output data of each row
            while($row = $result_list->fetch_assoc()) {
                echo "<tr>";
                foreach($row as $element) {
                    echo "<td>";
                    echo $element;
                    echo "</td>";
                }
                echo "</tr>";
            }
        echo "</table>";
    }
}
?>