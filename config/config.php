
<?php

// Fungsi mengambil data dari Public API menggunakan cURL
function http_request_get($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'WORLDESCAPE Travel Website',
        CURLOPT_FOLLOWLOCATION => true
    ]);

    // Menjalankan request API
    $output = curl_exec($ch);

    // Memeriksa error cURL
    if ($output === false) {
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'success' => false,
            'message' => 'cURL Error: ' . $error
        ];
    }

    // Mengambil HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    // Mengubah respons JSON menjadi array PHP
    $result = json_decode($output, true);

    // Memeriksa respons dari NewsAPI
    if ($http_code < 200 || $http_code >= 300) {
        $api_message = $result['message'] ?? 'Tidak ada keterangan dari API';
        $api_code = $result['code'] ?? 'Unknown';

        return [
            'success' => false,
            'http_code' => $http_code,
            'message' => $api_message,
            'api_code' => $api_code
        ];
    }

    if (!is_array($result)) {
        return [
            'success' => false,
            'http_code' => $http_code,
            'message' => 'Respons API bukan JSON yang valid.'
        ];
    }

    if (($result['status'] ?? '') !== 'ok') {
        return [
            'success' => false,
            'http_code' => $http_code,
            'message' => $result['message'] ?? 'NewsAPI gagal memproses permintaan.'
        ];
    }

    return [
        'success' => true,
        'http_code' => $http_code,
        'data' => $output
    ];
}

?>
