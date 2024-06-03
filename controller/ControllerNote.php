<?php

require_once 'model/User.php';
require_once 'model/Note.php';
require_once 'model/ChecklistNote.php';
require_once 'model/ChecklistNoteItem.php';
require_once 'model/SortOfNote.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'model/TextNote.php';

class ControllerNote extends Controller {
    public function index() : void {
        if ($this->user_logged()) {

            $user = $this->get_user_or_redirect();
            $pinned = $user->get_notes_pinned();
            $notPinned = $user->get_notes_unpinned();

            (new View("notes"))->show(["user" => $user, "pinned" => $pinned,"notPinned"=> $notPinned]);
        } else {
            $this->redirect();
        }
    }

    public function add_checklist_note() : void {

        $user = $this ->get_user_or_redirect();
        $errors = [];
        $items = [];
        $title = '';

        if (isset($_POST['title'])) {

            $title =$_POST['title'];
            $titleErr =  ChecklistNote::validate_note_title($_POST['title'], $user-> get_id(), 0);

            if(!empty($titleErr)){

                $titleErr = ['titleErr' => $titleErr];
                $errors = array_merge($errors,  $titleErr);
            }

            for($i = 1; $i <= 5; $i++){
                $itemName = "item" . $i;

                if (!empty($_POST[$itemName])){
                    $currentItem = [$itemName => $_POST[$itemName]];
                    $items = array_merge($items,  $currentItem );
                }
            }

            $errors = array_merge($errors , Note::check_duplicate($items));

            if(empty($errors)){

                $chk_note_items = array();

                for($i = 1; $i <= 5; $i++){
                    $itemI = "item" . $i;
                    if (isset($items[$itemI])) {

                        array_push($chk_note_items, new ChecklistNoteItem(0,0, $items[$itemI],0));

                    }
                }

                $formatted_now = Tools::get_datetime_formatted();
                $new_weight = Note::get_max_weight_all_notes($user->get_id()) + 1;

                $checklist_note= new ChecklistNote(0, $_POST['title'], $user -> get_id(), $formatted_now, null, 0,0,$new_weight, $chk_note_items);
                $checklist_note -> persist();

                $last_insert = Note::note_latest_ID();

                $this->redirect("note","open",$last_insert);

            }
        }

        (new View("add_checklist_note")) -> show(["title" => $title,"items" => $items, "errors" => $errors]);


    }



    public function open() : void {
        if(isset($_GET['param1'])) {
            
            $user = $this->get_user_or_redirect();
            $id = $_GET['param1'];
            $note = Note::get_note_by_key($id);
            $encoded_label = isset($_GET['param2']) ?  $_GET['param2'] : false;

            if($note && ($note->get_owner() == $user->get_id() || $user->is_reader($id)) ) {

                $now = Tools::get_datetime();
                $time_zone = Tools::get_time_zone();
                $date_interval_creation = $this->date_interval(new DateTime($note->get_created_at(), $time_zone), $now);

                if($note->get_edited_at() == null) {
                    $date_interval_edit = "never";
                } else {
                    $date_interval_edit = $this->date_interval(new DateTime($note->get_edited_at(), $time_zone), $now);
                }

                if ($user->is_editor($id)) {
                    $editor = true;
                } else {
                    $editor = false;
                }

                if ($note->is_text_note()) {
                    (new View("openTextNote"))->show(["note" => $note, "date_interval_creation" =>$date_interval_creation, "date_interval_edit" => $date_interval_edit, "user" => $user, "editor" => $editor, "encoded_label" => $encoded_label]);
                } else {
                    (new view("openChecklistNote"))->show(["note" => $note, "date_interval_creation" =>$date_interval_creation, "date_interval_edit" => $date_interval_edit, "user" => $user, "editor" => $editor, "encoded_label" => $encoded_label]);
                }

            } else {
                $this->redirect();
            }
        } else {
            $this->redirect();
        }
    }

    public function show_note() {
        $this->get_user_or_redirect();

        if (isset($_POST['param1']) && isset($_POST['param2'])) {
            $id = $_POST['param1'];
            $encoded_label = $_POST['param2'];
            $this->redirect("note","open","$id", $encoded_label);
        } else {
            $this->redirect();
        }
    }
    
    private function date_interval($date, $now): string {
        $diff = "";

        if ($date->diff($now)->y != 0) {
            $diff = $date->diff($now)->y . ' year' . ($date->diff($now)->y > 1 ? 's' : '') . ' ';
        } elseif ($date->diff($now)->m != 0) {
            $diff = $date->diff($now)->m . ' month' . ($date->diff($now)->m > 1 ? 's' : '') . ' ';
        } elseif ($date->diff($now)->d != 0) {
            $diff = $date->diff($now)->d . ' day' . ($date->diff($now)->d > 1 ? 's' : '') . ' ';
        } elseif ($date->diff($now)->h != 0) {
            $diff = $date->diff($now)->h . ' hour' . ($date->diff($now)->h > 1 ? 's' : '') . ' ';
        } elseif ($date->diff($now)->i != 0) {
            $diff = $date->diff($now)->i . ' minute' . ($date->diff($now)->i > 1 ? 's' : '') . ' ';
        } elseif ($date->diff($now)->s != 0) {
            $diff = $date->diff($now)->s . ' second' . ($date->diff($now)->s > 1 ? 's' : '') . ' ';
        } else {
            return "just now.";
        }
        return $diff . 'ago.';
    }

   public function archive_notes() {
       $user = $this->get_user_or_redirect();
       $notes = $user->get_archived_notes();
       (new View("archive_notes"))->show(["user" => $user, "notes" => $notes]);
   }

    public function pin_unpin() : void {
        $user = $this->get_user_or_redirect();
        if(isset($_POST['pinnable']) && isset($_POST['encoded_label'])){
            $id_note = $_POST['pinnable'];
            $encoded_label = $_POST['encoded_label'];

            if($note = Note::get_note_by_key($id_note)) {
                if($user->get_id() == $note->get_owner() || $user->is_editor($note->get_id())) {
                    $note->pin_unpin($user->get_id());
                    $this->redirect("note", "open", $id_note, $encoded_label);
                }
            } else $this->redirect();
        } else $this->redirect();
    }

    public function archive_unarchive() : void {
        $user = $this->get_user_or_redirect();
        if(isset($_POST['archivable']) && isset($_POST['encoded_label'])){
            $id_note = $_POST['archivable'];
            $encoded_label = $_POST['encoded_label'];

            if($note = Note::get_note_by_key($id_note)) {
                if($user->get_id() == $note->get_owner() || $user->is_editor($note->get_id())) {
                    $note->archive_unarchive($user->get_id());
                    $this->redirect("note", "open", $id_note, $encoded_label);
                }
            } else $this->redirect();
        } else $this->redirect();

    }

    public function delete() : void {
        $user = $this->get_user_or_redirect();

        if (isset($_POST['deletable'])) {
            $id_note = $_POST['deletable'];
            $encoded_label = isset($_POST['encoded_label']) ?  $_POST['encoded_label'] : false;

            $note = Note::get_note_by_key($id_note);

            if ($note && $user->get_id() == $note->get_owner() && !$encoded_label) {
                $note->delete();
                $this->redirect("note", "archive_notes");
            } else if (!$encoded_label) $this->redirect("note", "shared", $note->get_owner());
            
            if ($encoded_label) {
                $note->delete();
                $this->redirect("label","search", $encoded_label);
            }
        } else {
            $this->redirect();
        }
    }

    public function show_delete() {
        $this->get_user_or_redirect();

        if (isset($_POST['encoded_label']) && isset($_POST['deletable_id'])) {
            $encoded_label = $_POST['encoded_label'];
            $id = $_POST['deletable_id'];

            $this->redirect("note","deletable",$id, $encoded_label);
        } else {
            $this->redirect();
        }
    }

    public function deletable() : void {
        $user = $this->get_user_or_redirect();
        if (isset($_GET['param1'])) {
            $encoded_label = isset($_GET['param2']) ?  $_GET['param2'] : false;

            $note = Note::get_note_by_key($_GET['param1']);
            if($user->get_id() == $note->get_owner() || $user->is_editor($note->get_id())) {
                (new View("deletable"))->show(["note" => $note, "encoded_label" => $encoded_label]);
            }
        } else
            $this->redirect();
    }

    public function edit_note() {

        $this->get_user_or_redirect();
        
        if (isset($_POST['param1']) && isset($_POST['param2'])) {
            $id = $_POST['param1'];
            $encoded_label = $_POST['param2'];
            $this->redirect("note","edit", $id, $encoded_label);
        } else {
            $this->redirect();
        }
    }

    public function edit_checklist_note() : void {

        $user = $this->get_user_or_redirect();
        $error_edit_item = [];
        $error_add_item = [];
        $title_error = [];
        $is_changed = false;
        $encoded_label = isset($_GET['param2']) ?  $_GET['param2'] : false;

        if (isset($_GET['param1'])) {
            $note = Note::get_note_by_key($_GET['param1']);
            $time_zone = Tools::get_time_zone();
            $now = Tools::get_datetime();
            $date_interval_creation = $this->date_interval(new DateTime($note->get_created_at(), $time_zone), $now);
            $date_interval_edit = $note->get_Edited_At() ? $this->date_interval(new DateTime($note->get_Edited_At(), $time_zone), $now) : "Never Edited";

            if (isset($_POST['title'])) {
                $title_error = Note::validate_note_title_edit($_POST['title'], $note ->get_owner(), $note->get_id());
                if(empty($title_error) &&  $_POST['title'] !== $note->get_title()){
                    self::chk_note_update_title($_POST['title'], $user, $note);
                    $is_changed = true;
                }
            }

            if (isset($_POST['items'])) {
                if(self::chk_if_update_notes()){
                    $is_changed = true;
                }
                $error_edit_item = array_merge($error_edit_item,self::chk_note_update_notes($note, $error_edit_item));
            }

            if (isset($_POST['deleteItem'])) {
                self::chk_note_delete_note($note);
                $is_changed = true;
            }

            if (isset($_POST['addItem'])) {
                if(self::chk_if_add_item($note)){
                    $is_changed = true;
                }
                $error_add_item = self::chk_note_add_item($note);
            }



            if((empty($error_edit_item) && empty($error_add_item) && empty($title_error)) && (!isset($_POST['deleteItem']) && !isset($_POST['addItem']))){
                $this->redirect("note","open",  $note->get_id(), $encoded_label);
            }

            if ($is_changed){
                $date_interval_edit = $note->get_Edited_At() ? $this->date_interval(new DateTime($note->get_Edited_At(), $time_zone), $now) : "Never Edited";
            }

            (new View("edit_checklist_note"))->show(["note" => $note, "date_interval_creation" => $date_interval_creation, "date_interval_edit" => $date_interval_edit, 
            "error_add_item" => $error_add_item, "error_edit_item" => $error_edit_item, "title_error" => $title_error, "encoded_label" => $encoded_label]);
        } else {
            Tools::abort("Wrong/missing param or action no permited");
        }
    }

    private function chk_if_update_notes() : bool {

        $res = false;

        foreach ($_POST['items'] as $itemId => $content) {
            $checkListNoteItem = ChecklistNoteItem::get_checklistNoteItem_by_key($itemId);
            if ($checkListNoteItem) {
                if ($checkListNoteItem->get_content() !== $content) {
                    $res = true;
                }
            }
        }
        return $res;
    }

    private function chk_if_add_item(Note $note) : bool {

        $res = false;

        $newItem = $_POST['newItem'];
        if (!$note->is_duplicate_item($newItem)) {
            if (empty($titleError)) {
                $item = new ChecklistNoteItem(0, $_GET['param1'], $newItem, 0);
                $errors = $item->is_valid_item_string();
                if (empty($errors)) {
                    $res = true;
                }
            }
        }

        return $res;
    }


    private function chk_note_add_item(Note $note) : string {

        $res = '';
        $newItem = $_POST['newItem'];

        if (!$note->is_duplicate_item($newItem)) {
            if (empty($titleError)) {
                $item = new ChecklistNoteItem(0, $_GET['param1'], $newItem, 0);
                $errors = $item->is_valid_item_string();
                if (empty($errors)) {
                $note->update_edit_at();
                $note->set_title($_POST['title']);
                $note->add_item($item);
                $note->set_edited_at(date('Y-m-d H:i:s'));
                $note->persist();
                }
            }
        } else {
            $res = "L'élément '$newItem' existe déjà dans la liste. Sauvegarde impossible.";
        }

        return $res;
    }

    private function chk_note_delete_note(Note $note) : void {
        $item_id = $_POST['deleteItem'];
        $noteItem = ChecklistNoteItem::get_checklistNoteItem_by_key($item_id);
        $noteItem->delete_item();
        $note->update_edit_at();
        $note-> persist();
    }

    private function chk_note_update_notes(Note $note, array $errorsList) : array {

        foreach ($_POST['items'] as $itemId => $content) {
            $checkListNoteItem = ChecklistNoteItem::get_checklistNoteItem_by_key($itemId);
            if ($checkListNoteItem) {
                $checkListNote = ChecklistNote::get_checklistnote_by_item($checkListNoteItem->get_id());

                if ($checkListNoteItem->get_content() !== $content) {

                        $minLength = Configuration::get("item_min_length");
                        $maxLength = Configuration::get("item_max_length");

                    if($checkListNoteItem->is_valid_item_string_edit($content)){
                        $errorsList[] =  $checkListNoteItem->get_id()."la longeur de l'item doit être entre '$minLength' et '$maxLength' caractères";
                    }

                    if ($checkListNote->is_duplicate_item($content)) {

                        $message = "L'élément '$content' existe déjà dans la liste. Sauvegarde impossible.";
                        $id_duplicate = CheckListNoteItem::get_duplicate_id($checkListNoteItem->checklist_note_id,$content);

                        $errorsList[] = $id_duplicate.$message;
                        $errorsList[] = $itemId.$message;

                    } else if(empty($errorsList)) {
                        $checkListNoteItem->set_content($content);
                        $note->set_edited_at(date('Y-m-d H:i:s'));
                        $note-> persist();
                        $checkListNoteItem->persist();
                    }
                }
            }
        }
        return $errorsList;
    }

    private function chk_note_update_title(String $new_title, User $user, Note $note) : void {

        $note->set_title($new_title);
        $note->set_edited_at(date('Y-m-d H:i:s'));
        $note->persist();

    }

    public function show_edit() {
        $this->get_user_or_redirect();

        if (isset($_POST['id']) && isset($_POST['encoded_label'])) {
            $id = $_POST['id'];
            $encoded_label = $_POST['encoded_label'];
            $this->redirect("note","edit",$id, $encoded_label);
        } else {
            $this->redirect();
        }
    }

    public function edit() : void {
        $errors = [];
        $new_title = '';
        $encoded_label = isset($_GET['param2']) ?  $_GET['param2'] : false;

        if (isset($_GET['param1'])) {
            $user = $this ->get_user_or_redirect();
            $id = $_GET['param1'];
            $note = Note::get_note_by_key($id);
            $time_zone = Tools::get_time_zone();
            $now = Tools::get_datetime();
            $date_interval_creation = $this->date_interval(new DateTime($note->get_created_at(), $time_zone), $now);
            $date_interval_edit = $note->get_edited_at() ? $this->date_interval(new DateTime($note->get_edited_at(), $time_zone), $now) : "Never Edited";
            $note_updated = false; // Flag pour suivre si des modifications ont été apportées


            if (isset($_POST['texte'])) {
                $new_text = $_POST['texte'];
                if ($new_text != $note->get_content()) {
                    $note->set_content($new_text);
                    $note_updated = true;
                }
            }

            if (isset($_POST['title'])) {
                $new_title = $_POST['title'];
                $errors = TextNote::validate_note_title_edit($_POST['title'], $user-> get_id(), $id);
                if (empty($errors)) {
                    $note->set_title($new_title);
                    $note_updated = true;
                }
            }

            if ($note_updated && empty($errors)) {
                $note->set_edited_at(date('Y-m-d H:i:s'));
                $note->persist();
                $this->redirect("note", "open", $id, $encoded_label);
            }

            if ($note->is_text_note()) {
                (new View("edit_texte_note"))->show(["note" => $note, "date_interval_creation" => $date_interval_creation, "date_interval_edit" => 
                $date_interval_edit, "errors" => $errors,"new_title" => $new_title, "encoded_label" => $encoded_label]);
            } else {
                (new View("edit_checklist_note"))->show(["note" => $note, "date_interval_creation" => $date_interval_creation, "date_interval_edit" => 
                $date_interval_edit, "errors" => $errors, "new_title" => $new_title, "encoded_label" => $encoded_label]);
            }
        } else {
            Tools::abort("Wrong/missing param or action no permited");
        }
    }


    public function move_notes(): void {
        if (isset($_POST["right"])) {
            $direction = "right";
        } elseif (isset($_POST["left"])) {
            $direction = "left";
        } else {
            $this->redirect();
        }

        $note_id = $_POST[$direction];
        $note = Note::get_note_by_key($note_id);

        $note->move_note($direction);

        $this->redirect();
    }

    public function update_pos_notes() : void {
        // check :> si il passe en pinned/unPinned
        $user = $this->get_user_or_redirect();
        $notes = $user->get_notes_pinned_unPinned();

        if (isset($_POST["new_weights"])) {

        $new_weights = $_POST["new_weights"];
        $max = count($new_weights);

            if (!empty($new_weights)) {
                foreach ($new_weights as $note) {
                    $id = Note::get_note_by_key($note['id']);

                    // Recherche de la note correspondante dans le tableau des notes
                    foreach ($notes as $existing_note) {
                        // on triche pour ne pas avoir de pb d'unicité
                        $existing_note->transition_weight($id,$max++);
                    }
                }

                $max = count($new_weights) + 1;

                foreach ($new_weights as $note) {
                    $id = Note::get_note_by_key($note['id']);
                    $pinned = $note['pinned'];

                    foreach ($notes as $existing_note) {
                        $existing_note->new_pos_notes($id,$max,$pinned);
                    }
                    --$max;
                }
            }
        } else 
            $this->redirect();
    }

    public function check_item() : void{
        //$id_note = ???;
        if(isset($_POST['checker'])) {

            $id_item = $_POST['checker'];
            $encoded_label = isset($_GET['param1']) ?  $_GET['param1'] : false;

            $item = ChecklistNoteItem::get_checklistNoteItem_by_key($id_item);
            $item->check_item();
            $this->redirect("Note", "open", $item->get_checklist_note(), $encoded_label);
        } else {
            $this->redirect();
        }
    }

    public function uncheck_item() : void {

        if (isset($_POST['checker'])) {
            $id_item = $_POST['checker'];
            $encoded_label = isset($_GET['param1']) ?  $_GET['param1'] : false;

            $item = ChecklistNoteItem::get_checklistNoteItem_by_key($id_item);
            $item->uncheck_item();
            $this->redirect("Note", "open", $item->get_checklist_note(), $encoded_label);
        } else
            $this->redirect();
    }

    public function check_uncheck_item() : void {

        $this->get_user_or_redirect();

        if(isset($_POST['noteId'])) {
            $checked = $_POST['noteId']; 
            $item = ChecklistNoteItem::get_checklistNoteItem_by_key($checked);

            $data = array(
                'id' => $checked,
                'checked' => $item->is_checked_bis(),
            );

            $item->check_uncheck_item();

            $jsonMessage = json_encode($data);
            echo $jsonMessage;
        } else {
            $this->redirect();
        }
    }

    // icon sauvegarde :
    public function add_note() : void {
        $user = $this->get_user_or_redirect();
        $title ='';
        $texte ='';
        $errors = [];

        if (isset($_POST['titre']) && !empty($_POST['titre']) || isset($_POST['texte'])) {
            $title = $_POST['titre'];

            // Vérifie si le texte est présent dans la requête POST
            if (isset($_POST['texte'])) {
                $texte = $_POST['texte'];
            } else {
                $texte = "";
            }

            $created = date('Y-m-d H:i:s');

            $new_weight = Note::get_max_weight_all_notes($user->get_id()) + 1;
            $new_ID = Note::note_latest_ID() + 1;

            $textnote = new TextNote($new_ID,$title,$user->get_id(),$created,null,0,0,$new_weight,$texte);

            $errors = $textnote->validate();
            if(empty($errors)){

                $textnote->persist();
                $this->redirect("note","open",$new_ID);
            }
        }
        (new View("add_note"))->show(["owner" => $user, "title" => $title, "texte" => $texte, "errors" => $errors]);

    }

    public function shared() : void {
        $user = $this->get_user_or_redirect();
        if (isset($_GET['param1']) && User::get_user_by_key($_GET['param1'])) {
            $id = $_GET['param1'];
//must sort notes by as editor and as reader and give options accordingly
            $notes_editor = $user->user_get_shared_notes($id, 1);
            $notes_reader = $user->user_get_shared_notes($id, 0);
            $name_share = User::get_user_by_key($id)->get_full_name();
            (new View("shared"))->show(["user" => $user, "notes_editor" => $notes_editor, "notes_reader" => $notes_reader, "name_share" => $name_share]);
        } else $this->redirect();
    }


    public function add_item_JS() : void {

        if (isset($_POST['new_item']) && isset($_POST['note_id'])) {
            if ($this->user_logged() && $this->user_can(Note::get_note_by_key($_POST['note_id']))){
                $new_item = $_POST['new_item'];
                $note_id = $_POST['note_id'];
                $item = new ChecklistNoteItem(0, $note_id, $new_item, 0);
                $item -> persist();

                $items = ChecklistNoteItem::get_chkItem_as_json($note_id, $new_item);
                self::update_edited();

                echo $items;
            }
        }
    }


    public function del_item_JS() : void  {

        if (isset($_POST['note_id']) && isset($_POST['item_id'])) {
            if ($this->user_logged() && $this->user_can(Note::get_note_by_key($_POST['note_id']))){
                $delete_item = ChecklistNoteItem::get_checklistNoteItem_by_key($_POST['item_id']);
                $delete_item -> delete_item();

                $json = [];
                $json["id"] = $delete_item->get_id();
                $json["checklist_note"] = $delete_item->get_checklist_note();
                $json["content"] = $delete_item->get_content();
                $json["checked"] = $delete_item->get_checked();
                self::update_edited();



                echo json_encode($json);

            }
        }
    }

    public function check_duplicate_title_note_JS() : void{

        if (isset($_POST['title']) && isset($_POST['note_id'])) {
            if ($this->user_logged() && $this->user_can(Note::get_note_by_key($_POST['note_id']))){
                $is_duplicate = Note::is_duplicate($this -> get_user_or_false() -> get_id(),$_POST['title'],$_POST['note_id'] );

                echo json_encode($is_duplicate);
            }
        }
    }

    public function save_all_JS() : void{
        if (isset($_POST['list_item']) && isset($_POST['title']) && isset($_POST['note_id'])) {
            if ($this->user_logged() && $this->user_can(Note::get_note_by_key($_POST['note_id']))){

                $is_changed = false;

                foreach ($_POST['list_item'] as $key => $value) {

                    if(!ChecklistNoteItem::is_valid_item_string_edit_js($value)){
                        $chk_list_item = ChecklistNoteItem::get_checklistNoteItem_by_key($key);
                        if($chk_list_item->content != $value){
                            $chk_list_item->set_content($value);
                            $chk_list_item->persist();
                            $is_changed = true;
                        }
                    }
                }
                $chk_note = ChecklistNote::get_note_by_key($_POST['note_id']);

                if($_POST['title'] != $chk_note->get_title()){
                    $chk_note->set_title($_POST['title']);
                    $chk_note->persist();
                    $is_changed = true;
                }

                if($is_changed){
                    self::update_edited();
                }

                $note = Note::get_note_by_key($_POST['note_id']);
                $time_zone = Tools::get_time_zone();
                $now = Tools::get_datetime();
                $date_interval_edit = $note->get_Edited_At() ? $this->date_interval(new DateTime($note->get_Edited_At(), $time_zone), $now) : "Never Edited";


                $data = array(
                    'is_changed' => $is_changed,
                    'date_interval_edit' => $date_interval_edit
                );

                echo json_encode($data);
            }
        }
    }

    public function get_sorted_items_service(): void
    {
        $this->get_user_or_redirect();

        if (isset($_GET['param1'])) {
            $id_item = $_GET['param1'];
            $checklist = ChecklistNote::get_checklistnote_by_item($id_item);


            if (self::user_can_viewer($checklist)) {
                $checklistItems = $checklist->get_checklist_items_by_checked();
                $jsonResponse = json_encode($checklistItems);
                echo $jsonResponse;
            }

        }
    }

    private function user_can_viewer(Note $note) : bool {
        $user = $this->get_user_or_redirect();
        $res = false;
        $res = $res || $note -> get_owner() == $this ->get_user_or_false() -> get_id();
        $res = $res || $user -> is_reader($note -> get_id());
        return $res;

    }

    private function user_can(Note $note) : bool{
        $res = false;
        $res = $res || $note -> get_owner() == $this ->get_user_or_false() -> get_id();
        $res = $res || $note -> is_editor_note($note -> get_id(), $this -> get_user_or_false() ->  get_id());
        return $res;
    }

    private function update_edited() : void  {
        $note = Note::get_note_by_key($_POST['note_id']);
        $note->update_edit_at();
    }

    public function check_unicity_service() : void {
        $res = "true";
        $user = $this->get_user_or_redirect();
        if(isset($_POST["titre"]) && $_POST["titre"] !== "" && !Note::check_unicity($_POST["titre"], $user->get_id(), 0)){
            $res = "false";
        }
        echo $res;
    }

}