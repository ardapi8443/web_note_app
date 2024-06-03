<?php
include_once "view_add_note_factoring.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add TextNote</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Intégration de Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <script src="lib/jquery-3.7.1.min.js"></script>
    <script src="scripts/validation_text_note.js" ></script>
    <script>
        let inf, sup, title, texte;


        $(document).ready(function() {
            inf_title = <?= Configuration::get("title_min_length") ?>;
            sup_title = <?= Configuration::get("title_max_length") ?>;
            document.getElementById('inf_title').textContent = inf_title;
            document.getElementById('sup_title').textContent = sup_title;

            inf_content = <?= Configuration::get("item_min_length") ?>;
            sup_content = <?= Configuration::get("item_max_length") ?>;

            document.getElementById('inf_content').textContent = inf_content;
            document.getElementById('sup_content').textContent = sup_content;

            title = $("#title");
            texte = $("#texte");

            title.addClass("is-invalid");
            texte.addClass("is-valid");

            title.on("input", async function() {
                await validation_title(inf_title, sup_title, title);
            });
            texte.on("input", function() {
                validation_txt(inf_content, sup_content, texte);
            });
            
        }); 

        function check_all() {
            return title.hasClass('is-valid') && texte.hasClass('is-valid');
        }
    </script>
</head>
<body class="bg-dark text-white ">
    <div class="title">

        <?= get_buttons("noteAdd", "note") ?>
        <div class="row justify-content-center">
                <form class="row g-3 w-75" id="noteAdd" action="note/add_note" method="post" onsubmit="return check_all();">
                    <div class="bg-secondary p-3 rounded">
                        <div>
                            <h5 class="mt-2 mb-3">Title</h5>
                            <input id="title" type="text" class="form-control bg-dark text-white mb-3" name="titre" value = "<?= $title ?>" autofocus>
                            <div id="feedback" class="invalid-feedback">title must be filled, unique, and between <span id="inf_title"></span> to <span id="sup_title"></span> characters</div>
                        </div>
                        <div>
                            <h5 class="mt-4 mb-3">Text</h5>
                            <textarea id="texte" name="texte" class="form-control bg-dark text-white" style="min-height: 600px;"><?= $texte ?></textarea>
                            <div class="invalid-feedback"> texte must be empty or between <span id = "inf_content"></span> to <span id="sup_content"></span> characters</div>
                        </div>
                        <input type="submit" class="d-none">
                    </div>
                </form>
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <p><?= $errors ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>     