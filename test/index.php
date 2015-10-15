<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="//yui.yahooapis.com/pure/0.5.0/pure-min.css" rel="stylesheet">
    <title>UiTdatabank authorizatie test</title>
    <style type="text/css">
        body {
            margin: 20px;
        }
    </style>
</head>
<body>
<?php

$callback = str_replace('index.php', '/result.php?sid=12345', $_SERVER['PHP_SELF']);
$callback = str_replace('//', '/', $callback);

$target = str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF'])));
$target .= '/index.php';
$target = str_replace('//', '/', $target);

?>
<form method="post" action="<?= $target ?>">
    <input type="hidden" name="test" value="1">
    <input type="hidden" name="consumer_key" value="">
    <input type="hidden" name="consumer_secret" value="">
    <input type="hidden" name="callback" value="<?= $callback ?>">
    <button type="submit" class="pure-button pure-button-primary">Authenticatie starten</button>
</form>
</body>
</html>