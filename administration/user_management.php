<?php
require_once 'user_functions.php';
require_once '../includes/db_sql.php';
require_once '../includes/sql_functions.php';

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

$user_campus_id = $_SERVER["umbccampusid"];
$role = getEnrollment($user_campus_id, $conn);
if($role !== "Instructor" AND $user_campus_id !== $submit_system_admin) {
    header('Location: ../');
    exit();
}
?>

<style>
    #newUserOverLay, #editUserOverlay, #fileUploadOverlay {
        position: absolute;
        top: 0;
        left: 0;
        background-color: rgba(0,0,0,0.5);
        z-index: 999;
    }
    #newUserBox, #editUserBox, #fileUploadBox {
        background-color: white;
        max-height: 83vh;
    }
    #newUser_finalButton, #editUser_finalButton {
        color: #5e00ca;
        background-color: white;
        border-color: #5e00ca;
    }
    #newUser_finalButton:hover, #editUser_finalButton:hover {
        color: white;
        background-color: #5e00ca;
    }
    #courseCountDiv {
        margin-left: 1%;
    }
    .ajs-message {
        min-width: 50vw !important;
    }
    #editUserTitle, #editUserIDTitle {
        display: inline;
    }
    #fileUploadStatusDiv {
        background-color: #e4e7eb !important;
    }
    html, body {
        height: 100%;
        width: 100%;
        overflow-x: hidden;
    }
    th {
        background-color: darkgray !important;
    }
    @media (min-width: 410px) {
        #searchUsers {
            float: right;
            margin-right: 1%;
        }
    }
    @media (max-width: 410px) {
        #searchUsers {
            width: 100% !important;
        }
    }
    #spinnerObject {
        width: 5rem;
        height: 5rem;
    }
</style>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css">
    <link rel='shortcut icon' type='image/x-icon' href='/favicon.png' />
    <title>User Management</title>
</head>
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
<?php include_once "../includes/header.php";?>
<div id='masterContainer'>
<?php if($conn->connect_error): ?>
    <script>
        alertify.set('notifier','position', 'top-center');
        alertify.set('notifier','delay', 0);
        alertify.error('Fatal Error: Could not connect to SQL server: ' + "<?php echo $conn->connect_error?>");
    </script>
<?php endif; ?>
<div class="container mt-3" id="buttonsRow">
    <div class="row">
        <div class="col-sm d-grid mb-3">
            <?php if($conn->connect_error): ?>
                <button class="btn btn-secondary" type="button" id="addNewUserButton" disabled><i class="bi bi-person-plus"></i>&nbsp&nbsp Add New User</button>
            <?php else: ?>
                <button class="btn btn-secondary" type="button" id="addNewUserButton"><i class="bi bi-person-plus"></i>&nbsp&nbsp Add New User</button>
            <?php endif; ?>
        </div>
        <div class="col-sm d-grid mb-3">
            <?php if($conn->connect_error): ?>
                <button class="btn btn-secondary" type="button" id="fileUploadButton" disabled><i class="bi bi-upload"></i>&nbsp&nbsp Upload Student Roster From REX</button>
            <?php else: ?>
                <button class="btn btn-secondary" type="button" id="fileUploadButton"><i class="bi bi-upload"></i>&nbsp&nbsp Upload Student Roster From REX</button>
            <?php endif; ?>
        </div>
        <div class="col-sm d-grid mb-3">
            <?php
            $email_count_query = "SELECT umbc_id FROM Users WHERE email_sent = '0'";
            $email_count_result = $conn->query($email_count_query);
            ?>
            <?php if($email_count_result->num_rows === 0): ?>
                <button class="btn btn-secondary" type="button" id="emailAllUsersButton" disabled><i class="bi bi-envelope-fill"></i>&nbsp&nbsp All Users Emailed</button>
            <?php elseif($email_count_result->num_rows === 1): ?>
                <button class="btn btn-secondary" type="button" id="emailAllUsersButton"><i class="bi bi-envelope-fill"></i>&nbsp&nbsp Email 1 New User</button>
            <?php else: ?>
                <button class="btn btn-secondary" type="button" id="emailAllUsersButton"><i class="bi bi-envelope-fill"></i>&nbsp&nbsp Email <?php echo $email_count_result->num_rows; ?> New Users</button>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="align-items-center d-flex justify-content-center h-100 w-100 d-none" id="newUserOverlay">
<div class="p-3 overflow-auto" id="newUserBox">
    <div class="container">
        <div class="row">
            <div class="col">
                <h3>Add New User</h3>
            </div>
            <div class="col-auto">
                <button class="btn btn-danger" id="exit_newUserOverlay">X</button>
            </div>
        </div>
    </div>
    <form id="newUserForm">
        <div class="mb-2">
            <label for="newUser_firstName" class="form-label">First Name</label>
            <input type="text" class="form-control" id="newUser_firstName" placeholder="Enter Name Here...">
            <div class="invalid-feedback">
                Please enter a valid first name.
            </div>
        </div>
        <div class="mb-2">
            <label for="newUser_lastName" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="newUser_lastName" placeholder="Enter Name Here...">
            <div class="invalid-feedback">
                Please enter a valid last name.
            </div>
        </div>
        <div class="mb-2">
            <label for="newUser_campusID" class="form-label">Campus ID</label>
            <input type="text" class="form-control" id="newUser_campusID" placeholder="Enter Campus ID Here...">
            <div class="invalid-feedback">
                Please enter a valid Campus ID.
            </div>
        </div>
        <div class="mb-2">
            <label for="newUser_nameID" class="form-label">Name ID</label>
            <input type="text" class="form-control" id="newUser_nameID" placeholder="Enter Name ID Here...">
            <div class="invalid-feedback">
                Please enter a valid Name ID.
            </div>
        </div>
        <div class="mb-2">
            <label for="newUser_discussion" class="form-label">Discussion Section</label>
            <input type="number" class="form-control" id="newUser_discussion" placeholder="Enter Discussion Section Here..."min="1">
            <div class="invalid-feedback">
                Please enter a valid discussion section.
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="newUser_noDiscussionCheck">
                <label class="form-check-label" for="newUser_noDiscussionCheck">
                    No Discussion Section
                </label>
                <a id="newUser_discussionTooltip" class="d-inline-block infoHelp_tip" data-bs-toggle="tooltip" data-bs-placement="right" title="Select this option if the user does not have an assigned discussion section (e.g. there are more TAs than there are discussion sections in a particular semester)">
                    <i class="bi bi-info-circle"></i>
                </a>
            </div>
        </div>
        <div>
            <label class="form-label">Role</label>
        </div>
        <div class="btn-group justify-content-center mb-3" role="group" aria-label="Role Selection" id="newUser_roleSelectionGroup">
            <input type="radio" class="btn-check" name="newUser_roleRadio" id="newUser_studentRadio" autocomplete="off" checked>
            <label class="btn btn-outline-success" for="newUser_studentRadio">Student</label>

            <input type="radio" class="btn-check" name="newUser_roleRadio" id="newUser_taRadio" autocomplete="off">
            <label class="btn btn-outline-primary" for="newUser_taRadio">Teaching Assistant</label>

            <input type="radio" class="btn-check" name="newUser_roleRadio" id="newUser_instructorRadio" autocomplete="off">
            <label class="btn btn-outline-danger" for="newUser_instructorRadio">Instructor</label>
        </div>
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-outline-dark" id="newUser_finalButton">Create New User</button>
        </div>
    </form>
</div>
</div>
<div class="align-items-center d-flex justify-content-center h-100 w-100 d-none" id="editUserOverlay">
    <div class="p-3 overflow-auto" id="editUserBox">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h3 id="editUserTitle">Edit User:&nbsp</h3><h3 id="editUserIDTitle"></h3>
                </div>
                <div class="col-auto">
                    <button class="btn btn-danger" id="exit_editUserOverlay">X</button>
                </div>
            </div>
        </div>
        <form id="editUserForm">
            <div class="mb-2">
                <label for="editUser_firstName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="editUser_firstName" placeholder="Enter Name Here...">
                <div class="invalid-feedback">
                    Please enter a valid first name.
                </div>
            </div>
            <div class="mb-2">
                <label for="editUser_lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="editUser_lastName" placeholder="Enter Name Here...">
                <div class="invalid-feedback">
                    Please enter a valid last name.
                </div>
            </div>
            <div class="mb-2">
                <label for="editUser_nameID" class="form-label">Name ID</label>
                <input type="text" class="form-control" id="editUser_nameID" placeholder="Enter Name ID Here...">
                <div class="invalid-feedback">
                    Please enter a valid Name ID.
                </div>
            </div>
            <div class="mb-2">
                <label for="editUser_discussion" class="form-label">Discussion Section</label>
                <input type="number" class="form-control" id="editUser_discussion" placeholder="Enter Discussion Section Here..." min="1">
                <div class="invalid-feedback">
                    Please enter a valid discussion section.
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="editUser_noDiscussionCheck">
                    <label class="form-check-label" for="editUser_noDiscussionCheck">
                        No Discussion Section
                    </label>
                    <a id="editUser_discussionTooltip" class="d-inline-block infoHelp_tip" data-bs-toggle="tooltip" data-bs-placement="right" title="Select this option if the user does not have an assigned discussion section (e.g. there are more TAs than there are discussion sections in a particular semester)">
                        <i class="bi bi-info-circle"></i>
                    </a>
                </div>
            </div>
            <div>
                <label class="form-label">Role</label>
            </div>
            <div class="btn-group justify-content-center mb-3" role="group" aria-label="Role Selection" id="roleSelectionGroup">
                <input type="radio" class="btn-check" name="editUser_roleRadio" id="editUser_studentRadio" autocomplete="off">
                <label class="btn btn-outline-success" for="editUser_studentRadio">Student</label>

                <input type="radio" class="btn-check" name="editUser_roleRadio" id="editUser_taRadio" autocomplete="off">
                <label class="btn btn-outline-primary" for="editUser_taRadio">Teaching Assistant</label>

                <input type="radio" class="btn-check" name="editUser_roleRadio" id="editUser_instructorRadio" autocomplete="off">
                <label class="btn btn-outline-danger" for="editUser_instructorRadio">Instructor</label>
            </div>
            <div>
                <label class="form-label">Status</label>
            </div>
            <div class="btn-group justify-content-center mb-3 d-flex" role="group" id="statusUpdateGroup">
                <input type="radio" class="btn-check" name="statusUpdate" id="statusUpdate_Active" autocomplete="off">
                <label class="btn btn-outline-success" for="statusUpdate_Active">Active</label>

                <input type="radio" class="btn-check" name="statusUpdate" id="statusUpdate_Dropped" autocomplete="off">
                <label class="btn btn-outline-danger" for="statusUpdate_Dropped">Dropped</label>
            </div>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-dark" id="editUser_finalButton">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<div class="align-items-center d-flex justify-content-center h-100 w-100 d-none" id="fileUploadOverlay">
    <div class="p-3 overflow-auto" id="fileUploadBox">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h3 class="d-inline-block">Upload Roster File</h3>
                    <a id="fileUpload_Tooltip" class="d-inline-block infoHelp_tip" data-bs-toggle="tooltip" data-bs-placement="top" title="The only data columns needed for this file upload are: 'StudentLastName', 'StudentFirstName', 'StudentCampusID', 'StudentMyUMBCId', and 'ClassNumberClassSectionSourceKey'. Any other student information is discarded and not used.">
                        <i class="bi bi-info-circle"></i>
                    </a>
                </div>
                <div class="col-auto">
                    <button class="btn btn-danger" id="exit_fileUploadOverlay">X</button>
                </div>
            </div>
        </div>
        <form id="fileUploadForm">
            <div class="mb-3 mt-3">
                <input class="form-control" type="file" id="rexFile">
            </div>
            <div id='fileUploadStatusDiv' class="mb-3 bg-light input-group">
                <span class="input-group-text align-items-center bg-secondary w-100" id="fileUploadSymbolBackground"><i id='fileUploadStatusSymbol' class="bi bi-info-circle text-white"></i></span>
                <p id='fileUploadStatusText' class="mt-3 ms-3">No file selected yet!</p>
            </div>
            <div id="submitFileDiv" class="mb-3">
                <button type="button" class="btn btn-outline-success w-100" id="fileUpload_submit" disabled>Submit File</button>
            </div>
        </form>
    </div>
</div>
<?php
$user_result = null;
if($conn->connect_error === null) { // If the connection was successful
    $user_list_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname, section, role, status FROM Users";
    $user_result = $conn->query($user_list_sql);
}
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
<div id="totalUsersDiv">
<div id="courseCountDiv" class="w-100">
    <?php if($conn->connect_error === null): ?> <!-- If the connection was successful -->
        <h3 id="userCount" class="d-inline">Total Users: <?php echo $user_result->num_rows; ?></h3>
    <?php else: ?>
        <h3 id="userCount" class="d-inline">Total Users: 0</h3>
    <?php endif; ?>
    <label for="searchUsers"></label><input type="text" id="searchUsers" placeholder="Enter search term here..." class="w-50">
</div>
<div id="studentListDiv">
    <div class="d-flex justify-content-center d-block mt-4" id="spinnerDiv">
        <div class="spinner-border" role="status" id="spinnerObject">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <table id="usersTable" data-search="true" data-search-selector="#searchUsers" data-pagination="true" data-page-size="25">
    </table>
</div>
</div>
</div>
<script type="text/javascript" src="/javascript/user_management.js" charset="utf-8"></script>
</body>
</html>