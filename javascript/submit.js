let newSubmissionModal = new bootstrap.Modal(document.getElementById("newSubmissionBox"));

let new_submission_buttons = document.getElementsByClassName("newSubmissionForPart");
for(let index = 0; index < new_submission_buttons.length; index++) {
    new_submission_buttons[index].addEventListener("click", function (event) {
       let assignmentName = document.getElementById("assignmentTitle").innerText;
       let partName = this.parentNode.children[0].innerText;
       let number_id = this.parentNode.children[1].innerText;
       document.getElementById("newSubmissionPartTitle").innerText = "New Submission for '" + partName + "'";

       let ajaxQuery = new XMLHttpRequest();
       ajaxQuery.onreadystatechange = function() {
           if (this.readyState === 4 && this.status === 200) {
               let ajaxResponse = null;
               try {
                    ajaxResponse = JSON.parse(this.responseText);
               } catch (error) {
                   alertify.set('notifier', 'position', 'top-center');
                   alertify.set('notifier', 'delay', 0);
                   alertify.error(this.responseText);
               }
               if(ajaxResponse != null) {
                   let submitFilesDiv = document.getElementById("submitFilesDiv");
                   submitFilesDiv.innerHTML = "";
                   let hidden_partname_text = document.createElement("p");
                   hidden_partname_text.innerText = partName;
                   hidden_partname_text.id = "submitModal_partName";
                   hidden_partname_text.hidden = true;
                   submitFilesDiv.appendChild(hidden_partname_text);
                   let hidden_numberid_text = document.createElement("p");
                   hidden_numberid_text.innerText = number_id;
                   hidden_numberid_text.id = "submitModal_numberID";
                   hidden_numberid_text.hidden = true;
                   submitFilesDiv.appendChild(hidden_numberid_text);
                   for(let fileIndex = 0; fileIndex < ajaxResponse.length; fileIndex++) {
                       let sub_file_name = ajaxResponse[fileIndex];
                       let div = document.createElement("div");
                       div.classList.add("mb-2")
                       let file_input_label = document.createElement("label");
                       file_input_label.classList.add("form-label");
                       file_input_label.innerHTML = "File to submit: <b>" + sub_file_name + "</b>";
                       let file_input = document.createElement("input");
                       file_input.setAttribute("type", "file");
                       file_input.classList.add("form-control", "mt-1", "fileSubmissionInput");
                       file_input.addEventListener("change", checkFileName);
                       let hidden_filename_text = document.createElement("p");
                       hidden_filename_text.innerText = sub_file_name;
                       hidden_filename_text.hidden = true;
                       file_input_label.appendChild(hidden_filename_text);
                       file_input_label.appendChild(file_input);
                       let inputValidationDiv = document.createElement("div");
                       inputValidationDiv.classList.add("invalid-feedback");
                       inputValidationDiv.innerText = "You must submit a file named '" + sub_file_name + "'!";
                       file_input_label.appendChild(inputValidationDiv);
                       div.appendChild(file_input_label);
                       submitFilesDiv.appendChild(div);
                   }
                   bootstrap.Modal.getInstance(document.getElementById("newSubmissionBox")).show();

                   let finalButton = document.getElementById("newSubmission_finalButton");
                   finalButton.classList.replace("btn-success", "btn-outline-success");
                   finalButton.disabled = true;
               }
            }
        };
        ajaxQuery.open("POST", "submit_backend.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("action=retrieve_sub_files_for_part" + "&assignment=" + assignmentName + "&part=" + partName);
    });
}

function checkFileName() {
    let given_file = this.files[0];
    if(given_file !== undefined) {
        given_file_name = given_file["name"];
        let true_file_name = this.previousSibling.innerText;
        if(true_file_name !== given_file_name) {
            this.classList.remove("is-valid");
            this.classList.add("is-invalid");
        } else {
            this.classList.remove("is-invalid");
            this.classList.add("is-valid");
        }
        let file_submission_inputs = document.getElementsByClassName("fileSubmissionInput");
        let goodToGo = true;
        for(let index = 0; index < file_submission_inputs.length; index++) {
            if(!file_submission_inputs[index].classList.contains("is-valid")) {
                goodToGo = false;
            }
        }
        let finalButton = document.getElementById("newSubmission_finalButton");
        if(goodToGo === true) {
            finalButton.classList.replace("btn-outline-success", "btn-success");
            finalButton.disabled = false;
        } else {
            finalButton.classList.replace("btn-success", "btn-outline-success");
            finalButton.disabled = true;
        }
    } else {
        this.classList.remove("is-valid");
        this.classList.add("is-invalid");

        let finalButton = document.getElementById("newSubmission_finalButton");
        finalButton.classList.replace("btn-success", "btn-outline-success");
        finalButton.disabled = true;
    }
}

document.getElementById("newSubmission_finalButton").addEventListener("click", function(event) {
    let assignmentName = document.getElementById("assignmentTitle").innerText;
    let partName = document.getElementById("submitModal_partName").innerText;
    let file_submission_inputs = document.getElementsByClassName("fileSubmissionInput");
    let goodToGo = true;
    for(let index = 0; index < file_submission_inputs.length; index++) {
        if(!file_submission_inputs[index].classList.contains("is-valid")) {
            goodToGo = false;
        }
    }
    if(goodToGo) {
        let submissionData = new FormData();
        for(let index = 0; index < file_submission_inputs.length; index++) {
            let true_file_name = file_submission_inputs[index].previousSibling.innerText;
            submissionData.append(true_file_name, file_submission_inputs[index].files[0]);
        }
        submissionData.append("assignment_name", assignmentName);
        submissionData.append("part_name", partName);
        submissionData.append("action", "submit_part");

        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let ajaxResponse = null;
                try {
                    ajaxResponse = JSON.parse(this.responseText);
                } catch (error) {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.set('notifier', 'delay', 0);
                    alertify.error("Could not decode JSON Response: " + this.responseText);
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

                        let number_id = document.getElementById("submitModal_numberID").innerText;
                        let updated_card = document.getElementById("card_part_" + number_id);
                        let status_elem = updated_card.children[4];
                        status_elem.innerText = ajaxResponse[1];
                        status_elem.classList.replace("bg-danger", "bg-success");
                        let view_test_button = updated_card.children[6];
                        view_test_button.classList.remove("disabled");

                        bootstrap.Modal.getInstance(document.getElementById("newSubmissionBox")).hide();
                    }
                }
            }
        };
        ajaxQuery.open("POST", "submit_backend.php", true);
        ajaxQuery.send(submissionData);
    }
});

