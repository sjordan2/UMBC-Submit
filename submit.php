<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    <link rel='shortcut icon' type='image/x-icon' href='/favicon.png' />
    <title>UMBC Submit System</title>
</head>

<?php
include_once "./includes/header.php";

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

<style>
    html, body {
        height: 100%;
        width: 100%;
        overflow-x: hidden;
    }
    .ajs-message {
        min-width: 50vw !important;
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
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script type="text/javascript">
    //override defaults
    alertify.defaults.transition = "slide";
    alertify.defaults.theme.ok = "btn btn-primary";
    alertify.defaults.theme.cancel = "btn btn-danger";
    alertify.defaults.theme.input = "form-control";
    alertify.defaults.glossary.title = "UMBC Submit System"
</script>
<?php
$assignment_name = $conn->real_escape_string($_GET["assignment_name"]);
$get_parts_query = "SELECT part_name, point_value, extra_credit FROM SubmissionParts WHERE assignment_name = '$assignment_name'";
$get_parts_result = $conn->query($get_parts_query);
$part_counter = 1;
?>
<p id="assignmentTitle" hidden><?php echo $_GET["assignment_name"] ?></p>
<h2 class="text-center mt-1">Submission Parts for '<?php echo $_GET["assignment_name"] ?>'</h2>
<div class="p-2">
<?php while($row = $get_parts_result->fetch_assoc()):?>
    <div class="card text-center">
        <div class="card-body" id="<?php echo "card_part_" . $part_counter?>">
            <p hidden><?php echo $row["part_name"] ?></p>
            <p hidden><?php echo $part_counter?></p>
            <h4 class="card-title actual_title"><?php echo "Part " . $part_counter . ": " . $row["part_name"];?></h4>
            <?php if(intval($row["extra_credit"]) === 0): ?>
                <h5 class="card-title"><?php echo "Value: " . $row["point_value"] . " points";?></h5>
            <?php else: ?>
                <h5 class="card-title"><?php echo "Value: " . $row["point_value"] . " points (plus " . $row["extra_credit"] . " extra credit points)";?></h5>
            <?php endif;?>
            <?php $part_status = getPartSubmissionStatus($user_campus_id, $assignment_name, $conn->real_escape_string($row["part_name"]), $conn)?>
            <?php if($part_status === null): ?>
                <p class="card-text text-white bg-danger">Not Submitted</p>
                <button type="button" class="btn btn-outline-primary newSubmissionForPart">Start New Submission</button>
                <a href="<?php echo "view_test.php?assignment_name=" . htmlspecialchars($assignment_name, ENT_QUOTES) . "&part_name=" . htmlspecialchars($row["part_name"], ENT_QUOTES)?>" class="btn btn-outline-secondary disabled" role="button">View and Test Program</a>
            <?php else: ?>
                <p class="card-text text-white bg-success">Submitted - <?php echo $part_status ?></p>
                <button type="button" class="btn btn-outline-primary newSubmissionForPart">Start New Submission</button>
                <a href="<?php echo "view_test.php?assignment_name=" . htmlspecialchars($assignment_name, ENT_QUOTES) . "&part_name=" . htmlspecialchars($row["part_name"], ENT_QUOTES)?>" class="btn btn-outline-secondary" role="button">View and Test Program</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $part_counter++;?>
<?php endwhile; ?>
<div class="modal fade" id="newSubmissionBox">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="newSubmissionPartTitle">New Submission for&nbsp</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-1 pt-2">
                <span class="d-flex justify-content-center mt-2 d-none" id="spinnerDivNewSubmission">
                    <span class="spinner-border" role="status" id="spinnerObject">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </span>
            <div id="submitFilesDiv">
                <!-- Placeholder for AJAX Query -->
            </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success w-100" id="newSubmission_finalButton" type="button" disabled>Submit Uploaded Files</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="/javascript/submit.js" charset="utf-8"></script>
</body>
</html>
