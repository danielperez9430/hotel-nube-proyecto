$(document).ready(function () {
    var loginDialog = document.getElementById('loginDialog');

    // El diálogo solo existe para los visitantes sin sesión
    if (!loginDialog) {
        return;
    }

    // Cargamos los países la primera vez que se abre el diálogo
    var paisesCargados = false;

    loginDialog.addEventListener('shown.bs.modal', function () {
        if (!paisesCargados) {
            paisesCargados = true;
            getCountries();
        }
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

    $('#goRegister').click(function (e) { 
        e.preventDefault();
        
        mostrarSeccion('registerContent', 'Register');
    });

    // Muestra una de las secciones del diálogo (login, registro o recuperación)
    function mostrarSeccion(id, titulo) {
        $('#logRegTitle').text(titulo);
        $('#loginContent, #registerContent, #forgotContent').css('display', 'none');
        $('#' + id).css('display', 'flex');
    }

    $('.goLoginLink').click(function (e) {
        e.preventDefault();
        mostrarSeccion('loginContent', 'Login');
    });

    $('#goForgot').click(function (e) {
        e.preventDefault();
        $('#forgotEmail').val($('#loginEmail').val()).trigger('input');
        $('#forgotMessage').hide();
        mostrarSeccion('forgotContent', 'Forgot your password');
    });

    $('#forgotEmail').on('input', function () {
        document.getElementById('forgotBtn').disabled = !this.value || !this.checkValidity();
    });

    $('#forgotBtn').click(function (e) {
        e.preventDefault();

        const boton = this;
        boton.disabled = true;

        $.ajax({
            type: 'POST',
            url: 'api/forgotPassword.php',
            data: { email: document.getElementById('forgotEmail').value },
            dataType: 'JSON',
            success: function (response) {
                $('#forgotMessage').attr('class', 'alert alert-success mb-0').text(response.message).show();
            },
            error: function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The link could not be sent. Try again.';
                $('#forgotMessage').attr('class', 'alert alert-danger mb-0').text(mensaje).show();
                boton.disabled = false;
            }
        });
    });

    // index.php?login=1 abre el login e index.php?forgot=1 la recuperación
    const parametros = new URLSearchParams(window.location.search);

    if (parametros.has('login') || parametros.has('forgot')) {
        if (parametros.has('forgot')) {
            mostrarSeccion('forgotContent', 'Forgot your password');
        }
        bootstrap.Modal.getOrCreateInstance(loginDialog).show();
    }

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
                    // Recargamos para mostrar la cabecera con la sesión iniciada
                    setTimeout(() => window.location.reload(), 800);
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

        // Validación de email del propio navegador (input type="email");
        // el servidor la vuelve a comprobar con filter_var
        const valEmail = () => email !== '' && document.getElementById('registerEmail').checkValidity();

        const valPhone = () => {
            const regexCode = /^\+[1-9]\d?\d?$/;
            const regexPhone = /^\d{9}$/;
            return regexCode.test(phoneCode) && regexPhone.test(phoneNumber);
        };

        const valPassword = () => password.length >= 8;

        if (valEmail() && valPassword() && name && valPhone() && address && city) {
            $('#registerEmail').removeAttr('style');
            $('#registerPassword').removeAttr('style');
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
                        // El registro inicia la sesión: recargamos para mostrarla
                        setTimeout(() => window.location.reload(), 800);
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

            if (!valPassword()) {
                $('#registerPassword').css('border-color', 'red');
            }
            else {
                $('#registerPassword').removeAttr('style');
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
});
