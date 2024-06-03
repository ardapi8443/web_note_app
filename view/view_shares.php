<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>View Shares</title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
        <script src="lib/jquery-3.7.1.min.js" type="text/javascript"></script>

        <link href="css/styles.css" rel="stylesheet" type="text/css">
        <style>
            #header {
                display: flex;
                align-items: center;
                flex-direction: row;
            }
        </style>


        <script>



            $(document).ready(function() {

                sort_options();

                $("#addShare").submit(function (event) {
                    event.preventDefault();
                    $.ajax({
                        url: "share/add_perm_JS/",
                        type: "POST",
                        dataType: "json",
                        data: {
                            userId: $("#selectedUser").val(),
                            perm: $("#selectedPerm").val(),
                            noteId : "<?php echo $note->get_id(); ?>"
                        },
                        success: function(response) {
                            console.log("great success");
                            add_perm(response);
                            sort_options();
                        },
                        error: function(xhr, status, error) {
                            console.error("great error");
                            console.error(error);
                            console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                        }
                    });
                });
                $(document).on('click', '.deleteShare', function (event) {
                    event.preventDefault();

                    var actionAttributeValue = $(this).attr('action');
                    var parts = actionAttributeValue.split('/');

                    $.ajax({
                        url: "share/del_perm_JS/",
                        type: "POST",
                        dataType: "json",
                        data: {
                            userId: parts[parts.length - 1],
                            noteId : parts[parts.length - 2]
                        },
                        success: function(response) {
                            console.log("great success");
                            del_perm(response, parts[parts.length - 1]);
                            sort_options();
                        },
                        error: function(xhr, status, error) {
                            console.error("great error");
                            console.error(error);
                            console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                        }
                    });
                });
                $(document).on('click', '.switchShare', function (event) {
                    event.preventDefault();

                    var actionAttributeValue = $(this).attr('action');
                    var parts = actionAttributeValue.split('/');

                    $.ajax({
                        url: "share/switch_perm_JS/",
                        type: "POST",
                        dataType: "json",
                        data: {
                            userId: parts[parts.length - 1],
                            noteId : parts[parts.length - 2]
                        },
                        success: function(response) {
                            console.log("great success");
                            del_perm(response, parts[parts.length - 1]);
                            add_perm(response);
                        },
                        error: function(xhr, status, error) {
                            console.error("great error");
                            console.error(error);
                            console.error("AJAX request failed with status code: " + status + " (" + xhr.statusText + ")");
                        }
                    });
                });
            });
            function del_perm(response, userId){

                var user_name = response.userName;
                var note_id = response.noteId;

                $("#" + userId).remove();

                if($('#idUL').is(':empty')){
                    $("#notShareID").css("display", "block");
                    $('#idUL:first').remove();
                }
                
                if ($('#addShare').length === 0) {
                    add_share(note_id)
                }

                var optionHTML = '<option id="' + userId + '" value="' + userId + '">' + user_name + '</option>';

                $("#selectedUser").append(optionHTML);


            }

            function add_perm(response){

                var note_id = response.noteId;
                var user_name = response.userName;
                var user_id = response.userId;
                var permission = response.perm;

                if($("#notShareID").length){
                    $("#notShareID").css("display", "none");
                    $("#shareList").append('<ul id="idUL" class="list-group">')
                }

                var liHTML = '<li id="' + user_id + '" class="form-control bg-dark text-white mb-3 d-flex p-2">'
                                + '<div class="container d-inline-flex justify-content-end">'
                                        + '<div class="flex-grow-1">'
                                            + '<span>' + user_name + (permission ? "(editor) " : "(reader) ") + '</span>'
                                        + '</div>'
                                        + '<div>'
                                            + '<form class="switchShare" action="share/switch/' + note_id + '/' + user_id + '">'
                                                + '<button type="submit">'
                                                    + ' <i class="bi bi-joystick"></i>'
                                                + '</button>'
                                            + '</form>'
                                        + '</div>'
                                        + '<div>'
                                            + '<form class="deleteShare" action="share/delete/' + note_id + '/' + user_id + '">'
                                                + '<button type="submit">'
                                                    + '<i class="bi bi-patch-minus"></i>'
                                                + '</button>'
                                            + '</form>'
                                        + '</div>'
                                + '</div>'
                            + '</li>';
                $('#idUL').append(liHTML);

                $('#selectedUser option[value="' + user_id + '"]').remove();


                if ($("#selectedUser option").length === 1) {
                    $("#addShare").remove();
                }

                var listItems = $('#idUL li');
                listItems.sort(function(a, b) {
                    var textA = $(a).find('span').text().toUpperCase();
                    var textB = $(b).find('span').text().toUpperCase();
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                });
                $('#idUL').html(listItems);
            }

            function add_share(noteId){
                var shareHTML = '<form id="addShare" class="d-flex p-2" action="share/index/' + noteId + '" method="post" >'
                                + '<select id="selectedUser" class="form-select" name="userId" aria-label="Default select example" >'
                                + '<option>-User-</option>'
                                + '</select>'
                                + '<select id="selectedPerm" class="form-select" name="permission" aria-label="Default select example" >'
                                + '<option selected>-Permission-</option>'
                                + '<option value="0">Reader</option>'
                                + '<option value="1">Editor</option>'
                                + '</select>'
                                + '<button type="submit" class="btn btn-link">'
                                + '<i class="bi bi-clipboard-plus">'
                                + '</i>'
                                + '</button>'
                                + '</form>';
                $('#addShareFrom').append(shareHTML);
            }

            function sort_options() {
                var select = $('#selectedUser');
                var options = select.find('option:not(:first-child)').toArray();

                options.sort(function(a, b) {
                    return a.text.localeCompare(b.text);
                });

                // Clear existing options except the first one
                select.find('option:not(:first-child)').remove();

                // Append sorted options
                $.each(options, function(index, option) {
                    select.append(option);
                });
            }

        </script>
    </head>
    <body class="bg-dark text-white">
        <div class="title">
            <div id="edit_profile_container" >
                <div id ="header">
<!--                     <?php
                        echo " <a href=\"note/open/".$note->get_id() ."\"> <i class=\"bi bi-arrow-90deg-left\"></i> </a>";
                    ?> -->

                    <?php
                        echo "<form action='note/show_note' method='post'>
                        <input type='hidden' name='param1' value=" . $note->get_id() . " >
                        <input type='hidden' name='param2' value=' $encoded_label ' > 
                        <button type='submit' class='bi bi-arrow-90deg-left'> </button>
                    </form>"
                    ?>

                </div>
                <div>
                    <span id="shareList"> Shares : </span>
                        <?php
                            if(!$note -> is_shared()){
                                echo "<span id='notShareID'> this note is not shared yet </span>";
                            } else {?>
                                <div>
                                    <ul id="idUL" class="list-group">

                                       <?php

                                        foreach($note_share as $ns){
                                            echo "<li id=".$ns -> get_full_user_by_id() -> get_id()." class=\"form-control bg-dark text-white mb-3 d-flex p-2\"> 
                                                <div class='container d-inline-flex justify-content-end'>
                                                     <div class='flex-grow-1'>                                    
                                                        <span>".$ns -> get_full_user_by_id() -> get_full_name().($ns -> get_editor() == 1 ? "(editor)" : "(reader)")."</span>      
                                                     </div>
                                                     <div>    
                                                        <form class=\"switchShare\"  action=\"share/switch/" . $note->get_id() . '/' . $ns->get_full_user_by_id()->get_id() . '/' . ($encoded_label ? $encoded_label : '') . "\">
                                                            <button type=\"submit\">
                                                                 <i class=\"bi bi-joystick\"></i>                                                   
                                                            </button>              
                                                        </form>
                                                        </div>
                                                        <div>   
                                                        <form class=\"deleteShare\" action=\"share/delete/" . $note->get_id() . '/' . $ns->get_full_user_by_id()->get_id() . '/' . ($encoded_label ? $encoded_label : '') . "\">
                                                            <button type=\"submit\">                                       
                                                                <i class=\"bi bi-patch-minus\"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>    
                                                </li>";
                                        }
                                       ?>
                                    </ul>
                                </div>
                            <?php }?>
                </div>
                <div id="addShareFrom">
                <?php if($user_not_share  !== false) : ?>
                <form id="addShare" class="d-flex p-2" action="share/index/<?= $note->get_id() . '/' . ($encoded_label ? $encoded_label : '')  ?>" method="post" >
                    <select id="selectedUser" class="form-select" name="userId" aria-label="Default select example" >
                        <option>-User-</option>
                        <?php
                            foreach ($user_not_share as $user){
                                echo "<option id=\"{$user -> get_id()}\" value=\"{$user -> get_id()}\">{$user -> get_full_name()}</option>";
                            }
                        ?>
                    </select>

                    <select id="selectedPerm" class="form-select" name="permission" aria-label="Default select example" >
                        <option selected>-Permission-</option>
                        <option value="0">Reader</option>
                        <option value="1">Editor</option>
                    </select>
                    <button type="submit" class="btn btn-link"><i class="bi bi-clipboard-plus"></i></button>
                </form>
                <?php endif;?>
                </div>
            </div>
        </div>
    </body>
</html>

