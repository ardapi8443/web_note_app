$(document).ready(function () {
    let back = $("#back");
    let data = get_data();
    let modal = new bootstrap.Modal('#myModal');

    back.click(function (event) {
        event.preventDefault();  
        let data2 = get_data();
        if(JSON.stringify(data) === JSON.stringify(data2)) {
            window.history.back();
        } else
            modal.show();
    });
});

function get_data() {
    data = [];
    $("input[type='text']").each(function() {
        data.push($(this).val());
    });
    $("textarea").each(function() {
        data.push($(this).val());
    });
    return data;
}