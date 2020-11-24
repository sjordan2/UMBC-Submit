<html lang="en">
<style>
    body {background-color: #F1C04B}
    #newstudent_form {text-align: center;
        border: thin solid black;
    }
    p.errorMessage {
        display: none;
        color: red;
        margin: 0;
    }
    button {
        background-color: white;
        color: #0073ca;
        display: inline-block;
        padding: 10px 15px;
        cursor: pointer;
        border: 2px solid;
    }
    button:hover {
        background-color: #0073ca;
        color: white;
    }
</style>
<body>

	<form method="post" action="javascript:validateForm()" id="newstudent_form">
	<label for="student_first_name">
	Student's First Name:
	</label><br>
	<input type="text" id="student_first_name" name="student_fname">
	<br>

    <p id='fNameFeedback' class='errorMessage'>The student's first name cannot be empty!</p>

	<label for="student_last_name">
	Student's Last Name:
	</label><br>
	<input type="text" id="student_last_name" name="student_lname">
    <br>

    <p id='lNameFeedback' class='errorMessage'>The student's last name cannot be empty!</p>

    <label for="student_campus_id">
    Student's Campus ID:
    </label><br>
    <input type="text" id="student_campus_id" name="student_campus_id">
    <br>

    <p id='campusIdFeedback' class='errorMessage'>The student's campus ID cannot be empty!</p>

    <label for="student_name_id">
    Student's Name ID:
    </label><br>
    <input type="text" id="student_name_id" name="student_name_id">
    <br>

    <p id='nameIdFeedback' class='errorMessage'>The student's name ID cannot be empty!</p>

    <label for="student_discussion">
    Student's Discussion Section:
    </label><br>
    <input type="text" id="student_discussion" name="student_discussion">
    <br>

    <p id='discussionSectionFeedback' class='errorMessage'>The student's discussion section must be a number!</p>

    Student's Role:<br>
    <input type="radio" id="studentRadio" name="role" value="Student">
    <label for="studentRadio">Student</label>
    <input type="radio" id="taRadio" name="role" value="TA">
    <label for="taRadio">TA</label>
    <br>

    <p id='roleFeedback' class='errorMessage'>The student's role cannot be empty!</p>

    <input type="submit" value="Submit" name="submit_student">
    <p id="newStudentMessage" class=errorMessage></p>
	<br>
	</form>

	<button id="viewRosterButton" onclick="viewRoster()">
        View Student Roster
    </button>
    <p id="viewRosterMessage" class=errorMessage></p><br><br>


    Student Roster:
    <input type="file" name="students_file" id="students_file"><br>
    <button id="submitStudentFile" onclick="submitStudentRoster()">
        Upload Student Roster to Database
    </button>
    <p id="studentFileMessage" class=errorMessage></p><br><br>


</body>
</html>
<script>
    function validateForm() {
        resetFormMessages();

        let isGoodForm = true;

        let studentFirstName = document.getElementById("student_first_name").value;
        if(studentFirstName === "") {
            document.getElementById("fNameFeedback").style.display = 'block';
            isGoodForm = false;
        }

        let studentLastName = document.getElementById("student_last_name").value;
        if(studentLastName === "") {
            document.getElementById("lNameFeedback").style.display = 'block';
            isGoodForm = false;
        }

        let studentCampusId = document.getElementById("student_campus_id").value;
        if(studentCampusId === "") {
            document.getElementById("campusIdFeedback").style.display = 'block';
            isGoodForm = false;
        }

        let studentNameId = document.getElementById("student_name_id").value;
        if(studentNameId === "") {
            document.getElementById("nameIdFeedback").style.display = 'block';
            isGoodForm = false;
        }

        if (!document.getElementById('studentRadio').checked &&
            !document.getElementById('taRadio').checked) {
            document.getElementById("roleFeedback").style.display = 'block';
            isGoodForm = false;
        }
        let studentRole;
        if (document.getElementById('studentRadio').checked) {
            studentRole = "student";
        } else {
            studentRole = "TA";
        }

        let studentDiscussion = document.getElementById("student_discussion").value;
        if(studentDiscussion === "" || isNaN(studentDiscussion)) {
            document.getElementById("discussionSectionFeedback").style.display = 'block';
            isGoodForm = false;
        }
        if(isGoodForm === true) {
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let prefix = this.responseText;
                    prefix = prefix.substring(0, 5);
                    let newStudentMessage = document.getElementById('newStudentMessage');
                    if(prefix !== "ERROR") {
                        newStudentMessage.style.color = "#3f9b42";
                    }
                    newStudentMessage.innerText = this.responseText;
                    newStudentMessage.style.display = 'block';
                }
            };
            ajaxQuery.open("POST", "add_single_student.php", true);
            ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxQuery.send("fname=" + studentFirstName + "&lname=" + studentLastName + "&cID=" + studentCampusId
                            + "&nID=" + studentNameId + "&disc=" + studentDiscussion + "&role=" + studentRole);
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
    function viewRoster() {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if(this.readyState === 4 && this.status === 200) {
                let prefix = this.responseText;
                prefix = prefix.substring(0, 5);
                let viewRosterMessage = document.getElementById("viewRosterMessage");
                viewRosterMessage.innerText = this.responseText;
                if(prefix === "ERROR") {
                    viewRosterMessage.style.color = "#ff0000";
                    viewRosterMessage.style.display = 'block';
                } else {
                    window.location.href = "list_students.php";
                }
            }
        };
        ajaxQuery.open("POST", "create_student_table.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send();
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
                studentFileMessage.style.color = "#0073ca"
                studentFileMessage.style.display = 'block';

                let ajaxQuery = new XMLHttpRequest();
                let studentData = new FormData();
                studentData.append("students_file", rexOutput);
                ajaxQuery.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        if(this.responseText.substr(0, 7) === "SUCCESS") {
                            studentFileMessage.style.color = "#3f9b42"
                        }
                        studentFileMessage.innerText = this.responseText;
                    }
                };
                ajaxQuery.open("POST", "upload_students.php", true);
                ajaxQuery.send(studentData);
            }
        }
    }
</script>
