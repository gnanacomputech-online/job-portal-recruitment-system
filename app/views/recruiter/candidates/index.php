<?php use Core\Router; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-2">Candidate Search</h1>
<p class="text-sm text-slateink mb-6">Proactively browse the Open-to-Work candidate pool, filter by skill, and preview match score against any of your postings.</p>

<form method="get" action="<?= Router::url('/recruiter/candidates') ?>" class="card-surface p-4 mb-6 flex flex-wrap gap-3">
    <select name="skill_id" class="field-input flex-1 min-w-[200px]">
        <option value="">Any skill</option>
        <?php foreach ($skills as $skill): ?>
            <option value="<?= $skill['id'] ?>" <?= $selectedSkillId === (int) $skill['id'] ? 'selected' : '' ?>><?= e($skill['name']) ?> (<?= e($skill['category']) ?>)</option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-primary">Filter</button>
    <a href="<?= Router::url('/recruiter/candidates') ?>" class="btn-secondary">Clear</a>
</form>

<div class="grid md:grid-cols-2 gap-4">
    <?php foreach ($candidates as $c): ?>
        <div class="card-surface p-5">
            <p class="font-display font-semibold text-ink"><?= e($c['name']) ?></p>
            <p class="text-sm text-slateink"><?= e($c['headline'] ?? '') ?> <?= $c['location'] ? '&middot; ' . e($c['location']) : '' ?></p>
            <div class="flex items-center justify-between mt-4">
                <span class="chip chip-met">Open to Work</span>
                <a href="<?= Router::url('/recruiter/candidates/' . $c['id']) ?>" class="btn-secondary btn-sm">Preview match</a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($candidates)): ?>
        <div class="col-span-full skeleton-empty p-10 text-center">No open-to-work candidates match this filter.</div>
    <?php endif; ?>
</div>
