<style>
    table {
        margin-top: 10px;
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
    table {
        table-layout: fixed;
    }
    table,th,td,tr {
        border : 2px solid black;
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        padding: 5px;
        text-align: center;
        vertical-align: middle;
        overflow: auto;
        font-size: 20px;
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
        background-color: #F1C04B;
    }
    #searchAssignments {
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
    form {
        display: none;
        border: thin solid black;
        margin-top: 5px;
        margin-bottom: 10px;
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
<body onload="retrieveAssignments()">
<p class="title">Assignment Management</p>
<hr class="divider">
<div id="formButtonsDiv">
    <button id="viewNewAssignmentForm" class="utility" onclick="toggleNewAssignmentForm()">
        Create New Assignment
    </button>
    <button id="viewNewAssignmentForm" class="utility" onclick="location.href='extension_management.php';">
        Manage Extensions >>
    </button><br>
    <form method="post" action="javascript:validateForm()" id="newassignment_form">
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
        <input type="datetime-local" id="duedate" name="duedate"><br>
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
    function retrieveAssignments() {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("table_div").innerHTML = this.responseText;
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

    function validateForm() {
        resetFormMessages();

        let isGoodForm = true;

       let assignmentName = document.getElementById("assignmentname").value;
       if(assignmentName === "") {
           document.getElementById("nameFeedback").innerText = "The assignment name cannot be empty!";
           document.getElementById("nameFeedback").style.display = 'block';
           isGoodForm = false;
       }

       if(assignmentName.includes("~")) {
           document.getElementById("nameFeedback").innerText = "The assignment name cannot contain the '~' character!";
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
                    let prefix = this.responseText;
                    prefix = prefix.substring(0, 5);
                    let newUserMessage = document.getElementById('newAssignmentMessage');
                    if(prefix !== "ERROR") {
                        newUserMessage.style.color = "#3f9b42";

                    }
                    newUserMessage.innerText = this.responseText;
                    newUserMessage.style.display = 'block';

                    retrieveAssignments();
                }
            };
            ajaxQuery.open("POST", "create_assignment.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("name=" + assignmentName + "&dateCreated=" + dateCreated + "&dateDue=" + dueDate
                + "&maxPoints=" + maximumPoints + "&extraCredit=" + extraCredit);
        }
    }
    function resetFormMessages() {
        document.getElementById("nameFeedback").style.display = 'none';
        document.getElementById("pointFeedback").style.display = 'none';
        document.getElementById("extraCreditFeedback").style.display = 'none';
        document.getElementById("dateFeedbackEmpty").style.display = 'none';
        document.getElementById("dateFeedbackInvalid").style.display = 'none';
    }

    function deleteAssignment(button) {
        let assignment_name = button.id.split("_")[1]; // Gets Assignment Name
        if(confirm("Are you sure you want to delete this assignment (" + assignment_name.replace("~", " ") + ")?")) {
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
                    retrieveAssignments();
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
            document.getElementById("status_" + assignment_name).innerText = "Past Due";
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
