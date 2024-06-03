<?php
    require_once "view_note_factoring.php";
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Notes</title>

         <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

         <!-- JQUERY UI - Touch Punch -->
        <script src="lib/jquery-3.7.1.min.js" ></script>
        <script src="lib/jquery-ui.min.js" ></script>
        <script src="lib/jquery.ui.touch-punch.min.js" ></script>
        
        <style>
            .hidden {
                display: none !important;
            }
            .card-footer {
                display: block;
            }
            .custom-text-size {
                font-size: 17px;
            }
            nav.fixed-bottom.fixed-right {
                bottom: 55px;
                right: 30px;
            }
        </style>
  
        <script>

            const userLog = "<?= $user->get_id() ?>";
            let movedNotes = [];
            let noteIds = [];
            let isUpdating = false;

        document.addEventListener("DOMContentLoaded", function() {
            var cardFooters = document.querySelectorAll('.cardFooter');
            cardFooters.forEach(function(cardFooter) {
                cardFooter.style.display = 'none';
                cardFooter.classList.add('hidden');
            });
        });

   
        $(function() {
            $("#pinned, #unPinned").sortable({
                connectWith: ".sortableList",
                update: function(event, ui) {
                    if (isUpdating) {
                        return;
                    }
                    isUpdating = true;
                    movedNotes = [];
                    noteIds = [];
                    var sortedNotesPinned = $("#pinned .col-md-6").map(function () {
                        return {
                            id: $(this).data("note-id"),
                            pinned: 1
                        };
                    }).get();

                    var sortedNotesUnPinned = $("#unPinned .col-md-6").map(function () {
                        return {
                            id: $(this).data("note-id"),
                            pinned: 0
                        };
                    }).get();
                    // Trouver les notes qui ont été déplacées
                    sortedNotesPinned.forEach(function(noteIds, index) {
                                movedNotes.push(noteIds);
                    });

                    sortedNotesUnPinned.forEach(function(noteIds, index) {
                                movedNotes.push(noteIds);   
                    });
                    // console.log("Notes déplacées :", movedNotes);
                    // envoyer les 2 tabs pour mon async
                    update_notes(noteIds, movedNotes);

                    setTimeout(function () {
                        isUpdating = false;
                    }, 100);

                }
            }).disableSelection();
        
        });

        async function update_notes(noteId,new_weights) {
            try {
                const response = await $.ajax({
                    url: 'note/update_pos_notes',
                    method: 'POST',
                    //contentType: 'application/json',
                    data: {noteId:noteId, new_weights: new_weights}
                });
                console.log('Notes updated successfully');
            } catch (error) {
                console.error('Failed to update notes:', error);
            }
        }

        </script>

    </head>

    <body class='bg-dark text-white'>
        <?= navbar($user); ?>

        <div class="main container bg-dark">
            <h1 class="font-weight-bold mt-3 text-right ml-auto">My Notes</h1>
            <div class="row">
                <div class="col-md-12">
                    <?= ($user->has_notes_pinned() || $user->has_notes_others()) ? ($user->has_notes_pinned() ? "<h3>Pinned</h3>" : "") : "<h3>Your notes are empty</h3>"; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div id="pinned" class="row sortableList">
                        <?= view_notes_with_button($pinned, true, true); ?>
                    </div>
                </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $user->has_notes_others() ? " <h4> Others </h4>" : "" ; ?>
                </div>
            </div>
                <div class="col-md-12">
                    <div id="unPinned" class="row sortableList">
                        <?= view_notes_with_button($notPinned, true, true); ?>
                    </div>
                </div>
            </div>
                    <nav class="fixed-bottom fixed-right">
                        <ul class="list-unstyled d-flex justify-content-end">
                            <li class="ml-3">
                                <a class="nav-link text-warning" href="note/add_note" tabindex="-1" aria-disabled="false"><i class="bi bi-file-earmark display-6"></i></a>
                            </li>
                            <li>
                                <a class="nav-link text-warning" href="note/add_checklist_note"><i class="bi bi-list-check display-6"></i></a>
                            </li>
                        </ul>
                    </nav>
            </div>

    </body>
</html>