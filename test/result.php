<?php

ini_set('display_errors', 'off');

?>
<!DOCTYPE html>
<html>
<head>
    <title>UiTdatabank authorizatie</title>
    <meta charset="UTF-8">
    <link href="//yui.yahooapis.com/pure/0.5.0/pure-min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <style type="text/css">
        body {
            margin: 20px;
        }
        .button-success, .button-error, .button-warning, .button-secondary {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
            cursor: text;
        }
        .button-success { background: rgb(28, 184, 65); }
        .button-error { background: rgb(202, 60, 60); }
        .button-warning { background: rgb(223, 117, 20); }
        .button-secondary { background: rgb(66, 184, 221); }
    </style>
</head>
<body>

<?php if (!array_key_exists('error', $_GET)) { ?>
<div class="container">
    <div class="pure-form pure-form-aligned">
        <fieldset>
            <legend>Resultaten</legend>
            <div class="pure-control-group">
                <label for="userid">User ID:</label>
                <input class="pure-input-1-4" readonly="readonly" type="text" name="userid" id="userid" value="<?= $_GET['userId'] ?>">
            </div>
            <div class="pure-control-group">
                <label for="token">Access token:</label>
                <input class="pure-input-1-4" readonly="readonly" type="text" name="token" id="token" value="<?= $_GET['oauth_token'] ?>">
            </div>
            <div class="pure-control-group">
                <label for="secret">Access token secret:</label>
                <input class="pure-input-1-4" readonly="readonly" type="text" name="secret" id="secret" value="<?= $_GET['oauth_token_secret'] ?>">
            </div>
        </fieldset>
        <a class="pure-button button-success" type="button" href="index.php">Access token succesvol ontvangen.</a>
    <?php } else { ?>
        <button class="pure-button button-error" type="button">Er is een fout opgetreden bij het aanvragen van de access token.</button>
        <a href="index.php" class="pure-button pure-button-seconday">Authenticatie opnieuw starten</a>
<?php } ?>
    </div>
</div>

</body>
</html>
