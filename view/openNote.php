<?php
    function pin($id, $encoded_label) : void {
        echo
            "<form class='link' action='note/pin_unpin' method='post'>
            <input type='number' name='pinnable' value= '$id' hidden>
            <input type='hidden' name='encoded_label' value='$encoded_label'>
                <button data-bs-toggle='tooltip' data-bs-placement='right' title='pin this note'>
                    <i class=\"bi bi-pin\"></i>
                </button>      
            </form>";
        
    }

    function unpin($id, $encoded_label) : void {
        echo
            "<form class='link' action='note/pin_unpin' method='post'>
            <input type='number' name='pinnable' value= '$id' hidden>
            <input type='hidden' name='encoded_label' value='$encoded_label'>
                <button data-bs-toggle='tooltip' data-bs-placement='right' title='unpin this note'>
                    <i class=\"bi bi-pin-angle\"></i>
                </button>    
            </form>";
    }

    function archive($id, $encoded_label) : void {
        echo "
        <form class='link' action='note/archive_unarchive' method='post'>
            <input type='number' name='archivable' value= '$id' hidden>
            <input type='hidden' name='encoded_label' value='$encoded_label'>
                <button data-bs-toggle='tooltip' data-bs-placement='right' title='archive this note'>
                    <i class=\"bi bi-archive\"></i>
                </button>
            </form>";
    }

    function label($id, $encoded_label) : void {
        echo
        "<form class='link' action='label/show_label_handler' method='post'>
                <input type='number' name='label' value=$id hidden>
                <input type='hidden' name='encoded_label' value=$encoded_label >
                    <button data-bs-toggle='tooltip' data-bs-placement='right' title='handle labels'>
                       <i class=\"bi bi-ethernet\"></i>
                    </button>
                </form>";
    }

    function delete($id, $encoded_label) : void {
        echo "
            <script>
            document.onreadystatechange = function () {
                if (document.readyState === 'complete') {
                    let my_modal = new bootstrap.Modal('#myModal');
                    let two_modal = new bootstrap.Modal('#confirmDeleteDoneModal');
                    let btn = $('#btn');
                    let btnDelete;
                    btn.click(function (event) {
                            event.preventDefault();
                            my_modal.show();
                            btnDelete = $('#btnDelete');

                            btnDelete.click(function (event) {
                                my_modal.hide();
                                event.preventDefault();
                                two_modal.show();
                                btnDelete = $('#btnDelete');
                            }
                            );
                        }
                    );
                
                    
                }
            }

            function retrn() {
                window.location.href = 'note';
            }
            </script>
            <form class='link' action='note/show_delete' method='post'>
            <input type='number' name='deletable_id' value=$id hidden>
            <input type='hidden' name='encoded_label' value=$encoded_label >
            <button id ='btn' type='submit' data-bs-placement='right' title ='delete this note' class='text-danger' data-bs-toggle='modal' data-bs-target='#myModal'>
                    <i class='bi bi-trash3-fill'></i>
                </button>
            </form>
    
    <!-- Modal -->
    <div id='myModal' class='modal fade' tabindex='-1' aria-labelledby='confirmDeleteModalLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title text-primary' id='confirmDeleteModalLabel'> Please confirm </h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    <p class='text-danger'>Are you sure you want to delete this archived note? This action cannot be undone.</p>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                        Cancel
                    </button>

                    <!-- Form for submitting the note ID with the POST method -->
                    <form class='link' action='note/delete_service' method='post'>
                        <input type='number' name='deletable' value=$id hidden>
                        <input id='btnDelete' class='btn btn-danger' type='submit' value='Delete'>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Modal -->
    <div id='confirmDeleteDoneModal' class='modal fade' tabindex='-1' aria-labelledby='confirmDeleteDoneModalLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title text-primary' id='confirmDeleteDoneModalLabel'> Delete Successful </h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    <p class='text-success'>The note has been successfully deleted.</p>
                </div>
                <div class='modal-footer'>
                <form class='link' action='note/delete' method='post'>
                    <input type='number' name='deletable' value=$id hidden>
                    <input id='btnDelete' class='btn btn-primary' type='submit' value='OK'>
                </form>
                </div>
            </div>
        </div>
    </div>
        ";
    }

    function unarchive($id, $encoded_label) : void {
        echo
            "<form class='link' action='note/archive_unarchive' method='post'>
            <input type='number' name='archivable' value= '$id' hidden>
            <input type='hidden' name='encoded_label' value='$encoded_label'>
                <button data-bs-toggle='tooltip' data-bs-placement='right' title='unarchive this note'>
                    <i class=\"bi bi-archive-fill\"></i>
                </button>
            </form>";
    }

    function back_to_shared($id_user_share) {
        echo "
            <form class='flex-grow-1' action='note/shared/$id_user_share' data-bs-toggle='tooltip' data-bs-placement='right' title='get back'>
                <button data-bs-toggle='tooltip' data-bs-placement='right' title='back to shared note'>
                    <i class='bi bi-arrow-90deg-left'></i>
                   </button>
            </form>";
    }

    function back_to_search($encoded_label) {
        echo "
            <form class='flex-grow-1' action='label/search/$encoded_label' data-bs-toggle='tooltip' data-bs-placement='right' title='get back'>
                <button data-bs-toggle='tooltip' data-bs-placement='right' title='back to search note'>
                    <i class='bi bi-arrow-90deg-left'></i>
                   </button>
            </form>";
    }
?>
<div class ="container d-inline-flex justify-content-end">

        <?php
        $id = $note->get_id();
        $id_user_share = $note->get_owner();

    if($encoded_label) {
        echo back_to_search($encoded_label);
    }

    if (!$note->is_archived() && ($note->get_owner() == $user->get_id() || $editor == true)){
        if ($editor == true && $encoded_label == false) {
            echo back_to_shared($id_user_share);
        } else if ($encoded_label == false) {
        ?>
        <!-- back to my notes -->
        <form class="flex-grow-1" action="note" data-bs-toggle="tooltip" data-bs-placement="right" title="get back">
            <button data-bs-toggle='tooltip' data-bs-placement='right' title='back to home page'>
                <i class="bi bi-arrow-90deg-left"></i>
            </button>
        </form>
        <?php } ?>

        <!-- share -->
        <?php if ($note->get_owner() == $user->get_id()) { ?>
        <form action="share/open" method="post" data-bs-toggle="tooltip" data-bs-placement="right" title="share with others">
            <input type='number' name='share' value= '<?php echo $id ?>' hidden>
            <input type='hidden' name='encoded_label' value='<?php echo $encoded_label ?>'>
            <button data-bs-toggle='tooltip' data-bs-placement='right' title='share this note'>
                <i class="bi bi-share"></i>
            </button>
        </form>

        <!-- pin/unpin -->

        <?php
            if ($note->is_pinned()) {
                echo unpin($id, $encoded_label);
            } else echo pin($id, $encoded_label);
        ?>
        <!-- archived -->
        <?php
            echo archive($id,$encoded_label);
        } ?>
        <!-- label -->

        <?php
            echo label($id, $encoded_label);
        ?>

        <!-- edit --> 
        <form action="note/show_edit" method="post" data-bs-toggle="tooltip" data-bs-placement="right" title="edit this note">
            <input type='number' name='id' value= '<?php echo $id ?>' hidden>
            <input type='hidden' name='encoded_label' value='<?php echo $encoded_label ?>'>
            <button type='submit' data-bs-toggle='tooltip' data-bs-placement='right' title='edit this note'>
                <i class="bi bi-feather"></i>
            </button>
        </form>
    <?php 
    } else if ($note->is_archived() && ($note->get_owner() == $user->get_id() || $editor == true)){
        if ($editor == true && $encoded_label == false) {
            echo back_to_shared($id_user_share);
        }else if ($encoded_label == false) {
        ?>
        <!-- back to archived notes -->
        <form class="flex-grow-1" action="note/archive_notes" data-bs-toggle="tooltip" data-bs-placement="right" title="get back">
            <button data-bs-toggle='tooltip' data-bs-placement='right' title='leave this note'>
                <i class="bi bi-arrow-90deg-left"></i>
            </button>
        </form>
    <?php } 
        if ($note->get_owner() == $user->get_id()) {
            echo  delete($id,$encoded_label);
            echo unarchive($id,$encoded_label);    
        }
    } else if ($encoded_label == false) {
        echo back_to_shared($id_user_share);
    }  ?>
</div>
