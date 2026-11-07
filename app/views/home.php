<?php use Core\Router; use Core\View; ?>
<div class="-mx-4 -mt-8 mb-10 tf-hero px-4 py-20">
    <div class="max-w-4xl mx-auto text-center">
        <div class="section-eyebrow text-white/70 mb-3">Explainable skills-match scoring</div>
        <h1 class="font-display font-semibold text-4xl sm:text-5xl mb-5">Hiring, matched by evidence &mdash; not guesswork.</h1>
        <p class="text-white/70 text-lg mb-8 max-w-2xl mx-auto">TalentFit scores every candidate against every job on real skills, experience, and education &mdash; and shows its work, skill by skill, instead of a bare percentage.</p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="<?= Router::url('/register/candidate') ?>" class="btn-primary">I'm looking for a job</a>
            <a href="<?= Router::url('/register/recruiter') ?>" class="btn-secondary !bg-transparent !text-white !border-white/30">I'm hiring</a>
        </div>
    </div>
</div>

<div class="flex items-center justify-between mb-5">
    <h2 class="font-display font-semibold text-2xl text-ink">Recently posted</h2>
    <a href="<?= Router::url('/jobs') ?>" class="text-sm font-medium text-indigo hover:underline">Browse all jobs &rarr;</a>
</div>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($jobs as $job): ?>
        <?php View::renderBare('partials/job_card', ['job' => $job]); ?>
    <?php endforeach; ?>
    <?php if (empty($jobs)): ?>
        <p class="text-slateink col-span-full">No open positions right now &mdash; check back soon.</p>
    <?php endif; ?>
</div>
