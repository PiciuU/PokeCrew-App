<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="Welcome to the introductory page of the DreamFork framework - a lightweight and fast tool for web application development">

        <link rel="icon" type="image/x-icon" href="favicon.ico">

        <title>Dreamfork</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;700&family=Source+Sans+Pro:wght@200;400;700&display=swap" />

        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;700&family=Source+Sans+Pro:wght@200;400;700&display=swap"  media="print" onload="this.media='all'" />

        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

        <style>
            @resource(css/welcome.css);
        </style>
    </head>
    <body>
        <div id="app">
            <header>
                <div class="header__content">
                    <div class="header__logo">
                        @resource(icons/logo.svg);
                        <span>Dreamfork</span>
                    </div>
                    <div class="header__version-info">Dreamfork v{{ app()->version() }} (PHP v{{ PHP_VERSION }})</div>
                </div>
            </header>

            <main>
                <div class="main__content">
                    <h1> The PHP Framework </h1>
                    <h2> Dreamfork is a nimble and swift web application framework inspired by <a href="https://laravel.com" target="_blank">Laravel</a>, offering a lightweight and expressive syntax for seamless development.</h2>
                    <a class="main__btn" href="https://dreamfork.dream-speak.pl/" target="_blank">Start today</a>
                </div>
            </main>

            <footer>
                <div class="footer__content">
                <a href="https://dreamfork.dream-speak.pl/docs/1.x/" target="_blank">
                        <div class="footer__feature">
                            <div class="feature__icon">
                                <span class="material-symbols-outlined">
                                    description
                                </span>
                            </div>
                            <div class="feature__content">
                                <div class="feature__title">
                                    DreamFork Documentation
                                </div>
                                <div class="feature__description">
                                    Comprehensive framework guide
                                </div>
                            </div>
                        </div>
                    </a>
                    <a href="https://dev.dream-speak.pl/dreamfork/docs/1.x/examples" target="_blank">
                        <div class="footer__feature">
                            <div class="feature__icon">
                                <span class="material-symbols-outlined">
                                    public
                                </span>
                            </div>
                            <div class="feature__content">
                                <div class="feature__title">
                                    Examples of Usage
                                </div>
                                <div class="feature__description">
                                    Inspiring implementation demos
                                </div>
                            </div>
                        </div>
                    </a>
                    <a href="https://github.com/PiciuU/DreamFork-PHP-Framework" target="_blank">
                        <div class="footer__feature">
                            <div class="feature__icon">
                                <span class="material-symbols-outlined">
                                    code
                                </span>
                            </div>
                            <div class="feature__content">
                                <div class="feature__title">
                                    Project Repository
                                </div>
                                <div class="feature__description">
                                    Collaborate on GitHub
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </footer>
        </div>
    </body>
</html>
