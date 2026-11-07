<?php

namespace Core;

/**
 * The core differentiator of this platform (see FEATURES.md "Applications &
 * Match Scoring"): computes an explainable 0-100 fit score between a
 * candidate and a job posting from structured data only (skills taxonomy +
 * proficiency + experience timeline + education timeline) — never free text,
 * which is what makes every number here traceable back to a reason.
 *
 * Three weighted components:
 *   - Skills fit       (70%) — per required skill: weight (must_have=3,
 *                       nice_to_have=1) x min(candidate_proficiency /
 *                       required_proficiency, 1); missing a skill entirely
 *                       scores 0 for it. Sum earned / sum possible.
 *   - Experience fit    (20%) — candidate's total experience-entry years
 *                       (summed from candidate_experience, not a
 *                       self-reported number) / job's min_years_experience,
 *                       capped at 1 (more experience never over-scores).
 *   - Education fit     (10%) — binary: candidate's highest qualification
 *                       level meets the job's minimum, or it doesn't.
 *
 * Hard floor: missing even one must_have skill caps the overall score at 50,
 * regardless of how strong everything else is — a pile of nice-to-haves
 * can't quietly outweigh a genuinely missing core requirement.
 *
 * Called live while a candidate browses (so listings can sort by fit before
 * anyone applies) and once more at the moment of application, where the
 * result is snapshotted into applications.match_score /
 * applications.score_breakdown so it doesn't silently drift if either side
 * edits their data afterward — see App\Models\Application::recordSnapshot().
 */
class MatchScoreCalculator
{
    private const PROFICIENCY_ORDINAL = [
        'beginner' => 1,
        'intermediate' => 2,
        'expert' => 3,
    ];

    public const EDUCATION_ORDINAL = [
        'high_school' => 1,
        'diploma' => 2,
        'bachelor' => 3,
        'master' => 4,
        'phd' => 5,
    ];

    public const EDUCATION_LABELS = [
        'high_school' => 'High School',
        'diploma' => 'Diploma',
        'bachelor' => "Bachelor's Degree",
        'master' => "Master's Degree",
        'phd' => 'PhD / Doctorate',
    ];

    /**
     * @return array{
     *   overall:int, capped_missing_must_have:bool,
     *   skills_fit:float, experience_fit:float, education_fit:float,
     *   candidate_years_experience:float, job_min_years_experience:float,
     *   candidate_education_level:?string, job_min_education_level:?string, education_met:bool,
     *   skills:array
     * }
     */
    public static function compute(int $candidateId, int $jobId): array
    {
        $pdo = Database::connection();

        $jobStmt = $pdo->prepare('SELECT * FROM jobs WHERE id = ?');
        $jobStmt->execute([$jobId]);
        $job = $jobStmt->fetch();
        if (!$job) {
            throw new \InvalidArgumentException("Job #{$jobId} not found");
        }

        $reqStmt = $pdo->prepare(
            "SELECT js.skill_id, js.importance, js.min_proficiency, s.name AS skill_name
             FROM job_skills js
             JOIN skills s ON s.id = js.skill_id
             WHERE js.job_id = ?
             ORDER BY (js.importance = 'must_have') DESC, s.name ASC"
        );
        $reqStmt->execute([$jobId]);
        $requirements = $reqStmt->fetchAll();

        $candStmt = $pdo->prepare('SELECT skill_id, proficiency FROM candidate_skills WHERE candidate_id = ?');
        $candStmt->execute([$candidateId]);
        $candidateSkills = [];
        foreach ($candStmt->fetchAll() as $row) {
            $candidateSkills[(int) $row['skill_id']] = $row['proficiency'];
        }

        $breakdown = [];
        $totalWeight = 0.0;
        $earnedWeight = 0.0;
        $missingMustHave = false;

        foreach ($requirements as $req) {
            $weight = $req['importance'] === 'must_have' ? 3 : 1;
            $totalWeight += $weight;
            $minOrd = self::PROFICIENCY_ORDINAL[$req['min_proficiency']];
            $skillId = (int) $req['skill_id'];
            $candProf = $candidateSkills[$skillId] ?? null;

            if ($candProf === null) {
                $points = 0.0;
                $met = false;
                if ($req['importance'] === 'must_have') {
                    $missingMustHave = true;
                }
            } else {
                $candOrd = self::PROFICIENCY_ORDINAL[$candProf];
                $ratio = min($candOrd / $minOrd, 1.0);
                $points = $weight * $ratio;
                $met = $candOrd >= $minOrd;
            }
            $earnedWeight += $points;

            $breakdown[] = [
                'skill_id' => $skillId,
                'skill_name' => $req['skill_name'],
                'importance' => $req['importance'],
                'weight' => $weight,
                'min_proficiency' => $req['min_proficiency'],
                'candidate_proficiency' => $candProf,
                'met' => $met,
                'points_earned' => round($points, 2),
                'points_possible' => $weight,
            ];
        }

        $skillsFit = $totalWeight > 0 ? ($earnedWeight / $totalWeight) * 100 : 100.0;

        $candidateYears = self::candidateYearsExperience($candidateId);
        $minYears = (float) ($job['min_years_experience'] ?? 0);
        $experienceFit = $minYears > 0 ? min($candidateYears / $minYears, 1.0) * 100 : 100.0;

        $candidateEduOrdinal = self::candidateHighestEducationOrdinal($candidateId);
        $jobEduLevel = $job['min_education_level'];
        $jobEduOrdinal = $jobEduLevel ? self::EDUCATION_ORDINAL[$jobEduLevel] : null;
        $educationMet = $jobEduOrdinal === null || $candidateEduOrdinal >= $jobEduOrdinal;
        $educationFit = $educationMet ? 100.0 : 0.0;

        $rawOverall = $skillsFit * 0.70 + $experienceFit * 0.20 + $educationFit * 0.10;
        $overall = (int) round($rawOverall);
        $capped = false;
        if ($missingMustHave && $overall > 50) {
            $overall = 50;
            $capped = true;
        }

        return [
            'overall' => $overall,
            'capped_missing_must_have' => $capped,
            'skills_fit' => round($skillsFit, 1),
            'experience_fit' => round($experienceFit, 1),
            'education_fit' => round($educationFit, 1),
            'candidate_years_experience' => round($candidateYears, 1),
            'job_min_years_experience' => round($minYears, 1),
            'candidate_education_level' => self::ordinalToLevel($candidateEduOrdinal),
            'job_min_education_level' => $jobEduLevel,
            'education_met' => $educationMet,
            'skills' => $breakdown,
        ];
    }

    /** Sums durations of every experience entry — live, not a stored/self-reported number. */
    public static function candidateYearsExperience(int $candidateId): float
    {
        $stmt = Database::connection()->prepare(
            'SELECT start_date, end_date, is_current FROM candidate_experience WHERE candidate_id = ?'
        );
        $stmt->execute([$candidateId]);

        $totalDays = 0;
        foreach ($stmt->fetchAll() as $row) {
            $start = new \DateTimeImmutable($row['start_date']);
            $end = $row['is_current'] ? new \DateTimeImmutable('now') : new \DateTimeImmutable($row['end_date'] ?? 'now');
            if ($end < $start) {
                continue;
            }
            $totalDays += $start->diff($end)->days;
        }

        return $totalDays / 365.25;
    }

    public static function candidateHighestEducationOrdinal(int $candidateId): int
    {
        $stmt = Database::connection()->prepare('SELECT level FROM candidate_education WHERE candidate_id = ?');
        $stmt->execute([$candidateId]);

        $max = 0;
        foreach ($stmt->fetchAll() as $row) {
            $max = max($max, self::EDUCATION_ORDINAL[$row['level']] ?? 0);
        }
        return $max;
    }

    private static function ordinalToLevel(int $ordinal): ?string
    {
        return array_flip(self::EDUCATION_ORDINAL)[$ordinal] ?? null;
    }
}
