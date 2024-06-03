<?php


class TextNote extends Note{
    private ?string $content;

    // Constructor
//must contain all text note AND note attributes
    public function __construct($id, $title, $owner, $created_at, $edited_at, $pinned, $archived, $weight, $content) {
        parent::__construct($id, $title, $owner, $created_at, $edited_at, $pinned, $archived, $weight);
        $this->content = $content;
        $this->sort_of_note = SortOfNote::TextNote;
    }

    protected function to_array() {
        return ['id' => $this->get_id(), 'title' => $this->get_title(), 'content' => $this->get_content(), 'labels' => $this->get_labels()];
    }

    // Getter methods

    public function get_content() : ?string{
        return $this->content;
    }

    // Setter methods (if needed)
    public function set_content($content) : void {
        $this->content = $content;
    }

    public function delete() : TextNote|false {
        if (self::get_note_by_key($this->id)) {
            $id = $this->get_id();
            self::execute('DELETE FROM note_labels WHERE note = :id', 
                ['id' => $id]);
            self::execute('DELETE FROM note_shares WHERE note = :id',
                ['id' => $id]);
            self::execute('DELETE FROM text_notes WHERE id = :id',
                ['id' => $id]);
            self::execute('DELETE FROM notes WHERE id = :id',
                ['id' => $id]);
    
            return $this;
        }
        return false;
    }

    public function validate() : string {
         $errors = self::validate_note_title($this->title, $this->owner, 0);
         return $errors;
    }

//     public static function get_textnote_by_key($id) : TextNote|false {
// //        $query = self::execute("SELECT * FROM text_notes where id = :id",
// //            ["id"=>$id]);
//         $query = self::execute("SELECT * FROM notes n 
//                                     LEFT JOIN
//                                         text_notes tn ON n.id = tn.id where n.id = :id",
//             ["id"=>$id]);
//         $data = $query->fetch();
//         if ($query->rowCount() == 0) {
//             return false;
//         } else {
//             return new TextNote($data["id"], $data["title"], $data["owner"], $data["created_at"], 
//             $data["edited_at"], $data["pinned"], $data["archived"], $data["weight"], $data["content"]);
//         }
    // }

    public function persist() : TextNote {
        if(self::get_note_by_key($this->id)) {
            
            self::execute("UPDATE notes SET title=:title, owner=:owner, created_at=:created_at, edited_at=:edited_at, pinned=:pinned, archived=:archived, weight=:weight 
                                WHERE id=:id",
            ["title" => $this->title, "owner" => $this->owner, "created_at" => $this->created_at, "edited_at" => $this->edited_at,  "pinned" => $this->pinned, "archived" => $this->archived, "weight" => $this->weight, "id" => $this->id]);
    
            self::execute("UPDATE text_notes SET content=:content 
                                WHERE id=:id",
            ["content" => $this->content, "id" => $this->id]);
        } else {
           
            self::execute("INSERT INTO notes (title, owner, created_at, edited_at, pinned, archived, weight) 
                                VALUES (:title, :owner, :created_at, :edited_at, :pinned, :archived, :weight)",
            ["title" => $this->title, "owner" => $this->owner, "created_at" => $this->created_at, "edited_at" => $this->edited_at, "pinned" => $this->pinned, 
                "archived" => $this->archived, "weight" => $this->weight]);

            $this->get_id();
    
            self::execute("INSERT INTO text_notes (id, content) 
                                VALUES (:id, :content)",
            ["id" => $this->id, "content" => $this->content]);
        }
    
        return $this;
    }
    
}

?>