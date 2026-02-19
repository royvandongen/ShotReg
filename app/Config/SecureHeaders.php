<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SecureHeaders extends BaseConfig
{
    /**
     * Security headers sent on every response.
     */
    public array $headers = [
        'X-Content-Type-Options'            => 'nosniff',
        'X-DNS-Prefetch-Control'            => 'off',
        'X-Frame-Options'                   => 'SAMEORIGIN',
        'X-Permitted-Cross-Domain-Policies' => 'none',
        'X-XSS-Protection'                  => '1; mode=block',
        'Referrer-Policy'                   => 'strict-origin-when-cross-origin',
        'Strict-Transport-Security'         => 'max-age=31536000; includeSubDomains',
    ];
}
