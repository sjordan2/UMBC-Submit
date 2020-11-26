<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    table {
        margin-top: 10px;
    }
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
    button.edit_button {
        text-align: center;
        color: #0d6b0d;
        background-color: #ffffff;
        padding: 5px;
        cursor: pointer;
        border: solid 2px;
    }
    button.edit_button:hover {
        background-color: #0d6b0d;
        color: white;
        border: solid 2px;
    }
    table,th,td,tr {
        border : 2px solid black;
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }
    th, td {
        padding: 5px;
        text-align: center;
        font-size: 20px;
    }
    h {
        font-size: 30px
    }
    p.errorMessage {
        display: none;
    }
    body {
        background-color: #F1C04B;
    }
    #searchStudents {
        position: absolute;
        margin-left: 20px;
        width: 40%;
        height: 35px;
        font-size: 20px;
        right: 0.5%;
    }
    tr:hover {
        background-color: #858585;
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
    $sql_list = "SELECT lastname, firstname, umbc_name_id, umbc_id, section, role, status FROM Students";
    $result_list = $conn->query($sql_list);
    echo "<h id='currNumStudents'>Total Number of Students Enrolled: $result_list->num_rows</h>";
    echo "<input type='text' id='searchStudents' onkeyup='updateStudentsTable()' placeholder='Enter search term here'><br>";
    echo "<p id='messageFeedback' class='errorMessage'></p>";
    echo "<table id='student_table'>";
    echo "<tr id='header_row'><th>Last Name</th><th>First Name</th><th>Name ID</th><th>Campus ID</th><th>Discussion Section</th><th>Role</th><th>Status</th><th>Actions</th></tr>";
    if ($result_list->num_rows > 0) {
        // output data of each row
        $counter = 0;
        while($row = $result_list->fetch_assoc()) {
            $student_id = 'student_row_' . $row['umbc_name_id'];
            echo "<tr id=$student_id>";
            foreach($row as $element) {
                if($element == "Active") {
                    echo "<td bgcolor='#006400'>";
                } else if($element == "Dropped") {
                    echo "<td bgcolor='red'>";
                } else {
                    echo "<td>";
                }
                echo $element;
                echo "</td>";
            }
            $deleteid = "del_" . $row['umbc_name_id'];
            $editid = "edit_" . $row['umbc_name_id'];
            echo "<td><button class='delete_button' id=$deleteid onclick='deleteStudent(this)'>Remove from Database</button>
                        <button class='edit_button' id=$editid onclick='editStudent(this)'>Edit</button></td>";
            echo "</tr>";
            $counter++;
        }
        echo "</table>";
}
    ?>
<script>
    function deleteStudent(button) {
        let student_name_id = button.id.split("_")[1]; // Gets UMBC Name Id of Student
        if(confirm("Are you sure you want to delete this student (" + student_name_id + ")?")) {
            let ajaxQuery = new XMLHttpRequest();
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
    }
    function editStudent(button) {
        console.log(button.id);
    }

    function updateStudentsTable() {
        resetStudentsTable();
        let searchBar = document.getElementById('searchStudents');
        let currSearchTerm = searchBar.value.toLowerCase(); // Case insensitive searching :)
        let studentsTable = document.getElementById('student_table');
        let rowList = studentsTable.getElementsByTagName('tr'); // Returns list of rows to iterate through
        for(let rowCounter = 1; rowCounter < rowList.length; rowCounter++) {
            let rowHasElement;
            let elementList = rowList[rowCounter].getElementsByTagName('td'); // Returns list of elements to iterate through
            for(let elemCounter = 0; elemCounter < elementList.length - 3; elemCounter++) {
                if(elementList[elemCounter].innerText.toString().toLowerCase().includes(currSearchTerm)) {
                    rowHasElement = true;
                }
            }
            if(!rowHasElement) {
                rowList[rowCounter].style.display = 'none';
            }
        }
    }
    function resetStudentsTable() {
        let studentsTable = document.getElementById('student_table');
        let rowList = studentsTable.getElementsByTagName('tr');
        for(let rowCounter = 1; rowCounter < rowList.length; rowCounter++) {
            rowList[rowCounter].style.display = '';
        }

    }
</script>
