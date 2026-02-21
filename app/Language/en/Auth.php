<?php

return [
    // Login
    'login'             => 'Log In',
    'loginTitle'        => 'Log In',
    'usernameOrEmail'   => 'Username or Email',
    'password'          => 'Password',
    'createAccount'     => 'Create an account',
    'invalidCredentials' => 'Invalid credentials.',
    'tooManyAttempts'   => 'Too many login attempts. Please wait a minute.',

    // Register
    'register'           => 'Register',
    'registerTitle'      => 'Create Account',
    'username'           => 'Username',
    'confirmPassword'    => 'Confirm Password',
    'passwordMinLength'  => 'Minimum 8 characters.',
    'alreadyHaveAccount' => 'Already have an account? Log in',
    'tooManyRegistrations' => 'Too many registration attempts. Please try again later.',

    // Flash messages
    'accountCreated'        => 'Account created. Please log in.',
    'accountCreatedPendingApproval' => 'Account created. An administrator must approve your account before you can log in.',
    'accountPending'        => 'Your account is pending approval by an administrator.',
    'registrationDisabled'  => 'Registration is currently disabled.',
    'pleaseLoginFirst'      => 'Please log in first.',
    'userNotFound'          => 'User not found.',

    // 2FA
    'verify2fa'             => 'Verify 2FA',
    'twoFactorAuth'         => 'Two-Factor Authentication',
    'enter6DigitCode'       => 'Enter the 6-digit code from your authenticator app.',
    'authenticationCode'    => 'Authentication Code',
    'verify'                => 'Verify',
    'backToLogin'           => 'Back to login',
    'invalidOrExpiredCode'  => 'Invalid or expired code.',
    'tooMany2faAttempts'    => 'Too many attempts. Please wait a minute.',

    // 2FA Setup
    'setup2fa'              => 'Setup 2FA',
    'setup2faTitle'         => 'Setup Two-Factor Authentication',
    'setup2faStep1'         => 'Install an authenticator app (e.g. Google Authenticator, Authy)',
    'setup2faStep2'         => 'Scan the QR code below with the app',
    'setup2faStep3'         => 'Enter the 6-digit code to verify',
    'manualEntryKey'        => 'Manual entry key:',
    'verificationCode'      => 'Verification Code',
    'enable2fa'             => 'Enable 2FA',
    '2faEnabled'            => '2FA enabled successfully.',
    'invalidCode'           => 'Invalid code. Please try again.',
    'enterValid6Digit'      => 'Please enter a valid 6-digit code.',
    'sessionExpiredRescan'  => 'Session expired. Please scan the QR code again and enter the new code.',
    '2faRequired'           => 'Two-factor authentication is required. Please set it up to continue.',

    // 2FA Management
    'manage2faTitle'        => 'Two-Factor Authentication',
    '2faAlreadyEnabled'     => 'Two-factor authentication is already active on your account.',
    'disable2fa'            => 'Disable 2FA',
    'reset2fa'              => 'Reset 2FA',
    'disable2faConfirm'     => 'Are you sure you want to disable 2FA? Your account will be less secure.',
    'reset2faConfirm'       => 'Are you sure you want to reset 2FA? You will need to set it up again with a new code.',
    '2faDisabled'           => 'Two-factor authentication has been disabled.',
    '2faReset'              => 'Two-factor authentication has been reset. Please set it up again.',
    '2faActive'             => '2FA is active',
    '2faNotActive'          => '2FA is not enabled',
    '2faNotEnabledProfile'  => 'Add an extra layer of security to your account by enabling two-factor authentication.',
    'manage2fa'             => 'Manage 2FA',

    // Account disabled
    'accountDisabled'       => 'Your account has been disabled. Please contact an administrator.',

    // Remember me / session management
    'rememberMe'            => 'Keep me signed in for 30 days',
    'sessionInvalidated'    => 'Your session has been signed out. Please log in again.',
    'accessDenied'          => 'Access denied.',

    // Forgot / reset password
    'forgotPassword'        => 'Forgot password?',
    'forgotPasswordTitle'   => 'Reset Password',
    'forgotPasswordHelp'    => 'Enter your email address and we\'ll send you a reset link if an account exists.',
    'sendResetLink'         => 'Send Reset Link',
    'resetEmailSent'        => 'If that email address is registered, a reset link has been sent. Check your inbox.',
    'resetPasswordTitle'    => 'Set New Password',
    'newPassword'           => 'New Password',
    'setPassword'           => 'Set Password',
    'passwordResetSuccess'  => 'Password reset successfully. You can now log in.',
    'invalidResetToken'     => 'This reset link is invalid or has expired. Please request a new one.',
];
