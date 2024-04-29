<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?php echo $exception->getMessage(); ?></title>
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
        }

        main {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 25px;
            gap: 50px;
        }

        section {
            border-radius: 15px;
            background: rgba(31,41,55,.5);
            width: 100%;
            height: 100%;
            max-height: 800px;
            padding: 40px 40px 30px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 25px;
        }

        section .label {
            position: absolute;
            padding: 0px 10px;
            left: 20px;
            top: -10px;
            background: rgba(239,68,68);
        }

        section .content {
            word-break: break-word;
            overflow: auto;
        }

        section .content__tag {
            color: rgba(129,140,248,1);
            font-weight: bold;
            margin: 10px 0px 5px 0px;
        }

        section .content__text-spacing {
            margin-bottom: 5px;
        }

        section .content__text span {
            font-weight: bold;
        }

        section .content__tag:first-child {
            margin-top: 0px;
        }

        ::-webkit-scrollbar, ::-webkit-scrollbar-corner {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar, ::-webkit-scrollbar-corner {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: rgba(239,68,68,.9);
        }

        ::-webkit-scrollbar-track {
            background-color: transparent;
        }
    </style>
</head>

<body>
    <main>
        <section>
            <div class="label">
                Exception
            </div>
            <div class="content">
                <div class="content__tag">
                    <?php echo $exception->getFile(); ?>
                </div>
                <div class="content__text">
                    <?php echo $exception->getMessage(); ?>
                </div>
            </div>
        </section>

        <section>
            <div class="label">
                Trace
            </div>
            <div class="content">
                <div class="content__text">
                    <?php foreach($trace as $element): ?>
                    <div>
                        <?php echo $element; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($exception_context)): ?>
        <section>
            <div class="label">
                Context
            </div>
            <div class="content">
                <div class="content__text">
                    <?php foreach($exception_context as $key => $entry): ?>
                        <div class="content__text content__text-spacing" >
                            <span><?php echo ucFirst($key); ?>:</span> <?php echo $entry; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

         <section>
            <div class="label">
                Request
            </div>
            <div class="content">
                <div class="content__tag">
                    Request
                </div>
                <div class="content__text">
                    [<?php echo $request->getMethod(); ?>] <?php echo $request->getUri(); ?>
                </div>
                <div class="content__tag">
                    Browser
                </div>
                <div class="content__text">
                    <?php echo $request->headers->get('User-Agent'); ?>
                </div>
                <div class="content__tag">
                    Headers
                </div>
                <?php foreach($request->headers as $key => $value): ?>
                    <div class="content__text content__text-spacing" >
                        <span><?php echo ucFirst($key); ?>:</span> <?php echo $value[0]; ?>
                    </div>
                <?php endforeach; ?>
                <div class="content__tag">
                    Body
                </div>
                <div class="content__text">
                    <?php echo $request->getContent() ?: '[]'; ?>
                </div>
            </div>
        </section>

        <section>
            <div class="label">
                Environment
            </div>
            <div class="content">
                <div class="content__text">
                    PHP Version: <?php echo $context['php_version']; ?>
                </div>
                <div class="content__text">
                    Dreamfork Version: <?php echo $context['framework_version']; ?>
                </div>
                <div class="content__text">
                    App Debug: <?php echo boolval($context['app_debug']) ? 'true' : 'false'; ?>
                </div>
                <div class="content__text">
                    App Env: <?php echo $context['app_env']; ?>
                </div>
            </div>
        </section>
    </main>
</body>

</html>