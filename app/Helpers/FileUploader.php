<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Application;

/**
 * Handles validated file uploads for photos, documents, and school assets.
 */
final class FileUploader
{
    private const EXTENSIONS = [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
    ];

    /**
     * Uploads a file and returns metadata for storage.
     *
     * @param array<string, mixed> $file
     * @param array<int, string> $allowedExtensions
     * @return array<string, mixed>
     */
    public static function upload(array $file, string $directory = '', array $allowedExtensions = [], ?int $maxSize = null): array
    {
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('The uploaded file is invalid.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = $allowedExtensions !== [] ? $allowedExtensions : array_merge(self::EXTENSIONS['images'], self::EXTENSIONS['documents']);

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException('This file type is not allowed.');
        }

        $maxSize = $maxSize ?? (int) Application::instance()->config('uploads.max_size', 5242880);

        if ((int) $file['size'] > $maxSize) {
            throw new \InvalidArgumentException('The uploaded file is too large.');
        }

        $uploadRoot = Application::instance()->rootPath((string) Application::instance()->config('uploads.dir', 'app/Storage/uploads'));
        $targetDir = rtrim($uploadRoot . '/' . trim($directory, '/'), '/');

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $filename = Security::randomString(32) . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Unable to move uploaded file.');
        }

        return [
            'name' => $filename,
            'original_name' => $file['name'],
            'path' => str_replace('\\', '/', str_replace(Application::instance()->rootPath() . '/', '', $targetPath)),
            'mime_type' => mime_content_type($targetPath) ?: null,
            'size' => (int) $file['size'],
            'is_image' => in_array($extension, self::EXTENSIONS['images'], true),
        ];
    }

    /** Deletes an uploaded file by relative path. */
    public static function delete(string $path): bool
    {
        $fullPath = Application::instance()->rootPath($path);

        return is_file($fullPath) && unlink($fullPath);
    }
}
