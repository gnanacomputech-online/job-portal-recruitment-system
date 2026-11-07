<?php use Core\Csrf; use Core\Router; ?>
<div class="max-w-lg mx-auto">
    <h1 class="font-display font-semibold text-2xl text-ink mb-2">Bulk import recruiters</h1>
    <p class="text-sm text-slateink mb-6">Upload a .xlsx roster of already-vetted partner companies. Columns, in order: <span class="font-mono">company_name</span>, <span class="font-mono">contact_email</span>, <span class="font-mono">contact_name</span> — first row is treated as a header and skipped.</p>
    <div class="card-surface p-6">
        <form method="post" action="<?= Router::url('/admin/recruiters/import') ?>" enctype="multipart/form-data" class="space-y-4">
            <?= Csrf::field() ?>
            <div>
                <label class="field-label">Roster file (.xlsx)</label>
                <input type="file" name="roster" accept=".xlsx" required class="field-input">
            </div>
            <button type="submit" class="btn-primary w-full">Import</button>
        </form>
    </div>
</div>
