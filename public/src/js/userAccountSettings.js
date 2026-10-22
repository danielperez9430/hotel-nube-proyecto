// Ajustes de la cuenta: carga los datos del usuario y guarda los cambios
$(document).ready(function () {

    const dialogo = document.getElementById('accountDialog');

    // Solo existe para los usuarios con sesión iniciada
    if (!dialogo) {
        return;
    }

    const modal = () => bootstrap.Modal.getOrCreateInstance(dialogo);

    function mostrarMensaje(texto, tipo) {
        $('#accountMessage').attr('class', 'alert alert-' + tipo).text(texto).show();
    }

    // Rellena un select con new Option(), sin interpretar los textos como HTML
    function rellenarSelect(id, valores, seleccionado, textoInicial) {
        const select = document.getElementById(id);
        select.innerHTML = '';

        const inicial = new Option(textoInicial, '', !seleccionado, !seleccionado);
        inicial.disabled = true;
        select.add(inicial);

        valores.forEach(valor => select.add(new Option(valor, valor, false, valor === seleccionado)));

        // Si el valor guardado no está en la lista lo conservamos
        if (seleccionado && !valores.includes(seleccionado)) {
            select.add(new Option(seleccionado, seleccionado, true, true));
        }

        select.disabled = false;
    }

    const pedir = ruta => fetch(`${API_URL}${ruta}`).then(respuesta => respuesta.json());

    function cargarProvincias(pais, seleccionada) {
        document.getElementById('accountProvince').disabled = true;
        document.getElementById('accountCity').disabled = true;

        return pedir(`/buscar/${encodeURIComponent(pais)}`)
            .then(provincias => rellenarSelect('accountProvince', provincias, seleccionada, 'Select one province'));
    }

    function cargarCiudades(pais, provincia, seleccionada) {
        document.getElementById('accountCity').disabled = true;

        return pedir(`/buscar/${encodeURIComponent(pais)}/${encodeURIComponent(provincia)}/`)
            .then(datos => rellenarSelect('accountCity', datos[0] || [], seleccionada, 'Select one city'));
    }

    // Abrir el diálogo desde el menú de usuario
    $('#accountSettings').click(function (e) {
        e.preventDefault();

        $('#userLoggedMenu').css('display', 'none');
        $('#userLoggedMenuBase').css('display', 'none');
        $('#accountMessage').hide();
        $('#accountSave').prop('disabled', true);
        modal().show();

        $.getJSON('api/account.php', function (response) {
            const cuenta = response.account;
            const [prefijo, ...numero] = String(cuenta.telf).split(' ');

            $('#accountEmail').val(cuenta.email);
            $('#accountName').val(cuenta.nombre);
            $('#accountPhoneCode').val(prefijo);
            $('#accountPhoneNumber').val(numero.join(''));
            $('#accountAddress').val(cuenta.direccion);

            Promise.all([
                pedir('/paises').then(paises => rellenarSelect('accountCountry', paises, cuenta.pais, 'Select one country')),
                cargarProvincias(cuenta.pais, cuenta.provincia),
                cargarCiudades(cuenta.pais, cuenta.provincia, cuenta.ciudad)
            ]).catch(() => mostrarMensaje('The list of countries could not be loaded.', 'warning'));

            $('#accountSave').prop('disabled', false);
        }).fail(function () {
            mostrarMensaje('Your account could not be loaded.', 'danger');
        });
    });

    $('#accountCountry').change(function () {
        cargarProvincias(this.value, null);
        rellenarSelect('accountCity', [], null, 'Select one province first');
    });

    $('#accountProvince').change(function () {
        cargarCiudades(document.getElementById('accountCountry').value, this.value, null);
    });

    // Guardar los cambios
    $('#accountForm').submit(function (e) {
        e.preventDefault();

        const datos = {
            nombre: $('#accountName').val().trim(),
            telf: $('#accountPhoneCode').val().trim() + ' ' + $('#accountPhoneNumber').val().trim(),
            pais: $('#accountCountry').val() || '',
            provincia: $('#accountProvince').val() || '',
            ciudad: $('#accountCity').val() || '',
            direccion: $('#accountAddress').val().trim()
        };

        $('#accountSave').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: 'api/account.php',
            data: datos,
            dataType: 'JSON',
            success: function (response) {
                mostrarMensaje('Your details have been saved.', 'success');

                // Actualizamos el nombre de la cabecera y del menú
                const nombre = response.account.nombre;
                $('#userName').text(nombre.split(' ')[0]);
                $('#fullUserName').text(nombre);
            },
            error: function (xhr) {
                const errores = xhr.responseJSON && xhr.responseJSON.errors;
                const nombres = { nombre: 'name', telf: 'phone number', pais: 'country', provincia: 'province', ciudad: 'city', direccion: 'address' };

                if (errores) {
                    const lista = Object.keys(errores).map(campo => `${nombres[campo] || campo}: ${errores[campo].toLowerCase()}`);
                    mostrarMensaje('Please check: ' + lista.join(', ') + '.', 'danger');
                } else {
                    mostrarMensaje('Your details could not be saved. Try again.', 'danger');
                }
            },
            complete: function () {
                $('#accountSave').prop('disabled', false);
            }
        });
    });
});
