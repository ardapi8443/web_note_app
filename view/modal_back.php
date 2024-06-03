<!-- Modal -->
<div id='myModal' class='modal fade' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title text-primary' id='confirmDeleteModalLabel'> Please confirm </h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>
            <div class='modal-body'>
                <p class='text-danger'>Changes you made will not be saved.</p>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                    Cancel
                </button>

                <button type="button" class='btn btn-secondary' onclick=history.back()>Leave</button>
            </div>
        </div>
    </div>
</div>