<?php
/**

 * @return array{
 *   success: bool,
 *   provider: string,
 *   price_usd_per_oz: ?float,
 *   source_field: ?string,
 *   unit: string,
 *   updated: ?string,
 *   error: ?string
 * }
 */
function fetch_gold_price_usd(string $apiKey): array
{
    $result = [
        'success'          => false,
        'provider'         => 'MetalpriceAPI',
        'price_usd_per_oz' => null,
        'source_field'     => null,
        'unit'             => 'USD per troy ounce',
        'updated'          => null,
        'error'            => null,
    ];

    if ($apiKey === '' || $apiKey === 'd14337b93605498243eaf5adb84cc05f') {
        $result['error'] = 'MetalpriceAPI key is not configured.';
        return $result;
    }

    $url = 'https://api.metalpriceapi.com/v1/latest'
         . '?api_key=' . urlencode($apiKey)
         . '&base=USD&currencies=XAU';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response  = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        $result['error'] = 'Request to MetalpriceAPI failed: ' . $curlError;
        return $result;
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200 || !is_array($data) || empty($data['success'])) {
        $apiMsg = $data['error']['message'] ?? ('HTTP ' . $httpCode);
        $result['error'] = 'MetalpriceAPI returned an error: ' . $apiMsg;
        return $result;
    }

   
    if (isset($data['rates']['USDXAU']) && is_numeric($data['rates']['USDXAU'])) {
        $result['price_usd_per_oz'] = (float) $data['rates']['USDXAU'];
        $result['source_field']     = 'rates.USDXAU';
    } elseif (isset($data['rates']['XAU']) && is_numeric($data['rates']['XAU']) && $data['rates']['XAU'] > 0) {
        $result['price_usd_per_oz'] = 1 / (float) $data['rates']['XAU'];
        $result['source_field']     = 'rates.XAU (normalised: 1 / rate)';
    } else {
        $result['error'] = 'Expected gold price field not found in MetalpriceAPI response.';
        return $result;
    }

    $result['updated'] = isset($data['timestamp'])
        ? gmdate('Y-m-d H:i:s', (int) $data['timestamp']) . ' UTC'
        : ($data['date'] ?? 'unknown');

    $result['success'] = true;
    return $result;
}
