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
<form method="post" action="http://korpus.axoclub.be/uitid/index.php">
    <input type="hidden" name="test" value="1">
    <input type="text" name="consumer_key" value="" placeholder="consumer_key">
    <input type="text" name="consumer_secret" value="" placeholder="consumer_secret">
    <input type="hidden" name="callback" value="http://localhost/test/result.php">
    <button type="submit" class="pure-button pure-button-primary">Authenticatie starten</button>
</form>
</body>
</html>