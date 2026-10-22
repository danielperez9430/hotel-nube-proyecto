$(document).ready(function () {

    // Clave en sessionStorage de una búsqueda que espera al login
    const BUSQUEDA_PENDIENTE = 'busquedaPendiente';

    // Estancia de la última búsqueda (fechas en YYYY-MM-DD) y habitación elegida
    var estancia = null;
    var habitacionElegida = null;

    // Escapa un texto para insertarlo como HTML
    const esc = texto => $('<div>').text(texto == null ? '' : String(texto)).html();

    // Formatea un precio en euros
    const euros = valor => Number(valor).toLocaleString('en-GB', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + '€';

    // Formatea una fecha YYYY-MM-DD para mostrarla
    const fecha = texto => new Date(texto + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

    const hayUsuario = () => document.getElementById('userButton') !== null;

    const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

    function mostrarMensaje(texto, tipo) {
        $('#roomsMessage').attr('class', 'alert alert-' + (tipo || 'info')).text(texto).show();
    }

    function buscarHabitaciones(checkin, checkout) {
        $('#roomSelection').html('');
        $('#roomsDialogStay').text('');
        mostrarMensaje('Searching available rooms...');
        modal('roomsDialog').show();

        $.ajax({
            type: "GET",
            url: "api/searchRooms.php",
            data: { checkin, checkout },
            dataType: "JSON",
            success: function (response) {
                estancia = { checkin: response.checkin, checkout: response.checkout, nights: response.nights };

                $('#roomsDialogStay').text(`${fecha(response.checkin)} → ${fecha(response.checkout)} · ${response.nights} night${response.nights > 1 ? 's' : ''}`);

                if (response.rooms.length === 0) {
                    mostrarMensaje('There are no rooms available for those dates.', 'warning');
                    return;
                }

                $('#roomsMessage').hide();
                response.rooms.forEach(item => $('#roomSelection').append(plantillaHabitacion(item, response.nights)));
            },
            error: function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The rooms could not be loaded. Try again.';
                mostrarMensaje(mensaje, 'danger');
            }
        });
    }

    function plantillaHabitacion(item, noches) {
        const clase = valor => Number(valor) === 1 ? 'available' : 'notavailable';
        // Las rutas de las imágenes empiezan por / y son relativas a public/
        const imagen = String(item.imagen || '').replace(/^\//, '');

        return `<div class="room">
                <div class="top">
                    <div id="picture">
                        <img src="${esc(imagen)}" alt="${esc(item.descripcion_imagen)}">
                    </div>
                    <div class="content">
                        <span id="name">${esc(item.tipo_habitacion)} <small class="text-muted">· Room ${esc(item.id)}</small></span>
                        <span id="description">${esc(item.descripcion)}</span>
                        <div class="details">
                            <div class="detail">
                                <i class="fas fa-ruler"></i>
                                <span>${Math.round(item.m2)} m<sup>2</sup></span>
                            </div>
                            <div class="detail">
                                <img src="src/icons/window.svg" alt="Windows">
                                <span class="${clase(item.ventana)}">Windows</span>
                            </div>
                            <div class="detail">
                                <img src="src/icons/vacuum.svg" alt="Cleaning service">
                                <span class="${clase(item.servicio_limpieza)}">Cleaning service</span>
                            </div>
                            <div class="detail">
                                <i class="fas fa-wifi"></i>
                                <span class="${clase(item.internet)}">Wifi</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="price">
                    <span id="summary">${euros(item.precio)}/night · <strong>${euros(item.precio * noches)}</strong> total</span>
                    <button class="btn btn-primary confirmRoomSelection" roomid="${esc(item.id)}"
                        data-tipo="${esc(item.tipo_habitacion)}" data-precio="${esc(item.precio)}"><i class="fas fa-check"></i> Choose this room</button>
                </div>
            </div>`;
    }

    // Busca con las fechas del selector
    $('#searchRooms').click(function (e) {
        e.preventDefault();
        buscarHabitaciones(document.getElementById('fechaInicio').value, document.getElementById('fechaFin').value);
    });

    // Elegir habitación: pide el login si hace falta y si no muestra el resumen
    $(document).on('click', '.confirmRoomSelection', function (e) {
        e.preventDefault();

        if (!hayUsuario()) {
            // Guardamos la búsqueda para repetirla tras el login
            try {
                sessionStorage.setItem(BUSQUEDA_PENDIENTE, JSON.stringify(estancia));
            } catch {
                // Sin sessionStorage solo se pierde la búsqueda
            }

            modal('roomsDialog').hide();
            $('#loginNotice').text('Log in or register to book a room.').show();
            modal('loginDialog').show();
            return;
        }

        const boton = e.currentTarget;
        habitacionElegida = {
            id: boton.getAttribute('roomid'),
            tipo: boton.dataset.tipo,
            precio: Number(boton.dataset.precio)
        };

        $('#bookingSummary').html(`
            <p class="mb-1"><strong>${esc(habitacionElegida.tipo)}</strong> · Room ${esc(habitacionElegida.id)}</p>
            <p class="mb-1"><i class="far fa-calendar"></i> ${fecha(estancia.checkin)} → ${fecha(estancia.checkout)}</p>
            <p class="mb-3">${estancia.nights} night${estancia.nights > 1 ? 's' : ''} × ${euros(habitacionElegida.precio)}</p>
            <div id="bookingExtras" class="booking-extras"></div>
            <p class="fs-4 mb-0">Total: <strong id="bookingTotal"></strong></p>`);
        mostrarExtras();
        $('#bookingConfirm').prop('disabled', false).show();
        $('#bookingCancel').text('Cancel');

        modal('roomsDialog').hide();
        modal('bookingDialog').show();
    });

    // Servicios que se pueden añadir a la reserva (se piden una sola vez)
    var serviciosExtra = null;

    function mostrarExtras() {
        actualizarTotal();

        if (!serviciosExtra) {
            serviciosExtra = $.getJSON('api/getServices.php').then(response => response.services);
        }

        serviciosExtra.then(servicios => {
            const opciones = servicios.map(servicio => {
                const precio = Number(servicio.precio_servicio);
                return `<div class="form-check">
                        <input class="form-check-input booking-extra" type="checkbox" value="${esc(servicio.id)}" id="extra${esc(servicio.id)}" data-precio="${esc(precio)}"${precio === 0 ? ' checked disabled' : ''}>
                        <label class="form-check-label" for="extra${esc(servicio.id)}">
                            <i class="${esc(servicio.icono)}" aria-hidden="true"></i> ${esc(servicio.nombre_servicio)}
                            <span class="text-muted">${precio > 0 ? '+' + euros(precio) : 'included'}</span>
                        </label>
                    </div>`;
            }).join('');

            $('#bookingExtras').html(`<p class="booking-extras-title">Add extras to your stay <small class="text-muted">(price per stay)</small></p>${opciones}`);
        }, () => $('#bookingExtras').empty());
    }

    // Ids de los extras de pago elegidos (los incluidos no hace falta enviarlos)
    const extrasElegidos = () => $('.booking-extra:checked:not(:disabled)').map((i, el) => el.value).get();

    function actualizarTotal() {
        const extras = $('.booking-extra:checked:not(:disabled)').get().reduce((suma, el) => suma + Number(el.dataset.precio), 0);
        $('#bookingTotal').text(euros(habitacionElegida.precio * estancia.nights + extras));
    }

    $(document).on('change', '.booking-extra', actualizarTotal);

    // Confirmar la reserva
    $('#bookingConfirm').click(function (e) {
        e.preventDefault();

        $(this).prop('disabled', true);

        $.ajax({
            type: "POST",
            url: "api/bookRoom.php",
            data: {
                roomId: habitacionElegida.id,
                checkin: estancia.checkin,
                checkout: estancia.checkout,
                services: extrasElegidos()
            },
            dataType: "JSON",
            success: function (response) {
                $('#bookingSummary').html(`
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="fs-5 mb-1">Booking <strong>#${esc(response.booking)}</strong> confirmed!</p>
                        <p class="mb-0">We have sent the details to your email. Total: <strong>${euros(response.total)}</strong></p>
                    </div>`);
                $('#bookingConfirm').hide();
                $('#bookingCancel').text('Close');
            },
            error: function (xhr) {
                const mensajes = {
                    401: 'Your session has expired. Log in again to book.',
                    409: 'Sorry, this room has just been booked. Please choose another one.'
                };
                const mensaje = mensajes[xhr.status] || (xhr.responseJSON && xhr.responseJSON.message) || 'The booking could not be completed. Try again.';

                $('#bookingSummary').append(`<div class="alert alert-danger mt-3 mb-0">${esc(mensaje)}</div>`);
                $('#bookingConfirm').hide();
                $('#bookingCancel').text('Close');
            }
        });
    });

    // Reservas del usuario, por número de reserva
    var misReservas = {};
    // Reserva que se va a cancelar y cargo mostrado al cliente
    var cancelacion = null;

    const fechaHora = texto => new Date(texto).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

    function estadoReserva(r) {
        if (r.estado === 'cancelada') {
            const cargo = Number(r.cargo_cancelacion) > 0 ? ` · ${euros(r.cargo_cancelacion)} charge` : '';
            return `<span class="badge booking-status cancelled">Cancelled</span><small class="d-block text-muted">${esc(fecha(String(r.fecha_cancelacion).slice(0, 10)))}${cargo}</small>`;
        }

        const pasada = r.fecha_salida < new Date().toISOString().slice(0, 10);
        return `<span class="badge booking-status ${pasada ? 'past' : 'confirmed'}">${pasada ? 'Completed' : 'Confirmed'}</span>`;
    }

    function cargarReservas() {
        $('#myBookingsList').html('<p class="text-muted">Loading...</p>');

        $.getJSON('api/myBookings.php', function (response) {
            if (response.bookings.length === 0) {
                $('#myBookingsList').html('<p class="text-muted mb-0">You have no bookings yet.</p>');
                return;
            }

            misReservas = {};
            response.bookings.forEach(r => { misReservas[r.num_reserva] = r; });

            const filas = response.bookings.map(r => `<tr${r.estado === 'cancelada' ? ' class="booking-cancelled"' : ''}>
                    <td>#${esc(r.num_reserva)}</td>
                    <td>${esc(r.tipo_habitacion)} · ${esc(r.id_habitacion)}</td>
                    <td>${fecha(r.fecha_entrada)}</td>
                    <td>${fecha(r.fecha_salida)}</td>
                    <td class="text-end">${euros(Number(r.total) + Number(r.extras))}${r.servicios ? `<small class="d-block text-muted">incl. ${esc(r.servicios)}</small>` : ''}</td>
                    <td>${estadoReserva(r)}</td>
                    <td class="text-end">${r.cancelacion ? `<button class="btn btn-sm btn-outline-danger cancelBooking" data-booking="${esc(r.num_reserva)}">Cancel</button>` : ''}</td>
                </tr>`).join('');

            $('#myBookingsList').html(`<div class="table-responsive"><table class="table align-middle mb-0">
                    <thead><tr><th>Booking</th><th>Room</th><th>Check-in</th><th>Check-out</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
                    <tbody>${filas}</tbody>
                </table></div>
                <p class="small text-muted mt-3 mb-0">Cancellations follow our <a href="privacy.php#cancellation">cancellation policy</a>.</p>`);
        }).fail(function () {
            $('#myBookingsList').html('<div class="alert alert-danger mb-0">Your bookings could not be loaded.</div>');
        });
    }

    $('#myBookings').click(function (e) {
        e.preventDefault();

        $('#userLoggedMenu').css('display', 'none');
        $('#userLoggedMenuBase').css('display', 'none');
        cargarReservas();
        modal('myBookingsDialog').show();
    });

    function resumenCancelacion(r, cargo) {
        const texto = cargo > 0
            ? `Cancelling now has a charge of <strong>${euros(cargo)}</strong>, according to our cancellation policy.`
            : `Cancelling now is <strong>free of charge</strong> (free until ${esc(fechaHora(r.cancelacion.gratis_hasta))}).`;

        $('#cancelBookingSummary').html(`
            <p class="mb-1"><strong>Booking #${esc(r.num_reserva)}</strong> · ${esc(r.tipo_habitacion)}</p>
            <p class="mb-3"><i class="far fa-calendar"></i> ${fecha(r.fecha_entrada)} → ${fecha(r.fecha_salida)}</p>
            <p class="mb-0">${texto}</p>`);
    }

    $(document).on('click', '.cancelBooking', function (e) {
        e.preventDefault();

        const r = misReservas[this.dataset.booking];
        cancelacion = { booking: r.num_reserva, charge: r.cancelacion.cargo };

        resumenCancelacion(r, r.cancelacion.cargo);
        $('#cancelBookingMessage').hide();
        $('#cancelBookingConfirm').prop('disabled', false).show();
        $('#cancelBookingClose').text('Keep booking');

        modal('myBookingsDialog').hide();
        modal('cancelBookingDialog').show();
    });

    $('#cancelBookingConfirm').click(function (e) {
        e.preventDefault();

        const boton = this;
        boton.disabled = true;

        $.ajax({
            type: 'POST',
            url: 'api/cancelBooking.php',
            data: cancelacion,
            dataType: 'JSON',
            success: function (response) {
                const cargo = response.charge > 0 ? ` A charge of ${euros(response.charge)} applies.` : ' No charge applies.';
                $('#cancelBookingMessage').attr('class', 'alert alert-success mt-3 mb-0').text('Your booking has been cancelled.' + cargo + ' We have sent you a confirmation email.').show();
                $(boton).hide();
                $('#cancelBookingClose').text('Close');
            },
            error: function (xhr) {
                const respuesta = xhr.responseJSON || {};

                if (respuesta.code === 7) {
                    // El cargo ha cambiado: lo mostramos y pedimos confirmar otra vez
                    cancelacion.charge = respuesta.charge;
                    resumenCancelacion(misReservas[cancelacion.booking], respuesta.charge);
                    $('#cancelBookingMessage').attr('class', 'alert alert-warning mt-3 mb-0').text(respuesta.message).show();
                    boton.disabled = false;
                    return;
                }

                $('#cancelBookingMessage').attr('class', 'alert alert-danger mt-3 mb-0').text(respuesta.message || 'The booking could not be cancelled. Try again.').show();
                $(boton).hide();
                $('#cancelBookingClose').text('Close');
            }
        });
    });

    // Al cerrar el diálogo volvemos a la lista de reservas actualizada
    $('#cancelBookingDialog').on('hidden.bs.modal', function () {
        cargarReservas();
        modal('myBookingsDialog').show();
    });

    // Repetimos la búsqueda pendiente tras el login
    try {
        const pendiente = JSON.parse(sessionStorage.getItem(BUSQUEDA_PENDIENTE));

        if (pendiente && hayUsuario() && document.getElementById('searchRooms')) {
            sessionStorage.removeItem(BUSQUEDA_PENDIENTE);

            if (window.pickerFechas) {
                window.pickerFechas.setDateRange(new Date(pendiente.checkin + 'T00:00:00'), new Date(pendiente.checkout + 'T00:00:00'));
            }

            buscarHabitaciones(pendiente.checkin, pendiente.checkout);
        }
    } catch {
        // Búsqueda pendiente no válida: la ignoramos
    }
});
