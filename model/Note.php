<?php

require_once "framework/Model.php";
require_once "model/ChecklistNote.php";
require_once "model/TextNote.php";

abstract class Note extends Model {

    protected ?int $id;
    protected string $title;
    protected int $owner;
    //TYPE ??
    protected ?string $created_at;
    protected ?string $edited_at;
    protected int $pinned;
    protected int $archived;
    protected float  $weight;
    protected SortOfNote $sort_of_note;

    protected function __construct(?int $id, string $title, int $owner, $created_at, $edited_at, int $pinned, int $archived, float $weight) {
        $this->id = $id;
        $this->title = $title;
        $this->owner = $owner;
        $this->created_at = $created_at;
        $this->edited_at = $edited_at;
        $this->pinned = $pinned;
        $this->archived = $archived;
        $this->weight = $weight;
    }


    abstract protected function validate();

    abstract protected function persist();

    protected abstract function delete();

    // Getter methods
    public function get_id() : int {
        if($this->id >= 0) {
            return $this->id;
        } else {
            $id = $this->get_id_from_DB();
            $this->id = $id;
            return $id;
        }
    }

    private function get_id_from_DB()  : int {
        $note = Note::get_note_by_title($this->title);
        return $note->get_id();
    }

    public function get_title() : string {
        return $this->title;
    }

    public function get_owner() : int {
        return $this->owner;
    }

    public function get_created_at() {
        return $this->created_at;
    }

    public function get_edited_at()
    {
        return $this->edited_at;
    }

    public function is_pinned(): bool
    {
        return $this->pinned == 1;
    }

    public function is_archived(): bool
    {
        return $this->archived == 1;
    }

    public function get_weight()
    {
        return $this->weight;
    }

    public function set_weight($weight)
    {
        $this->weight = $weight;

    }

    public function pin_pong($pinned) {
        return $this->pinned = $pinned;
    }

    public function set_title($title) {
        $this->title = $title;
    }

    public function set_edited_at($editedAt) {
        $this->edited_at = $editedAt;
    }

    public function get_sort_of_note()
    {
        return $this->sort_of_note;
    }

    public static function get_notes_by_owner($owner) {
        $query = self::execute("
                SELECT
                    n.id AS note_id,
                    n.title,
                    n.owner,
                    n.created_at,
                    n.edited_at,
                    n.pinned,
                    n.archived,
                    n.weight,
                    tn.content,
                    cn.id AS checklist_id
                FROM
                    notes n
                LEFT JOIN
                    text_notes tn ON n.id = tn.id
                LEFT JOIN
                    checklist_notes cn ON n.id = cn.id
                LEFT JOIN
                    checklist_note_items cni ON cn.id = cni.checklist_note
                WHERE
                    n.owner = :owner
                "
            , ["owner" => $owner]);
        $data = $query->fetchAll();
        $return = array();
        foreach ($data as $row) {
            if ($data["checklist_id"] != NULL) {
                $return = new ChecklistNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"],
                    $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"],
                    ChecklistNoteItem::get_checklistNoteItem_by_checklist_note($data["note_id"]));
            } else {
                $return = new TextNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"],
                    $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"], $data["content"]);
            }
            return $return;
        }
    }

    public static function get_note_by_key($id) : Note|bool{
        $query = self::execute("
                SELECT
                    n.id AS note_id,
                    n.title,
                    n.owner,
                    n.created_at,
                    n.edited_at,
                    n.pinned,
                    n.archived,
                    n.weight,
                    tn.content,
                    cn.id AS checklist_id
                FROM
                    notes n
                LEFT JOIN
                    text_notes tn ON n.id = tn.id
                LEFT JOIN
                    checklist_notes cn ON n.id = cn.id
                LEFT JOIN
                    checklist_note_items cni ON cn.id = cni.checklist_note
                WHERE
                    n.id = :id
                "
            , ["id" => $id]);
        if ($query->rowCount() == 0) {
            return false;
        }
        $data = $query->fetch();
        if ($data["checklist_id"] != NULL) {
            $return = new ChecklistNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"],
                $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"],
                ChecklistNoteItem::get_checklistNoteItem_by_checklist_note($data["note_id"]));
        } else {
            $return = new TextNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"],
                $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"], $data["content"]);
        }
        return $return !=null ? $return : false;
    }

    public static function get_note_by_title($title): Note|bool {
        $query = self::execute("
                SELECT
                    n.id AS note_id,
                    n.title,
                    n.owner,
                    n.created_at,
                    n.edited_at,
                    n.pinned,
                    n.archived,
                    n.weight,
                    tn.content,
                    cn.id AS checklist_id
                FROM
                    notes n
                LEFT JOIN
                    text_notes tn ON n.id = tn.id
                LEFT JOIN
                    checklist_notes cn ON n.id = cn.id
                LEFT JOIN
                    checklist_note_items cni ON cn.id = cni.checklist_note
                WHERE
                    n.title = :title
                "
            , ["title" => $title]);
        if ($query->rowCount() == 0) {
            return false;
        }
        $data = $query->fetch();
        if ($data["checklist_id"] != NULL) {
            $return = new ChecklistNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"],
                $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"],
                ChecklistNoteItem::get_checklistNoteItem_by_checklist_note($data["note_id"]));
        } else {
            $return = new TextNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"],
                $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"], $data["content"]);
        }
        return $return !=null ? $return : false;
    }
        //abstract protected static function get_notes_by_owner($owner);

        //abstract protected static function get_note_by_key($id);

/* return Note[] */
         public static function get_all_notes_by_user_id_sorted_by_weight_desc($owner, int $archived , int $pinned) : array {
            $query = self::execute("SELECT
                n.id AS note_id,
                n.title,
                n.owner,
                n.created_at,
                n.edited_at,
                n.pinned,
                n.archived,
                n.weight,
                tn.content,
                cn.id AS checklist_id
                FROM
                    notes n
                LEFT JOIN
                    text_notes tn ON n.id = tn.id
                LEFT JOIN
                    checklist_notes cn ON n.id = cn.id
                LEFT JOIN
                    checklist_note_items cni ON cn.id = cni.checklist_note
                WHERE
                    n.owner = :owner 
                AND n.pinned = :pinned
                AND n.archived = :archived
                GROUP BY
                    n.id, n.title, n.owner, n.created_at, n.edited_at, n.pinned, n.archived, n.weight, tn.content, cn.id
                ORDER BY
                    n.weight DESC", 
            
            ["owner"=>$owner, "pinned"=>$pinned, "archived"=>$archived]);
            $data = $query->fetchAll();
            $return = array();
            foreach ($data as $row) {
                if ($row["checklist_id"] != NULL) {
                    array_push($return, new ChecklistNote($row["note_id"], $row["title"], $row["owner"], $row["created_at"], 
                            $row["edited_at"], $row["pinned"], $row["archived"], $row["weight"],
                            ChecklistNoteItem::get_checklistNoteItem_by_checklist_note($row["note_id"])));
                } else {
                    array_push($return, new TextNote($row["note_id"], $row["title"], $row["owner"], $row["created_at"], 
                    $row["edited_at"], $row["pinned"], $row["archived"], $row["weight"], $row["content"],
                                Note::get_note_by_key($row["note_id"])));
                }
            }
            return $return;
        }

        public static function get_note_pinned_and_unpinned($owner, int $archived) : array {
            $query = self::execute("SELECT
                n.id AS note_id,
                n.title,
                n.owner,
                n.created_at,
                n.edited_at,
                n.pinned,
                n.archived,
                n.weight,
                tn.content,
                cn.id AS checklist_id
                FROM
                    notes n
                LEFT JOIN
                    text_notes tn ON n.id = tn.id
                LEFT JOIN
                    checklist_notes cn ON n.id = cn.id
                LEFT JOIN
                    checklist_note_items cni ON cn.id = cni.checklist_note
                WHERE
                    n.owner = :owner 
                AND n.archived = :archived
                GROUP BY
                    n.id, n.title, n.owner, n.created_at, n.edited_at, n.pinned, n.archived, n.weight, tn.content, cn.id
                ORDER BY
                    n.weight DESC",
            ["owner"=>$owner, "archived"=>$archived]);
            $data = $query->fetchAll();
            $return = array();
            foreach ($data as $row) {
                if ($row["checklist_id"] != NULL) {
                    array_push($return, new ChecklistNote($row["note_id"], $row["title"], $row["owner"], $row["created_at"], 
                            $row["edited_at"], $row["pinned"], $row["archived"], $row["weight"],
                            ChecklistNoteItem::get_checklistNoteItem_by_checklist_note($row["note_id"])));
                } else {
                    array_push($return, new TextNote($row["note_id"], $row["title"], $row["owner"], $row["created_at"], 
                    $row["edited_at"], $row["pinned"], $row["archived"], $row["weight"], $row["content"],
                                TextNote::get_note_by_key($row["note_id"])));
                }
            }
            return $return;
        }

        public function get_notes_from_weight($weight) : ChecklistNote|TextNote {
            $query = self::execute("
                    SELECT
                        n.id AS note_id,
                        n.title,
                        n.owner,
                        n.created_at,
                        n.edited_at,
                        n.pinned,
                        n.archived,
                        n.weight,
                        tn.content,
                        cn.id AS checklist_id
                    FROM
                        notes n
                    LEFT JOIN
                        text_notes tn ON n.id = tn.id
                    LEFT JOIN
                        checklist_notes cn ON n.id = cn.id
                    LEFT JOIN
                        checklist_note_items cni ON cn.id = cni.checklist_note
                    WHERE
                        n.weight = :weight
                    ",
                ["weight" => $weight]);
            $data = $query->fetch();
            $return = array();
            if ($data["checklist_id"] != NULL) {
                $return = new ChecklistNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"],
                    $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"],
                    ChecklistNoteItem::get_checklistNoteItem_by_checklist_note($data["note_id"]));
            } else {
                $return = new TextNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"],
                    $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"], $data["content"]);
            }
            return $return;
        }
    
        public static function has_notes($owner, $pinned) : bool {
            $query = self::execute("SELECT COUNT(*) as note_count FROM notes WHERE owner = :owner AND pinned = :pinned AND archived = 0",
                ["owner" => $owner, "pinned" => $pinned]);
            $data = $query->fetch();
            
            return $data['note_count'] > 0;
        }

        public static function has_archived_note($owner) {
            $query = self::execute("SELECT COUNT(*) as note_count FROM notes WHERE owner = :owner AND archived = 1",
                ["owner" => $owner]);
            $data = $query->fetch();
            
            return $data['note_count'] > 0;
        }

         public static function get_max_weight_all_notes($owner) : int {
            $query = self::execute("SELECT MAX(weight) AS max_weight FROM notes WHERE owner = :owner", ["owner" => $owner]);
            $max_weight_data = $query->fetch();
            return (int)$max_weight_data['max_weight'];
        }

        public function get_max_weight() : int {
            $query = self::execute("SELECT MAX(weight) AS max_weight FROM notes WHERE owner = :owner AND pinned = :pinned AND archived = :archived",
                ["owner" => $this->owner, "pinned" => $this->pinned, "archived" => $this->archived]);
            $max_weight_data = $query->fetch();
            return (int)$max_weight_data['max_weight'];
        }

        public function get_min_weight() : int {
            $query = self::execute("SELECT MIN(weight) AS min_weight 
            FROM notes WHERE owner = :owner 
            AND pinned = :pinned AND archived = :archived", 
            ["owner" => $this->owner, "pinned" => $this->pinned, "archived" => $this->archived]);
            $max_weight_data = $query->fetch();
            return (int)$max_weight_data['min_weight'];
        }

        public function is_max_weight() : bool {
            return $this->get_weight() < $this->get_max_weight();
        }

        public  function is_min_weight() : bool {
            return $this->get_weight() > $this->get_min_weight();
        }

        public function update_edit_at() : void {
            $edited = date('Y-m-d H:i:s');
            self::execute("UPDATE notes SET edited_at = :editedAt WHERE id = :id",
            ['editedAt' => $edited, 'id' => $this->id]);
        }

        public function set_title_in_DB(string $new_title) : void {
            self::execute("UPDATE notes SET title = :newTitle WHERE id = :id",
            ['newTitle' => $new_title, 'id' => $this->id]);
        }

        public function move_note($direction) {
            // Récupérer la note actuelle
            $current_note = $this->get_note_by_key($this->get_id());
    
            if (!$current_note) {
                return false; // La note n'existe pas
            }
    
            // Récupérer la note adjacente
            $adjacent_note = self::get_adjacent_note($direction);
    
            if (!$adjacent_note) {
                return false; // Pas de note adjacente
            }
    
            // Échanger les poids
            $temp_weight_current = $current_note->get_weight();
            $temp_weight_adja = $adjacent_note->get_weight();
            
            $adjacent_note->set_weight($temp_weight_current + 0.5);
            $adjacent_note->persist();

            $current_note->set_weight($temp_weight_adja);
            $adjacent_note->set_weight($temp_weight_current);
                
            $current_note->persist();
            $adjacent_note->persist();
    
            return true;
        }
        

        public function transition_weight(Note $note_id, $new_weight) {
        
            $note_id->set_weight($new_weight + 0.5);
            $note_id->persist();
        }

        public function new_pos_notes(Note $note_id, $new_weight, $pinned) {
            $note_id->set_weight($new_weight);
            $note_id->pin_pong($pinned);
            $note_id->persist();
        }
        

        private function get_adjacent_note($direction) : Note {
            // Déterminer l'opérateur et l'ordre de tri en fonction de la direction
            if ($direction === 'right') {
                $operator = '<';
                $orderBy = 'DESC'; // Pour aller à droite, nous voulons le poids le plus élevé inférieur au poids actuel
            } else {
                $operator = '>';
                $orderBy = 'ASC'; // Pour aller à gauche, nous voulons le poids le plus bas supérieur au poids actuel
            }
        
            $query = self::execute("SELECT
                        n.id AS note_id,
                        n.title,
                        n.owner,
                        n.created_at,
                        n.edited_at,
                        n.pinned,
                        n.archived,
                        n.weight,
                        tn.content,
                        cn.id AS checklist_id
                    FROM
                        notes n
                    LEFT JOIN
                        text_notes tn ON n.id = tn.id
                    LEFT JOIN
                        checklist_notes cn ON n.id = cn.id
                    LEFT JOIN
                        checklist_note_items cni ON cn.id = cni.checklist_note
                    WHERE
                        owner = :owner
                        AND n.archived = :archived
                        AND n.pinned = :pinned
                        AND weight " . $operator . " :current_weight
                    GROUP BY
                        note_id, n.title, n.owner, n.created_at, n.edited_at, n.pinned, n.archived, n.weight, tn.content, checklist_id
                    ORDER BY
                        weight " . $orderBy . "
                    LIMIT 1;",
                ["owner" => $this->owner, "current_weight" => $this->weight, "archived" => $this->archived,"pinned" => $this->pinned]);
        
            $data = $query->fetch(); // un resulat
            $return = array();
            if ($data["checklist_id"] != NULL) {
                $return = new ChecklistNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"], 
                        $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"],
                        ChecklistNoteItem::get_checklistNoteItem_by_checklist_note($data["note_id"]));
            } else {
                $return = new TextNote($data["note_id"], $data["title"], $data["owner"], $data["created_at"], 
                $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"], $data["content"]);
            }
            return $return;
        }
        
        public static function filter_notes_pinned($notes){
            $res = [];
                foreach ($notes as $note){
                    if($note->is_pinned() && !$note->is_archived()){
                        $res[] = $note;
                    }
                }
            return $res;
        }
        public static function filter_notes_unpinned($notes){
            $res = [];
                foreach ($notes as $note){
                    if(!$note->is_pinned() && !$note->is_archived()){
                        $res[] = $note;
                    }
                }
            return $res;
        }
        public function filter_notes_archived($notes){
            $res = [];
                foreach ($notes as $note){
                    if($note->is_archived()){
                        $res[] = $note;
                    }
                }
            return $res;
        }
        
        public function filter_notes_unarchived($notes){
            $res = [];
                foreach ($notes as $note){
                    if(!$note->is_archived){
                        $res[] = $note;
                    }
                }
            return $res;
        }

        public function is_text_note() : bool {
           return $this->get_sort_of_note() == SortOfNote::TextNote;
        }
        public static function sorted_by_weight($notes){
            return usort($notes, "cmp");        
        }

        function cmp($a, $b) {
            return strcmp($a->weight, $b->weight);
        }

        public static function check_title_length(string $title): string {
            $errors = '';

            $minLength = Configuration::get("title_min_length");
            $maxLength = Configuration::get("title_max_length");

    
            if (strlen($title) < $minLength || strlen($title) > $maxLength) {
                $errors = "Title lenght must be between ".$minLength." and ".$maxLength;
            }
            return $errors;
        }

    public static function get_archived_notes($user) {
        $query = self::execute("
                    SELECT
                        n.id AS note_id,
                        n.title,
                        n.owner,
                        n.created_at,
                        n.edited_at,
                        n.pinned,
                        n.archived,
                        n.weight,
                        tn.content,
                        cn.id AS checklist_id
                    FROM
                        notes n
                    LEFT JOIN
                        text_notes tn ON n.id = tn.id
                    LEFT JOIN
                        checklist_notes cn ON n.id = cn.id
                    LEFT JOIN
                        checklist_note_items cni ON cn.id = cni.checklist_note
                    WHERE
                        n.owner = :owner AND
						n.archived = 1
                    GROUP BY
                        n.id, n.title, n.owner, n.created_at, n.edited_at, n.pinned, n.archived, n.weight, tn.content, cn.id
                    ORDER BY
                        n.weight DESC
                    ",
            ["owner" => $user]);
        $data = $query->fetchAll();
        $return = array();
        foreach ($data as $row) {
            if ($row["checklist_id"] != NULL) {
                array_push($return, new ChecklistNote($row["note_id"], $row["title"], $row["owner"], $row["created_at"],
                    $row["edited_at"], $row["pinned"], $row["archived"], $row["weight"],
                    ChecklistNoteItem::get_checklistNoteItem_by_checklist_note($row["note_id"])));
            } else {
                array_push($return, new TextNote($row["note_id"], $row["title"], $row["owner"], $row["created_at"],
                    $row["edited_at"], $row["pinned"], $row["archived"], $row["weight"], $row["content"]));
            }
        }
        return $return;
    }

    public static function check_duplicate(array $items): array
    {
        $errors = [];

        for ($i = 1; $i <= count($items); $i++) {

            $item_I = "item" . $i;

            if (isset($items[$item_I])) {

                foreach ($items as $key => $value) {
                    if ($value === $items[$item_I] && $key != $item_I) {

                        $err_item_I = [$item_I => 'items must be unique'];
                        $errors = array_merge($errors, $err_item_I);
                        $err_item_J = [$key => 'items must be unique'];
                        $errors = array_merge($errors, $err_item_J);
                    }
                }
            }
        }

        return $errors;
    }

    public function get_owner_name() {
        //static function get_user_by_key($id) ?
        $ownerId = $this->get_owner();  
        $query = self::execute("SELECT full_name FROM users WHERE id = :ownerId", ['ownerId' => $ownerId]);
        $result = $query->fetch();  
        return $result ? $result['full_name'] : null;
    }


    public static function note_latest_ID(): int
    {
        $res = 0;
        $query = self::execute("SELECT * FROM notes", []);
        $data = $query->fetchAll();

        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i][0] > $res) {
                $res = $data[$i][0];
            }
        }
        return $res;
    }

    public function pin_unpin(int $userId): void
    // Épingler/Désépingler une note lui donne le plus grand poids des notes épinglées/désépinglées
    {
        $id = $this->get_id();
        $pin = $this-> is_pinned() ? 0 : 1;
        $maxWeight = $this->get_max_weight_all_notes($userId) + 1;
        self::execute("UPDATE notes SET pinned = :pin WHERE id =:id", ["id" => $id, "pin" => $pin]);
        self::execute("UPDATE notes SET weight = :weight WHERE id =:id", ["id" => $id, "weight" => $maxWeight]);
    }

    public function archive_unarchive(int $user_id) : void
    // Désarchiver une note lui donne le plus grand poids des notes non épinglées
    // Archiver une note lui donne le plus grand des poids des notes archivées
    {
        $id = $this->get_id();
        $arch = $this->is_archived() ? 0 : 1;
        $max_weight = $this->get_max_weight_all_notes($user_id) + 1;
        self::execute("UPDATE notes SET archived = :arch WHERE id =:id",
            ["id" => $id, "arch" => $arch]);

        //désépingle la note si elle était épinglée
        if ($arch == 0) {
            self::execute("UPDATE notes set pinned = :pin WHERE id =:id",
                ["id" => $id, "pin" => 0]);
        }

        self::execute("UPDATE notes SET weight = :weight WHERE id =:id",
            ["id" => $id, "weight" => $max_weight]);
    }

    public function is_shared(): bool {
        $query = self::execute('SELECT * FROM note_shares WHERE note =:id',
            ["id" => $this->get_id()]);
        $data = $query->fetchAll();
        return count($data) > 0;
    }

    public static function get_shared_notes($id_user, $id_share_user, $editor_reader) {
        $query = self::execute("select n.id as shared_note_id
                                from notes n, note_shares ns, users u1, users u2
                                where 
                                    n.id = ns.note AND
                                    ns.user = u2.id AND
                                    n.owner = u1.id AND
                                    ns.user = :id_user AND
                                    u1.id = :id_share_user AND
                                    ns.editor = :editor_reader",
                ["id_user" => $id_user, "id_share_user" => $id_share_user, "editor_reader" => $editor_reader]);
        if ($query->rowCount() == 0) {
            return false;
        } else {
            $data = $query->fetchAll();
            $res = [];
            foreach ($data as $row) {
                array_push($res, Note::get_note_by_key($row['shared_note_id']));
                //$res = Note::getNoteByKey($row['shared_note_id']);
            }
            return $res;
        }
    }

    public static function get_shared_note($id_user, $id_share_user, $note_id) {
        $query = self::execute("SELECT DISTINCT n.id as shared_note_id
                                FROM notes n
                                JOIN note_shares ns ON n.id = ns.note
                                JOIN users u1 ON n.owner = u1.id
                                JOIN users u2 ON ns.user = u2.id
                                JOIN note_labels nl ON n.id = nl.note
                                WHERE ns.user = :id_user
                                    AND u1.id = :id_share_user
                                    AND nl.note = :label
                                ORDER BY n.weight",
                ["id_user" => $id_user, "id_share_user" => $id_share_user, "label" => $note_id]);
        if ($query->rowCount() == 0) {
            return false;
        } else {
            $data = $query->fetchAll();
            $res = [];
            foreach ($data as $row) {
                array_push($res, Note::get_note_by_key($row['shared_note_id']));
                //$res = Note::getNoteByKey($row['shared_note_id']);
            }
            return $res;
        }
    }

    public static function is_editor_note($id_note, $id_user) : bool {
        $query = self::execute("SELECT editor
                                FROM note_shares
                                WHERE
                                    note = :id_note AND
                                    user = :id_user",
            ["id_note" => $id_note, "id_user" => $id_user]);
        if ($query->rowCount() == 0) {
            return false;
        } else {
            if ($query->fetch()['editor'] == 1) {
                return true;
            } else return false;
        }
    }


    public static function is_duplicate(int $owner, string $title, int $note_id) : bool {
        $query = self::execute("SELECT *
                                    FROM notes
                                    WHERE title = :title AND
                                          owner = :owner AND
                                           id <> :note_id",
            ["title" => $title, "owner" => $owner, "note_id" => $note_id]);

        if ($query->rowCount() == 0) {
            return false;
        } else {
            return true;
        }
    }
    public static function is_shared_note($id_note, $id_user) : bool {
        $query = self::execute("SELECT editor
                                FROM note_shares
                                WHERE
                                    note = :id_note AND
                                    user = :id_user",
            ["id_note" => $id_note, "id_user" => $id_user]);
        return $query->rowCount() != 0;

    }

    public static function validate_note_title(String $title, int $user_id) : string {
        $errors = '';

        $minLength = Configuration::get("title_min_length");
        $maxLength = Configuration::get("title_max_length");

        if(empty($title)) {
            $errors = "Title must be filled";
        } else if(!self::check_unicity($title, $user_id)) {
            $errors = "Title must be unique";
        } else if(strlen($title) < $minLength || strlen($title) > $maxLength){
            $errors = "Title lenght must be between ".$minLength." and ".$maxLength;
        }
        return $errors;
    }

    //return true if unique
    public static function check_unicity(string $title, int $user_id) : bool {
        $query = self::execute("SELECT *
                                FROM notes
                                WHERE title = :title AND
                                    owner = :owner",
            ["title" => $title, "owner" => $user_id]);
        return $query->rowCount() == 0;

    }
    public static function validate_note_title_edit(String $title, int $user_id, int $note_id) : string {
        $errors = '';
        $minLength = Configuration::get("title_min_length");
        $maxLength = Configuration::get("title_max_length");

        if(empty($title)) {
            $errors = "Title must be filled";
        } else if(!self::check_unicity_edit($title, $user_id, $note_id)) {
            $errors = "Title must be unique";
        } else if(strlen($title) < $minLength || strlen($title) > $maxLength){
            $errors = "Title lenght must be between ".$minLength." and ".$maxLength;
        }
        return $errors;
    }

    public static function check_unicity_edit($title, $user_id, $note_id) : bool {
        $query = self::execute("SELECT *
                                FROM notes
                                WHERE title = :title AND
                                    owner = :owner AND
                                    id <> :note_id",
            ["title" => $title, "owner" => $user_id, "note_id" => $note_id]);
        return $query->rowCount() == 0;

    }


    public function get_labels() : array  {
        $res = [];
        $query = self::execute("select *
                                FROM note_labels
                                WHERE note = :id", 
            ["id" => $this->id]);
        if ($query->rowCount() == 0) {
            return [];
        } else {
            $data = $query->fetchAll();
            foreach ($data as $row) {
                array_push($res, $row['label']);
            }
            return $res;
        }
    }

    public function get_labels_json() : string  {
        $res = [];
        $query = self::execute("select *
                                FROM note_labels
                                WHERE note = :id",
            ["id" => $this->id]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $row) {
            $res[] = $row;
        }
        $json = json_encode($res);

        return $json;

    }

    public function get_other_labels() : array {
        $res = [];
        $query = self::execute("SELECT DISTINCT label
                                    FROM note_labels
                                    JOIN notes
                                    ON note_labels.note = notes.id
                                    WHERE
                                    (notes.owner = :owner AND notes.id <> :note_id)
                                    AND label NOT IN
                                    (SELECT label
                                     FROM note_labels
                                     WHERE note = :note_id)",
            ["owner" => $this->owner, "note_id" => $this -> id]);

        $data = $query->fetchAll();

        foreach ($data as $row) {
            $res[] = $row['label'];
        }

        return $res;
    }

    public function get_other_labels_json() : string {
        $query = self::execute("SELECT DISTINCT label
                                    FROM note_labels
                                    JOIN notes
                                    ON note_labels.note = notes.id
                                    WHERE
                                    (notes.owner = :owner AND notes.id <> :note_id)
                                    AND label NOT IN
                                    (SELECT label
                                     FROM note_labels
                                     WHERE note = :note_id)",
            ["owner" => $this->owner, "note_id" => $this -> id]);

        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $row) {
            $res[] = $row;
        }
        $json = json_encode($res);

        return $json;

    }

    public static function get_labels_by_user($id_user) : array {
        $res = [];
        $query = self::execute("SELECT DISTINCT nl.label
                                FROM notes n
                                JOIN note_labels nl ON n.id = nl.note
                                LEFT JOIN note_shares ns ON n.id = ns.note
                                WHERE n.owner = :id_user OR ns.user = :id_user
                                ORDER BY nl.label", 
            ["id_user" => $id_user]);
        if ($query->rowCount() == 0) {
            return $res;
        } else {
            $data = $query->fetchAll();
            foreach ($data as $row) {
                $res[] = $row['label'];
            }
            return $res;
        }
    }

    public function delete_label(string $label_content) : bool {
        $query_del = self::execute("DELETE FROM note_labels WHERE note = :id AND label = :label_content",
            ["id" => $this->id, "label_content" => $label_content]);

        if($query_del -> rowCount() == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function add_label(string $label_content)  : bool {
        $query_add = self::execute("INSERT INTO note_labels(note, label) VALUES( :id,:label_content)",
            ["id" => $this->id, "label_content" => $label_content]);

        if($query_add -> rowCount() == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function is_duplicate_label(String $label_content) : bool {

             $query = self::execute("SELECT * FROM note_labels WHERE note = :id AND label = :label", ["id" => $this->id, "label" => $label_content]);

           return $query -> rowCount() != 0;

    }

    public static function get_notes_by_label($labels, $user_id) : Array {
        $res = [];

        if ($labels != null) {
            $label_condition = "";
            $params = ["user_id" => $user_id];
            foreach ($labels as $index => $label) {
                if($index > 0) {
                    $label_condition .= " AND
                                        n.id IN(select nl.note
                                                FROM note_labels nl
                                                where nl.label = :label$index)";
                } else {
                    $label_condition .= " n.id IN(select nl.note
                                                FROM note_labels nl
                                                where nl.label = :label$index)";
                }
                $params["label$index"] = $label;
            }
            $label_condition .= ")";
            
            $sql = "SELECT n.id 
                    FROM notes n 
                    WHERE n.owner = :user_id AND(
                    $label_condition
                    GROUP BY n.id
                    ORDER BY n.weight DESC";
            $query = self::execute($sql,$params);

            if ($query->rowCount() > 0) {
                $data = $query->fetchAll();
                foreach ($data as $row) {
                    array_push($res, Note::get_note_by_key($row['id']));
                }
            }
        } 

        return $res; 
    }

    public static function get_notes_by_label_service($labels, $user_id) : Array {
        $res = [];

        if ($labels != null) {
            $label_condition = "";
            $params = ["user_id" => $user_id];
            foreach ($labels as $index => $label) {
                if($index > 0) {
                    $label_condition .= " AND
                                        n.id IN(select nl.note
                                                FROM note_labels nl
                                                where nl.label = :label$index)";
                } else {
                    $label_condition .= " n.id IN(select nl.note
                                                FROM note_labels nl
                                                where nl.label = :label$index)";
                }
                $params["label$index"] = $label;
            }
            $label_condition .= ")";
            
            $sql = "SELECT n.id 
                    FROM notes n 
                    WHERE n.owner = :user_id AND(
                    $label_condition
                    GROUP BY n.id
                    ORDER BY n.weight DESC";
            $query = self::execute($sql,$params);
            

            if ($query->rowCount() > 0) {
                $data = $query->fetchAll();
                foreach ($data as $row) {
                    $note = Note::get_note_by_key($row['id']);
                    array_push($res, $note->to_array());
                }
            } else return [];
        }

        return $res; 
    }

    public static function get_shared_notes_by_label($labels, $user_id) : Array {
        if ($labels != null) {
            $label_condition = "";
                $params = ["user_id" => $user_id];
                foreach ($labels as $index => $label) {
                    if($index > 0) {
                        $label_condition .= " AND
                                            n.id IN(select nl.note
                                                    FROM note_labels nl
                                                    where nl.label = :label$index)";
                    } else {
                        $label_condition .= " n.id IN(select nl.note
                                                    FROM note_labels nl
                                                    where nl.label = :label$index)";
                    }
                    $params["label$index"] = $label;
                }
                $label_condition .= ")";
                
                $sql = "SELECT n.*, u.full_name as owner_name 
                        FROM notes n
                            JOIN note_shares ns ON n.id = ns.note
                            JOIN users u ON n.owner = u.id

                        WHERE ns.user = :user_id AND(
                        $label_condition
                        ORDER BY n.weight DESC";
                $query = self::execute($sql,$params);

            if ($query->rowCount() > 0) {
                $data = $query->fetchAll();
                $res = [];
                foreach ($data as $row) {
                    $note = Note::get_note_by_key($row['id']);
                    if ($note) {
                        $res[] = $note;
                    }
                }
                return $res;
            }
            return []; 
        }
    }

    public static function get_shared_notes_by_label_service($labels, $user_id) {
        $res = [];

        if ($labels != null) {
            $label_condition = "";
            $params = ["user_id" => $user_id];
            foreach ($labels as $index => $label) {
                if($index > 0) {
                    $label_condition .= " AND
                                        n.id IN(select nl.note
                                                FROM note_labels nl
                                                where nl.label = :label$index)";
                } else {
                    $label_condition .= " n.id IN(select nl.note
                                                FROM note_labels nl
                                                where nl.label = :label$index)";
                }
                $params["label$index"] = $label;
            }
            $label_condition .= ")";
            
            $sql = "SELECT n.*, u.full_name as owner_name 
                    FROM notes n
                        JOIN note_shares ns ON n.id = ns.note
                        JOIN users u ON n.owner = u.id

                    WHERE ns.user = :user_id AND(
                    $label_condition
                    ORDER BY n.weight DESC";
            $query = self::execute($sql,$params);

        if ($query->rowCount() > 0) {
            $data = $query->fetchAll();
            $res = [];
            foreach ($data as $row) {
                $note = Note::get_note_by_key($row['id']);
                array_push($res, $row['owner'], $note->to_array());
            }
            return $res;
        }
        return []; 
        }

        return $res;
    }
    
}

?>
