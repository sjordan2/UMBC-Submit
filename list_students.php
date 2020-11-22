<style>
    button.delete_button {
        text-align: center;
        color: #ff0000;
        background-color: #ffffff;
        padding: 5px;
        cursor: pointer;
        border: solid 2px;
    }
    button.delete_button:hover {
        background-color: #ff0000;
        color: white;
        border: solid 2px;
    }
    table,th,td {
        border : 1px solid black;
        border-collapse: collapse;
    }
    th, td {
        padding: 5px;
    }
    h {font-size: 30px}
    p.errorMessage {
        display: none;
    }
    body {
        background-color: #F1C04B;
    }
</style>

<?php

include 'db_sql.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

	// Check connection
	if ($conn->connect_error) {
  	    die("Connection failed: " . $conn->connect_error);
	}

    $sql_list = "SELECT lastname, firstname, umbc_name_id, umbc_id, role, section FROM Students";
    $result_list = $conn->query($sql_list);
    echo "<h id='currNumStudents'>Number of Students: $result_list->num_rows</h>";
    echo "<p id='messageFeedback' class='errorMessage'></p>";
    echo "<table id='student_table'>";
    echo "<tr id='header_row'><th>Last Name</th><th>First Name</th><th>Name ID</th><th>Campus ID</th><th>Role</th><th>Discussion Section</th><th>Actions</th></tr>";
    if ($result_list->num_rows > 0) {
        // output data of each row
        $counter = 0;
        while($row = $result_list->fetch_assoc()) {
            $student_id = 'student_row_' . $row['umbc_name_id'];
            echo "<tr id=$student_id>";
            foreach($row as $element) {
                echo "<td>";
                echo $element;
                echo "</td>";
            }
            $deleteid = "del_" . $row['umbc_name_id'];
            echo "<td><button class='delete_button' id=$deleteid onclick='deleteStudent(this)'>Remove Student</button></td>";
            echo "</tr>";
            $counter++;
        }
        echo "</table>";
}
    ?>
<script>
    function deleteStudent(button) {
        let ajaxQuery = new XMLHttpRequest();
        let student_name_id = button.id.split("_")[1]; // Gets UMBC Name Id of Student
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let studentRowId = "student_row_" + student_name_id;
                let studentRow = document.getElementById(studentRowId);
                studentRow.parentNode.removeChild(studentRow);
                let studentTable = document.getElementById("student_table");
                let newNumStudents = studentTable.rows.length - 1;
                let headerNumStudents = document.getElementById("currNumStudents");
                headerNumStudents.innerHTML = "Number of Students: " + newNumStudents;
                let messageFeedback = document.getElementById('messageFeedback');
                let prefix = this.responseText;
                prefix = prefix.substring(0, 5);
                if(prefix === "ERROR") {
                    messageFeedback.style.color = "#ff0000";
                } else {
                    messageFeedback.style.color = "#3f9b42";
                }
                messageFeedback.innerText = this.responseText;
                messageFeedback.style.display = 'block';
            }
        };
        ajaxQuery.open("POST", "delete_student.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("sname=" + student_name_id);
    }
</script>
