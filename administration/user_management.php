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
    p.errorMessage {
        display: none;
    }
    body {
        background-color: #ABABAB;
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
    #searchUsers {
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
    #students_file {
        text-align: center;
        margin-top: 5px;
        display: inline-block;
    }
    #addUserTop {
        margin-top: 5px;
        display: inline-block;
    }

</style>
<body onload="retrieveUsers(false, null)">
<p class="title">User Management</p>
<hr class="divider">
<div id="formButtonsDiv">
<button id="viewNewUserForm" class="utility" onclick="toggleNewUserForm()">
    Add New User
</button>
<button id="viewUploadForm" class="utility" onclick="toggleFileUploadForm()">
    Upload Student Roster from REX
</button>
<button id="emailNewStudents" class="utility" onclick="emailNewStudents()">
    Email Newly Added Students
</button><br>
<form method="post" action="javascript:validateForm()" id="newuser_form">
    <label for="user_first_name" id="addUserTop">
        User's First Name:
    </label><br>
    <input type="text" id="user_first_name" name="user_fname">
    <br>

    <p id='fNameFeedback' class='errorMessage'>The user's first name cannot be empty!</p>

    <label for="user_last_name">
        User's Last Name:
    </label><br>
    <input type="text" id="user_last_name" name="user_lname">
    <br>

    <p id='lNameFeedback' class='errorMessage'>The user's last name cannot be empty!</p>

    <label for="user_campus_id">
        User's Campus ID:
    </label><br>
    <input type="text" id="user_campus_id" name="user_campus_id">
    <br>

    <p id='campusIdFeedback' class='errorMessage'>The user's campus ID cannot be empty!</p>

    <label for="user_name_id">
        User's Name ID:
    </label><br>
    <input type="text" id="user_name_id" name="user_name_id">
    <br>

    <p id='nameIdFeedback' class='errorMessage'>The user's name ID cannot be empty!</p>

    <label for="user_discussion">
        User's Discussion Section:
    </label><br>
    <input type="text" id="user_discussion" name="user_discussion">
    <br>

    <p id='discussionSectionFeedback' class='errorMessage'>The user's discussion section must be a positive integer!</p>

    User's Role:<br>
    <input type="radio" id="studentRadio" name="role" value="Student">
    <label for="studentRadio">Student</label>
    <input type="radio" id="taRadio" name="role" value="TA">
    <label for="taRadio">TA</label>
    <input type="radio" id="instructorRadio" name="role" value="Instructor">
    <label for="instructorRadio">Instructor</label>
    <br>

    <p id='roleFeedback' class='errorMessage'>The user's role cannot be empty!</p>

    <input class='submitClass' type="submit" value="Submit" name="submit_user">
    <p id="newUserMessage" class=errorMessage></p>
    <br>
</form>
    <form id="fileupload_form" action="javascript:submitStudentRoster()">
        <input type="file" name="students_file" id="students_file"><br>
        <input class='submitClass' type="submit" value="Submit" name="submit_student">
        <p class="errorMessage" id="studentFileMessage"></p>
    </form>
</div>
<div id="table_div">
</div>
</body>

<script>
    function retrieveUsers(showMessage, ajaxResponse) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("table_div").innerHTML = this.responseText;
                if(showMessage) {
                    if(ajaxResponse.substr(0, 7) === "SUCCESS") {
                        document.getElementById('messageFeedback').style.color = "#3f9b42"
                    }
                    document.getElementById('messageFeedback').innerText = ajaxResponse;
                    document.getElementById('messageFeedback').style.display = 'block';
                }
            }
        };
        ajaxQuery.open("POST", "retrieve_users.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send();
    }

    function toggleNewUserForm() {
        if(document.getElementById("fileupload_form").style.display === 'block') {
            document.getElementById("fileupload_form").style.display = 'none';
            document.getElementById("newuser_form").style.display = 'block';
        } else {
            if(document.getElementById("newuser_form").style.display === 'block') {
                document.getElementById("newuser_form").style.display = 'none';
            } else {
                document.getElementById("newuser_form").style.display = 'block';
            }
        }
    }

    function submitStudentRoster() {

        let rexOutput = document.getElementById("students_file").files[0];
        let studentFileMessage = document.getElementById("studentFileMessage");
        if(rexOutput === undefined) {
            studentFileMessage.innerText = "You must select a file to upload!";
            studentFileMessage.style.display = 'block';
        } else {
            if(rexOutput['name'].split(".")[1] !== "csv") {
                studentFileMessage.innerText = "ERROR: The file must be in CSV format!";
                studentFileMessage.style.display = 'block';
            } else {
                studentFileMessage.innerText = "Uploading " + rexOutput['name'] + "...";
                studentFileMessage.style.color = "#0073ca";
                studentFileMessage.style.display = 'block';

                let ajaxQuery = new XMLHttpRequest();
                let studentData = new FormData();
                studentData.append("students_file", rexOutput);
                ajaxQuery.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        retrieveUsers(true, this.responseText);
                    }
                };
                ajaxQuery.open("POST", "upload_students.php", true);
                ajaxQuery.send(studentData);
            }
        }
        document.getElementById('students_file').value = "";
    }

    function toggleFileUploadForm() {
        if(document.getElementById("newuser_form").style.display === 'block') {
            document.getElementById("newuser_form").style.display = 'none';
            document.getElementById("fileupload_form").style.display = 'block';
        } else {
            if(document.getElementById("fileupload_form").style.display === 'block') {
                document.getElementById("fileupload_form").style.display = 'none';
            } else {
                document.getElementById("fileupload_form").style.display = 'block';
            }
        }
    }

    function validateForm() {
        resetFormMessages();

        let isGoodForm = true;

        let userFirstName = document.getElementById("user_first_name").value;
        if(userFirstName === "") {
            document.getElementById("fNameFeedback").style.display = 'block';
            isGoodForm = false;
        }

        let userLastName = document.getElementById("user_last_name").value;
        if(userLastName === "") {
            document.getElementById("lNameFeedback").style.display = 'block';
            isGoodForm = false;
        }

        let userCampusId = document.getElementById("user_campus_id").value;
        if(userCampusId === "") {
            document.getElementById("campusIdFeedback").style.display = 'block';
            isGoodForm = false;
        }

        let userNameId = document.getElementById("user_name_id").value;
        if(userNameId === "") {
            document.getElementById("nameIdFeedback").style.display = 'block';
            isGoodForm = false;
        }

        if (!document.getElementById('studentRadio').checked &&
            !document.getElementById('taRadio').checked &&
            !document.getElementById('instructorRadio').checked) {
            document.getElementById("roleFeedback").style.display = 'block';
            isGoodForm = false;
        }
        let userRole;
        if (document.getElementById('studentRadio').checked) {
            userRole = "Student";
        } else if(document.getElementById('taRadio').checked) {
            userRole = "TA";
        } else {
            userRole = "Instructor";
        }

        let userDiscussion = document.getElementById("user_discussion").value;
        if(userDiscussion === "" || !Number.isInteger(Number(userDiscussion))) {
            document.getElementById("discussionSectionFeedback").style.display = 'block';
            isGoodForm = false;
        }
        // It is an integer.. we must make sure it is positive
        if(Number(userDiscussion) <= 0) {
            document.getElementById("discussionSectionFeedback").style.display = 'block';
            isGoodForm = false;
        }
        if(isGoodForm === true) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("user_first_name").value = "";
                    document.getElementById("user_last_name").value = "";
                    document.getElementById("user_campus_id").value = "";
                    document.getElementById("user_name_id").value = "";
                    document.getElementById("user_discussion").value = "";
                    document.getElementById('studentRadio').checked = false;
                    document.getElementById('taRadio').checked = false;
                    document.getElementById('instructorRadio').checked = false;
                    retrieveUsers(true, this.responseText);
                }
            };
            ajaxQuery.open("POST", "add_single_user.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("fname=" + userFirstName + "&lname=" + userLastName + "&cID=" + userCampusId
                + "&nID=" + userNameId + "&disc=" + userDiscussion + "&role=" + userRole);
        }
    }
    function resetFormMessages() {
        document.getElementById("fNameFeedback").style.display = 'none';
        document.getElementById("lNameFeedback").style.display = 'none';
        document.getElementById("campusIdFeedback").style.display = 'none';
        document.getElementById("nameIdFeedback").style.display = 'none';
        document.getElementById("discussionSectionFeedback").style.display = 'none';
        document.getElementById("roleFeedback").style.display = "none";
    }

    function deleteUser(button) {
        let user_campus_id = button.id.split("_")[1]; // Gets UMBC Campus Id of User
        if(confirm("Are you sure you want to delete this user (" + user_campus_id + ")?")) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    retrieveUsers(true, this.responseText);
                }
            };
            ajaxQuery.open("POST", "delete_user.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("user_id=" + user_campus_id);
        }
    }
    function editUser(button) {
        button.className = "save_button";
        button.innerText = "Save Changes";
        button.onclick = function () {updateEditedUser(button)};
        let user_campus_id = button.id.split("_")[1]; // Gets UMBC Campus Id of User
        let inputAttributeList = ["lastname_", "firstname_", "umbc_name_id_", "section_"];
        for (let counter = 0; counter < inputAttributeList.length; counter++) {
            let oldText = document.getElementById(inputAttributeList[counter] + user_campus_id + "_element");
            let newText = document.createElement("input");
            newText.setAttribute("value", oldText.innerText);
            newText.style.fontSize = "20px";
            newText.style.textAlign = 'center';
            newText.style.width = oldText.offsetWidth.toString();
            newText.id = inputAttributeList[counter] + user_campus_id + "_element";
            oldText.replaceWith(newText);
        }
        let oldRole = document.getElementById("role_" + user_campus_id + "_element");
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
        newRole.id = "role_" + user_campus_id + "_element";
        oldRole.replaceWith(newRole);

        let oldStatus = document.getElementById("status_" + user_campus_id + "_element");
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
        newStatus.id = "status_" + user_campus_id + "_element";
        oldStatus.replaceWith(newStatus);

    }

    function updateEditedUser(button) {
        let user_campus_id = button.id.split("_")[1]; // Gets UMBC Campus Id of User
        let newLastName = document.getElementById("lastname_" + user_campus_id + "_element").value;
        let newFirstName = document.getElementById("firstname_" + user_campus_id + "_element").value;
        let newNameID = document.getElementById("umbc_name_id_" + user_campus_id + "_element").value;
        let newDiscussionSection = document.getElementById("section_" + user_campus_id + "_element").value;
        let newRole = document.getElementById("role_" + user_campus_id + "_element").value;
        let newStatus = document.getElementById("status_" + user_campus_id + "_element").value;
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
                    finishEditingUser(user_campus_id);
                    button.className = "edit_button";
                    button.innerText = "Edit User";
                    button.onclick = function () {editUser(button)};
                }
                messageFeedback.innerText = this.responseText;
                messageFeedback.style.display = 'block';

            }
        }
        ajaxQuery.open("POST", "edit_user.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("lname=" + newLastName + "&fname=" + newFirstName + "&nameID=" + newNameID
                    + "&disc=" + newDiscussionSection + "&role=" + newRole + "&status=" + newStatus + "&cID=" + user_campus_id);
    }

    function finishEditingUser(campus_id) {
        let inputAttributeList = ["lastname_", "firstname_", "umbc_name_id_", "section_", "role_"];
        for (let counter = 0; counter < inputAttributeList.length; counter++) {
            let oldText = document.getElementById(inputAttributeList[counter] + campus_id + "_element");
            let newText = document.createElement("p");
            newText.id = oldText.id;
            newText.innerText = oldText.value;
            oldText.replaceWith(newText)
        }

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

    function updateUsersTable() {
        resetUsersTable();
        let searchBar = document.getElementById('searchUsers');
        let currSearchTerm = searchBar.value.toLowerCase(); // Case insensitive searching :)
        let usersTable = document.getElementById('user_table');
        let rowList = usersTable.getElementsByTagName('tr'); // Returns list of rows to iterate through
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
    function resetUsersTable() {
        let usersTable = document.getElementById('user_table');
        let rowList = usersTable.getElementsByTagName('tr');
        for(let rowCounter = 1; rowCounter < rowList.length; rowCounter++) {
            rowList[rowCounter].style.display = '';
        }

    }

    function emailNewStudents() {
        document.getElementById("messageFeedback").style.display = 'block';
        document.getElementById("messageFeedback").innerText = "Emailing newly enrolled students...";
        document.getElementById("messageFeedback").style.color = "#0073ca";

            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("messageFeedback").innerText = this.responseText;
                    if(this.responseText.substring(0, 5) !== "ERROR") {
                        document.getElementById("messageFeedback").style.color = "#3f9b42";
                    } else {
                        document.getElementById("messageFeedback").style.color = "red";
                    }
                }
            };
            ajaxQuery.open("POST", "email_new_students.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send();
    }
</script>
