<?php use Core\Csrf; use Core\Router; use Core\View; ?>
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <h1 class="font-display font-semibold text-2xl text-ink">My Profile</h1>
    <div class="flex items-center gap-3">
        <span class="text-sm text-slateink">Completeness: <span class="font-mono text-indigo font-semibold"><?= $completeness['percent'] ?>%</span></span>
        <form method="post" action="<?= Router::url('/candidate/profile/open-to-work') ?>">
            <?= Csrf::field() ?>
            <button type="submit" class="chip <?= !empty($profile['open_to_work']) ? 'chip-met' : '' ?>" style="<?= empty($profile['open_to_work']) ? 'background:#EEF0F4;color:var(--slate);' : '' ?>">
                <?= !empty($profile['open_to_work']) ? '&#9679; Open to Work' : 'Not visible to recruiters' ?>
            </button>
        </form>
    </div>
</div>
<div class="meter-track mb-8"><div class="meter-fill" style="width: <?= $completeness['percent'] ?>%"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card-surface p-6">
            <h2 class="font-display font-semibold text-base text-ink mb-4">Basics</h2>
            <form method="post" action="<?= Router::url('/candidate/profile') ?>" class="space-y-4">
                <?= Csrf::field() ?>
                <div>
                    <label class="field-label">Headline</label>
                    <input type="text" name="headline" placeholder="e.g. Backend Engineer | Node.js & Cloud" class="field-input" value="<?= e($profile['headline'] ?? '') ?>">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Location</label>
                        <input type="text" name="location" class="field-input" value="<?= e($profile['location'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="field-label">Phone</label>
                        <input type="text" name="phone" class="field-input" value="<?= e($profile['phone'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn-primary">Save basics</button>
            </form>

            <div class="grid sm:grid-cols-2 gap-4 mt-6 pt-6 border-t hairline">
                <form method="post" action="<?= Router::url('/candidate/profile/resume') ?>" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <label class="field-label">Resume <span class="text-slateink font-normal">(PDF/DOC/DOCX, under 5MB)</span></label>
                    <div class="flex gap-2">
                        <input type="file" name="resume" accept=".pdf,.doc,.docx" class="field-input flex-1">
                        <button type="submit" class="btn-secondary">Upload</button>
                    </div>
                    <?php if (!empty($profile['resume_path'])): ?>
                        <a href="<?= Router::url('/uploads/' . e($profile['resume_path'])) ?>" target="_blank" class="text-xs text-indigo hover:underline">Current resume &rarr;</a>
                    <?php endif; ?>
                </form>
                <form method="post" action="<?= Router::url('/candidate/profile/photo') ?>" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <label class="field-label">Photo <span class="text-slateink font-normal">(JPG/PNG/WebP, under 2MB)</span></label>
                    <div class="flex gap-2">
                        <input type="file" name="photo" accept="image/*" class="field-input flex-1">
                        <button type="submit" class="btn-secondary">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-surface p-6">
            <h2 class="font-display font-semibold text-base text-ink mb-4">Work experience</h2>
            <div class="tf-timeline mb-5">
                <?php foreach ($experience as $exp): ?>
                    <div class="tf-timeline-item">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-ink"><?= e($exp['title']) ?> &middot; <?= e($exp['company']) ?></p>
                                <p class="text-xs text-slateink"><?= format_date($exp['start_date'], 'M Y') ?> &ndash; <?= $exp['is_current'] ? 'Present' : format_date($exp['end_date'], 'M Y') ?></p>
                                <?php if ($exp['description']): ?><p class="text-sm text-ink mt-1"><?= e($exp['description']) ?></p><?php endif; ?>
                            </div>
                            <form method="post" action="<?= Router::url('/candidate/profile/experience/' . $exp['id'] . '/delete') ?>"><?= Csrf::field() ?><button class="text-rose text-sm">Remove</button></form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($experience)): ?><p class="text-slateink text-sm">No experience added yet.</p><?php endif; ?>
            </div>
            <details class="border-t hairline pt-4">
                <summary class="cursor-pointer text-sm font-medium text-indigo">+ Add experience</summary>
                <form method="post" action="<?= Router::url('/candidate/profile/experience') ?>" class="space-y-3 mt-3">
                    <?= Csrf::field() ?>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <input type="text" name="title" placeholder="Title" required class="field-input">
                        <input type="text" name="company" placeholder="Company" required class="field-input">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3 items-center">
                        <div>
                            <label class="field-label">Start date</label>
                            <input type="date" name="start_date" required class="field-input">
                        </div>
                        <div>
                            <label class="field-label">End date</label>
                            <input type="date" name="end_date" class="field-input">
                        </div>
                    </div>
                    <label class="text-sm flex items-center gap-2"><input type="checkbox" name="is_current" value="1"> I currently work here</label>
                    <textarea name="description" placeholder="What did you do?" rows="2" class="field-input"></textarea>
                    <button type="submit" class="btn-secondary btn-sm">Add experience</button>
                </form>
            </details>
        </div>

        <div class="card-surface p-6">
            <h2 class="font-display font-semibold text-base text-ink mb-4">Education</h2>
            <div class="tf-timeline mb-5">
                <?php foreach ($education as $edu): ?>
                    <div class="tf-timeline-item">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-ink"><?= e($edu['degree']) ?><?= $edu['field'] ? ', ' . e($edu['field']) : '' ?></p>
                                <p class="text-xs text-slateink"><?= e($edu['institution']) ?> &middot; <?= e($edu['year_completed'] ?? '') ?></p>
                            </div>
                            <form method="post" action="<?= Router::url('/candidate/profile/education/' . $edu['id'] . '/delete') ?>"><?= Csrf::field() ?><button class="text-rose text-sm">Remove</button></form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($education)): ?><p class="text-slateink text-sm">No education added yet.</p><?php endif; ?>
            </div>
            <details class="border-t hairline pt-4">
                <summary class="cursor-pointer text-sm font-medium text-indigo">+ Add education</summary>
                <form method="post" action="<?= Router::url('/candidate/profile/education') ?>" class="space-y-3 mt-3">
                    <?= Csrf::field() ?>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <input type="text" name="degree" placeholder="Degree, e.g. B.Tech" required class="field-input">
                        <input type="text" name="institution" placeholder="Institution" required class="field-input">
                    </div>
                    <div class="grid sm:grid-cols-3 gap-3">
                        <input type="text" name="field" placeholder="Field of study" class="field-input">
                        <select name="level" required class="field-input">
                            <?php foreach (\Core\MatchScoreCalculator::EDUCATION_LABELS as $val => $label): ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="year_completed" placeholder="Year" min="1980" max="2035" class="field-input">
                    </div>
                    <button type="submit" class="btn-secondary btn-sm">Add education</button>
                </form>
            </details>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card-surface p-6">
            <h2 class="font-display font-semibold text-base text-ink mb-4">Skills</h2>
            <div class="flex flex-wrap gap-2 mb-4">
                <?php foreach ($candidateSkills as $cs): ?>
                    <form method="post" action="<?= Router::url('/candidate/profile/skills/' . $cs['skill_id'] . '/delete') ?>" class="inline-flex">
                        <?= Csrf::field() ?>
                        <button type="submit" class="chip" style="background:var(--indigo-50);color:var(--indigo);" title="Click to remove"><?= e($cs['skill_name']) ?> &middot; <?= ucfirst($cs['proficiency']) ?> &times;</button>
                    </form>
                <?php endforeach; ?>
                <?php if (empty($candidateSkills)): ?><p class="text-slateink text-sm">No skills added yet.</p><?php endif; ?>
            </div>
            <form method="post" action="<?= Router::url('/candidate/profile/skills') ?>" class="space-y-2">
                <?= Csrf::field() ?>
                <select name="skill_id" required class="field-input">
                    <option value="">Choose a skill&hellip;</option>
                    <?php foreach ($grouped as $category => $skills): ?>
                        <optgroup label="<?= e($category) ?>">
                            <?php foreach ($skills as $skill): ?><option value="<?= $skill['id'] ?>"><?= e($skill['name']) ?></option><?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                <select name="proficiency" class="field-input">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="expert">Expert</option>
                </select>
                <button type="submit" class="btn-secondary btn-sm w-full">Add skill</button>
            </form>
        </div>

        <div class="card-surface p-6">
            <h3 class="font-display font-semibold text-sm text-ink mb-2">Can't find a skill?</h3>
            <form method="post" action="<?= Router::url('/skill-requests') ?>" class="space-y-2">
                <?= Csrf::field() ?>
                <input type="hidden" name="redirect_to" value="/candidate/profile">
                <input type="text" name="name" placeholder="Skill name" class="field-input">
                <input type="text" name="category" placeholder="Category" class="field-input">
                <button type="submit" class="btn-secondary btn-sm w-full">Request new skill</button>
            </form>
        </div>
    </div>
</div>
