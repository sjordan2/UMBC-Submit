if(document.getElementById("partSelectionDropdown").value !== "") {
    loadViewTestPanelForPart();
}

function loadViewTestPanelForPart() {
    let url_params = new URLSearchParams(window.location.search);
    let assignment_name = url_params.get("assignment_name");
    let part_name = url_params.get("part_name");
    let ajaxQuery = new XMLHttpRequest();
    ajaxQuery.onreadystatechange = function () {
        let ajaxResponse = null;
        if (this.readyState === 4 && this.status === 200) {
            try {
                ajaxResponse = JSON.parse(this.responseText);
            } catch (error) { // The backend returned a single error
                alertify.set('notifier', 'position', 'top-center');
                alertify.set('notifier', 'delay', 0);
                alertify.error("Could not decode JSON Response: " + this.responseText);
            }
            if(ajaxResponse !== null) {
                let tab_root = document.getElementById("submittedFileTabs");
                let sub_file_names_list = ajaxResponse[0];
                for(let index = 0; index < sub_file_names_list.length; index++) {
                    let new_list = document.createElement("li");
                    new_list.classList.add("nav-item");
                    let new_button = document.createElement("button");
                    new_button.classList.add("nav-link", "tabButton");
                    if(index === 0) {
                        new_button.classList.add("active");
                        new_button.classList.add("disabled");
                    }
                    new_button.innerText = sub_file_names_list[index];
                    new_button.addEventListener("click", loadNewFile);
                    new_list.appendChild(new_button);
                    tab_root.appendChild(new_list);
                }
                console.log(ajaxResponse[1]);
                if(ajaxResponse[1] === null) {
                    let p_element = document.createElement("p");
                    p_element.classList.add("text-danger", "fw-bold", "text-center");
                    p_element.innerText = "No file submitted for this part yet!";
                    document.getElementById("selectedFileText").appendChild(p_element);
                } else {
                    let preContent = document.createElement("pre");
                    let codeContent = document.createElement("code");
                    // codeContent.classList.add("language-python");
                    codeContent.setAttribute("data-language", "python");
                    codeContent.innerText = ajaxResponse[1];
                    // hljs.highlightElement(codeContent);
                    preContent.appendChild(codeContent);
                    document.getElementById("selectedFileText").appendChild(preContent);
                }
            }
        }
    };
    ajaxQuery.open("POST", "view_test_backend.php", true);
    ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxQuery.send("action=load_panel_for_part&assignment=" + assignment_name + "&part=" + part_name);
}

document.getElementById("partSelectionDropdown").addEventListener("change", updateURL);

function updateURL() {
    let part_name = document.getElementById("partSelectionDropdown").value;
    let currURL = new URL(window.location.href);
    currURL.searchParams.set("part_name", encodeURIComponent(part_name));
    location.href = currURL.href.replace(new RegExp("\\+", "g"), "%20");
}

function loadNewFile() {
    let url_params = new URLSearchParams(window.location.search);
    let assignment_name = url_params.get("assignment_name");
    let part_name = url_params.get("part_name");
    let file_to_load = this.innerText;
    resetTabSelections();
    this.classList.add("active", "disabled");
    let ajaxQuery = new XMLHttpRequest();
    ajaxQuery.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("selectedFileText").innerHTML = "";
            console.log(this.responseText);
            console.log("hi");
            if(this.responseText === "") {
                let p_element = document.createElement("p");
                p_element.classList.add("text-danger", "fw-bold", "text-center");
                p_element.innerText = "No file submitted for this part yet!";
                document.getElementById("selectedFileText").appendChild(p_element);
            } else {
                let preContent = document.createElement("pre");
                let codeContent = document.createElement("code");
                // codeContent.classList.add("language-python");
                codeContent.setAttribute("data-language", "python");
                codeContent.innerText = this.responseText;
                // hljs.highlightElement(codeContent);
                preContent.appendChild(codeContent);
                document.getElementById("selectedFileText").appendChild(preContent);
            }
        }
    };
    ajaxQuery.open("POST", "view_test_backend.php", true);
    ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxQuery.send("action=load_file&assignment=" + assignment_name + "&part=" + part_name + "&file_to_load=" + file_to_load);
}

function resetTabSelections() {
    let tab_buttons = document.getElementsByClassName("tabButton");
    for(let index = 0; index < tab_buttons.length; index++) {
        tab_buttons[index].classList.remove("active", "disabled");
    }
}