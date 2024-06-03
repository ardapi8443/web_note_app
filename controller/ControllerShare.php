<?php
require_once 'framework/Configuration.php';
require_once 'framework/Tools.php';
require_once 'model/User.php';
require_once 'model/ChecklistNote.php';
require_once 'model/NoteShare.php';


class ControllerShare extends Controller{
    public function index() : void {

        if(isset($_POST['userId']) && isset($_POST['permission'])){

            if($_POST['userId'] != "-User-" && $_POST['permission'] != "-Permission-"){

                $encoded_label = isset($_GET['param2']) ?  $_GET['param2'] : false;

                $ns = new NoteShare($_GET["param1"], $_POST['userId'], $_POST['permission']);
                $ns -> persist();
                $this->redirect("share", "index", $_GET["param1"],$encoded_label);
            }
        }
            self::launch_page();
    }

    public function switch() : void{

        $ns = NoteShare::get_noteShare_by_note_id_user_id($_GET["param1"], $_GET["param2"]);

        $encoded_label = isset($_GET['param3']) ?  $_GET['param3'] : false;

        if($ns -> get_editor() == 1){
            $ns -> set_editor(0);
        } else {
            $ns -> set_editor(1);
        }

        $ns->persist();
       // self::launch_page();
       $this->redirect("share", "index", $_GET["param1"],$encoded_label);
    }

    public function delete() : void{

        $ns = NoteShare::get_noteShare_by_note_id_user_id($_GET["param1"], $_GET["param2"]);
        $encoded_label = isset($_GET['param3']) ?  $_GET['param3'] : false;

        if($ns){
            $ns->delete();
        }

        //self::launch_page();
        $this->redirect("share", "index", $_GET["param1"],$encoded_label);

    }

    private function launch_page () : void {

        $user = $this ->get_user_or_redirect();
        $note = Note::get_note_by_key($_GET["param1"]);
        $encoded_label = isset($_GET['param2']) ?  $_GET['param2'] : false;
        
        $note_share = NoteShare::get_shared_by_note($_GET["param1"]);
        $user_not_share = User::get_not_shared($note, self::get_user_or_false());

        if($note_share){
            $note_share = self::sorting_note_share($note_share);
        }
        if($user_not_share){
            $user_not_share = self::sorting_user_not_share($user_not_share);
        }

        (new View("shares")) -> show(["user" => $user, "note" => $note, "note_share" => $note_share, "user_not_share" => $user_not_share, "encoded_label" => $encoded_label]);

    }

    private function sorting_user_not_share(Array $user_not_share) : Array {

        $res = $user_not_share;

        usort($res, function($a, $b) {
            return strcmp($a -> get_full_name(), $b -> get_full_name());
        });


        return $res;
    }
    
    private function sorting_note_share(Array $note_share) : Array {
        $res = [];
        $note_share_with_names = [];

        foreach ($note_share as $ns){
            $fullname = $ns -> get_full_user_by_id() -> get_full_name();
            $note_share_with_names[] = ['noteShare' => $ns, 'userName' => $fullname];
        }
        usort($note_share_with_names, function($a, $b) {
            return strcmp($a['userName'], $b['userName']);
        });
        foreach ($note_share_with_names as $item) {
            $res[] = $item['noteShare'];
        }

        return  $res;
    }


    public function add_perm_JS() : void {

        if (isset($_POST['perm']) && isset($_POST['userId']) && isset($_POST['noteId'])) {
            if ($this->user_logged() && $this->user_can(Note::get_note_by_key($_POST['noteId']))){

                $ns = new NoteShare($_POST['noteId'], $_POST['userId'], $_POST['perm']);
                $ns -> persist();

                $json = [];
                $json["noteId"] = $ns->get_note_id();
                $json["userName"] = User::get_user_by_key($ns->get_user_id())->get_full_name();
                $json["userId"] = $ns->get_user_id();
                $json["perm"] = $ns->get_editor();

                echo json_encode($json);
            }
        }
    }
    public function del_perm_JS() : void {

        if (isset($_POST['userId']) && isset($_POST['noteId'])) {
            if ($this->user_logged() && $this->user_can(Note::get_note_by_key($_POST['noteId']))){

                $ns = NoteShare::get_noteShare_by_note_id_user_id($_POST["noteId"], $_POST["userId"]);
                if($ns){
                    $ns->delete();
                }

                $json = [];
                $json["userName"]  = User::get_user_by_key($_POST['userId']) -> get_full_name();
                $json["noteId"] = $_POST['noteId'];

                echo json_encode($json);
            }
        }
    }

    public function open() {
        $this->get_user_or_redirect();

        if (isset($_POST['share']) && isset($_POST['encoded_label'])) {
            $id = $_POST['share'];
            $encoded_label = $_POST['encoded_label'];

            $this->redirect("share", "index", $id, $encoded_label);
        } else {
            $this->redirect();
        }

    }

    public function switch_perm_JS() : void {

        if (isset($_POST['userId']) && isset($_POST['noteId'])) {
            if ($this->user_logged() && $this->user_can(Note::get_note_by_key($_POST['noteId']))){

                $ns = NoteShare::get_noteShare_by_note_id_user_id($_POST["noteId"], $_POST["userId"]);
                if($ns -> get_editor() == 1){
                    $ns -> set_editor(0);
                } else {
                    $ns -> set_editor(1);
                }
                $ns->persist();

                $json = [];
                $json["noteId"] = $ns->get_note_id();
                $json["userName"] = User::get_user_by_key($ns->get_user_id())->get_full_name();
                $json["userId"] = $ns->get_user_id();
                $json["perm"] = $ns->get_editor();

                echo json_encode($json);
            }
        }
    }


    private function user_can(Note $note) : bool{
        $res = false;
        $res = $res || $note -> get_owner() == $this ->get_user_or_false() -> get_id();
        return $res;
    }

}

?>