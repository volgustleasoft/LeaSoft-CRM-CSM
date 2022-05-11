<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Care portal</title>

        <!-- meta -->
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">


        <!-- FONTS -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,600" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="/css/style.css" />

        <!-- FAVICON -->
        <link rel="icon" href="/img/favicon.png" type="image/x-icon">

    </head>


    <body>

        <header class="header login-header">
            <div class="wrap">
                <h1 class="logo">Care portal</h1>
            </div>
        </header>

        <main class="main log-in-form">
            {{ $slot }}
        </main>

        <!-- JAVASCRIPTS -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="/js/scripts.js"></script>

    </body>

</html>
