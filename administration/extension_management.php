<style>
    hr.divider {
        border-top: 2px solid black;
    }
    p.title {
        font-size: xxx-large;
        text-align: center;
        margin-bottom: 5px;
        margin-top: 0px;
    }
    body {
        background-color: #F1C04B;
    }
    select {
        font-size: large;
    }
    h {
        font-size: 30px
    }
    table {
        table-layout: fixed;
    }
    table,th,td,tr {
        border : 2px solid black;
        border-collapse: collapse;
        width: 100%;
    }
    tr:hover {
        background-color: #858585;
    }
    th, td {
        padding: 5px;
        text-align: center;
        vertical-align: middle;
        overflow: auto;
        font-size: 20px;
    }
    button.utility {
        background-color: white;
        color: #0073ca;
        display: inline-block;
        padding: 10px 15px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        align-content: center;
    }
    button.utility:hover {
        background-color: #0073ca;
        color: white;
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
        margin-bottom: 5px;
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
        horiz-align: center;
        color: #1644b7;
        margin-bottom: 5px;
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
    input.submitClass {
        background-color: white;
        color: #5e00ca;
        display: inline-block;
        padding: 5px 15px;
        margin-top: 5px;
        cursor: pointer;
        border: 2px solid;
        text-align: center;
        align-content: center;
    }
    input.submitClass:hover {
        background-color: #5e00ca;
        color: white;
    }
    #addNewExtension {
        position: absolute;
        top:89px;
        right: 15px;
    }
    #topExtensionDiv {
        padding-bottom: 5px;
        border: thin solid black;
    }
    p.errorMessage {
        text-align: center;
        display: none;
        color: red;
        margin: 0px;
    }
    p.information {
        font-size: large;
        margin: 3px;
        display: none;
    }
    form {
        display: none;
        text-align: center;
        margin-top: 5px;
        margin-bottom: 10px;
    }
</style>
<body onload='javascript:getStudentsFromDatabase()'>
<div>
<p class="title">Extension Management</p>
<hr class="divider">
<?php
require_once "../sql_functions.php";
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ensureExtensionsTableCreation($conn);

$sql_assignmentlist = "SELECT assignment_name FROM Assignments";
$result_list = $conn->query($sql_assignmentlist);
echo "<div id='topExtensionDiv'>";
echo "<label for='assignment_dropdown' style='font-size: large; padding-left: 5px;'>Select an Assignment to View Extensions</label><br>";
echo "<select id='assignment_dropdown' onchange='javascript:processSelectedAssignment(true)' style='margin-left: 5px;'>";
echo "<option selected value>Select an Assignment...</option>";
while($row = $result_list->fetch_assoc()) {
    $assignment_name = $row['assignment_name'];
    $assignment_id = str_replace(" ", "~", $assignment_name);
    echo "<option id=$assignment_id>$assignment_name</option>";
}
echo "</select>";
?>
    <p class="information" id="assignmentDueDate"></p>
<button id="addNewExtension" class="utility" onclick="showNewExtensionForm()">
    Grant an Extension
</button>
    <form method="post" action="javascript:validateForm()" id="newextension_form">
        <label for="studentSearch">Student (Type to Search)</label><br>
        <input list="studentsList" id="studentSearch" name="studentSearch" onkeyup="showSearchedStudents()"><br>
        <datalist id="studentsList">
        </datalist>
        <label for="duedate">New Due Date</label><br>
        <input type="datetime-local" id="duedate" name="duedate"><br>

        <input type="submit" class="submitClass">
    </form>
    <p id="inputErrorMessage" class=errorMessage></p>
    <p id="newExtensionMessage" class=errorMessage></p>
</div>
<div id="table_div">
</div>
</body>

<script>
    function processSelectedAssignment(clearExtensionMessages) {
        let dropdown = document.getElementById("assignment_dropdown");
        if(clearExtensionMessages) {
            document.getElementById("newExtensionMessage").style.display = 'none';
            document.getElementById("inputErrorMessage").style.display = 'none';
        }
        if(dropdown.value !== "") {
            getAssignmentDueDate(dropdown.value);
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("table_div").innerHTML = this.responseText;
                }
            };
            ajaxQuery.open("POST", "retrieve_extensions.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + dropdown.value);
        } else {
            document.getElementById("table_div").innerHTML = "";
            document.getElementById("newextension_form").style.display = 'none';
            document.getElementById("assignmentDueDate").style.display = 'none';
        }
    }

    function getAssignmentDueDate(assignment) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("assignmentDueDate").innerHTML = "Current Course-Wide Due Date: " + this.responseText.bold();
                document.getElementById("assignmentDueDate").style.display = 'block';
            }
        };
        ajaxQuery.open("POST", "get_due_date.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignment);
    }

    function validateForm() {
        resetFormMessage();
        let isValidForm = true;
        if(document.getElementById("studentSearch").value === "") {
            document.getElementById("inputErrorMessage").innerText = "You must enter a student!"
            document.getElementById("inputErrorMessage").style.display = 'block';
            isValidForm = false;
        }
        if(document.getElementById("duedate").value === "" && isValidForm) {
            document.getElementById("inputErrorMessage").innerText = "You must enter a valid date!";
            document.getElementById("inputErrorMessage").style.display = 'block';
            isValidForm = false;
        }

        if(isValidForm) {
            let timeZoneOffset = (new Date()).getTimezoneOffset() * 60000;
            let dateCreated = new Date(Date.now() - timeZoneOffset).toISOString().slice(0, 19).replace('T', ' ');
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let prefix = this.responseText;
                    prefix = prefix.substring(0, 5);
                    if(prefix !== "ERROR") {
                        document.getElementById("newExtensionMessage").style.color = "#3f9b42";
                        document.getElementById("studentSearch").value = "";
                        document.getElementById("duedate").value = "";
                    }
                    document.getElementById("newExtensionMessage").innerText = this.responseText;
                    document.getElementById("newExtensionMessage").style.display = 'block';

                    processSelectedAssignment(false);
                }
            };
            ajaxQuery.open("POST", "grant_extension.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignment=" + document.getElementById("assignment_dropdown").value + "&dateCreated=" + dateCreated
                + "&dateDue=" + document.getElementById("duedate").value + "&student=" + document.getElementById("studentSearch").value);
        }
    }

    function showNewExtensionForm() {
        if(document.getElementById("assignment_dropdown").value === "") {
            document.getElementById("newExtensionMessage").innerText = "You must select an assignment first!";
            document.getElementById("newExtensionMessage").style.display = 'block';
        } else {
            document.getElementById("newExtensionMessage").style.display = 'none';
            if(document.getElementById("newextension_form").style.display === 'block') {
                document.getElementById("newextension_form").style.display = 'none';
            } else {
                document.getElementById("newextension_form").style.display = 'block';
            }
        }
    }

    function resetFormMessage() {
        document.getElementById("newExtensionMessage").style.display = 'none';
    }

    function showSearchedStudents() {
        let currSearchString = document.getElementById("studentSearch").value.toLowerCase();
        let studentList = JSON.parse(sessionStorage.getItem("studentList"));
        let numResults = 0;
        let innerSearchHTML = "";
        let counter = 0;
        while(counter < studentList.length && numResults < 10) {
            if(studentList[counter].toLowerCase().includes(currSearchString)) {
                innerSearchHTML += '<option value="' + studentList[counter] + '" />';
                numResults++;
            }
            counter++;
        }
        document.getElementById("studentsList").innerHTML = innerSearchHTML;
    }

    function getStudentsFromDatabase() {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                sessionStorage.setItem("studentList", JSON.stringify(JSON.parse(this.responseText)));
            }
        };
        ajaxQuery.open("POST", "get_searchable_students.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send();
    }
    function editExtension(button) {
        button.className = "save_button";
        button.innerText = "Save Changes";
        button.onclick = function () {updateEditedExtension(button)};

        let user_campus_id = button.id.split("_")[1]; // Gets UMBC Campus Id of User
        let currExtElement = document.getElementById("new_due_date_" + user_campus_id + "_element");
        let newExtElement = document.createElement("input");
        newExtElement.setAttribute("type", "datetime-local");
        newExtElement.value = parseFancyToDateTime(currExtElement.innerText);
        newExtElement.style.fontSize = "20px";
        newExtElement.style.textAlign = 'center';
        newExtElement.style.width = currExtElement.offsetWidth.toString();
        newExtElement.id = "new_due_date_" + user_campus_id + "_element";
        currExtElement.replaceWith(newExtElement);
    }

    function updateEditedExtension(button) {
        let user_campus_id = button.id.split("_")[1]; // Gets UMBC Campus Id of User
        let currAssignment = document.getElementById("assignment_dropdown").value;
        let newExtElement = document.getElementById("new_due_date_" + user_campus_id + "_element").value;
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let messageFeedback = document.getElementById('newExtensionMessage');
                let prefix = this.responseText;
                prefix = prefix.substring(0, 5);
                if(prefix === "ERROR") {
                    messageFeedback.style.color = "#ff0000";
                } else {
                    messageFeedback.style.color = "#3f9b42";
                    finishEditingExtension(user_campus_id);
                    button.className = "edit_button";
                    button.innerText = "Edit Extension";
                    button.onclick = function () {editExtension(button)};
                }
                messageFeedback.innerText = this.responseText;
                messageFeedback.style.display = 'block';

            }
        }
        ajaxQuery.open("POST", "edit_extension.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("student=" + user_campus_id + "&assignment=" + currAssignment
            + "&newDueDate=" + newExtElement);
    }

    function finishEditingExtension(user_campus_id) {
        let oldDueDate = document.getElementById("new_due_date_" + user_campus_id + "_element");
        let newDueDate = document.createElement("p");
        newDueDate.id = oldDueDate.id;
        newDueDate.innerText = parseDateTimeToFancy(oldDueDate.value);
        oldDueDate.replaceWith(newDueDate);

        let editedDueDate = new Date(parseFancyToDateTime(newDueDate.innerText)).getTime();
        let timeZoneOffset = (new Date()).getTimezoneOffset() * 60000;
        let currentTime = new Date(Date.now() - timeZoneOffset);
        if(editedDueDate - currentTime > 0) {
            document.getElementById("status_" + user_campus_id).innerText = "Open";
            document.getElementById("status_" + user_campus_id).style.backgroundColor = '#006400';
        } else {
            document.getElementById("status_" + user_campus_id).innerText = "Past Due";
            document.getElementById("status_" + user_campus_id).style.backgroundColor = '#ff0000';
        }
    }

    function deleteExtension(button) {
        let student_id = button.id.split("_")[1]; // Gets UMBC Campus Id of User
        let assignment = document.getElementById("assignment_dropdown").value;
        if(confirm("Are you sure you want to delete the " + assignment + " extension for " + student_id + "?")) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let messageFeedback = document.getElementById('newExtensionMessage');
                    let prefix = this.responseText;
                    prefix = prefix.substring(0, 5);
                    if(prefix === "ERROR") {
                        messageFeedback.style.color = "#ff0000";
                    } else {
                        messageFeedback.style.color = "#3f9b42";
                    }
                    messageFeedback.innerText = this.responseText;
                    messageFeedback.style.display = 'block';
                    processSelectedAssignment(false);
                }
            };
            ajaxQuery.open("POST", "delete_extension.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("student_id=" + student_id + "&assignment=" + assignment);
        }
    }

    function parseFancyToDateTime(fancy) {
        let months = {
            'January' : '01',
            'February' : '02',
            'March' : '03',
            'April' : '04',
            'May' : '05',
            'June' : '06',
            'July' : '07',
            'August' : '08',
            'September' : '09',
            'October' : '10',
            'November' : '11',
            'December' : '12'
        }
        let new_date_format = [];
        let date_list = fancy.split(",");
        new_date_format.push(date_list[2].trim());
        new_date_format.push(months[date_list[1].trim().split(" ")[0]]);
        let dayMillisVar = date_list[1].trim().split(" ")[1];
        new_date_format.push(dayMillisVar.substring(0, dayMillisVar.length - 2));
        if(new_date_format[2].length !== 2) {
            new_date_format[2] = "0".concat(new_date_format[2]);
        }

        let timeMillisVar = date_list[3].trim().substring(0, date_list[3].trim().length - 2).split(":");
        if(date_list[3].trim()[date_list[3].trim().length - 2] === "P" && Number(timeMillisVar[0]) !== 12) {
            timeMillisVar[0] = (Number(timeMillisVar[0]) + 12).toString();
        }
        if(date_list[3].trim()[date_list[3].trim().length - 2] === "A" && Number(timeMillisVar[0]) === 12) {
            timeMillisVar[0] = "00";
        }
        return new_date_format.join("-") + "T" + timeMillisVar.join(":");
    }

    function parseDateTimeToFancy(datetime) {
        // 2020-12-19T12:20
        let months = {
            '01' : 'January',
            '02' : 'February',
            '03' : 'March',
            '04' : 'April',
            '05' : 'May',
            '06' : 'June',
            '07' : 'July',
            '08' : 'August',
            '09' : 'September',
            '10' : 'October',
            '11' : 'November',
            '12' : 'December'
        }
        let days = {
            '0': 'Sunday',
            '1': 'Monday',
            '2': 'Tuesday',
            '3': 'Wednesday',
            '4': 'Thursday',
            '5': 'Friday',
            '6': 'Saturday'
        }

        let dayOfTheWeek = new Date(datetime).getDay();
        let dateTimeSplit = datetime.split("T");
        let ymdSplit = dateTimeSplit[0].split("-");
        let hmSplit = dateTimeSplit[1].split(":");
        if(hmSplit.length === 3) {
            hmSplit = hmSplit.slice(0, -1);
        }
        let timeSuffix = "AM";
        if(Number(hmSplit[0]) >= 12) {
            hmSplit[0] = (Number(hmSplit[0]) - 12).toString();
            timeSuffix = "PM";
        }
        if(Number(hmSplit[0]) === 0) {
            hmSplit[0] = "12";
        }

        ymdSplit[2] = (Number(ymdSplit[2])).toString();


        let finalDateString = days[dayOfTheWeek] + ", " + months[ymdSplit[1]] + " " + ymdSplit[2] + findSuffix(Number(ymdSplit[2])) + ", " + ymdSplit[0]
            + ", " + hmSplit.join(":") + ":00" + timeSuffix;
        return finalDateString;
    }

    function findSuffix(day) {
        let stSuffix = [1, 21, 31];
        let ndSuffix = [2, 22];
        let rdSuffix = [3, 23];

        if(stSuffix.includes(day)) {
            return "st";
        } else if(ndSuffix.includes(day)) {
            return "nd";
        } else if(rdSuffix.includes(day)) {
            return "rd";
        } else {
            return "th";
        }
    }
</script>