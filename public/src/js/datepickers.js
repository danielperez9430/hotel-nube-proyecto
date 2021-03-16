$(document).ready(function () {
    var dateToday = new Date();
    
    const picker = new Litepicker({ 
        element: document.getElementById('fechaInicio'),
        elementEnd: document.getElementById('fechaFin'),
        minDate: dateToday,
        singleMode: false,
        allowRepick: true,
        autoRefresh: true,
        format: 'DD-MM-YYYY',
        lang: 'es-ES',
        tooltipText: {
            one: 'night',
            other: 'nights'
        },
    });
    const date = new Date();
    picker.setDateRange(new Date(), new Date(date.getTime() + (10 * 24 * 60 * 60 * 1000)));
});