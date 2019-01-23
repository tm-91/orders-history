(function () {
    'use strict';

    var styles;

    if (localStorage.getItem('styles')) {
        styles = JSON.parse(localStorage.getItem('styles'));
        injectStyles(styles);
    }

    window.shopAppInstance = new ShopApp(function (app) {
        app.init(null, function (params, app) {
            if (localStorage.getItem('styles') === null) {
                injectStyles(params.styles);
            }
            localStorage.setItem('styles', JSON.stringify(params.styles));

            app.show(null, function () {
                app.adjustIframeSize();
            });
        }, function (errmsg, app) {
            alert(errmsg);
        });
    }, true);

    function injectStyles (styles) {
        var i;
        var el;
        var sLength;

        sLength = styles.length;
        for (i = 0; i < sLength; ++i) {
            el = document.createElement('link');
            el.rel = 'stylesheet';
            el.type = 'text/css';
            el.href = styles[i];
            document.getElementsByTagName('head')[0].appendChild(el);
        }
    }
}());

(function(){
    var elements = document.querySelectorAll('.entry_container');
    for (var i = 0; i < elements.length; i++) {
        elements[i].classList.add('hide_entry');
    }
}());

document.querySelector('.history_container').addEventListener("mousedown",function(event){
        var elem = event.target;
        if (elem.classList.contains('entry_date')){
            elem.parentElement.parentElement.classList.toggle('hide_entry');
        }
    }
);
