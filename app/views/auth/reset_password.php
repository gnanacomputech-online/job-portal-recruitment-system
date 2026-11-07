<?php use Core\Csrf; use Core\Router; ?>
<div class="max-w-md mx-auto card-surface p-8 mt-6">
    <h1 class="font-display font-semibold text-2xl text-ink mb-1">Choose a new password</h1>
    <form method="post" action="<?= Router::url('/reset-password') ?>" class="space-y-4 mt-6">
        <?= Csrf::field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div>
            <label class="field-label">New password</label>
            <input type="password" name="password" required class="field-input">
            <p class="field-hint">At least 8 characters.</p>
        </div>
        <div>
            <label class="field-label">Confirm new password</label>
            <input type="password" name="password_confirm" required class="field-input">
        </div>
        <button type="submit" class="btn-primary w-full">Update password</button>
    </form>
</div>
