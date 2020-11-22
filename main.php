<?php
session_start();

?>
<html lang="en">
<style>
    body {background-color: #F1C04B}
    #newstudent_form {text-align: center;
        border: thin solid black;
    }
    p.errorMessage {
        display: none;
        color: red;
        margin: 0px;
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
    <input type="radio" id="studentRadio" name="role" value="student">
    <label for="student">Student</label>
    <input type="radio" id="taRadio" name="role" value="ta">
    <label for="ta">TA</label>
    <br>

    <p id='roleFeedback' class='errorMessage'>The student's role cannot be empty!</p>

    <input type="submit" value="Submit" name="submit_student">
    <p id="newStudentMessage" class=errorMessage></p>
	<br>
	</form>

	<form method="post" action="list_students.php">
        <input type="submit" value="List Students" name="list_student">
        <br>
    </form>

<p style="color:#ff0000">
    <?php
    if(isset($_SESSION["ListStudentsMessage"])) {
        print(nl2br("ERROR: " . $_SESSION["ListStudentsMessage"] . "\r\n"));
        session_unset();
    }
    ?>
</p>

	<form method="post" action="create_student_table.php">
	    <input type="submit" value="Create Students Table" name="create_table">
	</form>

<?php
if(isset($_SESSION["CreateTableMessage"])) {
    print(nl2br($_SESSION["CreateTableMessage"] . "\r\n"));
    session_unset();
}
?>
    <form method="post" action="delete_student_table.php">
        <input type="submit" value="Delete Students Table" name="delete_table">
    </form>

<?php
if(isset($_SESSION["DeleteTableMessage"])) {
    print(nl2br($_SESSION["DeleteTableMessage"] . "\r\n"));
    session_unset();
}
?>

    <form enctype="multipart/form-data" action="upload_students.php" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
        Student Roster: <input name="students_file" type="file" /><br>
        <input type="submit" value="Upload Student Roster to Server" />
    </form>


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
            console.log("Good form!")
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
            ajaxQuery.open("POST", "new_student.php", true);
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
</script>
