<?php use Core\Csrf; use Core\Router; use Core\View; $errors = View::errors(); ?>
<div class="max-w-md mx-auto card-surface p-8 mt-6">
    <h1 class="font-display font-semibold text-2xl text-ink mb-1">Log in</h1>
    <p class="text-sm text-slateink mb-6">Welcome back to TalentFit.</p>

    <form method="post" action="<?= Router::url('/login') ?>" class="space-y-4">
        <?= Csrf::field() ?>
        <div>
            <label class="field-label">Email</label>
            <input type="email" name="email" required class="field-input" value="<?= View::old('email') ?>">
        </div>
        <div>
            <label class="field-label">Password</label>
            <input type="password" name="password" required class="field-input">
        </div>
        <button type="submit" class="btn-primary w-full">Log in</button>
    </form>

    <div class="flex justify-between mt-4 text-sm">
        <a href="<?= Router::url('/forgot-password') ?>" class="text-indigo hover:underline">Forgot password?</a>
        <a href="<?= Router::url('/register') ?>" class="text-indigo hover:underline">Create an account</a>
    </div>

    <div class="mt-8 pt-6 border-t hairline text-xs text-slateink space-y-1">
        <p class="font-semibold text-ink mb-1">Demo credentials</p>
        <p>Admin: <span class="font-mono">admin@talentfit.test</span> / <span class="font-mono">Admin@123</span></p>
        <p>Recruiter: <span class="font-mono">priya@nimbuscloud.test</span> / <span class="font-mono">Recruiter@123</span></p>
        <p>Candidate: <span class="font-mono">aarav.gupta@talentfit.test</span> / <span class="font-mono">Candidate@123</span></p>
    </div>
</div>
