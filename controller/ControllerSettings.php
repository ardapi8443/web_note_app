<?php 
require_once 'framework/Configuration.php';
require_once 'framework/Tools.php';
require_once 'model/User.php';


class ControllerSettings extends Controller
{


    public function index(): void {
        $user = $this->get_user_or_redirect();
//        $user = $user->get_user_by_key($user->getId());
        (new View("settings"))->show(["user" => $user]);
    }

    public function edit_profile(): void {

        $user = $this->get_user_or_redirect();
        // $user = $user->get_user_by_key($user->getId());
        $errors = [];

        if (isset($_POST['mail']) && isset($_POST['fullName'])) {

            $new_mail = $_POST['mail'];
            $old_mail = $user->get_mail();
            $new_name = $_POST['fullName'];
            $old_name = $user->get_full_name();

            if ($new_mail != $old_mail || $new_name != $old_name) {

                $errors = array_merge($errors, Tools::isEmpty($new_name));
                if($old_mail != $new_mail){
                    $errors = array_merge($errors, User::validate_unicity($new_mail));
                }

                $errors = array_merge($errors, User::validate_mail($new_mail));

                if (empty($errors)) {

                    $newUser = $user->create_user($new_mail, $new_name);
                    $newUser->persist();
                    $this->log_user($newUser, "settings");

                } else {
                    (new View("edit_profile"))->show(["user" => $user, "errors" => $errors]);
                }
            } else {
                (new View("settings"))->show(["user" => $user]);
            }
        } else {

            (new View("edit_profile"))->show(["user" => $user, "errors" => $errors]);
        }

    }

    public function edit_password(): void {

        $user = $this->get_user_or_redirect();
        $user = $user->get_user_by_key($user->get_id());
        $errors = [];

        if (isset($_POST['PasswordOrigin']) && isset($_POST['PasswordConfirm'])) {

            $password = Tools::sanitize(trim($_POST['PasswordOrigin']));
            $password_confirm = Tools::sanitize(trim($_POST['PasswordConfirm']));
            $errors = array_merge($errors, User::validate_password($password));
            $errors = array_merge($errors, User::validate_passwords($password, $password_confirm));
            // $errors = array_values(array_unique($errors));

            if (empty($errors)) {
                $user = $user->create_user_PWD($password);
                $user->persist();
                $this->log_user($user, "settings");

            } else {
                (new View("edit_password"))->show(["user" => $user, "errors" => $errors]);
            }
        } else {
            (new View("edit_password"))->show(["user" => $user, "errors" => $errors]);
        }

    }

}

?>
