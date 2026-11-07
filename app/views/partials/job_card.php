<?php
/**
 * Naukri-style job card (FEATURES.md): title, company, location, experience
 * required, posted-X-days-ago, match-score badge, Save button.
 * Expects $job (with optional match_score / is_saved keys) in scope.
 */
use Core\Auth;
use Core\Router;
$score = $job['match_score'] ?? null;
$tier = $score === null ? '' : ($score >= 80 ? 'tier-great' : ($score >= 60 ? 'tier-good' : ($score >= 40 ? 'tier-partial' : 'tier-low')));
$isClosed = ($job['status'] ?? 'open') !== 'open'
    || ($job['moderation_status'] ?? 'active') !== 'active'
    || ($job['recruiter_status'] ?? 'approved') !== 'approved';
?>
<div class="job-card <?= $tier ?> p-5">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="<?= Router::url('/jobs/' . $job['id']) ?>" class="font-display font-semibold text-lg text-ink hover:text-indigo truncate"><?= e($job['title']) ?></a>
                <?php if ($isClosed): ?><span class="status-badge status-closed capitalize text-xs">No longer available</span><?php endif; ?>
            </div>
            <a href="<?= Router::url('/companies/' . $job['recruiter_id']) ?>" class="text-sm text-slateink hover:text-indigo"><?= e($job['company_name']) ?></a>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-slateink">
                <span>&#128205; <?= e($job['location']) ?></span>
                <span>&#128188; <?= number_format((float) $job['min_years_experience'], 1) ?>+ yrs</span>
                <span class="capitalize"><?= e(str_replace('_', ' ', $job['job_type'])) ?></span>
                <span><?= time_ago($job['created_at']) ?></span>
            </div>
        </div>
        <?php if ($score !== null): ?>
            <div class="fit-ring" style="--pct: <?= $score ?>; --ring-color: var(--<?= $score >= 80 ? 'mint' : ($score >= 60 ? 'indigo' : ($score >= 40 ? 'amber' : 'rose')) ?>);">
                <span><?= $score ?></span>
            </div>
        <?php endif; ?>
    </div>
    <div class="flex items-center justify-between mt-4 pt-4 border-t hairline">
        <a href="<?= Router::url('/jobs/' . $job['id']) ?>" class="btn-secondary btn-sm">View details</a>
        <?php if (Auth::role() === 'candidate'): ?>
            <form method="post" action="<?= Router::url('/jobs/' . $job['id'] . (($job['is_saved'] ?? false) ? '/unsave' : '/save')) ?>">
                <?= \Core\Csrf::field() ?>
                <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? '/jobs') ?>">
                <button type="submit" class="btn-secondary btn-sm">
                    <?= ($job['is_saved'] ?? false) ? '&#9733; Saved' : '&#9734; Save' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
