<?php
require_once 'assignment_functions.php';
require_once '../includes/db_sql.php';
require_once '../includes/sql_functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

$user_campus_id = $_SERVER["umbccampusid"];
$role = getEnrollment($user_campus_id, $conn);
if($role !== "Instructor" AND $user_campus_id !== $submit_system_admin) {
    header('Location: ../');
    exit();
}
?>
<style>
    #configureAssignmentBox, #submissionFilesBox {
        max-height: 90vh;
    }
    #selectStudentForExtensionBox {
        max-height: 96vh;
    }
    #gradingRubricBox {
        max-height: 95vh;
     }
    #gradingRubricBody {
        max-height: 67vh;
    }
    #newAssignment_finalButton {
        color: #5e00ca;
        background-color: white;
        border-color: #5e00ca;
    }
    #newAssignment_finalButton:hover {
        color: white;
        background-color: #5e00ca;
    }
    .visible_button, .show_sub_files_button {
        background-color: #5e00ca !important;
        border-color: #5e00ca !important;
    }
    .visible_button:hover, .show_sub_files_button:hover {
        background-color: #4c00a7 !important;
    }
    #spinnerObject {
        width: 5rem;
        height: 5rem;
    }
    .ajs-message {
        min-width: 50vw !important;
    }
    th {
        background-color: darkgray !important;
    }
    html, body {
        height: 100%;
        width: 100%;
        overflow-x: hidden;
    }
    .nav-tabs{
        display:inline-flex;
    }
    .configure_tab:not(.active) {
        cursor: pointer;
        color: #1560f4;
    }
    .configure_tab.active {
        background-color: #1560f4 !important;
        color: white !important;
    }
    @media screen and (max-width: 768px) { /* Because bootstrap doesn't have breakpoint support for width and height >:( */
        #newAssignmentBox {
            min-width: 100vw;
        }
    }
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css">
    <link rel='shortcut icon' type='image/x-icon' href='/favicon.png' />
    <title>Assignment Management</title>
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
<?php if($conn->connect_error): ?>
    <script>
        alertify.set('notifier','position', 'top-center');
        alertify.set('notifier','delay', 0);
        alertify.error('Fatal Error: Could not connect to SQL server: ' + "<?php echo $conn->connect_error?>");
    </script>
<?php endif; ?>
<div class="container mt-3" id="buttonsRow">
    <div class="row">
        <div class="col">
            <?php if($conn->connect_error): ?>
                <button class="btn btn-secondary w-100" type="button" id="createNewAssignmentButton" disabled><i class="bi bi-plus-lg"></i>&nbsp&nbsp Create New Assignment</button>
            <?php else: ?>
                <button class="btn btn-secondary w-100" type="button" id="createNewAssignmentButton"><i class="bi bi-plus-lg"></i>&nbsp&nbsp Create New Assignment</button>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="modal fade" id="newAssignmentBox">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create New Assignment</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newAssignmentForm">
                    <div class="mb-2">
                        <label for="newAssignment_name" class="form-label">Assignment Name</label>
                        <input type="text" class="form-control new_validation" id="newAssignment_name" placeholder="Enter Assignment Name Here..." required>
                        <div class="invalid-feedback">
                            Please enter a valid assignment name.
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="newAssignment_pointValue" class="form-label">Point Value</label>
                        <input type="number" class="form-control new_validation" id="newAssignment_pointValue" placeholder="Enter Point Value..." min="1">
                        <div class="invalid-feedback">
                            Please choose a valid point value.
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <div class="col">
                            <label for="newAssignment_dueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control new_validation" id="newAssignment_dueDate">
                            <div class="invalid-feedback">
                                Please choose a valid date time combination that is in the future.
                            </div>
                        </div>
                        <div class="col">
                            <label for="newAssignment_dueTime" class="form-label">Due Time</label>
                            <input type="time" class="form-control new_validation" id="newAssignment_dueTime" step="1">
                            <div class="invalid-feedback">
                                Please choose a valid date time combination that is in the future.
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Class Type</label>
                    </div>
                    <div class="btn-group justify-content-center mb-3 w-100" role="group" id="classSelectionGroup">
                        <input type="radio" class="btn-check" name="newAssignment_typeRadio" id="newAssignment_classSelect_Majors" autocomplete="off">
                        <label class="btn btn-outline-dark" for="newAssignment_classSelect_Majors">Majors Only</label>

                        <input type="radio" class="btn-check" name="newAssignment_typeRadio" id="newAssignment_classSelect_Both" autocomplete="off" checked>
                        <label class="btn btn-outline-dark" for="newAssignment_classSelect_Both">Both</label>

                        <input type="radio" class="btn-check" name="newAssignment_typeRadio" id="newAssignment_classSelect_NonMajors" autocomplete="off">
                        <label class="btn btn-outline-dark" for="newAssignment_classSelect_NonMajors">Non-Majors Only</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark" id="newAssignment_finalButton">Create Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="configureAssignmentBox">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Configure Assignment:&nbsp</h3><h3 id="configureAssignmentIDTitle" class="modal-title"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link configure_tab active disabled" id="detailsTab">Assignment Details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link configure_tab" id="submissionsTab">Submission Parts and Files</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link configure_tab" id="gradingTab">Testing and Grading</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link configure_tab" id="extensionsTab">Student Extensions</a>
                        </li>
                    </ul>
                </div>
                <span class="d-flex justify-content-center mt-4 d-block" id="spinnerDivConfigure">
                    <span class="spinner-border" role="status" id="spinnerObject">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </span>
                <div id="detailsPanel" class="d-none">
                    <form id="configureAssignment_form">
                        <div class="mt-1 mb-3">
                            <label for="configureAssignment_pointValue" class="form-label">Overall Point Value</label>
                            <div>
                                <input type="number" class="form-control edit_validation" id="configureAssignment_pointValue" placeholder="Enter Point Value..." min="1">
                                <div class="invalid-feedback">
                                    Please choose a valid point value.
                                </div>
                            </div>
                        </div>
                        <div class="mt-1 mb-3 row">
                            <div class="col">
                                <label for="configureAssignment_dueDate" class="form-label">Course-Wide Due Date</label>
                                <input type="date" class="form-control edit_validation" id="configureAssignment_dueDate">
                                <div class="invalid-feedback">
                                    Please choose a valid date time combination that is in the future.
                                </div>
                            </div>
                            <div class="col">
                                <label for="configureAssignment_dueTime" class="form-label">Course-Wide Due Time</label>
                                <input type="time" class="form-control edit_validation" id="configureAssignment_dueTime" step="1">
                                <div class="invalid-feedback">
                                    Please choose a valid date time combination that is in the future.
                                </div>
                            </div>
                        </div>

                        <label for="configureAssignment_classSelectionGroup" class="form-label">Class Type</label>
                        <div class="btn-group justify-content-center mb-3 w-100" role="group" id="configureAssignment_classSelectionGroup">
                            <input type="radio" class="btn-check" name="configureAssignment_typeRadio" id="configureAssignment_classSelect_Majors" autocomplete="off">
                            <label class="btn btn-outline-dark" for="configureAssignment_classSelect_Majors">Majors Only</label>

                            <input type="radio" class="btn-check" name="configureAssignment_typeRadio" id="configureAssignment_classSelect_Both" autocomplete="off">
                            <label class="btn btn-outline-dark" for="configureAssignment_classSelect_Both">Both</label>

                            <input type="radio" class="btn-check" name="configureAssignment_typeRadio" id="configureAssignment_classSelect_NonMajors" autocomplete="off">
                            <label class="btn btn-outline-dark" for="configureAssignment_classSelect_NonMajors">Non-Majors Only</label>
                        </div>

                        <div>
                            <label for="currDocLinkInput" id="docLinkLabel" class="form-label">Current Document Link</label><br>
                            <div class="input-group">
                                <input id="currDocLinkInput" type="text" class="form-control edit_validation" placeholder="Enter Document Link Here...">
                                <button class="btn btn-secondary configure_button" id="followLinkButton" type="button" data-bs-toggle='tooltip' title='Open Link in New Tab'><i class="bi bi-box-arrow-up-right"></i></button>
                            </div>
                        </div>
                    </form>
                    <div class="overflow-auto" id="saveConfigureAssignmentChangesDiv">
                        <button class="btn btn-success w-100" id="configureAssignment_finalButton" type="button">Save Changes</button>
                    </div>
                </div>
                <div id="submissionsPanel" class="d-none">
                    <button class="btn btn-secondary w-100 mt-2" type="button" id="createNewSubmissionPartButton" ><i class="bi bi-plus-lg"></i>&nbsp&nbsp Add New Submission Part</button>
                    <div class="mt-2 h-50">
                        <table id="submissionPartsTable">
                        </table>
                    </div>
                </div>
                <div id="gradingPanel" class="d-none">
                    <div class="mt-3">
                        <label for="selectSubPart"></label><select class="form-select" id="selectSubPart">
                            <option disabled selected>Select a Submission Part...</option>
                        </select>
                    </div>
                    <div class="mt-3 container">
                        <div class="row">
                            <div class="col">
                                <button class="btn btn-outline-danger btn-lg w-100 gradingTestingButton" type="button" id="gradingRubricButton" disabled><i class="bi bi-table"></i><br>Grading Rubric</button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <button class="btn btn-outline-success btn-lg w-100 gradingTestingButton" type="button" id="sampleMakefileButton" disabled><i class="bi bi-file-earmark-code"></i><br>Sample Makefile</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-outline-primary btn-lg w-100 gradingTestingButton" type="button" id="testingMakefileButton" disabled><i class="bi bi-file-earmark-code"></i><br>Testing Makefile</button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <button class="btn btn-outline-success btn-lg w-100 gradingTestingButton" type="button" id="sampleIOFilesButton" disabled><i class="bi bi-file-earmark-text"></i><br>Sample IO Files</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-outline-primary btn-lg w-100 gradingTestingButton" type="button"  id="testingIOFilesButton" disabled><i class="bi bi-file-earmark-text"></i><br>Testing IO Files</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="extensionsPanel" class="d-none">
                    <button class="btn btn-secondary w-100 mt-2" type="button" id="createNewExtensionButton" ><i class="bi bi-plus-lg"></i>&nbsp&nbsp Add New Extension</button>
                    <div class="mt-2 h-50">
                        <table id="extensionsTable">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="newSubmissionPartBox">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Submission Part for&nbsp</h3><h3 id="submissionPartIDTitle" class="modal-title"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newSubmissionPart_form">
                    <div class="mt-1 mb-3">
                        <label for="newSubmissionPart_name" class="form-label">Submission Part Name</label>
                        <div class="mb-3">
                            <input type="text" class="form-control subPart_validation" id="newSubmissionPart_name" placeholder="Enter Part Name...">
                            <div class="invalid-feedback">
                                Please enter a valid submission part name.
                            </div>
                        </div>
                        <label for="newSubmissionPart_pointValue" class="form-label">Point Value</label>
                        <div class="mb-3">
                            <input type="number" class="form-control subPart_validation" id="newSubmissionPart_pointValue" placeholder="Enter Point Value..." min="0">
                            <div class="invalid-feedback">
                                Please choose a valid point value.
                            </div>
                        </div>
                        <label for="newSubmissionPart_extraCredit" class="form-label">Extra Credit Points</label>
                        <div>
                            <input type="number" class="form-control subPart_validation" id="newSubmissionPart_extraCredit" placeholder="Enter Point Value..." value="0" min="0">
                            <div class="invalid-feedback">
                                Please choose a valid extra credit value.
                            </div>
                        </div>
                    </div>
                </form>
                <div class="overflow-auto" id="addNewSubmissionPartDiv">
                    <button class="btn btn-success w-100" id="addNewSubmissionPart_Button" type="button">Add New Submission Part</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editSubmissionPartBox">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Edit Submission Part:&nbsp</h3><h3 id="editSubmissionPartIDTitle" class="modal-title"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSubmissionPart_form">
                    <div class="mt-1 mb-3">
                        <label for="editSubmissionPart_pointValue" class="form-label">Point Value</label>
                        <div class="mb-3">
                            <input type="number" class="form-control editSubPart_validation" id="editSubmissionPart_pointValue" placeholder="Enter Point Value..." min="0">
                            <div class="invalid-feedback">
                                Please enter a valid point value.
                            </div>
                        </div>
                        <label for="editSubmissionPart_extraCredit" class="form-label">Extra Credit Points</label>
                        <div>
                            <input type="number" class="form-control editSubPart_validation" id="editSubmissionPart_extraCredit" placeholder="Enter Point Value..." min="0">
                            <div class="invalid-feedback">
                                Please enter a valid extra credit value.
                            </div>
                        </div>
                    </div>
                </form>
                <div class="overflow-auto" id="editSubmissionPartDiv">
                    <button class="btn btn-success w-100" id="editSubmissionPart_Button" type="button">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="submissionFilesBox">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Submission Files for&nbsp</h3><h3 id="submissionFilesTitle" class="modal-title"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span class="d-flex justify-content-center mt-2 d-block" id="spinnerDivSubFiles">
                    <span class="spinner-border" role="status" id="spinnerObject">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </span>
                <table id="submissionFilesTable">
                </table>
                <button class="btn btn-primary w-100 mt-3" id="addNewSubmissionFile" type="button">Add New File</button>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success w-100" id="submissionFiles_Button" type="button">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="gradingRubricBox">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Grading Rubric for&nbsp</h3><h3 id="gradingRubricTitle" class="modal-title"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto" id="gradingRubricBody">
                <span class="d-flex justify-content-center mt-2 d-block" id="spinnerDivGradingRubric">
                    <span class="spinner-border" role="status" id="spinnerObject">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </span>
                <table id="gradingRubricTable">
                </table>
            </div>
            <div class="modal-footer">
                <div class="w-100 mt-0 mb-2">
                    <p class="d-inline h5">Total Allowance For Rubric:&nbsp</p><p class="fw-bold d-inline h4" id="totalPartPointsID"></p><p class="d-inline h5" id="totalPartPointsEnding">&nbsppoints</p>
                    <p class="d-none extra_credit_rubric_text h5"> (of which&nbsp</p><p class="d-none extra_credit_rubric_text fw-bold h4" id="extraCreditRubricPointValue"></p><p class="d-none extra_credit_rubric_text h5">&nbspare extra credit points)</p>
                </div>
                <button class="btn btn-success w-100" id="gradingRubric_saveButton" type="button">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="makefileBox">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="sampleOrTestingMakefile"></h3><h3 class="modal-title">&nbspMakefile for&nbsp</h3><h3 id="makefilePartTitle" class="modal-title"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto" id="makefileBody">
                <span class="d-flex justify-content-center mt-2 d-block" id="spinnerDivMakefile">
                    <span class="spinner-border" role="status" id="spinnerObject">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </span>
                <table id="makefileTable">
                </table>
                <button class="btn btn-secondary w-100 mt-3" id="uploadFile_Makefile" type="button">Upload New Makefile</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uploadMakefileBox">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Upload&nbsp</h3><h3 class="modal-title" id="sampleOrTestingMakefile_Upload"></h3><h3 class="modal-title">&nbspMakefile</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto" id="makefileBody">
                <div class="mb-3">
                    <label for="uploadedMakefile" class="form-label">Upload Makefile Here</label>
                    <input class="form-control" type="file" id="uploadedMakefile">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success w-100" id="submit_uploadedMakefile" type="button" disabled>Submit File</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="IOFileBox">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="sampleOrTestingIOFile"></h3><h3 class="modal-title">&nbspIO Files for&nbsp</h3><h3 id="IOFilePartTitle" class="modal-title"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto" id="IOFileBody">
                <span class="d-flex justify-content-center mt-2 d-block" id="spinnerDivIOFile">
                    <span class="spinner-border" role="status" id="spinnerObject">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </span>
                <table id="IOFileTable">
                </table>
                <button class="btn btn-secondary w-100 mt-3" id="uploadFile_IOFile" type="button">Upload New IO File</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uploadIOFileBox">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Upload&nbsp</h3><h3 class="modal-title" id="sampleOrTestingIOFile_Upload"></h3><h3 class="modal-title">&nbspIO File</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto">
                <div class="mb-3">
                    <label for="uploadedIOFile" class="form-label">Upload File Here</label>
                    <input class="form-control" type="file" id="uploadedIOFile">
                </div>
                <div class="mb-3">
                    <label for="uploadedIOFile_type" class="form-label">IO File Type</label>
                    <select class="form-select" id="uploadedIOFile_type">
                        <option selected>Input File</option>
                        <option>Output File</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success w-100" id="submit_uploadedIOFile" type="button" disabled>Submit File</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="previewFileBox">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Preview&nbsp</h3><h3 class="modal-title" id="whatTypeOfFile"></h3><h3 class="modal-title" id="fileNameIfApplicable"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto" id="previewFileBody">
                <div>
                    <pre id="fileContents" class="text-white bg-dark p-2">
                    </pre>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="grantNewExtensionBox">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Grant New Extension for&nbsp</h3><h3 class="modal-title" id="assignmentForExtension"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto">
                <form id="grantNewExtension_form">
                    <div class="mt-1">
                        <div class="mb-2">
                            <label for="grantNewExtension_student" class="form-label">Student Name and ID</label>
                            <div class="input-group flex-nowrap mb-0 has-validation">
                                <input type="text" class="form-control grantNewExtension_validation" id="grantNewExtension_student" readonly>
                                <button type="button" class="btn btn-primary" id="selectStudentForExtension">Select Student</button>
                            </div>
                            <div class="text-danger d-none" id="inputGroupValidationNoWork">
                                <small>You must select a student.</small>
                            </div>
                        </div>
                        <div id="currentDueDateDiv mb-3" class="container">
                            <div class="row">
                                <p class="mb-0">Current Course-Wide Assignment Due Date:</p>
                            </div>
                            <div class="row">
                                <p class="fw-bold" id="courseWideDueDate_extension"></p>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <div class="col">
                                <label for="grantNewExtension_dueDate" class="form-label">New Due Date</label>
                                <input type="date" class="form-control grantNewExtension_validation" id="grantNewExtension_dueDate">
                                <div class="invalid-feedback">
                                    Please choose a valid date time combination that is both past the current assignment due date and in the future.
                                </div>
                            </div>
                            <div class="col">
                                <label for="grantNewExtension_dueTime" class="form-label">New Due Time</label>
                                <input type="time" class="form-control grantNewExtension_validation" id="grantNewExtension_dueTime" step="1">
                                <div class="invalid-feedback">
                                    Please choose a valid date time combination that is both past the current assignment due date and in the future.
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success w-100" id="grantNewExtension_saveButton" type="button">Grant Extension</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editExtensionBox">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Extension for&nbsp</h3><h3 class="modal-title" id="userForExtensionEdit"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto">
                <form id="editExtension_form">
                    <div class="mt-1">
                        <div id="mb-3" class="container">
                            <div class="row">
                                <p class="mb-0">Current Course-Wide Assignment Due Date:</p>
                            </div>
                            <div class="row">
                                <p class="fw-bold" id="courseWideDueDate_editExtension"></p>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <div class="col">
                                <label for="editExtension_dueDate" class="form-label">New Due Date</label>
                                <input type="date" class="form-control editExtension_validation" id="editExtension_dueDate">
                                <div class="invalid-feedback">
                                    Please choose a valid date time combination that is both past the current assignment due date and in the future.
                                </div>
                            </div>
                            <div class="col">
                                <label for="editExtension_dueTime" class="form-label">New Due Time</label>
                                <input type="time" class="form-control editExtension_validation" id="editExtension_dueTime" step="1">
                                <div class="invalid-feedback">
                                    Please choose a valid date time combination that is both past the current assignment due date and in the future.
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success w-100" id="editExtension_saveButton" type="button">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="selectStudentForExtensionBox">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Select Student For Extension</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto">
                <span class="d-flex justify-content-center mt-2 d-block" id="spinnerDivExtensionUser">
                    <span class="spinner-border" role="status" id="spinnerObject">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </span>
                <label for="searchUsersForExtensions"></label><input type="text" id="searchUsersForExtensions" placeholder="Enter search term here..." class="w-100 form-control mb-2">
                <table id="selectUserForExtensionTable" data-search-selector="#searchUsersForExtensions" data-search="true" data-pagination="true" data-page-size="8">
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
<div class="row" id="searchAssignmentsDiv">
    <label for="searchAssignments"></label><input type="text" id="searchAssignments" placeholder="Enter search term here..." class="w-50 mt-3 float-end mb-1 ms-auto me-3">
</div>
<div id="assignmentListDiv" class="w-100">
    <div class="d-flex justify-content-center d-block mt-4" id="spinnerDiv">
        <div class="spinner-border" role="status" id="spinnerObject">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <table id="assignmentsTable" data-search="true" data-search-selector="#searchAssignments">
    </table>
</div>
<script type="text/javascript" src="/javascript/assignment_management.js" charset="utf-8"></script>
</body>
</html>