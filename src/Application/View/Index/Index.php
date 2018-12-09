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
                <div class="history_container">
                    <?php
                    foreach ($historyEntries as $entry){
                        $view = new \Application\View\View(
                                'Index/History/historyEntry',
                            [
                                'entry' => $entry,
                                'translations' => $translations
                            ]
                        );
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
.history_container {
    position: relative;
    display: block;
}

.history_container:before {
    position: absolute;
    content: '';
    width: 8px;
    height: 100%;
    background-color: #2c5f88;
    float: left;
    margin: 0 0 0 -50px;
}

.entry_container {
    padding: 1em 1em 1em 0;
}

.entry_date_wrapper {
    position: relative;
}

.entry_date_wrapper:before {
    position: absolute;
    top: 50%;
    content: '';
    width: 25px;
    height: 25px;
    background-color: #617e94;
    border-radius: 15px;
    border: 5px solid #2c5f88;
    margin: -15px 0 0 -58.5px;
}

.entry_date_wrapper:hover:before {
    background-color: #38f689;
}

.entry_data_wrapper {
    display: inline-block;
}

.entry_column {
    background-color: white;
    padding: 1em;
    margin-right: 1em;
}

.entry_data {
    display: inline-block;
    float: left;
}

        </style>
    </body>
</html>
