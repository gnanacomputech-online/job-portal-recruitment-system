<?php

namespace App\Models;

use Core\Database;

class CandidateEducation
{
    public static function listForCandidate(int $candidateId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM candidate_education WHERE candidate_id = ? ORDER BY year_completed DESC'
        );
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM candidate_education WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $candidateId, array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO candidate_education (candidate_id, degree, institution, field, level, year_completed)
             VALUES (:candidate_id, :degree, :institution, :field, :level, :year_completed)'
        );
        $stmt->execute([
            'candidate_id' => $candidateId,
            'degree' => $data['degree'],
            'institution' => $data['institution'],
            'field' => $data['field'] ?: null,
            'level' => $data['level'],
            'year_completed' => $data['year_completed'] ?: null,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function delete(int $id, int $candidateId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM candidate_education WHERE id = ? AND candidate_id = ?');
        $stmt->execute([$id, $candidateId]);
    }
}
