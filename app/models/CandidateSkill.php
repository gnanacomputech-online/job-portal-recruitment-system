<?php

namespace App\Models;

use Core\Database;

class CandidateSkill
{
    public static function listForCandidate(int $candidateId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT cs.*, s.name AS skill_name, s.category
             FROM candidate_skills cs JOIN skills s ON s.id = cs.skill_id
             WHERE cs.candidate_id = ? ORDER BY s.category ASC, s.name ASC'
        );
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll();
    }

    public static function has(int $candidateId, int $skillId): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT 1 FROM candidate_skills WHERE candidate_id = ? AND skill_id = ?'
        );
        $stmt->execute([$candidateId, $skillId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function add(int $candidateId, int $skillId, string $proficiency): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO candidate_skills (candidate_id, skill_id, proficiency) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE proficiency = VALUES(proficiency)'
        );
        $stmt->execute([$candidateId, $skillId, $proficiency]);
    }

    public static function remove(int $candidateId, int $skillId): void
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM candidate_skills WHERE candidate_id = ? AND skill_id = ?'
        );
        $stmt->execute([$candidateId, $skillId]);
    }
}
