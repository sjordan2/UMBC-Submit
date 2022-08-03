<!doctype html>
<html lang="en">
<style>
    #selectedFileText {
        max-height: 70vh;
    }
</style>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.2.0/styles/default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rainbow/1.2.0/themes/obsidian.min.css" integrity="sha512-eS98R6OTbikWigm7ENo2EsvY/64ltvfWYC7GC+NpebYF/BZ4f82XrC6P1jmMfzL3VwCuC6k2KjoRTJXk3ERJwQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel='shortcut icon' type='image/x-icon' href='/favicon.png' />
    <title>UMBC Submit System</title>
</head>

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

$assignment_is_good = false;
$given_assignment_name = null;
if(isset($_GET["assignment_name"])) {
    $given_assignment_name = $_GET["assignment_name"];
    if($given_assignment_name !== "") {
        $assignment_name_check_sql = "SELECT assignment_name FROM Assignments WHERE assignment_name = '$given_assignment_name'";
        $assignment_name_check_result = $conn->query($assignment_name_check_sql);
        if($assignment_name_check_result->num_rows === 0) { // That assignment doesn't exist
            header('Location: ../assignments.php');
            exit();
        } else {
            $assignment_is_good = true;
        }
    } else {
        header('Location: ../assignments.php');
        exit();
    }
} else {
    header('Location: ../assignments.php');
    exit();
}

$part_is_true = false;
$given_part_name = null;
if(isset($_GET["part_name"])) {
    $given_part_name = urldecode($_GET["part_name"]);
    if($given_part_name !== "") {
        $check_parts_sql = "SELECT part_name FROM SubmissionParts WHERE assignment_name = '$given_assignment_name' AND part_name = '$given_part_name'";
        $check_parts_result = $conn->query($check_parts_sql);
        if($check_parts_result->num_rows === 0) {
            header('Location: ../assignments.php');
            exit();
        } else {
            $part_is_true = true;
        }
    } else {
        header('Location: ../assignments.php');
        exit();
    }
}
include_once "./includes/header.php";
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
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.2.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/rainbow/1.2.0/js/rainbow.min.js" integrity="sha512-1iwOtzgGTn5KiNhyTHdGh8IpixXZnsD0vRXUqNUgWqET4Uv3vDXuHq55cGjdJ+qNBL/HxH815N7qvLxgzA1dYw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/rainbow/1.2.0/js/language/generic.min.js" integrity="sha512-KdE3K98nhI99yInPx0Gt56dWBzCR4LLjZLb9kF7cyNplwlFohmc7pYe8A2j7M3v6iw5zrHNU9vQ3uG3CjXIi8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/rainbow/1.2.0/js/language/python.min.js" integrity="sha512-Ipp3Al6qIGqI9eUXo7yfKTS93FwkVoOaJV4zSBE8HYFxrQxUmhVdDgB+TjonlqUl+rEcJoxmkFcphVLL7P1OBg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    //override defaults
    alertify.defaults.transition = "slide";
    alertify.defaults.theme.ok = "btn btn-primary";
    alertify.defaults.theme.cancel = "btn btn-danger";
    alertify.defaults.theme.input = "form-control";
    alertify.defaults.glossary.title = "UMBC Submit System"
</script>
<h2 class="text-center mt-1">View and Test Code for '<?php echo $given_assignment_name ?>'</h2>
<div class="text-center">
    <label for="partSelectionDropdown">Select a Part</label>
    <select class="form-select mx-auto w-50" id="partSelectionDropdown">
    <?php if($part_is_true === false): ?>
    <!-- Display select dropdown with all part options, but none selected -->
        <option selected disabled value="">No part selected!</option>
        <?php
        $query_parts_sql = "SELECT part_name FROM SubmissionParts WHERE assignment_name = '$given_assignment_name'";
        $query_parts_result = $conn->query($query_parts_sql);
        $counter = 1;
        ?>
        <?php while($row = $query_parts_result->fetch_assoc()): ?>
        <option value="<?php echo htmlspecialchars($row["part_name"], ENT_QUOTES); ?>"><?php echo "Part " . $counter . ": " . $row["part_name"]; ?></option>
        <?php endwhile; ?>
    <?php else: ?>
        <option disabled>Select a part...</option>
        <?php
        $query_parts_sql = "SELECT part_name FROM SubmissionParts WHERE assignment_name = '$given_assignment_name'";
        $query_parts_result = $conn->query($query_parts_sql);
        $counter = 1;
        ?>
        <?php while($row = $query_parts_result->fetch_assoc()): ?>
            <?php if($row["part_name"] === $given_part_name): ?>
                <option selected value="<?php echo htmlspecialchars($row["part_name"], ENT_QUOTES); ?>"><?php echo "Part " . $counter . ": " . $row["part_name"] ?></option>
            <?php else: ?>
                <option value="<?php echo htmlspecialchars($row["part_name"], ENT_QUOTES); ?>"><?php echo "Part " . $counter . ": " . $row["part_name"] ?></option>
            <?php endif ?>
            <?php $counter++; ?>
        <?php endwhile; ?>
    <?php endif; ?>
    </select>
</div>
<div id="submissionSelectionDiv" class="d-none">
    <label for="submissionSelection">Select a Submission</label>
    <select id="submissionSelection">
    </select>
</div>
<div class="mt-2">
    <ul class="nav nav-tabs" id="submittedFileTabs">

    </ul>
    <div id="selectedFileText" class="p-2 overflow-scroll pb-0">

    </div>
</div>
<script type="text/javascript" src="/javascript/view_test.js" charset="utf-8"></script>
</body>
</html>