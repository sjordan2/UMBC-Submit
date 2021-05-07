<?php
require_once '../sql_functions.php';
require_once '../config.php';
require_once $php_nested_cas_path . 'CAS.php';
phpCAS::setDebug();
phpCAS::setVerbose(true);
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
phpCAS::setNoCasServerValidation(); // FIX THIS BUCKO
phpCAS::forceAuthentication();

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_REQUEST['logout'])) {
    header('Location: https://www.csee.umbc.edu');
//    phpCAS::logout();
}

if(getEnrollment(phpCAS::getUser(), $conn) === false) {
    header('Location: ../home.php');
    exit();
}

if(getEnrollment(phpCAS::getUser(), $conn) !== "Instructor") {
    header('Location: ../home.php');
    exit();
}
?>

<style>
    table {
        margin-top: 10px;
    }
    input.submitClass {
        background-color: white;
        color: #5e00ca;
        display: inline-block;
        padding: 5px 15px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        text-align: center;
        align-content: center;
    }
    input.submitClass:hover {
        background-color: #5e00ca;
        color: white;
    }
    button.utility {
        background-color: white;
        color: #0d6b0d;
        display: inline-block;
        padding: 10px 15px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        align-content: center;
    }
    button.utility:hover {
        background-color: #0d6b0d;
        color: white;
    }
    button.delete_button {
        text-align: center;
        color: #ff0000;
        background-color: #ffffff;
        padding: 5px;
        cursor: pointer;
        border: solid 2px;
        margin-bottom: 5px;
    }
    button.delete_button_disabled {
        text-align: center;
        color: #a50000;
        background-color: #aeaeae;
        padding: 5px;
        border: solid 2px;
        margin-bottom: 5px;
    }
    button.delete_button:hover {
        background-color: #ff0000;
        color: white;
        border: solid 2px;
    }
    button.edit_button {
        text-align: center;
        color: #0073ca;
        background-color: #ffffff;
        padding: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        border: solid 2px;
    }
    button.edit_button:hover {
        background-color: #0073ca;
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
    table,th,td,tr {
        border : 2px solid black;
        border-collapse: collapse;
        table-layout: fixed;
    }
    th, td {
        padding: 5px;
        text-align: center;
        vertical-align: middle;
        overflow: auto;
        font-size: 20px;
    }
    tr.hidden {
        display: none;
    }

    tr.assignment {
        border-top: 5px solid black;
        border-bottom: 3px solid black;
        border-left: 5px solid black;
        border-right: 5px solid black;
    }

    tr.extension {
        border-bottom: 5px solid black;
        border-left: 5px solid black;
        border-right: 5px solid black;
    }
    h {
        font-size: 30px
    }
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
        background-color: #ABABAB;
        max-width: 100%;
    }
    #searchAssignments {
        position: absolute;
        margin-left: 20px;
        width: 40%;
        height: 35px;
        font-size: 20px;
        right: 0.5%;
    }
    tr.regular:hover {
        background-color: #858585;
    }
    form {
        display: none;
        border: thin solid black;
        margin-top: 5px;
        margin-bottom: 10px;
    }
    #makefile_upload {
        display: block;
        margin-top: 5px;
    }
    #sample_input_upload {
        display: block;
        margin-top: 5px;
    }
    p.errorMessage {
        display: none;
        color: red;
        margin: 0;
    }
    #formButtonsDiv {
        text-align: center;
    }
    input.submitClass {
        background-color: white;
        color: #5e00ca;
        display: inline-block;
        padding: 5px 15px;
        margin-top: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        text-align: center;
        align-content: center;
    }
    input.submitClass:hover {
        background-color: #5e00ca;
        color: white;
    }

</style>
<body onload="retrieveAssignments(false, null); getStudentsFromDatabase();">
<p class="title">Assignment Management</p>
<hr class="divider">
<div id="formButtonsDiv">
    <button id="viewNewAssignmentForm" class="utility" onclick="toggleNewAssignmentForm()">
        Create New Assignment ⇩
    </button><br>
    <form method="post" action="javascript:validateAssignmentsForm()" id="newassignment_form">
        <label for="assignmentname">Name of Assignment</label><br>
        <input type="text" id="assignmentname" name="assignmentname"><br>
        <p id='nameFeedback' class='errorMessage'>The assignment name cannot be empty!</p>

        <label for="maximumPoints">Total Point Value</label><br>
        <input type="text" id="maximumPoints" name="maximumPoints"><br>
        <p id='pointFeedback' class='errorMessage'>The total point value must be a non-negative integer!</p>

        <label for="extraCreditPoints">Extra Credit Points</label><br>
        <input type="text" id="extraCreditPoints" name="extraCreditPoints" value="0"><br>
        <p id='extraCreditFeedback' class='errorMessage'>The extra credit must be a non-negative integer!</p>

        <label for="duedate">Due Date</label><br>
        <input type="datetime-local" id="duedate" name="duedate" step='1'><br>
        <p id='dateFeedbackEmpty' class='errorMessage'>The assignment due date cannot be empty!</p>
        <p id='dateFeedbackInvalid' class='errorMessage'>The assignment due date must be in the future.</p>

        <input type="submit" class="submitClass">
        <p id="newAssignmentMessage" class=errorMessage></p>
    </form>
</div>
<div id="table_div">
</div>
</body>

<script>
    function retrieveAssignments(setMessageBool, responseText) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("table_div").innerHTML = this.responseText;
                if(setMessageBool === true) {
                    let messageFeedback = document.getElementById('messageFeedback');
                    let prefix = responseText;
                    prefix = prefix.substring(0, 5);
                    if(prefix === "ERROR") {
                        messageFeedback.style.color = "#ff0000";
                    } else {
                        messageFeedback.style.color = "#3f9b42";
                        document.getElementById("assignmentname").value = "";
                        document.getElementById("maximumPoints").value = "";
                        document.getElementById("extraCreditPoints").value = "0";
                        document.getElementById("duedate").value = "";
                    }
                    messageFeedback.innerText = responseText;
                    messageFeedback.style.display = 'block';
                }
            }
        };
        ajaxQuery.open("POST", "retrieve_assignments.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send();
    }

    function toggleNewAssignmentForm() {
        if(document.getElementById("newassignment_form").style.display === 'block') {
            document.getElementById("newassignment_form").style.display = 'none';
        } else {
            document.getElementById("newassignment_form").style.display = 'block';
        }
    }

    function validateAssignmentsForm() {
        resetAssignmentFormMessages();

        let isGoodForm = true;

       let assignmentName = document.getElementById("assignmentname").value;
       if(assignmentName === "") {
           document.getElementById("nameFeedback").innerText = "The assignment name cannot be empty!";
           document.getElementById("nameFeedback").style.display = 'block';
           isGoodForm = false;
       }

       if(assignmentName.includes("~") || assignmentName.includes("_")) {
           document.getElementById("nameFeedback").innerText = "The assignment name cannot contain the '~' or '_' character!";
           document.getElementById("nameFeedback").style.display = 'block';
           isGoodForm = false;
       }

        let maximumPoints = document.getElementById("maximumPoints").value;
        if(maximumPoints === "" || isNaN(maximumPoints)) {
            document.getElementById("pointFeedback").style.display = 'block';
            isGoodForm = false;
        }
        // It is an integer.. we must make sure it is non-negative
        if(Number(maximumPoints) < 0) {
            document.getElementById("pointFeedback").style.display = 'block';
            isGoodForm = false;
        }

        let extraCredit = document.getElementById("extraCreditPoints").value;
        if(extraCredit === "" || isNaN(extraCredit)) {
            document.getElementById("extraCreditFeedback").style.display = 'block';
            isGoodForm = false;
        }
        // It is an integer.. we must make sure it is non-negative
        if(Number(extraCredit) < 0) {
            document.getElementById("extraCreditFeedback").style.display = 'block';
            isGoodForm = false;
        }

       let dueDate = document.getElementById("duedate").value;
       if(dueDate === "") {
           document.getElementById("dateFeedbackEmpty").style.display = 'block';
           isGoodForm = false;
       } else {
           let dueDateObject = new Date(dueDate);
           let timeZoneOffset = (new Date()).getTimezoneOffset() * 60000;
           let currentDate = new Date(Date.now() - timeZoneOffset).toISOString().slice(0, 19).replace('T', ' ');
           if(dueDateObject.getTime() < currentDate) {
               document.getElementById("dateFeedbackInvalid").style.display = 'block';
               isGoodForm = false;
           }
       }


        if(isGoodForm === true) {
            let timeZoneOffset = (new Date()).getTimezoneOffset() * 60000;
            let dateCreated = new Date(Date.now() - timeZoneOffset).toISOString().slice(0, 19).replace('T', ' ');
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    retrieveAssignments(true, this.responseText);
                }
            };
            ajaxQuery.open("POST", "create_assignment.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("name=" + assignmentName + "&dateCreated=" + dateCreated + "&dateDue=" + dueDate
                + "&maxPoints=" + maximumPoints + "&extraCredit=" + extraCredit);
        }
    }
    function resetAssignmentFormMessages() {
        document.getElementById("nameFeedback").style.display = 'none';
        document.getElementById("pointFeedback").style.display = 'none';
        document.getElementById("extraCreditFeedback").style.display = 'none';
        document.getElementById("dateFeedbackEmpty").style.display = 'none';
        document.getElementById("dateFeedbackInvalid").style.display = 'none';
    }

    function deleteAssignment(button) {
        let assignment_name = button.id.split("_")[1]; // Gets Assignment Name
        if(confirm("Are you sure you want to delete this assignment (" + assignment_name.replace(new RegExp('~', 'g'), " ") + ")?")) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    retrieveAssignments(true, this.responseText);
                }
            };
            ajaxQuery.open("POST", "delete_assignment.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignment_name=" + assignment_name);
        }
    }
    function editAssignment(button) {
        button.className = "save_button";
        button.innerText = "Save Changes";
        button.onclick = function () {updateEditedAssignment(button)};
        let assignment_name = button.id.split("_")[1]; // Gets Assignment Name

        let oldMaxPoints = document.getElementById("max_points_" + assignment_name + "_element");
        let newMaxPoints = document.createElement("input");
        newMaxPoints.setAttribute("value", oldMaxPoints.innerText);
        newMaxPoints.style.fontSize = "20px";
        newMaxPoints.style.textAlign = 'center';
        newMaxPoints.style.width = oldMaxPoints.offsetWidth.toString();
        newMaxPoints.id = "max_points_" + assignment_name + "_element";
        oldMaxPoints.replaceWith(newMaxPoints);

        let oldECPoints = document.getElementById("extra_credit_" + assignment_name + "_element");
        let newECPoints = document.createElement("input");
        newECPoints.setAttribute("value", oldECPoints.innerText);
        newECPoints.style.fontSize = "20px";
        newECPoints.style.textAlign = 'center';
        newECPoints.style.width = oldECPoints.offsetWidth.toString();
        newECPoints.id = "extra_credit_" + assignment_name + "_element";
        oldECPoints.replaceWith(newECPoints);

        let oldDueDate = document.getElementById("date_due_" + assignment_name + "_element");
        let newDueDate = document.createElement("input");
        newDueDate.setAttribute("type", "datetime-local");
        newDueDate.setAttribute("step", "1");
        newDueDate.value = parseFancyToDateTime(oldDueDate.innerText);
        newDueDate.style.fontSize = "20px";
        newDueDate.style.textAlign = 'center';
        newDueDate.style.width = oldDueDate.offsetWidth.toString();
        newDueDate.id = "date_due_" + assignment_name + "_element";
        oldDueDate.replaceWith(newDueDate);

    }

    function updateEditedAssignment(button) {
        let assignment_name = button.id.split("_")[1]; // Gets Assignment Name
        let newMaxPoints = document.getElementById("max_points_" + assignment_name + "_element").value;
        let newECPoints = document.getElementById("extra_credit_" + assignment_name + "_element").value;
        let newDueDate = document.getElementById("date_due_" + assignment_name + "_element").value;
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
                    finishEditingAssignment(assignment_name);
                    button.className = "edit_button";
                    button.innerText = "Edit Assignment";
                    button.onclick = function () {editAssignment(button)};
                }
                messageFeedback.innerText = this.responseText;
                messageFeedback.style.display = 'block';
            }
        }
        ajaxQuery.open("POST", "edit_assignment.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("maxPoints=" + newMaxPoints + "&ecPoints=" + newECPoints
                        + "&dueDate=" + newDueDate + "&assignmentName=" + assignment_name);
    }

    function finishEditingAssignment(assignment_name) {
        let inputAttributeList = ["max_points_", "extra_credit_"];
        for (let counter = 0; counter < inputAttributeList.length; counter++) {
            let oldText = document.getElementById(inputAttributeList[counter] + assignment_name + "_element");
            let newText = document.createElement("p");
            newText.id = oldText.id;
            newText.innerText = oldText.value;
            oldText.replaceWith(newText);
        }
        let oldDueDate = document.getElementById("date_due_" + assignment_name + "_element");
        let newDueDate = document.createElement("p");
        newDueDate.id = oldDueDate.id;
        newDueDate.innerText = parseDateTimeToFancy(oldDueDate.value);
        oldDueDate.replaceWith(newDueDate);

        let editedDueDate = new Date(parseFancyToDateTime(newDueDate.innerText)).getTime();
        let timeZoneOffset = (new Date()).getTimezoneOffset() * 60000;
        let currentTime = new Date(Date.now() - timeZoneOffset);
        if(editedDueDate - currentTime > 0) {
            document.getElementById("status_" + assignment_name).innerText = "Open";
            document.getElementById("status_" + assignment_name).style.backgroundColor = '#006400';
        } else {
            document.getElementById("status_" + assignment_name).innerText = "Closed";
            document.getElementById("status_" + assignment_name).style.backgroundColor = '#ff0000';
        }
    }

    function updateAssignmentsTable() {
        resetAssignmentsTable();
        let searchBar = document.getElementById('searchAssignments');
        let currSearchTerm = searchBar.value.toLowerCase(); // Case insensitive searching :)
        let usersTable = document.getElementById('assignments_table');
        let rowList = usersTable.getElementsByTagName('tr'); // Returns list of rows to iterate through
        for(let rowCounter = 1; rowCounter < rowList.length; rowCounter++) {
            let rowHasElement;
            let elementList = rowList[rowCounter].getElementsByTagName('td'); // Returns list of elements to iterate through
            if(elementList[0].innerText.toString().toLowerCase().includes(currSearchTerm)) {
                rowHasElement = true;
            }
            if(!rowHasElement) {
                rowList[rowCounter].style.display = 'none';
            }
        }
    }
    function resetAssignmentsTable() {
        let usersTable = document.getElementById('assignments_table');
        let rowList = usersTable.getElementsByTagName('tr');
        for(let rowCounter = 1; rowCounter < rowList.length; rowCounter++) {
            rowList[rowCounter].style.display = '';
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
        // 2020-12-19T12:20:59
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
        let timeSuffix = "AM";
        if(Number(hmSplit[0]) >= 12) {
            hmSplit[0] = (Number(hmSplit[0]) - 12).toString();
            timeSuffix = "PM";
        }
        if(Number(hmSplit[0]) === 0) {
            hmSplit[0] = "12";
        }

        ymdSplit[2] = (Number(ymdSplit[2])).toString();

        return days[dayOfTheWeek] + ", " + months[ymdSplit[1]] + " " + ymdSplit[2] + findSuffix(Number(ymdSplit[2])) + ", " + ymdSplit[0]
                    + ", " + hmSplit.join(":") + timeSuffix;
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

    function manageExtensions(button) {
        let assignment_tilde = button.id.split("_")[1];
        document.getElementById("details_" + assignment_tilde + "_row").classList.add("hidden");
        document.getElementById("grading_" + assignment_tilde + "_row").classList.add("hidden");
        if(document.getElementById("extensions_" + assignment_tilde + "_row").classList.contains("hidden")) {
            let assignment = assignment_tilde.replace(new RegExp('~', 'g'), " ");
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("extensions_" + assignment_tilde + "_row").classList.remove("hidden");
                    document.getElementById("assignment_row_" + assignment_tilde).classList.add("assignment");
                    document.getElementById("buttondata_" + assignment_tilde).style.borderRight = "6px solid black";
                    document.getElementById("extensions_" + assignment_tilde + "_cell").innerHTML = this.responseText;
                }
            };
            ajaxQuery.open("POST", "retrieve_extensions.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + assignment);
        } else {
            document.getElementById("extensions_" + assignment_tilde + "_row").classList.add("hidden");
            document.getElementById("assignment_row_" + assignment_tilde).classList.remove("assignment");
            document.getElementById("buttondata_" + assignment_tilde).style.borderRight = "";
        }
    }

    function showNewExtensionForm(button) {
        let assignment_tilde = button.id.split("_")[1];
        if(document.getElementById("extform_" + assignment_tilde).style.display === 'block') {
            document.getElementById("extform_" + assignment_tilde).style.display = 'none';
        } else {
            document.getElementById("extform_" + assignment_tilde).style.display = 'block';
        }
        return false;
    }

    function showSearchedStudents(searchbar) {
        let currSearchString = searchbar.value.toLowerCase();
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

    function validateExtensionsForm(formObject) {
        let assignment_tilde = formObject.id.split("_")[1];

        document.getElementById("inputError_" + assignment_tilde).style.display = 'none';
        document.getElementById("newExtensionMessage_" + assignment_tilde).style.display = 'none';
        let isValidForm = true;
        if(document.getElementById("searchStudents_" + assignment_tilde).value === "") {
            document.getElementById("inputError_" + assignment_tilde).innerText = "You must enter a student!"
            document.getElementById("inputError_" + assignment_tilde).style.display = 'block';
            isValidForm = false;
        }
        if(document.getElementById("dueDate_" + assignment_tilde).value === "" && isValidForm) {
            document.getElementById("inputError_" + assignment_tilde).innerText = "You must enter a valid date!";
            document.getElementById("inputError_" + assignment_tilde).style.display = 'block';
            isValidForm = false;
        }

        if(isValidForm) {
            let timeZoneOffset = (new Date()).getTimezoneOffset() * 60000;
            let dateCreated = new Date(Date.now() - timeZoneOffset).toISOString().slice(0, 19).replace('T', ' ');
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let studentName = document.getElementById("searchStudents_" + assignment_tilde).value;
                    let proposedDate = document.getElementById("dueDate_" + assignment_tilde).value;
                    reloadExtensionsForAssignment(assignment_tilde, this.responseText, studentName, proposedDate);
                }
            };
            ajaxQuery.open("POST", "grant_extension.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignment=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&dateCreated=" + dateCreated
                + "&dateDue=" + document.getElementById("dueDate_" + assignment_tilde).value
                + "&student=" + document.getElementById("searchStudents_" + assignment_tilde).value);
        }
        return false;
    }

    function reloadExtensionsForAssignment(assignment_name_id, ajaxResponse, studentName, proposedDate) {
        let assignment = assignment_name_id.replace(new RegExp('~', 'g'), " ");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("extensions_" + assignment_name_id + "_cell").innerHTML = this.responseText;
                document.getElementById("extform_" + assignment_name_id).style.display = "block";
                let prefix = ajaxResponse.substring(0, 5);
                if(prefix !== "ERROR") {
                    document.getElementById("newExtensionMessage_" + assignment_name_id).style.color = "#3f9b42";
                    document.getElementById("searchStudents_" + assignment_name_id).value = "";
                    document.getElementById("dueDate_" + assignment_name_id).value = "";
                } else {
                    document.getElementById("searchStudents_" + assignment_name_id).value = studentName;
                    document.getElementById("dueDate_" + assignment_name_id).value = proposedDate;
                }
                document.getElementById("newExtensionMessage_" + assignment_name_id).innerText = ajaxResponse;
                document.getElementById("newExtensionMessage_" + assignment_name_id).style.display = 'block';
            }
        };
        ajaxQuery.open("POST", "retrieve_extensions.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignment);
    }

    function reloadDeletedExtensions(assignment_name_id, ajaxResponse) {
        let assignment = assignment_name_id.replace(new RegExp('~', 'g'), " ");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("extensions_" + assignment_name_id + "_cell").innerHTML = this.responseText;
                let prefix = this.responseText.substring(0, 5);
                if(prefix !== "ERROR") {
                    document.getElementById("edited_" + assignment_name_id).style.color = "#3f9b42";
                }
                document.getElementById("edited_" + assignment_name_id).innerText = ajaxResponse;
                document.getElementById("edited_" + assignment_name_id).style.display = 'block';
            }
        };
        ajaxQuery.open("POST", "retrieve_extensions.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignment);
    }

    function editExtension(button) {
        button.className = "save_button";
        button.innerText = "Save Changes";
        button.onclick = function () {updateEditedExtension(button)};
        let split_button_id = button.id.split("_");
        let user_assignment_combo = split_button_id[1] + "_" + split_button_id[2];
        let currExtElement = document.getElementById("new_due_date_" + user_assignment_combo + "_element");
        let newExtElement = document.createElement("input");
        newExtElement.setAttribute("type", "datetime-local");
        newExtElement.setAttribute("step", "1");
        newExtElement.value = parseFancyToDateTime(currExtElement.innerText);
        newExtElement.style.fontSize = "20px";
        newExtElement.style.textAlign = 'center';
        newExtElement.style.width = currExtElement.offsetWidth.toString();
        newExtElement.id = "new_due_date_" + user_assignment_combo + "_element";
        currExtElement.replaceWith(newExtElement);
    }

    function updateEditedExtension(button) {
        let split_button_id = button.id.split("_");
        let user_assignment_combo = split_button_id[1] + "_" + split_button_id[2];
        let currAssignment = split_button_id[2];
        let newExtElement = document.getElementById("new_due_date_" + user_assignment_combo + "_element").value;
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let messageFeedback = document.getElementById("edited_" + currAssignment);
                let prefix = this.responseText;
                prefix = prefix.substring(0, 5);
                if(prefix === "ERROR") {
                    messageFeedback.style.color = "#ff0000";
                } else {
                    messageFeedback.style.color = "#3f9b42";
                    finishEditingExtension(user_assignment_combo);
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
        ajaxQuery.send("student=" + split_button_id[1] + "&assignment=" + currAssignment.replace(new RegExp('~', 'g'), " ")
            + "&newDueDate=" + newExtElement);
    }

    function finishEditingExtension(user_assignment_combo) {
        let oldDueDate = document.getElementById("new_due_date_" + user_assignment_combo + "_element");
        let newDueDate = document.createElement("p");
        newDueDate.id = oldDueDate.id;
        newDueDate.innerText = parseDateTimeToFancy(oldDueDate.value);
        oldDueDate.replaceWith(newDueDate);

        let editedDueDate = new Date(parseFancyToDateTime(newDueDate.innerText)).getTime();
        let timeZoneOffset = (new Date()).getTimezoneOffset() * 60000;
        let currentTime = new Date(Date.now() - timeZoneOffset);
        if(editedDueDate - currentTime > 0) {
            document.getElementById("status_" + user_assignment_combo).innerText = "Open";
            document.getElementById("status_" + user_assignment_combo).style.backgroundColor = '#006400';
        } else {
            document.getElementById("status_" + user_assignment_combo).innerText = "Past Due";
            document.getElementById("status_" + user_assignment_combo).style.backgroundColor = '#ff0000';
        }
    }

    function deleteExtension(button) {
        let split_button_id = button.id.split("_");
        let student_id = split_button_id[1];
        let currAssignment = split_button_id[2];
        if(confirm("Are you sure you want to delete the " + currAssignment.replace(new RegExp('~', 'g'), " ") + " extension for " + student_id + "?")) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let messageFeedback = document.getElementById("edited_" + currAssignment);
                    let prefix = this.responseText;
                    prefix = prefix.substring(0, 5);
                    if(prefix === "ERROR") {
                        messageFeedback.style.color = "#ff0000";
                    } else {
                        messageFeedback.style.color = "#3f9b42";
                    }
                    reloadDeletedExtensions(currAssignment, this.responseText);
                }
            };
            ajaxQuery.open("POST", "delete_extension.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("student_id=" + student_id + "&assignment=" + currAssignment.replace(new RegExp('~', 'g'), " "));
        }
    }

    function manageDetails(button) {
        let assignment_tilde = button.id.split("_")[1];
        document.getElementById("extensions_" + assignment_tilde + "_row").classList.add("hidden");
        document.getElementById("grading_" + assignment_tilde + "_row").classList.add("hidden");
        if(document.getElementById("details_" + assignment_tilde + "_row").classList.contains("hidden")) {
            let assignment = assignment_tilde.replace(new RegExp('~', 'g'), " ");
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("details_" + assignment_tilde + "_row").classList.remove("hidden");
                    document.getElementById("assignment_row_" + assignment_tilde).classList.add("assignment");
                    document.getElementById("buttondata_" + assignment_tilde).style.borderRight = "6px solid black";
                    document.getElementById("details_" + assignment_tilde + "_cell").innerHTML = this.responseText;
                }
            };
            ajaxQuery.open("POST", "retrieve_details.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + assignment);
        } else {
            document.getElementById("details_" + assignment_tilde + "_row").classList.add("hidden");
            document.getElementById("assignment_row_" + assignment_tilde).classList.remove("assignment");
            document.getElementById("buttondata_" + assignment_tilde).style.borderRight = "";
        }
    }

    function setDocumentLink(button) {
        let assignment_tilde = button.id.split("_")[2];
        if(document.getElementById("link_input_" + assignment_tilde).value === "") {
            document.getElementById("link_message_" + assignment_tilde).innerText = "You must enter a link!";
            document.getElementById("link_message_" + assignment_tilde).style.display = 'block';
        } else {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let messageFeedback = document.getElementById("link_message_" + assignment_tilde);
                    let prefix = this.responseText;
                    prefix = prefix.substring(0, 5);
                    if(prefix === "ERROR") {
                        messageFeedback.style.color = "#ff0000";
                    } else {
                        messageFeedback.style.color = "#3f9b42";
                    }
                    messageFeedback.style.display = 'block';
                    messageFeedback.innerText = this.responseText;
                }
            };
            ajaxQuery.open("POST", "set_link.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&newLink=" + document.getElementById("link_input_" + assignment_tilde).value);
        }
    }

    function addNewSubmissionPart(button) {
        let assignment_tilde = button.id.split("_")[1];
        if(document.getElementById("createPartName_" + assignment_tilde).value === "") {
            document.getElementById("partMessage_" + assignment_tilde).innerText = "You must supply a part name.";
            document.getElementById("partMessage_" + assignment_tilde).style.display = 'block';
        } else {
            if(document.getElementById("createPartName_" + assignment_tilde).value.includes("~")) {
                document.getElementById("partMessage_" + assignment_tilde).innerText = "The part name cannot contain '~'!";
                document.getElementById("partMessage_" + assignment_tilde).style.display = 'block';
            } else {
                if(document.getElementById("createPartValue_" + assignment_tilde).value === "") {
                    document.getElementById("partMessage_" + assignment_tilde).innerText = "You must supply a point value for this part!";
                    document.getElementById("partMessage_" + assignment_tilde).style.display = 'block';
                } else {
                    let ajaxQuery = new XMLHttpRequest();
                    ajaxQuery.onreadystatechange = function () {
                        if (this.readyState === 4 && this.status === 200) {
                            reloadDetailsSection(assignment_tilde, this.responseText);
                        }
                    };
                    ajaxQuery.open("POST", "add_submission_part.php", true);
                    ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&partName=" + document.getElementById("createPartName_" + assignment_tilde).value + "&pointValue=" + document.getElementById("createPartValue_" + assignment_tilde).value);
                }
            }
        }
    }

    function deleteSubmissionPart(button) {
        let assignment_tilde = button.id.split("_")[1];
        if(document.getElementById("subParts_" + assignment_tilde).value === "Select a Part...") {
            document.getElementById("partMessage_" + assignment_tilde).innerText = "You do not have a part selected!";
            document.getElementById("partMessage_" + assignment_tilde).style.display = 'block';
        } else {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    reloadDetailsSection(assignment_tilde, this.responseText);
                }
            };
            ajaxQuery.open("POST", "delete_submission_part.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&partName=" + document.getElementById("subParts_" + assignment_tilde).value);
        }
    }

    function reloadDetailsSection(assignment_tilde, ajaxResponse) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("details_" + assignment_tilde + "_cell").innerHTML = this.responseText;
                let messageFeedback = document.getElementById("partMessage_" + assignment_tilde);
                let prefix = ajaxResponse.substring(0, 5);
                if(prefix === "ERROR") {
                    messageFeedback.style.color = "#ff0000";
                } else {
                    messageFeedback.style.color = "#3f9b42";
                }
                messageFeedback.innerText = ajaxResponse;
                messageFeedback.style.display = 'block';
            }
        };
        ajaxQuery.open("POST", "retrieve_details.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " "));
    }

    function updatePartSelection(select_element) {
        getSubmissionFiles(select_element);
        getSampleTestingFiles(select_element);
    }

    function getSampleTestingFiles(select_element) {
        let assignment_tilde = select_element.id.split("_")[1];
        let selected_part = select_element.value;
        let testFilesDiv = document.getElementById("sampleTestingDiv_" + assignment_tilde);
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                testFilesDiv.innerHTML = this.responseText;
            }
        };
        ajaxQuery.open("POST", "get_testing_files.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&partName=" + selected_part + "&method=SAMPLE");
    }

    function getSubmissionFiles(select_element) {
        let assignment_tilde = select_element.id.split("_")[1];
        let selected_part = select_element.value;
        let subFilesDiv = document.getElementById("subFilesDiv_" + assignment_tilde);
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                subFilesDiv.innerHTML = this.responseText;
            }
        };
        ajaxQuery.open("POST", "get_submission_files.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&partName=" + selected_part);
    }

    function addSubmissionFile(button) {
        let button_split = button.id.split("_");
        let assignment_tilde = button_split[1]
        let part_tilde = button_split[2];
        if(document.getElementById("createFile_" + assignment_tilde + "_" + part_tilde).value === "") {
            document.getElementById("subFileError_" + part_tilde).innerText = "The submission file name cannot be empty!";
            document.getElementById("subFileError_" + part_tilde).style.display = 'block';
        } else {
            if(document.getElementById("createFile_" + assignment_tilde + "_" + part_tilde).value.includes(" ")) {
                document.getElementById("subFileError_" + part_tilde).innerText = "The submission file name cannot contain spaces!";
                document.getElementById("subFileError_" + part_tilde).style.display = 'block';
            } else {
                let ajaxQuery = new XMLHttpRequest();
                ajaxQuery.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        reloadSubmissionFiles(document.getElementById("subFilesDiv_" + assignment_tilde).id, assignment_tilde, part_tilde, this.responseText);
                    }
                };
                ajaxQuery.open("POST", "add_submission_file.php", true);
                ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&partName=" + part_tilde.replace(new RegExp('~', 'g'), " ") + "&fileName=" + document.getElementById("createFile_" + assignment_tilde + "_" + part_tilde).value);
            }
        }
    }

    function deleteSubmissionFile(button) {
        let button_split = button.id.split("_");
        let assignment_tilde = button_split[1];
        let part_tilde = button_split[2];
        let submission_file = button_split[3];
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                reloadSubmissionFiles(document.getElementById("subFilesDiv_" + assignment_tilde).id, assignment_tilde, part_tilde, this.responseText);
            }
        };
        ajaxQuery.open("POST", "delete_submission_file.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&partName=" + part_tilde.replace(new RegExp('~', 'g'), " ") + "&fileName=" + submission_file);
    }

    function reloadSubmissionFiles(subFilesDivId, assignmentTilde, partTilde, ajaxResponse) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById(subFilesDivId).innerHTML = this.responseText;
                document.getElementById("subFileError_" + partTilde).innerText = ajaxResponse;
                let prefix = ajaxResponse.substring(0, 5);
                if (prefix === "ERROR") {
                    document.getElementById("subFileError_" + partTilde).style.color = "#ff0000";
                } else {
                    document.getElementById("subFileError_" + partTilde).style.color = "#3f9b42";
                }
                document.getElementById("subFileError_" + partTilde).style.display = 'block';
            }
        };
        ajaxQuery.open("POST", "get_submission_files.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignmentTilde.replace(new RegExp('~', 'g'), " ") + "&partName=" +  partTilde.replace(new RegExp('~', 'g'), " "));
    }

    function manageGrading(button) {
        let assignment_tilde = button.id.split("_")[1];
        document.getElementById("extensions_" + assignment_tilde + "_row").classList.add("hidden");
        document.getElementById("details_" + assignment_tilde + "_row").classList.add("hidden");
        if(document.getElementById("grading_" + assignment_tilde + "_row").classList.contains("hidden")) {
            let assignment = assignment_tilde.replace(new RegExp('~', 'g'), " ");
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("grading_" + assignment_tilde + "_row").classList.remove("hidden");
                    document.getElementById("assignment_row_" + assignment_tilde).classList.add("assignment");
                    document.getElementById("buttondata_" + assignment_tilde).style.borderRight = "6px solid black";
                    document.getElementById("grading_" + assignment_tilde + "_cell").innerHTML = this.responseText;
                }
            };
            ajaxQuery.open("POST", "retrieve_grading.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + assignment);
        } else {
            document.getElementById("grading_" + assignment_tilde + "_row").classList.add("hidden");
            document.getElementById("assignment_row_" + assignment_tilde).classList.remove("assignment");
            document.getElementById("buttondata_" + assignment_tilde).style.borderRight = "";
        }
    }

    function setGradingDueDate(button) {
        let assignment_tilde = button.id.split("_")[2];
        document.getElementById("gradingMessage_" + assignment_tilde).style.display = 'none';
        let newDueDate = document.getElementById("grading_input_" + assignment_tilde).value;
        if(newDueDate === "") {
            document.getElementById("gradingMessage_" + assignment_tilde).style.color = 'red';
            document.getElementById("gradingMessage_" + assignment_tilde).innerText = "You must enter a due date!";
            document.getElementById("gradingMessage_" + assignment_tilde).style.display = 'block';
        } else {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    reloadGrading(assignment_tilde, this.responseText);
                }
            };
            ajaxQuery.open("POST", "set_grading_date.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&newDueDate=" + newDueDate);
        }
    }

    function reloadGrading(assignment_tilde, ajaxResponse) {
            let assignment = assignment_tilde.replace(new RegExp('~', 'g'), " ");
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("grading_" + assignment_tilde + "_cell").innerHTML = this.responseText;
                    let prefix = ajaxResponse.substring(0, 5);
                    if (prefix === "ERROR") {
                        document.getElementById("gradingMessage_" + assignment_tilde).style.color = "#ff0000";
                    } else {
                        document.getElementById("gradingMessage_" + assignment_tilde).style.color = "#3f9b42";
                    }
                    document.getElementById("gradingMessage_" + assignment_tilde).innerText = ajaxResponse;
                    document.getElementById("gradingMessage_" + assignment_tilde).style.display = 'block';
                }
            };
            ajaxQuery.open("POST", "retrieve_grading.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + assignment);
    }

    function getRubricForPart(select) {
        let assignment_tilde = select.id.split("_")[1];
        let part_name = select.value;
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("rubricDiv_" + assignment_tilde).innerHTML = this.responseText;
            }
        };
        ajaxQuery.open("POST", "retrieve_rubric.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&partName=" + part_name);
    }

    function checkDisabledPointValue(select) {
        let selectSplit = select.id.split("_");
        let idNum = selectSplit[1];
        let assignment_tilde = selectSplit[3];
        let part_tilde = selectSplit[4];
        let pointValueID = "rubricLine_" + idNum + "_value_" + assignment_tilde + "_" + part_tilde;
        if(select.value === "0") {
            document.getElementById(pointValueID).disabled = false;
        } else {
            document.getElementById(pointValueID).value = "";
            document.getElementById(pointValueID).disabled = true;
        }
    }

    function addRubricRow(button) {
        let buttonSplit = button.id.split("_");
        let serverRubricID = buttonSplit[1];
        let assignment_tilde = buttonSplit[3];
        let part_tilde = buttonSplit[4];
        let position = buttonSplit[5];
        let rubricTableID = "rubricTable_" + assignment_tilde + "_" + part_tilde;
        let rubricTable = document.getElementById(rubricTableID);
        let newRow = rubricTable.insertRow(parseInt(position) + 1);
        let typeCell = newRow.insertCell(0);
        typeCell.innerHTML = getTypeHTML(serverRubricID, assignment_tilde, part_tilde);
        let itemCell = newRow.insertCell(1);
        itemCell.innerHTML = getItemHTML(serverRubricID, assignment_tilde, part_tilde);
        let valueCell = newRow.insertCell(2);
        valueCell.innerHTML = getValueHTML(serverRubricID, assignment_tilde, part_tilde);
        let actionCell = newRow.insertCell(3);
        actionCell.innerHTML = getActionsHTML(serverRubricID, position, assignment_tilde, part_tilde);
        updateClientSideRubricIDs(rubricTableID);
    }

    function deleteRubricRow(button) {
        let buttonSplit = button.id.split("_");
        let assignment_tilde = buttonSplit[3];
        let part_tilde = buttonSplit[4];
        let position = parseInt(buttonSplit[5]);
        let rubricTableID = "rubricTable_" + assignment_tilde + "_" + part_tilde;
        let rubricTable = document.getElementById(rubricTableID);
        rubricTable.deleteRow(position);
        updateClientSideRubricIDs(rubricTableID);
    }

    function getTypeHTML(serverRubricID, assignment_tilde, part_tilde) {
        let newSelectID = "rubricLine_" + (parseInt(serverRubricID) + 1).toString() + "_type_" + assignment_tilde + "_" + part_tilde;
        let html = "<select id={} style='display: inline-block; margin-right: 5px;width: 120px;font-size: medium;' onchange='checkDisabledPointValue(this)'>".replace("{}", newSelectID);
        html += "<option selected value=0 style='width: 120px'>Graded Item</option>";
        html += "<option value=-1 style='width: 120px'>TA Note</option>";
        html += "<option value=1 style='width: 120px'>Student Note</option>";
        html += "</select>";
        return html;
    }

    function getItemHTML(serverRubricID, assignment_tilde, part_tilde) {
        let newItemID = "rubricLine_" + (parseInt(serverRubricID) + 1).toString() + "_item_" + assignment_tilde + "_" + part_tilde;
        return "<input type='text' id={} value=''>".replace("{}", newItemID);
    }

    function getValueHTML(serverRubricID, assignment_tilde, part_tilde) {
        let newValueID = "rubricLine_" + (parseInt(serverRubricID) + 1).toString() + "_value_" + assignment_tilde + "_" + part_tilde;
        return "<input type='number' id={} style='width: 75px' value=''>".replace("{}", newValueID);
    }

    function getActionsHTML(serverRubricID, position, assignment_tilde, part_tilde) {
        let addRowID = "rubricLine_" + (parseInt(serverRubricID) + 1).toString() + "_add_" + assignment_tilde + "_" + part_tilde + "_" + (parseInt(position) + 1).toString();
        let delRowID = "rubricLine_" + (parseInt(serverRubricID) + 1).toString() + "_del_" + assignment_tilde + "_" + part_tilde + "_" + (parseInt(position) + 1).toString();
        let html = "<button id={} class='edit_button' style='margin-right: 2px;padding: 6px 15px;font-size: large;font-weight: bold' onclick='addRubricRow(this)'>+</button>".replace("{}", addRowID);
        html += "<button id={} class='delete_button' style='padding: 6px 15px;font-size: large;font-weight: bold' onclick='deleteRubricRow(this)'>-</button>".replace("{}", delRowID);
        return html;
    }

    function updateClientSideRubricIDs(rubricTableID) {
        let rubricTable = document.getElementById(rubricTableID);
        let rows = rubricTable.children[0].children;
        let currentRowID = null; // SQL Identifier
        let rowCounter = 1;
        while(rowCounter < rows.length) {
            if(rowCounter === 1) {
                // We set our baseline (the first row never changes and is always right)
                currentRowID = rows[rowCounter].children[0].children[0].id.split("_")[1];
            } else {
                let oneRow = rows[rowCounter];
                for(let columnCounter = 0; columnCounter < oneRow.children.length; columnCounter++) {
                    if(columnCounter !== 3) { // The buttons need to be dealt with differently
                        let idSplit = oneRow.children[columnCounter].children[0].id.split("_");
                        idSplit[1] = currentRowID;
                        oneRow.children[columnCounter].children[0].id = idSplit.join("_");
                    } else {
                        let addRowSplit = oneRow.children[columnCounter].children[0].id.split("_");
                        addRowSplit[1] = currentRowID;
                        addRowSplit[5] = rowCounter;
                        oneRow.children[columnCounter].children[0].id = addRowSplit.join("_");

                        let delRowSplit = oneRow.children[columnCounter].children[1].id.split("_");
                        delRowSplit[1] = currentRowID;
                        delRowSplit[5] = rowCounter;
                        oneRow.children[columnCounter].children[1].id = delRowSplit.join("_");
                    }
                }
            }
            rowCounter++;
            currentRowID++;
        }
    }

    function saveRubric(button) {
        let doTheSave = true;
        let buttonSplit = button.id.split("_");
        let assignment_tilde = buttonSplit[1];
        let part_tilde = buttonSplit[2];
        document.getElementById("rubricMessage_" + assignment_tilde).style.display = 'none';
        document.getElementById("rubricMessage_" + assignment_tilde).style.color = "#ff0000";
        let rubricTableID = "rubricTable_" + assignment_tilde + "_" + part_tilde;
        let rubricTable = document.getElementById(rubricTableID);
        let rows = rubricTable.children[0].children;
        let rubricData=[];
        let rowCounter = 1;
        while(rowCounter < rows.length && doTheSave === true) { // Offset is to exclude the header row
            let oneRow = rows[rowCounter];
            let rowID = oneRow.children[0].children[0].id.split("_")[1]; // Gets ID of row, which is put into SQL database.
            let lineType = oneRow.children[0].children[0].value;
            let lineItem = oneRow.children[1].children[0].value;
            if(lineItem === "") {
                doTheSave = false;
                document.getElementById("rubricMessage_" + assignment_tilde).innerText = "You must have a line item for row " + rowCounter + "!";
                document.getElementById("rubricMessage_" + assignment_tilde).style.display = 'block';
            }
            let lineValue = oneRow.children[2].children[0].value;
            if(doTheSave === true && lineType === "0" && lineValue === "") {
                doTheSave = false;
                document.getElementById("rubricMessage_" + assignment_tilde).innerText = "You must have a point value for row " + rowCounter + "!";
                document.getElementById("rubricMessage_" + assignment_tilde).style.display = 'block';
            }
            rubricData.push({row_id:rowID, line_type:lineType, line_item:lineItem, line_value:lineValue});
            rowCounter++;
        }
        if(doTheSave === true) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("rubricMessage_" + assignment_tilde).innerText = this.responseText;
                    let prefix = this.responseText.substring(0, 5);
                    if (prefix === "ERROR") {
                        document.getElementById("rubricMessage_" + assignment_tilde).style.color = "#ff0000";
                    } else {
                        document.getElementById("rubricMessage_" + assignment_tilde).style.color = "#3f9b42";
                    }
                    document.getElementById("rubricMessage_" + assignment_tilde).style.display = 'block';
                }
            };
            ajaxQuery.open("POST", "update_rubric.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + assignment_tilde.replace(new RegExp('~', 'g'), " ") + "&partName=" + part_tilde.replace(new RegExp('~', 'g'), " ") + "&rubricData=" + JSON.stringify(rubricData));
        }

    }

    function releaseGrading(assignment) {
        let assignmentName = decode_html(assignment.replace(new RegExp('~', 'g'), " "));
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                if(this.responseText.substring(0, 5) !== "ERROR") {
                    document.getElementById("gradeRelease_" + assignment).style.color = "#3f9b42";
                }
                document.getElementById("gradeRelease_" + assignment).innerText = this.responseText;
                document.getElementById("gradeRelease_" + assignment).style.display = "block";
            }
        };
        ajaxQuery.open("POST", "release_grading.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignmentName);
    }

    function decode_html(text) { // function to decode html special chars
        let map = {
            '&amp;': '&',
            '&#038;': "&",
            '&lt;': '<',
            '&gt;': '>',
            '&quot;': '"',
            '&#039;': "'",
            '&#8217;': "’",
            '&#8216;': "‘",
            '&#8211;': "–",
            '&#8212;': "—",
            '&#8230;': "…",
            '&#8221;': '”'
        };
        return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
    }

    function uploadMakefile(assignment, part) {
        let makefile = document.getElementById("makefile_file").files[0];
        let makefileMessage = document.getElementById("makefileMessage");
        if(makefile === undefined) {
            makefileMessage.innerText = "You must select a file to upload!";
            makefileMessage.style.display = 'block';
        } else {
            if(makefile['name'] !== "Makefile") {
                makefileMessage.innerText = "ERROR: The file must be named 'Makefile'!";
                makefileMessage.style.display = 'block';
            } else {
                makefileMessage.innerText = "Uploading " + makefile['name'] + "...";
                makefileMessage.style.color = "#0073ca";
                makefileMessage.style.display = 'block';

                let ajaxQuery = new XMLHttpRequest();
                let makefileData = new FormData();
                makefileData.append("makefile", makefile);
                makefileData.append("assignmentName", decode_html(assignment.replace(new RegExp('~', 'g'), " ")));
                makefileData.append("partName", decode_html(part.replace(new RegExp('~', 'g'), " ")));
                makefileData.append("type", "SAMPLE");
                ajaxQuery.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        if(this.responseText === "Success!") {
                            makefileMessage.style.color = "#3f9b42";
                        } else {
                            makefileMessage.style.color = "#ff0000";
                        }
                        makefileMessage.innerText = this.responseText;
                        updatePartSelection(document.getElementById("subParts_" + decode_html(assignment.replace(new RegExp(' ', 'g'), "~"))));

                    }
                };
                ajaxQuery.open("POST", "upload_makefile.php", true);
                ajaxQuery.send(makefileData);
            }
        }
        document.getElementById('makefile_file').value = "";
    }

    function uploadSampleInput(assignment, part) {
        let input_file = document.getElementById("sample_input_file").files[0];
        let sampleInputMessage = document.getElementById("sampleInputMessage");
        if(input_file === undefined) {
            sampleInputMessage.innerText = "You must select a file to upload!";
            sampleInputMessage.style.display = 'block';
        } else {
            sampleInputMessage.innerText = "Uploading " + input_file['name'] + "...";
            sampleInputMessage.style.color = "#0073ca";
            sampleInputMessage.style.display = 'block';

            let ajaxQuery = new XMLHttpRequest();
            let inputFileData = new FormData();
            inputFileData.append("input_file", input_file);
            inputFileData.append("assignmentName", decode_html(assignment.replace(new RegExp('~', 'g'), " ")));
            inputFileData.append("partName", decode_html(part.replace(new RegExp('~', 'g'), " ")));
            inputFileData.append("fileName", input_file['name']);
            inputFileData.append("type", "SAMPLE");
            ajaxQuery.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    if(this.responseText === "Success!") {
                        sampleInputMessage.style.color = "#3f9b42";
                    } else {
                        sampleInputMessage.style.color = "#ff0000";
                    }
                    sampleInputMessage.innerText = this.responseText;
                    updatePartSelection(document.getElementById("subParts_" + decode_html(assignment.replace(new RegExp(' ', 'g'), "~"))));
                }
            };
            ajaxQuery.open("POST", "upload_io_file.php", true);
            ajaxQuery.send(inputFileData);
        }
        document.getElementById('sample_input_file').value = "";
    }

    function removeInput(button) {
        let button_id_split = button.id.split("_");
        let assignmentName = button_id_split[1];
        let partName = button_id_split[2];
        let method = button_id_split[3];
        let row_count = button_id_split[4];
        let fileName = document.getElementById("input_" + assignmentName + "_" + row_count).innerText;
        if(confirm("Are you sure you want to delete " + fileName + " as an input file?")) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    updatePartSelection(document.getElementById("subParts_" + decode_html(assignmentName.replace(new RegExp(' ', 'g'), "~"))));
                }
            };
            ajaxQuery.open("POST", "remove_io_file.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("assignmentName=" + decode_html(assignmentName.replace(new RegExp('~', 'g'), " ")) + "&partName=" + decode_html(partName.replace(new RegExp('~', 'g'), " ")) + "&fileName=" + fileName + "&method=" + method);
        }
    }

    function previewInput(button) {
        let button_id_split = button.id.split("_");
        let assignmentName = button_id_split[1];
        let partName = button_id_split[2];
        let row_count = parseInt(button_id_split[3]);
        let fileName = document.getElementById("input_" + assignmentName + "_" + row_count).innerText;
        let table = document.getElementById("inputTable_" + assignmentName);
        let currRow = button.parentNode.parentNode; // Gets row object user wants to preview
        if(currRow.rowIndex === table.rows.length - 1) { // If this is the last row in the table, then there can't be a preview, so add one
            let previewRow = table.insertRow(currRow.rowIndex + 1);
            let previewCell = previewRow.insertCell(0);
            previewCell.colSpan = 2;
            previewInputAJAX(previewCell, assignmentName, partName, fileName);
        } else {
            if(table.rows[currRow.rowIndex + 1].cells[0].colSpan === 2) { // If there already is a preview, close it
                table.deleteRow(currRow.rowIndex + 1);
            } else { // Otherwise, add preview
                let previewRow = table.insertRow(currRow.rowIndex + 1);
                let previewCell = previewRow.insertCell(0);
                previewCell.colSpan = 2;
                previewInputAJAX(previewCell, assignmentName, partName, fileName);
            }
        }
    }

    function previewInputAJAX(previewCell, assignmentName, partName, fileName) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                previewCell.innerHTML = this.responseText;
            }
        };
        ajaxQuery.open("POST", "fetch_io_file.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignmentName=" + decode_html(assignmentName.replace(new RegExp('~', 'g'), " ")) + "&partName=" + decode_html(partName.replace(new RegExp('~', 'g'), " ")) + "&fileName=" + fileName);
    }

</script>