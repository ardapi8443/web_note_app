<?php

require_once 'model/User.php';
require_once 'model/Note.php';
require_once 'model/User.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class ControllerLabel extends Controller {
    public function index() : void {

        if(isset($_POST['label']) && $_POST['encoded_label']){
            $id_note = $_POST['label'];
            $encode_label = $_POST['encoded_label'];

            if(self::user_can(Note::get_note_by_key($id_note))){
                $note = Note::get_note_by_key($_POST['label']);
                self::launch_page($note, $error = "", $encode_label);
            }
        }
    }

    public function add_label() : void {

        if(isset($_POST["new_label"]) && isset($_POST["note_id"])){
            if(self::user_can(Note::get_note_by_key($_POST["note_id"]))){

                $encoded_label = isset($_GET['param2']) ?  $_GET['param2'] : false;

                $note = Note::get_note_by_key($_POST["note_id"]);
                $error_add_label = "";
                $new_label = Tools::sanitize($_POST["new_label"]);
                $min_length = Configuration::get("label_min_length");
                $max_length = Configuration::get("label_max_length");

                if(mb_strlen($new_label) < $min_length || mb_strlen($new_label) > $max_length){
                    $error_add_label = "Label length must be between " .$min_length." and ".$max_length.".";
                }

                if(str_contains($new_label, ' ')){
                    $error_add_label = "The string contains a space character.";
                }

                if($note -> is_duplicate_label($_POST["new_label"])){
                    $error_add_label = "A note cannot contain the same label twice";
                }


               if(mb_strlen($error_add_label) == 0){
                   $note -> add_label($new_label);
                   $this->redirect("label", "show_labels", $note->get_id(), $encoded_label);
               } else {
                    self::launch_page($note, $error_add_label, $encoded_label);
               }
            }
        }
    }

    public function add_label_JS() : void{

        if(isset($_POST["new_label"]) && isset($_POST["note_id"])){
            if(self::user_can(Note::get_note_by_key($_POST["note_id"]))){

                $note = Note::get_note_by_key($_POST["note_id"]);
                $new_label = $_POST["new_label"];
                $note -> add_label($new_label);

                $all_label_per_note = $note -> get_labels_json();

                echo $all_label_per_note;

            }
        }
    }

    public function del_label() : void {
        if(isset($_POST["del_label"]) && isset($_POST["note_id"])){
            if(self::user_can(Note::get_note_by_key($_POST["note_id"]))){
                $encoded_label = isset($_GET['param1']) ?  $_GET['param1'] : false;
                $note = Note::get_note_by_key($_POST["note_id"]);
                if($note -> delete_Label($_POST["del_label"])){
                    self::launch_page($note, $errors = "", $encoded_label);
                }
            }
        }
    }

    public function search_my_notes() : void {
        $user = $this->get_user_or_redirect();
        $labels = $user->get_all_labels();
        $label_checked= $_POST['labels'];
        //notes dans l'ordre décroissant des poids !!
        if( isset($_POST['labels'])) {
           
            $notes = $user->get_my_notes_by_labels($label_checked);
            $encoded_labels = Tools::url_safe_encode($label_checked);
            $this -> redirect("label", "search", "$encoded_labels");
        } else {
            $label_checked = [];
            // $label_checked = $labels;
            // $notes = $user->get_my_notes_by_labels($labels);
         //   $encoded_labels = Tools::url_safe_encode($labels);
            $this -> redirect("label", "search");
        }
        $note_shared = $user->get_shared_notes_by_labels($labels);

        (new View("search"))->show(["user" => $user, "notes" => $notes, "labels" => $labels, "label_checked" => $label_checked, 
            "note_shared" => $note_shared]);
    }

    public function search() : void {
        $user = $this->get_user_or_redirect();
        $labels = $user->get_all_labels();
        $label_checked = [];
        $notes = [];
        $note_shared = [];
        $encode_label = false;

        if (isset($_GET['param1'])) {
            $encode_label = $_GET['param1'];
            $label  = Tools::url_safe_decode($encode_label);
            $label_checked = $label;
            $notes = $user->get_my_notes_by_labels($label);
            $note_shared = $user->get_shared_notes_by_labels($label);

            (new View("search"))->show(["user" => $user, "notes" => $notes, "labels" => $labels, "label_checked" => $label_checked, 
                "note_shared" => $note_shared, "encode_label" => $encode_label]);
        } else {
            
            (new View("search"))->show(["user" => $user, "notes" => $notes, "labels" => $labels, "label_checked" => $label_checked, 
                "note_shared" => $note_shared, "encode_label" => $encode_label]);
        }
    }

    public function get_labels_service() {
        $user = $this->get_user_or_redirect();
        echo json_encode($user->get_all_labels());
    }

    public function encode_label_service() {
        $this->get_user_or_redirect();
        if(!empty($_POST['labels'])) {
            echo Tools::url_safe_encode($_POST['labels']);
        } else echo null;
    }

    public function search_my_notes_service() {
        $user = $this->get_user_or_redirect();
        if(!empty($_POST['labels'])) {
            echo json_encode($user->get_my_notes_by_labels_service($_POST['labels']));
        } else echo null;
    }

    public function decoded_label_service() {
        $this->get_user_or_redirect();
        if(isset($_POST['param1'])) {
            echo json_encode(Tools::url_safe_decode($_POST['param1']));
        } else echo null;
    }

    public function get_shared_notes_by_labels_service() {
        $user = $this->get_user_or_redirect();
        if(!empty($_POST['labels'])) {
            echo json_encode($user->get_shared_notes_by_labels_service($_POST['labels']));
        } else echo null;
    }

    public function get_user_full_name_service() {
        $user = $this->get_user_or_redirect();
        if(!empty($_POST['user_id'])) {
            echo User::get_user_full_name_service($_POST['user_id']);
        } else echo null;
    }

    public function get_other_labels_JS () : void {

        if(isset($_POST["note_id"])) {
            if (self::user_can(Note::get_note_by_key($_POST["note_id"]))) {
                $note = Note::get_note_by_key($_POST["note_id"]);
                $other_labels = $note -> get_other_labels_json();

                echo $other_labels;
            }
        }
    }

    public function del_label_JS() : void {
        if(isset($_POST["old_label"]) && isset($_POST["note_id"])){
            if(self::user_can(Note::get_note_by_key($_POST["note_id"]))){

                $note = Note::get_note_by_key($_POST["note_id"]);
                $old_label = $_POST["old_label"];
                $note -> delete_label($old_label);

                $all_label_per_note = $note -> get_labels_json();

                echo $all_label_per_note;

            }
        }
    }

    public function show_label_handler() {
        $this->get_user_or_redirect();

        if (isset($_POST['label']) && isset($_POST['encoded_label'])) {
            $id = $_POST['label'];
            $encoded_label = $_POST['encoded_label'];
            $this->redirect("label","show_labels","$id", "$encoded_label");
        } else {
            $this->redirect();
        }
    }

    public function show_labels() {
        $this->get_user_or_redirect();

        if (isset($_GET['param1'])) {
            $id = $_GET['param1'];
            $note = Note::get_note_by_key($id);
            $encoded_label = isset($_GET['param2']) ?  $_GET['param2'] : false;
            $current_labels = $note -> get_labels();
            $other_labels = $note -> get_other_labels();
            sort($other_labels);

            (new View("label_handler"))->show(["note" => $note, "current_labels" => $current_labels, "other_labels" => $other_labels, "encoded_label" => $encoded_label]);
        } else {
            $this->redirect();
        }
    }

    private function launch_page (Note $note, String $errors, $encoded_label = false) : void{
        $current_labels = $note -> get_labels();
        $other_labels = $note -> get_other_labels();
        sort($other_labels);

        (new View("label_handler"))->show(["note" => $note, "current_labels" => $current_labels, "other_labels" => $other_labels ,"errors" => $errors, "encoded_label" => $encoded_label]);

    }
    private function user_can(Note $note) : bool{
        $res = false;
        $res = $res || $note -> get_owner() == $this ->get_user_or_false() -> get_id();
        $res = $res || $note -> is_editor_note($note -> get_id(), $this -> get_user_or_false() ->  get_id());
        return $res;
    }
}
?>