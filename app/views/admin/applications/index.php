<?php use Core\Router; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6">Applications &amp; Audit Log</h1>

<div class="flex gap-2 mb-5 text-sm flex-wrap">
    <?php foreach (['' => 'All', 'applied' => 'Applied', 'shortlisted' => 'Shortlisted', 'interview' => 'Interview', 'offered' => 'Offered', 'rejected' => 'Rejected', 'withdrawn' => 'Withdrawn'] as $val => $label): ?>
        <a href="<?= Router::url('/admin/applications' . ($val ? '?status=' . $val : '')) ?>"
           class="px-3 py-1.5 rounded-full <?= ($status ?: '') === $val ? 'bg-indigo text-white' : 'bg-white border hairline text-slateink' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="card-surface overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slateink border-b hairline">
                <th class="py-3 px-4 font-medium">Candidate</th>
                <th class="py-3 px-4 font-medium">Job</th>
                <th class="py-3 px-4 font-medium">Company</th>
                <th class="py-3 px-4 font-medium">Match</th>
                <th class="py-3 px-4 font-medium">Status</th>
                <th class="py-3 px-4 font-medium">Applied</th>
                <th class="py-3 px-4 font-medium"></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($applications as $a): ?>
            <tr class="border-b hairline">
                <td class="py-3 px-4 text-ink font-medium"><?= e($a['candidate_name']) ?></td>
                <td class="py-3 px-4"><?= e($a['job_title']) ?></td>
                <td class="py-3 px-4 text-slateink"><?= e($a['company_name']) ?></td>
                <td class="py-3 px-4 font-mono"><?= (int) $a['match_score'] ?></td>
                <td class="py-3 px-4"><span class="status-badge status-<?= e($a['status']) ?> capitalize"><?= e($a['status']) ?></span></td>
                <td class="py-3 px-4 text-slateink"><?= format_date($a['applied_at']) ?></td>
                <td class="py-3 px-4 text-right"><a href="<?= Router::url('/admin/applications/' . $a['id']) ?>" class="btn-secondary btn-sm">View</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($applications)): ?>
            <tr><td colspan="7" class="py-8 text-center text-slateink">No applications yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
