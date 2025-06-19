<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class UMSApi extends Model
{
    public static $baseurl_api = 'http://ums-stikes.dbi-project.my.id/api';

    public static function Login($email, $pass)
    {
        $client = new Client();
        $response = $client->post(UMSApi::$baseurl_api . '/auth/login_mbkm', [
            'form_params' => [
                'email' => $email,
                'password' => $pass
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public static function Register($data)
    {
        $client = new Client();
        $response = $client->post(UMSApi::$baseurl_api . '/auth/register', [
            'form_params' => $data,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public static function updatePhotoProfile($base64Image)
    {
        $client = new Client();
        $response = $client->post(UMSApi::$baseurl_api . '/mahasiswa/update_foto', [
            'form_params' => [
                'IMAGE' => $base64Image
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => session('jwt')[0]['data']
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public static function CheckActiveSession()
    {
        $client = new Client();
        $response = $client->get(UMSApi::$baseurl_api . '/auth/check-session', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => session('jwt')[0]['data']
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public static function MasterDataDosen()
    {
        try {
            $jwtSession = session('jwt');
            if (!$jwtSession || !isset($jwtSession[0]['data'])) {
                throw new \Exception('JWT token not found or expired. Please login again.');
            }
    
            $token = $jwtSession[0]['data'];
    
            $client = new Client();
            $response = $client->get(UMSApi::$baseurl_api . '/dosen', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => $token
                ]
            ]);
    
            $body = json_decode($response->getBody(), true);
            \Log::info('API response:', $body);
    
            if (!isset($body['data'])) {
                throw new \Exception($body['status_message'] ?? 'API response missing "data" key');
            }
    
            return $body['data'];
        } catch (\Exception $e) {
            \Log::error('UMS API error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function MasterDataMahasiswa()
    {
        $client = new Client();
        $response = $client->get(UMSApi::$baseurl_api . '/mahasiswa/list', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => session('jwt')[0]['data']
            ]
        ]);
        return json_decode($response->getBody(), true)['data'];
    }

    public static function MasterDataProdi()
    {
        $client = new Client();
        $response = $client->get(UMSApi::$baseurl_api . '/prodi', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => session('jwt')[0]['data']
            ]
        ]);
        return json_decode($response->getBody(), true)['data'];
    }
}
