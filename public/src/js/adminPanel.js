$(document).ready(function () {

    // Cargamos los datos de las habitaciones con Ajax
    $.ajax({
        type: "POST",
        url: "http://localhost/Hotel_Nube/test.php",
        data: {},
        dataType: "JSON",
        success: function (response) {

            $.each(response, function (i, item) {

                var wifi = (item.internet === '1') ? "Yes" : "No";
                var ventana = (item.ventana === '1') ? "Yes" : "No";
                var servicio_limpieza = (item.servicio_limpieza === '1') ? "Yes" : "No";

                var habitacion = `<tr class="roomClickableRow" data-bs-toggle="modal" data-bs-target="#roomSettingsDialog" roomid="${item.id}">
                        <td>${item.id}</td>
                        <td>${item.tipo_habitacion}</td>
                        <td>${item.m2}</td>
                        <td>${ventana}</td>
                        <td>${wifi}</td>
                        <td>${servicio_limpieza}</td>
                        <td>${item.precio}</td>
                    </tr>`;
                $('.table_content').append(habitacion);
            });




        },
        error: function (e) {
            console.log(e);
        }
    });


    $(document).on('shown.bs.modal', function (event) {
        var numero = $(event.relatedTarget.attributes.roomid)[0].value; // Fila en la que hacemos click

        fetch('http://localhost/Hotel_Nube/public/api/getRoomTypes.php').then(response => {
            return response.json()
        }).then(data => {
            var options = "";
            var opcion_marcada = document.querySelector("tr[roomid='1']").getElementsByTagName('td')[1].innerText;

            data.forEach(element => {

                if (element === opcion_marcada) {
                    options = options + `<option value='${element}' selected>${element}</option>`;

                } else {
                    options = options + `<option value='${element}'>${element}</option>`;

                }

            });

            document.getElementById('roomTypeSelect').innerHTML = options;

            document.getElementById('roomNoValue').innerText = document.querySelector(`tr[roomid='${numero}']`).getElementsByTagName('td')[0].innerText;
            // Input number
            document.getElementById('roomSizeField').value = document.querySelector(`tr[roomid='${numero}']`).getElementsByTagName('td')[2].innerText;
            // Checkbox
            var ventana = document.querySelector(`tr[roomid='${numero}']`).getElementsByTagName('td')[3].innerText;
            document.getElementById('roomWindowsField').checked = (ventana === "Yes") ? true : false;
            var internet = document.querySelector(`tr[roomid='${numero}']`).getElementsByTagName('td')[4].innerText;
            document.getElementById('roomInternetCheck').checked = (internet === "Yes") ? true : false;

            var limpieza = document.querySelector(`tr[roomid='${numero}']`).getElementsByTagName('td')[5].innerText;
            document.getElementById('roomCleaningCheck').checked = (limpieza === "Yes") ? true : false;

            document.getElementById('roomPriceField').value = document.querySelector(`tr[roomid='${numero}']`).getElementsByTagName('td')[6].innerText;

        });



    });



    $('#removeRoom').click(function (e) {
        e.preventDefault();

        var clicked;
        switch (e.target.nodeName) {
            case 'I':
                clicked = e.target.parentNode;
                break;

            case 'SPAN':
                clicked = e.target.parentNode;
                break;

            case 'BUTTON':
                clicked = e.target;
                break;

            default:
                clicked = e.target;
                break;
        }
        console.log(clicked)
        const nClicks = clicked.getAttribute('clicks');
        if (nClicks == 0) {
            document.getElementById('removeText').innerHTML = 'Click to confirm';
            clicked.setAttribute('clicks', '1');
            $(clicked).addClass('clicked');
            const interval = setInterval(() => {
                document.getElementById('removeText').innerHTML = 'Remove room';
                document.getElementById('removeText').setAttribute('clicks', '0');
                $(clicked).removeClass('clicked');
                clearInterval(interval)
            }, 10000);
        } else if (nClicks == 1) {
            document.getElementById('removeText').innerHTML = 'Remove room';
            document.getElementById('removeText').setAttribute('clicks', '0');
            $(clicked).removeClass('clicked');

            $.ajax({
                type: "POST",
                url: "http://localhost/Hotel_Nube/public/api/removeRoom.php",
                data: {roomId: 1},
                dataType: "JSON",
                success: function (response) {
                        console.log(response) 
                    },
                error: function () {

                }
            });
        }
    });

    // Redirección a la página principal
   document.getElementById('goBack').addEventListener("click", e => {
        window.location = "/";
    });

});