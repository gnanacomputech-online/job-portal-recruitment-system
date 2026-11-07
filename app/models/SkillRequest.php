<?php

namespace App\Models;

use Core\Database;

/** Recruiter/candidate-submitted new-skill requests, pending admin approval (FEATURES.md). */
class SkillRequest
{
    public static function create(int $requestedBy, string $name, string $category): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO skill_requests (requested_by, name, category, status) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$requestedBy, $name, $category, 'pending']);
        return (int) Database::connection()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM skill_requests WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function listPending(): array
    {
        $stmt = Database::connection()->query(
            "SELECT sr.*, u.name AS requested_by_name, u.role AS requested_by_role
             FROM skill_requests sr JOIN users u ON u.id = sr.requested_by
             WHERE sr.status = 'pending' ORDER BY sr.created_at ASC"
        );
        return $stmt->fetchAll();
    }

    public static function listAll(): array
    {
        $stmt = Database::connection()->query(
            "SELECT sr.*, u.name AS requested_by_name, u.role AS requested_by_role, r.name AS reviewed_by_name
             FROM skill_requests sr JOIN users u ON u.id = sr.requested_by
             LEFT JOIN users r ON r.id = sr.reviewed_by
             ORDER BY sr.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public static function approve(int $id, int $reviewerId): void
    {
        $stmt = Database::connection()->prepare(
            "UPDATE skill_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$reviewerId, $id]);
    }

    public static function reject(int $id, int $reviewerId): void
    {
        $stmt = Database::connection()->prepare(
            "UPDATE skill_requests SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$reviewerId, $id]);
    }
}
