<?php

require_once "framework/Model.php";


class NoteShare extends Model {

    private int $note_id;
    private int $user_id;
    private bool $editor;


    public function __construct($note, $user, $editor){
        $this->note_id = $note;
        $this->user_id = $user;
        $this->editor = $editor;
    }
    // Add getters for each property


    public function get_note_id() : int {
        return $this->note_id;
    }


    public function get_user_id() : int {
        return $this->user_id;

    }

    public function get_editor() : bool {

        return $this->editor;
    }
    public function set_editor(bool $editor) : void {
        $this->editor = $editor;
    }

    public function delete()  {

        self::execute('DELETE FROM note_shares WHERE note = :note AND user = :user',
            ['note' => $this->note_id, 'user' => $this -> user_id]);

    }

    public static function get_noteShare_by_note_id_user_id(int $note, int $user) : NoteShare | false {
        $query = self::execute("SELECT * FROM note_shares where note = :note AND user = :user",
            ['note' => $note, 'user' => $user]);
        if ($query->rowCount() == 0) {
            return false;
        } else {
            $data = $query->fetch();
            return new NoteShare($data["note"],$data["user"],$data["editor"]);
        }
    }
    public static function get_noteShare_by_all($note, $user, $editor) : NoteShare | false {
        $query = self::execute("SELECT * FROM note_shares where note = :note AND user = :user AND editor = :editor",
            ['note' => $note, 'user' => $user, 'editor' => $editor]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new NoteShare($data["note"],$data["user"],$data["editor"]);
        }
    }
    public function persist() : NoteShare {
        
        $editor_ternary = (!$this->get_editor() ? 0 : 1);

        if(self::get_noteShare_by_note_id_user_id($this->note_id, $this->user_id))
            self::execute("UPDATE note_shares SET note = :note , user = :user , editor = :editor WHERE note=:note AND user= :user ", 
            ["note"=>$this->note_id, "user"=>$this->user_id, "editor"=> $editor_ternary]);
        else
            self::execute("INSERT INTO note_shares(note,user,editor) VALUES(:note, :user, :editor)", 
            ['note' => $this->note_id, 'user' => $this->user_id, 'editor' => $editor_ternary]);
        return $this;
    }
//    public function is_shared() : bool {
//
//        $query = self::execute("SELECT * FROM note_shares WHERE note =:id",
//            ["id" => $this -> id]);
//
//        return ($query->fetch() == 1);
//    }

    public static function get_shared_by_note($id) : array | false {

        $query = self::execute('SELECT *
                                    FROM note_shares ns
                                    WHERE ns.note = :id',
            ["id" => $id]);

        if ($query->rowCount() == 0) {

            return false;

        } else {

            $data = $query ->fetchAll();
            $res = [];

            foreach($data as $row){

                $current = new NoteShare($row['note'], $row['user'], $row['editor']);
                $res [] = $current;

            }

            return $res;

        }
    }
    public function get_full_user_by_id() : User {
        $query = self::execute('SELECT *
                                    FROM users u
                                    WHERE u.id = :id;',["id" => $this -> get_user_id()]);
        $data = $query ->fetch();

        return new User($data["mail"],
                        $data["hashed_password"],
                        $data["full_name"],
                        $data["role"],
                        $data["id"]);

    }

}