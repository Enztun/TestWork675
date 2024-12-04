jQuery(document).ready(function ($) {
    $("#countries-cities-table").DataTable({
        paging: true,
        ordering: true,
        info: true,
        responsive: true,
        autoWidth: false,
        search: true
    });
});