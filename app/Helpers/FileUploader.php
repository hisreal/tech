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
     * Extension => acceptable real content MIME types, used to reject a
     * file whose content doesn't match its claimed extension (e.g. a
     * renamed script uploaded as ".jpg").
     *
     * @var array<string, array<int, string>>
     */
    private const MIME_MAP = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
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

        self::assertContentMatchesExtension((string) $file['tmp_name'], $extension);

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

    /**
     * Rejects a file whose real content type doesn't match its extension.
     * Only enforced when the extension has a known mapping, so custom
     * extensions passed by future callers aren't blocked.
     */
    private static function assertContentMatchesExtension(string $tmpPath, string $extension): void
    {
        $allowedMimes = self::MIME_MAP[$extension] ?? null;

        if ($allowedMimes === null) {
            return;
        }

        $detected = null;

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $detected = finfo_file($finfo, $tmpPath) ?: null;
                finfo_close($finfo);
            }
        }

        if ($detected === null) {
            $detected = mime_content_type($tmpPath) ?: null;
        }

        if ($detected !== null && !in_array($detected, $allowedMimes, true)) {
            throw new \InvalidArgumentException('The uploaded file content does not match its extension.');
        }
    }

    /** Deletes an uploaded file by relative path. */
    public static function delete(string $path): bool
    {
        $fullPath = Application::instance()->rootPath($path);

        return is_file($fullPath) && unlink($fullPath);
    }
}
