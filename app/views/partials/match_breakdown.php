<?php
/**
 * The signature screen (FEATURES.md): explainable match score, never a bare
 * percentage. Expects $score = Core\MatchScoreCalculator::compute() result
 * (or the decoded score_breakdown of a snapshotted application).
 * Optional $snapshotNote to label it as a point-in-time snapshot.
 */
use Core\MatchScoreCalculator;
$overall = (int) $score['overall'];
$tier = $overall >= 80 ? 'mint' : ($overall >= 60 ? 'indigo' : ($overall >= 40 ? 'amber' : 'rose'));
?>
<div class="card-surface p-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-6 pb-6 mb-6 border-b hairline">
        <div class="fit-ring" style="--pct: <?= $overall ?>; --ring-color: var(--<?= $tier ?>); --ring-size: 96px;">
            <span style="font-size:1.4rem;"><?= $overall ?></span>
        </div>
        <div class="flex-1">
            <div class="section-eyebrow mb-1">Match Score<?= isset($snapshotNote) ? ' &middot; ' . e($snapshotNote) : '' ?></div>
            <h3 class="font-display font-semibold text-xl text-ink"><?= $overall ?>/100 fit for this role</h3>
            <?php if (!empty($score['capped_missing_must_have'])): ?>
                <p class="mt-2 text-sm chip chip-missing">Capped at 50 &mdash; missing a must-have skill</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="space-y-3 mb-6">
        <div class="fit-bar-row">
            <span class="text-sm font-medium text-ink">Skills fit <span class="text-slateink font-normal">(70%)</span></span>
            <div class="fit-bar-track"><div class="fit-bar-fill" style="width: <?= $score['skills_fit'] ?>%; background: var(--indigo);"></div></div>
            <span class="text-sm font-mono text-right"><?= $score['skills_fit'] ?>%</span>
        </div>
        <div class="fit-bar-row">
            <span class="text-sm font-medium text-ink">Experience fit <span class="text-slateink font-normal">(20%)</span></span>
            <div class="fit-bar-track"><div class="fit-bar-fill" style="width: <?= $score['experience_fit'] ?>%; background: var(--mint);"></div></div>
            <span class="text-sm font-mono text-right"><?= $score['experience_fit'] ?>%</span>
        </div>
        <div class="fit-bar-row">
            <span class="text-sm font-medium text-ink">Education fit <span class="text-slateink font-normal">(10%)</span></span>
            <div class="fit-bar-track"><div class="fit-bar-fill" style="width: <?= $score['education_fit'] ?>%; background: var(--amber);"></div></div>
            <span class="text-sm font-mono text-right"><?= $score['education_fit'] ?>%</span>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-3 mb-6 text-sm">
        <div class="rounded-lg border hairline p-3">
            <div class="text-slateink text-xs mb-1">Experience</div>
            <div class="font-mono"><?= $score['candidate_years_experience'] ?> yrs <span class="text-slateink">vs</span> <?= $score['job_min_years_experience'] ?> yrs required</div>
        </div>
        <div class="rounded-lg border hairline p-3">
            <div class="text-slateink text-xs mb-1">Education</div>
            <div>
                <?= e($score['candidate_education_level'] ? MatchScoreCalculator::EDUCATION_LABELS[$score['candidate_education_level']] : 'Not specified') ?>
                <span class="text-slateink">vs</span>
                <?= e($score['job_min_education_level'] ? MatchScoreCalculator::EDUCATION_LABELS[$score['job_min_education_level']] : 'No minimum') ?>
                <?= $score['education_met'] ? '<span class="chip chip-met ml-1">Met</span>' : '<span class="chip chip-missing ml-1">Not met</span>' ?>
            </div>
        </div>
    </div>

    <div class="section-eyebrow mb-2">Per-skill breakdown</div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slateink border-b hairline">
                    <th class="py-2 pr-3 font-medium">Required skill</th>
                    <th class="py-2 pr-3 font-medium">Importance</th>
                    <th class="py-2 pr-3 font-medium">Required level</th>
                    <th class="py-2 pr-3 font-medium">Candidate's level</th>
                    <th class="py-2 pr-3 font-medium">Status</th>
                    <th class="py-2 pr-3 font-medium text-right">Points</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($score['skills'] as $row): ?>
                <?php
                    $rowClass = $row['met'] ? 'skill-row-met' : ($row['candidate_proficiency'] ? 'skill-row-partial' : 'skill-row-missing');
                    $chipClass = $row['met'] ? 'chip-met' : ($row['candidate_proficiency'] ? 'chip-partial' : 'chip-missing');
                    $chipLabel = $row['met'] ? 'Met' : ($row['candidate_proficiency'] ? 'Below level' : 'Missing');
                ?>
                <tr class="border-b hairline <?= $rowClass ?>">
                    <td class="py-2 pr-3 font-medium text-ink"><?= e($row['skill_name']) ?></td>
                    <td class="py-2 pr-3"><?= $row['importance'] === 'must_have' ? '<span class="chip chip-missing">Must-have</span>' : '<span class="chip" style="background:var(--indigo-50);color:var(--indigo);">Nice-to-have</span>' ?></td>
                    <td class="py-2 pr-3 capitalize"><?= e($row['min_proficiency']) ?></td>
                    <td class="py-2 pr-3 capitalize"><?= e($row['candidate_proficiency'] ?? '—') ?></td>
                    <td class="py-2 pr-3"><span class="chip <?= $chipClass ?>"><?= $chipLabel ?></span></td>
                    <td class="py-2 pr-3 text-right font-mono"><?= $row['points_earned'] ?> / <?= $row['points_possible'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($score['skills'])): ?>
                <tr><td colspan="6" class="py-4 text-center text-slateink">This job has no specific skill requirements listed.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
