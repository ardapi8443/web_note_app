<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist Note</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <script src="lib/jquery-3.7.1.min.js"></script>
    <script src="scripts/back.js"></script>
    <style>
        .error-list {
            display: none;
        }
    </style>

    <script>
        let title, err_title, err_toggled;
        const minItem = <?= Configuration::get("item_min_length") ?>;
        const maxItem = <?= Configuration::get("item_max_length") ?>;
        const minTitle = <?= Configuration::get("title_min_length") ?>;
        const maxTitle = <?= Configuration::get("title_max_length") ?>;
        let saveButton = "";
        let item_valid = true;
        let title_valid = true;

        function check_title() {
            title_valid = true;
            var count = title.val().length;

            if (count < minTitle || count > maxTitle) {
                title.addClass("is-invalid");
                err_title.text("Between "+ minTitle +" and "+ maxTitle +" characters please");
                title_valid = false;
            } else {
                title.removeClass("is-invalid");
                err_title.text("");
            }

            $("#ulTitle").toggle(err_title.text().trim() !== "");

            if(title_valid){
                $.ajax({
                    url: "note/check_duplicate_title_note_JS/",
                    type: "POST",
                    dataType: "json",
                    data: {
                        title: title.val(),
                        note_id: "<?= $note->get_id() ?>"
                    },
                    success: function(response) {
                        console.log("great title success");
                        if (response) {
                            title.addClass("is-invalid");
                            err_title.text("title must be unique among your notes");
                            title_valid = false;

                        } else {
                            title.removeClass("is-invalid");
                            if (err_title.text().trim() === "") {
                                err_title.text("");
                            }

                        }
                        $("#ulTitle").toggle(err_title.text().trim() !== "");

                        serial_enable_save();

                    },
                    error: function(xhr, status, error, response) {
                        console.error("great title error");
                        console.error(error);
                        console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                        console.log(response);
                    }
                });
            } else {
                serial_enable_save();
            }
        }

        function check_items() {
            var enableAddItem = true;
            item_valid = true;

            if ($("#addItemInput").val().trim() === "") {
                $("#addItemInput").removeClass("itemSelector").removeClass("is-invalid").removeClass("is-valid");
                enableAddItem = false;
            } else {
                $("#addItemInput").addClass("itemSelector")
            }


            $(".itemSelector").each(function() {

                var tempVal = $(this).val();
                var tempId = $(this).attr('id');
                var isValid = true;

                $(".itemSelector").each(function() {

                    if (tempVal === $(this).val() && tempId !== $(this).attr('id')) {

                        $("#" + tempId).removeClass("is-valid").addClass("is-invalid");
                        $(this).addClass("is-invalid");

                        $("#err" + tempId).text("must be unique");
                        isValid = false;
                        item_valid = false;

                        if ($(this).attr('id') === "addItemInput") {
                            enableAddItem = false;
                        }
                    }
                });

                if ($(this).val().trim().length < minItem || $(this).val().trim().length > maxItem) {
                    $(this).addClass("is-invalid");
                    $("#err" + tempId).text("must be between "+ minItem +" and "+ maxItem+" characters");
                    isValid = false;
                    item_valid = false;
                    enableAddItem = false;
                }

                if (isValid) {
                    $("#" + tempId).removeClass("is-invalid").addClass("is-valid");
                    $("#err" + tempId).text("");
                    $("#ul" + tempId).addClass("error-list");
                } else {
                    $("#ul" + tempId).removeClass("error-list");
                }

            });

            $("#addItemButton").prop("disabled", !enableAddItem);
            serial_enable_save();
        }

        function serial_enable_save(){
            if(title_valid && item_valid){
                $("#saveButton").prop("disabled",false);
            } else {
                $("#saveButton").prop("disabled",true);
            }
        }

        $(document).ready(function() {

            $("#editChecklist").on("click", "button[type='submit']", function() {
                $("#buttonClicked").val($(this).attr("id"));
            });

            $("#editChecklist").submit(function(event) {
                event.preventDefault();
                var buttonClicked = $("#buttonClicked").val();
                var inputValue = $("#addItemInput").val().trim();

                if (buttonClicked === "addItemButton" && inputValue !== "") {
                    var contentClicked = $("#addItemInput").val();

                    $.ajax({
                        url: "note/add_item_JS/",
                        type: "POST",
                        dataType: "json",
                        data: {
                            note_id: "<?= $note->get_id() ?>",
                            new_item: contentClicked,
                        },
                        success: function(response) {
                            console.log("great add success");
                            $('#addItemInput').val('');
                            $("#addItemButton").prop("disabled", true);
                            $("#addItemInput").removeClass("itemSelector").removeClass("is-invalid").removeClass("is-valid");
                            update_item(response, true);
                            update_edited();
                        },
                        error: function(xhr, status, error) {
                            console.error("great add error");
                            console.error(error);
                            console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                        }
                    });

                } else if (buttonClicked == saveButton || saveButton == buttonClicked+"save") {

                    var idValuePairs = {};
                    var title_value = $("#title").val();

                    $('#listItems').find('div').each(function() {
                        var id = $(this).attr('id').replace('div_', '');
                        var value = $(this).find('input[type="text"]').val();
                        idValuePairs[id] = value;
                    });

                    $.ajax({
                        url: "note/save_all_JS/",
                        type: "POST",
                        dataType: "json",
                        data: {
                            note_id: "<?= $note->get_id() ?>",
                            list_item: idValuePairs,
                            title: title_value,
                        },
                        success: function(response) {
                            console.log("great save success");
                            update_edited_save_all(response.date_interval_edit);

                            var currentUrl = window.location.href;
                            var newUrl = currentUrl.replace("/edit/", "/open/");
                            window.location.href = newUrl;

                        },
                        error: function(xhr, status, error) {
                            console.error("great save error");
                            console.error(error);
                            console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                        }
                    });

                } else {

                    $.ajax({
                        url: "note/del_item_JS/",
                        type: "POST",
                        dataType: "json",
                        data: {
                            note_id: "<?= $note->get_id() ?>",
                            item_id: buttonClicked
                        },
                        success: function(response) {
                            console.log("great del success");
                            update_item(response, false);
                            update_edited();
                        },
                        error: function(xhr, status, error) {
                            console.error("great del error");
                            console.error(error);
                            console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                        }
                    });

                    $("#div_" + buttonClicked).remove();
                    $("#ulitem" + buttonClicked).remove();

                }


            });
        });

        function update_item(response, add) {

            var item_id = response.id;
            var is_checked = response.checked ? 'checked' : '';
            var content = response.content


            if (add) {
                saveButton = "addItemButtonsave";

                var divHtml = '<div id="div_' + item_id + '" class="input-group mb-2">' +
                    '<span class="input-group-text bg-dark text-white">' +
                    '<input class="form-check-input mt-0" type="checkbox" ' + is_checked + ' disabled>' +
                    '</span>' +
                    '<input id="item' + item_id + '" type="text" class="itemSelector form-control bg-dark text-white" name="items[' + item_id + ']" value="' + content + '" />' +
                    '<span class="input-group-text bg-dark text-white">' +
                    '<button id="' + item_id + '" class="btn btn-danger" type="submit" name="deleteItem" value="' + item_id + '">-</button>' +
                    '</span>' +
                    '</div>';

                var ulHtml = '<ul id="ulitem' + item_id + '" class="error-list"> <li id="erritem' + item_id + '" class="errToggled"></li></ul>';


                $('#listItems').append(divHtml);
                $('#listItems').append(ulHtml);

            } else {
                saveButton = item_id;
                $("div_" + item_id).remove();

            }

            check_items();
        }

        function update_edited() {
            var text = $("#time").text();
            var beforeEdited = text.split("Edited")[0]
            var newText = beforeEdited + "Edited just now.";
            $("#time").text(newText);
        }

        function update_edited_save_all(date_interval) {
            var text = $("#time").text();
            var beforeEdited = text.split("Edited")[0]
            var newText = beforeEdited + "Edited " + date_interval;
            $("#time").text(newText);
        }

        $(function() {

            title = $("#title");
            err_title = $("#errTitle");
            title.bind("input", check_title);
            $("#ulTitle").toggle(err_title.text().trim() !== "");
            $(".itemSelector").bind("input", check_items);
            $(".errToggled").css("color", "red");
            $("#addItemInput").bind("input", check_items);

        });
    </script>

</head>


<body class="bg-dark">
<?php require_once "modal_back.php";?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form name='back' id="back" action="note/open/<?= $note->get_id() . '/' . ($encoded_label == true ? $encoded_label : '') ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="get back">
            <button data-bs-toggle='tooltip' data-bs-placement='right' title='back to open'>
                <i class="bi bi-arrow-90deg-left"></i>
            </button>
        </form>
        <button type="submit" form="editChecklist" class="btn btn-outline-light p-0 border-0 bg-transparent" id="saveButton">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16">
                <path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z" />
            </svg>
        </button>
    </div>
    <div class="container py-4 bg-dark">
        <div class="card bg-dark text-white">
            <div class="card-body">
                <p id="time" class="text">Created <?= $date_interval_creation ?> Edited <?= $date_interval_edit ?></p>
                <h3 class="card-subtitle mb-2 text">Title</h3>
                <form id="editChecklist" method="post" action="note/edit_checklist_note/<?= $note->get_id() . '/' . ($encoded_label == true ? $encoded_label : '') ?> ">
                    <input type="hidden" id="actionType" value="">
                    <div class="mb-3">

                        <input type="text" id="title" name="title" class="form-control <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($title_error)) ? 'is-invalid' : ''; ?>" value="<?= $note->get_title() ?>">
                        <ul id="ulTitle" style="display:none">
                            <li id="errTitle" class="errToggled"></li>
                        </ul> 
                        <?php
                        if (!empty($title_error)) {
                            echo "
                                <div class=\"invalid-feedback\">
                                    <ul>
                                        <li>
                                            {$title_error}
                                        </li>
                                    </ul>
                                </div>";
                        }
                        ?>
                    </div>

                    <h3 class="card-subtitle mb-2 text">Items</h3>
                    <div class="card bg-dark text-white mb-2">
                        <div id="listItems" class="card-body">
                            <?php foreach ($note->get_checklist_items_by_creation() as $item) : ?>
                                <div id="div_<?php echo $item->get_id() ?>" class="input-group mb-2">


                                    <span class="input-group-text bg-dark text-white">
                                        <input class="form-check-input mt-0" type="checkbox" <?= $item->is_checked() ? 'checked' : '' ?> disabled>
                                    </span>

                                    <input id="item<?php echo $item->get_id() ?>" type="text" class="itemSelector form-control bg-dark text-white" name="items[<?= $item->get_id() ?>]" value="<?= $item->get_content() ?>">

                                    <span class="input-group-text bg-dark text-white">
                                        <button id="<?= $item->get_id() ?>" class="btn btn-danger del" type="submit" name="deleteItem" value="<?= $item->get_id() ?>">-</button>
                                    </span>

                                </div>
                                <ul id="ulitem<?php echo $item->get_id() ?>" class="error-list">
                                    <li id="erritem<?php echo $item->get_id() ?>" class="errToggled"></li>
                                </ul>
                                <?php

                                if (!empty($error_edit_item)) {
                                    foreach ($error_edit_item as $error) {
                                        if (!empty($error)) {
                                            $error_id = substr($error, 0, strpos($error, 'L'));
                                            $error_message = substr($error, strpos($error, 'L'));
                                            if ($error_id == $item->get_id()) {
                                                echo "<div class=\"invalid-feedback\" style=\"display: block\">";
                                                echo "<ul>";
                                                echo "<li>{$error_message}</li>";
                                                echo "</ul>";
                                                echo "</div>";
                                            }
                                        }
                                    }
                                }
                                ?>

                            <?php endforeach; ?>
                        </div>
                    </div>

                    <h4 class="text-white">New Item</h4>
                    <div class="input-group">
                        <input id="addItemInput" type="text" class=" form-control bg-dark text-white me-2 <?= ($new_item !== '' && isset($new_item)) ? 'is-invalid' : '' ?>" placeholder="" name="newItem" value="<?= isset($new_item) ? $new_item : '' ?>">
                        <button id="addItemButton" class="btn btn-primary" type="submit" name="addItem">+</button>
                    </div>
                    <ul id="uladdItem" class="error-list">
                        <li id="erraddItem" class="errToggled"></li>
                    </ul>
                    <input type="hidden" id="buttonClicked" name="buttonClicked">
                    <?php
                    if (!empty($error_add_item)) {
                        echo "
                                <div class=\"invalid-feedback\" style=\"display: block\">
                                    <ul>
                                        <li>
                                            {$error_add_item}
                                        </li>
                                    </ul>
                                </div>";
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
</body>

</html>