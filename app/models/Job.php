<?php

namespace App\Models;

use Core\Database;

class Job
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT j.*, rp.company_name, rp.logo_path, u.status AS recruiter_status
             FROM jobs j
             JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
             JOIN users u ON u.id = j.recruiter_id
             WHERE j.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $recruiterId, array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO jobs (recruiter_id, title, description, location, job_type, min_years_experience, min_education_level, status)
             VALUES (:recruiter_id, :title, :description, :location, :job_type, :min_years_experience, :min_education_level, :status)'
        );
        $stmt->execute([
            'recruiter_id' => $recruiterId,
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'job_type' => $data['job_type'],
            'min_years_experience' => $data['min_years_experience'],
            'min_education_level' => $data['min_education_level'] ?: null,
            'status' => $data['status'] ?? 'open',
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE jobs SET title = :title, description = :description, location = :location,
             job_type = :job_type, min_years_experience = :min_years_experience,
             min_education_level = :min_education_level WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'job_type' => $data['job_type'],
            'min_years_experience' => $data['min_years_experience'],
            'min_education_level' => $data['min_education_level'] ?: null,
        ]);
    }

    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE jobs SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function updateModeration(int $id, string $moderationStatus, ?string $note): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE jobs SET moderation_status = ?, moderation_note = ? WHERE id = ?'
        );
        $stmt->execute([$moderationStatus, $note, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM jobs WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function listForRecruiter(int $recruiterId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS application_count
             FROM jobs j WHERE j.recruiter_id = ? ORDER BY j.created_at DESC'
        );
        $stmt->execute([$recruiterId]);
        return $stmt->fetchAll();
    }

    /**
     * Public/candidate browse. Only 'open' + 'active' postings from a
     * currently-approved recruiter, with optional filters — a suspended
     * recruiter's postings must never surface here even if the posting
     * itself was never individually moderated. Returns rows joined with
     * company name for the job cards.
     */
    public static function browse(array $filters = []): array
    {
        $sql = "SELECT j.*, rp.company_name, rp.logo_path
                FROM jobs j
                JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
                JOIN users u ON u.id = j.recruiter_id
                WHERE j.status = 'open' AND j.moderation_status = 'active' AND u.status = 'approved'";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= ' AND (j.title LIKE ? OR rp.company_name LIKE ?)';
            $kw = '%' . $filters['keyword'] . '%';
            $params[] = $kw;
            $params[] = $kw;
        }
        if (!empty($filters['location'])) {
            $sql .= ' AND j.location LIKE ?';
            $params[] = '%' . $filters['location'] . '%';
        }
        if (!empty($filters['job_type'])) {
            $sql .= ' AND j.job_type = ?';
            $params[] = $filters['job_type'];
        }
        $sql .= ' ORDER BY j.created_at DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** For admin moderation screen — every job regardless of status. */
    public static function listAllForAdmin(): array
    {
        $stmt = Database::connection()->query(
            'SELECT j.*, rp.company_name, u.name AS recruiter_name
             FROM jobs j
             JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
             JOIN users u ON u.id = j.recruiter_id
             ORDER BY j.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public static function countOpen(): int
    {
        $stmt = Database::connection()->query(
            "SELECT COUNT(*) FROM jobs j JOIN users u ON u.id = j.recruiter_id
             WHERE j.status = 'open' AND j.moderation_status = 'active' AND u.status = 'approved'"
        );
        return (int) $stmt->fetchColumn();
    }

    public static function countTotal(): int
    {
        $stmt = Database::connection()->query('SELECT COUNT(*) FROM jobs');
        return (int) $stmt->fetchColumn();
    }
}
