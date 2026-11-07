<?php use Core\Csrf; use Core\Router; use Core\View; $errors = View::errors(); $isEdit = $job !== null; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6"><?= $isEdit ? 'Edit posting' : 'Post a new job' ?></h1>

<form method="post" action="<?= $isEdit ? Router::url('/recruiter/jobs/' . $job['id']) : Router::url('/recruiter/jobs') ?>" class="grid lg:grid-cols-3 gap-6">
    <?= Csrf::field() ?>
    <div class="lg:col-span-2 space-y-4">
        <div class="card-surface p-6 space-y-4">
            <div>
                <label class="field-label">Job title</label>
                <input type="text" name="title" required class="field-input" value="<?= e($job['title'] ?? '') ?>">
                <?php if (!empty($errors['title'])): ?><p class="field-error"><?= e($errors['title']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="field-label">Description</label>
                <textarea name="description" rows="6" required class="field-input"><?= e($job['description'] ?? '') ?></textarea>
                <?php if (!empty($errors['description'])): ?><p class="field-error"><?= e($errors['description']) ?></p><?php endif; ?>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Location</label>
                    <input type="text" name="location" required class="field-input" value="<?= e($job['location'] ?? '') ?>">
                </div>
                <div>
                    <label class="field-label">Job type</label>
                    <select name="job_type" class="field-input">
                        <?php foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Contract'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($job['job_type'] ?? 'full_time') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="field-label">Minimum years experience</label>
                    <input type="number" step="0.5" min="0" name="min_years_experience" class="field-input" value="<?= e((string) ($job['min_years_experience'] ?? '0')) ?>">
                </div>
                <div>
                    <label class="field-label">Minimum education</label>
                    <select name="min_education_level" class="field-input">
                        <option value="">No minimum</option>
                        <?php foreach (\Core\MatchScoreCalculator::EDUCATION_LABELS as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($job['min_education_level'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-surface p-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-display font-semibold text-base text-ink">Required skills</h2>
            </div>
            <div id="req-rows" class="space-y-2">
                <?php $i = 0; foreach ($requirements as $req): ?>
                    <div class="req-row grid grid-cols-12 gap-2 items-center">
                        <select name="skill_id[]" class="field-input col-span-5">
                            <?php foreach ($grouped as $category => $skills): ?>
                                <optgroup label="<?= e($category) ?>">
                                    <?php foreach ($skills as $skill): ?>
                                        <option value="<?= $skill['id'] ?>" <?= (int) $req['skill_id'] === (int) $skill['id'] ? 'selected' : '' ?>><?= e($skill['name']) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <select name="importance[]" class="field-input col-span-3">
                            <option value="must_have" <?= $req['importance'] === 'must_have' ? 'selected' : '' ?>>Must-have</option>
                            <option value="nice_to_have" <?= $req['importance'] === 'nice_to_have' ? 'selected' : '' ?>>Nice-to-have</option>
                        </select>
                        <select name="min_proficiency[]" class="field-input col-span-3">
                            <option value="beginner" <?= $req['min_proficiency'] === 'beginner' ? 'selected' : '' ?>>Beginner+</option>
                            <option value="intermediate" <?= $req['min_proficiency'] === 'intermediate' ? 'selected' : '' ?>>Intermediate+</option>
                            <option value="expert" <?= $req['min_proficiency'] === 'expert' ? 'selected' : '' ?>>Expert</option>
                        </select>
                        <button type="button" onclick="this.closest('.req-row').remove()" class="col-span-1 text-rose">&times;</button>
                    </div>
                <?php $i++; endforeach; ?>
            </div>
            <button type="button" id="add-req-row" class="btn-secondary btn-sm mt-3">+ Add skill requirement</button>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card-surface p-6 space-y-3">
            <button type="submit" name="action" value="publish" class="btn-primary w-full">Publish job</button>
            <button type="submit" name="action" value="draft" class="btn-secondary w-full">Save as draft</button>
        </div>
        <div class="card-surface p-6">
            <h3 class="font-display font-semibold text-sm text-ink mb-2">Need a skill that's not listed?</h3>
            <form method="post" action="<?= Router::url('/skill-requests') ?>" class="space-y-2">
                <?= Csrf::field() ?>
                <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI']) ?>">
                <input type="text" name="name" placeholder="Skill name" class="field-input">
                <input type="text" name="category" placeholder="Category" class="field-input">
                <button type="submit" class="btn-secondary btn-sm w-full">Request new skill</button>
            </form>
        </div>
    </div>
</form>

<template id="req-row-template">
    <div class="req-row grid grid-cols-12 gap-2 items-center">
        <select name="skill_id[]" class="field-input col-span-5">
            <?php foreach ($grouped as $category => $skills): ?>
                <optgroup label="<?= e($category) ?>">
                    <?php foreach ($skills as $skill): ?><option value="<?= $skill['id'] ?>"><?= e($skill['name']) ?></option><?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        <select name="importance[]" class="field-input col-span-3">
            <option value="must_have">Must-have</option><option value="nice_to_have">Nice-to-have</option>
        </select>
        <select name="min_proficiency[]" class="field-input col-span-3">
            <option value="beginner">Beginner+</option><option value="intermediate">Intermediate+</option><option value="expert">Expert</option>
        </select>
        <button type="button" onclick="this.closest('.req-row').remove()" class="col-span-1 text-rose">&times;</button>
    </div>
</template>
<script>
document.getElementById('add-req-row').addEventListener('click', function () {
    const tpl = document.getElementById('req-row-template');
    document.getElementById('req-rows').appendChild(tpl.content.cloneNode(true));
});
</script>
