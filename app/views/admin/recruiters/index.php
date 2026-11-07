<?php use Core\Csrf; use Core\Router; ?>
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <h1 class="font-display font-semibold text-2xl text-ink">Recruiter Accounts</h1>
    <div class="flex gap-2">
        <a href="<?= Router::url('/admin/recruiters/import') ?>" class="btn-secondary btn-sm">Bulk import</a>
        <a href="<?= Router::url('/admin/recruiters/new') ?>" class="btn-primary btn-sm">Add recruiter</a>
    </div>
</div>

<div class="flex gap-2 mb-5 text-sm">
    <?php foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $val => $label): ?>
        <a href="<?= Router::url('/admin/recruiters' . ($val ? '?status=' . $val : '')) ?>"
           class="px-3 py-1.5 rounded-full <?= ($status ?: '') === $val ? 'bg-indigo text-white' : 'bg-white border hairline text-slateink' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="card-surface overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slateink border-b hairline">
                <th class="py-3 px-4 font-medium">Company</th>
                <th class="py-3 px-4 font-medium">Contact</th>
                <th class="py-3 px-4 font-medium">Status</th>
                <th class="py-3 px-4 font-medium">Source</th>
                <th class="py-3 px-4 font-medium">Joined</th>
                <th class="py-3 px-4 font-medium text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($recruiters as $r): ?>
            <tr class="border-b hairline">
                <td class="py-3 px-4 font-medium text-ink"><?= e($r['company_name'] ?? '—') ?></td>
                <td class="py-3 px-4"><?= e($r['name']) ?><br><span class="text-slateink text-xs"><?= e($r['email']) ?></span></td>
                <td class="py-3 px-4"><span class="status-badge status-<?= e($r['status']) ?> capitalize"><?= e($r['status']) ?></span></td>
                <td class="py-3 px-4 text-slateink"><?= e(str_replace('_', ' ', $r['source'])) ?></td>
                <td class="py-3 px-4 text-slateink"><?= format_date($r['created_at']) ?></td>
                <td class="py-3 px-4">
                    <div class="flex gap-2 justify-end flex-wrap">
                        <?php if ($r['status'] === 'pending'): ?>
                            <form method="post" action="<?= Router::url('/admin/recruiters/' . $r['id'] . '/approve') ?>"><?= Csrf::field() ?><button class="btn-primary btn-sm">Approve</button></form>
                            <form method="post" action="<?= Router::url('/admin/recruiters/' . $r['id'] . '/reject') ?>"><?= Csrf::field() ?><button class="btn-danger btn-sm">Reject</button></form>
                        <?php endif; ?>
                        <a href="<?= Router::url('/admin/recruiters/' . $r['id'] . '/edit') ?>" class="btn-secondary btn-sm">Edit</a>
                        <?php if ($r['status'] === 'suspended'): ?>
                            <form method="post" action="<?= Router::url('/admin/recruiters/' . $r['id'] . '/unsuspend') ?>"><?= Csrf::field() ?><button class="btn-secondary btn-sm">Reinstate</button></form>
                        <?php elseif ($r['status'] === 'approved'): ?>
                            <form method="post" action="<?= Router::url('/admin/recruiters/' . $r['id'] . '/suspend') ?>"><?= Csrf::field() ?><button class="btn-secondary btn-sm">Suspend</button></form>
                        <?php endif; ?>
                        <?php if ((int) ($r['application_count'] ?? 0) > 0): ?>
                            <span class="text-xs text-slateink self-center" title="Their postings hold <?= (int) $r['application_count'] ?> candidate application(s); deleting the account would erase those records. Suspend instead.">Locked &mdash; <?= (int) $r['application_count'] ?> application(s)</span>
                        <?php else: ?>
                            <form method="post" action="<?= Router::url('/admin/recruiters/' . $r['id'] . '/delete') ?>" onsubmit="return confirm('Delete this recruiter account and all their postings?');"><?= Csrf::field() ?><button class="btn-danger btn-sm">Delete</button></form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($recruiters)): ?>
            <tr><td colspan="6" class="py-8 text-center text-slateink">No recruiters in this category.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
