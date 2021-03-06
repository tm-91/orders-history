<!doctype html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="referrer" content="origin">
    <script src="//cdn.dcsaas.net/js/dc-sdk-1.0.2.min.js"></script>

    <script>
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
    </script>
    <!-- // <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
</head>
<body>
<main class="rwd-layout-width rwd-layout-container">
    <section class="rwd-layout-col-12">
        <?php echo 'Ups... coś poszło nie tak'; ?>
    </section>
</main>
<script src="//cdn.dcsaas.net/js/appstore-sdk.js"></script>
</body>
</html>
