<!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Edit profile</title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <link href="css/styles.css" rel="stylesheet" type="text/css">
    </head>


    <body class="bg-dark text-white" >
        <div class="title">Edit profile</div>
            <div id="edit_profile_container" >
                <form id="update_profile" action="Settings/edit_profile" method = "post">
                    <label for="mail">Mail:</label><br>
                    <input type="text" id="mail" name="mail" class="form-control bg-dark text-white mb-3  <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' && (in_array("This email already exists.", $errors) || in_array("This must be a valid mail addresse !", $errors))) ? 'is-invalid' : ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'is-valid' : ''); ?>" value = "<?php echo $user->get_mail(); ?>"><br>

                    <label for="fullName">Full name:</label><br>
                    <input type="text" id="fullName" name="fullName" class="form-control bg-dark text-white mb-3 <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array("The new name cannot be empty!", $errors)) ? 'is-invalid' : ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'is-valid' : ''); ?>" value="<?php echo $user->get_full_name() ; ?>"><br>

                    <a class="btn btn-primary text-white" role="button" href="settings" > Cancel</a>
                    <input  class="btn btn-primary" type="submit" value="submit change">


                <?php
                if(!empty($errors)){
                    
                    foreach($errors as $error){
                        echo "
                                    <div class=\"invalid-feedback\">
                                        <ul>
                                            <li>
                                                $error
                                            </li>
                                        </ul>
                                    </div>";
                    }
                }
                ?>
                </form>
            </div>
    </body>
</html>
