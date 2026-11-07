<?php use Core\Router; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6">Admin Dashboard</h1>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php
    $tiles = [
        ['label' => 'Candidates', 'value' => $stats['candidates'], 'link' => '/admin/candidates'],
        ['label' => 'Approved recruiters', 'value' => $stats['recruiters_approved'], 'link' => '/admin/recruiters?status=approved'],
        ['label' => 'Pending recruiters', 'value' => $stats['recruiters_pending'], 'link' => '/admin/recruiters?status=pending', 'highlight' => $stats['recruiters_pending'] > 0],
        ['label' => 'Open jobs', 'value' => $stats['jobs_open'], 'link' => '/admin/jobs'],
        ['label' => 'Total jobs', 'value' => $stats['jobs_total'], 'link' => '/admin/jobs'],
        ['label' => 'Applications', 'value' => $stats['applications_total'], 'link' => '/admin/applications'],
        ['label' => 'Pending skill requests', 'value' => $stats['skill_requests_pending'], 'link' => '/admin/skills', 'highlight' => $stats['skill_requests_pending'] > 0],
    ];
    ?>
    <?php foreach ($tiles as $tile): ?>
        <a href="<?= Router::url($tile['link']) ?>" class="card-surface p-5 hover:border-indigo transition <?= !empty($tile['highlight']) ? '!border-amber' : '' ?>">
            <div class="text-3xl font-display font-semibold text-ink font-mono"><?= $tile['value'] ?></div>
            <div class="text-sm text-slateink mt-1"><?= e($tile['label']) ?></div>
        </a>
    <?php endforeach; ?>
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <a href="<?= Router::url('/admin/recruiters/import') ?>" class="btn-secondary justify-center">Bulk-import recruiters (Excel)</a>
    <a href="<?= Router::url('/admin/skills') ?>" class="btn-secondary justify-center">Manage skills taxonomy</a>
</div>
