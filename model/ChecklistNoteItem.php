<?php

require_once "framework/Model.php";

class ChecklistNoteItem extends Model {

    public $id;
    public $checklist_note_id;
    public $content;
    public int $checked;

    public function __construct($id, $checklist_note_id, $content, $checked){
        $this->id = $id;
        $this->checklist_note_id = $checklist_note_id;
        $this->content = $content;
        $this->checked = $checked;
    }

    public function get_id() : int {
        return $this->id;
    }

    public function get_content() : string {
        return $this->content;
    }

    public static function get_checklistNoteItem_by_key($id) : ChecklistNoteItem|false {
        $query = self::execute("SELECT * FROM checklist_note_items where id = :id",
            ["id"=>$id]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new ChecklistNoteItem($data["id"],$data["checklist_note"],$data["content"],$data["checked"]);
        }
    }



    public function delete_item() : ChecklistNoteItem|false {
        $checklist = self::get_checklistNoteItem_by_key($this->id);
        if ($checklist) {
            self::execute('DELETE FROM checklist_note_items WHERE checklist_note_items.id = :id',
                ['id' => $this->id]);
            return $this;
        }
        return false;
    }


    public function get_checklist_note() : int {
        return $this->checklist_note_id;
    }


    public function is_valid_item() :  array {
        $errors = [];
        $minLength = Configuration::get("item_min_length");
        $maxLength = Configuration::get("item_max_length");

        if (strlen($this->content) < $minLength || strlen($this->content) > $maxLength) {
            $errors[] = "la longeur de l'item doit être entre '$minLength' et '$maxLength' caractères";
        }
        return $errors;
    }


    public function is_valid_item_string() :  string {
        $errors = '';
        $minLength = Configuration::get("item_min_length");
        $maxLength = Configuration::get("item_max_length");

        if (strlen($this->content) < $minLength || strlen($this->content) > $maxLength) {
            $errors = "la longeur de l'item doit être entre '$minLength' et '$maxLength' caractères";
        }
        return $errors;
    }

    public function is_valid_item_string_edit(string $content) :  bool {
        $error = false;
        $minLength = Configuration::get("item_min_length");
        $maxLength = Configuration::get("item_max_length");

        if (strlen($content) < $minLength || strlen($content) > $maxLength) {
            $error = true;
        }
        return $error;
    }

    public static function is_valid_item_string_edit_js(string $content) :  bool {
        $error = false;
        $minLength = Configuration::get("item_min_length");
        $maxLength = Configuration::get("item_max_length");

        if (strlen($content) < $minLength || strlen($content) > $maxLength) {
            $error = true;
        }
        return $error;
    }


    public function set_content($content): void {

        $this->content = $content;
    }

    public function get_checked() : int {
        return $this->checked;
    }

    public static function get_checklistNoteItem_by_checklist_note($checklist_note_id) : array|false {

        $query = self::execute("SELECT * FROM checklist_note_items where checklist_note = :checklist_note",
            ["checklist_note"=>$checklist_note_id]);
        $data = $query->fetchAll();
        $checklistnote_item = [];
        foreach ($data as $row) {
            $current_note = ChecklistNoteItem::get_checklistNoteItem_by_key($row['id']);
            $checklistnote_item[] = $current_note;
        }
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return $checklistnote_item;
        }
    }

    public function is_checked() : bool {
        return $this -> checked == 1;
    }

    public function is_checked_bis() : int {
        return $this -> checked == 1;
    }
    
    public function persist() : ChecklistNoteItem {
        $checklist = self::get_checklistNoteItem_by_key($this->id);
    
        if ($checklist) {
            self::execute("UPDATE checklist_note_items SET content = :content, checked = :checked WHERE id = :id",
                ["content" => $this->content, "checked" => $this->checked, "id" => $this->id]);
        } else {
            self::execute("INSERT INTO checklist_note_items(checklist_note, content, checked) VALUES(:checklist_note, :content, :checked)",
                ["checklist_note" => $this->checklist_note_id, "content" => $this->content, "checked" => $this->checked]);
        }
        return $this;
    }
    

    public function check_item() : void {
        $id = $this -> id;
        self::execute("UPDATE checklist_note_items SET checked = 1 WHERE id =:id",
            ["id" => $id]);
        $this->checked = 1;
    }

    public function uncheck_item() : void {
        $id = $this -> id;
        self::execute("UPDATE checklist_note_items SET checked = 0 WHERE id =:id",
            ["id" => $id]);
        $this->checked = 0;
    }

    public static function get_checklistNoteItem_by_key_and_content(int $id, string $content) : ChecklistNoteItem|false {
        $query = self::execute("SELECT * FROM checklist_note_items where checklist_note = :id AND content = :content",
            ["id"=>$id, "content" => $content]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new ChecklistNoteItem($data["id"],$data["checklist_note"],$data["content"],$data["checked"]);
        }
    }

    public static function get_chkItem_as_json(int $noteId, string $itemContent) : string {

        $checklistnote_item = ChecklistNoteItem::get_checklistNoteItem_by_key_and_content($noteId, $itemContent);

        $json = [];
        $json["id"] = $checklistnote_item->get_id();
        $json["checklist_note"] = $checklistnote_item->get_checklist_note();
        $json["content"] = $checklistnote_item->get_content();
        $json["checked"] = $checklistnote_item->get_checked();

        return json_encode($json);
    }
    public function check_uncheck_item() : void {
        $id = $this -> id;
        if ($this->is_checked()) {
            self::execute("UPDATE checklist_note_items SET checked = 0 WHERE id =:id", ["id" => $id]);
            $this->checked = 0;
        } else {
            self::execute("UPDATE checklist_note_items SET checked = 1 WHERE id =:id", ["id" => $id]);
            $this->checked = 1;
        }
    }

    public static function get_duplicate_id(int $checklist_note_id,string $content) : int {
        $query = self::execute("SELECT id FROM checklist_note_items where checklist_note = :checklist_note and content = :content",
            ["checklist_note"=>$checklist_note_id, "content" => $content]);
        $data = $query->fetch();
        return $data['id'];
    }
}

?>

