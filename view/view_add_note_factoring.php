<?php
    function get_buttons ($form_id, $form_action) {
        echo "
    
        <div class='container'>
            <!-- Row pour les boutons -->
            <div class='row'>
                <!-- Bouton au milieu -->
                <div class='col-4 text-center'>
                    <form class='d-inline' action='$form_action' data-bs-toggle='tooltip' data-bs-placement='top' title='get back'>
                        <button type='submit' data-bs-toggle='tooltip' data-bs-placement='top' title='back to home page'>
                            <i class='bi bi-arrow-90deg-left'></i>
                        </button>
                    </form>
                </div>
        
                <!-- Bouton à droite -->
                <div class='col-8 text-center'>
                    <button type='submit' form='$form_id' class='btn btn-outline-light p-0 border-0 bg-transparent'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' fill='currentColor' class='bi bi-patch-plus-fill' viewBox='0 0 16 16'>
                            <path d='M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm6.5 4.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3a.5.5 0 0 1 1 0z'/>
                        </svg>
                    </button>
                </div>
            </div>
        ";
    }
?>