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

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="src/css/style.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
    
    <!-- Fonts Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="src/css/adminPanel.css">
    <script src="src/js/csrf.js"></script>
    <script src="src/js/adminPanel.js"></script>
</head>
<body>
    <div class="topBar">
        <button id="goBack"><i class="fas fa-arrow-left"></i></button>
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