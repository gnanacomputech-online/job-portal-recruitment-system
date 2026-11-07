<?php

namespace App\Models;

use Core\Database;

/**
 * `match_score`/`score_breakdown` are an immutable snapshot taken at the
 * moment of applying (FEATURES.md) — see Core\MatchScoreCalculator.
 */
class Application
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, j.title AS job_title, j.recruiter_id, j.status AS job_status, j.moderation_status AS job_moderation_status,
                    rp.company_name, u.name AS candidate_name, u.email AS candidate_email,
                    u.status AS candidate_status
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
             JOIN users u ON u.id = a.candidate_id
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['score_breakdown'] = json_decode($row['score_breakdown'], true);
        }
        return $row ?: null;
    }

    public static function findByJobAndCandidate(int $jobId, int $candidateId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM applications WHERE job_id = ? AND candidate_id = ?'
        );
        $stmt->execute([$jobId, $candidateId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $jobId, int $candidateId, array $scoreResult): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO applications (job_id, candidate_id, status, match_score, score_breakdown)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$jobId, $candidateId, 'applied', $scoreResult['overall'], json_encode($scoreResult)]);
        $id = (int) $pdo->lastInsertId();

        ApplicationStatusHistory::record($id, null, 'applied', $candidateId, null);

        return $id;
    }

    public static function listForCandidate(int $candidateId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, j.title AS job_title, j.location, j.job_type, rp.company_name
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
             WHERE a.candidate_id = ?
             ORDER BY a.applied_at DESC'
        );
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll();
    }

    /** Ranked applicants for a recruiter's job, highest match first. */
    public static function listForJob(int $jobId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, u.name AS candidate_name, u.email AS candidate_email, u.status AS candidate_status,
                    cp.headline, cp.location, cp.resume_path
             FROM applications a
             JOIN users u ON u.id = a.candidate_id
             LEFT JOIN candidate_profiles cp ON cp.user_id = a.candidate_id
             WHERE a.job_id = ?
             ORDER BY a.match_score DESC, a.applied_at ASC'
        );
        $stmt->execute([$jobId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE applications SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    /** Platform-wide, for admin audit view, most recent first, with optional status filter. */
    public static function listAllForAdmin(?string $status = null): array
    {
        $sql = 'SELECT a.*, j.title AS job_title, rp.company_name, u.name AS candidate_name
                FROM applications a
                JOIN jobs j ON j.id = a.job_id
                JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
                JOIN users u ON u.id = a.candidate_id';
        $params = [];
        if ($status !== null) {
            $sql .= ' WHERE a.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY a.applied_at DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Has this candidate applied to any of this recruiter's postings? Used to
     * decide whether a recruiter may open a candidate's profile preview when
     * that candidate isn't in the open-to-work pool.
     */
    public static function existsForRecruiterAndCandidate(int $recruiterId, int $candidateId): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT 1 FROM applications a JOIN jobs j ON j.id = a.job_id
             WHERE j.recruiter_id = ? AND a.candidate_id = ? LIMIT 1'
        );
        $stmt->execute([$recruiterId, $candidateId]);
        return (bool) $stmt->fetchColumn();
    }

    /** How many applications a single posting has — guards destructive job deletes. */
    public static function countForJob(int $jobId): int
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM applications WHERE job_id = ?');
        $stmt->execute([$jobId]);
        return (int) $stmt->fetchColumn();
    }

    /** How many applications sit across all of one recruiter's postings — guards account deletes. */
    public static function countForRecruiter(int $recruiterId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.recruiter_id = ?'
        );
        $stmt->execute([$recruiterId]);
        return (int) $stmt->fetchColumn();
    }

    public static function countTotal(): int
    {
        $stmt = Database::connection()->query('SELECT COUNT(*) FROM applications');
        return (int) $stmt->fetchColumn();
    }
}
