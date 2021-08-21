let sourceInBox = false;

toolTips = {};

let tooltipTriggerListTable = [];
let tooltipListTable = [];

refreshUsersTable(false, "refresh");

function refreshUsersTable(goToPage, type, updatedUser, studentData, firstName, lastName, nameID, discussion_section, role, status) {
    document.getElementById("searchUsers").value = "";
    $usersTable = $('#usersTable');
    let currPageSize = $usersTable.bootstrapTable('getOptions')['pageSize'];
    if(goToPage === false) {
        if(type === "refresh") {
            // Just refresh the table - that's it :)
            $.ajax({
                type: "POST",
                url: "user_management_backend.php",
                data: {action: "refresh_table"},
                success: function (data) {
                    document.getElementById("spinnerDiv").classList.replace("d-block", "d-none");
                    $usersTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        height: window.innerHeight - document.getElementById("topNavBar").offsetHeight - document.getElementById("buttonsRow").offsetHeight - document.getElementById("courseCountDiv").offsetHeight - 16,
                        onPostBody: reCompileTable
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
        } else if(type === "uploadRoster") {
            $.ajax({
                type: "POST",
                url: "user_management_backend.php",
                contentType: false,
                processData: false,
                data: studentData,
                success: function (data) {
                    $usersTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        height: window.innerHeight - document.getElementById("topNavBar").offsetHeight - document.getElementById("buttonsRow").offsetHeight - document.getElementById("courseCountDiv").offsetHeight - 16,
                        onPostBody: reCompileTable
                    });
                    if(data.message === "ERROR") {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 0);
                        alertify.error("File uploading finished with errors. Check the javascript console for more details.");

                        for(let index = 0; index < data.error_list.length; index++) {
                            console.error(data.error_list[index]);
                        }
                    } else {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 5);
                        alertify.success("Successfully uploaded all students from the input file!");
                    }

                    toggleOverlay("fileUpload").call();
                    document.getElementById("userCount").innerText = "Total Users: " + data.userCount;

                    document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "Email " + data.emailCount + " New Users";
                    document.getElementById("emailAllUsersButton").disabled = false;
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
    } else { // We need to go to a specific page and row index
        if(type === "newUser" || type === "editUser") {
            $.ajax({
                type: "POST",
                url: "user_management_backend.php",
                data: {action: type, pageSize: currPageSize, userID: updatedUser, firstname: firstName, lastname: lastName, nameID: nameID, discussion: discussion_section, role: role, status: status},
                success: function (data) {
                    $usersTable.bootstrapTable('destroy').bootstrapTable({
                        columns: JSON.parse(data.columns),
                        data: JSON.parse(data.table),
                        height: window.innerHeight - document.getElementById("topNavBar").offsetHeight - document.getElementById("buttonsRow").offsetHeight - document.getElementById("courseCountDiv").offsetHeight - 16,
                        onPostBody: reCompileTable
                    })
                        .bootstrapTable('selectPage', data.page).bootstrapTable('scrollTo', {
                        unit: 'rows',
                        value: data.row,
                    });
                    if (data.message[0] === "E") {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 0);
                        alertify.error(data.message);
                    } else {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 5);
                        alertify.success(data.message);

                        toggleOverlay(type).call();

                        document.getElementById("emailAllUsersButton").disabled = false;
                        document.getElementById("userCount").innerText = "Total Users: " + data.userCount;
                        if(data.emailCount === 1) {
                            document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "Email 1 New User";
                        } else {
                            document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "Email " + data.emailCount + " New Users";
                        }
                        document.getElementById(type + "_firstName").value = "";
                        document.getElementById(type + "_firstName").classList.remove("is-valid");
                        document.getElementById(type + "_lastName").value = "";
                        document.getElementById(type + "_lastName").classList.remove("is-valid");
                        if (type === "newUser") {
                            document.getElementById(type + "_campusID").value = "";
                            document.getElementById(type + "_campusID").classList.remove("is-valid");
                        }
                        document.getElementById(type + "_nameID").value = "";
                        document.getElementById(type + "_nameID").classList.remove("is-valid");

                        document.getElementById(type + "_discussion").value = "";
                        document.getElementById(type + "_discussion").classList.remove("is-valid");
                        document.getElementById(type + "_noDiscussionCheck").checked = false;
                        document.getElementById(type + "_discussion").disabled = false;

                        if (type === "editUser") {
                            document.getElementById(type + "_studentRadio").checked = false;
                        } else {
                            document.getElementById(type + "_studentRadio").checked = true;
                        }
                        document.getElementById(type + "_taRadio").checked = false;
                        document.getElementById(type + "_instructorRadio").checked = false;
                    }
                },
                error: function (xhr, status, error) {
                    // There was a SQL connection error
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(xhr.responseText + " " + error);
                },
                dataType: 'json'
            });
        } else { // Goal is to delete user and refresh
            $.ajax({
                type: "POST",
                url: "user_management_backend.php",
                data: {action: "delete", user: updatedUser},
                success: function (data) {
                    if(data.message[0] === "E") {
                        alertify.set('notifier','position', 'top-center');
                        alertify.set('notifier','delay', 0);
                        alertify.error(xhr.responseText);
                    } else {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 5);
                        alertify.success(data.message);

                        document.getElementById("userCount").innerText = "Total Users: " + data.userCount;
                        if(Number(data.emailCount) === 0) {
                            document.getElementById("emailAllUsersButton").disabled = true;
                            document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "All Users Emailed";
                        } else if(Number(data.emailCount) === 1) {
                            document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "Email 1 New User";
                        } else {
                            document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "Email " + data.emailCount + " New Users";
                        }

                        $usersTable.bootstrapTable('destroy').bootstrapTable({
                            columns: JSON.parse(data.columns),
                            data: JSON.parse(data.table),
                            height: window.innerHeight - document.getElementById("topNavBar").offsetHeight - document.getElementById("buttonsRow").offsetHeight - document.getElementById("courseCountDiv").offsetHeight - 16,
                            onPostBody: reCompileTable
                        });
                    }
                },
                error: function (xhr, status, error) {
                    // There was a SQL connection error
                    alertify.set('notifier','position', 'top-center');
                    alertify.set('notifier','delay', 0);
                    alertify.error(xhr.responseText + " " + error);
                },
                dataType: 'json'
            });
        }
    }
}

function reCompileTable() {
    tooltipListTable = [];
    for (let index = 0; index < tooltipListTable.length; index++) {
        tooltipListTable[index].dispose();
    }

    removeEmailListeners();
    removeEditListeners();
    removeDeleteListeners();

    emailListener();
    editListener();
    deleteListener();

    tooltipTriggerListTable = [].slice.call(document.querySelectorAll('.table_button'))
    tooltipListTable = tooltipTriggerListTable.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl,
            {
                delay: { "show": 1400, "hide": 0 }
            })
    });
}

function loadActions() {
    let action_html = "<div class='container'>";
    action_html += "<div class='row flex-nowrap'>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-primary btn-lg w-100 table_button email_button' data-bs-toggle='tooltip' title='Send Registration E-mail to User'><i class='bi bi-envelope'></i></button>";
    action_html += "</div>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-success btn-lg w-100 table_button edit_button' data-bs-toggle='tooltip' title='Edit User'><i class='bi bi-pencil-square'></i></button>";
    action_html += "</div>";
    action_html += "<div class='col'>";
    action_html += "<button class='btn btn-danger btn-lg w-100 table_button delete_button' data-bs-toggle='tooltip' title='Remove User from Database'><i class='bi bi-trash'></i></button>";
    action_html += "</div>";
    action_html += "</div>";
    action_html += "</div>";
    return action_html;
}

function toggleOverlay(type) {
    return function() {
        if (document.getElementById(type + "Overlay").classList.contains("d-none")) {
            for (let index = 0; index < tooltipListTable.length; index++) {
                tooltipListTable[index].hide();
                tooltipListTable[index].disable();
            }
            document.getElementById(type + "Overlay").classList.replace("d-none", "d-block");
            if(type === "newUser") {
                document.getElementById(type + "_firstName").focus();
            } else if(type === "editUser") {
                document.getElementById(type + "_firstName").classList.remove("is-invalid");
                document.getElementById(type + "_lastName").classList.remove("is-invalid");
                document.getElementById(type + "_nameID").classList.remove("is-invalid");
                document.getElementById(type + "_discussion").classList.remove("is-invalid");
                document.getElementById(type + "_firstName").classList.add("is-valid");
                document.getElementById(type + "_lastName").classList.add("is-valid");
                document.getElementById(type + "_nameID").classList.add("is-valid");
                document.getElementById(type + "_discussion").classList.add("is-valid");
            }
        } else {
            document.getElementById(type + "Overlay").classList.replace("d-block", "d-none");
            if(type === "fileUpload") {
                document.getElementById("rexFile").value = "";
                setFileUploadStatus("default", "No file selected yet!");
                document.getElementById("fileUpload_submit").children[0].remove();
                document.getElementById("fileUpload_submit").innerText = "Submit File";
            }
            for (let index = 0; index < tooltipListTable.length; index++) {
                tooltipListTable[index].enable();
            }
        }
    }
}

document.getElementById("fileUploadButton").addEventListener("click", toggleOverlay("fileUpload"));
document.getElementById("addNewUserButton").addEventListener("click", toggleOverlay("newUser"));
document.getElementById("fileUpload_submit").addEventListener("click", fileUploadPrep);

function fileUploadPrep() {
    document.getElementById("fileUpload_submit").innerText = "";
    document.getElementById("fileUpload_submit").insertAdjacentHTML('afterbegin', "<span class='spinner-border spinner-border-sm' role='status' ></span>&nbspLoading...");
    let studentData = new FormData();
    let rexOutput = document.getElementById("rexFile").files[0];
    studentData.append("rexFile", rexOutput);
    studentData.append("action", "upload_students");

    refreshUsersTable(false, "uploadRoster", null, studentData);
}

function addBaseEventListeners(type) {
    document.getElementById(type + "Box").addEventListener("mousedown", function(event) {
        event.stopPropagation();
        sourceInBox = true;
    });

    document.getElementById(type + "Box").addEventListener("mouseup", function(event) {
        event.stopPropagation();
    });

    document.getElementById(type + "Overlay").addEventListener("mousedown", function(event) {
        sourceInBox = false;
    });

    document.getElementById(type + "Overlay").addEventListener("mouseup", function(event) {
        if(sourceInBox === false) {
            toggleOverlay(type).call();
        }
        sourceInBox = true;
    });

    document.getElementById("exit_" + type + "Overlay").addEventListener("click", toggleOverlay(type));
}

document.getElementById("rexFile").addEventListener("change", function() {
    let rexOutput = document.getElementById("rexFile").files[0];
    if(rexOutput !== undefined) {
        let fileReader = new FileReader();
        fileReader.readAsText(rexOutput, "UTF-8");
        fileReader.onload = function(event) {
            let fileText = event.target.result;
            if(rexOutput['name'].split(".")[1] !== "csv") {
                setFileUploadStatus("error", "You must upload a CSV file!");
            } else {
                let csvFile = fileText.split("\n");
                let headerLineList = csvFile[0].split(",");

                // Headings required for file input from REX
                let requiredHeaderList = ["StudentLastName", "StudentFirstName", "StudentCampusID", "StudentMyUMBCId", "ClassNumberClassSectionSourceKey"];

                let goodToUpload = true;
                let index = 0;
                while(goodToUpload && index < requiredHeaderList.length) {
                    if(headerLineList.includes(requiredHeaderList[index]) === false) {
                        goodToUpload = false;
                        setFileUploadStatus("error", "The '" + requiredHeaderList[index] + "' header was not found in this CSV file!");
                    }
                    index++;
                }
                if(goodToUpload) {
                    let totalUsersFound = getUserCountInFile(headerLineList, csvFile.slice(1));
                    setFileUploadStatus("success", "File format is correct.\nFound " + totalUsersFound + " students to upload.");
                    document.getElementById("fileUpload_submit").disabled = false;
                    document.getElementById("fileUpload_submit").classList.replace("btn-outline-success", "btn-success");
                }
            }
        }
        fileReader.onerror = function(event) {
            setFileUploadStatus("error", "There was an error loading that file!");
        }
    }
});

function getUserCountInFile(headerList, csvFile) {
    let id_list = [];
    let umbc_id_index = headerList.indexOf("StudentCampusID");
    for(let index = 0; index < csvFile.length; index++) {
        if(csvFile[index] !== "") {
            id_list.push(csvFile[index].split(",")[umbc_id_index]);
        }
    }
    return new Set(id_list).size; // The set constructor removes duplicates if passed in an array :)
}

function setFileUploadStatus(type, message) {
    if(type === "error") {
        if(document.getElementById("fileUploadSymbolBackground").classList.contains("bg-secondary")) {
            document.getElementById("fileUploadSymbolBackground").classList.replace("bg-secondary", "bg-danger");
            document.getElementById("fileUploadStatusSymbol").classList.replace("bi-info-circle", "bi-exclamation-circle");
        } else if(document.getElementById("fileUploadSymbolBackground").classList.contains("bg-success")) {
            document.getElementById("fileUpload_submit").disabled = true;
            document.getElementById("fileUpload_submit").classList.replace("btn-success", "btn-outline-success");
            document.getElementById("fileUploadSymbolBackground").classList.replace("bg-success", "bg-danger");
            document.getElementById("fileUploadStatusSymbol").classList.replace("bi-check-circle", "bi-exclamation-circle");
        }
    } else if(type === "success") {
        if(document.getElementById("fileUploadSymbolBackground").classList.contains("bg-secondary")) {
            document.getElementById("fileUploadSymbolBackground").classList.replace("bg-secondary", "bg-success");
            document.getElementById("fileUploadStatusSymbol").classList.replace("bi-info-circle", "bi-check-circle");
        } else if(document.getElementById("fileUploadSymbolBackground").classList.contains("bg-danger")) {
            document.getElementById("fileUploadSymbolBackground").classList.replace("bg-danger", "bg-success");
            document.getElementById("fileUploadStatusSymbol").classList.replace("bi-exclamation-circle", "bi-check-circle");
        }
    } else {
        if(document.getElementById("fileUploadSymbolBackground").classList.contains("bg-success")) {
            document.getElementById("fileUpload_submit").disabled = true;
            document.getElementById("fileUpload_submit").classList.replace("btn-success", "btn-outline-success");
            document.getElementById("fileUploadSymbolBackground").classList.replace("bg-success", "bg-secondary");
            document.getElementById("fileUploadStatusSymbol").classList.replace("bi-check-circle", "bi-info-circle");
        } else if(document.getElementById("fileUploadSymbolBackground").classList.contains("bg-danger")) {
            document.getElementById("fileUploadSymbolBackground").classList.replace("bg-danger", "bg-secondary");
            document.getElementById("fileUploadStatusSymbol").classList.replace("bi-exclamation-circle", "bi-info-circle");
        }
    }
    document.getElementById("fileUploadStatusText").innerText = message;
}

function addUserOverlayEventListeners(type) { // type is "newUser", "editUser", or "fileUpload"
    addBaseEventListeners(type);
    if (type === "newUser" || type === "editUser") {
        document.getElementById(type + "_noDiscussionCheck").addEventListener("change", function (event) {
            if (this.checked === true) {
                document.getElementById(type + "_discussion").disabled = true;
                document.getElementById(type + "_discussion").value = "";
                document.getElementById(type + "_discussion").classList.remove("is-invalid");
                document.getElementById(type + "_discussion").classList.add("is-valid");
            } else {
                document.getElementById(type + "_discussion").disabled = false;
                document.getElementById(type + "_discussion").classList.remove("is-valid");
                document.getElementById(type + "_discussion").classList.add("is-invalid");
            }
        });

        document.getElementById(type + "_firstName").addEventListener("input", function (event) {
            if (document.getElementById(type + "_firstName").value === "") {
                document.getElementById(type + "_firstName").classList.remove("is-valid");
                document.getElementById(type + "_firstName").classList.add("is-invalid");
            } else {
                document.getElementById(type + "_firstName").classList.remove("is-invalid");
                document.getElementById(type + "_firstName").classList.add("is-valid");
            }
        });

        document.getElementById(type + "_lastName").addEventListener("input", function (event) {
            if (document.getElementById(type + "_lastName").value === "") {
                document.getElementById(type + "_lastName").classList.remove("is-valid");
                document.getElementById(type + "_lastName").classList.add("is-invalid");
            } else {
                document.getElementById(type + "_lastName").classList.remove("is-invalid");
                document.getElementById(type + "_lastName").classList.add("is-valid");
            }
        });

        if(type === "newUser") {
            document.getElementById(type + "_campusID").addEventListener("input", function (event) {
                if (document.getElementById(type + "_campusID").value === "" || document.getElementById("newUser_campusID").value.length != 7) {
                    document.getElementById(type + "_campusID").classList.remove("is-valid");
                    document.getElementById(type + "_campusID").classList.add("is-invalid");
                } else {
                    document.getElementById(type + "_campusID").classList.remove("is-invalid");
                    document.getElementById(type + "_campusID").classList.add("is-valid");
                }
            });
        }

        document.getElementById(type + "_nameID").addEventListener("input", function (event) {
            if (document.getElementById(type + "_nameID").value === "") {
                document.getElementById(type + "_nameID").classList.remove("is-valid");
                document.getElementById(type + "_nameID").classList.add("is-invalid");
            } else {
                document.getElementById(type + "_nameID").classList.remove("is-invalid");
                document.getElementById(type + "_nameID").classList.add("is-valid");
            }
        });

        document.getElementById(type + "_discussion").addEventListener("input", function (event) {
            if (document.getElementById(type + "_discussion").value === "" || !Number.isInteger(Number(document.getElementById(type + "_discussion").value))) {
                document.getElementById(type + "_discussion").classList.remove("is-valid");
                document.getElementById(type + "_discussion").classList.add("is-invalid");
            } else {
                document.getElementById(type + "_discussion").classList.remove("is-invalid");
                document.getElementById(type + "_discussion").classList.add("is-valid");
            }
        });

        document.getElementById(type + "_finalButton").addEventListener("click", function (event) {
            let goodToGo = true;
            if (document.getElementById(type + "_firstName").value === "") {
                goodToGo = false;
                document.getElementById(type + "_firstName").classList.add("is-invalid");
            }
            if (document.getElementById(type + "_lastName").value === "") {
                goodToGo = false;
                document.getElementById(type + "_lastName").classList.add("is-invalid");
            }
            if (type === "newUser") {
                if ((document.getElementById(type + "_campusID").value === "" || document.getElementById(type + "_campusID").value.length != 7)) {
                    goodToGo = false;
                    document.getElementById(type + "_campusID").classList.add("is-invalid");
                }
            }
            if (document.getElementById(type + "_nameID").value === "") {
                goodToGo = false;
                document.getElementById(type + "_nameID").classList.add("is-invalid");
            }
            if (document.getElementById(type + "_noDiscussionCheck").checked === false) {
                if ((document.getElementById(type + "_discussion").value === "" || !Number.isInteger(Number(document.getElementById(type + "_discussion").value)))) {
                    goodToGo = false;
                    document.getElementById(type + "_discussion").classList.add("is-invalid");
                }
            }

            // All of the verification has been completed! Yippee!
            if (goodToGo === true) {
                let first_name = document.getElementById(type + "_firstName").value;
                let last_name = document.getElementById(type + "_lastName").value;
                let campus_id = null;
                if (type === "newUser") {
                    campus_id = document.getElementById(type + "_campusID").value;
                } else {
                    campus_id = document.getElementById("editUserIDTitle").innerText;
                }
                let name_id = document.getElementById(type + "_nameID").value;

                let discussion_section = null;
                if (document.getElementById(type + "_noDiscussionCheck").checked === false) {
                    discussion_section = document.getElementById(type + "_discussion").value;
                } else {
                    discussion_section = "NULL";
                }

                let role = null;
                if (document.getElementById(type + "_studentRadio").checked === true) {
                    role = "Student";
                } else if (document.getElementById(type + "_taRadio").checked === true) {
                    role = "TA";
                } else {
                    role = "Instructor";
                }

                let status = null;
                if (type === "editUser") {
                    if (document.getElementById("statusUpdate_Active").checked === true) {
                        status = "Active";
                    } else {
                        status = "Dropped";
                    }
                } else {
                    status = "Active";
                }

                refreshUsersTable(true, type, campus_id, null, first_name, last_name, name_id, discussion_section, role, status);
            }
        });
    }
}

document.getElementById("emailAllUsersButton").addEventListener("click", function() {
    let number_users = this.innerText.split(" ")[2];
    alertify.confirm("Are you sure you want to send registration emails to " + number_users +  " new users?", function (selection) {
        document.getElementById("emailAllUsersButton").innerText = "";
        document.getElementById("emailAllUsersButton").insertAdjacentHTML('afterbegin', "<span class='spinner-border spinner-border-sm' role='status' ></span>&nbspLoading...");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                try {
                    ajaxResponse = JSON.parse(this.responseText);
                } catch (error) { // The backend returned a single error
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error("Could not decode JSON Response: " + this.responseText);
                }
                if(ajaxResponse !== null) {
                    if(ajaxResponse.length === 0) {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 5);
                        alertify.success("Successfully sent registration emails to " + number_users + " new users!");
                    } else {
                        for(let index = 0; index < ajaxResponse.lenth; index++) {
                            console.log(ajaxResponse[index]);
                        }
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 0);
                        alertify.error("Email sending failed for some users. Check the javascript console for more details.");
                    }
                    document.getElementById("emailAllUsersButton").children[0].remove();
                    document.getElementById("emailAllUsersButton").disabled = true;
                    document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "All Users Emailed";
                }
            }
        };
        ajaxQuery.open("POST", "user_management_backend.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("action=email_all");
    });
});

function removeEmailListeners() {
    let email_buttons = document.getElementsByClassName("email_button");
    for(let index = 0; index < email_buttons.length; index++) {
        email_buttons[index].removeEventListener("click", emailUserPrep);
    }
}

function removeEditListeners() {
    let edit_buttons = document.getElementsByClassName("edit_button");
    for(let index = 0; index < edit_buttons.length; index++) {
        edit_buttons[index].removeEventListener("click", editUserPrep);
    }
}

function removeDeleteListeners() {
    let delete_buttons = document.getElementsByClassName("delete_button");
    for(let index = 0; index < delete_buttons.length; index++) {
        delete_buttons[index].removeEventListener("click", deleteUserPrep);
    }
}

function emailUserPrep() {
    let row_element = this.closest("tr").getElementsByTagName("td");
    let umbc_id = row_element[0].innerText;
    let last_name = row_element[1].innerText;
    let first_name = row_element[2].innerText;
    let name_id = row_element[3].innerText;
    let currPageSize = $usersTable.bootstrapTable('getOptions')['pageSize'];
    alertify.confirm('Are you sure you want to send a registration email to ' + first_name + " " + last_name + " (" + name_id + ")?", function (selection) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
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
                    if(ajaxResponse[0][0] === "E") {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 0);
                        alertify.error(ajaxResponse[0]);
                    } else {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.set('notifier', 'delay', 5);
                        alertify.success(ajaxResponse[0]);
                    }
                }
                let emailCount = ajaxResponse[1];
                if(emailCount === 0) {
                    document.getElementById("emailAllUsersButton").disabled = true;
                    document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "All Users Emailed";
                } else if(emailCount === 1) {
                    document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "Email 1 New User";
                } else {
                    document.getElementById("emailAllUsersButton").innerHTML = "<i class='bi bi-envelope-fill'></i>&nbsp&nbsp " + "Email " + emailCount + " New Users";
                }
            }
        };
        ajaxQuery.open("POST", "user_management_backend.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("action=send_email" + "&umbc_id=" + umbc_id);
    });
}

function emailListener() {
    let email_buttons = document.getElementsByClassName("email_button");
    for(let index = 0; index < email_buttons.length; index++) {
        email_buttons[index].addEventListener("click", emailUserPrep);
    }
}

function editListener() {
    let edit_buttons = document.getElementsByClassName("edit_button");
    for(let index = 0; index < edit_buttons.length; index++) {
        edit_buttons[index].addEventListener("click", editUserPrep);
    }
}

function deleteListener() {
    let delete_buttons = document.getElementsByClassName("delete_button");
    for(let index = 0; index < delete_buttons.length; index++) {
        delete_buttons[index].addEventListener("click", deleteUserPrep);
    }
}

function editUserPrep() {
    let row_element = this.closest("tr").getElementsByTagName("td");
    let umbc_id = row_element[0].innerText;
    let last_name = row_element[1].innerText;
    let first_name = row_element[2].innerText;
    let name_id = row_element[3].innerText;
    let discussion = row_element[4].innerText;
    let role = row_element[5].innerText;
    let status = row_element[6].innerText;

    document.getElementById("editUserIDTitle").innerText = umbc_id;

    document.getElementById("editUser_firstName").value = first_name;
    document.getElementById("editUser_lastName").value = last_name;
    document.getElementById("editUser_nameID").value = name_id;

    document.getElementById("editUser_noDiscussionCheck").checked = false;
    document.getElementById("editUser_discussion").disabled = false;
    document.getElementById("editUser_discussion").value = "";
    if(discussion === "-") {
        document.getElementById("editUser_noDiscussionCheck").checked = true;
        document.getElementById("editUser_discussion").disabled = true;
    } else {
        document.getElementById("editUser_discussion").value = discussion;
    }

    if(role === "Instructor") {
        document.getElementById("editUser_instructorRadio").checked = true;
    } else if(role === "TA") {
        document.getElementById("editUser_taRadio").checked = true;
    } else {
        document.getElementById("editUser_studentRadio").checked = true;
    }

    if(status === "Active") {
        document.getElementById("statusUpdate_Active").checked = true;
    } else {
        document.getElementById("statusUpdate_Dropped").checked = true;
    }

    toggleOverlay("editUser").call();
}

function deleteUserPrep() {
    let row_element = this.closest("tr").getElementsByTagName("td");
    let umbc_id = row_element[0].innerText;
    let last_name = row_element[1].innerText;
    let first_name = row_element[2].innerText;
    let name_id = row_element[3].innerText;

    alertify.confirm('Are you sure you want to remove ' + first_name + " " + last_name + " (" + name_id + ") from the database?", function (selection) {
        refreshUsersTable(true, "deleteUser", umbc_id);
    });
}

let tooltipTriggerListInfoHelp = [].slice.call(document.querySelectorAll('.infoHelp_tip'));
let tooltipListInfoHelp = tooltipTriggerListInfoHelp.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

addUserOverlayEventListeners("newUser");
addUserOverlayEventListeners("editUser");
addUserOverlayEventListeners("fileUpload");