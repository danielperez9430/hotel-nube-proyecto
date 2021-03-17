$(document).ready(function () {
    var loginDialog = document.getElementById('loginDialog');
    loginDialog.addEventListener('shown.bs.modal', function () {
       
    });

    const getCountries = async () => {
        const response = await fetch('https://api.hotelnube.ml/paises').then(response => {return response.json()});
        $('#registerCountry').html('');
        $('#registerCountry').append(`<option value="" disabled="true" selected>Select one country</option>`);
        response.map(country => {
            $('#registerCountry').append(`<option value='${country}'>${country}</option>`);
        })
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

    $('#loginEmail').on('input', function () {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        if (email != '' && password != '') {
            document.getElementById('loginBtn').disabled = false;
        }
        else {
            document.getElementById('loginBtn').disabled = true;
        }
    });

    $('#loginPassword').on('input', function () {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        if (email != '' && password != '') {
            document.getElementById('loginBtn').disabled = false;
        }
        else {
            document.getElementById('loginBtn').disabled = true;
        }
    });

    $('#loginBtn').click(function (e) { 
        e.preventDefault();

        $('#spinnerContainer span').html('Logging in...');
        $('#loginFail span').html('Login failed, retry again.');
        $('#loginSuccessful span').html('User logged in successfully.');
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const keepLogged = document.getElementById('keepLogged').value;
  
        $.ajax({
            type: "POST",
            url: "/Hotel_Nube/public/api/login.php",
            data: {
                email,
                password,
                keepLogged
            },
            dataType: "JSON",
            success: function (response) {
                if (response.result == 'success' && response.code == 0) {
                    $('#spinnerContainer').css('display', 'none');
                    $('#loginSuccessful').css('display', 'flex');
                    const timeout = setTimeout(() => {
                        $('#loginSuccessful').css('display', 'none');
                        clearTimeout(timeout);
                    }, 3000);
                    // login
                }
                else {
                    $('#spinnerContainer').css('display', 'none');
                    $('#loginFail').css('display', 'flex');
                    const timeout = setTimeout(() => {
                        $('#loginFail').css('display', 'none');
                        clearTimeout(timeout);
                    }, 3000);
                }
            },
            error: function () {
                $('#spinnerContainer').css('display', 'none');
                $('#loginFail').css('display', 'flex');
                const timeout = setTimeout(() => {
                    $('#loginFail').css('display', 'none');
                    clearTimeout(timeout);
                }, 3000);
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

        fetch(`https://api.hotelnube.ml/buscar/${value}`).then(response =>  response.json()).then(data => {
            $('#registerProvince').html('');
            $('#registerProvince').append(`<option value="" disabled="true" selected>Select one province</option>`);
            data.map(province => {
                $('#registerProvince').append(`<option value='${province}'>${province}</option>`);
                document.getElementById('registerProvince').disabled = false;
            });
        });

        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerProvince').change(function () { 
        const country = document.getElementById("registerCountry").value;
        const province = document.getElementById("registerProvince").value;

        $('#registerCity').html('');
        document.getElementById('registerCity').disabled = true;
        $('#registerCity').append(`<option value="">Loading...</option>`);

        fetch(`https://api.hotelnube.ml/buscar/${country}/${province}/`).then(response =>  response.json()).then(data => {
            $('#registerCity').html('');
            $('#registerCity').append(`<option value="" disabled="true" selected>Select one city</option>`);
            data[0].map(city => {
                $('#registerCity').append(`<option value='${city}'>${city}</option>`);
                document.getElementById('registerCity').disabled = false;
            });
        });

        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerCity').change(function () { 
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    })

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

        if (email && password && name && phoneCode && phoneNumber && address && city && province && country) {
            return true;
        }
        else {
            return false;
        }
    }

    $('#registerEmail').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerPassword').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerName').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerPhoneCode').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerPhoneNumber').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerAddress').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerCity').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerProvince').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

    $('#registerCountry').on('input', function () {
        if (getFormFields() == true) {
            document.getElementById('registerBtn').disabled = false;
        }
        else {
            document.getElementById('registerBtn').disabled = true;
        }
    });

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
            if (regex.test(email)) {
                return true;
            }
            else {
                return false;
            }
        };

        const valPhone = () => {
            const regexCode = /^\+[1-9]\d?\d?$/;
            const regexPhone = /^\d{9}$/;
            console.log(regexCode.test(phoneCode), regexPhone.test(phoneNumber))
            if (regexCode.test(phoneCode) && regexPhone.test(phoneNumber)) {
                return true;
            }
            else {
                return false;
            }
        }

        if (valEmail() && password && name && valPhone() && address && city) {
            $('#registerEmail').removeAttr('style');
            $('#registerPhoneCode').removeAttr('style');
            $('#registerPhoneNumber').removeAttr('style');

            $.ajax({
                type: "POST",
                url: "/Hotel_Nube/public/api/register.php",
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
                        $('#spinnerContainer').css('display', 'none');
                        $('#loginSuccessful').css('display', 'flex')
                        const timeout = setTimeout(() => {
                            $('#loginSuccessful').css('display', 'none');
                            clearTimeout(timeout);
                        }, 3000);
                        // login
                    }
                    else {
                        $('#spinnerContainer').css('display', 'none');
                        $('#loginFail').css('display', 'flex');
                        const timeout = setTimeout(() => {
                            $('#loginFail').css('display', 'none');
                            clearTimeout(timeout);
                        }, 3000);
                    }
                },
                error: function() {
                    $('#spinnerContainer').css('display', 'none');
                    $('#loginFail').css('display', 'flex');
                    const timeout = setTimeout(() => {
                        $('#loginFail').css('display', 'none');
                        clearTimeout(timeout);
                    }, 3000);
                }
            });
        }
        else {
            if (valEmail() == false) {
                $('#registerEmail').css('border-color', 'red');
            }
            else {
                $('#registerEmail').removeAttr('style');
            }

            if (valPhone() == false) {
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

$('#logOut').click(function (e) { 
    e.preventDefault();
    
    $.ajax({
        type: "POST",
        url: "php/logout.php",
        dataType: "JSON",
        success: function (response) {
            if (response.result == true) {
                window.location.reload();
            }
        },
        error: function () {
            
        }
    });
});