<?php

namespace Laravel\Msg91;

use GuzzleHttp\Client as Guzzle;

/**
 * Class Client
 * @package Laravel\Msg91
 */
class Client
{
    const ENDPOINT_OTP = 'http://control.msg91.com/api/sendotp.php';
    const ENDPOINT_OTP_VERIFY = 'http://api.msg91.com/api/verifyRequestOTP.php';
    const ENDPOINT_SMS = 'http://api.msg91.com/api/v2/sendsms';

    /**
     * @var Guzzle
     */
    protected $http;

    /**
     * @var string
     */
    protected $key;

    /**
     * Client constructor.
     * @param string $key
     */
    public function __construct($key)
    {
        $this->http = new Guzzle(['http_errors' => false]);
        $this->key = $key;
    }

    /**
     * @param string $number
     * @param string|null $sender
     * @param string|null $message
     * @return bool
     */
    public function otp($number, $sender = null, $message = null)
    {
        $response = $this->http->post(self::ENDPOINT_OTP, [
            'form_params' => [
                'authkey' => $this->key,
                'message' => $message,
                'mobile' => $number,
                'sender' => $sender ?? config('msg91.default_sender'),
            ]
        ]);
        if ($response->getStatusCode() === 200) {
            $body = json_decode((string) $response->getBody(), true);
            return isset($body['type']) && ($body['type'] === 'success');
        }
        return false;
    }

    /**
     * @param string|array $number
     * @param string $message
     * @param string $sender
     * @param int|null $route
     * @param string|null $country
     * @return string|false
     */
    public function sms($number, $message, $sender = null, $route = null, $country = null)
    {
        $response = $this->http->post(self::ENDPOINT_SMS, [
            'form_params' => [
                'authkey' => $this->key,
                'country' => $country ?? config('msg91.default_country'),
                'message' => $message,
                'mobiles' => implode(',', (array) $number),
                'route' => $route ?? config('msg91.default_route'),
                'sender' => $sender ?? config('msg91.default_sender'),
            ]
        ]);
        if ($response->getStatusCode() === 200) {
            $body = trim((string) $response->getBody());
            if (strlen($body) === 24) {
                return $body;
            }
        }
        return false;
    }

    /**
     * @param string $number
     * @param string $otp
     * @return bool
     */
    public function verify($number, $otp)
    {
        $response = $this->http->post(self::ENDPOINT_OTP_VERIFY, [
            'form_params' => [
                'authkey' => $this->key,
                'mobile' => $number,
                'otp' => $otp,
            ]
        ]);
        if ($response->getStatusCode() === 200) {
            $body = json_decode((string) $response->getBody(), true);
            return isset($body['type']) && ($body['type'] === 'success');
        }
        return false;
    }
}
