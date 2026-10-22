$(document).ready(function () {
    // El selector de fechas solo está en la página principal
    if (!document.getElementById('fechaInicio')) {
        return;
    }

    var dateToday = new Date();
    
    // Se expone para poder restaurar las fechas de una búsqueda pendiente
    const picker = window.pickerFechas = new Litepicker({ 
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