<?php

require_once 'model/User.php';
require_once 'model/Note.php';
require_once 'model/User.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

Class ControllerMain extends Controller {
    //si l'utilisateur est connecté, redirige vers ses notes.
    //sinon, produit la vue d'accueil.
    public function index() : void {
        if ($this->user_logged()) {
            $this->redirect("note");

        } else {
            $this->login();

        }
    }

    public function login() : void {
        $mail = '';
        $password = '';
        $errors = [];
        if (isset($_POST['mail']) && isset($_POST['password'])) { //note : pourraient contenir des chaînes vides

            $mail = $_POST['mail'];
            $password = $_POST['password'];
            $errors = User::validate_login($mail, $password);
            
            if (empty($errors)) {

            $this->log_user(User::get_user_by_mail($mail), "note");

            }

            (new View("login"))->show(["mail" => $mail, "password" => $password, "errors" => $errors, "user" => $user = User::get_user_by_mail($mail)]);

        } else (new View("login"))->show(["mail" => $mail, "password" => $password, "errors" => $errors, "user" => $user = User::get_user_by_mail($mail)]);
    }

    public function signup() : void {
        $mail = '';
        $full_name = '';
        $password = '';
        $password_confirm = '';
        $errors = [];

        if(isset($_POST['full_name']) && isset($_POST['password']) && isset($_POST['password_confirm']) && isset($_POST['mail'])){
            $full_name = Tools::sanitize(trim($_POST['full_name']));
            $password = Tools::sanitize(trim($_POST['password']));
            $password_confirm = Tools::sanitize(trim($_POST['password_confirm']));
            $mail = $_POST['mail'];
            
            $user = new User(mail: $mail, hashed_password: Tools::my_hash($password), full_name: $full_name, role: "user", id: 0);
            $errors = User::validate_unicity($mail);
            $errors = array_merge($errors, $user->validate());
            $errors = array_merge($errors, User::validate_passwords($password, $password_confirm));
        
            if (count($errors) == 0) { 
                $user->persist(); //sauve l'utilisateur
                $user = User::get_user_by_mail($user -> get_mail());
                $this->log_user($user);
            }
        }
        
        (new View("signup"))->show(["mail" => $mail, "full_name" => $full_name, "password" => $password, "password_confirm" => $password_confirm, "errors" => $errors]);
    }
    
}

?>