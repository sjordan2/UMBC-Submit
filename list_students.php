<?php
session_start();

include 'db_sql.php';
echo "<style>
.delete_button {
    text-align: center;
    color: #ff0000;
    background-color: #ffffff;
    padding: 5px;
    cursor: pointer;
}
.delete_button:hover {
    background-color: #ff0000;
    color: white;
}
table {border: 1px solid #000000;}
th {border: 1px solid #000000;}
td {border: 1px solid #000000; text-align: center}
tr {border: 1px solid #000000;}
</style>
";
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
        echo "<table>";
        echo "<tr><th>Last Name</th><th>First Name</th><th>Name ID</th><th>Campus ID</th><th>Role</th><th>Discussion Section</th><th>Actions</th></tr>";
        $sql_list = "SELECT lastname, firstname, umbc_name_id, umbc_id, role, section FROM Students";
        $result_list = $conn->query($sql_list);
        if ($result_list->num_rows > 0) {
            // output data of each row
            $counter = 0;
            while($row = $result_list->fetch_assoc()) {
                echo "<tr>";
                foreach($row as $element) {
                    echo "<td>";
                    echo $element;
                    echo "</td>";
                }
                echo "<td><button class='delete_button', id='delbutton' . $counter>Remove Student</button></td>";

                echo "</tr>";
                $counter++;
            }
        echo "</table>";
    }
}