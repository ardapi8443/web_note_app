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
</head>
<body class="bg-dark text-white ">
    <?php
        $id = $note->get_id();
    ?>
    <h1>vous êtes sur le point de supprimer une note !!</h1>
    <h3>cette action est iréversible !!</h3>
    <h4>êtes vous certain ?</h4>
    <form class='link' action='note/delete' method='post'>
    <input type='number' name='deletable' value=<?=$id?> hidden>
    <input type='hidden' name='encoded_label' value= '<?= $encoded_label ?>' >
        <button title ='delete this note' class='text-danger'>
            YES
        </button>
    </form>

    <form class='link' action='note/open/<?=$id . '/' . ($encoded_label == true ? $encoded_label : '') ?>' method='post'>
        <button title ='back to reason'>
        CANCEL
        </button>
    </form>
</body>
</html>