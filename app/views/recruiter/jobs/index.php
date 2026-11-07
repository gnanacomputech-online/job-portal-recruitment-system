<?php use Core\Csrf; use Core\Router; ?>
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <h1 class="font-display font-semibold text-2xl text-ink">My Postings</h1>
    <a href="<?= Router::url('/recruiter/jobs/new') ?>" class="btn-primary btn-sm">Post a job</a>
</div>

<div class="card-surface overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slateink border-b hairline">
            <th class="py-3 px-4 font-medium">Title</th><th class="py-3 px-4 font-medium">Status</th>
            <th class="py-3 px-4 font-medium">Moderation</th><th class="py-3 px-4 font-medium">Applicants</th>
            <th class="py-3 px-4 font-medium">Posted</th><th class="py-3 px-4 font-medium text-right">Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($jobs as $j): ?>
            <tr class="border-b hairline">
                <td class="py-3 px-4 font-medium text-ink"><?= e($j['title']) ?></td>
                <td class="py-3 px-4"><span class="status-badge status-<?= e($j['status']) ?> capitalize"><?= e($j['status']) ?></span></td>
                <td class="py-3 px-4"><span class="status-badge status-<?= $j['moderation_status'] === 'active' ? 'open' : ($j['moderation_status'] === 'flagged' ? 'flagged' : 'suspended') ?> capitalize"><?= e($j['moderation_status']) ?></span></td>
                <td class="py-3 px-4 font-mono"><a href="<?= Router::url('/recruiter/jobs/' . $j['id'] . '/applicants') ?>" class="text-indigo hover:underline"><?= $j['application_count'] ?></a></td>
                <td class="py-3 px-4 text-slateink"><?= format_date($j['created_at']) ?></td>
                <td class="py-3 px-4">
                    <div class="flex gap-2 justify-end flex-wrap">
                        <a href="<?= Router::url('/recruiter/jobs/' . $j['id'] . '/edit') ?>" class="btn-secondary btn-sm">Edit</a>
                        <?php if ($j['status'] === 'open'): ?>
                            <form method="post" action="<?= Router::url('/recruiter/jobs/' . $j['id'] . '/close') ?>"><?= Csrf::field() ?><button class="btn-secondary btn-sm">Close</button></form>
                        <?php elseif ($j['status'] === 'closed'): ?>
                            <form method="post" action="<?= Router::url('/recruiter/jobs/' . $j['id'] . '/reopen') ?>"><?= Csrf::field() ?><button class="btn-secondary btn-sm">Reopen</button></form>
                        <?php endif; ?>
                        <?php if ((int) $j['application_count'] > 0): ?>
                            <span class="text-xs text-slateink self-center" title="Deleting would erase these candidates' applications and their snapshotted match scores. Close the posting instead.">Locked &mdash; has applicants</span>
                        <?php else: ?>
                            <form method="post" action="<?= Router::url('/recruiter/jobs/' . $j['id'] . '/delete') ?>" onsubmit="return confirm('Delete this posting? It has no applications yet.');"><?= Csrf::field() ?><button class="btn-danger btn-sm">Delete</button></form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($jobs)): ?><tr><td colspan="6" class="py-8 text-center text-slateink">No postings yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
