<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hotel Nube</title>

        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet"
              integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
        <link href="src/css/style.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
                integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
        crossorigin="anonymous"></script>

        <!-- Fonts Awesome -->
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

        <!-- Jquery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 

        <!-- Litepicker -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

        <script src="src/js/roomSelection.js"></script<script src="src/js/userAccountSettings.js"></script>
        <link rel="stylesheet" href="src/css/roomSelection.css">
        <link rel="stylesheet" href="src/css/style.css">
    </head>

    <?php
    if (isset($_SESSION['userId']) && isset($_SESSION['userName']) && isset($_SESSION['userEmail'])) {
        $sessionLoaded = true;
    } else {
        $sessionLoaded = false;
    }
    ?>

    <body>

        <!-- Barra de navegación -->
        <nav class="navbar navbar-expand-lg navbar-light bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="src/logo/hotel_nube.svg" height="40" /></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav align-items-center">
                        <a class="nav-link active text-light" aria-current="page" href="#">Home</a>
                        <a class="nav-link text-light" href="#">Promotions</a>
                        <a class="nav-link text-light" href="#">Services</a>
                        <a class="nav-link text-light" href="contact.php">Contact</a>

                        <div class="navbar-nav login" style="<?php echo $sessionLoaded == true ? 'display: none;' : '' ?>">
                            <a class="nav-link text-light me-auto" data-bs-toggle="modal" data-bs-target="#loginDialog">Login/Register</a>
                        </div>
                        <div class="userButtonDiv" style="<?php echo $sessionLoaded == false ? 'display: none;' : '' ?>">
                            <button id="userButton"><i class="fas fa-user"></i><span id="userName"><?php echo $_SESSION['userName'] ?></span></button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div id="userLoggedMenuBase" style="display: none;"></div>
        <div id="userLoggedMenu" style="display: none;">
            <div class="topDiv">
                <button id="closeLogout"><i class="fas fa-times"></i></button>
            </div>
            <div class="logoutContent">
                <span id="fullUserName"><i class="fas fa-user"></i><?php echo $sessionLoaded ? $_SESSION['userName'] : '' ?></span>
                <span id="email"><i class="fas fa-at"></i><?php echo $sessionLoaded ? $_SESSION['userEmail'] : '' ?></span>
                <?php
                if ($_SESSION['rol'] === 'admin') {
                    echo '<button id="adminPanel" ><i class="fas fa-sliders-h"></i>Admin panel</button>';
                }
                ?>
                <div class="logoutButtons">
                    <button id="userSettings" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userSettingsDialog"><i class="fas fa-cog"></i></button>
                    <button id="logOut" class="btn btn-primary"><i class="fas fa-door-open"></i>Log out</button>
                </div>
            </div>
        </div>



        <!-- Sección de login de la página -->
        <div class="modal fade" data-bs-backdrop="static" tabindex="-1" id="loginDialog">
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
                        <i class="fas fa-times"></i>
                        <span>Login failed, retry again.</span>
                    </div>
                    <div id="loginSuccessful" style="display: none">
                        <i class="fas fa-check"></i>
                        <span>Login successful.</span>
                    </div>
                </div>
            </div>
        </div>


        <!-- Editar datos de usuario -->

        <div class="modal fade" id="userSettingsDialog" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">User accout settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="settingsInputs">
                            <input type="text" class="form-control" id="userNameEditField" placeholder="Name">
                            <input type="password" class="form-control" id="passwordEditField" placeholder="Password">
                            <input type="password" class="form-control" id="rePasswordEditField" placeholder="Check password">
                            <div class="phone">
                                <div class="label"><span>Phone number</span></div>
                                <div>
                                    <input type="text" class="form-control" id="phoneCodeEditField" placeholder="+12" autocomplete="off" maxlength="4" required pattern="/^\+[1-9]\d?\d?$/">
                                    <input type="text" class="form-control" id="phoneEditField" placeholder=123456779 autocomplete="off" maxlength="9" required pattern="/^\d{9}$/">
                                </div>
                            </div>
                            <input type="text" class="form-control" id="addressEditField" placeholder="Address" autocomplete="off" required>
                            <div class="selectLabel"><span>Country</span></div>
                            <select class="form-select" id="countryEditField"></select>
                            <div class="selectLabel"><span>Province</span></div>
                            <select class="form-select" id="provinceEditField"></select>
                            <div class="selectLabel"><span class="selectLabel">City</span></div>
                            <select class="form-select" id="cityEditField"></select>
                        </div>
                        <button id="saveSettings" class="btn btn-primary"><i class="fas fa-save"></i>Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="src/js/login.js"></script>
        <script src="src/js/logout.js"></script>
        <script src="src/js/userAccountSettings.js"></script>