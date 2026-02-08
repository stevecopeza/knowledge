<?php
$url = 'https://www.wanderlustchloe.com/wp-content/uploads/2017/09/Sicily-27.jpg';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

echo "Fetching $url via PHP curl...\n";
$response = curl_exec($ch);

if ($response === false) {
    echo "Error: " . curl_error($ch) . "\n";
} else {
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP Code: $code\n";
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    echo "Body Length: " . strlen($body) . "\n";
    echo "Headers:\n$header\n";
}
curl_close($ch);
