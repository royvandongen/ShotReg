<?php

namespace App\Filters;

use App\Models\AppSettingModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/auth/login')
                             ->with('error', 'Please log in first.');
        }

        // Get path from URI - normalize it
        $currentPath = trim($request->getUri()->getPath(), '/');
        $currentPath = strtolower($currentPath); // Normalize to lowercase
        
        // Paths that are exempt from 2FA enforcement
        $exemptPaths = ['auth/setup2fa', 'auth/logout'];
        $normalizedExemptPaths = array_map('strtolower', $exemptPaths);
        
        // CRITICAL: Always allow exempt paths through - check FIRST before anything else
        if (in_array($currentPath, $normalizedExemptPaths, true)) {
            // Clear any redirect flag when we're on setup2fa
            session()->remove('redirecting_to_setup2fa');
            return null;
        }

        // Only check 2FA requirement if user hasn't enabled it
        if (! session()->get('totp_enabled')) {
            $settingModel = new AppSettingModel();
            if ($settingModel->getValue('force_2fa', '0') === '1') {
                // Prevent redirect loop - don't redirect if we're already redirecting
                if (! session()->get('redirecting_to_setup2fa')) {
                    session()->regenerate(true);
                    session()->set('redirecting_to_setup2fa', true);
                    return redirect()->to('/auth/setup2fa')
                                     ->with('error', 'Two-factor authentication is required. Please set it up to continue.');
                }
            }
        }
        
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}