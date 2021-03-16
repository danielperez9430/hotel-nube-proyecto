$(document).ready(function () {
    $('#searchRooms').click(function (e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "api/room_type.php",
            data: {
                checkin: document.getElementById('fechaInicio').value,
                checkout: document.getElementById('fechaFin').value
            },
            dataType: "JSON",
            success: function (response) {
                console.log(response);

                $.each(response, function (i, item) {


                    var wifi = (item.internet === '1') ? "available" : "notavailable";
                    var ventana = (item.ventana === '1') ? "available" : "notavailable";
                    var servicio_limpieza = (item.servicio_limpieza === '1') ? "available" : "notavailable";

                    var habitacion = `<div class="room">
                <div class="top">
                    <div id="picture">
                        <img src="${item.imagenes[0][0]}" alt="${item.imagenes[0][1]}">
                    </div>
                    <div class="content">
                        <span id="name">${item.tipo_habitacion}</span>
                        <span id="description">${item.descripcion}</span>
                        <div class="details">
                            <div class="detail">
                                <i class="fas fa-ruler"></i>
                                <span id="roomSize">${Math.round(item.m2)} m<sup>2</sup></span>
                            </div>
                            <div class="detail">
                                <img src="src/icons/window.svg" alt="Number of Windows">
                                <span class="${ventana}" id="windows" value="${item.ventana}"> Windows</span>      
                            </div>
                             <div class="detail">
                             <img src="src/icons/vacuum.svg" alt="Clear Service">
                             <span class="${servicio_limpieza}" id="ClearService" value="${item.servicio_limpieza}"> Clear Service</span>
                            </div>
                            <div class="detail">
                             <i class="fas fa-wifi"></i>
                             <span class="${wifi}" id="internet" value="${item.internet}">Wifi</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="price">
                    <span id="summary">${Math.round(item.precio)}€/night</span>
                    <button class="btn btn-primary confirmRoomSelection" roomid="${item.id}"><i class="fas fa-check"></i> Choose this room</button>
                </div>
            </div>`;
                    $('#roomSelection').append(habitacion);
                });

            },
            error: function () {

            }
        });

        $('#selectRoomWindow').addClass('windowBackgroundOpen');
        $('#selectRoomWindow').css('display', 'flex');

    });

    $('#cancelChooseRoomButton').click(function (e) {
        e.preventDefault();

        $('#selectRoomWindow').removeClass('windowBackgroundOpen');
        const interval = setInterval(() => {
            $('#selectRoomWindow').css('display', 'none');
            clearInterval(interval);
        }, 300);
    });

});