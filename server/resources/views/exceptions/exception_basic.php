<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $message; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --text: #ffffff;
            --background: #121212;

            font-size: 62.5%;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            position: relative;
            font-weight: normal;
        }

        html,
        body {
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
            font-family: 'Poppins', Avenir, Helvetica, Arial, sans-serif;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-size: 1.6rem;
            text-align: center;
        }

        main {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 25px;
            padding: 10px 20px;
        }

        h1 {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <main>
        <h1><?php echo $code; ?> | <?php echo $message ?></h1>
    </main>
</body>

</html>