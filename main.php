<?php
session_start();

?>
<html lang="en">
<style>
    body {background-color: #F1C04B}
    #newstudent_form {text-align: center;
        border: thin solid black;
    }
</style>
<body>

	<form method="post" action="newstudent.php" id="newstudent_form">
	<label for="student_first_name">
	Student's First Name:
	</label><br>
	<input type="text" id="student_first_name" name="student_fname">
	<br>

	<label for="student_last_name">
	Student's Last Name:
	</label><br>
	<input type="text" id="student_last_name" name="student_lname">
    <br>

    <label for="student_campus_id">
    Student's Campus ID:
    </label><br>
    <input type="text" id="student_campus_id" name="student_campus_id">
    <br>

    <label for="student_name_id">
    Student's Name ID:
    </label><br>
    <input type="text" id="student_name_id" name="student_name_id">
    <br>

    <label for="student_role">
    Student's Role:
    </label><br>
    <input type="text" id="student_role" name="student_role">
    <br>

    <label for="student_discussion">
    Student's Discussion Section:
    </label><br>
    <input type="text" id="student_discussion" name="student_discussion">
    <br>


    <input type="submit" value="Submit" name="submit_student">
	<br>
	</form>
    <p style="color:#ff0000">
<?php
if(isset($_SESSION["StudentError"])) {
    print(nl2br("ERROR: " . $_SESSION["StudentError"] . "\r\n"));
    session_unset();
}
?>
    </p>
<?php
if(isset($_SESSION["StudentObject"])){
    print(nl2br("New Student Object Created!" . "\r\n"));
    print(nl2br("First Name: " . $_SESSION["StudentObject"]["fname"] . "\r\n"));
    print(nl2br("Last Name: " . $_SESSION["StudentObject"]["lname"] . "\r\n"));
    print(nl2br("Campus ID: " . $_SESSION["StudentObject"]["campus_id"] . "\r\n"));
    print(nl2br("Name ID: " . $_SESSION["StudentObject"]["name_id"] . "\r\n"));
    print(nl2br("Role: " . $_SESSION["StudentObject"]["role"] . "\r\n"));
    print(nl2br("Discussion Section: " . $_SESSION["StudentObject"]["discussion"] . "\r\n"));
    session_unset();
}
?>
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
