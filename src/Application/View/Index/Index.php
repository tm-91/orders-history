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
                <?php 
                echo 'HISTORIA EDYCJI</br></br>';
                echo 'ilość wpisów: ' . count($historyEntries);
                ?>
                <div class="history-container">
                    <?php
                    foreach ($historyEntries as $entry){
                        $view = new \Application\View\View('Index/History/historyEntry', ['entry' => $entry]);
                        $view->render();
//                        require 'History/historyEntry.php';
//                        echo "</br></br>";
                    }
                    ?>
                </div>
            </section>
        </main>
        <script src="//cdn.dcsaas.net/js/appstore-sdk.js"></script>
        <style>
.history-container {
    /*max-width: 80%;*/
    /*height: auto;*/
}

.h-entry-container {
    margin-top: 10px;
    margin-bottom: 10px;
    /* max-width: 80%; */
    /* height: auto; */
    padding: 5px;
}

.entry-data.date {
    clear: left;
}

.entry-data {
    padding: 5px;
    margin-bottom: 10px;
    float: left;
    /*max-width: 20%;*/
}

        </style>
    </body>
</html>
