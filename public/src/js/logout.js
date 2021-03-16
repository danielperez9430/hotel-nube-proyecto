$(document).ready(function () {
    $('#userButton').click(function (e) { 
        e.preventDefault();
        
        $('#userLoggedMenuBase').css('display', 'block');
        $('#userLoggedMenu').css('display', 'flex');
        $('#userLoggedMenu').addClass('openLogout');
    });

    $('#closeLogout').click(function (e) { 
        e.preventDefault();
        
        $('#userLoggedMenu').removeClass('openLogout');
        const interval = setInterval(() => {
            $('#userLoggedMenu').css('display', 'none');
            clearInterval(interval);
        }, 300);
    });

    $('#userLoggedMenuBase').click(function (e) { 
        e.preventDefault();
        
        $('#userLoggedMenu').removeClass('openLogout');
        const interval = setInterval(() => {
            $('#userLoggedMenu').css('display', 'none');
            $('#userLoggedMenuBase').css('display', 'none');
            clearInterval(interval);
        }, 300);
    });

    $('#adminPanel').click(function (e) { 
        e.preventDefault();
        
        location.href = "adminPanel.php"
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
            },
            error: function () {
                
            }
        });
    });
});