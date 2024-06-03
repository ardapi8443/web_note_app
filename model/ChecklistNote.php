<?php
require_once "framework/Model.php";
require_once "model/ChecklistNoteItem.php";
require_once "model/SortOfNote.php";
require_once 'model/Note.php';

// snake_case for method name (no mix)
Class ChecklistNote extends Note {

    public $checklist_item = [];

    public function __construct($id, $title, $owner, $created_at, $edited_at, $pinned, $archived, $weight, $checklistItem) {
        parent::__construct($id, $title, $owner, $created_at, $edited_at, $pinned, $archived, $weight);
        $this->checklist_item = $checklistItem;
        $this->sort_of_note = SortOfNote::ChecklistNote;
    }

    protected function to_array() {
        return ['id' => $this->get_id(), 'title' => $this->get_title(), 'items' => $this->checklist_item, 'labels' => $this->get_labels()];
    }

    public function get_checklist_item() : array {
        if (!$this->checklist_item) {
            return []; // Retourne un tableau vide si aucun item n'est trouvé
        }
    
        return $this->checklist_item;
    }


    public function set_checklist_item($new_checklist_item) : void {
         $this->checklist_item = array_merge($this->checklist_item , $new_checklist_item);
    }

    public function add_item(ChecklistNoteItem $item) : ChecklistNoteItem|array {
        return $item->persist();
    }


    public function delete()   {

        $id = $this->get_id();

        self::execute('DELETE FROM note_labels WHERE note = :id', ['id' => $id]);
        self::execute('DELETE FROM note_shares WHERE note_shares.note = :id ', ['id' => $id]);
        self::execute('DELETE FROM checklist_note_items WHERE checklist_note_items.checklist_note = :id', ['id' => $id]);
        self::execute('DELETE FROM checklist_notes WHERE checklist_notes.id = :id', ['id' => $id]);
        self::execute('DELETE FROM notes WHERE notes.id = :id', ['id' => $id]);
    }

    public function get_checklist_items_by_creation() : array {
        // Requête pour récupérer les items triés par date de création
        $query = self::execute("SELECT n.*, cni.*, n.id as idnote, cni.id as idcni              
            FROM 
                notes n
            JOIN 
                checklist_notes cn ON n.id = cn.id
            JOIN 
                checklist_note_items cni ON cn.id = cni.checklist_note
            WHERE 
                n.id = :id 
            ORDER BY cni.id ASC",['id' => $this->get_id()]);

            $data = $query->fetchAll();

            $checklist_items = [];
            foreach ($data as $row) {
                $current = new ChecklistNoteItem($row['idcni'], $row['checklist_note'], $row['content'], $row['checked']);
                $checklist_items[] = $current;
            }
        
            return $checklist_items;
        }

        public function get_checklist_items_by_checked() : array {
            // Requête pour récupérer les items triés par check*
            $query = self::execute("SELECT n.*, cni.*, n.id as idnote, cni.id as idcni              
                FROM 
                    notes n
                JOIN 
                    checklist_notes cn ON n.id = cn.id
                JOIN 
                    checklist_note_items cni ON cn.id = cni.checklist_note
                WHERE 
                    n.id = :id 
                ORDER BY  cni.checked ASC, cni.id ASC",['id' => $this->get_id()]);
    
                $data = $query->fetchAll();
    
                $checklist_items = [];
                foreach ($data as $row) {
                    $current = new ChecklistNoteItem($row['idcni'], $row['checklist_note'], $row['content'], $row['checked']);
                    $checklist_items[] = $current;
                }
            
                return $checklist_items;
            }


    public static function get_checklistnote_by_item($item_id) : ChecklistNote|false {
        // Obtenir d'abord l'ID de la ChecklistNote associée à l'Item
        $query = self::execute("SELECT checklist_note FROM checklist_note_items WHERE id = :itemId", ['itemId' => $item_id]);

        $data = $query->fetch();
    
        $note_id = $data['checklist_note'];
    
        // Utiliser l'ID obtenu pour récupérer la ChecklistNote et ses Items
        $query = self::execute("SELECT n.*, cni.*, n.id as idnote, cni.id as idcni
                                FROM notes n
                                JOIN checklist_notes cn ON n.id = cn.id
                                JOIN checklist_note_items cni ON cn.id = cni.checklist_note
                                WHERE n.id = :note_id
                                GROUP BY cni.id, n.id, n.title, n.owner, n.created_at, n.edited_at, n.pinned, n.archived, n.weight, cni.checklist_note, cni.content, cni.checked",
            ["note_id" => $note_id]);
    
        $data = $query->fetchAll();
        if ($query->rowCount() == 0) {
            return false; // Aucune ChecklistNote trouvée pour cet ID
        }
    
        $checklist_item = [];
        foreach ($data as $row) {
            $current = new ChecklistNoteItem($row['idcni'], $row['checklist_note'], $row['content'], $row['checked']);
            $checklist_item[] = $current;
        }
    
        return new ChecklistNote($data[0]['idnote'], $data[0]['title'], $data[0]['owner'], $data[0]['created_at'],
            $data[0]['edited_at'], $data[0]['pinned'], $data[0]['archived'], $data[0]['weight'],
            $checklist_item);
    }
    

    public static function get_checklistnote_by_key($id) : ChecklistNote|false {

        $query = self::execute("select n.*, cni.*, n.id as idnote, cni.id as idcni
                                    from notes n
                                    join checklist_notes cn ON n.id = cn.id
                                    join checklist_note_items cni ON cn.id = cni.checklist_note
                                    where n.id = :id
                                    group by cni.id, n.id, n.title, n.owner, n.created_at, n.edited_at, n.pinned, n.archived, n.weight, cni.checklist_note, cni.content, cni.checked", ["id"=>$id]);                           
        $data = $query->fetchAll();
        if ($query->rowCount() == 0) {

            return false;

        } else {

            $checklist_item = [];

            foreach ($data as $row) {
                $current = new ChecklistNoteItem($row['idcni'], $row['checklist_note'], $row['content'], $row['checked']);
                $checklist_item[] = $current;
            }
            return new ChecklistNote($data[0]['idnote'], $data[0]['title'],$data[0]['owner'],$data[0]['created_at'],
                $data[0]['edited_at'],$data[0]['pinned'],$data[0]['archived'],$data[0]['weight'],
                $checklist_item);
        }
    }

    public static function exist($id) : bool {

        $query = self::execute("select *
                                    from checklist_notes
                                    where id = :id ", ["id"=>$id]);

        if ($query->rowCount() == 0) {

            return false;

        } else {

            return true;
        }
    }

    public function is_duplicate_item($item) : bool {
        if (self::get_checklistnote_by_key($this->id)) {
            $query = self::execute('SELECT COUNT(*) as duplic_item FROM checklist_note_items WHERE content = :item AND checklist_note = :id',
                ['item' => $item, 'id' => $this->id]);
            $data = $query->fetch();
            return $data['duplic_item'] > 0;
        } else 
            return false;
    }

    public function validate() : array {

        $errors = [];

        $contents = [];
        foreach ($this->get_checklist_item() as $item) {
            if (in_array($item->get_content(), $contents)) {
                // Si un contenu en double est trouvé
                $errors[] = "L'élément '$item' existe déjà dans la liste. Sauvegarde impossible.";
        }
        $contents[] = $item->get_content();
    }
        return $errors;
    }         
    
    public function persist() : ChecklistNote {
        if (self::exist($this->id)) {
         /*   self::execute('INSERT INTO notes(title, owner, created_at, edited_at, pinned, archived, weight)
                            VALUES(:title,:owner,:created_at,:edited_at,:pinned,:archived,:weight)',
                                ["title" => $this -> title, */

            self::execute('UPDATE notes SET title=:title, owner=:owner, created_at=:created_at, edited_at=:edited_at, pinned=:pinned, archived=:archived, weight=:weight WHERE id=:id', 
            ["id" => $this->id, "title" => $this->title, "owner" => $this->owner, "created_at" => $this->created_at, "edited_at" => $this->edited_at, "pinned" => $this->pinned, "archived" => $this->archived, "weight" => $this->weight]);
        } else {
            self::execute('INSERT INTO notes(id, title, owner, created_at, edited_at, pinned, archived, weight)
                            VALUES(:id,:title,:owner,:created_at,:edited_at,:pinned,:archived,:weight)',
                                ["id" => $this -> id,
                                "title" => $this -> title,
                                "owner" => $this -> owner,
                                "created_at" => $this -> created_at,
                                "edited_at" => $this -> edited_at,
                                "pinned" => $this -> pinned,
                                "archived" => $this -> archived,
                                "weight" => $this -> weight]);

            $id = self::get_SQL_Id();

            self::execute('INSERT INTO checklist_notes(id) VALUES(:id)', ["id" => $id]);

            foreach($this -> checklist_item as $item){
                $current = new ChecklistNoteItem(0,$id,$item -> get_content(),$item -> get_checked());
                $current -> persist();
            }
        }
        return $this;
    }


    public static function get_notes_by_owner($owner) : Array|false {
        $query = self::execute("select n.*, cni.*, cni.id as idcni
                                    from notes n
                                    join checklist_notes cn ON n.id = cn.id
                                    join checklist_note_items cni ON cn.id = cni.checklist_note
                                    where n.owner = :owner
                                    group by cni.id", ["owner"=>$owner]);

        $data = $query->fetchAll();

        $check_List_array = [];

        if ($query->rowCount() == 0) {
            return false;
        } else {

            foreach ($data as $row){
                if(in_array($row["title"], $check_List_array)){
                    $check_List_array = self::get_checklistnote_by_key($row["id"]);

                }
            }
        }
        return $check_List_array;
    }

    public function get_SQL_Id() : int{

        $query = self::execute('SELECT * from notes n WHERE n.created_at = :created_at AND title = :title',
            ["created_at" => $this -> created_at,
                "title" => $this -> title]);
        $data = $query->fetch();
        return $data["id"];
    }


}
?>