<?php
  require_once "view_note_factoring.php";
?>

<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="UTF-8">
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My shared Notes</title>

     <!-- Bootstrap CSS -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</head>
<body class="bg-dark text-white">
    <?= navbar($user); ?>

    <div class="main container bg-dark">    

        <h4 class="font-weight-bold mt-3 text-right ml-auto">Shared by <?= $name_share ?> </h4>

        <?php if ($notes_editor != false) { ?>
            <h2 class="font-weight-bold mt-3 text-right ml-auto">Notes share to you by <?= $name_share ?> as editor</h2>
            <div class="row">
                <?= view_notes_with_button($notes_editor, false, true,false); ?>
            </div>
        <?php } ?>

        <?php if ($notes_reader != false) { ?>
            <h2 class="font-weight-bold mt-3 text-right ml-auto">Notes share to you by <?= $name_share ?> as reader</h2>
            <div class="row">
                <?= view_notes_with_button($notes_reader, false, false,false); ?>
            </div>
            <?php } ?>
    </div>
</body>
</html>