<?php use Core\Csrf; use Core\Router; use Core\View; $errors = View::errors(); ?>
<div class="max-w-lg mx-auto space-y-6">
    <div>
        <h1 class="font-display font-semibold text-2xl text-ink">My Account</h1>
        <p class="text-sm text-slateink">Role: <span class="status-badge status-approved capitalize"><?= e($user['role']) ?></span></p>
    </div>

    <div class="card-surface p-6">
        <h2 class="font-display font-semibold text-lg text-ink mb-4">Account details</h2>
        <form method="post" action="<?= Router::url('/account') ?>" class="space-y-4">
            <?= Csrf::field() ?>
            <div>
                <label class="field-label">Name</label>
                <input type="text" name="name" required class="field-input" value="<?= e($user['name']) ?>">
                <?php if (!empty($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="field-label">Email</label>
                <input type="email" name="email" required class="field-input" value="<?= e($user['email']) ?>">
                <?php if (!empty($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>
            <button type="submit" class="btn-primary">Save changes</button>
        </form>
    </div>

    <div class="card-surface p-6">
        <h2 class="font-display font-semibold text-lg text-ink mb-4">Change password</h2>
        <form method="post" action="<?= Router::url('/account/password') ?>" class="space-y-4">
            <?= Csrf::field() ?>
            <div>
                <label class="field-label">Current password</label>
                <input type="password" name="current_password" required class="field-input">
            </div>
            <div>
                <label class="field-label">New password</label>
                <input type="password" name="new_password" required class="field-input">
            </div>
            <div>
                <label class="field-label">Confirm new password</label>
                <input type="password" name="new_password_confirm" required class="field-input">
            </div>
            <button type="submit" class="btn-primary">Update password</button>
        </form>
    </div>
</div>
