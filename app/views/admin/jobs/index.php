<?php use Core\Csrf; use Core\Router; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6">Job Postings Moderation</h1>

<div class="card-surface overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slateink border-b hairline">
                <th class="py-3 px-4 font-medium">Title</th>
                <th class="py-3 px-4 font-medium">Company</th>
                <th class="py-3 px-4 font-medium">Status</th>
                <th class="py-3 px-4 font-medium">Moderation</th>
                <th class="py-3 px-4 font-medium">Posted</th>
                <th class="py-3 px-4 font-medium text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($jobs as $j): ?>
            <tr class="border-b hairline">
                <td class="py-3 px-4 font-medium text-ink"><a class="hover:text-indigo" href="<?= Router::url('/jobs/' . $j['id']) ?>" target="_blank"><?= e($j['title']) ?></a></td>
                <td class="py-3 px-4 text-slateink"><?= e($j['company_name']) ?></td>
                <td class="py-3 px-4"><span class="status-badge status-<?= e($j['status']) ?> capitalize"><?= e($j['status']) ?></span></td>
                <td class="py-3 px-4">
                    <span class="status-badge status-<?= $j['moderation_status'] === 'active' ? 'open' : ($j['moderation_status'] === 'flagged' ? 'flagged' : 'suspended') ?> capitalize"><?= e($j['moderation_status']) ?></span>
                    <?php if ($j['moderation_note']): ?><div class="text-xs text-slateink mt-1 max-w-xs"><?= e($j['moderation_note']) ?></div><?php endif; ?>
                </td>
                <td class="py-3 px-4 text-slateink"><?= format_date($j['created_at']) ?></td>
                <td class="py-3 px-4">
                    <div class="flex gap-2 justify-end flex-wrap">
                        <?php if ($j['moderation_status'] !== 'flagged'): ?>
                            <form method="post" action="<?= Router::url('/admin/jobs/' . $j['id'] . '/flag') ?>" onsubmit="return promptNote(this, 'Reason for flagging?');">
                                <?= Csrf::field() ?><input type="hidden" name="note" class="note-input">
                                <button type="submit" class="btn-secondary btn-sm">Flag</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($j['moderation_status'] !== 'removed'): ?>
                            <form method="post" action="<?= Router::url('/admin/jobs/' . $j['id'] . '/remove') ?>" onsubmit="return promptNote(this, 'Reason for removal?');">
                                <?= Csrf::field() ?><input type="hidden" name="note" class="note-input">
                                <button type="submit" class="btn-danger btn-sm">Remove</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($j['moderation_status'] !== 'active'): ?>
                            <form method="post" action="<?= Router::url('/admin/jobs/' . $j['id'] . '/restore') ?>"><?= Csrf::field() ?><button class="btn-secondary btn-sm">Restore</button></form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($jobs)): ?>
            <tr><td colspan="6" class="py-8 text-center text-slateink">No job postings yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
function promptNote(form, message) {
    const note = window.prompt(message, '');
    if (note === null) return false;
    form.querySelector('.note-input').value = note;
    return true;
}
</script>
