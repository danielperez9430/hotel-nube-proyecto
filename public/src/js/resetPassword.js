$(document).ready(function () {

    const formulario = document.getElementById('resetForm');

    if (!formulario) {
        return;
    }

    function mostrarMensaje(texto, tipo) {
        $('#resetMessage').attr('class', 'alert alert-' + tipo).text(texto).show();
    }

    $(formulario).on('submit', function (e) {
        e.preventDefault();

        const password = document.getElementById('resetPassword').value;
        const repetida = document.getElementById('resetPasswordRepeat').value;

        if (password.length < 8) {
            mostrarMensaje('The password must be at least 8 characters long.', 'danger');
            return;
        }

        if (password !== repetida) {
            mostrarMensaje('The passwords do not match.', 'danger');
            return;
        }

        $('#resetSubmit').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: 'api/resetPassword.php',
            data: {
                token: document.getElementById('resetToken').value,
                password
            },
            dataType: 'JSON',
            success: function (response) {
                $(formulario).find('input, button').prop('disabled', true);
                mostrarMensaje(response.message, 'success');
                $(formulario).append('<a class="btn btn-primary mt-2" href="index.php?login=1"><i class="fas fa-sign-in-alt"></i> Log in</a>');
            },
            error: function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The password could not be changed. Try again.';
                mostrarMensaje(mensaje, 'danger');
                $('#resetSubmit').prop('disabled', false);
            }
        });
    });
});
