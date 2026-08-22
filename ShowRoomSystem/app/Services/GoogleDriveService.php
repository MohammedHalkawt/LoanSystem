<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected $drive;
    protected $baseFolderId;

    public function __construct()
    {
        try {
            $client = $this->buildClient();

            $this->drive = new Drive($client);
            $this->baseFolderId = env('GOOGLE_DRIVE_FOLDER_ID');
        } catch (\Exception $e) {
            Log::warning('Google Drive setup failed: ' . $e->getMessage());
            $this->drive = null;
            $this->baseFolderId = null;
        }
    }

    /**
     * Create a folder under the base folder.
     *
     * @param string $folderName
     * @return string|null Folder ID if successful, else null.
     */
    public function createFolder($folderName)
    {
        return $this->createFolderIn($folderName, $this->baseFolderId);
    }

    public function createFolderIn($folderName, $parentFolderId)
    {
        try {
            if (!$this->drive || !$parentFolderId) {
                return null;
            }

            $fileMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentFolderId]
            ]);

            $folder = $this->drive->files->create($fileMetadata, ['fields' => 'id']);
            return $folder->id;
        } catch (\Exception $e) {
            Log::warning('Google Drive folder creation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function buildClient()
    {
        $client = new Client();
        $client->addScope(Drive::DRIVE);

        if (env('GOOGLE_DRIVE_AUTH_MODE') === 'oauth') {
            $client->setAuthConfig(storage_path(env('GOOGLE_DRIVE_OAUTH_CREDENTIALS_PATH')));
            $client->setAccessType('offline');
            $client->setPrompt('consent');

            $refreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');

            if (!$refreshToken) {
                throw new \RuntimeException('GOOGLE_DRIVE_REFRESH_TOKEN is missing.');
            }

            $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (isset($token['error'])) {
                throw new \RuntimeException('Google OAuth refresh failed: ' . ($token['error_description'] ?? $token['error']));
            }

            return $client;
        }

        $client->setAuthConfig(storage_path(env('GOOGLE_DRIVE_CREDENTIALS_PATH')));

        return $client;
    }

    public function findOrCreateFolder($folderName, $parentFolderId)
    {
        if (!$parentFolderId) {
            return null;
        }

        try {
            if (!$this->drive) {
                return null;
            }

            $escapedName = str_replace("'", "\\'", $folderName);
            $query = sprintf(
                "name = '%s' and mimeType = 'application/vnd.google-apps.folder' and '%s' in parents and trashed = false",
                $escapedName,
                $parentFolderId
            );

            $folders = $this->drive->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'pageSize' => 1,
            ]);

            if (count($folders->files) > 0) {
                return $folders->files[0]->id;
            }

            return $this->createFolderIn($folderName, $parentFolderId);
        } catch (\Exception $e) {
            Log::warning('Google Drive folder lookup failed: ' . $e->getMessage());
            return null;
        }
    }

    public function uploadFile($filePath, $fileName, $parentFolderId, $mimeType = 'application/pdf')
    {
        if (!$parentFolderId || !is_file($filePath)) {
            return null;
        }

        try {
            if (!$this->drive) {
                return null;
            }

            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$parentFolderId],
            ]);

            $file = $this->drive->files->create($fileMetadata, [
                'data' => file_get_contents($filePath),
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink',
            ]);

            return $file->id;
        } catch (\Exception $e) {
            Log::warning('Google Drive upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the direct web view link for a folder.
     *
     * @param string $folderId
     * @return string
     */
    public function getFolderLink($folderId)
    {
        return "https://drive.google.com/drive/folders/{$folderId}";
    }
}
