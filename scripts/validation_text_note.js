    async function validation_title(inf, sup, element) {
    let unicity = await $.post("note/check_unicity_service/", 
                            {titre: element.val()}, 
                            function() {}, 
                            'json');

    if (title.val().length < inf || element.val().length > sup || !unicity) {
        element.addClass('is-invalid').removeClass('is-valid');
    } else {
        element.addClass('is-valid').removeClass('is-invalid');
        }
    } 
    
    async function validation_txt(inf, sup, element) { {
        if (element.val() && texte.val().length < inf || element.val().length > sup) {
            element.addClass('is-invalid').removeClass('is-valid');
        } else {
            element.addClass('is-valid').removeClass('is-invalid');
        }
    }
}