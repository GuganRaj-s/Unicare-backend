(function () {
    'use strict';

    const intStatus = document.getElementById('internetStatus');
    const backText = 'Your internet connection is back';
    const lostText = 'Oops! Check your internet connection.';
    const successColor = '#008000';
    const failureColor = '#ff0000';

    if (window.navigator.onLine) {
        $('#internetStatus').html(backText);
        $('#internetStatus').css("background-color", successColor);
        $('#internetStatus').css("display", "none");
        $('.appHeader, .appBottomMenu, .appFooter').removeClass('NoInternet');
    } else {
        $('#internetStatus').html(lostText);
        $('#internetStatus').css("background-color", failureColor);
        $('#internetStatus').css("display", "block");
        $('.appHeader, .appBottomMenu, .appFooter').addClass('NoInternet');
    }

    window.addEventListener('online', function () {
        $('#internetStatus').html(backText);
        $('#internetStatus').css("background-color", successColor);
        $('.appHeader, .appBottomMenu, .appFooter').removeClass('NoInternet');
        var hideTime = setTimeout( function() {
            $('#internetStatus').css("display", "none");
        }, 5000);
    });

    window.addEventListener('offline', function () {
        $('#internetStatus').html(lostText);
        $('#internetStatus').css("background-color", failureColor);
        $('#internetStatus').css("display", "block");
        $('.appHeader, .appBottomMenu, .appFooter').addClass('NoInternet');
    });

})();