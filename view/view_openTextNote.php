<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <base href="<?= $web_root ?>">
        <title><?=$note->get_title()?></title>
        <!-- Bootstrap CSS -->
        <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"> -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <script src="lib/jquery-3.7.1.min.js" type="text/javascript"></script>
    </head>

    <body class="bg-dark text-white vh-100">
        <?php require_once "openNote.php"?>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="bg-secondary p-3 rounded">
                    <p class="text">Created <?= $date_interval_creation ?> <?= $note->get_edited_at() != null ? 'Edited ' . $date_interval_edit : 'Never Edited' ?></p>
                        <h5 class="mt-2 mb-3">Title</h5>
                        <!-- Title Text Field -->
                        <input type="text" id="titre" name="title" class="form-control bg-dark text-white mb-3" value= "<?= $note->get_title() ?>" readonly>

                        <h5 class="mt-4 mb-3">Text</h5>
                        <!-- Text Text Field -->
                        <p id="texte" class="form-control bg-dark text-white" style="min-height: 600px;"><?= $note->get_content() ?></p>

                        <!-- Le bouton de soumission est maintenant caché, car nous utilisons l'icône pour soumettre -->
                        <input type="submit" class="d-none">
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>