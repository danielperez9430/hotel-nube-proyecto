$(document).ready(function () {
    var loginDialog = document.getElementById('loginDialog');
    loginDialog.addEventListener('shown.bs.modal', function () {
       
    });

    const getCountries = async () => {
        const response = await fetch(`${API_URL}/paises`).then(response => {return response.json()});
        rellenarSelect('registerCountry', response, 'Select one country');
    }

    // Rellena un select con opciones creadas con new Option(), así los textos
    // que llegan de la API no se interpretan como HTML
    function rellenarSelect(id, valores, textoInicial) {
        const select = document.getElementById(id);
        select.innerHTML = '';

        const inicial = new Option(textoInicial, '', true, true);
        inicial.disabled = true;
        select.add(inicial);

        valores.forEach(valor => select.add(new Option(valor, valor)));
        select.disabled = valores.length === 0;
    }
    
    getCountries();

    $('#goRegister').click(function (e) { 
        e.preventDefault();
        
        $('#logRegTitle').html('Register');
        $('#loginContent').css('display', 'none');
        $('#registerContent').css('display', 'flex');
    });

    $('#goLogin').click(function (e) { 
        e.preventDefault();
        
        $('#logRegTitle').html('Login');
        $('#registerContent').css('display', 'none');
        $('#loginContent').css('display', 'flex');
    });

    $('#loginEmail, #loginPassword').on('input', function () {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        document.getElementById('loginBtn').disabled = (email == '' || password == '');
    });

    // Muestra un mensaje durante 3 segundos tras ocultar el spinner
    function mostrarResultado(selector) {
        $('#spinnerContainer').css('display', 'none');
        $(selector).css('display', 'flex');
        setTimeout(() => $(selector).css('display', 'none'), 3000);
    }

    $('#loginBtn').click(function (e) { 
        e.preventDefault();

        $('#spinnerContainer span').html('Logging in...');
        $('#loginFail span').html('Login failed, retry again.');
        $('#loginSuccessful span').html('User logged in successfully.');
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const keepLogged = document.getElementById('keepLogged').checked;

        $('#spinnerContainer').css('display', 'flex');

        $.ajax({
            type: "POST",
            url: "api/login.php",
            data: {
                email,
                password,
                keepLogged
            },
            dataType: "JSON",
            success: function (response) {
                if (response.result == 'success' && response.code == 0) {
                    mostrarResultado('#loginSuccessful');
                    // login
                }
                else {
                    mostrarResultado('#loginFail');
                }
            },
            error: function (xhr) {
                if (xhr.status == 429) {
                    $('#loginFail span').html('Too many failed attempts. Try again in 15 minutes.');
                }
                mostrarResultado('#loginFail');
            }
        });
    });


    $('#registerCountry').change(function () { 
        const value = document.getElementById("registerCountry").value;

        $('#registerProvince').html('');
        $('#registerCity').html('');

        $('#registerProvince').append(`<option value="">Loading...</option>`);

        document.getElementById('registerProvince').disabled = true;
        document.getElementById('registerCity').disabled = true;

        fetch(`${API_URL}/buscar/${encodeURIComponent(value)}`).then(response =>  response.json()).then(data => {
            rellenarSelect('registerProvince', data, 'Select one province');
        });

        actualizarBotonRegistro();
    });

    $('#registerProvince').change(function () { 
        const country = document.getElementById("registerCountry").value;
        const province = document.getElementById("registerProvince").value;

        $('#registerCity').html('');
        document.getElementById('registerCity').disabled = true;
        $('#registerCity').append(`<option value="">Loading...</option>`);

        fetch(`${API_URL}/buscar/${encodeURIComponent(country)}/${encodeURIComponent(province)}/`).then(response =>  response.json()).then(data => {
            rellenarSelect('registerCity', data[0], 'Select one city');
        });

        actualizarBotonRegistro();
    });

    function getFormFields() {
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const name = document.getElementById('registerName').value;
        const phoneCode = document.getElementById('registerPhoneCode').value;
        const phoneNumber = document.getElementById('registerPhoneNumber').value;
        const address = document.getElementById('registerAddress').value;
        const city = document.getElementById('registerCity').value;
        const province = document.getElementById('registerProvince').value;
        const country = document.getElementById('registerCountry').value;

        return Boolean(email && password && name && phoneCode && phoneNumber && address && city && province && country);
    }

    function actualizarBotonRegistro() {
        document.getElementById('registerBtn').disabled = !getFormFields();
    }

    $('#registerContent').on('input change', 'input, select', actualizarBotonRegistro);

    $('#registerBtn').click(function (e) { 
        e.preventDefault();
        
        $('#spinnerContainer span').html('Registering...');
        $('#loginFail span').html('Register failed, retry again.');
        $('#loginSuccessful span').html('User registered successfully.');

        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const name = document.getElementById('registerName').value;
        const phoneCode = document.getElementById('registerPhoneCode').value;
        const phoneNumber = document.getElementById('registerPhoneNumber').value;
        const address = document.getElementById('registerAddress').value;
        const city = document.getElementById('registerCity').value;
        const province = document.getElementById('registerProvince').value;
        const country = document.getElementById('registerCountry').value;

        const valEmail = () => {
            const regex = /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/;
            return regex.test(email);
        };

        const valPhone = () => {
            const regexCode = /^\+[1-9]\d?\d?$/;
            const regexPhone = /^\d{9}$/;
            return regexCode.test(phoneCode) && regexPhone.test(phoneNumber);
        };

        if (valEmail() && password && name && valPhone() && address && city) {
            $('#registerEmail').removeAttr('style');
            $('#registerPhoneCode').removeAttr('style');
            $('#registerPhoneNumber').removeAttr('style');

            $('#spinnerContainer').css('display', 'flex');

            $.ajax({
                type: "POST",
                url: "api/register.php",
                data: {
                    email,
                    password,
                    name,
                    telf: phoneCode + ' ' + phoneNumber,
                    direccion: address,
                    ciudad: city,
                    provincia: province,
                    pais: country
                },
                dataType: "JSON",
                success: function (response) {
                    if (response.result == 'success' && response.code == 0) {
                        mostrarResultado('#loginSuccessful');
                        // login
                    }
                    else {
                        mostrarResultado('#loginFail');
                    }
                },
                error: function() {
                    mostrarResultado('#loginFail');
                }
            });
        }
        else {
            if (!valEmail()) {
                $('#registerEmail').css('border-color', 'red');
            }
            else {
                $('#registerEmail').removeAttr('style');
            }

            if (!valPhone()) {
                $('#registerPhoneCode').css('border-color', 'red');
                $('#registerPhoneNumber').css('border-color', 'red');
            }
            else {
                $('#registerPhoneCode').removeAttr('style');
                $('#registerPhoneNumber').removeAttr('style');
            }
        }
    });

    $('#logOut').click(function (e) { 
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "api/logout.php",
            dataType: "JSON",
            success: function (response) {
                if (response.result == true) {
                    window.location.reload();
                }
            }
        });
    });
});
