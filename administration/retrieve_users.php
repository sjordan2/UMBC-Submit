<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ensureUsersTableCreation($conn);

$sql_list = "SELECT umbc_id, lastname, firstname, umbc_name_id, section, role, status FROM Users";
$result_list = $conn->query($sql_list);
echo "<h id='currNumUsers'>Total Number of Database Entries: $result_list->num_rows</h>";
echo "<input type='text' id='searchUsers' onkeyup='updateUsersTable()' placeholder='Enter search term here'><br>";
echo "<p id='messageFeedback' class='errorMessage'></p>";
echo "<table id='user_table'>";
$id_list = [ "umbc_id", "lastname", "firstname", "umbc_name_id", "section", "role", "status"];
echo "<tr id='header_row'><th>Campus ID</th><th>Last Name</th><th>First Name</th><th>Name ID</th><th>Discussion Section</th><th>Role</th><th>Status</th><th>Actions</th></tr>";
if ($result_list->num_rows > 0) {
    // output data of each row
    $counter = 0;
    while($row = $result_list->fetch_assoc()) {
        $student_id = $row['umbc_id'];
        $row_id = 'user_row_' . $student_id;
        echo "<tr id=$row_id>";
        for($colNum = 0; $colNum < count($row); $colNum++) {
            $element = $row[$id_list[$colNum]];
            $element_id = $id_list[$colNum] . "_" . $student_id;
            if($element == "Active") {
                echo "<td bgcolor='#006400' id=$element_id>";
            } else if($element == "Dropped") {
                echo "<td bgcolor='red' id=$element_id>";
            } else {
                echo "<td id=$element_id>";
            }
            $text_id = $element_id . "_element";
            echo "<p id=$text_id>";
            echo $element;
            echo "</p>";
            echo "</td>";
        }
        $deleteid = "del_" . $row['umbc_id'];
        $editid = "edit_" . $row['umbc_id'];
        echo "<td><button class='edit_button' id=$editid onclick='editUser(this)'>Edit User</button>
                <button class='delete_button' id=$deleteid onclick='deleteUser(this)'>Remove from Database</button></td>";
        echo "</tr>";
        $counter++;
    }
    echo "</table>";
}