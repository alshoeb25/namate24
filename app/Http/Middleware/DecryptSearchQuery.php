<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DecryptSearchQuery
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $encryptedQuery = $request->header('X-Search-Query');
        
        if ($encryptedQuery) {
            try {
                $decrypted = $this->decryptAES($encryptedQuery);
                if ($decrypted) {
                    $queryData = json_decode($decrypted, true);
                    if (is_array($queryData)) {
                        // Merge decrypted data into request
                        $request->merge($queryData);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to decrypt search query: ' . $e->getMessage());
            }
        }
        
        return $next($request);
    }
    
    /**
     * Decrypt AES encrypted string (compatible with CryptoJS)
     *
     * @param string $encryptedData
     * @return string|null
     */
    private function decryptAES($encryptedData)
    {
        try {
            $secretKey = 'namate24-secret-key-2024'; // Same as frontend
            $encryptedData = urldecode($encryptedData);
            
            // Decode base64
            $encrypted = base64_decode($encryptedData);
            
            if ($encrypted === false) {
                return null;
            }
            
            // Extract salt (first 8 bytes after "Salted__")
            $salt = substr($encrypted, 8, 8);
            $ct = substr($encrypted, 16);
            
            // Derive key and IV using EVP_BytesToKey
            $rounds = 1;
            $data00 = $secretKey . $salt;
            $md5_hash = [];
            $md5_hash[0] = md5($data00, true);
            $result = $md5_hash[0];
            
            for ($i = 1; $i < $rounds; $i++) {
                $md5_hash[$i] = md5($md5_hash[$i - 1] . $data00, true);
                $result .= $md5_hash[$i];
            }
            
            $key = substr($result, 0, 32);
            $iv = substr($result, 32, 16);
            
            // Decrypt
            $decrypted = openssl_decrypt($ct, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            
            return $decrypted;
        } catch (\Exception $e) {
            \Log::error('AES Decryption error: ' . $e->getMessage());
            return null;
        }
    }
}
