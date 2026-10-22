<?php
require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;

// La sesión debe iniciarse antes de enviar cualquier HTML
s::comprobar_sesion();

require_once(__DIR__ . '/../views/header.php');

?>

<!-- Carroules -->
<div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="src/img/carousel/1.jpg" class="d-block w-100" alt="...">
        </div>
        <div class="carousel-item">
            <img src="src/img/carousel/2.jpg" class="d-block w-100" alt="...">
        </div>
        <div class="carousel-item">
            <img src="src/img/carousel/3.jpg" class="d-block w-100" alt="...">
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls"  data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls"  data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- Contenido de la página -->

<form>
    <div class="formDateBaseContainer">
        <div class="formDateContainer">
            <span class="tituloSelectorFecha">Select your stay</span>
            <div class="selectoresFecha">
                <div class="divSelectorFecha">
                    <span>Check-in</span>
                    <i class="far fa-calendar"></i>
                    <input type="text" name="fecha_entrada" id="fechaInicio" class="dateSelector"/>
                </div>
                <div class="divSelectorFecha">
                    <span>Check-out</span>
                    <i class="far fa-calendar"></i>
                    <input type="text" name="fecha_salida" id="fechaFin" class="dateSelector"/>
                </div>
            </div>
            <div class="divBotonBuscar">
                <button type="button" class="btn btn-primary buscar" id="searchRooms"><i class="fas fa-search"></i>Search</button>
            </div>
        </div>
    </div>
</form>

<!-- Habitaciones disponibles -->
<div class="modal fade" id="roomsDialog" tabindex="-1" aria-labelledby="roomsDialogTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="roomsDialogTitle">Available rooms</h5>
                    <span id="roomsDialogStay" class="text-muted"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="roomsMessage" class="alert alert-info" style="display: none;"></div>
                <div id="roomSelection"></div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmación de la reserva -->
<div class="modal fade" id="bookingDialog" tabindex="-1" aria-labelledby="bookingDialogTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingDialogTitle">Confirm your booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bookingSummary"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="bookingCancel">Cancel</button>
                <button type="button" class="btn btn-primary" id="bookingConfirm"><i class="fas fa-check"></i> Confirm booking</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once(__DIR__ . '/../views/footer.php');
?>