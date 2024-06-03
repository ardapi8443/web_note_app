
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$note->get_title()?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
     <script src="lib/jquery-3.7.1.min.js" type="text/javascript"></script>
     <script src="scripts/back.js"></script>
     <script src="scripts/validation_text_note.js" type="text/javascript"></script>
     <script>
        let inf, sup, title, texte;

        $(document).ready(function() {
            title = $("#title");
            texte = $("#texte");

            title.addClass("is-valid");
            texte.addClass("is-valid");

            title.on("input", async function() {
                await validation_title(<?= Configuration::get("title_min_length") ?>, 
                    <?= Configuration::get("title_max_length") ?>, 
                    title);
            });
            texte.on("input", function() {
                validation_txt(<?= Configuration::get("item_min_length") ?>, 
                    <?= Configuration::get("item_max_length") ?>, 
                    texte);
            });
        }); 

        function check_all() {
            return title.hasClass('is-valid') && texte.hasClass('is-valid');
        }
    </script>
</head>



<body class="bg-dark text-white vh-100">

<?php
    require_once "modal_back.php";
?>

            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <!-- Header with Back and Save Icons -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            
                            <form action="note/open/<?= $note->get_id() . '/' . ($encoded_label == true ? $encoded_label : '') ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="get back">
                                <button id="back" data-bs-toggle='tooltip' data-bs-placement='right' title='back to open'>
                                    <i class="bi bi-arrow-90deg-left"></i>
                                </button>
                            </form>

                            <button type="submit" form="editTextNote" class="btn btn-outline-light p-0 border-0 bg-transparent">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16">
                                    <path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/>
                                </svg>
                            </button> 
                        </div>      
                <form id="editTextNote" action="note/edit/<?= $note->get_id() . '/' . ($encoded_label == true ? $encoded_label : '') ?>" method="post" data-bs-toggle="tooltip" data-bs-placement="right" title="edit this" note onsubmit="return check_all();">
                    <div class="bg-secondary p-3 rounded">

                    <p class="text">Created <?= $date_interval_creation ?> <?= $note->get_edited_at() != null ? 'Edited ' . $date_interval_edit : 'Never Edited' ?></p>

                        <h5 class="mt-2 mb-3">Title</h5>

                        <!-- Title Text Field -->
                        <input id="title" type="text" class="form-control bg-dark text-white mb-3 <?= !empty($errors) ? 'is-invalid' : '' ?>" name="title" value="<?= $note->get_title() ?>" autofocus>
                        <div class="invalid-feedback"> title must be filled, unique and between <?= Configuration::get("title_min_length") ?> to <?= Configuration::get("title_max_length") ?> characters</div>

                        <h5 class="mt-4 mb-3">Text</h5>
                        <!-- Text Text Field -->
                        <textarea id="texte" name="texte" class="form-control bg-dark text-white" style="min-height: 600px;"><?= $note->get_content() ?></textarea>
                        <div class="invalid-feedback"> texte must be empty or between <?= Configuration::get("item_min_length") ?> to <?= Configuration::get("item_max_length") ?> characters</div>

                        <input type="submit" class="d-none">
                    </div>
                </form>
            </div>
        </div>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                        <p><?= $errors ?></p>
                </div>
            <?php endif; ?>
    </div>
</body>
</html>