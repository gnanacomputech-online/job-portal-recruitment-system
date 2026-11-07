<?php

namespace App\Models;

use Core\Database;

class CandidateExperience
{
    public static function listForCandidate(int $candidateId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM candidate_experience WHERE candidate_id = ? ORDER BY is_current DESC, start_date DESC'
        );
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM candidate_experience WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $candidateId, array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO candidate_experience (candidate_id, title, company, start_date, end_date, is_current, description)
             VALUES (:candidate_id, :title, :company, :start_date, :end_date, :is_current, :description)'
        );
        $stmt->execute([
            'candidate_id' => $candidateId,
            'title' => $data['title'],
            'company' => $data['company'],
            'start_date' => $data['start_date'],
            'end_date' => $data['is_current'] ? null : ($data['end_date'] ?: null),
            'is_current' => $data['is_current'] ? 1 : 0,
            'description' => $data['description'] ?: null,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function delete(int $id, int $candidateId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM candidate_experience WHERE id = ? AND candidate_id = ?');
        $stmt->execute([$id, $candidateId]);
    }
}
