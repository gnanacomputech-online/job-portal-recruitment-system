<?php

namespace App\Models;

use Core\Database;
use PDO;

/**
 * All three personas (admin/recruiter/candidate) live in this one table,
 * distinguished by `role`, per schema.sql's header comment and FEATURES.md's
 * "three onboarding paths, one converging model".
 */
class User
{
    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (name, email, password_hash, role, status, source)
             VALUES (:name, :email, :password_hash, :role, :status, :source)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'status' => $data['status'],
            'source' => $data['source'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function updateProfile(int $id, string $name, string $email): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$name, $email, $id]);
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$passwordHash, $id]);
    }

    public static function setResetToken(int $id, string $token, string $expiresAt): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?'
        );
        $stmt->execute([$token, $expiresAt, $id]);
    }

    public static function findByValidResetToken(string $token): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public static function clearResetToken(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?'
        );
        $stmt->execute([$id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function emailExists(string $email): bool
    {
        return self::findByEmail($email) !== null;
    }

    /** @return array<int, array> recruiters, optionally filtered by status, newest first */
    public static function listRecruiters(?string $status = null): array
    {
        // application_count drives the delete guard on the admin screen: an
        // account whose postings already hold applications can't be deleted
        // (the cascade would take those applications with it), only suspended.
        $sql = "SELECT u.*, rp.company_name, rp.work_email_domain,
                       (SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id
                        WHERE j.recruiter_id = u.id) AS application_count
                FROM users u LEFT JOIN recruiter_profiles rp ON rp.user_id = u.id
                WHERE u.role = 'recruiter'";
        $params = [];
        if ($status !== null) {
            $sql .= ' AND u.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY u.created_at DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<int, array> candidates, optionally filtered by status */
    public static function listCandidates(?string $status = null): array
    {
        $sql = "SELECT u.*, cp.headline, cp.location, cp.open_to_work
                FROM users u LEFT JOIN candidate_profiles cp ON cp.user_id = u.id
                WHERE u.role = 'candidate'";
        $params = [];
        if ($status !== null) {
            $sql .= ' AND u.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY u.created_at DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByRole(string $role): int
    {
        $stmt = Database::connection()->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return (int) $stmt->fetchColumn();
    }

    public static function countByRoleStatus(string $role, string $status): int
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM users WHERE role = ? AND status = ?');
        $stmt->execute([$role, $status]);
        return (int) $stmt->fetchColumn();
    }
}
