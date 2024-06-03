<?php

require_once "framework/Model.php";

class User extends Model {

        private $id;
        private $mail;
        private $hashed_password;
        private $full_name;
        private $role;

        // Constructor
        public function __construct($mail, $hashed_password, $full_name, $role, $id = 0) {
            $this->id = $id;
            $this->mail = $mail;
            $this->hashed_password = $hashed_password;
            $this->full_name = $full_name;
            $this->role = $role;
        }
        public function create_user($mail, $full_name) : User {
            $new_user = new User($mail, $this->hashed_password, $full_name,  $this->role,  $this->id);
            return $new_user;
        }
        public function create_user_PWD($pwd) : User {
            $new_user = new User($this -> mail, self::hashpwd($pwd), $this->full_name,  $this->role,  $this->id);
            return $new_user;
        }
        public function persist() : User {
            if(self::get_user_by_key($this->id))
                self::execute("UPDATE users SET mail=:mail, hashed_password=:hashed_password, full_name =:full_name ,role=:role WHERE id=:id", 
                              ["mail"=>$this->mail, "hashed_password"=>$this->hashed_password, "full_name" => $this->full_name ,"role"=>$this->role,  "id"=>$this->id]);
                              
            else
                self::execute("INSERT INTO users(mail,hashed_password,full_name,role) VALUES(:mail,:hashed_password,:full_name,:role)", 
                              ["mail"=>$this->mail, "hashed_password"=>$this->hashed_password, "full_name"=>$this->full_name, "role"=>$this->role]);
            return $this;
        }

        //getter and setter
        public function get_id() : int {
            return $this->id;
        }
    
        public function get_mail() : string{
            return $this->mail;
        }
    
        public function get_hashed_password() : string {
            return $this->hashed_password;
        }
    
        public function get_full_name() : string {
            return $this->full_name;
        }
    
        public function get_role() : string {
            return $this->role;
        }

    public static function get_user_by_key($id) : User|false {
        $query = self::execute("SELECT * FROM users where id = :id",
            ["id"=>$id]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User($data["mail"], $data["hashed_password"], $data["full_name"], $data["role"], $data["id"]);
        } 
    }


    public static function get_user_by_mail($mail) : User|false {
        $query = self::execute("SELECT * FROM users where mail = :mail",
            ["mail"=>$mail]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User($data["mail"], $data["hashed_password"], $data["full_name"], $data["role"], $data["id"]);
      
        }
    }

    public static function get_users() : array {
        $query = self::execute("SELECT * FROM users", []);
        $data = $query->fetchAll();
        $results = [];
        foreach ($data as $row) {
            $current_user = User::get_user_by_key($row['id']);
            $results[] = $current_user;
        }
        return $results;
    }


    public function get_notes_pinned() {
        return Note::get_all_notes_by_user_id_sorted_by_weight_desc($this->get_id(),0,1);
    }

    public function get_notes_pinned_unPinned() {
        return Note::get_note_pinned_and_unpinned($this->get_id(),0);
    }

    public function get_notes_unpinned() {
        return Note::get_all_notes_by_user_id_sorted_by_weight_desc($this->get_id(),0,0);
    }

    public function has_notes_pinned() {
        return Note::has_notes($this->get_id(),1);
    }

    public function has_notes_others() {
        return Note::has_notes($this->get_id(),0);
    }

    public function has_archived_notes() {
        return Note::has_archived_note($this->get_id());
    }

    public function get_archived_notes() {
        return Note::get_archived_notes($this->get_id());
    }

    public static function validate_password(string $password) : array {
        $errors = [];
        if (strlen($password) < 8 || strlen($password) > 16) {
            $errors[] = "Password length must be between 8 and 16.";
        } if (!((preg_match("/[A-Z]/", $password)) && preg_match("/\d/", $password) && preg_match("/['\";:,.\/?!\\-]/", $password))) {
            $errors[] = "Password must contain one uppercase letter, one number and one punctuation mark.";
        }
        return $errors;

    }

    public static function validate_passwords(string $password, string $password_confirm) : array {
        $errors = User::validate_password($password);
        if ($password != $password_confirm) {
            $errors[] = "You must enter the same password twice.";
        }
        return $errors;
    }

    public static function validate_unicity(string $mail) : array {
        $errors = [];
        $user = self::get_user_by_mail($mail);
        if ($user) {
            $errors[] = "This email already exists.";
        } 
        return $errors;
    }

    public static function check_password(string $clear_password, string $hash) : bool {
        return $hash === Tools::my_hash($clear_password);
    }

    public function validate() : array {
        $errors = [];
        if (!filter_var($this->mail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "This must be a valid mail addresse !";
        }
        return $errors;
    }

    public static function validate_mail(string $mail) : array {
        $errors = [];
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "This must be a valid mail addresse !";
        }
        return $errors;
    }

    public static function validate_login(string $mail, string $password) : array {
        $errors = [];
        $user = User::get_user_by_mail($mail);
        if ($user) {
            if (!self::check_password($password, $user->hashed_password)) {
                $errors[] = "Wrong password. Please try again.";
            }
        } else {
            $errors[] = "Can't find a user with the mail '$mail'. Please sign up.";
        }
        return $errors;
    }

    private static function hashpwd(string $pwd) : string {
        return Tools::my_hash($pwd);

    }

    public static function get_not_shared($note, $loggedUser) : array | false{
        $res = [];

            $query = self::execute("SELECT *
                                        FROM users u
                                        WHERE u.id NOT IN (
                                            SELECT ns.user
                                            FROM note_shares ns
                                            WHERE ns.note = :idNote
                                        )
                                        AND u.id <> :idUser",
                ["idNote" => $note -> get_id(), "idUser" => $loggedUser -> get_id()]);

            $data = $query->fetchAll();
           // $data = Tools::sort_array_of_note_by_weight($data, 'full_name'); 

            if ($query->rowCount() == 0) {
                return false;
            } else {
                foreach($data as $row){
                    $res[] =  new User($row["mail"], $row["hashed_password"], $row["full_name"], $row["role"], $row["id"]);
                }
            }

        return $res;

    }

    public function get_id_and_name_share_notes_with_us() {
        $query = self::execute("select DISTINCT u1.id, u1.full_name
                                from notes n, note_shares ns, users u1, users u2
                                where 
                                    n.id = ns.note AND
                                    ns.user = u2.id AND
                                    n.owner = u1.id AND
                                    ns.user = :id",
                ["id" => $this->get_id()]);
        if ($query->rowCount() == 0) {
            return false;
        }
        else {
            $data = $query->fetchAll();
            $data = Tools::sort_array_by_attribute($data, 'full_name');
            return $data;
        }
    }

    public function user_get_shared_notes($id_shared_user, $editor_reader) {
        return Note::get_shared_notes($this->get_id(), $id_shared_user, $editor_reader);
    }

    public function user_get_shared_note($id_shared_user, $note_id) {
        return Note::get_shared_note($this->get_id(), $id_shared_user, $note_id);
    }

    public function is_editor($id_note) {
        if (Note::is_editor_note($id_note, $this->get_id())) {
            return true;
        } else return false;
    }

    public function is_reader($id_note) {
        if (Note::is_shared_note($id_note, $this->get_id())) {
            return true;
        }else return false;
    }

    public function get_my_notes_by_labels(Array $labels) : Array {
        return array_unique(Note::get_notes_by_label($labels, $this->get_id()), SORT_REGULAR);
    }

    public function get_my_notes_by_labels_service(Array $labels) : Array {
        return Note::get_notes_by_label_service($labels, $this->get_id());
    }

    public function get_shared_notes_by_labels(Array $labels) : Array {
        return array_unique(Note::get_shared_notes_by_label($labels, $this->get_id()), SORT_REGULAR);
    }

    public function get_shared_notes_by_labels_service(Array $labels) : Array {
        return Note::get_shared_notes_by_label_service($labels, $this->get_id());
    }

    public function get_all_labels() {
        return Note::get_labels_by_user($this->get_id());
    }

    public static function get_user_full_name_service(int $user_id) {
        $user = self::get_user_by_key($user_id);
        return $user->get_full_name();
    }

}