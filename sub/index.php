<?php

if (empty($_SERVER['HTTP_USER_AGENT'])) {
    http_response_code(403);
    exit();
}

// PUT YOUR BASE URL HERE
const UPSTREAM = '';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$url = UPSTREAM . $requestUri;

/**
 * Build forwarded headers from the client request
 */
$forwardHeaders = [];

foreach (getallheaders() as $name => $value) {
    $lower = strtolower($name);

    // skip headers that break proxying
    if (in_array($lower, [
        'host',
        'content-length',
        'connection',
        'accept-encoding' // let cURL handle compression
    ])) {
        continue;
    }

    $forwardHeaders[] = "$name: $value";
}

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER         => false,

    // forward full client user agent
    CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],

    // send all client headers upstream
    CURLOPT_HTTPHEADER     => $forwardHeaders,

    // auto-handle gzip / br / deflate
    CURLOPT_ENCODING       => '',

    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,

    // proxy
    // if you get a connection error, try using a simple http/socks5 proxy
    // curl proxy types: https://curl.se/libcurl/c/CURLOPT_PROXYTYPE.html
    // CURLOPT_PROXYTYPE      => CURLPROXY_HTTP,
    // CURLOPT_PROXY          => '0.0.0.0:443',
    // CURLOPT_PROXYUSERPWD   => 'username:password',
]);

// forward request body (POST/PUT/etc)
$method = $_SERVER['REQUEST_METHOD'];

if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(502);
    exit(curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);

http_response_code($httpCode);

// forward content-type so HTML renders properly
if ($contentType) {
    header("Content-Type: $contentType");
}

echo $response;