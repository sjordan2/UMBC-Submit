let sourceInBox = false;
toolTips = {};

refreshAssignmentsTable("refresh_table");

function refreshAssignmentsTable(type, updatedAssignment, pointValue, dueDate, classType, documentLink) {
    document.getElementById("searchAssignments").value = "";
    $assignmentsTable = $('#assignmentsTable');
    if(type === "refresh_table") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "refresh_table"},
            success: function (data) {
                document.getElementById("spinnerDiv").classList.replace("d-block", "d-none");
                $assignmentsTable.bootstrapTable('destroy').bootstrapTable({
                    columns: JSON.parse(data.columns),
                    data: JSON.parse(data.table),
                    height: window.innerHeight - document.getElementById("topNavBar").offsetHeight - document.getElementById("buttonsRow").offsetHeight - document.getElementById("searchAssignmentsDiv").offsetHeight - 16,
                    onPostBody: reCompileTable
                });
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(type === "delete_assignment") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "delete_assignment", assignment: updatedAssignment},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                }
                $assignmentsTable.bootstrapTable('destroy').bootstrapTable({
                    columns: JSON.parse(data.columns),
                    data: JSON.parse(data.table),
                    height: window.innerHeight - document.getElementById("topNavBar").offsetHeight - document.getElementById("buttonsRow").offsetHeight - document.getElementById("searchAssignmentsDiv").offsetHeight - 16,
                    onPostBody: reCompileTable
                });
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(type === "newAssignment") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "newAssignment", assignment: updatedAssignment, pointValue: pointValue, dateDue: dueDate, classSelection: classType, documentLink: documentLink},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    bootstrap.Modal.getInstance(document.getElementById("newAssignmentBox")).hide();
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    $assignmentsTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        height: window.innerHeight - document.getElementById("topNavBar").offsetHeight - document.getElementById("buttonsRow").offsetHeight - document.getElementById("searchAssignmentsDiv").offsetHeight - 16,
                        onPostBody: reCompileTable
                    });
                    clearNewInputsAndValidation();
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error.toString());
            },
            dataType: 'json'
        });
    } else if(type === "configureAssignment") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "configureAssignment", assignment: updatedAssignment, pointValue: pointValue, dateDue: dueDate, classSelection: classType, documentLink: documentLink},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    $assignmentsTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        height: window.innerHeight - document.getElementById("topNavBar").offsetHeight - document.getElementById("buttonsRow").offsetHeight - document.getElementById("searchAssignmentsDiv").offsetHeight - 16,
                        onPostBody: reCompileTable
                    });
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error.toString());
            },
            dataType: 'json'
        });
    }
}

function refreshSubmissionPartsTable(type, assignment_name, part_name, point_value, extra_credit) {
    $submissionPartsTable = $('#submissionPartsTable');
    if(type === "refresh") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "load_panel", panel: "submissions", name: assignment_name},
            success: function (data) {
                $submissionPartsTable.bootstrapTable('destroy').bootstrapTable({
                    columns: JSON.parse(data.columns),
                    data: JSON.parse(data.table),
                    onPostBody: reCompileSubmissionPartsTable
                });
                document.getElementById("spinnerDivConfigure").classList.replace("d-block", "d-none");
                document.getElementById("submissionsPanel").classList.replace("d-none", "d-block");
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(type === "add") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "add_submission_part", assignment_name: assignment_name, part_name: part_name, point_value: point_value, extra_credit: extra_credit},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    bootstrap.Modal.getInstance(document.getElementById("newSubmissionPartBox")).hide();
                    $submissionPartsTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: reCompileSubmissionPartsTable
                    });
                    refreshAssignmentsTable("refresh_table");
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(type === "edit") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "edit_submission_part", assignment_name: assignment_name, part_name: part_name, point_value: point_value, extra_credit: extra_credit},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    bootstrap.Modal.getInstance(document.getElementById("editSubmissionPartBox")).hide();
                    $submissionPartsTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: reCompileSubmissionPartsTable
                    });
                    refreshAssignmentsTable("refresh_table");
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(type === "delete") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "delete_submission_part", assignment_name: assignment_name, part_name: part_name},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    $submissionPartsTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: reCompileSubmissionPartsTable
                    });
                    refreshAssignmentsTable("refresh_table");
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    }
}

function refreshExtensionsTable(type, assignment_name, student_string, new_due_date) {
    $extensionsTable = $('#extensionsTable');
    if(type === "refresh") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "load_panel", panel: "extensions", name: assignment_name},
            success: function (data) {
                $extensionsTable.bootstrapTable('destroy').bootstrapTable({
                    columns: JSON.parse(data.columns),
                    data: JSON.parse(data.table),
                    onPostBody: reCompileExtensionsTable
                });
                document.getElementById("spinnerDivConfigure").classList.replace("d-block", "d-none");
                document.getElementById("extensionsPanel").classList.replace("d-none", "d-block");
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(type === "new_extension") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "grant_new_extension", assignment_name: assignment_name, student_string: student_string, new_due_date: new_due_date},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    bootstrap.Modal.getInstance(document.getElementById("grantNewExtensionBox")).hide();
                    $extensionsTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: reCompileExtensionsTable
                    });
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(type === "edit_extension") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "edit_extension", assignment_name: assignment_name, student_string: student_string, new_due_date: new_due_date},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    bootstrap.Modal.getInstance(document.getElementById("editExtensionBox")).hide();
                    $extensionsTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: reCompileExtensionsTable
                    });
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "delete_extension", assignment_name: assignment_name, student_string: student_string},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    $extensionsTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: reCompileExtensionsTable
                    });
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    }
}

function refreshSubmissionFilesTable(assignment_name, part_name) {
    $submissionFilesTable = $('#submissionFilesTable');
    $.ajax({
        type: "POST",
        url: "assignment_management_backend.php",
        data: {action: "load_submission_files", assignment_name: assignment_name, part_name: part_name},
        success: function (data) {
            $submissionFilesTable.bootstrapTable('destroy').bootstrapTable({
                columns: JSON.parse(data.columns),
                data: JSON.parse(data.table),
                onPostBody: loadSubmissionFilesTable
            });
            document.getElementById("spinnerDivSubFiles").classList.replace("d-block", "d-none");
        },
        error: function (xhr, status, error) {
            // There was a SQL connection error
            alertify.set('notifier','position', 'top-center');
            alertify.set('notifier','delay', 0);
            alertify.error(error);
        },
        dataType: 'json'
    });
}

function refreshGradingRubricTable(assignment_name, part_name) {
    $gradingRubricTable = $('#gradingRubricTable');
    $.ajax({
        type: "POST",
        url: "assignment_management_backend.php",
        data: {action: "load_grading_rubric", assignment_name: assignment_name, part_name: part_name},
        success: function (data) {
            $gradingRubricTable.bootstrapTable('destroy').bootstrapTable({
                columns: JSON.parse(data.columns),
                data: JSON.parse(data.table),
                onPostBody: loadGradingRubricTable
            });
            document.getElementById("totalPartPointsID").innerText = data.pointTotal;
            let extra_credit_rubric_texts = document.getElementsByClassName("extra_credit_rubric_text");
            if(data.extraCredit !== 0) {
                for(let index = 0; index < extra_credit_rubric_texts.length; index++) {
                    extra_credit_rubric_texts[index].classList.replace("d-none", "d-inline");
                    document.getElementById("extraCreditRubricPointValue").innerText = data.extraCredit;
                }
            } else {
                for(let index = 0; index < extra_credit_rubric_texts.length; index++) {
                    extra_credit_rubric_texts[index].classList.replace("d-inline", "d-none");
                }
            }
            document.getElementById("spinnerDivGradingRubric").classList.replace("d-block", "d-none");
        },
        error: function (xhr, status, error) {
            // There was a SQL connection error
            alertify.set('notifier','position', 'top-center');
            alertify.set('notifier','delay', 0);
            alertify.error(error);
        },
        dataType: 'json'
    });
}

function refreshMakefileTable(action, type, assignment, part, fileData, fileName) {
    $makefileTable = $('#makefileTable');
    if(action === "load") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "load_makefile_table", type: type, assignment_name: assignment, part_name: part},
            success: function (data) {
                $makefileTable.bootstrapTable('destroy').bootstrapTable({
                    columns: JSON.parse(data.columns),
                    data: JSON.parse(data.table),
                    onPostBody: loadMakefileTable
                });
                document.getElementById("spinnerDivMakefile").classList.replace("d-block", "d-none");
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(action === "upload_makefile") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            contentType: false,
            processData: false,
            data: fileData,
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error(data.message);
                } else {
                    $makefileTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: loadMakefileTable
                    });
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    bootstrap.Modal.getInstance(document.getElementById("uploadMakefileBox")).hide();
                    document.getElementById("uploadedMakefile").value = "";
                    document.getElementById("submit_uploadedMakefile").disabled = true;
                    refreshAssignmentsTable("refresh_table");
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "delete_file", type: type, assignment_name: assignment, part_name: part, file_name: fileName},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    $makefileTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table)
                    });
                    refreshAssignmentsTable("refresh_table");
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    }
}

function refreshIOFileTable(action, type, assignment, part, fileData, fileName) {
    $IOFileTable = $('#IOFileTable');
    if(action === "load") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "load_io_file_table", type: type, assignment_name: assignment, part_name: part},
            success: function (data) {
                $IOFileTable.bootstrapTable('destroy').bootstrapTable({
                    columns: JSON.parse(data.columns),
                    data: JSON.parse(data.table),
                    onPostBody: loadIOFileTable
                });
                document.getElementById("spinnerDivIOFile").classList.replace("d-block", "d-none");
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else if(action === "upload_io_file") {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            contentType: false,
            processData: false,
            data: fileData,
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    $IOFileTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: loadIOFileTable
                    });
                    bootstrap.Modal.getInstance(document.getElementById("uploadIOFileBox")).hide();
                    document.getElementById("uploadedIOFile").value = "";
                    document.getElementById("submit_uploadedIOFile").disabled = true;
                    refreshAssignmentsTable("refresh_table");
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    } else {
        $.ajax({
            type: "POST",
            url: "assignment_management_backend.php",
            data: {action: "delete_file", type: type, assignment_name: assignment, part_name: part, file_name: fileName},
            success: function (data) {
                if(data.message[0] === "E") {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error(data.message);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(data.message);
                    $IOFileTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        onPostBody: loadIOFileTable
                    });
                    refreshAssignmentsTable("refresh_table");
                }
            },
            error: function (xhr, status, error) {
                // There was a SQL connection error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(error);
            },
            dataType: 'json'
        });
    }
}

function assignmentStatusFormatter(value) {
    if(value[0] === "E") {
        return value.slice(7);
    } else {
        return value;
    }
}

function reCompileExtensionsTable() {
    let edit_extension_buttons = document.getElementsByClassName("edit_extension_button");
    for(let index = 0; index < edit_extension_buttons.length; index++) {
        edit_extension_buttons[index].addEventListener("click", editUserExtension);
        let currTooltip = new bootstrap.Tooltip(edit_extension_buttons[index], {title: "Edit Extension", delay: { "show": 1400, "hide": 0 }});
    }

    let delete_extension_buttons = document.getElementsByClassName("delete_extension_button");
    for(let index = 0; index < delete_extension_buttons.length; index++) {
        delete_extension_buttons[index].addEventListener("click", deleteUserExtension);
        let currTooltip = new bootstrap.Tooltip(delete_extension_buttons[index], {title: "Delete Extension", delay: { "show": 1400, "hide": 0 }});
    }
}

function editUserExtension() {
    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
    let row = this.closest("tr");
    document.getElementById("userForExtensionEdit").innerText = row.cells[0].innerText;

    let tooltip =  bootstrap.Tooltip.getInstance(this);
    tooltip.hide();
    tooltip.disable();

    let ajaxQuery = new XMLHttpRequest();
    ajaxQuery.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let ajaxResponse = null;
            try {
                ajaxResponse = JSON.parse(this.responseText);
            } catch (error) { // The backend returned a single error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(this.responseText);
            }
            if(ajaxResponse !== null) { // 0 - Current Course-Wide Due Date/Time, 1 - Current Extended Due Date, 2 - Current Extended Due Time
                document.getElementById("courseWideDueDate_editExtension").innerText = ajaxResponse[0];
                document.getElementById("editExtension_dueDate").value = ajaxResponse[1];
                document.getElementById("editExtension_dueTime").value = ajaxResponse[2];

                bootstrap.Modal.getInstance(document.getElementById("editExtensionBox")).show();
            }
        }
    };
    ajaxQuery.open("POST", "assignment_management_backend.php", true);
    ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxQuery.send("action=grab_edit_extension_data" + "&assignment_name=" + assignmentName + "&student_string=" + document.getElementById("userForExtensionEdit").innerText);
}

function deleteUserExtension() {
    let rows = this.closest("tr");
    let studentString = rows.cells[0].innerText;
    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
    alertify.confirm("Are you sure you want to remove the " + assignmentName + " extension for " + studentString + "?", function (selection) {
        refreshExtensionsTable("delete_extension", assignmentName, studentString);
    });
}

document.getElementById("editExtension_dueDate").addEventListener("change", editExtensionDateListener);
document.getElementById("editExtension_dueTime").addEventListener("change", editExtensionDateListener);

function editExtensionDateListener() {
    let newDueDateElem = document.getElementById("editExtension_dueDate");
    let newDueTimeElem = document.getElementById("editExtension_dueTime");
    let currentDate = new Date();
    let proposedNewDueDate = new Date(newDueDateElem.value + " " + newDueTimeElem.value);
    let currentAssignmentDueDate = getDateObjectFromString(document.getElementById("courseWideDueDate_editExtension").innerText);
    if(proposedNewDueDate <= currentDate || proposedNewDueDate <= currentAssignmentDueDate) {
        newDueDateElem.classList.remove("is-valid");
        newDueTimeElem.classList.remove("is-valid");
        newDueDateElem.classList.add("is-invalid");
        newDueTimeElem.classList.add("is-invalid");
    } else {
        newDueDateElem.classList.remove("is-invalid");
        newDueTimeElem.classList.remove("is-invalid");
        newDueDateElem.classList.add("is-valid");
        newDueTimeElem.classList.add("is-valid");
    }
}

document.getElementById("editExtension_saveButton").addEventListener("click", function(event) {
    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
    let studentInputEl = document.getElementById("userForExtensionEdit");
    let newDueDateElem = document.getElementById("editExtension_dueDate");
    let newDueTimeElem = document.getElementById("editExtension_dueTime");
    let currentDate = new Date();
    let proposedNewDueDate = new Date(newDueDateElem.value + " " + newDueTimeElem.value);
    let currentAssignmentDueDate = getDateObjectFromString(document.getElementById("courseWideDueDate_editExtension").innerText);
    if(proposedNewDueDate <= currentDate || proposedNewDueDate <= currentAssignmentDueDate) {
        newDueDateElem.classList.add("is-invalid");
        newDueTimeElem.classList.add("is-invalid");
    } else {
        refreshExtensionsTable("edit_extension", assignmentName, studentInputEl.innerText, newDueDateElem.value + " " + newDueTimeElem.value);
    }
});

document.getElementById("editExtensionBox").addEventListener("hidden.bs.modal", function(event) {
    let edit_extension_buttons = document.getElementsByClassName("edit_extension_button");
    for(let index = 0; index < edit_extension_buttons.length; index++) {
        bootstrap.Tooltip.getInstance(edit_extension_buttons[index]).dispose();
        let currTooltip = new bootstrap.Tooltip(edit_extension_buttons[index], {title: "Edit Extension", delay: { "show": 1400, "hide": 0 }});
    }
});

function refreshUsersTableForExtensions() {
    $usersTableForExtensions = $('#selectUserForExtensionTable');
    $.ajax({
        type: "POST",
        url: "assignment_management_backend.php",
        data: {action: "get_users_table"},
        success: function (data) {
            document.getElementById("spinnerDivExtensionUser").classList.replace("d-block", "d-none");
            $usersTableForExtensions.bootstrapTable('destroy').bootstrapTable({
                columns: JSON.parse(data.columns),
                data: JSON.parse(data.table),
                onPostBody: loadUsersTableForExtensions
            });
        },
        error: function (xhr, status, error) {
            // There was a SQL connection error
            alertify.set('notifier', 'position', 'top-center');
            alertify.set('notifier', 'delay', 0);
            alertify.error(xhr.responseText + " " + error);
        },
        dataType: 'json'
    });
}

function loadIOFileTable() {
    let baseType = null;
    let type = null;
    if(document.getElementById("sampleOrTestingIOFile").innerText === "Sample") {
        baseType = "SAMPLE";
    } else {
        baseType = "TESTING";
    }

    let action_cells = document.getElementById("IOFileTable").querySelectorAll("td:last-child");
    if(action_cells[0].childNodes[0].hasChildNodes() === true) {
        for (let index = 0; index < action_cells.length; index++) {
            let prev_button = action_cells[index].children[0].children[0].children[0].children[0];
            prev_button.addEventListener("click", previewFile(baseType, true, index));
        }

        for (let index = 0; index < action_cells.length; index++) {
            let del_button = action_cells[index].children[0].children[0].children[1].children[0];
            del_button.addEventListener("click", deleteFile(baseType, true, index));
        }
    }
}

function loadUserTableForExtensionsActions() {
    return "<button type='button' class='btn btn-success w-100 selectStudentExtension'>Select This Student</button>";
}

function loadMakefileTable() {
    let type = null;
    if(document.getElementById("sampleOrTestingMakefile").innerText === "Sample") {
        type = "SAMPLE_MAKEFILE";
    } else {
        type = "TESTING_MAKEFILE";
    }

    if(document.getElementById("makefileTable").rows.length > 0) {
        let action_cells = document.getElementById("makefileTable").querySelectorAll("td:last-child");
        if(action_cells[0].childNodes[0].hasChildNodes() === true) {
            for (let index = 0; index < action_cells.length; index++) {
                let prev_button = action_cells[index].children[0].children[0].children[0].children[0];
                prev_button.addEventListener("click", previewFile(type));
            }

            for (let index = 0; index < action_cells.length; index++) {
                let del_button = action_cells[index].children[0].children[0].children[1].children[0];
                del_button.addEventListener("click", deleteFile(type));
            }
        }
    }
}

document.getElementById("selectStudentForExtension").addEventListener("click", function (event) {
    bootstrap.Modal.getInstance(document.getElementById("selectStudentForExtensionBox")).show();
    refreshUsersTableForExtensions();
});

function loadUsersTableForExtensions() {
    let select_extension_buttons = document.getElementsByClassName("selectStudentExtension");
    for(let index = 0; index < select_extension_buttons.length; index++) {
        select_extension_buttons[index].addEventListener("click", function (event) {
            let table_row = this.closest("tr");
            let first_name = table_row.cells[2].innerText;
            let last_name = table_row.cells[1].innerText;
            let name_id = table_row.cells[3].innerText;
            let student_string = first_name + " " + last_name + " (" + name_id + ")";
            document.getElementById("grantNewExtension_student").value = student_string;
            bootstrap.Modal.getInstance(document.getElementById("selectStudentForExtensionBox")).hide();
        });
    }
}

function previewFile(type, is_io_file, index) {
    return function () {
        if(is_io_file) {
            let rows = document.getElementById("IOFileTable").rows;
            if(type.split("_").length !== 2) {
                type = type + "_" + rows[index + 1].cells[0].innerText;
            }
        }

        let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
        let partName = document.getElementById("selectSubPart").value;
        let fileName = null;
        if(is_io_file) {
            fileName = this.closest("tr").cells[1].innerText;
        } else {
            fileName = this.closest("tr").cells[0].innerText;
        }
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let ajaxResponse = null;
                try {
                    ajaxResponse = JSON.parse(this.responseText);
                } catch (error) { // The backend returned a single error
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error(this.responseText);
                }
                if(ajaxResponse !== null) {
                    document.getElementById("fileContents").innerText = ajaxResponse[0];
                    bootstrap.Modal.getInstance(document.getElementById("previewFileBox")).show();
                }
            }
        };
        ajaxQuery.open("POST", "assignment_management_backend.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("action=preview_file" + "&name=" + assignmentName + "&part=" + partName + "&type=" + type + "&fileName=" + fileName);
    }
}

function deleteFile(type, is_io_file, index) {
    return function() {
        if(is_io_file) {
            let rows = document.getElementById("IOFileTable").rows;
            if(type.split("_").length !== 2) {
                type = type + "_" + rows[index + 1].cells[0].innerText;
            }
        }

        let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
        let partName = document.getElementById("selectSubPart").value;
        if(is_io_file) {
            fileName = this.closest("tr").cells[1].innerText;
        } else {
            fileName = this.closest("tr").cells[0].innerText;
        }

        let confirmationMessage = null;
        if(type.split("_")[1] === "MAKEFILE") {
            confirmationMessage = "Are you sure you want to remove this Makefile from " + partName + "?";
        } else {
            confirmationMessage = "Are you sure you want to remove '" + fileName + "' from " + partName + "?";
        }
        alertify.confirm(confirmationMessage, function (selection) {
            if(type.split("_")[1] === "MAKEFILE") {
                refreshMakefileTable("delete", type, assignmentName, partName, null, fileName);
            } else {
                refreshIOFileTable("delete", type, assignmentName, partName, null, fileName);
            }
        });
    }
}

function loadGradingRubricLineType(value) {
    let select_html = "";
    if(value === "0") {
        return "<select class='form-select rubricLineTypeInput'><option value='0' selected>Graded Item</option><option value='1'>TA Note</option><option value='2'>Student Note</option></select>";
    } else if(value === "1") {
        return "<select class='form-select rubricLineTypeInput'><option value='0'>Graded Item</option><option value='1' selected>TA Note</option><option value='2'>Student Note</option></select>";
    } else {
        return "<select class='form-select rubricLineTypeInput'><option value='0'>Graded Item</option><option value='1'>TA Note</option><option value='2' selected>Student Note</option></select>";
    }
}

function loadGradingRubricLineItem(value) {
    return "<div><input type='text' class='form-control rubricLineItemInput' value='" + value + "'><div class='invalid-feedback'>Line Item cannot be empty.</div></div>";
}

function loadGradingRubricLineValue(value) {
    if(value === null) {
        return "<div><input type='number' class='form-control rubricLineValueInput' disabled><div class='invalid-feedback'>Line Value must be filled in for a graded item!</div></div>";
    } else {
        return "<input type='number' class='form-control rubricLineValueInput' value='" + value + "'>";
    }
}

function loadGradingRubricActions(value, row, index) {
    if(index === 0) {
        return "<div class='input-group flex-nowrap'>" +
            "<button class='btn btn-lg btn-primary w-50 addRubricRowButton'><i class='bi bi-plus-lg'></i></button>" +
            "<button class='btn btn-lg btn-danger w-50 deleteRubricRowButton' disabled><i class='bi bi-x-lg'></i></button>"
            "</div>";
    } else {
        return "<div class='input-group flex-nowrap'>" +
            "<button class='btn btn-lg btn-primary w-50 addRubricRowButton'><i class='bi bi-plus-lg'></i></button>" +
            "<button class='btn btn-lg btn-danger w-50 deleteRubricRowButton'><i class='bi bi-x-lg'></i></button>"
            "</div>";
    }
}
document.getElementById("gradingRubric_saveButton").addEventListener("click", function (event) {
    let lineTypes = document.getElementsByClassName("rubricLineTypeInput");
    let lineItems = document.getElementsByClassName("rubricLineItemInput");
    let lineValues = document.getElementsByClassName("rubricLineValueInput");
    let tableLength = document.getElementById("gradingRubricTable").rows.length - 1;
    let alertSent = false;
    let goodToGo = true;
    let rubricJSON = {};
    for(let index = 0; index < tableLength; index++) {
        lineItems[index].removeEventListener("input", rubricLineListener);
        if(lineItems[index].value === "") {
            lineItems[index].classList.add("is-invalid");
            lineItems[index].addEventListener("input", rubricLineListener);
            if(alertSent === false) {
                let rowIndex = lineItems[index].closest("tr").rowIndex;
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error("ERROR: The line item on row " + rowIndex + " is empty!");
                alertSent = true;
            }
            goodToGo = false;
        }
        lineValues[index].removeEventListener("input", rubricLineListener);
        if(lineTypes[index].value === "0" && lineValues[index].value === "") {
            lineValues[index].classList.add("is-invalid");
            lineValues[index].addEventListener("input", rubricLineListener);
            if(alertSent === false) {
                let rowIndex = lineValues[index].closest("tr").rowIndex;
                alertify.set('notifier','position', 'top-center');
                alertify.set('notifier','delay', 0);
                alertify.error("ERROR: The point value on row " + rowIndex + " must be filled in, as it is for a graded item!");
                alertSent = true;
            }
            goodToGo = false;
        }
    }

    if(goodToGo === true) {
        for(let index = 0; index < tableLength; index++) {
            currJSON = {type: lineTypes[index].value, item: lineItems[index].value, value: lineValues[index].value};
            for(let key of Object.keys(rubricJSON)) {
                line_json = rubricJSON[key];
                if(currJSON.type === line_json.type && currJSON.item === line_json.item && currJSON.value === line_json.value) {
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error("ERROR: The " + lineTypes[index].options[lineTypes[index].selectedIndex].text + ": '" + line_json.item + "' (worth " + line_json.value + " points) is listed more than once in this rubric!");
                    goodToGo = false;
                    break;
                }
            }
            if(goodToGo === true) {
                rubricJSON[index] = currJSON;
            }
        }
    }
    if(goodToGo === true) {
        let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
        let partName = document.getElementById("gradingRubricTitle").innerText;

        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                if(this.responseText[0] === "E") {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error(this.responseText);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(this.responseText);
                }
            }
        };
        ajaxQuery.open("POST", "assignment_management_backend.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("action=update_grading_rubric" + "&name=" + assignmentName + "&part=" + partName + "&newRubric=" + JSON.stringify(rubricJSON));
    }
});

function loadSubmissionFilesTable() {
    let remove_file_buttons = document.getElementsByClassName("removeSubFile_button");
    for(let index = 0; index < remove_file_buttons.length; index++) {
        remove_file_buttons[index].addEventListener("click", deleteRow);
    }
}

function loadGradingRubricTable() {
    let select_dropdowns = document.getElementsByClassName("rubricLineTypeInput");
    for(let index = 0; index < select_dropdowns.length; index++) {
        select_dropdowns[index].addEventListener("change", checkLineTypeForNull);
    }
    let add_row_buttons = document.getElementsByClassName("addRubricRowButton");
    for(let index = 0; index < add_row_buttons.length; index++) {
        add_row_buttons[index].addEventListener("click", addRubricRow);
        let currTooltip = new bootstrap.Tooltip(add_row_buttons[index], {title: "Add New Row", delay: { "show": 1400, "hide": 0 }});
    }
    let delete_row_buttons = document.getElementsByClassName("deleteRubricRowButton");
    for(let index = 1; index < delete_row_buttons.length; index++) {
        delete_row_buttons[index].addEventListener("click", deleteRubricRow);
        let currTooltip = new bootstrap.Tooltip(delete_row_buttons[index], {title: "Delete Row", delay: { "show": 1400, "hide": 0 }});
    }
}

function checkLineTypeForNull() {
    let valueInput = this.closest("tr").cells[2].childNodes[0];
    valueInput.removeEventListener("change", rubricLineListener);
    if(this.value !== "0") {
        valueInput.value = "";
        valueInput.disabled = true;
    } else {
        valueInput.value = 0;
        valueInput.disabled = false;
    }
}

function addRubricRow() {
    let currIndex = this.closest("tr").rowIndex;
    let newRow = document.getElementById("gradingRubricTable").insertRow(currIndex + 1);
    let selectColumn = newRow.insertCell(0);
    selectColumn.innerHTML = loadGradingRubricLineType("0");
    selectColumn.childNodes[0].addEventListener("change", checkLineTypeForNull);
    let textColumn = newRow.insertCell(1);
    textColumn.innerHTML = loadGradingRubricLineItem("");
    let pointColumn = newRow.insertCell(2);
    pointColumn.innerHTML = loadGradingRubricLineValue(0);
    let actionsColumn = newRow.insertCell(3);
    let inputGroup = document.createElement("div");
    inputGroup.classList.add("input-group");
    let addButton = document.createElement("button");
    addButton.classList.add("btn", "btn-lg", "btn-primary", "w-50", "addRubricRowButton");
    addButton.innerHTML = "<i class='bi bi-plus-lg'></i>";
    let deleteButton = document.createElement("button");
    deleteButton.classList.add("btn", "btn-lg", "btn-danger", "w-50", "deleteRubricRowButton");
    deleteButton.innerHTML = "<i class='bi bi-x-lg'></i>";
    inputGroup.appendChild(addButton);
    inputGroup.appendChild(deleteButton);
    actionsColumn.appendChild(inputGroup);
    addButton.addEventListener("click", addRubricRow);
    let addTooltip = new bootstrap.Tooltip(addButton, {title: "Add New Row", delay: { "show": 1400, "hide": 0 }});
    deleteButton.addEventListener("click", deleteRubricRow);
    let deleteTooltip = new bootstrap.Tooltip(deleteButton, {title: "Delete Row", delay: { "show": 1400, "hide": 0 }});
}

function rubricLineListener() {
    if(this.value === "") {
        this.classList.add("is-invalid");
    } else {
        this.classList.remove("is-invalid");
    }
}

function deleteRubricRow() {
    document.getElementById("gradingRubricTable").deleteRow(this.closest("tr").rowIndex);
}

function loadSubmissionFileTextBoxes(value) {
    return "<input type='text' value='" + value + "' class='form-control subFile_textBox'>";
}

function loadSubmissionFileActions(value, row, index) {
    if(index === 0) {
        return "<button class='btn btn-danger w-100' disabled>Remove File</button>"
    } else {
        return "<button class='btn btn-danger removeSubFile_button w-100'>Remove File</button>"
    }
}

function reCompileTable() {
    visibleListener();
    configureListener();
    deleteListener();

    tooltipTriggerListTable = [].slice.call(document.querySelectorAll('.table_button'))
    tooltipListTable = tooltipTriggerListTable.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl,
            {
                delay: { "show": 1400, "hide": 0 }
            })
    });
}

function reCompileSubmissionPartsTable() {
    subPartFileListener();
    subPartEditListener();
    subPartDeleteListener();

    tooltipTriggerListTable = [].slice.call(document.querySelectorAll('.sub_part_button'))
    tooltipListTableSubParts = tooltipTriggerListTable.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl,
            {
                delay: { "show": 1400, "hide": 0 }
            })
    });
}

function subPartFileListener() {
    let subPart_file_buttons = document.getElementsByClassName("sub_part_files_button");
    for(let index = 0; index < subPart_file_buttons.length; index++) {
        subPart_file_buttons[index].addEventListener("click", subPartFilesPrep);
    }
}

function subPartEditListener() {
    let subPart_edit_buttons = document.getElementsByClassName("edit_sub_part_button");
    for(let index = 0; index < subPart_edit_buttons.length; index++) {
        subPart_edit_buttons[index].addEventListener("click", editSubPartPrep);
    }
}

function subPartDeleteListener() {
    let delete_buttons = document.getElementsByClassName("delete_sub_part_button");
    for(let index = 0; index < delete_buttons.length; index++) {
        delete_buttons[index].addEventListener("click", deleteSubPartPrep);
    }
}

function subPartFilesPrep() {
    let row_element = this.closest("tr").getElementsByTagName("td");
    document.getElementById("submissionFilesTitle").innerText = row_element[0].innerText;

    for (let index = 0; index < tooltipListTableSubParts.length; index++) {
        tooltipListTableSubParts[index].hide();
        tooltipListTableSubParts[index].disable();
    }

    refreshSubmissionFilesTable(document.getElementById("configureAssignmentIDTitle").innerText, row_element[0].innerText);

    bootstrap.Modal.getInstance(document.getElementById("submissionFilesBox")).show();
}

function editSubPartPrep() {
    let row_element = this.closest("tr").getElementsByTagName("td");
    document.getElementById("editSubmissionPartIDTitle").innerText = row_element[0].innerText;
    document.getElementById("editSubmissionPart_pointValue").value = row_element[1].innerText;
    document.getElementById("editSubmissionPart_extraCredit").value = row_element[2].innerText;

    document.getElementById("editSubmissionPart_pointValue").classList.add("is-valid");
    document.getElementById("editSubmissionPart_extraCredit").classList.add("is-valid");

    for (let index = 0; index < tooltipListTableSubParts.length; index++) {
        tooltipListTableSubParts[index].hide();
        tooltipListTableSubParts[index].disable();
    }

    bootstrap.Modal.getInstance(document.getElementById("editSubmissionPartBox")).show();
}

function deleteSubPartPrep() {
    let row_element = this.closest("tr").getElementsByTagName("td");
    let assignment_name = document.getElementById("configureAssignmentIDTitle").innerText;
    let part_name = row_element[0].innerText;

    alertify.confirm('Are you sure you want to remove ' + part_name + " from " + assignment_name + "?", function (selection) {
        refreshSubmissionPartsTable("delete", assignment_name, part_name);
    });
}

function visibleListener() {
    let visible_buttons = document.getElementsByClassName("visible_button");
    for(let index = 0; index < visible_buttons.length; index++) {
        visible_buttons[index].addEventListener("click", toggleAssignmentVisibility);
    }
}

function toggleAssignmentVisibility() {
    bootstrap.Tooltip.getInstance(this).hide();
    let row_element = this.closest("tr").getElementsByTagName("td");
    let assignment_name = row_element[0].innerText;
    let ajaxQuery = new XMLHttpRequest();
    ajaxQuery.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            if(this.responseText[0] === "E") {
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(this.responseText);
            } else {
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 5);
                alertify.success(this.responseText);
                refreshAssignmentsTable("refresh_table");
            }
        }
    };
    ajaxQuery.open("POST", "assignment_management_backend.php", true);
    ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxQuery.send("action=toggle_visibility" + "&name=" + assignment_name);
}

function configureListener() {
    let configure_buttons = document.getElementsByClassName("configure_table_button");
    for(let index = 0; index < configure_buttons.length; index++) {
        configure_buttons[index].addEventListener("click", configureAssignmentPrep);
    }
}

function configureAssignmentPrep() {
    let row_element = this.closest("tr").getElementsByTagName("td");
    document.getElementById("configureAssignmentIDTitle").innerText = row_element[0].innerText;

    for (let index = 0; index < tooltipListTable.length; index++) {
        tooltipListTable[index].hide();
        tooltipListTable[index].disable();
    }
    bootstrap.Modal.getInstance(document.getElementById("configureAssignmentBox")).show();

    resetEditValidation();
    resetTabActiveStates(document.getElementById("detailsTab"));
    loadDetailsPanel();
}

function deleteListener() {
    let delete_buttons = document.getElementsByClassName("delete_button");
    for(let index = 0; index < delete_buttons.length; index++) {
        delete_buttons[index].addEventListener("click", deleteAssignmentPrep);
    }
}

function deleteAssignmentPrep() {
    let row_element = this.closest("tr").getElementsByTagName("td");
    let assignment_name = row_element[0].innerText;

    alertify.confirm('Are you sure you want to remove ' + assignment_name + " from the database?", function (selection) {
        refreshAssignmentsTable("delete_assignment", assignment_name);
    });
}

function loadActions(value) {
    let action_html = "<div class='container'>";
    action_html += "<div class='row flex-nowrap'>";
    action_html += "<div class='col'>";
    if(value === "hidden") {
        action_html += "<button class='btn btn-success btn-lg w-100 table_button visible_button' data-bs-toggle='tooltip' title='Make Assignment Visible to Students'><i class='bi bi-eye-fill'></i></button>";
    } else {
        action_html += "<button class='btn btn-success btn-lg w-100 table_button visible_button' data-bs-toggle='tooltip' title='Hide Assignment From Students'><i class='bi bi-eye-slash-fill'></i></button>";
    }
    action_html += "</div>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-success btn-lg w-100 table_button configure_table_button' data-bs-toggle='tooltip' title='Configure Assignment'><i class='bi bi-tools'></i></button>";
    action_html += "</div>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-danger btn-lg w-100 table_button delete_button' data-bs-toggle='tooltip' title='Delete Assignment'><i class='bi bi-trash'></i></button>";
    action_html += "</div>";
    action_html += "</div>";
    action_html += "</div>";
    return action_html;
}

function loadSubmissionPartActions(value) {
    let action_html = "<div class='container'>";
    action_html += "<div class='row flex-nowrap'>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-success btn-lg w-100 sub_part_button sub_part_files_button visible_button' data-bs-toggle='tooltip' title='Show Submission Files'><i class='bi bi-file-earmark'></i></button>";
    action_html += "</div>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-success btn-lg w-100 sub_part_button edit_sub_part_button' data-bs-toggle='tooltip' title='Edit Part'><i class='bi bi-pencil-square'></i></button>";
    action_html += "</div>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-danger btn-lg w-100 sub_part_button delete_sub_part_button' data-bs-toggle='tooltip' title='Delete Part'><i class='bi bi-trash'></i></button>";
    action_html += "</div>";
    action_html += "</div>";
    action_html += "</div>";
    return action_html;
}

document.getElementById("followLinkButton").addEventListener("click", function() {
   let target_link = document.getElementById("currDocLinkInput").value;
    window.open(target_link, '_blank').focus();
});

function dateSorter(firstDate, secondDate) {
    dateOne = getDateObjectFromString(firstDate);
    dateTwo = getDateObjectFromString(secondDate);

    if(dateOne < dateTwo) {
        return -1;
    } else if(dateOne > dateTwo) {
        return 1;
    } else {
        return 0;
    }
}

function loadDetailsPanel() {
    resetPanelSelection();

    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;

    if(document.getElementById("spinnerDivConfigure").classList.contains("d-none")) {
        document.getElementById("spinnerDivConfigure").classList.replace("d-none", "d-block");
    }
    let ajaxQuery = new XMLHttpRequest();
    ajaxQuery.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let ajaxResponse = null;
            try {
                ajaxResponse = JSON.parse(this.responseText);
            } catch (error) { // The backend returned a single error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(this.responseText);
            }
            if(ajaxResponse !== null) {
                // Order of JSON Response is: 1 - Point Value, 2 - Due Date, 3 - Due Time, 4 - Class Type, 5 - Document Link
                document.getElementById("configureAssignment_pointValue").value = ajaxResponse[0];
                document.getElementById("configureAssignment_dueDate").value = ajaxResponse[1];
                document.getElementById("configureAssignment_dueTime").value = ajaxResponse[2];
                let classType = ajaxResponse[3];
                if(classType === "Both") {
                    document.getElementById("configureAssignment_classSelect_Both").checked = true;
                } else if(classType === "Majors") {
                    document.getElementById("configureAssignment_classSelect_Majors").checked = true;
                } else {
                    document.getElementById("configureAssignment_classSelect_NonMajors").checked = true;
                }
                document.getElementById("currDocLinkInput").value = ajaxResponse[4];

                if(document.getElementById("currDocLinkInput").value === "") {
                    document.getElementById("followLinkButton").disabled = true;
                } else {
                    document.getElementById("followLinkButton").disabled = false;
                }

                document.getElementById("spinnerDivConfigure").classList.replace("d-block", "d-none");
                document.getElementById("detailsPanel").classList.replace("d-none", "d-block");
            }
        }
    };
    ajaxQuery.open("POST", "assignment_management_backend.php", true);
    ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxQuery.send("action=load_panel" + "&name=" + assignmentName + "&panel=details");
}

function loadSubmissionsPanel() {
    if(document.getElementById("spinnerDivConfigure").classList.contains("d-none")) {
        document.getElementById("spinnerDivConfigure").classList.replace("d-none", "d-block");
    }

    resetPanelSelection();

    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;

    refreshSubmissionPartsTable("refresh", assignmentName);
}

function loadExtensionsPanel() {
    if(document.getElementById("spinnerDivConfigure").classList.contains("d-none")) {
        document.getElementById("spinnerDivConfigure").classList.replace("d-none", "d-block");
    }

    resetPanelSelection();

    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;

    refreshExtensionsTable("refresh", assignmentName);
}

function loadGradingPanel() {
    if(document.getElementById("spinnerDivConfigure").classList.contains("d-none")) {
        document.getElementById("spinnerDivConfigure").classList.replace("d-none", "d-block");
    }

    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;

    resetPanelSelection();

    let ajaxQuery = new XMLHttpRequest();
    ajaxQuery.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let ajaxResponse = null;
            try {
                ajaxResponse = JSON.parse(this.responseText);
            } catch (error) { // The backend returned a single error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(this.responseText);
            }
            if(ajaxResponse !== null) {
                let selectSubPart = document.getElementById("selectSubPart");

                for(let index = 1; index < selectSubPart.options.length; index++) { // Offset by 1 to exclude the disabled/default select value
                    selectSubPart.remove(index);
                }

                selectSubPart.selectedIndex = 0;

                resetDisabledState(true).call();

                for(let index = 0; index < ajaxResponse.length; index++) {
                    selectSubPart.options.add(new Option(ajaxResponse[index], ajaxResponse[index]));
                }

                document.getElementById("spinnerDivConfigure").classList.replace("d-block", "d-none");
                document.getElementById("gradingPanel").classList.replace("d-none", "d-block");
            }
        }
    };
    ajaxQuery.open("POST", "assignment_management_backend.php", true);
    ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxQuery.send("action=load_panel" + "&name=" + assignmentName + "&panel=grading");
}

function loadExtensionsTableActions(value) {
    let action_html = "<div class='container'>";
    action_html += "<div class='row flex-nowrap'>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-success btn-lg w-100 edit_extension_button'><i class='bi bi-pencil-square'></i></button>";
    action_html += "</div>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-danger btn-lg w-100 delete_extension_button'><i class='bi bi-trash'></i></button>";
    action_html += "</div>";
    action_html += "</div>";
    action_html += "</div>";
    return action_html;
}

function getDateObjectFromString(fancy_string) {
    stringList = fancy_string.split(",");
    stringList[1] = stringList[1].slice(0, -2);
    newString = stringList.join();
    return new Date(newString);
}

document.getElementById("detailsTab").addEventListener("click", loadDetailsPanel);
document.getElementById("submissionsTab").addEventListener("click", loadSubmissionsPanel);
document.getElementById("gradingTab").addEventListener("click", loadGradingPanel);
document.getElementById("extensionsTab").addEventListener("click", loadExtensionsPanel);

function resetPanelSelection() {
    document.getElementById("detailsPanel").classList.replace("d-block", "d-none");
    document.getElementById("submissionsPanel").classList.replace("d-block", "d-none");
    document.getElementById("gradingPanel").classList.replace("d-block", "d-none");
    document.getElementById("extensionsPanel").classList.replace("d-block", "d-none");
}

function clearNewInputsAndValidation() {
    let validation_inputs = document.querySelectorAll(".new_validation");
    for(let index = 0; index < validation_inputs.length; index++) {
        validation_inputs[index].classList.remove("is-valid");
        validation_inputs[index].classList.remove("is-invalid");
    }
    document.getElementById("newAssignment_name").value = "";
    document.getElementById("newAssignment_pointValue").value = "";
    document.getElementById("newAssignment_dueDate").value = "";
    document.getElementById("newAssignment_dueTime").value = "";
}

function clearSubmissionPartInputsAndValidation() {
    let sub_part_inputs = document.querySelectorAll(".subPart_validation");
    for(let index = 0; index < sub_part_inputs.length; index++) {
        sub_part_inputs[index].classList.remove("is-valid");
        sub_part_inputs[index].classList.remove("is-invalid");
    }
    document.getElementById("newSubmissionPart_name").value = "";
    document.getElementById("newSubmissionPart_pointValue").value = "";
    document.getElementById("newSubmissionPart_extraCredit").value = "0";
}

function clearEditPartInputsAndValidation() {
    let edit_sub_part_inputs = document.querySelectorAll(".editSubPart_validation");
    for(let index = 0; index < edit_sub_part_inputs.length; index++) {
        edit_sub_part_inputs[index].classList.remove("is-valid");
        edit_sub_part_inputs[index].classList.remove("is-invalid");
    }
    document.getElementById("editSubmissionPart_pointValue").value = "";
    document.getElementById("editSubmissionPart_extraCredit").value = "";
}

function resetEditValidation() {
    let validation_inputs = document.querySelectorAll(".edit_validation");
    for(let index = 0; index < validation_inputs.length; index++) {
        validation_inputs[index].classList.remove("is-invalid");
        validation_inputs[index].classList.add("is-valid");
    }
}

function addAssignmentOverlayEventListeners(type) {
    if(type === "newAssignment") {
        document.getElementById(type + "_name").addEventListener("input", function() {
            if(document.getElementById(type + "_name").value === "") {
                document.getElementById(type + "_name").classList.remove("is-valid");
                document.getElementById(type + "_name").classList.add("is-invalid");
            } else {
                document.getElementById(type + "_name").classList.remove("is-invalid");
                document.getElementById(type + "_name").classList.add("is-valid");
            }
        });
    } else {
        document.getElementById("currDocLinkInput").addEventListener("input", function () {
            if(this.value === "") {
                document.getElementById("followLinkButton").disabled = true;
            } else {
                document.getElementById("followLinkButton").disabled = false;
            }
        });
    }

    document.getElementById(type + "_pointValue").addEventListener("input", function() {
        if (document.getElementById(type + "_pointValue").value === "" || !Number.isInteger(Number(document.getElementById(type + "_pointValue").value))) {
            document.getElementById(type + "_pointValue").classList.remove("is-valid");
            document.getElementById(type + "_pointValue").classList.add("is-invalid");
        } else {
            document.getElementById(type + "_pointValue").classList.remove("is-invalid");
            document.getElementById(type + "_pointValue").classList.add("is-valid");
        }
    });

    document.getElementById(type + "_dueDate").addEventListener("change", dueDateTimeListener(type, "_dueDate", "_dueTime"));
    document.getElementById(type + "_dueTime").addEventListener("change", dueDateTimeListener(type, "_dueTime", "_dueDate"));

    document.getElementById(type + "_finalButton").addEventListener("click", function() {
        let goodToGo = true;
        if(type === "newAssignment") {
            if (document.getElementById(type + "_name").value === "" || document.getElementById(type + "_name").classList.contains("is-invalid")) {
                goodToGo = false;
                document.getElementById(type + "_name").classList.add("is-invalid");
            }
        }
        if (document.getElementById(type + "_pointValue").value === "" || document.getElementById(type + "_pointValue").classList.contains("is-invalid")) {
            goodToGo = false;
            document.getElementById(type + "_pointValue").classList.add("is-invalid");
        }
        if(document.getElementById(type + "_dueDate").value === "" || document.getElementById(type + "_dueTime").value === "" ||
           document.getElementById(type + "_dueDate").classList.contains("is-invalid") || document.getElementById(type + "_dueTime").classList.contains("is-invalid")) {
            goodToGo = false;
            document.getElementById(type + "_dueDate").classList.add("is-invalid");
            document.getElementById(type + "_dueTime").classList.add("is-invalid");
        }

        // All of the verification has been completed. Yippee!
        if(goodToGo === true) {
            let assignmentName = null;
            if(type === "newAssignment") {
                assignmentName = document.getElementById(type + "_name").value;
            } else {
                assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
            }
            let pointValue = parseInt(document.getElementById(type + "_pointValue").value);
            let dateTimeCompound = document.getElementById(type + "_dueDate").value + " " + document.getElementById(type + "_dueTime").value;
            let classSelection = null;
            if(document.getElementById(type + "_classSelect_Majors").checked === true) {
                classSelection = "Majors";
            } else if(document.getElementById(type + "_classSelect_NonMajors").checked === true) {
                classSelection = "Non-Majors";
            } else {
                classSelection = "Both";
            }
            let documentLink = document.getElementById("currDocLinkInput").value;
            if(documentLink === "") {
                documentLink = "NULL";
            }

            refreshAssignmentsTable(type, assignmentName, pointValue, dateTimeCompound, classSelection, documentLink);
        }
    });
}

function dueDateTimeListener(type, origin, target) {
    return function() {
        // If the element itself is empty after a change, alert them
        if(document.getElementById(type + origin).value === "") {
            document.getElementById(type + origin).classList.remove("is-valid");
            document.getElementById(type + origin).classList.add("is-invalid");
        } else {
            // Otherwise, if the element itself is full but the other date element is empty, remove valid alerts from the target and all alerts from the origin
            if (document.getElementById(type + origin).value !== "" && document.getElementById(type + target).value === "") {
                document.getElementById(type + origin).classList.remove("is-valid");
                document.getElementById(type + origin).classList.remove("is-invalid");
                document.getElementById(type + target).classList.remove("is-valid");
            } else {

                // Otherwise, they are both full, so now we go into the actual date checks
                let chosenDueDate = new Date(document.getElementById(type + "_dueDate").value + " " + document.getElementById(type + "_dueTime").value);
                let currentDate = new Date();
                if (isNaN(chosenDueDate.getTime())) {
                    document.getElementById(type + "_dueDate").classList.remove("is-valid");
                    document.getElementById(type + "_dueDate").classList.add("is-invalid");
                    document.getElementById(type + "_dueTime").classList.remove("is-valid");
                    document.getElementById(type + "_dueTime").classList.add("is-invalid");
                } else {
                    if (chosenDueDate <= currentDate) { // Then the date/time configuration is invalid, since it has to be in the future
                        document.getElementById(type + "_dueDate").classList.remove("is-valid");
                        document.getElementById(type + "_dueDate").classList.add("is-invalid");
                        document.getElementById(type + "_dueTime").classList.remove("is-valid");
                        document.getElementById(type + "_dueTime").classList.add("is-invalid");
                    } else {
                        // Both date and time are valid
                        document.getElementById(type + "_dueDate").classList.remove("is-invalid");
                        document.getElementById(type + "_dueDate").classList.add("is-valid");
                        document.getElementById(type + "_dueTime").classList.remove("is-invalid");
                        document.getElementById(type + "_dueTime").classList.add("is-valid");
                    }
                }
            }
        }
    }
}

addAssignmentOverlayEventListeners("newAssignment");
addAssignmentOverlayEventListeners("configureAssignment");

// Allows stacked modals
$(document).on('show.bs.modal', '.modal', function () {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function() {
        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    }, 0);
});

// Prevent some weird behavior with stacked bootstrap modals
document.body.addEventListener("hidePrevented.bs.modal", function(event) {
    event.preventDefault();
});

let newAssignmentModal = new bootstrap.Modal(document.getElementById("newAssignmentBox"));
let configureAssignmentModal = new bootstrap.Modal(document.getElementById("configureAssignmentBox"));
let newSubmissionPartModal = new bootstrap.Modal(document.getElementById("newSubmissionPartBox"));
let editSubmissionPartModal = new bootstrap.Modal(document.getElementById("editSubmissionPartBox"));
let submissionFilesModal = new bootstrap.Modal(document.getElementById("submissionFilesBox"));
let gradingRubricModal = new bootstrap.Modal(document.getElementById("gradingRubricBox"));
let makefileModal = new bootstrap.Modal(document.getElementById("makefileBox"));
let makefileUploadModal = new bootstrap.Modal(document.getElementById("uploadMakefileBox"));
let previewFileModal = new bootstrap.Modal(document.getElementById("previewFileBox"));
let IOFileModal = new bootstrap.Modal(document.getElementById("IOFileBox"));
let IOFileUploadModal = new bootstrap.Modal(document.getElementById("uploadIOFileBox"));
let grantNewExtensionModal = new bootstrap.Modal(document.getElementById("grantNewExtensionBox"));
let selectStudentForExtensionModal = new bootstrap.Modal(document.getElementById("selectStudentForExtensionBox"));
let editExtensionModal = new bootstrap.Modal(document.getElementById("editExtensionBox"));

document.getElementById("createNewAssignmentButton").addEventListener("click", function(event) {
    clearNewInputsAndValidation();
    bootstrap.Modal.getInstance(document.getElementById("newAssignmentBox")).show();
});

document.getElementById("createNewExtensionButton").addEventListener("click", function (event) {
    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
    document.getElementById("assignmentForExtension").innerText = assignmentName;

    let ajaxQuery = new XMLHttpRequest();
    ajaxQuery.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let ajaxResponse = null;
            try {
                ajaxResponse = JSON.parse(this.responseText);
            } catch (error) { // The backend returned a single error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error(this.responseText);
            }
            if(ajaxResponse !== null) {
                document.getElementById("courseWideDueDate_extension").innerText = ajaxResponse[0];
            }
        }
    };
    ajaxQuery.open("POST", "assignment_management_backend.php", true);
    ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxQuery.send("action=get_assignment_due_date" + "&name=" + assignmentName);

    bootstrap.Modal.getInstance(document.getElementById("grantNewExtensionBox")).show();

});

function validateExtensionDateListener() {
    let dueDate = document.getElementById("grantNewExtension_dueDate");
    let dueTime = document.getElementById("grantNewExtension_dueTime");
    if(dueDate.value !== "" && dueTime.value !== "") {
        let currentDueDate = getDateObjectFromString(document.getElementById("courseWideDueDate_extension").innerText);
        let newDueDate = new Date(dueDate.value + " " + dueTime.value);
        let currentDate = new Date();
        if(currentDate >= currentDueDate) { // If it is after the current assignment due date
            if(newDueDate <= currentDate) { // If the new due date is before the current date, then it is invalid
                dueDate.classList.remove("is-valid");
                dueTime.classList.remove("is-valid");
                dueDate.classList.add("is-invalid");
                dueTime.classList.add("is-invalid");
            } else {
                dueDate.classList.remove("is-invalid");
                dueTime.classList.remove("is-invalid");
                dueDate.classList.add("is-valid");
                dueTime.classList.add("is-valid");
            }
        } else { // It is before the current due date
            if(newDueDate <= currentDueDate) { // If the new due date is before the current course-wide due date, then it's invalid
                dueDate.classList.remove("is-valid");
                dueTime.classList.remove("is-valid");
                dueDate.classList.add("is-invalid");
                dueTime.classList.add("is-invalid");
            } else {
                dueDate.classList.remove("is-invalid");
                dueTime.classList.remove("is-invalid");
                dueDate.classList.add("is-valid");
                dueTime.classList.add("is-valid");
            }
        }
    }
}

document.getElementById("grantNewExtension_dueDate").addEventListener("change", validateExtensionDateListener);
document.getElementById("grantNewExtension_dueTime").addEventListener("change", validateExtensionDateListener);

document.getElementById("grantNewExtension_saveButton").addEventListener("click", function () {
    let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;

    let goodToGo = true;
    let studentInputEl = document.getElementById("grantNewExtension_student");
    if(studentInputEl.value === "") {
        goodToGo = false;
        studentInputEl.classList.add("is-invalid");
        document.getElementById("inputGroupValidationNoWork").classList.replace("d-none", "d-block");
    }

    let dueDate = document.getElementById("grantNewExtension_dueDate");
    let dueTime = document.getElementById("grantNewExtension_dueTime");
    if(dueDate.value === "" || dueTime.value === "") {
        dueDate.classList.add("is-invalid");
        dueTime.classList.add("is-invalid");
        goodToGo = false;
    } else {
        let currentDueDate = getDateObjectFromString(document.getElementById("courseWideDueDate_extension").innerText);
        let newDueDate = new Date(dueDate.value + " " + dueTime.value);
        let currentDate = new Date();
        if(currentDate >= currentDueDate && newDueDate <= currentDate) {
            dueDate.classList.add("is-invalid");
            dueTime.classList.add("is-invalid");
            goodToGo = false;
        } else if(currentDate < currentDueDate && newDueDate <= currentDueDate) {
            dueDate.classList.add("is-invalid");
            dueTime.classList.add("is-invalid");
            goodToGo = false;
        }
    }
    if(goodToGo === true) {
        refreshExtensionsTable("new_extension", assignmentName, studentInputEl.value, dueDate.value + " " + dueTime.value);
    }
});

document.getElementById("createNewSubmissionPartButton").addEventListener("click", function (event) {
    document.getElementById("submissionPartIDTitle").innerText = document.getElementById("configureAssignmentIDTitle").innerText;
    bootstrap.Modal.getInstance(document.getElementById("newSubmissionPartBox")).show();
});

document.getElementById("selectStudentForExtensionBox").addEventListener("hidden.bs.modal", function(event) {
   document.getElementById("searchUsersForExtensions").value = "";
   let studentInputEl = document.getElementById("grantNewExtension_student");
   studentInputEl.classList.remove("is-invalid");
   studentInputEl.classList.add("is-valid");
   document.getElementById("inputGroupValidationNoWork").classList.replace("d-block", "d-none");
});

document.getElementById("newAssignmentBox").addEventListener("shown.bs.modal", function (event) {
    document.getElementById("newAssignment_name").focus();
});

document.getElementById("newSubmissionPartBox").addEventListener("shown.bs.modal", function (event) {
   document.getElementById("newSubmissionPart_name").focus();
});

document.getElementById("editSubmissionPartBox").addEventListener("shown.bs.modal", function (event) {
   document.getElementById("editSubmissionPart_pointValue").focus();
});

document.getElementById("addNewSubmissionPart_Button").addEventListener("click", function (event) {
   let goodToGo = true;
   let part_name_elem = document.getElementById("newSubmissionPart_name");
   if(part_name_elem.value === "") {
       part_name_elem.classList.add("is-invalid");
       goodToGo = false;
   }
   let point_value_elem = document.getElementById("newSubmissionPart_pointValue");
    if (point_value_elem.value === "" || !Number.isInteger(Number(point_value_elem.value))) {
        point_value_elem.classList.add("is-invalid");
        goodToGo = false;
    }
    let extra_credit_elem = document.getElementById("newSubmissionPart_extraCredit");
    if (extra_credit_elem.value === "" || !Number.isInteger(Number(extra_credit_elem.value))) {
        extra_credit_elem.classList.add("is-invalid");
        goodToGo = false;
    }
    if(goodToGo) {
        refreshSubmissionPartsTable("add", document.getElementById("submissionPartIDTitle").innerText, part_name_elem.value, point_value_elem.value, extra_credit_elem.value);
        clearSubmissionPartInputsAndValidation();
    }
});

document.getElementById("editSubmissionPart_Button").addEventListener("click", function(event) {
   let goodToGo = true;
    let point_value_elem = document.getElementById("editSubmissionPart_pointValue");
    if (point_value_elem.value === "" || !Number.isInteger(Number(point_value_elem.value))) {
        point_value_elem.classList.add("is-invalid");
        goodToGo = false;
    }
    let extra_credit_elem = document.getElementById("editSubmissionPart_extraCredit");
    if (extra_credit_elem.value === "" || !Number.isInteger(Number(extra_credit_elem.value))) {
        extra_credit_elem.classList.add("is-invalid");
        goodToGo = false;
    }
    if(goodToGo) {
        refreshSubmissionPartsTable("edit", document.getElementById("configureAssignmentIDTitle").innerText, document.getElementById("editSubmissionPartIDTitle").innerText, point_value_elem.value, extra_credit_elem.value);
    }
});

function textListener() {
    if(this.value === "") {
        this.classList.remove("is-valid");
        this.classList.add("is-invalid");
    } else {
        this.classList.remove("is-invalid");
        this.classList.add("is-valid");
    }
}

function numberListener() {
    if(this.value === "" || !Number.isInteger(Number(this.value))) {
        this.classList.remove("is-valid");
        this.classList.add("is-invalid");
    } else {
        this.classList.remove("is-invalid");
        this.classList.add("is-valid");
    }
}

// TODO: Use the below event listener for all of the text-based event listeners - partly done, check for other occurences
document.getElementById("newSubmissionPart_name").addEventListener("input", textListener);

// TODO: Combine the two event listeners below into one function and use it for all the number-based event listeners - partly done, check for other occurences
document.getElementById("newSubmissionPart_pointValue").addEventListener("input", numberListener);
document.getElementById("editSubmissionPart_pointValue").addEventListener("input", numberListener);

document.getElementById("newSubmissionPart_extraCredit").addEventListener("input", numberListener);
document.getElementById("editSubmissionPart_extraCredit").addEventListener("input", numberListener);

document.getElementById("detailsTab").addEventListener("click", function(event) {
    resetTabActiveStates(this);
});

document.getElementById("submissionsTab").addEventListener("click", function(event) {
    resetTabActiveStates(this);
});

document.getElementById("gradingTab").addEventListener("click", function(event) {
    resetTabActiveStates(this);
});

document.getElementById("extensionsTab").addEventListener("click", function(event) {
    resetTabActiveStates(this);
});

document.getElementById("selectSubPart").addEventListener("change", resetDisabledState(false));

document.getElementById("gradingRubricButton").addEventListener("click", function(event) {
    let assignment_name = document.getElementById("configureAssignmentIDTitle").innerText;
    let part_name = document.getElementById("selectSubPart").value;
    document.getElementById("gradingRubricTitle").innerText = part_name;

    bootstrap.Modal.getInstance(document.getElementById("gradingRubricBox")).show();
    document.getElementById("spinnerDivGradingRubric").classList.replace("d-none", "d-block");
    refreshGradingRubricTable(assignment_name, part_name);
});

document.getElementById("sampleMakefileButton").addEventListener("click", makefileButton("Sample"));

document.getElementById("testingMakefileButton").addEventListener("click", makefileButton("Testing"));

document.getElementById("sampleIOFilesButton").addEventListener("click", IOFileButton("Sample"));

document.getElementById("testingIOFilesButton").addEventListener("click", IOFileButton("Testing"));

function makefileButton(type) {
    return function() {
        let assignment = document.getElementById("configureAssignmentIDTitle").innerText;
        let part = document.getElementById("selectSubPart").value;
        document.getElementById("sampleOrTestingMakefile").innerText = type;
        document.getElementById("makefilePartTitle").innerText = part;
        bootstrap.Modal.getInstance(document.getElementById("makefileBox")).show();
        document.getElementById("spinnerDivMakefile").classList.replace("d-none", "d-block");
        refreshMakefileTable("load", type, assignment, part);
    }
}

function IOFileButton(type) {
    return function () {
        let assignment = document.getElementById("configureAssignmentIDTitle").innerText;
        let part = document.getElementById("selectSubPart").value;
        document.getElementById("sampleOrTestingIOFile").innerText = type;
        document.getElementById("IOFilePartTitle").innerText = part;
        bootstrap.Modal.getInstance(document.getElementById("IOFileBox")).show();
        document.getElementById("spinnerDivIOFile").classList.replace("d-none", "d-block");
        refreshIOFileTable("load", type, assignment, part);
    }
}

function loadAuxiliaryFileActions(value) {
    let action_html = "<div class='container'>";
    action_html += "<div class='row flex-nowrap'>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-primary w-100 preview_aux_file_button'>Preview</button>";
    action_html += "</div>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-danger w-100 delete_aux_file_button'>Delete</button>";
    action_html += "</div>";
    action_html += "</div>";
    action_html += "</div>";
    return action_html;
}



document.getElementById("uploadFile_Makefile").addEventListener("click", function (event) {
    document.getElementById("sampleOrTestingMakefile_Upload").innerText = document.getElementById("sampleOrTestingMakefile").innerText;
    bootstrap.Modal.getInstance(document.getElementById("uploadMakefileBox")).show();
});

document.getElementById("uploadFile_IOFile").addEventListener("click", function (event) {
    document.getElementById("sampleOrTestingIOFile_Upload").innerText = document.getElementById("sampleOrTestingIOFile").innerText;
    bootstrap.Modal.getInstance(document.getElementById("uploadIOFileBox")).show();
});

document.getElementById("submit_uploadedMakefile").addEventListener("click", function (event) {
    let type = document.getElementById("sampleOrTestingMakefile").innerText;
    let assignment = document.getElementById("configureAssignmentIDTitle").innerText;
    let part = document.getElementById("selectSubPart").value;

   let submitted_makefile = document.getElementById("uploadedMakefile").files[0];
   if(submitted_makefile['name'] !== "Makefile") {
       alertify.set('notifier','position', 'top-center');
       alertify.set('notifier','delay', 0);
       alertify.error("ERROR: The file you upload must be named 'Makefile'!");
   } else {
       let newMakefileData = new FormData();
       newMakefileData.append("makefile", submitted_makefile);
       newMakefileData.append("action", "upload_makefile");
       newMakefileData.append("type", type);
       newMakefileData.append("assignment", assignment);
       newMakefileData.append("part", part);
       refreshMakefileTable("upload_makefile", type, assignment, part, newMakefileData);
   }
});

document.getElementById("submit_uploadedIOFile").addEventListener("click", function (event) {
    let firstType = document.getElementById("sampleOrTestingIOFile").innerText.toUpperCase();
    let secondType = document.getElementById("uploadedIOFile_type").value.split(" ")[0].toUpperCase();

    let type = firstType + "_" + secondType;
    let assignment = document.getElementById("configureAssignmentIDTitle").innerText;
    let part = document.getElementById("selectSubPart").value;
    let submitted_io_file = document.getElementById("uploadedIOFile").files[0];
    let newIOFileData = new FormData();
    newIOFileData.append("io_file", submitted_io_file);
    newIOFileData.append("file_name", submitted_io_file['name'])
    newIOFileData.append("action", "upload_io_file");
    newIOFileData.append("type", type);
    newIOFileData.append("assignment", assignment);
    newIOFileData.append("part", part);
    refreshIOFileTable("upload_io_file", type, assignment, part, newIOFileData);
});

document.getElementById("uploadedMakefile").addEventListener("change", function () {
    document.getElementById("submit_uploadedMakefile").disabled = false;
});

document.getElementById("uploadedIOFile").addEventListener("change", function () {
   document.getElementById("submit_uploadedIOFile").disabled = false;
});

document.getElementById("configureAssignmentBox").addEventListener("hidden.bs.modal", function() {
    for (let index = 0; index < tooltipListTable.length; index++) {
        tooltipListTable[index].enable();
    }
});

document.getElementById("newSubmissionPartBox").addEventListener("hidden.bs.modal", function() {
    clearSubmissionPartInputsAndValidation();
    for (let index = 0; index < tooltipListTableSubParts.length; index++) {
        tooltipListTableSubParts[index].enable();
    }
});

document.getElementById("grantNewExtensionBox").addEventListener("hidden.bs.modal", function() {
   let validation_elems = document.getElementsByClassName("grantNewExtension_validation");
   for(let index = 0; index < validation_elems.length; index++) {
       validation_elems[index].classList.remove("is-invalid");
       validation_elems[index].classList.remove("is-valid");
       validation_elems[index].value = "";
   }
});

document.getElementById("editSubmissionPartBox").addEventListener("hidden.bs.modal", function() {
    clearEditPartInputsAndValidation();
    for (let index = 0; index < tooltipListTableSubParts.length; index++) {
        tooltipListTableSubParts[index].enable();
    }
});

document.getElementById("submissionFilesBox").addEventListener("hidden.bs.modal", function() {
    for (let index = 0; index < tooltipListTableSubParts.length; index++) {
        tooltipListTableSubParts[index].enable();
    }
    document.getElementById("spinnerDivSubFiles").classList.replace("d-none", "d-block");
});

document.getElementById("addNewSubmissionFile").addEventListener("click", function() {
    let newRow = document.getElementById("submissionFilesTable").insertRow(-1);
    let leftCell = newRow.insertCell(0);
    let textInput = document.createElement("input");
    textInput.setAttribute("type", "text");
    textInput.classList.add("form-control", "subFile_textBox");
    leftCell.appendChild(textInput);

    let rightCell = newRow.insertCell(1);
    let removeFileButton = document.createElement("button");
    removeFileButton.setAttribute("type", "button");
    removeFileButton.classList.add("btn", "btn-danger", "w-100");
    removeFileButton.innerText = "Remove File";
    removeFileButton.addEventListener("click", deleteRow);
    rightCell.appendChild(removeFileButton);
});

function deleteRow() {
    document.getElementById("submissionFilesTable").deleteRow(this.closest("tr").rowIndex);
}

function resetDisabledState(disabled_bool) {
    return function() {
        let gradingTestingButtons = document.getElementsByClassName("gradingTestingButton");
        for(let index=0; index < gradingTestingButtons.length; index++) {
            gradingTestingButtons[index].disabled = disabled_bool;
        }
    }
}

document.getElementById("submissionFiles_Button").addEventListener("click", function() {
    let fileNames = document.getElementById("submissionFilesTable").querySelectorAll("td:first-child");
    let fileNamesArray = [];
    let goodToGo = true;
    for(let index = 0; index < fileNames.length; index++) {
        let tempValue = fileNames[index].childNodes[0].value;
        // Ignore blank spaces and move on
        if(tempValue === "") {
            alertify.set('notifier','position', 'top-center');
            alertify.set('notifier','delay', 0);
            alertify.error("You cannot have a submission file with an empty input box!");
            goodToGo = false;
            break;
        }
        if(fileNamesArray.includes(tempValue)) {
            alertify.set('notifier','position', 'top-center');
            alertify.set('notifier','delay', 0);
            alertify.error("You have more than one instance of the submission file named: " + tempValue);
            goodToGo = false;
            break;
        }
        fileNamesArray.push(tempValue);
    }
    if(goodToGo === true) {
        let assignmentName = document.getElementById("configureAssignmentIDTitle").innerText;
        let partName = document.getElementById("submissionFilesTitle").innerText;

        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                if(this.responseText[0] === "E") {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error(this.responseText);
                } else {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 5);
                    alertify.success(this.responseText);
                }
            }
        };
        ajaxQuery.open("POST", "assignment_management_backend.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("action=update_submission_files" + "&name=" + assignmentName + "&part=" + partName + "&newFiles=" + JSON.stringify(fileNamesArray));
    }
});

function resetTabActiveStates(tab) {
    let configuretabsList = document.querySelectorAll(".configure_tab");
    for(let index = 0; index < configuretabsList.length; index++) {
        if(configuretabsList[index].classList.contains("disabled")) {
            configuretabsList[index].classList.remove("active");
            configuretabsList[index].classList.remove("disabled");
        }
    }
    tab.classList.add("active", "disabled");
}

tooltipTriggerListConfigure = [].slice.call(document.querySelectorAll('.configure_button'))
tooltipListConfigure = tooltipTriggerListConfigure.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl,
        {
            delay: { "show": 1400, "hide": 0 },
        })
});