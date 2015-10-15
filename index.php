<?php

ini_set('display_errors', 'off');
require_once(__DIR__ . '/OAuth/OAuth.php');
$url = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
session_start();

function unparse_url($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
}

// Default endpoint
if (!array_key_exists('endpoint', $_SESSION) || ($_SESSION['endpoint'] != 'http://www.uitid.be/uitid/rest/')) {
    $_SESSION['endpoint'] = 'http://acc.uitid.be/uitid/rest/';
}

if (!empty($_POST)) {
    // 1. Retrieve request token
    if ((int)$_POST['test'] == 1) $_POST['endpoint'] = 'http://acc.uitid.be/uitid/rest/';
    else $_POST['endpoint'] = 'http://www.uitid.be/uitid/rest/';
    $_SESSION['consumer_key'] = $_POST['consumer_key'];
    $_SESSION['consumer_secret'] = $_POST['consumer_secret'];
    $_SESSION['endpoint'] = $_POST['endpoint'];
    $_SESSION['callback'] = $_POST['callback'];
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
        $callback = parse_url($_SESSION['callback']);
        parse_str($callback['query'], $query);
        $callback['query'] = http_build_query(array_merge($query, $values));
        $callback = unparse_url($callback);
    } else {
        $values = ['error' => 1];
        $callback = parse_url($_SESSION['callback']);
        parse_str($callback['query'], $query);
        $callback['query'] = http_build_query(array_merge($query, $values));
        $callback = unparse_url($callback);
    }
    $_SESSION = [];
    header('Location: ' . $callback);
}