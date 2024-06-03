<?php
require_once "view_note_factoring.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search my notes</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

         <!-- JQUERY UI -->
        <script src="lib/jquery-3.7.1.min.js" ></script>
        <script src="lib/jquery-ui.min.js" ></script>

        <script>

        let labels, my_notes, shared_notes;
        let count = 0;
        let decoded_label;
        let selectedLabels;

        $(document).ready(async function() {
            $("#submit").hide();
            $("#search input[type='checkbox']").prop("checked", false);

            labels = await $.get("label/get_labels_service/");
            labels = JSON.parse(labels).map(label => decodeHtml(label));
            selectedLabels = [];
            var url = location.href;
            let urlObj = new URL(url);
            let pathname = urlObj.pathname;
            var urldecode = pathname.split('/search/')[1];

            if (urldecode) {

            try {
                let response = await $.post("label/decoded_label_service/", {
                    param1: urldecode
                });

                response = JSON.parse(response);
                
                if (Array.isArray(response)) {
                    selectedLabels = [];
                
                    $("input[type='checkbox']").each(function() {
                        var label = $(this).attr('id');
                
                        if (response.includes(label)) {
                            $(this).prop("checked", true);
                            selectedLabels.push(label);
                        } else {
                            selectedLabels = selectedLabels.filter(l => l !== label);
                            $(this).prop("checked", false);
                        }
                    });
                    update_notes(selectedLabels);
                } else {
                    console.error("La réponse n'est pas un tableau :", response);
                }
            } catch (error) {
                console.error("Erreur lors de la récupération des étiquettes:", error);
            }
        }      

            $.each(labels, function(index, label) {
                var label_element = $("#" + label);
                label_element.on("click", function() {
                    if (label_element.is(":checked")) {
                        selectedLabels.push(label);
                    } else {
                        selectedLabels = selectedLabels.filter(l => l !== label);
                    }
                    update_notes(selectedLabels);
                });
            });
        });
        

            async function update_notes(label_check) {
                let my_notes_HTML = $("#my_notes");
                let title = document.querySelector("#your_notes");
                let shared_notes_HTML = $("#shared_notes");

                let response_my_notes = await $.post("label/search_my_notes_service/", {
                                labels: label_check
                            });
                let response_shared_notes = await $.post("label/get_shared_notes_by_labels_service/", {
                    labels: label_check
                });

                if(response_my_notes.trim().length > 0 && response_my_notes.trim() != "[]"){
                my_notes_HTML.empty();
                title.innerHTML = "<h3 style='color: white'>Your notes</h3>";

                let notes = JSON.parse(response_my_notes);
                // console.log(notes);
                await display_notes(my_notes_HTML, notes);
                
                } else {
                    my_notes_HTML.empty();
                    shared_notes_HTML.empty();
                    title.innerHTML = "<h3 class='text-danger'>No notes found !!</h3>";
                }

                
                // console.log(response_shared_notes);
               if (response_shared_notes.trim().length > 0 && response_shared_notes.trim() != "[]") {
                    title.innerHTML = "<h3 style='color: white'>Your notes</h3>";
                    let notes_shared = JSON.parse(response_shared_notes);
                    shared_notes_HTML.empty();

                    let notes_by_sharer = {};
                    let current_user_id = notes_shared[0];
                    let notes_for_current_user = [];

                    for (let index = 1; index < notes_shared.length; index++) {
                        if (typeof notes_shared[index] === 'number') {
                            // Store notes for the previous user
                            if (notes_for_current_user.length > 0) {
                                if (!notes_by_sharer[current_user_id]) {
                                    notes_by_sharer[current_user_id] = [];
                                }
                                notes_by_sharer[current_user_id].push(...notes_for_current_user);
                            }
                            // Start a new array for the next user
                            current_user_id = notes_shared[index];
                            notes_for_current_user = [];
                        } else {
                            // This is a note, so add it to the current user's array
                            notes_for_current_user.push(notes_shared[index]);
                        }
                    }

                    // Store notes for the last user
                    if (notes_for_current_user.length > 0) {
                        if (!notes_by_sharer[current_user_id]) {
                            notes_by_sharer[current_user_id] = [];
                        }
                        notes_by_sharer[current_user_id].push(...notes_for_current_user);
                    }

                    // Display the grouped notes
                    for (const [user_id, user_notes] of Object.entries(notes_by_sharer)) {
                        let sharer_name = await $.post("label/get_user_full_name_service/", { user_id: user_id });
                        if (sharer_name && sharer_name.trim() !== '') {
                            shared_notes_HTML.append("<div class='col-md-12'><h4>Shared by " + sharer_name + "</h4>");
                            await display_notes(shared_notes_HTML, user_notes);
                            shared_notes_HTML.append("</div>"); // Close the div for each sharer
                        }
                    }
                } else {
                    shared_notes_HTML.empty();
                }

            }

            async function display_notes(place, notes) {
                let encoced_label = await $.post("label/encode_label_service", { 
                        labels : selectedLabels
                    }); 

                $.each(notes, function(index, note) {
                
                    count = 0;
            
                    let html = "<div data-note-id='" + note.id + "' class='col-md-6 mb-3'><div class='card text-white bg-dark border-white' style='height: 275px;'><div class='card-body'>";
                    if(!note.items) {
                        html += "<form id='open_this' action='note/show_note' method='post'>" +
                            "<input type='hidden' name='param1' value=" + note.id + ">" +
                            "<input type='hidden' name='param2' value=" + encoced_label + ">" +
                            "<button type='submit' class='card-header font-weight-bold h4' style='color: #89CFF0; background-color: transparent; text-decoration: underline; border: none;'>" + note.title + "</button>" +
                        "</form>";

                        if (note.content) {
                            html += "</h5><p class='card-text custom-text-size'>" + note.content.substr(0, 100) + "</p>";
                        }
                    } else {
                        html += "<form id='open_this' action='note/show_note' method='post'>" +
                            "<input type='hidden' name='param1' value=" + note.id + ">" +
                            "<input type='hidden' name='param2' value=" + encoced_label + ">" +
                            "<button type='submit' class='card-header font-weight-bold h4' style='color: #89CFF0; background-color: transparent; text-decoration: underline; border: none;'>" + note.title + "</button>" +
                        "</form>";
                        html += "<p class='card-text custom-text-size'><ul class='list-unstyled'>";
                        $.each(note.items, function(index, item) {
                            if (count < 3) {
                                html += "<li><input type='checkbox' id=" + item.id + " name=" + item.id + " " + ((item.checked == 1) ? 'checked' : '') + " disabled>";
                                html += "<label for=" + item.id + ">" + item.content + "</label></li>";
                                ++count;
                            } else {
                                html += "<li> ... </li>";
                                return false;
                            }
                        }); 
                    }

                    html+= '<div class="d-flex flex-row align-items-center justify-content-center p-3">';
                        $.each(note.labels, function(index,  label) {
                            html+= "<button class='btn btn-primary m-1'>" + label + "</button>";
                        });
                        html+= '</div>';

                        html += "</ul></p></div></div>";
                    
                    place.append(html);
                    html = "";
                });
            }

            function decodeHtml(html) {
                var txt = document.createElement("textarea");
                txt.innerHTML = html;
                return txt.value;
            }
        </script>
</head>

<body class='bg-dark text-white'>
    <?= navbar($user);?>

    <div class="main container bg-dark">
        <h1 class="font-weight-bold mt-3 text-right ml-auto">Search my notes</h1>

        <div class="row">
            <div class="col-md-12">
                <h3>Search notes by tags :</h3>
                <form id="search" action="label/search_my_notes" method="post">
                    <?php
                        foreach ($labels as $label) {
                            echo "<input id='$label' type='checkbox' name='labels[]' value='$label' ";
                            echo (in_array($label, $label_checked)) ? "checked" : "";
                            echo "> <label for='$label' class='me-2'>$label</label>";
                        }
                    ?>
                    <input id="submit" class="btn btn-primary" type="submit" value="Search">
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php echo (!empty($notes)) ? '<h3 id="your_notes" style="color: white" >Your notes</h3>' : '<h3 id="your_notes" class="text-danger" > No notes found !! </h3>' ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="my_notes" class="row sortableList">
                    <?= view_notes_with_button($notes, false, false, $encode_label); ?>
                </div>
            </div>
            <!-- notes shared by ... -->
            <div class="row">
                <div class="col-md-12">
                    <div id="shared_notes" class="row sortableList">
                        <?php if (!empty($note_shared)): ?>
                            <?php 
                            $notes_by_sharer = [];
                            foreach ($note_shared as $row) {
                                $owner_name = $row->get_owner_name(); 
                                $owner_id = $row->get_owner();  
                                $note = $user->user_get_shared_note($owner_id, $row->get_id());  
                                
                                if (!empty($note)) {  
                                    if (!isset($notes_by_sharer[$owner_name])) {
                                        $notes_by_sharer[$owner_name] = [];  
                                    }
                                    $notes_by_sharer[$owner_name][] = $note;
                                }
                            }  
                                foreach ($notes_by_sharer as $sharer_name => $notes): ?>
                                    <div class="col-md-12" >
                                        <h4>Shared by <?= htmlspecialchars($sharer_name) ?></h4>
                                        <?php foreach ($notes as $note): ?>
                                            <?= view_notes_with_button($note, false, false, $encode_label); ?> 
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

</body>

</html>