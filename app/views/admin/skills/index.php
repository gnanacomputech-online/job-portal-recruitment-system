<?php use Core\Csrf; use Core\Router; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6">Skills Taxonomy</h1>

<div class="grid lg:grid-cols-2 gap-6">
    <div>
        <div class="card-surface p-6 mb-6">
            <h2 class="font-display font-semibold text-base text-ink mb-3">Add a skill directly</h2>
            <form method="post" action="<?= Router::url('/admin/skills') ?>" class="flex gap-2 flex-wrap">
                <?= Csrf::field() ?>
                <input type="text" name="name" placeholder="Skill name" required class="field-input flex-1 min-w-[140px]">
                <input type="text" name="category" placeholder="Category" required class="field-input flex-1 min-w-[140px]">
                <button type="submit" class="btn-primary">Add</button>
            </form>
        </div>

        <div class="card-surface p-6">
            <h2 class="font-display font-semibold text-base text-ink mb-3">Pending &amp; reviewed skill requests</h2>
            <div class="space-y-3">
                <?php foreach ($requests as $req): ?>
                    <div class="border hairline rounded-lg p-3 flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <p class="font-medium text-ink"><?= e($req['name']) ?> <span class="text-slateink font-normal text-sm">(<?= e($req['category']) ?>)</span></p>
                            <p class="text-xs text-slateink">Requested by <?= e($req['requested_by_name']) ?> (<?= e($req['requested_by_role']) ?>) &middot; <?= format_date($req['created_at']) ?></p>
                        </div>
                        <?php if ($req['status'] === 'pending'): ?>
                            <div class="flex gap-2">
                                <form method="post" action="<?= Router::url('/admin/skill-requests/' . $req['id'] . '/approve') ?>"><?= Csrf::field() ?><button class="btn-primary btn-sm">Approve</button></form>
                                <form method="post" action="<?= Router::url('/admin/skill-requests/' . $req['id'] . '/reject') ?>"><?= Csrf::field() ?><button class="btn-danger btn-sm">Reject</button></form>
                            </div>
                        <?php else: ?>
                            <span class="status-badge status-<?= e($req['status']) ?> capitalize"><?= e($req['status']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?><p class="text-slateink text-sm">No skill requests yet.</p><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-surface p-6">
        <h2 class="font-display font-semibold text-base text-ink mb-1">Master skill list</h2>
        <p class="text-xs text-slateink mb-3">
            A skill already referenced by a job posting or a candidate profile is locked &mdash; deleting it
            would rewrite those requirements and profiles underneath the match scores already stored on
            applications. Unused skills show a &times; and can be removed.
        </p>
        <div class="space-y-4 max-h-[70vh] overflow-y-auto">
            <?php foreach ($grouped as $category => $skills): ?>
                <div>
                    <p class="section-eyebrow mb-2"><?= e($category) ?></p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($skills as $skill):
                            $use = $usage[(int) $skill['id']] ?? ['jobs' => 0, 'candidates' => 0, 'total' => 0]; ?>
                            <?php if ($use['total'] > 0): ?>
                                <span class="chip" style="background:#EEF0F4;color:var(--slate);"
                                      title="In use by <?= $use['jobs'] ?> job requirement(s) and <?= $use['candidates'] ?> candidate profile(s) — locked">
                                    <?= e($skill['name']) ?>
                                    <span class="font-mono opacity-70"><?= $use['total'] ?></span>
                                    &#128274;
                                </span>
                            <?php else: ?>
                                <form method="post" action="<?= Router::url('/admin/skills/' . $skill['id'] . '/delete') ?>" onsubmit="return confirm('Remove this unused skill from the taxonomy?');" class="inline-flex">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="chip" style="background:var(--indigo-50);color:var(--indigo);" title="Not used anywhere yet — click to remove"><?= e($skill['name']) ?> &times;</button>
                                </form>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
