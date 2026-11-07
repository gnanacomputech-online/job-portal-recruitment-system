<?php use Core\Csrf; use Core\Router; use Core\View; $errors = View::errors(); ?>
<div class="max-w-md mx-auto card-surface p-8 mt-6">
    <h1 class="font-display font-semibold text-2xl text-ink mb-1">Register your company</h1>
    <p class="text-sm text-slateink mb-6">Recruiter accounts require admin approval before you can post jobs &mdash; this protects candidates from fake employers.</p>

    <form method="post" action="<?= Router::url('/register/recruiter') ?>" class="space-y-4">
        <?= Csrf::field() ?>
        <div>
            <label class="field-label">Your name</label>
            <input type="text" name="name" required class="field-input" value="<?= View::old('name') ?>">
            <?php if (!empty($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>
        </div>
        <div>
            <label class="field-label">Work email</label>
            <input type="email" name="email" required class="field-input" value="<?= View::old('email') ?>">
            <?php if (!empty($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
        </div>
        <div>
            <label class="field-label">Company name</label>
            <input type="text" name="company_name" required class="field-input" value="<?= View::old('company_name') ?>">
            <?php if (!empty($errors['company_name'])): ?><p class="field-error"><?= e($errors['company_name']) ?></p><?php endif; ?>
        </div>
        <div>
            <label class="field-label">Company work-email domain <span class="text-slateink font-normal">(optional)</span></label>
            <input type="text" name="work_email_domain" placeholder="yourcompany.com" class="field-input" value="<?= View::old('work_email_domain') ?>">
        </div>
        <div>
            <label class="field-label">Contact phone <span class="text-slateink font-normal">(optional)</span></label>
            <input type="text" name="contact_phone" class="field-input" value="<?= View::old('contact_phone') ?>">
        </div>
        <div>
            <label class="field-label">Password</label>
            <input type="password" name="password" required class="field-input">
            <p class="field-hint">At least 8 characters.</p>
            <?php if (!empty($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
        </div>
        <div>
            <label class="field-label">Confirm password</label>
            <input type="password" name="password_confirm" required class="field-input">
            <?php if (!empty($errors['password_confirm'])): ?><p class="field-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
        </div>
        <button type="submit" class="btn-primary w-full">Submit for approval</button>
    </form>
    <p class="text-center text-sm text-slateink mt-4">Already have an account? <a href="<?= Router::url('/login') ?>" class="text-indigo hover:underline">Log in</a></p>
</div>
