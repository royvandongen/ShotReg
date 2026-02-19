<?php

namespace App\Libraries;

use App\Models\AppSettingModel;
use CodeIgniter\Email\Email;

class Mailer
{
    protected AppSettingModel $settings;

    public function __construct()
    {
        $this->settings = new AppSettingModel();
    }

    /**
     * Send an email using SMTP settings from app_settings.
     */
    public function send(string $to, string $toName, string $subject, string $html): bool
    {
        $email = $this->buildEmailService();
        if ($email === null) {
            return false;
        }

        $email->setTo($to, $toName);
        $email->setSubject($subject);
        $email->setMessage($html);

        return $email->send(false);
    }

    /**
     * Send an invite email.
     */
    public function sendInvite(string $toEmail, string $inviterName, string $token): bool
    {
        $baseUrl    = rtrim(config('App')->baseURL, '/');
        $inviteLink = $baseUrl . '/invite/' . $token;
        $siteName   = 'Shotr';

        $template = $this->settings->getValue('email_template_invite') ?: $this->defaultInviteTemplate();

        $html = $this->renderTemplate($template, [
            '{site_name}'    => $siteName,
            '{inviter_name}' => $inviterName,
            '{invite_link}'  => $inviteLink,
            '{expires_hours}' => '48',
        ]);

        $subject = $inviterName . ' has invited you to ' . $siteName;

        return $this->send($toEmail, '', $subject, $html);
    }

    /**
     * Send a password reset email.
     */
    public function sendPasswordReset(string $toEmail, string $token, int $expiryMinutes = 60): bool
    {
        $baseUrl   = rtrim(config('App')->baseURL, '/');
        $resetLink = $baseUrl . '/auth/reset-password/' . $token;
        $siteName  = 'Shotr';

        $template = $this->settings->getValue('email_template_reset') ?: $this->defaultResetTemplate();

        $html = $this->renderTemplate($template, [
            '{site_name}'            => $siteName,
            '{reset_link}'           => $resetLink,
            '{password_reset_link}'  => $resetLink,
            '{expires_minutes}'      => (string) $expiryMinutes,
        ]);

        return $this->send($toEmail, '', 'Reset your ' . $siteName . ' password', $html);
    }

    /**
     * Test email connectivity by sending to a specific address.
     */
    public function sendTest(string $toEmail): bool
    {
        $siteName = 'Shotr';
        $html     = '<p>This is a test email from <strong>' . $siteName . '</strong>. Your email settings are working correctly.</p>';

        return $this->send($toEmail, '', $siteName . ' — Test Email', $html);
    }

    /**
     * Check if email is configured.
     */
    public function isConfigured(): bool
    {
        $protocol = $this->settings->getValue('email_protocol', 'smtp');

        if ($protocol === 'smtp') {
            return (bool) $this->settings->getValue('smtp_host');
        }

        return true;
    }

    // -------------------------------------------------------------------------

    protected function buildEmailService(): ?Email
    {
        $protocol    = $this->settings->getValue('email_protocol', 'smtp');
        $fromAddress = $this->settings->getValue('email_from_address');
        $fromName    = $this->settings->getValue('email_from_name', 'Shotr');

        if (! $fromAddress) {
            return null;
        }

        $config = [
            'protocol' => $protocol,
            'mailType' => 'html',
            'charset'  => 'utf-8',
            'wordWrap' => true,
        ];

        if ($protocol === 'smtp') {
            $config['SMTPHost']   = $this->settings->getValue('smtp_host', '');
            $config['SMTPPort']   = (int) $this->settings->getValue('smtp_port', '587');
            $config['SMTPUser']   = $this->settings->getValue('smtp_user', '');
            $config['SMTPPass']   = $this->settings->getValue('smtp_pass', '');
            $config['SMTPCrypto'] = $this->settings->getValue('smtp_crypto', 'tls');
            $config['SMTPTimeout'] = 10;
        }

        $email = service('email', null, false);
        $email->initialize($config);
        $email->setFrom($fromAddress, $fromName);

        return $email;
    }

    protected function renderTemplate(string $template, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public function defaultInviteTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>You've been invited to {site_name}</h2>
    <p><strong>{inviter_name}</strong> has invited you to join {site_name}, a shooting session log.</p>
    <p>
        <a href="{invite_link}" style="display:inline-block;padding:10px 20px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:4px;">
            Accept Invitation
        </a>
    </p>
    <p style="color:#888;font-size:12px;">This invitation expires in {expires_hours} hours. If you did not expect this, you can ignore this email.</p>
</body>
</html>
HTML;
    }

    public function defaultResetTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Reset your {site_name} password</h2>
    <p>We received a request to reset the password for your account.</p>
    <p>
        <a href="{password_reset_link}" style="display:inline-block;padding:10px 20px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:4px;">
            Reset Password
        </a>
    </p>
    <p style="color:#888;font-size:12px;">This link expires in {expires_minutes} minutes. If you did not request a password reset, you can ignore this email.</p>
</body>
</html>
HTML;
    }
}
