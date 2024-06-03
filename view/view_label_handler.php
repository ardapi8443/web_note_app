<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> <?=$note->get_title()?> </title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="lib/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="scripts/back.js"></script>
    <script src="scripts/validation_text_note.js" type="text/javascript"></script>

    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        li::marker {
            content : '';
        }
    </style>

    <script>
        var disable_add_button = true;
        const min_length= <?= Configuration::get("label_min_length") ?>;
        const max_length = <?= Configuration::get("label_max_length") ?>;
        let error_space = "The string contains a space character.";
        let error_duplicate = "A note cannot contain the same label twice";
        let error_length = "Label length must be between " + min_length + " and " + max_length + ".";

        function check_add_label() {

            var add_label_content = $("#addLabelInput").val();

            if(add_label_content.length === 0){
                disable_add_button = true;
                $('#error_js').empty();
            } else if (add_label_content.indexOf(' ') !== -1){
                disable_add_button = true;
                add_error(error_space);
            } else if(add_label_content.length < min_length || add_label_content.length > max_length){
                disable_add_button = true;
                add_error(error_length);
            } else if (check_duplicate(add_label_content)) {
                disable_add_button = true;
                add_error(error_duplicate);
            } else {
                disable_add_button = false;
                $('#error_js').empty();
            }

            button_disable();
        }

        function add_error(error){

            $('#error_js').empty();

            var divHtml = '<div class=\"invalid-feedback\" style=\"display: block\">' +
                                '<ul>' +
                                    '<li>' +
                                        error +
                                    '</li>' +
                                '</ul>' +
                            '</div';

            $('#error_js').append(divHtml);
        }


        function check_duplicate(add_label_content){

            var res = false;

            $("#list_label span").each(function(){

                var spanText = $(this).text().trim();
                if (spanText === add_label_content){
                    res = true;
                }
                return false;
            })
            return res;
        }

        function button_disable(){
            document.getElementById("addLabelButton").disabled = disable_add_button;
        }

        function reset_add_input(){
            $("#addLabelInput").val('');
            disable_add_button = true;
            button_disable();
        }

        function sort_response(response){
            response.sort((a, b) => {
                if (a.label < b.label) {
                    return -1;
                } else if (a.label > b.label) {
                    return 1;
                } else {
                    return 0;
                }
            });
        }

        function add_list_label(){
            $('#no_label').remove();
            var divHtml = '<ul id=\"list_label\"></ul>';
            $('#labels').append(divHtml);
        }

        function  add_no_label_span(){
            $('#list_label').remove();
            var divHtml = '<span id=\"no_label\">This note does not have yet a label</span>';
            $('#labels').append(divHtml);
        }

        function redo_li(response){
            sort_response(response);

            var listLabelElement = document.getElementById("list_label");

            if (listLabelElement === null) {
                add_list_label();
            }
            if(response.length === 0){
                add_no_label_span();
            }


            $('#list_label').empty();

            for(var i = 0; i < response.length ; i++){

                var divHtml = ' <li class=\"form-control bg-dark text-white mb-3 d-flex p-2\">' +
                                    '<span>' +
                                        response[i].label +
                                    '</span>' +
                                    '<form class=\"delete_label\" action=\"label\\del_label\" method=\"post\">' +
                                        '<input type=\"hidden\" name=\"del_label\" value=' + response[i].label + '>' +
                                        '<input type=\"hidden\" name=\"note_id\" value=' + response[i].note + '>' +
                                        '<button type =\"submit\">' +
                                            '<li class=\"bi bi-patch-minus\">' +
                                            '</li>' +
                                        '</button>' +
                                    '</form>' +
                                '</li>';


                $('#list_label').append(divHtml);
            }
        }

        function redo_datalist(response){
            sort_response(response);

            $('#other_labels').empty();

            for(var i = 0; i < response.length ; i++){
                var divHtml = '<option value=' + response[i].label + '></option>';
                $('#other_labels').append(divHtml);
            }


        }

        function recalculate_datalist(){
            $.ajax({
                url: "label/get_other_labels_JS/",
                type: "POST",
                dataType: "json",
                data: {
                    note_id: "<?= $note->get_id() ?>",
                },
                success: function(response) {
                    console.log("great datalist success");
                    redo_datalist(response);

                },
                error: function(xhr, status, error) {
                    console.error("great add error");
                    console.error(error);
                    console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                }
            });
        }

        function add_label(event){
            event.preventDefault();
            var add_label_content = $("#addLabelInput").val();


            $.ajax({
                url: "label/add_label_JS/",
                type: "POST",
                dataType: "json",
                data: {
                    note_id: "<?= $note->get_id() ?>",
                    new_label: add_label_content,
                },
                success: function(response) {
                    console.log("great add success");
                    reset_add_input();
                    redo_li(response);
                    recalculate_datalist();

                },
                error: function(xhr, status, error) {
                    console.error("great add error");
                    console.error(error);
                    console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                }
            });

        }

        function del_label(event){
            event.preventDefault();

            var form = event.target.closest('form');
            var del_label_content = form.querySelector('input[name="del_label"]').value;
            console.log("prout");

            $.ajax({
                url: "label/del_label_JS/",
                type: "POST",
                dataType: "json",
                data: {
                    note_id: "<?= $note->get_id() ?>",
                    old_label: del_label_content,
                },
                success: function(response) {
                    console.log("great add success");
                    redo_li(response);
                    recalculate_datalist();

                },
                error: function(xhr, status, error) {
                    console.error("great add error");
                    console.error(error);
                    console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                }
            });

        }

        $(function() {

            button_disable();
            $("#addLabelInput").bind("input", check_add_label);
            $("#addLabelButton").click(function (event) {
                add_label(event);
            });
            $(".del_label").click(function (event) {
                del_label(event);
            });
        });


    </script>
</head>

<body class="bg-dark text-white">
    <div class="col-4 text-center">
            <form name='back' id="back" action="note/show_note" method="post" data-bs-toggle="tooltip" data-bs-placement="right" title="get back">
            <input type="hidden" name="param1" value="<?php echo $note->get_id() ?>">
            <input type="hidden" name="param2" value="<?php echo $encoded_label ?>">
                <button data-bs-toggle='tooltip' data-bs-placement='right' title='back to the moon'>
                    <i class="bi bi-arrow-90deg-left"></i>
                </button>
            </form>
    </div>


    <div id="labels" class="col-4 text-center">
        <span> Labels : </span>
        <?php
        if(count($current_labels) == 0){
            echo "<span id='no_label'>This note does not have yet a label</span>";
        } else {?>
            <div>
                <ul id="list_label">
                    <?php
                    foreach($current_labels as $label){?>
                        <li class="form-control bg-dark text-white mb-3 d-flex p-2">
                            <span>
                                <?php echo $label; ?>
                            </span>

                            <form class="del_label" class="delete_label"  action="label/del_label/<?= ($encoded_label == true ? $encoded_label : '') ?>"  method="post">
                                <input type="hidden" name="del_label" value="<?php echo $label ?>">
                                <input type="hidden" name="note_id" value="<?php echo $note -> get_id() ?>">

                                <button type="submit">
                                    <i class="bi bi-patch-minus"></i>
                                </button>
                            </form>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>

    <div class="col-4 text-center">
        <h4 class="text-white">Add new label</h4>
        <form id="addShare" class="d-flex p-2" action="label/add_label/<?= $note->get_id() . '/' . ($encoded_label == true ? $encoded_label : '') ?>" method="post" >
            <div class="input-group">
                <input list="other_labels" id="addLabelInput" type="text" class=" form-control bg-dark text-white me-2 <?= empty($errors) ? '' : 'is-invalid' ?>" placeholder="Type to search or create..." name="new_label" value="">
                <input type="hidden" name="note_id" value="<?= $note->get_id() ?>">
                <button id="addLabelButton" class="btn btn-primary" type="submit" name="addLabel">+</button>
            </div>
        </form>
        <datalist id="other_labels">
            <?php
                if(!empty($other_labels)){
                    foreach($other_labels as $label){
                        echo "<option value={$label}></option>";
                    }
                }

            ?>

        </datalist>

        <div id ="error_js"></div>
        <?php
        if (!empty($errors)) {
            echo "
                                <div class=\"invalid-feedback\" style=\"display: block\">
                                    <ul>
                                        <li>
                                            {$errors}
                                        </li>
                                    </ul>
                                </div>";
        }
        ?>
    </div>
</body>