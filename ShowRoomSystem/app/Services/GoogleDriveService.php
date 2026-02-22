<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    protected $drive;
    protected $baseFolderId;

    public function __construct()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path(env('GOOGLE_DRIVE_CREDENTIALS_PATH')));
        $client->addScope(Drive::DRIVE);

        $this->drive = new Drive($client);
        $this->baseFolderId = env('GOOGLE_DRIVE_FOLDER_ID');
    }

    /**
     * Create a folder under the base folder.
     *
     * @param string $folderName
     * @return string|null Folder ID if successful, else null.
     */
    public function createFolder($folderName)
    {
        try {
            $fileMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$this->baseFolderId]
            ]);

            $folder = $this->drive->files->create($fileMetadata, ['fields' => 'id']);
            return $folder->id;
        } catch (\Exception $e) {
            // Log error: $e->getMessage()
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