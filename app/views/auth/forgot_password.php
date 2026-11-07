<?php use Core\Csrf; use Core\Router; ?>
<div class="max-w-md mx-auto card-surface p-8 mt-6">
    <h1 class="font-display font-semibold text-2xl text-ink mb-1">Reset your password</h1>
    <p class="text-sm text-slateink mb-6">Enter your account email and we'll send a reset link.</p>
    <form method="post" action="<?= Router::url('/forgot-password') ?>" class="space-y-4">
        <?= Csrf::field() ?>
        <div>
            <label class="field-label">Email</label>
            <input type="email" name="email" required class="field-input">
        </div>
        <button type="submit" class="btn-primary w-full">Send reset link</button>
    </form>
    <p class="text-center text-sm text-slateink mt-4"><a href="<?= Router::url('/login') ?>" class="text-indigo hover:underline">Back to login</a></p>
</div>
