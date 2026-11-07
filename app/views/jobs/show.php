<?php use Core\Auth; use Core\Csrf; use Core\Router; use Core\View; ?>
<div class="mb-6">
    <a href="<?= Router::url('/jobs') ?>" class="text-sm text-indigo hover:underline">&larr; Back to jobs</a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card-surface p-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="font-display font-semibold text-2xl text-ink"><?= e($job['title']) ?></h1>
                    <a href="<?= Router::url('/companies/' . $job['recruiter_id']) ?>" class="text-indigo hover:underline"><?= e($job['company_name']) ?></a>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-sm text-slateink">
                        <span>&#128205; <?= e($job['location']) ?></span>
                        <span class="capitalize"><?= e(str_replace('_', ' ', $job['job_type'])) ?></span>
                        <span>&#128188; <?= number_format((float) $job['min_years_experience'], 1) ?>+ yrs</span>
                        <span><?= time_ago($job['created_at']) ?></span>
                        <span class="status-badge status-<?= e($job['status']) ?> capitalize"><?= e($job['status']) ?></span>
                    </div>
                </div>
            </div>

            <div class="mt-6 prose-sm whitespace-pre-line text-ink"><?= nl2br(e($job['description'])) ?></div>

            <div class="mt-6 pt-6 border-t hairline">
                <h2 class="font-display font-semibold text-base text-ink mb-3">Required skills</h2>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($requirements as $req): ?>
                        <span class="chip <?= $req['importance'] === 'must_have' ? 'chip-missing' : 'chip-partial' ?>">
                            <?= e($req['skill_name']) ?> &middot; <?= e(ucfirst($req['min_proficiency'])) ?>+
                            <?= $req['importance'] === 'must_have' ? '(must-have)' : '(nice-to-have)' ?>
                        </span>
                    <?php endforeach; ?>
                    <?php if (empty($requirements)): ?><span class="text-slateink text-sm">No specific skill requirements listed.</span><?php endif; ?>
                </div>
                <?php if ($job['min_education_level']): ?>
                    <p class="text-sm text-slateink mt-3">Minimum education: <?= e(\Core\MatchScoreCalculator::EDUCATION_LABELS[$job['min_education_level']]) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($score): ?>
            <?php View::renderBare('partials/match_breakdown', ['score' => $score]); ?>
        <?php endif; ?>
    </div>

    <div class="space-y-4">
        <div class="card-surface p-6 text-center">
            <?php if (Auth::role() === 'candidate'): ?>
                <?php if ($alreadyApplied): ?>
                    <p class="chip chip-met inline-flex mb-3">Already applied</p>
                    <a href="<?= Router::url('/candidate/applications') ?>" class="btn-secondary w-full">View my application</a>
                <?php elseif ($job['status'] !== 'open'): ?>
                    <p class="text-slateink text-sm">This job is no longer accepting applications.</p>
                <?php else: ?>
                    <form method="post" action="<?= Router::url('/jobs/' . $job['id'] . '/apply') ?>" class="mb-3">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn-primary w-full">Apply now</button>
                    </form>
                <?php endif; ?>
                <form method="post" action="<?= Router::url('/jobs/' . $job['id'] . ($isSaved ? '/unsave' : '/save')) ?>">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="redirect_to" value="<?= e('/jobs/' . $job['id']) ?>">
                    <button type="submit" class="btn-secondary w-full"><?= $isSaved ? '&#9733; Saved' : '&#9734; Save job' ?></button>
                </form>
            <?php elseif (!Auth::check()): ?>
                <p class="text-sm text-slateink mb-3">Log in as a candidate to see your match score and apply.</p>
                <a href="<?= Router::url('/login') ?>" class="btn-primary w-full">Log in</a>
            <?php else: ?>
                <p class="text-sm text-slateink">Only candidates can apply to jobs.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
