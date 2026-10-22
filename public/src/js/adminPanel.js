$(document).ready(function () {

    // Id de la habitación abierta en el diálogo de ajustes
    var habitacionActual = null;

    const siNo = valor => (valor === true || Number(valor) === 1) ? "Yes" : "No";

    // Cargamos los datos de las habitaciones con Ajax
    $.ajax({
        type: "GET",
        url: "api/getAllRooms.php",
        dataType: "JSON",
        success: function (response) {

            $.each(response, function (i, item) {

                var habitacion = `<tr class="roomClickableRow" data-bs-toggle="modal" data-bs-target="#roomSettingsDialog" roomid="${item.id}">
                        <td>${item.id}</td>
                        <td>${item.tipo_habitacion}</td>
                        <td>${item.m2}</td>
                        <td>${siNo(item.ventana)}</td>
                        <td>${siNo(item.internet)}</td>
                        <td>${siNo(item.servicio_limpieza)}</td>
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
        habitacionActual = event.relatedTarget.getAttribute('roomid'); // Fila en la que hacemos click
        const celdas = document.querySelector(`tr[roomid='${habitacionActual}']`).getElementsByTagName('td');

        fetch('api/getRoomTypes.php').then(response => {
            return response.json();
        }).then(data => {
            const select = document.getElementById('roomTypeSelect');
            const opcion_marcada = celdas[1].innerText;

            select.innerHTML = '';
            data.forEach(element => {
                select.add(new Option(element, element, false, element === opcion_marcada));
            });
        });

        document.getElementById('roomNoValue').innerText = celdas[0].innerText;
        // Input number
        document.getElementById('roomSizeField').value = celdas[2].innerText;
        // Checkbox
        document.getElementById('roomWindowsField').checked = celdas[3].innerText === "Yes";
        document.getElementById('roomInternetCheck').checked = celdas[4].innerText === "Yes";
        document.getElementById('roomCleaningCheck').checked = celdas[5].innerText === "Yes";

        document.getElementById('roomPriceField').value = celdas[6].innerText;
    });


    var temporizadorBorrado = null;

    // Devuelve el botón de borrado a su estado inicial
    const reiniciarBotonBorrar = boton => {
        clearTimeout(temporizadorBorrado);
        document.getElementById('removeText').innerHTML = 'Remove room';
        boton.setAttribute('clicks', '0');
        $(boton).removeClass('clicked');
    };

    $('#removeRoom').click(function (e) {
        e.preventDefault();

        // currentTarget es siempre el botón, aunque se pulse el icono o el texto
        const clicked = e.currentTarget;
        const nClicks = clicked.getAttribute('clicks');

        if (nClicks == 0) {
            document.getElementById('removeText').innerHTML = 'Click to confirm';
            clicked.setAttribute('clicks', '1');
            $(clicked).addClass('clicked');
            temporizadorBorrado = setTimeout(() => reiniciarBotonBorrar(clicked), 10000);
        } else if (nClicks == 1) {
            reiniciarBotonBorrar(clicked);

            const roomId = habitacionActual;

            $.ajax({
                type: "POST",
                url: "api/removeRoom.php",
                data: {roomId},
                dataType: "JSON",
                success: function (response) {
                    if (response.result == 'success') {
                        $(`tr[roomid='${roomId}']`).remove();
                        bootstrap.Modal.getInstance(document.getElementById('roomSettingsDialog')).hide();
                    }
                },
                error: function (e) {
                    console.log(e);
                }
            });
        }
    });

    $('#saveRoomChanges').click(function (e) {
        e.preventDefault();

        const boton = e.currentTarget;
        const roomId = habitacionActual;
        const datos = {
            roomId,
            tipo: document.getElementById('roomTypeSelect').value,
            m2: document.getElementById('roomSizeField').value,
            ventana: document.getElementById('roomWindowsField').checked,
            internet: document.getElementById('roomInternetCheck').checked,
            limpieza: document.getElementById('roomCleaningCheck').checked,
            precio: document.getElementById('roomPriceField').value
        };

        boton.disabled = true;

        $.ajax({
            type: "POST",
            url: "api/updateRoom.php",
            data: datos,
            dataType: "JSON",
            success: function (response) {
                if (response.result == 'success') {
                    // Actualizamos la fila de la tabla con los datos guardados
                    const celdas = document.querySelector(`tr[roomid='${roomId}']`).getElementsByTagName('td');
                    celdas[1].innerText = datos.tipo;
                    celdas[2].innerText = Number(datos.m2).toFixed(2);
                    celdas[3].innerText = siNo(datos.ventana);
                    celdas[4].innerText = siNo(datos.internet);
                    celdas[5].innerText = siNo(datos.limpieza);
                    celdas[6].innerText = Number(datos.precio).toFixed(2);

                    bootstrap.Modal.getInstance(document.getElementById('roomSettingsDialog')).hide();
                } else {
                    alert('The room could not be saved. Check the size and the price.');
                }
            },
            error: function () {
                alert('The room could not be saved.');
            },
            complete: function () {
                boton.disabled = false;
            }
        });
    });

    // Redirección a la página principal
    document.getElementById('goBack').addEventListener("click", () => {
        window.location = "index.php";
    });

});
