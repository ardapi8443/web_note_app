<?php
    function view_notes_with_button($note_name, $with_button_bool, $owner_or_editor_bool, $search_mode = false) {

      if ($note_name != false) {
       
        foreach ($note_name as $note): 
        $id = $note->get_id();
        $title = $note->get_title();
        $labels = $note->get_labels();

        $form = "<form action='note/show_note' method='post'>
                  <input type='hidden' name='param1' value=' $id ' >
                  <input type='hidden' name='param2' value=' $search_mode ' > 
                  <button type='submit' class='card-header font-weight-bold h4' style='color: #89CFF0; background-color: transparent; text-decoration : underline; border :none;'> $title </button>
              </form>";

        echo "
        <div data-note-id=" .  $note->get_id(). " class='col-md-6 mb-3'>
          <div  class='card text-white bg-dark border-white' style='height: 350px;'>
                    <div class='card-body'>";
                        if ($note instanceof TextNote) { 
                          $content = Tools::truncateText($note->get_content(), 100);
                          if ($owner_or_editor_bool == true) {
                            echo " $form
                              <p class='card-text custom-text-size'>$content</p>";
                          } else {
                            echo " $form
                              <p class='card-text custom-text-size'>$content</p>";
                          }
                        } else if($note instanceof ChecklistNote) {
                          if ($owner_or_editor_bool == true) {
                            echo $form;
                          } else {
                            echo $form;
                          }
                            echo "
                              <p class='card-text custom-text-size'>
                              <ul class='list-unstyled'>";
                            $items = $note->get_checklist_item();
                            for($i = 0 ; $i < 3 && sizeof($items) > $i; ++$i) {
                                $cheked = $items[$i]->is_checked($items[$i]->get_id());
                                get_checklist_row($items[$i], $i);
                            }
                            if(sizeof($items) > 3)
                                echo '<li>...</li>';
                            echo "</ul>";
                        } 
                    echo "</div>";
                      echo '<div class="d-flex flex-row align-items-center justify-content-center p-3">';
                      foreach ($labels as $label) {
                          echo "<button class='btn btn-primary m-1'>$label</button>";
                      }
                      echo '</div>';
                    if($with_button_bool) { 
                        
                        echo "
                        <div class='card-footer d-flex justify-content-between cardFooter'>
                             <form action='note/move_notes' method='post'>
                                <input type='hidden' name='left' value=$id>";
                                if ($note->is_max_weight()): echo "
                                    <button class='btn btn-link text-primary p-0' type='submit' style='font-size: 30px; text-decoration: none;'>&lt;&lt;</button>";
                                endif;
                            echo "
                            </form>
                            <form action='note/move_notes' method='post'>
                                <input type='hidden' name='right' value=$id>";
                                if ($note->is_min_weight()): echo "
                                    <button class='btn btn-link text-primary p-0' type='submit' style='font-size: 30px; text-decoration: none;'>&gt;&gt;</button>";
                                endif;
                            echo "
                            </form>
                        </div>";
                    }
                        
                echo "</div>
            </div>";
        endforeach;
      }
    }

    function get_checklist_row($item, $i) {
        $ckeck = "";
        $content = $item->get_content();
        if ($item->is_checked()) {
            $ckeck = "checked";
        }
        $checker_id = 'checker'.$item->get_id().$i;

        echo "<li>
                <input type='checkbox' id=$checker_id name=$checker_id $ckeck disabled>
                <label for=$checker_id>$content</label>
            </li>";
    }

    function navbar($user) {
      $share = $user->get_id_and_name_share_notes_with_us();
        echo "
        <div class='offcanvas offcanvas-start' tabindex='-1' id='navbarToggleExternalContent' aria-labelledby='navbarLabel'>
      <div class='offcanvas-header bg-dark text-white'>
        <h5 class='offcanvas-title' id='navbarLabel'>NoteApp</h5>
        <button type='button' class='btn-close btn-close-white' data-bs-dismiss='offcanvas' aria-label='Close'></button>
      </div>
      <div class='offcanvas-body bg-dark text-warning'>
        <ul class='navbar-nav'>
          <li class='nav-item'>
            <a class='nav-link active' aria-current='page' href='main'>My notes</a>
          </li>
          <li class='nav-item'>
            <a class='nav-link active' aria-current='page' href='label/search_my_notes'>Search</a>
          </li>
          <li class='nav-item'>
            <a class='nav-link' href='note/archive_notes'>My archives</a>
          </li>";
            if($share != false) {
                foreach ($share as $row) {
                    $name = $row['full_name'];
                    $id = $row['id'];
                    echo "<li class='nav-item'>
                      <a class='nav-link' href='note/shared/$id'>Shared by $name</a>
                      </li>";
                }
            }

          echo "
          <li class='nav-item'>
            <a class='nav-link' href='settings'>Settings</a>
          </li>
        </ul>
      </div>
    </div>

    

<!-- Navbar -->
<nav class='navbar navbar-dark bg-dark'>
  <div class='container-fluid'>
    <!-- Button that triggers the offcanvas menu -->
    <button class='navbar-toggler' type='button' data-bs-toggle='offcanvas' data-bs-target='#navbarToggleExternalContent' aria-controls='navbarToggleExternalContent' aria-expanded='false' aria-label='Toggle navigation'>
      <span class='navbar-toggler-icon'></span>
    </button>
  </div>
</nav>";
    }
?>
    