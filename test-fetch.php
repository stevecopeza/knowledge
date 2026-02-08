<?php
require_once '/Users/stevecope/Sites/knowledge.cope.zone/wp-load.php';

$url = 'https://www.wanderlustchloe.com/wp-content/uploads/2017/09/Sicily-27.jpg';
$args = [
    'timeout' => 15,
    'sslverify' => false,
    'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
];

echo "Fetching $url ...\n";
$response = wp_remote_get( $url, $args );

if ( is_wp_error( $response ) ) {
    echo "Error: " . $response->get_error_message() . "\n";
} else {
    $code = wp_remote_retrieve_response_code( $response );
    echo "HTTP Code: $code\n";
    echo "Body Length: " . strlen( wp_remote_retrieve_body( $response ) ) . "\n";
    $headers = wp_remote_retrieve_headers( $response );
    print_r( $headers );
}
