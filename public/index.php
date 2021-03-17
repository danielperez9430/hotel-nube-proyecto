<?php
require_once(__DIR__ . '/../views/header.php');

require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;
s::comprobar_sesion();

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

<!-- Sección de login de la página -->
<div class="modal" tabindex="-1" id="loginDialog">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logRegTitle">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="loginContent" id="loginContent">
                    <input type="email" class="form-control" id="loginEmail" placeholder="Email" autocomplete="off"\>
                    <input type="password" class="form-control" id="loginPassword" placeholder="Password" autocomplete="off"\>
                    <div class="buttons">
                        <div>
                            <input type="checkbox" id="keepLogged"/>
                            <label for="keepLogged">Keep logged in</label>
                        </div>
                        <button id="loginBtn" class="btn btn-primary" disabled>Login</button>
                    </div>
                    <div class="goTo">
                        <span id="goRegister">¿Not registered yet?</span>
                    </div>
                </div>
                <div class="registerContent" id="registerContent" style="display: none;">
                    <div class="registerInputs">
                        <div class="title"><span>Account data</span></div>
                        <input type="email" class="form-control" id="registerEmail" placeholder="Email" autocomplete="off" required\>
                        <input type="password" class="form-control" id="registerPassword" placeholder="Password" autocomplete="off" required\>
                    </div>
                    <div class="registerInputs">
                        <div class="title"><span>Personal data</span></div>
                        <input type="text" class="form-control" id="registerName" placeholder="Name" autocomplete="off" required>
                        <div class="phone">
                            <div class="label"><span>Phone number</span></div>
                            <div>
                                <input type="text" class="form-control" id="registerPhoneCode" placeholder="+12" autocomplete="off" maxlength="4" required pattern="/^\+[1-9]\d?\d?$/">
                                <input type="text" class="form-control" id="registerPhoneNumber" placeholder=123456779 autocomplete="off" maxlength="9" required pattern="/^\d{9}$/">
                            </div>
                        </div>
                        <input type="text" class="form-control" id="registerAddress" placeholder="Address" autocomplete="off" required>
                        <div class="selectLabel"><span>Country</span></div>
                        <select class="form-select" id="registerCountry">
                            <option value="loading">Loading...</option>
                        </select>
                        <div class="selectLabel"><span>Province</span></div>
                        <select class="form-select" id="registerProvince" disabled></select>
                        <div class="selectLabel"><span class="selectLabel">City</span></div>
                        <select class="form-select" id="registerCity" disabled></select>
                    </div>
                    <div class="buttons">
                        <button id="registerBtn" class="btn btn-primary" disabled>Register</button>
                    </div>
                    <div class="goTo">
                        <span id="goLogin">¿Alredy registered?</span>
                    </div>
                </div>
            </div>
            <div id="spinnerContainer" style="display: none">
                <div class="spinner-border" role="status"></div>
                <span>Logging in...</span>
            </div>
            <div id="loginFail" style="display: none">
                <span>Login failed, retry again.</span>
            </div>
            <div id="loginSuccessful" style="display: none">
                <span>Login successful.</span>
            </div>
        </div>
    </div>
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
                <button type="button" class="btn btn-primary buscar"><i class="fas fa-search"></i>Search</button>
            </div>
        </div>
    </div>
</form>

<?php
require_once(__DIR__ . '/../views/footer.php');
?>