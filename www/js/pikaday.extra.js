$(document).ready(function () {

    /* add date picker to date fields (if not supported by browser) */
    $('input.date').each(function (index, element) {
        if ($(element).prop('type') != 'date' && typeof Pikaday === 'function') { // jQuery().datepicker) {
            var picker = new Pikaday({
                field: $(element)[0],
                format: 'YYYY-MM-DD'
            });
            /*
            $(element).datepicker({
                dateFormat: 'yy-mm-dd'
            });
            */
        }
    });

});