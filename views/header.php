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
        
        <!-- Fonts Awesome -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 

        <!-- Litepicker -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

        <script src="src/js/login.js"></script>
        <script src="src/js/datepickers.js"></script>
    </head>

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
                        <a class="nav-link text-light" href="#">Contact</a>

                        <div class="navbar-nav login">
                            <a class="nav-link text-light me-auto" data-bs-toggle="modal" data-bs-target="#loginDialog">Login/Register</a>
                        </div>
                        <div class="userButtonDiv" style="display: none;">
                            <button id="userButton"><i class="fas fa-user"></i><span id="userName">Juan</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div id="userLoggedMenu" style="display: none;">
            <span id="fullUserName">Juan Gilsanz Polo</span>
            <button id="logOut" class="btn btn-primary"><i class="fas fa-door-open"></i>Log out</button>
        </div>

