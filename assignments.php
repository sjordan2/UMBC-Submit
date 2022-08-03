<?php
require_once './includes/sql_functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

$user_campus_id = $_SERVER["umbccampusid"];
$role = getEnrollment($user_campus_id, $conn);

if($role === false) {
    header('Location: ../');
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel='shortcut icon' type='image/x-icon' href='/favicon.png' />
    <title>UMBC Submit System</title>
</head>

<style>
    html, body {
        height: 100%;
        width: 100%;
        overflow-x: hidden;
    }
</style>

<script>
    let entry = performance.getEntriesByType("navigation")[0];
    // Prevents weird behavior when hitting the back button from other pages (basically updates things with the latest information)
    if(entry["type"] === "back_forward") {
        location.reload();
    }
</script>

<body>
<?php
include_once "./includes/header.php";
$extension_name_array = [];
$current_assignment_sql_list = "(";
$past_assignment_sql_list = "(";
$get_curr_assignments_sql = "SELECT assignment_name, date_due FROM Assignments WHERE is_visible = 1";
$get_curr_assignments_result = $conn->query($get_curr_assignments_sql);
while($row = $get_curr_assignments_result->fetch_assoc()) {
    // See if a student has an extension
    $assignment_name = $conn->real_escape_string(stripslashes($row["assignment_name"]));
    $query_extension_sql = "SELECT new_due_date FROM Extensions WHERE assignment_name = '$assignment_name' AND student_id = '$user_campus_id'";
    $query_extension_result = $conn->query($query_extension_sql);
    if($query_extension_result->num_rows > 0) { // The student *does* have an extension
        // Now, we must check the extended due date against the current date and time
        $extended_due_date = $query_extension_result->fetch_assoc()["new_due_date"];
        $extended_due_date_obj = null;
        $current_date_obj = null;
        try {
            $extended_due_date_obj = new DateTime($extended_due_date);
            $current_date_obj = new DateTime();
        } catch(Exception $e) {
            die("Date Time Error when checking dates in presence of an extension!");
        }
        if($current_date_obj >= $extended_due_date_obj) { // Then it is past the student's extension, so put it in past assignments
            $past_assignment_sql_list .= "'" . $assignment_name . "', ";
        } else { // The student still has time to submit their extension, so put it in current assignments
            $current_assignment_sql_list .= "'" . $assignment_name . "', ";
        }
        $extension_name_array[$assignment_name] = $extended_due_date;
    } else { // The student does NOT have an extension
        // Just check the course-wide assignment due date against the current date and time
        $assignment_due_date = $row["date_due"];
        $assignment_due_date_obj = null;
        $current_date_obj = null;
        try {
            $assignment_due_date_obj = new DateTime($assignment_due_date);
            $current_date_obj = new DateTime();
        } catch(Exception $e) {
            die("Date Time Error when checking dates for current/past assignments!");
        }
        if($current_date_obj >= $assignment_due_date_obj) { // In past assignments
            $past_assignment_sql_list .= "'" . $assignment_name . "', ";
        } else { // In current assignments
            $current_assignment_sql_list .= "'" . $assignment_name . "', ";
        }
    }
}
$past_assignment_go_time = true;
$current_assignment_go_time = true;
if(strlen($past_assignment_sql_list) === 1) {
    $past_assignment_sql_list .= ")";
    $past_assignment_go_time = false;
} else {
    $past_assignment_sql_list = substr($past_assignment_sql_list, 0, -2) . ")"; // Remove the ", " at the end and add a closing parenthesis
}
if(strlen($current_assignment_sql_list) === 1) {
    $current_assignment_sql_list .= ")";
    $current_assignment_go_time = false;
} else {
    $current_assignment_sql_list = substr($current_assignment_sql_list, 0, -2) . ")"; // Remove the ", " at the end and add a closing parenthesis
}
?>
<h2 class="text-center mt-1 mb-0">Current Assignments</h2>
<div class="ps-4 pe-4 pb-4">
<?php
    $curr_assignment_query = "SELECT assignment_name, date_due, point_value, document_link FROM Assignments WHERE assignment_name IN $current_assignment_sql_list";
    $curr_assignment_result = $conn->query($curr_assignment_query);
?>
    <div class="row">
<?php if($current_assignment_go_time === true): ?>
    <?php while($row = $curr_assignment_result->fetch_assoc()): ?>
        <?php
        $date_due_obj = null;
        if(array_key_exists($row["assignment_name"], $extension_name_array)) {
            try {
                $date_due_obj = new DateTime($extension_name_array[$row["assignment_name"]]);
            } catch(Exception $e) {
                die("Date Time error when printing current assignment due dates with extension!");
            }
        } else {
            try {
                $date_due_obj = new DateTime($row["date_due"]);
            } catch(Exception $e) {
                die("Date Time error when printing current assignment due dates without extension!");
            }
        }
        ?>
        <div class="col-lg-4 col-md-6 mt-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row["assignment_name"] . " - " . $row["point_value"] . " points";?></h5>
                    <h6 class="card-title"><?php echo "Due Date: " . $date_due_obj->format("l, F jS, Y, g:i:s A");?></h6>
                    <p class="card-text">Status: 0/2 parts completed (TODO).</p>
                    <div class="btn-group" role="group">
                        <a href="<?php echo $row["document_link"]?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">View Assignment</a>
                        <a href="<?php echo "submit.php?assignment_name=" . htmlspecialchars($row["assignment_name"], ENT_QUOTES)?>" class="btn btn-success">Submit Code</a>
                        <a href="<?php echo "view_test.php?assignment_name=" . htmlspecialchars($row["assignment_name"], ENT_QUOTES)?>" class="btn btn-primary">Test Program</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="h4 text-danger mt-4 text-center">No current assignments! You're free!</p>
<?php endif; ?>
    </div>
</div>
<hr class="m-0">
<h2 class="text-center mt-1 mb-0">Past Assignments</h2>
<div class="ps-4 pe-4 pb-4">
    <div class="row">
<?php
$past_assignment_query = "SELECT assignment_name, date_due, point_value, document_link FROM Assignments WHERE assignment_name IN $past_assignment_sql_list";
$past_assignment_result = $conn->query($past_assignment_query);
?>
<?php if($past_assignment_go_time === true): ?>
    <?php while($row = $past_assignment_result->fetch_assoc()): ?>
        <?php
        $date_due_obj = null;
        if(array_key_exists($row["assignment_name"], $extension_name_array)) {
            try {
                $date_due_obj = new DateTime($extension_name_array[$row["assignment_name"]]);
            } catch(Exception $e) {
                die("Date Time error when printing past assignment due dates with extension!");
            }
        } else {
            try {
                $date_due_obj = new DateTime($row["date_due"]);
            } catch(Exception $e) {
                die("Date Time error when printing past assignment due dates without extension!");
            }
        }
        ?>
        <div class="col-lg-4 col-md-6 mt-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row["assignment_name"] . " - " . $row["point_value"] . " points";?></h5>
                    <h6 class="card-title"><?php echo "Due Date: " . $date_due_obj->format("l, F jS, Y, g:i:s A");?></h6>
                    <p class="card-text">Status: 0/2 parts completed (TODO).</p>
                    <div class="btn-group" role="group">
                        <a href="<?php echo $row["document_link"]?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">View Assignment</a>
                        <a href="<?php echo "submit.php?assignment_name=" . htmlspecialchars($row["assignment_name"], ENT_QUOTES)?>" class="btn btn-success">Submit Code</a>
                        <a href="<?php echo "view_test.php?assignment_name=" . htmlspecialchars($row["assignment_name"], ENT_QUOTES)?>" class="btn btn-primary">Test Program</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="h4 text-danger mt-4 text-center">No past assignments! You're at the beginning of something great!</p>
<?php endif; ?>
    </div>
</div>
</body>
</html>
