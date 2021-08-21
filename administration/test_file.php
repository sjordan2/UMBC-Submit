<style>
    #newAssignmentOverlay, #configureAssignmentOverlay {
        position: absolute;
        top: 0;
        left: 0;
        background-color: rgba(0,0,0,0.5);
        z-index: 999;
    }
    #newAssignmentBox, #configureAssignmentBox, #saveConfigureAssignmentChangesDiv {
        background-color: white;
        max-height: 83vh;
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
    .visible_button {
        background-color: #5e00ca !important;
        border-color: #5e00ca !important;
    }
    .visible_button:hover {
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
    <title>Assignment Management</title>
</head>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<body>

<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#configureAssignmentBox">
    Launch demo modal
</button>

<!-- Modal -->
<div class="modal fade" id="configureAssignmentBox" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
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
                            <a class="nav-link configure_tab" id="gradingTab">Grading</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link configure_tab" id="extensionsTab">Student Extensions</a>
                        </li>
                    </ul>
                </div>
                <span class="d-flex justify-content-center mt-4 d-none" id="spinnerDivConfigure">
                    <span class="spinner-border" role="status" id="spinnerObject">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </span>
                <div id="detailsPanel" class="d-block">
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
            </div>
        </div>
    </div>
</div>
</body>
</html>