<?php

namespace App\Models;

use Core\Database;

class JobSkill
{
    public static function listForJob(int $jobId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT js.*, s.name AS skill_name, s.category
             FROM job_skills js JOIN skills s ON s.id = js.skill_id
             WHERE js.job_id = ?
             ORDER BY (js.importance = 'must_have') DESC, s.name ASC"
        );
        $stmt->execute([$jobId]);
        return $stmt->fetchAll();
    }

    public static function replaceForJob(int $jobId, array $requirements): void
    {
        $pdo = Database::connection();
        $pdo->prepare('DELETE FROM job_skills WHERE job_id = ?')->execute([$jobId]);
        $stmt = $pdo->prepare(
            'INSERT INTO job_skills (job_id, skill_id, importance, min_proficiency) VALUES (?, ?, ?, ?)'
        );
        foreach ($requirements as $req) {
            $stmt->execute([$jobId, $req['skill_id'], $req['importance'], $req['min_proficiency']]);
        }
    }
}
