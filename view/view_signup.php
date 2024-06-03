<!DOCTYPE html>
<html lang = "en">
<head>
        <meta charset="UTF-8">
        <title>Sign Up</title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    </head>
    <body class="bg-dark text-white">
        <div class="main container bg-dark  d-flex flex-column">
            <form class="form-control bg-dark" id="signupForm" action="main/signup" method="post">
                <div class="title text-white border-bottom mb-3 text-center">Sign Up</div>
                <div class="title text-white border-bottom mb-3 text-center">Please enter your details to sign up :</div>
                            
                <div class="input-group mb-3 justify-content-md-center">
                    <span class="input-group-text bg-secondary" id="email"><i class="bi bi-envelope"></i></span>
                    <input id="mail" name="mail" type="text" size="16" value="<?= $mail ?>">
                </div>
                <div class="input-group mb-3 justify-content-md-center">
                    <span class="input-group-text bg-secondary" id="span_full_name"><i class="bi bi-person"></i></span>
                    <input id="input_full_name" name="full_name" type="text" size="16" value="<?= $full_name ?>">
                </div>
                <div class="input-group mb-3 justify-content-md-center">
                    <span class="input-group-text bg-secondary" id="span_password"><i class="bi bi-key"></i></span>
                    <input id="input_password" name="password" type="password" size="16" value="<?= $password ?>">
                </div>
                <div class="input-group mb-3 justify-content-md-center">
                    <span class="input-group-text bg-secondary" id="span_password_confirm"><i class="bi bi-key"></i></span>
                    <input id="input_password_confirm" name="password_confirm" type="password" size="16" value="<?= $password_confirm ?>">
                </div>
                <div class="input-group mb-3 justify-content-md-center">
                    <input class="bg-primary" type="submit" value="Sign Up">
                </div>
                <div class="subscribe mb-3 text-center">
                    <a href="./main/login">Cancel</a>
                </div>
            </form>
            <?php if(count($errors)!=0): ?>
                <div class='errors'>
                    <br><br><p>Please correct the following error(s) :</p>
                    <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </body>
</html>