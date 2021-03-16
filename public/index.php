<?php
require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;
s::comprobar_sesion();

require_once(__DIR__ . '/../views/header.php');
?>


<script src="src/js/datepickers.js"></script>

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

<div class="windowBackground" id="selectRoomWindow">
    <div class="windowContainer">
        <div class="windowNavbar">
            <span class="title">Choose a room</span>
            <button id="cancelChooseRoomButton">Cancel</button>
        </div>
        <div id="roomSelection">
           
        </div>
    </div>
</div>

<?php
require_once(__DIR__ . '/../views/footer.php');
?>