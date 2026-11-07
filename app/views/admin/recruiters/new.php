<?php use Core\Csrf; use Core\Router; use Core\View; $errors = View::errors(); ?>
<div class="max-w-lg mx-auto">
    <h1 class="font-display font-semibold text-2xl text-ink mb-6">Add a recruiter account</h1>
    <div class="card-surface p-6">
        <form method="post" action="<?= Router::url('/admin/recruiters') ?>" class="space-y-4">
            <?= Csrf::field() ?>
            <div>
                <label class="field-label">Contact name</label>
                <input type="text" name="name" required class="field-input" value="<?= View::old('name') ?>">
                <?php if (!empty($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="field-label">Email</label>
                <input type="email" name="email" required class="field-input" value="<?= View::old('email') ?>">
                <?php if (!empty($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="field-label">Company name</label>
                <input type="text" name="company_name" required class="field-input" value="<?= View::old('company_name') ?>">
                <?php if (!empty($errors['company_name'])): ?><p class="field-error"><?= e($errors['company_name']) ?></p><?php endif; ?>
            </div>
            <p class="field-hint">A one-time password will be generated and shown to you (and emailed) — this account is created already <strong>approved</strong> (admin_manual source).</p>
            <button type="submit" class="btn-primary w-full">Create recruiter</button>
        </form>
    </div>
</div>
