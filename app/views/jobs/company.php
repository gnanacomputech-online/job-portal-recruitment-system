<?php use Core\Router; use Core\View; ?>
<div class="card-surface p-8 mb-8">
    <div class="flex items-center gap-4">
        <?php if (!empty($company['logo_path'])): ?>
            <img src="<?= Router::url('/uploads/' . e($company['logo_path'])) ?>" alt="" class="w-16 h-16 rounded-lg object-cover border hairline">
        <?php else: ?>
            <div class="brand-mark w-16 h-16 !rounded-lg text-2xl text-white flex items-center justify-center"><?= e(strtoupper(substr($company['company_name'], 0, 1))) ?></div>
        <?php endif; ?>
        <div>
            <h1 class="font-display font-semibold text-2xl text-ink"><?= e($company['company_name']) ?></h1>
            <?php if (!empty($company['website'])): ?>
                <a href="<?= e($company['website']) ?>" target="_blank" rel="noopener" class="text-indigo hover:underline text-sm"><?= e($company['website']) ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!empty($company['company_description'])): ?>
        <p class="mt-5 text-ink whitespace-pre-line"><?= nl2br(e($company['company_description'])) ?></p>
    <?php endif; ?>
</div>

<h2 class="font-display font-semibold text-xl text-ink mb-4">Open positions at <?= e($company['company_name']) ?></h2>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($jobs as $job): ?>
        <?php View::renderBare('partials/job_card', ['job' => $job]); ?>
    <?php endforeach; ?>
    <?php if (empty($jobs)): ?>
        <div class="col-span-full skeleton-empty p-10 text-center">No open positions right now.</div>
    <?php endif; ?>
</div>
