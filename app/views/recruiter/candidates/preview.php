<?php use Core\Router; use Core\View; ?>
<a href="<?= Router::url('/recruiter/candidates') ?>" class="text-sm text-indigo hover:underline">&larr; Candidate search</a>

<div class="card-surface p-6 my-6">
    <h1 class="font-display font-semibold text-xl text-ink"><?= e($candidate['name']) ?></h1>
    <p class="text-sm text-slateink"><?= e($profile['headline'] ?? '') ?> <?= !empty($profile['location']) ? '&middot; ' . e($profile['location']) : '' ?></p>
    <?php if (!empty($profile['resume_path'])): ?>
        <a href="<?= Router::url('/uploads/' . e($profile['resume_path'])) ?>" target="_blank" class="text-indigo hover:underline text-sm">Download resume</a>
    <?php endif; ?>

    <div class="grid sm:grid-cols-2 gap-6 mt-5">
        <div>
            <p class="section-eyebrow mb-2">Skills</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($candidateSkills as $cs): ?>
                    <span class="chip" style="background:var(--indigo-50);color:var(--indigo);"><?= e($cs['skill_name']) ?> &middot; <?= ucfirst($cs['proficiency']) ?></span>
                <?php endforeach; ?>
                <?php if (empty($candidateSkills)): ?><span class="text-slateink text-sm">No skills listed.</span><?php endif; ?>
            </div>
        </div>
        <div>
            <p class="section-eyebrow mb-2">Experience</p>
            <?php foreach ($experience as $exp): ?>
                <p class="text-sm text-ink"><?= e($exp['title']) ?> @ <?= e($exp['company']) ?> <span class="text-slateink">(<?= format_date($exp['start_date'], 'Y') ?>&ndash;<?= $exp['is_current'] ? 'present' : format_date($exp['end_date'], 'Y') ?>)</span></p>
            <?php endforeach; ?>
            <?php if (empty($experience)): ?><span class="text-slateink text-sm">No experience listed.</span><?php endif; ?>
        </div>
    </div>
</div>

<div class="card-surface p-6 mb-6">
    <h2 class="font-display font-semibold text-base text-ink mb-3">Preview match against one of your postings</h2>
    <form method="get" action="<?= Router::url('/recruiter/candidates/' . $candidate['id']) ?>" class="flex gap-3 flex-wrap">
        <select name="job_id" class="field-input flex-1 min-w-[220px]">
            <option value="">Choose a job posting&hellip;</option>
            <?php foreach ($jobs as $j): ?>
                <option value="<?= $j['id'] ?>" <?= ($selectedJob['id'] ?? null) == $j['id'] ? 'selected' : '' ?>><?= e($j['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary">Preview</button>
    </form>
</div>

<?php if ($score): ?>
    <?php View::renderBare('partials/match_breakdown', ['score' => $score]); ?>
<?php endif; ?>
