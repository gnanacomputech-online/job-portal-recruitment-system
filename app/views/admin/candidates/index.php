<?php use Core\Csrf; use Core\Router; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6">Candidate Accounts</h1>

<div class="flex gap-2 mb-5 text-sm">
    <?php foreach (['' => 'All', 'approved' => 'Active', 'suspended' => 'Suspended'] as $val => $label): ?>
        <a href="<?= Router::url('/admin/candidates' . ($val ? '?status=' . $val : '')) ?>"
           class="px-3 py-1.5 rounded-full <?= ($status ?: '') === $val ? 'bg-indigo text-white' : 'bg-white border hairline text-slateink' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="card-surface overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slateink border-b hairline">
                <th class="py-3 px-4 font-medium">Name</th>
                <th class="py-3 px-4 font-medium">Headline</th>
                <th class="py-3 px-4 font-medium">Location</th>
                <th class="py-3 px-4 font-medium">Open to Work</th>
                <th class="py-3 px-4 font-medium">Status</th>
                <th class="py-3 px-4 font-medium">Joined</th>
                <th class="py-3 px-4 font-medium text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($candidates as $c): ?>
            <tr class="border-b hairline">
                <td class="py-3 px-4 font-medium text-ink"><?= e($c['name']) ?><br><span class="text-slateink text-xs font-normal"><?= e($c['email']) ?></span></td>
                <td class="py-3 px-4 text-slateink"><?= e($c['headline'] ?? '—') ?></td>
                <td class="py-3 px-4 text-slateink"><?= e($c['location'] ?? '—') ?></td>
                <td class="py-3 px-4"><?= !empty($c['open_to_work']) ? '<span class="chip chip-met">Yes</span>' : '<span class="text-slateink">No</span>' ?></td>
                <td class="py-3 px-4"><span class="status-badge status-<?= e($c['status']) ?> capitalize"><?= e($c['status']) ?></span></td>
                <td class="py-3 px-4 text-slateink"><?= format_date($c['created_at']) ?></td>
                <td class="py-3 px-4 text-right">
                    <?php if ($c['status'] === 'suspended'): ?>
                        <form method="post" action="<?= Router::url('/admin/candidates/' . $c['id'] . '/unsuspend') ?>"><?= Csrf::field() ?><button class="btn-secondary btn-sm">Reinstate</button></form>
                    <?php else: ?>
                        <form method="post" action="<?= Router::url('/admin/candidates/' . $c['id'] . '/suspend') ?>"><?= Csrf::field() ?><button class="btn-danger btn-sm">Suspend</button></form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($candidates)): ?>
            <tr><td colspan="7" class="py-8 text-center text-slateink">No candidates in this category.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
