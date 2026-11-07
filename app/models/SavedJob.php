<?php

namespace App\Models;

use Core\Database;

class SavedJob
{
    public static function isSaved(int $candidateId, int $jobId): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT 1 FROM saved_jobs WHERE candidate_id = ? AND job_id = ?'
        );
        $stmt->execute([$candidateId, $jobId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function save(int $candidateId, int $jobId): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT IGNORE INTO saved_jobs (candidate_id, job_id) VALUES (?, ?)'
        );
        $stmt->execute([$candidateId, $jobId]);
    }

    public static function unsave(int $candidateId, int $jobId): void
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM saved_jobs WHERE candidate_id = ? AND job_id = ?'
        );
        $stmt->execute([$candidateId, $jobId]);
    }

    public static function listForCandidate(int $candidateId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT sj.saved_at, j.*, rp.company_name, rp.logo_path, u.status AS recruiter_status
             FROM saved_jobs sj
             JOIN jobs j ON j.id = sj.job_id
             JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
             JOIN users u ON u.id = j.recruiter_id
             WHERE sj.candidate_id = ?
             ORDER BY sj.saved_at DESC'
        );
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll();
    }

    /** Set of job ids the candidate has saved, for quickly flagging cards in a listing. */
    public static function savedJobIds(int $candidateId): array
    {
        $stmt = Database::connection()->prepare('SELECT job_id FROM saved_jobs WHERE candidate_id = ?');
        $stmt->execute([$candidateId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }
}
