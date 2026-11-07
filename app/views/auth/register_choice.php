<?php use Core\Router; ?>
<div class="max-w-2xl mx-auto mt-6">
    <h1 class="font-display font-semibold text-2xl text-ink mb-6 text-center">Join TalentFit as&hellip;</h1>
    <div class="grid sm:grid-cols-2 gap-5">
        <a href="<?= Router::url('/register/candidate') ?>" class="card-surface p-8 text-center hover:border-indigo transition">
            <div class="text-3xl mb-3">&#128188;</div>
            <h2 class="font-display font-semibold text-lg text-ink mb-1">A Candidate</h2>
            <p class="text-sm text-slateink">Build a profile, browse jobs, and see your match score for every posting.</p>
        </a>
        <a href="<?= Router::url('/register/recruiter') ?>" class="card-surface p-8 text-center hover:border-indigo transition">
            <div class="text-3xl mb-3">&#127970;</div>
            <h2 class="font-display font-semibold text-lg text-ink mb-1">A Recruiter</h2>
            <p class="text-sm text-slateink">Post jobs and review applicants ranked by explainable fit. Requires admin approval.</p>
        </a>
    </div>
    <p class="text-center text-sm text-slateink mt-6">Already have an account? <a href="<?= Router::url('/login') ?>" class="text-indigo hover:underline">Log in</a></p>
</div>
