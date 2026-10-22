$(document).ready(function () {

    // Escapa un texto para insertarlo como HTML
    const esc = texto => $('<div>').text(texto == null ? '' : String(texto)).html();

    const siNo = valor => (valor === true || Number(valor) === 1) ? 'Yes' : 'No';

    const euros = valor => Number(valor).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';

    const fecha = texto => new Date(String(texto).slice(0, 10) + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

    const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

    // ------------------------------------------------------------------
    // Habitaciones
    // ------------------------------------------------------------------

    // Habitaciones por id y la abierta en el diálogo (null si es nueva)
    var habitaciones = {};
    var habitacionActual = null;
    var tiposCargados = null;

    function filaHabitacion(item) {
        return `<tr class="roomClickableRow" roomid="${esc(item.id)}" tabindex="0">
                <td>${esc(item.id)}</td>
                <td>${esc(item.tipo_habitacion)}</td>
                <td>${esc(Number(item.m2).toFixed(2))}</td>
                <td>${siNo(item.ventana)}</td>
                <td>${siNo(item.internet)}</td>
                <td>${siNo(item.servicio_limpieza)}</td>
                <td>${esc(Number(item.precio).toFixed(2))}</td>
            </tr>`;
    }

    function cargarHabitaciones() {
        $.getJSON('api/getAllRooms.php', function (response) {
            habitaciones = {};
            response.forEach(item => { habitaciones[item.id] = item; });
            $('.table_content').html(response.map(filaHabitacion).join(''));
        }).fail(function () {
            $('.table_content').html('<tr><td colspan="7" class="text-danger">The rooms could not be loaded.</td></tr>');
        });
    }

    // Los tipos de habitación se piden una sola vez
    function tiposHabitacion() {
        if (!tiposCargados) {
            tiposCargados = $.getJSON('api/getRoomTypes.php');
        }
        return tiposCargados;
    }

    // Abre el diálogo para editar una habitación o, sin id, para crear una
    function abrirHabitacion(id) {
        const item = id ? habitaciones[id] : null;
        habitacionActual = item ? item.id : null;

        $('#roomSettingsTitle').text(item ? 'Room settings' : 'New room');
        $('#roomNo').toggle(item !== null);
        $('#roomNoValue').text(item ? item.id : '');
        $('#removeRoom').toggle(item !== null);
        $('#roomMessage').hide();
        reiniciarBotonBorrar(document.getElementById('removeRoom'));

        document.getElementById('roomSizeField').value = item ? item.m2 : '';
        document.getElementById('roomWindowsField').checked = item ? siNo(item.ventana) === 'Yes' : true;
        document.getElementById('roomInternetCheck').checked = item ? siNo(item.internet) === 'Yes' : true;
        document.getElementById('roomCleaningCheck').checked = item ? siNo(item.servicio_limpieza) === 'Yes' : true;
        document.getElementById('roomPriceField').value = item ? item.precio : '';

        tiposHabitacion().then(tipos => {
            const select = document.getElementById('roomTypeSelect');
            select.innerHTML = '';
            tipos.forEach(tipo => select.add(new Option(tipo, tipo, false, item !== null && tipo === item.tipo_habitacion)));
        });

        modal('roomSettingsDialog').show();
    }

    $(document).on('click', '.roomClickableRow', function () {
        abrirHabitacion(this.getAttribute('roomid'));
    });

    $(document).on('keydown', '.roomClickableRow', function (e) {
        if (e.key === 'Enter') {
            abrirHabitacion(this.getAttribute('roomid'));
        }
    });

    $('#addRoom').click(() => abrirHabitacion(null));

    var temporizadorBorrado = null;

    // Devuelve el botón de borrado a su estado inicial
    function reiniciarBotonBorrar(boton) {
        clearTimeout(temporizadorBorrado);
        document.getElementById('removeText').textContent = 'Remove room';
        boton.setAttribute('clicks', '0');
        $(boton).removeClass('clicked');
    }

    $('#removeRoom').click(function (e) {
        e.preventDefault();

        // currentTarget es siempre el botón, aunque se pulse el icono o el texto
        const boton = e.currentTarget;

        if (boton.getAttribute('clicks') === '0') {
            document.getElementById('removeText').textContent = 'Click to confirm';
            boton.setAttribute('clicks', '1');
            $(boton).addClass('clicked');
            temporizadorBorrado = setTimeout(() => reiniciarBotonBorrar(boton), 10000);
            return;
        }

        reiniciarBotonBorrar(boton);
        const roomId = habitacionActual;

        $.ajax({
            type: 'POST',
            url: 'api/removeRoom.php',
            data: { roomId },
            dataType: 'JSON',
            success: function (response) {
                if (response.result === 'success') {
                    modal('roomSettingsDialog').hide();
                    cargarHabitaciones();
                } else {
                    $('#roomMessage').text('The room could not be removed. Rooms with bookings cannot be removed.').show();
                }
            },
            error: function () {
                $('#roomMessage').text('The room could not be removed.').show();
            }
        });
    });

    $('#saveRoomChanges').click(function (e) {
        e.preventDefault();

        const boton = e.currentTarget;
        const datos = {
            tipo: document.getElementById('roomTypeSelect').value,
            m2: document.getElementById('roomSizeField').value,
            ventana: document.getElementById('roomWindowsField').checked,
            internet: document.getElementById('roomInternetCheck').checked,
            limpieza: document.getElementById('roomCleaningCheck').checked,
            precio: document.getElementById('roomPriceField').value
        };

        if (habitacionActual !== null) {
            datos.roomId = habitacionActual;
        }

        boton.disabled = true;

        $.ajax({
            type: 'POST',
            url: habitacionActual !== null ? 'api/updateRoom.php' : 'api/createRoom.php',
            data: datos,
            dataType: 'JSON',
            success: function (response) {
                if (response.result === 'success') {
                    modal('roomSettingsDialog').hide();
                    cargarHabitaciones();
                } else {
                    $('#roomMessage').text('The room could not be saved. Check the size (1 to 9999 m²) and the price.').show();
                }
            },
            error: function () {
                $('#roomMessage').text('The room could not be saved. Check the size (1 to 9999 m²) and the price.').show();
            },
            complete: function () {
                boton.disabled = false;
            }
        });
    });

    // ------------------------------------------------------------------
    // Reservas y ocupación
    // ------------------------------------------------------------------

    function tarjetaOcupacion(icono, valor, texto) {
        return `<div class="occupancy-card">
                <i class="${icono}"></i>
                <span class="occupancy-value">${valor}</span>
                <span class="occupancy-label">${texto}</span>
            </div>`;
    }

    function estadoReserva(r, hoy) {
        if (r.estado === 'cancelada') {
            const cargo = Number(r.cargo_cancelacion) > 0 ? ` · ${euros(r.cargo_cancelacion)}` : ' · free';
            return `<span class="badge booking-status cancelled">Cancelled</span><small class="d-block text-muted">${fecha(r.fecha_cancelacion)}${cargo}</small>`;
        }

        if (r.fecha_entrada <= hoy && r.fecha_salida > hoy) {
            return '<span class="badge booking-status in-house">In house</span>';
        }

        if (r.fecha_salida <= hoy) {
            return '<span class="badge booking-status past">Completed</span>';
        }

        return '<span class="badge booking-status confirmed">Confirmed</span>';
    }

    function cargarReservas() {
        const filtro = $('input[name="bookingFilter"]:checked').val();
        $('#bookingsList').html('<p class="text-muted">Loading...</p>');

        $.getJSON('api/getBookings.php', { filter: filtro }, function (response) {
            const o = response.occupancy;

            $('#occupancy').html(
                tarjetaOcupacion('fas fa-bed', `${o.ocupadas_hoy}/${o.habitaciones}`, 'Rooms occupied tonight') +
                tarjetaOcupacion('fas fa-sign-in-alt', o.entradas_hoy, 'Check-ins today') +
                tarjetaOcupacion('fas fa-sign-out-alt', o.salidas_hoy, 'Check-outs today') +
                tarjetaOcupacion('fas fa-chart-line', `${o.ocupacion}%`, 'Occupancy next 30 days') +
                tarjetaOcupacion('fas fa-euro-sign', euros(o.ingresos_futuros), 'Upcoming room revenue'));

            if (response.bookings.length === 0) {
                $('#bookingsList').html('<p class="text-muted">There are no bookings to show.</p>');
                return;
            }

            const filas = response.bookings.map(r => `<tr${r.estado === 'cancelada' ? ' class="booking-cancelled"' : ''}>
                    <td>#${esc(r.num_reserva)}</td>
                    <td>${esc(r.nombre)}<small class="d-block text-muted">${esc(r.email)} · ${esc(r.telf)}</small></td>
                    <td>${esc(r.tipo_habitacion)} · ${esc(r.id_habitacion)}</td>
                    <td>${fecha(r.fecha_entrada)}</td>
                    <td>${fecha(r.fecha_salida)}</td>
                    <td class="text-end">${euros(Number(r.total) + Number(r.extras))}${r.servicios ? `<small class="d-block text-muted">incl. ${esc(r.servicios)}</small>` : ''}</td>
                    <td>${estadoReserva(r, response.today)}</td>
                </tr>`).join('');

            $('#bookingsList').html(`<div class="table-responsive"><table class="table admin-table align-middle">
                    <thead><tr><th>Booking</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th class="text-end">Total</th><th>Status</th></tr></thead>
                    <tbody>${filas}</tbody>
                </table></div>`);
        }).fail(function () {
            $('#bookingsList').html('<div class="alert alert-danger">The bookings could not be loaded.</div>');
        });
    }

    $('#tabBookings').on('shown.bs.tab', cargarReservas);
    $('input[name="bookingFilter"]').change(cargarReservas);

    // ------------------------------------------------------------------
    // Servicios
    // ------------------------------------------------------------------

    var servicios = {};

    // Solo clases de Font Awesome válidas para el icono
    const iconoValido = icono => /^fa[a-z]? fa-[a-z0-9-]+$/.test(icono);

    function cargarServicios() {
        $.getJSON('api/getAllServices.php', function (response) {
            servicios = {};
            response.services.forEach(item => { servicios[item.id] = item; });

            $('#servicesTable').html(response.services.map(item => `<tr class="serviceRow${Number(item.disponibilidad) === 1 ? '' : ' service-unavailable'}" data-id="${esc(item.id)}" tabindex="0">
                    <td class="service-icon"><i class="${iconoValido(item.icono) ? esc(item.icono) : 'fas fa-concierge-bell'}"></i></td>
                    <td>${esc(item.nombre_servicio)}</td>
                    <td class="text-muted">${esc(item.descripcion)}</td>
                    <td class="text-end">${Number(item.precio_servicio) > 0 ? esc(Number(item.precio_servicio).toFixed(2)) : 'Included'}</td>
                    <td>${siNo(item.disponibilidad)}</td>
                </tr>`).join(''));
        }).fail(function () {
            $('#servicesTable').html('<tr><td colspan="5" class="text-danger">The services could not be loaded.</td></tr>');
        });
    }

    function abrirServicio(id) {
        const item = id ? servicios[id] : null;

        $('#serviceTitle').text(item ? 'Edit service' : 'New service');
        $('#serviceMessage').hide();
        $('#removeService').toggle(item !== null);
        $('#serviceId').val(item ? item.id : '');
        $('#serviceName').val(item ? item.nombre_servicio : '');
        $('#serviceDescription').val(item ? item.descripcion : '');
        $('#servicePrice').val(item ? item.precio_servicio : '0');
        $('#serviceIcon').val(item ? item.icono : 'fas fa-concierge-bell').trigger('input');
        document.getElementById('serviceAvailable').checked = item ? Number(item.disponibilidad) === 1 : true;

        modal('serviceDialog').show();
    }

    $(document).on('click', '.serviceRow', function () {
        abrirServicio(this.dataset.id);
    });

    $(document).on('keydown', '.serviceRow', function (e) {
        if (e.key === 'Enter') {
            abrirServicio(this.dataset.id);
        }
    });

    $('#addService').click(() => abrirServicio(null));

    // Vista previa del icono
    $('#serviceIcon').on('input', function () {
        const icono = this.value.trim();
        document.getElementById('serviceIconPreview').className = iconoValido(icono) ? icono : 'fas fa-question';
    });

    $('#serviceForm').on('submit', function (e) {
        e.preventDefault();

        const boton = document.getElementById('saveService');
        boton.disabled = true;

        $.ajax({
            type: 'POST',
            url: 'api/saveService.php',
            data: {
                id: $('#serviceId').val(),
                nombre: $('#serviceName').val(),
                descripcion: $('#serviceDescription').val(),
                precio: $('#servicePrice').val(),
                icono: $('#serviceIcon').val(),
                disponible: document.getElementById('serviceAvailable').checked
            },
            dataType: 'JSON',
            success: function (response) {
                if (response.result === 'success') {
                    modal('serviceDialog').hide();
                    cargarServicios();
                } else {
                    $('#serviceMessage').text('The service could not be saved.').show();
                }
            },
            error: function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The service could not be saved.';
                $('#serviceMessage').text(mensaje).show();
            },
            complete: function () {
                boton.disabled = false;
            }
        });
    });

    $('#removeService').click(function () {
        const id = $('#serviceId').val();

        if (!window.confirm('Remove this service? This cannot be undone.')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'api/removeService.php',
            data: { id },
            dataType: 'JSON',
            success: function (response) {
                if (response.result === 'success') {
                    modal('serviceDialog').hide();
                    cargarServicios();
                } else {
                    $('#serviceMessage').text('The service could not be removed. If guests have booked it, mark it as unavailable instead.').show();
                }
            },
            error: function () {
                $('#serviceMessage').text('The service could not be removed.').show();
            }
        });
    });

    $('#tabServices').on('shown.bs.tab', cargarServicios);

    // Redirección a la página principal
    document.getElementById('goBack').addEventListener('click', () => {
        window.location = 'index.php';
    });

    cargarHabitaciones();
});
