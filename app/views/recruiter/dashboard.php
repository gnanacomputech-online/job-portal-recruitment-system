<?php use Core\Router; ?>
<?php if ($user['status'] === 'pending'): ?>
    <div class="mb-6 rounded-lg border border-amber/30 bg-amber-bg text-amber px-4 py-3 text-sm font-medium">
        Your company account is pending admin approval. You'll be able to post jobs once approved — feel free to fill in your company profile meanwhile.
    </div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="font-display font-semibold text-2xl text-ink"><?= e($profile['company_name'] ?? $user['name']) ?></h1>
        <p class="text-sm text-slateink">Recruiter Dashboard</p>
    </div>
    <a href="<?= Router::url('/recruiter/jobs/new') ?>" class="btn-primary <?= $user['status'] !== 'approved' ? 'opacity-50 pointer-events-none' : '' ?>">Post a job</a>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-8">
    <div class="card-surface p-5"><div class="text-3xl font-mono text-ink"><?= count($jobs) ?></div><div class="text-sm text-slateink mt-1">Total postings</div></div>
    <div class="card-surface p-5"><div class="text-3xl font-mono text-ink"><?= $openJobsCount ?></div><div class="text-sm text-slateink mt-1">Open postings</div></div>
    <div class="card-surface p-5"><div class="text-3xl font-mono text-ink"><?= $totalApplicants ?></div><div class="text-sm text-slateink mt-1">Total applicants</div></div>
</div>

<h2 class="font-display font-semibold text-lg text-ink mb-4">Your recent postings</h2>
<div class="card-surface overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slateink border-b hairline">
            <th class="py-3 px-4 font-medium">Title</th><th class="py-3 px-4 font-medium">Status</th>
            <th class="py-3 px-4 font-medium">Applicants</th><th class="py-3 px-4 font-medium"></th>
        </tr></thead>
        <tbody>
        <?php foreach (array_slice($jobs, 0, 8) as $j): ?>
            <tr class="border-b hairline">
                <td class="py-3 px-4 font-medium text-ink"><?= e($j['title']) ?></td>
                <td class="py-3 px-4"><span class="status-badge status-<?= e($j['status']) ?> capitalize"><?= e($j['status']) ?></span></td>
                <td class="py-3 px-4 font-mono"><?= $j['application_count'] ?></td>
                <td class="py-3 px-4 text-right"><a href="<?= Router::url('/recruiter/jobs/' . $j['id'] . '/applicants') ?>" class="btn-secondary btn-sm">View applicants</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($jobs)): ?><tr><td colspan="4" class="py-8 text-center text-slateink">No postings yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
