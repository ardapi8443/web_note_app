<?php


//if (!function_exists('inputClass')) {
    function inputClass(String $error_type, array $errors) : String{
        $res = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($errors[$error_type])) ? 'is-invalid' : ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'is-valid' : '');
        return $res;

    }
//}


    include_once "view_add_note_factoring.php";
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Add Checklist Note</title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <link href="css/styles.css" rel="stylesheet" type="text/css">
        <script src="lib/jquery-3.7.1.min.js" ></script>
    </head>

    <body class="bg-dark text-white">

    <?= get_buttons("add_checklist_note", "Note") ?>
    </div>
        <div class="title d-flex flex-row justify-content-center">

            <form id="add_checklist_note" class="row g-3" action="Note/add_checklist_note" method = "post">
                <div class="bg-secondary p-3 rounded">
                    <label for="title" class="form-label">Title</label>     
                    <div class="input-group has-validation">
                        <input type="text" class=" form-control bg-dark text-white mb-3 <?php echo inputClass('titleErr', $errors)?>"
                            id="title" name="title" value="<?php echo $title ?? ''; ?>" required>
                        <?php
                            if(isset($errors['titleErr'])){
                                echo "
                                <div class=\"invalid-feedback\">
                                    <ul>
                                        <li>
                                            {$errors['titleErr']}
                                        </li>
                                    </ul>
                                </div>";
                            }
                        ?>     
                    </div>      
                    <div>
                        <label >items </label><br>
                            <ul>
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <li>
                                        <input type="text" class="form-control bg-dark text-white mb-3 <?php echo inputClass('item' . $i, $errors) ?>"
                                            id="item<?php echo $i ?>" name="item<?php echo $i ?>" value="<?php echo $items['item' . $i] ?? ''; ?>">

                                        <?php if(isset($errors['item' . $i])) : ?>
                                            <div class="invalid-feedback">
                                                <ul>
                                                    <li>
                                                        <?php echo $errors['item' . $i]; ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                    </div> 
                </div>
            </form>
        </div>
    </body>
</html>
