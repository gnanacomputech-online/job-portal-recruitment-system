<?php use Core\Csrf; use Core\Router; use Core\View; ?>
<a href="<?= Router::url('/candidate/applications') ?>" class="text-sm text-indigo hover:underline">&larr; My applications</a>

<div class="card-surface p-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display font-semibold text-xl text-ink"><?= e($application['job_title']) ?></h1>
            <p class="text-sm text-slateink"><?= e($application['company_name']) ?> &middot; Applied <?= format_date($application['applied_at']) ?></p>
        </div>
        <div class="flex items-center gap-3">
            <span class="status-badge status-<?= e($application['status']) ?> capitalize text-sm"><?= e($application['status']) ?></span>
            <?php if (in_array($application['status'], ['applied', 'shortlisted'], true)): ?>
                <form method="post" action="<?= Router::url('/candidate/applications/' . $application['id'] . '/withdraw') ?>" onsubmit="return confirm('Withdraw this application?');">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn-danger btn-sm">Withdraw</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <?php View::renderBare('partials/match_breakdown', ['score' => $application['score_breakdown'], 'snapshotNote' => 'snapshot at time of application']); ?>
    </div>
    <div class="card-surface p-6 h-fit">
        <h2 class="font-display font-semibold text-base text-ink mb-4">Status timeline</h2>
        <div class="tf-timeline">
            <?php foreach ($history as $h): ?>
                <div class="tf-timeline-item">
                    <p class="text-sm font-medium text-ink capitalize"><?= e($h['to_status']) ?></p>
                    <p class="text-xs text-slateink"><?= format_date($h['changed_at'], 'd M Y, H:i') ?></p>
                    <?php if ($h['note']): ?><p class="text-xs text-ink mt-1"><?= e($h['note']) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
