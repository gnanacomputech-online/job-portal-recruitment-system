<?php

namespace App\Models;

use Core\Database;

/** The curated skills taxonomy (FEATURES.md) — never free text. */
class Skill
{
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM skills ORDER BY category ASC, name ASC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM skills WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function nameExists(string $name): bool
    {
        $stmt = Database::connection()->prepare('SELECT id FROM skills WHERE name = ?');
        $stmt->execute([$name]);
        return (bool) $stmt->fetch();
    }

    public static function create(string $name, string $category): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO skills (name, category) VALUES (?, ?)');
        $stmt->execute([$name, $category]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM skills WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * How many postings and candidate profiles currently reference a skill.
     * Both `job_skills` and `candidate_skills` cascade on delete, so removing
     * an in-use skill would silently strip it from every posting's
     * requirements and every candidate's profile — changing live match scores
     * and orphaning the skill names already frozen into
     * `applications.score_breakdown`. Callers use this to refuse the delete.
     *
     * @return array{jobs:int, candidates:int, total:int}
     */
    public static function usage(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM job_skills WHERE skill_id = ?');
        $stmt->execute([$id]);
        $jobs = (int) $stmt->fetchColumn();

        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM candidate_skills WHERE skill_id = ?');
        $stmt->execute([$id]);
        $candidates = (int) $stmt->fetchColumn();

        return ['jobs' => $jobs, 'candidates' => $candidates, 'total' => $jobs + $candidates];
    }

    /** Usage counts for every skill at once, keyed by skill id — for the admin taxonomy screen. */
    public static function usageMap(): array
    {
        $map = [];
        $sql = 'SELECT s.id,
                       (SELECT COUNT(*) FROM job_skills js WHERE js.skill_id = s.id) AS job_count,
                       (SELECT COUNT(*) FROM candidate_skills cs WHERE cs.skill_id = s.id) AS candidate_count
                FROM skills s';
        foreach (Database::connection()->query($sql) as $row) {
            $map[(int) $row['id']] = [
                'jobs' => (int) $row['job_count'],
                'candidates' => (int) $row['candidate_count'],
                'total' => (int) $row['job_count'] + (int) $row['candidate_count'],
            ];
        }
        return $map;
    }

    /** Grouped by category, for the autocomplete/picker UI. */
    public static function grouped(): array
    {
        $grouped = [];
        foreach (self::all() as $skill) {
            $grouped[$skill['category']][] = $skill;
        }
        return $grouped;
    }
}
