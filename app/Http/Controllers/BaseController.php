<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function base64UrlEncode(string $data): string
    {
        $base64Url = strtr(base64_encode($data), '+/', '-_');
        
        return rtrim($base64Url, '=');
    }
    
    protected function base64UrlDecode(string $base64Url): string
    {
        return base64_decode(strtr($base64Url, ['-' => '+', '_' => '/']));
    }
    
    protected function secret($plaintext, $type)
    {
        if (!empty($plaintext)) {
            $iv_text = '1973022119801989';
            $key_text = "21januari1973";
            $ciphering = "AES-256-CFB";
            //hash $secret_key dengan algoritma sha256
            $key = hash("sha512", $key_text);
            
            //iv(initialize vector), encrypt iv dengan encrypt method AES-256-CBC (16 bytes)
            $iv = substr(hash("sha512", $iv_text), 0, 16);
            
            if ($type == 'encryption') {
                $encryption = $this->base64UrlEncode(openssl_encrypt($plaintext, $ciphering, $key, 0, $iv));
                return $encryption;
            } elseif ($type == 'decryption') {
                $decryption = openssl_decrypt($this->base64UrlDecode($plaintext), $ciphering, $key, 0, $iv);
                return $decryption;
            }
        }
    }
}
