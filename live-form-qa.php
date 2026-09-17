<?php

$base = 'http://127.0.0.1:8000';
$cookieFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'meetaj-form-qa.txt';
@unlink($cookieFile);

function http_request(string $method, string $url, array $headers = [], ?array $fields = null): array
{
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADER => true,
    ]);
    if ($fields !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    }
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $status,
        'error' => $error,
        'headers' => substr((string) $raw, 0, $headerSize),
        'body' => substr((string) $raw, $headerSize),
    ];
}

$home = http_request('GET', $base.'/');
if ($home['status'] !== 200) {
    fwrite(STDERR, "homepage {$home['status']} {$home['error']}\n");
    exit(1);
}

preg_match('/name="csrf-token" content="([^"]+)"/', $home['body'], $m);
$token = $m[1] ?? '';
echo 'home_status='.$home['status'].' token_len='.strlen($token).PHP_EOL;
if ($token === '') {
    fwrite(STDERR, "no csrf token in homepage\n");
    exit(1);
}

$contact = http_request('POST', $base.'/forms/contact.php', [
    'Accept: text/plain',
    'X-CSRF-TOKEN: '.$token,
    'X-Requested-With: XMLHttpRequest',
], [
    '_token' => $token,
    'csrf_token' => $token,
    'name' => 'Live QA Contact',
    'email' => 'live-qa-contact@example.com',
    'phone' => '09120000999',
    'subject' => 'Need help with network',
    'message' => 'This is a valid live contact message.',
]);
echo 'contact_status='.$contact['status'].' body='.trim($contact['body']).PHP_EOL;

$servicePage = http_request('GET', $base.'/services/network-design');
preg_match('/name="csrf-token" content="([^"]+)"/', $servicePage['body'], $sm);
$serviceToken = $sm[1] ?? $token;
echo 'service_page_status='.$servicePage['status'].' token_len='.strlen($serviceToken).PHP_EOL;

$service = http_request('POST', $base.'/forms/contact.php', [
    'Accept: text/plain',
    'X-CSRF-TOKEN: '.$serviceToken,
    'X-Requested-With: XMLHttpRequest',
], [
    '_token' => $serviceToken,
    'csrf_token' => $serviceToken,
    'name' => 'Live QA Service',
    'email' => 'live-qa-service@example.com',
    'phone' => '0501234567',
    'service' => 'network-design',
    'subject' => 'Network Design Quote Request',
    'message' => 'Please quote a campus network refresh.',
]);
echo 'service_status='.$service['status'].' body='.trim($service['body']).PHP_EOL;

@unlink($cookieFile);
