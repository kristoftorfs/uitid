<?php

require_once(__DIR__ . '/OAuth/OAuth.php');
$url = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
session_start();

// Default endpoint
if (!array_key_exists('endpoint', $_SESSION) || ($_SESSION['endpoint'] != 'http://www.uitid.be/uitid/rest/')) {
    $_SESSION['endpoint'] = 'http://acc.uitid.be/uitid/rest/';
}

if (!empty($_POST)) {
    // 1. Retrieve request token
    $_SESSION['consumer_key'] = $_POST['consumer_key'];
    $_SESSION['consumer_secret'] = $_POST['consumer_secret'];
    $_SESSION['endpoint'] = $_POST['endpoint'];
    $sigmethod = new OAuthSignatureMethod_HMAC_SHA1();
    $consumer = new OAuthConsumer($_POST['consumer_key'], $_POST['consumer_secret'], $url);
    $params = array('oauth_callback' => $url);
    $request = OAuthRequest::from_consumer_and_token($consumer, null, 'POST', $_POST['endpoint'] . 'requestToken', $params);
    $request->sign_request($sigmethod, $consumer, NULL);
    $auth = $request->to_header();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_POST['endpoint'] . 'requestToken');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth, 'Content-Type: ' . 'application/x-www-form-urlencoded'));
    $output = curl_exec($ch);
    parse_str($output, $values);
    $_SESSION['oauth_token'] = $values['oauth_token'];
    $_SESSION['oauth_token_secret'] = $values['oauth_token_secret'];
    curl_close($ch);
    // 2. Redirect for authorization
    header('Location: http://acc.uitid.be/uitid/rest/auth/authorize?oauth_token=' . $values['oauth_token'] . '&oauth_token_secret=' . $values['oauth_token_secret']);
    exit;
} elseif (!empty($_GET)) {
    // 3. Exchange request token for access token
    $token = new OAuthToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $verifier = $_GET['oauth_verifier'];
    $sigmethod = new OAuthSignatureMethod_HMAC_SHA1();
    $consumer = new OAuthConsumer($_SESSION['consumer_key'], $_SESSION['consumer_secret'], $url);
    $params = array('oauth_verifier' => $verifier);
    $request = OAuthRequest::from_consumer_and_token($consumer, $token, 'POST', $_SESSION['endpoint'] . 'accessToken', $params);
    $request->sign_request($sigmethod, $consumer, $token);
    $auth = str_replace(",", ', ', $request->to_header());
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_SESSION['endpoint'] . 'accessToken');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth, 'Content-Type:', 'Expect:', 'Accept:'));
    $output = curl_exec($ch);
    parse_str($output, $values);
    if (array_key_exists('userId', $values) && array_key_exists('oauth_token', $values) && array_key_exists('oauth_token_secret', $values)) {
        $result = $values;
    } else {
        $error = true;
    }
}

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

<div class="container">
    <form method="post" action="index.php" class="pure-form pure-form-aligned">
        <fieldset>
            <legend>Endpoint</legend>
            <label for="endpoint1" class="pure-radio">
                <input type="radio" name="endpoint" id="endpoint1" value="http://acc.uitid.be/uitid/rest/"<?= ($_SESSION['endpoint'] == 'http://acc.uitid.be/uitid/rest/' ? ' checked="checked"' : '') ?>>
                Ik wil in de testomgeving een token aanvragen
            </label>
            <label for="endpoint2" class="pure-radio">
                <input type="radio" name="endpoint" id="endpoint2" value="http://www.uitid.be/uitid/rest/"<?= ($_SESSION['endpoint'] == 'http://www.uitid.be/uitid/rest/' ? ' checked="checked"' : '') ?>>
                Ik wil in productie een token aanvragen
            </label>
        </fieldset>
        <fieldset>
            <legend>Consumer gegevens</legend>
            <div class="pure-control-group">
                <label for="consumer_key">Consumer key:</label>
                <input class="pure-input-1-4" type="text" name="consumer_key" id="consumer_key" value="<?= $_SESSION['consumer_key'] ?>">
            </div>
            <div class="pure-control-group">
                <label for="consumer_secret">Consumer secret:</label>
                <input class="pure-input-1-4" type="text" name="consumer_secret" id="consumer_secret" value="<?= $_SESSION['consumer_secret'] ?>">
            </div>
        </fieldset>
<?php if (isset($result)) { ?>
        <fieldset>
            <legend>Resultaten</legend>
            <div class="pure-control-group">
                <label for="userid">User ID:</label>
                <input class="pure-input-1-4" readonly="readonly" type="text" name="userid" id="userid" value="<?= $result['userId'] ?>">
            </div>
            <div class="pure-control-group">
                <label for="token">Access token:</label>
                <input class="pure-input-1-4" readonly="readonly" type="text" name="token" id="token" value="<?= $result['oauth_token'] ?>">
            </div>
            <div class="pure-control-group">
                <label for="secret">Access token secret:</label>
                <input class="pure-input-1-4" readonly="readonly" type="text" name="secret" id="secret" value="<?= $result['oauth_token_secret'] ?>">
            </div>
        </fieldset>
        <button class="pure-button button-success" type="button">Access token succesvol ontvangen.</button>
<?php } elseif (isset($error)) { ?>
        <button class="pure-button button-error" type="button">Er is een fout opgetreden bij het aanvragen van de access token.</button>
        <button type="submit" class="pure-button pure-button-primary">Authenticatie opnieuw starten</button>
<?php } else { ?>
        <button type="submit" class="pure-button pure-button-primary">Authenticatie starten</button>
<?php } ?>
    </form>
</div>

</body>
</html>
