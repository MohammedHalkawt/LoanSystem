<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Google\Client;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('google-drive:auth {code?}', function (?string $code = null) {
    $credentialsPath = storage_path(env('GOOGLE_DRIVE_OAUTH_CREDENTIALS_PATH', 'app/private/google-drive-oauth-client.json'));

    if (!is_file($credentialsPath)) {
        $this->error('OAuth client file not found: ' . $credentialsPath);
        $this->line('Download a Desktop App OAuth JSON file from Google Cloud and save it there first.');
        return 1;
    }

    $client = new Client();
    $client->setAuthConfig($credentialsPath);
    $client->addScope(\Google\Service\Drive::DRIVE);
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    $client->setRedirectUri('http://127.0.0.1');

    if (!$code) {
        $this->line('Open this URL while logged into the Drive account you want the app to use:');
        $this->newLine();
        $this->line($client->createAuthUrl());
        $this->newLine();
        $this->line('Your browser may show "site cannot be reached" after approval. That is okay.');
        $this->line('Copy the value after code= from the browser address bar, then run:');
        $this->line('php artisan google-drive:auth "YOUR_CODE_HERE"');
        return 0;
    }

    $token = $client->fetchAccessTokenWithAuthCode($code);

    if (isset($token['error'])) {
        $this->error($token['error_description'] ?? $token['error']);
        return 1;
    }

    if (empty($token['refresh_token'])) {
        $this->error('Google did not return a refresh token. Re-run the first command and make sure you approve access.');
        return 1;
    }

    setEnvValue('GOOGLE_DRIVE_AUTH_MODE', 'oauth');
    setEnvValue('GOOGLE_DRIVE_OAUTH_CREDENTIALS_PATH', env('GOOGLE_DRIVE_OAUTH_CREDENTIALS_PATH', 'app/private/google-drive-oauth-client.json'));
    setEnvValue('GOOGLE_DRIVE_REFRESH_TOKEN', $token['refresh_token']);

    $this->info('Google Drive OAuth is ready. Receipts will upload as the authorized Google account.');
    return 0;
})->purpose('Authorize Google Drive uploads with a real Google account');

if (!function_exists('setEnvValue')) {
    function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $env = file_get_contents($envPath);
        $line = $key . '=' . $value;

        if (preg_match('/^' . preg_quote($key, '/') . '=.*/m', $env)) {
            $env = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $line, $env);
        } else {
            $env = rtrim($env) . PHP_EOL . $line . PHP_EOL;
        }

        file_put_contents($envPath, $env);
    }
}
