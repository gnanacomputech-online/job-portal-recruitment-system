<?php

namespace App\Models;

use Core\Database;

class ApplicationStatusHistory
{
    public static function record(int $applicationId, ?string $fromStatus, string $toStatus, ?int $changedBy, ?string $note): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO application_status_history (application_id, from_status, to_status, changed_by, note)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$applicationId, $fromStatus, $toStatus, $changedBy, $note]);
    }

    public static function listForApplication(int $applicationId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT ash.*, u.name AS changed_by_name
             FROM application_status_history ash
             LEFT JOIN users u ON u.id = ash.changed_by
             WHERE ash.application_id = ?
             ORDER BY ash.changed_at ASC'
        );
        $stmt->execute([$applicationId]);
        return $stmt->fetchAll();
    }
}
