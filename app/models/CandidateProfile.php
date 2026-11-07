<?php

namespace App\Models;

use Core\Database;

class CandidateProfile
{
    public static function find(int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM candidate_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function upsertBasics(int $userId, array $data): void
    {
        if (self::find($userId) === null) {
            $stmt = Database::connection()->prepare(
                'INSERT INTO candidate_profiles (user_id, headline, location, phone) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $data['headline'] ?? null, $data['location'] ?? null, $data['phone'] ?? null]);
            return;
        }
        $stmt = Database::connection()->prepare(
            'UPDATE candidate_profiles SET headline = ?, location = ?, phone = ? WHERE user_id = ?'
        );
        $stmt->execute([$data['headline'] ?? null, $data['location'] ?? null, $data['phone'] ?? null, $userId]);
    }

    /** Ensures a row exists (e.g. before toggling open_to_work on a brand-new account). */
    private static function ensureRow(int $userId): void
    {
        if (self::find($userId) === null) {
            $stmt = Database::connection()->prepare('INSERT INTO candidate_profiles (user_id) VALUES (?)');
            $stmt->execute([$userId]);
        }
    }

    public static function setOpenToWork(int $userId, bool $open): void
    {
        self::ensureRow($userId);
        $stmt = Database::connection()->prepare('UPDATE candidate_profiles SET open_to_work = ? WHERE user_id = ?');
        $stmt->execute([$open ? 1 : 0, $userId]);
    }

    public static function updateResumePath(int $userId, string $path): void
    {
        self::ensureRow($userId);
        $stmt = Database::connection()->prepare('UPDATE candidate_profiles SET resume_path = ? WHERE user_id = ?');
        $stmt->execute([$path, $userId]);
    }

    public static function updatePhotoPath(int $userId, string $path): void
    {
        self::ensureRow($userId);
        $stmt = Database::connection()->prepare('UPDATE candidate_profiles SET photo_path = ? WHERE user_id = ?');
        $stmt->execute([$path, $userId]);
    }

    /**
     * LinkedIn-style profile-completeness meter (FEATURES.md). Purely derived
     * on read, not stored — six equally-weighted sections.
     * @return array{percent:int, missing:array<int,string>}
     */
    public static function completeness(int $userId): array
    {
        $profile = self::find($userId);
        $sections = [
            'Headline' => !empty($profile['headline'] ?? null),
            'Photo' => !empty($profile['photo_path'] ?? null),
            'Resume' => !empty($profile['resume_path'] ?? null),
            'Work experience' => count(CandidateExperience::listForCandidate($userId)) > 0,
            'Education' => count(CandidateEducation::listForCandidate($userId)) > 0,
            'Skills' => count(CandidateSkill::listForCandidate($userId)) > 0,
        ];
        $filled = count(array_filter($sections));
        $missing = array_keys(array_filter($sections, fn($v) => !$v));
        return [
            'percent' => (int) round(($filled / count($sections)) * 100),
            'missing' => $missing,
        ];
    }

    /** Open-to-work candidates, optionally filtered by a skill id, for recruiter candidate search. */
    public static function searchOpenToWork(?int $skillId = null): array
    {
        $sql = "SELECT u.id, u.name, u.email, cp.headline, cp.location, cp.resume_path, cp.open_to_work
                FROM users u
                JOIN candidate_profiles cp ON cp.user_id = u.id
                WHERE u.role = 'candidate' AND u.status = 'approved' AND cp.open_to_work = 1";
        $params = [];
        if ($skillId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM candidate_skills cs WHERE cs.candidate_id = u.id AND cs.skill_id = ?)';
            $params[] = $skillId;
        }
        $sql .= ' ORDER BY u.name ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
