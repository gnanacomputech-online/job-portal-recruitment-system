<?php use Core\View; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6">Saved Jobs</h1>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($jobs as $job): ?>
        <?php $job['is_saved'] = true; ?>
        <?php View::renderBare('partials/job_card', ['job' => $job]); ?>
    <?php endforeach; ?>
    <?php if (empty($jobs)): ?>
        <div class="col-span-full skeleton-empty p-10 text-center">You haven't saved any jobs yet.</div>
    <?php endif; ?>
</div>
