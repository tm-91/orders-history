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
    </head>
    <body>
        <main class="rwd-layout-width rwd-layout-container">
            <section class="rwd-layout-col-12">
                <div class="edition-form">

                <h2>Zmień productna na:</h2>
                <form action="<?php echo Application\APP::getUrl('index/change') ?>" method="post">
                    <select name="wybrany_producent">
                        <?php
                           foreach ($producers as $prod) {
                                echo '<option value="' . $prod['producer_id'] . '">' . $prod['name'] . '</option>';
                            }
                        ?>
                    </select>
                    <button type="submit" name="save">Zmień</button>
                </form>
 <div class="clearfix"></div>
                </div>
            </section>
        </main>
        <script src="//cdn.dcsaas.net/js/appstore-sdk.js"></script>
    </body>
</html>
