<?php
/**
 * API 2 — Currency Exchange (AbstractAPI Exchange Rates)
 *
 * Retrieves the latest available USD -> MYR reference exchange rate.
 * AbstractAPI states free-plan data is normally updated every
 * 45-60 minutes, so this is the "latest available" rate, not real-time.
 *
 * Docs: https://docs.abstractapi.com/api/exchangerates
 * Endpoint: GET https://exchange-rates.abstractapi.com/v1/live?api_key=...&base=USD&target=MYR
 *
 * Example response:
 * {
 *   "base": "USD",
 *   "last_updated": 1725900000,
 *   "exchange_rates": { "MYR": 4.69 }
 * }
 *
 * @return array{
 *   success: bool,
 *   provider: string,
 *   rate: ?float,
 *   base: string,
 *   target: string,
 *   source_field: string,
 *   updated: ?string,
 *   error: ?string
 * }
 */
function fetch_usd_myr_rate(string $apiKey): array
{
    $result = [
        'success'      => false,
        'provider'     => 'AbstractAPI',
        'rate'         => null,
        'base'         => 'USD',
        'target'       => 'MYR',
        'source_field' => 'exchange_rates.MYR',
        'updated'      => null,
        'error'        => null,
    ];

    if ($apiKey === '' || $apiKey === 'd5c5ab0b1902453fac8f6148414fde55') {
        $result['error'] = 'AbstractAPI key is not configured.';
        return $result;
    }

    $url = 'https://exchange-rates.abstractapi.com/v1/live'
         . '?api_key=' . urlencode($apiKey)
         . '&base=USD&target=MYR';

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
        $result['error'] = 'Request to AbstractAPI failed: ' . $curlError;
        return $result;
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200 || !is_array($data) || !isset($data['exchange_rates']['MYR'])) {
        $apiMsg = $data['error']['message'] ?? ('HTTP ' . $httpCode);
        $result['error'] = 'AbstractAPI returned an error or unexpected response: ' . $apiMsg;
        return $result;
    }

    $result['rate']    = (float) $data['exchange_rates']['MYR'];
    $result['updated'] = isset($data['last_updated'])
        ? gmdate('Y-m-d H:i:s', (int) $data['last_updated']) . ' UTC'
        : 'unknown';

    $result['success'] = true;
    return $result;
}
