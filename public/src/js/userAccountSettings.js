$(document).ready(function () {
    var userSettingsDialog = document.getElementById('userSettingsDialog');
    userSettingsDialog.addEventListener('shown.bs.modal', function () {
        $('#userLoggedMenu').removeClass('openLogout');
        const interval = setInterval(() => {
            $('#userLoggedMenu').css('display', 'none');
            clearInterval(interval);
        }, 300);
    });

    // Variables de elementos seleccionados
    var selectedCountry = "Spain";
    var selectedProvince = 'Segovia';
    var selectedCity = 'Segovia';


    // Ejecución en la primera carga
    getProvinces();
    getCities();


    fetch('https://api.hotelnube.ml/paises').then(response => {return response.json()}).then(data => {
        data.map(country => {
            if (country == selectedCountry) {
                $('#countryEditField').append(`<option value='${country}' selected>${country}</option>`);
            }
            else {
                $('#countryEditField').append(`<option value='${country}'>${country}</option>`);
            }
        })
    });

    function getProvinces() {
        fetch(`https://api.hotelnube.ml/buscar/${selectedCountry}`).then(response => response.json()).then(data => {
            $('#provinceEditField').html('');
            data.map(province => {
                if (province == selectedProvince) {
                    $('#provinceEditField').append(`<option value='${province}' selected>${province}</option>`);
                }
                else {
                    $('#provinceEditField').append(`<option value='${province}'>${province}</option>`);
                }
            });
            document.getElementById('provinceEditField').disabled = false;
        });
    }

    function getCities() {
        fetch(`https://api.hotelnube.ml/buscar/${selectedCountry}/${selectedProvince}/`).then(response =>  response.json()).then(data => {
            $('#cityEditField').html('');
            data[0].map(city => {
                if (city == selectedCity) {
                    $('#cityEditField').append(`<option value='${city}' selected>${city}</option>`);
                }
                else {
                    $('#cityEditField').append(`<option value='${city}'>${city}</option>`);
                }
            });
            document.getElementById('cityEditField').disabled = false;
        });
    }

    $('#countryEditField').change(function () { 
        document.getElementById('provinceEditField').disabled = true;
        document.getElementById('cityEditField').disabled = true;

        selectedCountry = document.getElementById('countryEditField').value;

        $('#provinceEditField').html('<select value="" selected>Loading...</select>');
        $('#cityEditField').html('<option value="" selected>Select one province first</option>');

        getProvinces();
    });

    $('#provinceEditField').change(function () { 
        document.getElementById('cityEditField').disabled = true;

        selectedProvince = document.getElementById('provinceEditField').value;

        $('#cityEditField').html('<select value="" selected>Loading...</select>');

        getCities();
    });

    $('#cityEditField').change(function (e) { 
        e.preventDefault();
        
        selectedCity = document.getElementById('cityEditField').value;
    });
});