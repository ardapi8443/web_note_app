<!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Settings</title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <link href="css/styles.css" rel="stylesheet" type="text/css">
        <style>
            #idUL {
                text-align: left ;
            }
        </style>

    </head>

    <body class="bg-dark text-white" >
        <div class="main container bg-dark  d-flex flex-column">
            <div class="title text-white border-bottom mb-3 text-center" >
                <div class="row gx-5">
                    <div  class="col">
                        <a href="note"> <i class="bi bi-arrow-90deg-left"></i> </a>
                    </div>
                    <div class="col">
                        <span>Settings</span>
                    </div>

                </div>     
                <div>
                    <span>Hey <strong><?php echo $user->get_full_name(); ?></strong> ! </span>
                </div>
                <div class="d-flex flex-column bd-highlight mb-3 text-start" id="idUL">
                    <ul class="list-unstyled">
                        <li ><i class="bi bi-gear"></i>
                            <a href="settings/edit_profile"> Edit profile </a>
                        </li>
                        <li><i class="bi bi-three-dots"></i>
                            <a href="settings/edit_password"> Change password </a>
                        </li>
                        <li><i class="bi bi-arrow-bar-right"></i>
                            <a href="settings/logout"> logout </a>
                        </li>
                    </ul> 
                </div>  
            </div>
        </div>
    </body>
</html>

