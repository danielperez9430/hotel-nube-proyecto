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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(s::tokenCsrf()) ?>">
    <title>Administration panel · Hotel Nube</title>

    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <meta name="theme-color" content="#1F2929">
    <!-- Librerías (copias locales en src/vendor) -->
    <link href="src/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="src/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="src/vendor/fontawesome/css/all.min.css"/>
    <link rel="stylesheet" href="src/css/adminPanel.css">
    <script src="src/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="src/vendor/jquery/jquery.min.js"></script>
    <script src="src/js/csrf.js"></script>
    <script src="src/js/adminPanel.js"></script>
</head>
<body>
    <div class="topBar">
        <button id="goBack" aria-label="Back to the website"><i class="fas fa-arrow-left"></i></button>
        <img src="src/logo/hotel_nube_sin_fondo.svg" height="34" alt="Hotel Nube">
        <span>Administration panel</span>
    </div>

    <main class="mainContent">
        <ul class="nav nav-tabs admin-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tabRooms" data-bs-toggle="tab" data-bs-target="#panelRooms" type="button" role="tab" aria-controls="panelRooms" aria-selected="true"><i class="fas fa-bed"></i> Rooms</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabBookings" data-bs-toggle="tab" data-bs-target="#panelBookings" type="button" role="tab" aria-controls="panelBookings" aria-selected="false"><i class="fas fa-calendar-check"></i> Bookings</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabServices" data-bs-toggle="tab" data-bs-target="#panelServices" type="button" role="tab" aria-controls="panelServices" aria-selected="false"><i class="fas fa-concierge-bell"></i> Services</button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Habitaciones -->
            <section class="tab-pane fade show active" id="panelRooms" role="tabpanel" aria-labelledby="tabRooms">
                <div class="admin-toolbar">
                    <p class="text-muted mb-0">Click a room to edit or remove it.</p>
                    <button type="button" class="btn btn-primary" id="addRoom"><i class="fas fa-plus"></i> Add room</button>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Room no.</th>
                                <th scope="col">Type</th>
                                <th scope="col">Size (m²)</th>
                                <th scope="col">Windows</th>
                                <th scope="col">Internet</th>
                                <th scope="col">Cleaning service</th>
                                <th scope="col">Price (€)</th>
                            </tr>
                        </thead>
                        <tbody class="table_content"></tbody>
                    </table>
                </div>
            </section>

            <!-- Reservas y ocupación -->
            <section class="tab-pane fade" id="panelBookings" role="tabpanel" aria-labelledby="tabBookings">
                <div class="occupancy" id="occupancy"></div>
                <div class="admin-toolbar">
                    <div class="btn-group" role="group" aria-label="Filter bookings">
                        <input type="radio" class="btn-check" name="bookingFilter" id="filterUpcoming" value="proximas" checked>
                        <label class="btn btn-outline-secondary" for="filterUpcoming">Upcoming</label>
                        <input type="radio" class="btn-check" name="bookingFilter" id="filterCancelled" value="canceladas">
                        <label class="btn btn-outline-secondary" for="filterCancelled">Cancelled</label>
                        <input type="radio" class="btn-check" name="bookingFilter" id="filterAll" value="todas">
                        <label class="btn btn-outline-secondary" for="filterAll">All</label>
                    </div>
                </div>
                <div id="bookingsList"></div>
            </section>

            <!-- Servicios -->
            <section class="tab-pane fade" id="panelServices" role="tabpanel" aria-labelledby="tabServices">
                <div class="admin-toolbar">
                    <p class="text-muted mb-0">Unavailable services are hidden from the website and cannot be booked.</p>
                    <button type="button" class="btn btn-primary" id="addService"><i class="fas fa-plus"></i> Add service</button>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col">Service</th>
                                <th scope="col">Description</th>
                                <th scope="col" class="text-end">Price (€)</th>
                                <th scope="col">Available</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTable"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- Crear o editar una habitación -->
    <div class="modal fade" id="roomSettingsDialog" tabindex="-1" aria-labelledby="roomSettingsTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roomSettingsTitle">Room settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body roomSettingsContent">
                    <div class="roomNo" id="roomNo">
                        <span>Room <span id="roomNoValue"></span></span>
                    </div>
                    <div id="roomMessage" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="roomTypeSelect" class="form-label">Type</label>
                        <select class="form-select" id="roomTypeSelect"></select>
                    </div>
                    <div class="roomSizeDiv">
                        <div class="mb-3">
                            <label for="roomSizeField" class="form-label">Size</label>
                            <input type="number" class="form-control" id="roomSizeField" placeholder="Room size" min="1" max="9999" step="0.01">
                        </div>
                        <span>m²</span>
                    </div>
                    <div>
                        <span class="form-label">Options</span>
                    </div>
                    <div class="checkboxes mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="roomWindowsField">
                            <label class="form-check-label" for="roomWindowsField">Windows</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="roomInternetCheck">
                            <label class="form-check-label" for="roomInternetCheck">Internet service</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="roomCleaningCheck">
                            <label class="form-check-label" for="roomCleaningCheck">Cleaning service</label>
                        </div>
                    </div>
                    <div class="roomPriceDiv mt-2">
                        <div class="mb-3">
                            <label for="roomPriceField" class="form-label">Price per night</label>
                            <input type="number" class="form-control" id="roomPriceField" placeholder="Room price" min="0" max="9999" step="0.01">
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

    <!-- Crear o editar un servicio -->
    <div class="modal fade" id="serviceDialog" tabindex="-1" aria-labelledby="serviceTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceTitle">Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="modal-body" id="serviceForm" novalidate>
                    <div id="serviceMessage" class="alert alert-danger" style="display: none;"></div>
                    <input type="hidden" id="serviceId">
                    <div class="mb-3">
                        <label for="serviceName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="serviceName" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label for="serviceDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="serviceDescription" rows="3" maxlength="255" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3 text-start">
                            <label for="servicePrice" class="form-label">Price (€, 0 = included)</label>
                            <input type="number" class="form-control" id="servicePrice" min="0" max="9999" step="0.01" required>
                        </div>
                        <div class="col-6 mb-3 text-start">
                            <label for="serviceIcon" class="form-label">Icon <i id="serviceIconPreview" class="fas fa-concierge-bell"></i></label>
                            <input type="text" class="form-control" id="serviceIcon" placeholder="fas fa-spa" maxlength="50">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="serviceAvailable" checked>
                        <label class="form-check-label" for="serviceAvailable">Available on the website</label>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger me-auto" id="removeService"><i class="fas fa-trash-alt"></i> Remove</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" form="serviceForm" id="saveService"><i class="fas fa-save"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
