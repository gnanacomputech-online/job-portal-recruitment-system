<?php use Core\Router; ?>
<a href="<?= Router::url('/recruiter/jobs') ?>" class="text-sm text-indigo hover:underline">&larr; My postings</a>
<div class="flex items-center justify-between mt-3 mb-6 flex-wrap gap-3">
    <h1 class="font-display font-semibold text-2xl text-ink">Applicants &mdash; <?= e($job['title']) ?></h1>
    <span class="text-sm text-slateink"><?= count($applicants) ?> applicant(s), ranked by match score</span>
</div>

<div class="space-y-3">
    <?php foreach ($applicants as $a): ?>
        <a href="<?= Router::url('/recruiter/applications/' . $a['id']) ?>" class="card-surface p-5 flex items-center gap-5 hover:border-indigo transition">
            <div class="fit-ring" style="--pct: <?= $a['match_score'] ?>; --ring-color: var(--<?= $a['match_score'] >= 80 ? 'mint' : ($a['match_score'] >= 60 ? 'indigo' : ($a['match_score'] >= 40 ? 'amber' : 'rose')) ?>);">
                <span><?= $a['match_score'] ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-display font-semibold text-ink">
                    <?= e($a['candidate_name']) ?>
                    <?php if (($a['candidate_status'] ?? 'approved') !== 'approved'): ?>
                        <span class="status-badge status-suspended capitalize ml-1">Account <?= e($a['candidate_status']) ?></span>
                    <?php endif; ?>
                </p>
                <p class="text-sm text-slateink"><?= e($a['headline'] ?? '') ?> <?= $a['location'] ? '&middot; ' . e($a['location']) : '' ?></p>
                <p class="text-xs text-slateink mt-1">Applied <?= time_ago($a['applied_at']) ?></p>
            </div>
            <span class="status-badge status-<?= e($a['status']) ?> capitalize"><?= e($a['status']) ?></span>
        </a>
    <?php endforeach; ?>
    <?php if (empty($applicants)): ?>
        <div class="skeleton-empty p-10 text-center">No applicants yet for this posting.</div>
    <?php endif; ?>
</div>
