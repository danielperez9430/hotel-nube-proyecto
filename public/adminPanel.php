<?php
require_once(__DIR__ . '/../autoload.php');
use \Session\session as s;

s::comprobar_sesion();

s::comprobarAdmin();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(s::tokenCsrf()) ?>">
    <title>Administration panel</title>

<link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <meta name="theme-color" content="#1F2929">
    <!-- Librerías (copias locales en src/vendor) -->
    <!-- Bootstrap 5 -->
    <link href="src/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="src/css/style.css" rel="stylesheet" />
    <script src="src/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- Fonts Awesome -->
    <link rel="stylesheet" href="src/vendor/fontawesome/css/all.min.css"/>
    
    <!-- jQuery -->
    <script src="src/vendor/jquery/jquery.min.js"></script>

    <link rel="stylesheet" href="src/css/adminPanel.css">
    <script src="src/js/csrf.js"></script>
    <script src="src/js/adminPanel.js"></script>
</head>
<body>
    <div class="topBar">
        <button id="goBack" aria-label="Back to the website"><i class="fas fa-arrow-left"></i></button>
        <img src="src/logo/hotel_nube_sin_fondo.svg" height="34" alt="Hotel Nube">
        <span>Administration panel</span>
    </div>
    <div class="mainContent">
        <div class="roomsTableContainer">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Room no.</th>
                        <th scope="col">Type</th>
                        <th scope="col">Size</th>
                        <th scope="col">No. windows</th>
                        <th scope="col">Internet</th>
                        <th scope="col">Cleaning service</th>
                        <th scope="col">Price</th>
                    </tr>
                </thead>
                <tbody class="table_content">
    <!-- Data here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Room settings window -->
    <div class="modal static fade" id="roomSettingsDialog" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Room settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body roomSettingsContent">
                    <div class="roomNo">
                        <span>Room <span id="roomNoValue">3</span></span>
                    </div>
                    <div class="mb-3">
                        <label for="roomTypeSelect" class="form-label">Type</label>
                        <select class="form-select" id="roomTypeSelect">
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <div class="roomSizeDiv">
                        <div class="mb-3">
                            <label for="roomSizeField" class="form-label">Size</label>
                            <input type="number" class="form-control" id="roomSizeField" placeholder="Room size">
                        </div>
                        <span>m2</span>
                    </div>
                    <div class="">
                        <label class="form-label">Options</label>
                    </div>
                    <div class="checkboxes mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="roomWindowsField">
                            <label class="form-check-label" for="roomWindowsField">
                                Windows
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="roomInternetCheck">
                            <label class="form-check-label" for="roomInternetCheck">
                                Internet service
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="roomCleaningCheck">
                            <label class="form-check-label" for="roomCleaningCheck">
                                Cleaning service
                            </label>
                        </div>
                    </div>
                    <div class="roomPriceDiv mt-2">
                        <div class="mb-3">
                            <label for="roomPriceField" class="form-label">Price</label>
                            <input type="number" class="form-control" id="roomPriceField" placeholder="Room price">
                        </div>
                        <span>€</span>
                    </div>
                    <div class="buttonsDiv">
                        <button class="btn btn-danger" id="removeRoom" clicks="0"><i class="fas fa-trash-alt"></i><span id="removeText">Remove room</span></button>
                        <button type="button" class="btn btn-primary" id="saveRoomChanges"><i class="fas fa-save"></i>Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>