<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <base href="<?= $web_root ?>">
        <title><?=$note->get_title()?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

        <script src="lib/jquery-3.7.1.min.js" ></script>

    <style>
        .text-strikethrough {
        text-decoration: line-through;}
    </style>

<script>

$(document).on('click', '.check-uncheck-btn', function(event) {
    event.preventDefault();

    var noteId = $(this).data('note-id');
    var checked = $(this).data('note-checked');
    check_uncheck(noteId, checked);
    
});



async function check_uncheck(noteId, checked) {
    try {
        const response = await $.ajax({
            url: 'note/check_uncheck_item',
            method: 'POST',
            //contentType: 'application/json',
            data: {noteId:noteId, checked : checked }
        });
        sorted_items(noteId);
    } catch (error) {
        console.error('Failed to update notes:', error);
    }
}

function sorted_items(noteId) {
    // Exécutez une requête AJAX pour récupérer les éléments triés
    $.ajax({
        url: 'note/get_sorted_items_service/' + noteId,
        method: 'GET',
        // Autres paramètres de la requête
    }).done(function(response) {

        updateUIWithSortedItems(response);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Failed to fetch sorted items:', textStatus, errorThrown);
    });
}

function updateUIWithSortedItems(itemsJson) {

    const items = JSON.parse(itemsJson);

    $('.input-group').remove();

    items.forEach(function(itemData) {
        
        const formAction = itemData.checked ? 'note/uncheck_item' : 'note/check_item';
        const checkedIcon = itemData.checked ? 'V' : 'X';
        const checkedClass = itemData.checked ? 'primary' : 'secondary';
        const strikethroughClass = itemData.checked ? 'text-strikethrough' : '';

        const inputGroup = `
            <form class="link" action="${formAction}" method="post">
                <input type="number" value="${itemData.id}" name="checker" hidden>
                <div class="input-group mb-2">
                    <span class="input-group-text bg-dark text-white">
                        <button data-note-id="${itemData.id}" data-note-checked="${itemData.checked}" type="submit" class="btn btn-${checkedClass} check-uncheck-btn">
                            ${checkedIcon}
                        </button>
                    </span>
                    <input type="text" class="form-control bg-dark text-white ${strikethroughClass}" name="items[${itemData.id}]" value="${itemData.content}" disabled>
                </div>
            </form>
        `;

        $('.card-body').append(inputGroup);
    });
}
</script>

    </head>


    <body class="bg-dark">
    <?php require_once "openNote.php"; ?>
        <div class="container py-4 bg-dark">
            <div class="card bg-dark text-white">
                <div class="card-body">
                <p class="text">Created <?= $date_interval_creation ?> <?= $note->get_edited_at() != null ? 'Edited ' . $date_interval_edit : 'Never Edited' ?></p>
                    <h3 class="card-subtitle mb-2 text">Title</h3>
                    <div class="mb-3">
                        <input type="text" class="form-control bg-dark text-white" value="<?= $note->get_title() ?>" readonly>
                    </div>

                    <h3 id="itemsTitle" class="card-subtitle mb-2 text">Items</h3>
                    <?php foreach($note->get_checklist_items_by_checked() as $item): ?>
                        <?php $formAction = $item->is_checked() ? 'note/uncheck_item/' . ($encoded_label == true ? $encoded_label : '') : 'note/check_item/' . ($encoded_label == true ? $encoded_label : ''); ?>
                        <form class="link" action="<?= $formAction ?>" method="post">
                        <input type="number" value="<?= $item->get_id() ?>" name="checker" hidden>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-dark text-white">
                            <button data-note-id="<?= $item->get_id() ?>" data-note-checked=" <?= $item->is_checked_bis() ?>" type="submit" class="btn btn-<?= $item->is_checked_bis() ? 'primary' : 'secondary' ?> check-uncheck-btn" <?= $note->is_archived() || ($editor == false && $note->get_owner() != $user->get_id()) ? 'disabled' : '' ?>>
                                    <?= $item->is_checked_bis() ? 'V' : 'X' ?>
                                </button>
                            </span>
                            <input type="text" class="form-control bg-dark text-white <?= $item->is_checked_bis() ? 'text-strikethrough' : '' ?>" name="items[<?= $item->get_id() ?>]" value="<?= $item->get_content() ?>" disabled>
                        </div>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </body>
</html>