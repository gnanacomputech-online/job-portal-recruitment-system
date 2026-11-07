<?php use Core\Auth; use Core\Router; use Core\View; ?>
<div class="mb-6">
    <h1 class="font-display font-semibold text-2xl text-ink mb-1">Browse Jobs</h1>
    <p class="text-sm text-slateink">Naukri-style cards, ranked by fit when you're logged in as a candidate.</p>
</div>

<form method="get" action="<?= Router::url('/jobs') ?>" class="card-surface p-4 mb-6 grid sm:grid-cols-4 gap-3">
    <input type="text" name="keyword" placeholder="Title or company" class="field-input sm:col-span-2" value="<?= e($filters['keyword']) ?>">
    <input type="text" name="location" placeholder="Location" class="field-input" value="<?= e($filters['location']) ?>">
    <select name="job_type" class="field-input">
        <option value="">Any job type</option>
        <?php foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Contract'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= $filters['job_type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
    <div class="sm:col-span-4 flex items-center justify-between gap-3">
        <div class="flex gap-2">
            <button type="submit" class="btn-primary btn-sm">Search</button>
            <a href="<?= Router::url('/jobs') ?>" class="btn-secondary btn-sm">Clear</a>
        </div>
        <?php if (Auth::role() === 'candidate'): ?>
            <div class="flex gap-2 text-sm items-center">
                <span class="text-slateink">Sort:</span>
                <a href="<?= Router::url('/jobs?' . http_build_query(array_merge($filters, ['sort' => 'recent']))) ?>" class="<?= $sortBy === 'recent' ? 'text-indigo font-semibold' : 'text-slateink' ?>">Recent</a>
                <a href="<?= Router::url('/jobs?' . http_build_query(array_merge($filters, ['sort' => 'match']))) ?>" class="<?= $sortBy === 'match' ? 'text-indigo font-semibold' : 'text-slateink' ?>">Best match</a>
            </div>
        <?php endif; ?>
    </div>
</form>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($jobs as $job): ?>
        <?php View::renderBare('partials/job_card', ['job' => $job]); ?>
    <?php endforeach; ?>
    <?php if (empty($jobs)): ?>
        <div class="col-span-full skeleton-empty p-10 text-center">No jobs match your search.</div>
    <?php endif; ?>
</div>
