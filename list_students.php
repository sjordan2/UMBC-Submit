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
    button.save_button {
        text-align: center;
        color: #1644b7;
        background-color: #ffffff;
        padding: 5px;
        cursor: pointer;
        border: solid 2px;
    }
    button.save_button:hover {
        background-color: #1644b7;
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
        vertical-align: middle;
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

include 'sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

	// Check connection
	if ($conn->connect_error) {
  	    die("Connection failed: " . $conn->connect_error);
	}
    $sql_list = "SELECT umbc_id, lastname, firstname, umbc_name_id, section, role, status FROM Students";
    $result_list = $conn->query($sql_list);
    echo "<h id='currNumStudents'>Total Number of Database Entries: $result_list->num_rows</h>";
    echo "<input type='text' id='searchStudents' onkeyup='updateStudentsTable()' placeholder='Enter search term here'><br>";
    echo "<p id='messageFeedback' class='errorMessage'></p>";
    echo "<table id='student_table'>";
    $id_list = [ "umbc_id", "lastname", "firstname", "umbc_name_id", "section", "role", "status"];
    echo "<tr id='header_row'><th>Campus ID</th><th>Last Name</th><th>First Name</th><th>Name ID</th><th>Discussion Section</th><th>Role</th><th>Status</th><th>Actions</th></tr>";
    if ($result_list->num_rows > 0) {
        // output data of each row
        $counter = 0;
        while($row = $result_list->fetch_assoc()) {
            $student_id = $row['umbc_id'];
            $row_id = 'student_row_' . $student_id;
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
            echo "<td><button class='delete_button' id=$deleteid onclick='deleteStudent(this)'>Remove from Database</button>
                        <button class='edit_button' id=$editid onclick='editStudent(this)'>Edit Student</button></td>";
            echo "</tr>";
            $counter++;
        }
        echo "</table>";
}
    ?>
<script>
    function deleteStudent(button) {
        let student_campus_id = button.id.split("_")[1]; // Gets UMBC Campus Id of Student
        if(confirm("Are you sure you want to delete this student (" + student_campus_id + ")?")) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let studentRowId = "student_row_" + student_campus_id;
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
            ajaxQuery.send("student_id=" + student_campus_id);
        }
    }
    function editStudent(button) {
        button.className = "save_button";
        button.innerText = "Save Changes";
        button.onclick = function () {updateEditedStudent(button)};
        let student_campus_id = button.id.split("_")[1]; // Gets UMBC Campus Id of Student
        let inputAttributeList = ["lastname_", "firstname_", "umbc_name_id_", "section_"];
        for (let counter = 0; counter < inputAttributeList.length; counter++) {
            let oldText = document.getElementById(inputAttributeList[counter] + student_campus_id + "_element");
            let newText = document.createElement("input");
            newText.setAttribute("value", oldText.innerText);
            newText.style.fontSize = "20px";
            newText.style.textAlign = 'center';
            newText.style.width = oldText.offsetWidth.toString();
            newText.id = inputAttributeList[counter] + student_campus_id + "_element";
            oldText.replaceWith(newText);
        }
        let oldRole = document.getElementById("role_" + student_campus_id + "_element");
        let newRole = document.createElement("select");

        let instructorOption = document.createElement("option");
        instructorOption.text = "Instructor";
        let taOption = document.createElement("option");
        taOption.text = "TA";
        let studentOption = document.createElement("option");
        studentOption.text = "Student";

        if(oldRole.innerText === "Instructor") {
            newRole.add(instructorOption);
            newRole.add(taOption);
            newRole.add(studentOption);
        } else if(oldRole.innerText === "TA") {
            newRole.add(taOption);
            newRole.add(studentOption);
            newRole.add(instructorOption);
        } else {
            newRole.add(studentOption);
            newRole.add(taOption);
            newRole.add(instructorOption);
        }

        newRole.style.fontSize = "20px";
        newRole.style.textAlign = 'center';
        newRole.style.width = oldRole.offsetWidth.toString();
        newRole.id = "role_" + student_campus_id + "_element";
        oldRole.replaceWith(newRole);

        let oldStatus = document.getElementById("status_" + student_campus_id + "_element");
        let newStatus = document.createElement("select");

        let activeOption = document.createElement("option");
        activeOption.text = "Active";
        let droppedOption = document.createElement("option");
        droppedOption.text = "Dropped";

        if(oldStatus.innerText === "Active") {
            newStatus.add(activeOption);
            newStatus.add(droppedOption);
        } else {
            newStatus.add(droppedOption);
            newStatus.add(activeOption);
        }
        newStatus.style.fontSize = "20px";
        newStatus.style.textAlign = 'center';
        newStatus.style.width = oldStatus.offsetWidth.toString();
        newStatus.id = "status_" + student_campus_id + "_element";
        oldStatus.replaceWith(newStatus);

    }

    function updateEditedStudent(button) {
        let student_campus_id = button.id.split("_")[1]; // Gets UMBC Campus Id of Student
        button.className = "edit_button";
        button.innerText = "Edit Student";
        let newLastName = document.getElementById("lastname_" + student_campus_id + "_element").value;
        let newFirstName = document.getElementById("firstname_" + student_campus_id + "_element").value;
        let newNameID = document.getElementById("umbc_name_id_" + student_campus_id + "_element").value;
        let newDiscussionSection = document.getElementById("section_" + student_campus_id + "_element").value;
        let newRole = document.getElementById("role_" + student_campus_id + "_element").value;
        let newStatus = document.getElementById("status_" + student_campus_id + "_element").value;
        button.onclick = function () {editStudent(button)};
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
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

                finishEditingStudent(student_campus_id);
            }
        }
        ajaxQuery.open("POST", "edit_student.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("lname=" + newLastName + "&fname=" + newFirstName + "&nameID=" + newNameID
                    + "&disc=" + newDiscussionSection + "&role=" + newRole + "&status=" + newStatus + "&cID=" + student_campus_id);
    }

    function finishEditingStudent(campus_id) {
        let inputAttributeList = ["lastname_", "firstname_", "umbc_name_id_", "section_"];
        for (let counter = 0; counter < inputAttributeList.length; counter++) {
            let oldText = document.getElementById(inputAttributeList[counter] + campus_id + "_element");
            let newText = document.createElement("p");
            newText.id = oldText.id;
            newText.innerText = oldText.value;
            oldText.replaceWith(newText)
        }

        let oldRole = document.getElementById("role_" + campus_id + "_element");
        let newRole = document.createElement("p");
        newRole.id = oldRole.id;
        newRole.innerText = oldRole.value;
        oldRole.replaceWith(newRole);

        let oldStatus = document.getElementById("status_" + campus_id + "_element");
        let newStatus = document.createElement("p");
        newStatus.id = oldStatus.id;
        newStatus.innerText = oldStatus.value;
        oldStatus.replaceWith(newStatus);
        let statusBackground = document.getElementById("status_" + campus_id);
        if(newStatus.innerText === "Active") {
            statusBackground.style.backgroundColor = '#006400';
        } else {
            statusBackground.style.backgroundColor = 'red';
        }

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
            for(let elemCounter = 0; elemCounter < elementList.length - 4; elemCounter++) {
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
