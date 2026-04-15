<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\FileUploadHandler;
use App\Core\AvatarManager;
use App\Core\DocumentManager;
use App\Core\DownloadHandler;

class UploadController extends BaseController
{
    private FileUploadHandler $uploadHandler;
    private AvatarManager $avatarManager;
    private DocumentManager $documentManager;
    private DownloadHandler $downloadHandler;

    public function __construct()
    {
        parent::__construct();
        $this->uploadHandler = new FileUploadHandler();
        $this->avatarManager = new AvatarManager();
        $this->documentManager = new DocumentManager();
        $this->downloadHandler = new DownloadHandler();
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(): void
    {
        if (!isset($_FILES['avatar'])) {
            $this->json([
                'success' => false,
                'message' => 'No file provided.',
            ], 400);
            return;
        }

        try {
            $result = $this->uploadHandler->uploadAvatar($_FILES['avatar']);

            if (!$result['success']) {
                $this->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
                return;
            }

            $this->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully.',
                'data' => [
                    'filename' => $result['filename'],
                    'url' => $result['url'],
                ],
            ], 200);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload document file
     */
    public function uploadDocument(): void
    {
        if (!isset($_FILES['document'])) {
            $this->json([
                'success' => false,
                'message' => 'No file provided.',
            ], 400);
            return;
        }

        try {
            $result = $this->uploadHandler->uploadDocument($_FILES['document']);

            if (!$result['success']) {
                $this->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
                return;
            }

            $this->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'data' => [
                    'filename' => $result['filename'],
                    'url' => $result['url'],
                ],
            ], 200);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload product image
     */
    public function uploadProductImage(): void
    {
        if (!isset($_FILES['image'])) {
            $this->json([
                'success' => false,
                'message' => 'No file provided.',
            ], 400);
            return;
        }

        try {
            $result = $this->uploadHandler->uploadProductImage($_FILES['image']);

            if (!$result['success']) {
                $this->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
                return;
            }

            $this->json([
                'success' => true,
                'message' => 'Product image uploaded successfully.',
                'data' => [
                    'filename' => $result['filename'],
                    'thumbnail' => $result['thumbnail'] ?? null,
                    'url' => $result['url'],
                ],
            ], 200);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload product document
     */
    public function uploadProductDocument(): void
    {
        if (!isset($_FILES['file'])) {
            $this->json([
                'success' => false,
                'message' => 'No file provided.',
            ], 400);
            return;
        }

        try {
            $result = $this->uploadHandler->uploadProductDocument($_FILES['file']);

            if (!$result['success']) {
                $this->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
                return;
            }

            $this->json([
                'success' => true,
                'message' => 'Product document uploaded successfully.',
                'data' => [
                    'filename' => $result['filename'],
                    'url' => $result['url'],
                ],
            ], 200);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete uploaded file
     */
    public function deleteFile(string $type, int $id): void
    {
        try {
            $result = $this->uploadHandler->deleteFile($type, $id);

            if (!$result['success']) {
                $this->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
                return;
            }

            $this->json([
                'success' => true,
                'message' => 'File deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download file
     */
    public function download(string $type, string $filename): void
    {
        try {
            $this->downloadHandler->download($type, $filename);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
