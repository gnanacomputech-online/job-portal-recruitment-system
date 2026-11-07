<?php use Core\Csrf; use Core\Router; use Core\View; ?>
<a href="<?= Router::url('/recruiter/jobs/' . $application['job_id'] . '/applicants') ?>" class="text-sm text-indigo hover:underline">&larr; Back to applicants</a>

<div class="card-surface p-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display font-semibold text-xl text-ink"><?= e($application['candidate_name']) ?></h1>
            <p class="text-sm text-slateink"><?= e($application['candidate_email']) ?> &middot; Applied for <?= e($application['job_title']) ?></p>
            <?php if (($application['candidate_status'] ?? 'approved') !== 'approved'): ?>
                <span class="status-badge status-suspended capitalize mt-2">Account <?= e($application['candidate_status']) ?></span>
            <?php endif; ?>
            <?php if (!empty($application['score_breakdown'])): ?>
                <?php $resumePath = \App\Models\CandidateProfile::find($application['candidate_id'])['resume_path'] ?? null; ?>
                <?php if ($resumePath): ?>
                    <a href="<?= Router::url('/uploads/' . e($resumePath)) ?>" target="_blank" class="text-indigo hover:underline text-sm">Download resume</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <span class="status-badge status-<?= e($application['status']) ?> capitalize text-sm"><?= e($application['status']) ?></span>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <?php View::renderBare('partials/match_breakdown', ['score' => $application['score_breakdown'], 'snapshotNote' => 'snapshot at time of application']); ?>
    </div>
    <div class="space-y-6">
        <?php if ($application['job_moderation_status'] !== 'active'): ?>
            <div class="card-surface p-6 text-sm text-slateink">
                This job posting has been <?= e($application['job_moderation_status']) ?> by an admin — applicant status can no longer be changed.
            </div>
        <?php elseif (($application['candidate_status'] ?? 'approved') !== 'approved'): ?>
            <div class="card-surface p-6 text-sm text-slateink">
                This candidate's account is <?= e($application['candidate_status']) ?> — their application can no longer be advanced.
            </div>
        <?php elseif (!empty($transitions)): ?>
            <div class="card-surface p-6">
                <h2 class="font-display font-semibold text-base text-ink mb-3">Advance status</h2>
                <form method="post" action="<?= Router::url('/recruiter/applications/' . $application['id'] . '/status') ?>" class="space-y-3">
                    <?= Csrf::field() ?>
                    <select name="status" required class="field-input">
                        <option value="">Choose new status&hellip;</option>
                        <?php foreach ($transitions as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?>
                    </select>
                    <textarea name="note" rows="2" placeholder="Optional note to candidate" class="field-input"></textarea>
                    <button type="submit" class="btn-primary w-full">Update status</button>
                </form>
            </div>
        <?php endif; ?>
        <div class="card-surface p-6">
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
</div>
