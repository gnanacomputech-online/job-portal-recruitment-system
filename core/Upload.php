<?php

namespace Core;

/**
 * Validated file upload helper (GROUNDRULES §7/§12): checks real MIME type
 * (via finfo, not the client-supplied Content-Type) + size, then stores the
 * file under a randomized filename to avoid overwrite/path-traversal risk
 * from the original client filename.
 */
class Upload
{
    /**
     * @param array $file one entry of $_FILES
     * @param string $subdir 'resumes' or 'logos' under public/uploads/
     * @param array $allowedMime map of allowed mime type => extension
     * @param int $maxBytes
     * @return string|null the stored relative path (e.g. 'resumes/ab12....pdf'), or null on failure
     */
    public static function store(array $file, string $subdir, array $allowedMime, int $maxBytes): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > $maxBytes || $file['size'] <= 0) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMime[$mime])) {
            return null;
        }

        $ext = $allowedMime[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir = __DIR__ . '/../public/uploads/' . $subdir;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $dest = $destDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return $subdir . '/' . $filename;
    }

    public static function storeResume(array $file): ?string
    {
        return self::store($file, 'resumes', [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ], 5 * 1024 * 1024);
    }

    public static function storeLogo(array $file): ?string
    {
        return self::store($file, 'logos', [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ], 2 * 1024 * 1024);
    }

    public static function storePhoto(array $file): ?string
    {
        return self::store($file, 'logos', [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ], 2 * 1024 * 1024);
    }
}
