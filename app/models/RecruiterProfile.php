<?php

namespace App\Models;

use Core\Database;

class RecruiterProfile
{
    public static function find(int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM recruiter_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $userId, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO recruiter_profiles (user_id, company_name, company_description, website, work_email_domain, contact_name, contact_phone)
             VALUES (:user_id, :company_name, :company_description, :website, :work_email_domain, :contact_name, :contact_phone)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'company_name' => $data['company_name'],
            'company_description' => $data['company_description'] ?? null,
            'website' => $data['website'] ?? null,
            'work_email_domain' => $data['work_email_domain'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
        ]);
    }

    public static function upsert(int $userId, array $data): void
    {
        if (self::find($userId) === null) {
            self::create($userId, $data);
            return;
        }
        $stmt = Database::connection()->prepare(
            'UPDATE recruiter_profiles SET company_name = :company_name, company_description = :company_description,
             website = :website, work_email_domain = :work_email_domain, contact_name = :contact_name,
             contact_phone = :contact_phone WHERE user_id = :user_id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'company_name' => $data['company_name'],
            'company_description' => $data['company_description'] ?? null,
            'website' => $data['website'] ?? null,
            'work_email_domain' => $data['work_email_domain'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
        ]);
    }

    public static function updateLogo(int $userId, string $logoPath): void
    {
        $stmt = Database::connection()->prepare('UPDATE recruiter_profiles SET logo_path = ? WHERE user_id = ?');
        $stmt->execute([$logoPath, $userId]);
    }

    /** Joined with the users row, for a public company page. */
    public static function findWithUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT rp.*, u.name AS contact_user_name, u.status AS account_status
             FROM recruiter_profiles rp JOIN users u ON u.id = rp.user_id
             WHERE rp.user_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
}
