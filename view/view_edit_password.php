<!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Change Password</title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <link href="css/styles.css" rel="stylesheet" type="text/css">
    </head>

    <body class="bg-dark text-white" >
        <div class="title">Change Password</div>
            <div id="edit_profile_container" >
                <form id="update_password" action="Settings/edit_password" method = "post">
                    <label for="PasswordOrigin">Password :</label><br>
                    <input type="password" id="PasswordOrigin" class="form-control bg-dark text-white mb-3  <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($errors)) ? 'is-invalid' : ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'is-valid' : ''); ?>" name="PasswordOrigin" value = ""><br>
                    <label for="PasswordConfirm">Confirmation :</label><br>
                    <input type="password" id="PasswordConfirm" class="form-control  bg-dark text-white mb-3  <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($errors)) ? 'is-invalid' : ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'is-valid' : ''); ?>" name="PasswordConfirm"  value=""><br>
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
                    <a class="btn btn-primary text-white" role="button" href="settings" > Cancel</a>

                    <input class="btn btn-primary" type="submit" value="submit new password">

                </form> 
            </div>
    </body>
</html>


