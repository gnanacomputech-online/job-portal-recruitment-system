<?php use Core\Csrf; use Core\Router; ?>
<div class="max-w-lg mx-auto">
    <h1 class="font-display font-semibold text-2xl text-ink mb-6">Edit recruiter</h1>
    <div class="card-surface p-6">
        <form method="post" action="<?= Router::url('/admin/recruiters/' . $recruiterUser['id']) ?>" class="space-y-4">
            <?= Csrf::field() ?>
            <div>
                <label class="field-label">Contact name</label>
                <input type="text" name="name" required class="field-input" value="<?= e($recruiterUser['name']) ?>">
            </div>
            <div>
                <label class="field-label">Email</label>
                <input type="email" name="email" required class="field-input" value="<?= e($recruiterUser['email']) ?>">
            </div>
            <div>
                <label class="field-label">Company name</label>
                <input type="text" name="company_name" required class="field-input" value="<?= e($profile['company_name'] ?? '') ?>">
            </div>
            <div>
                <label class="field-label">Company description</label>
                <textarea name="company_description" rows="3" class="field-input"><?= e($profile['company_description'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="field-label">Website</label>
                <input type="text" name="website" class="field-input" value="<?= e($profile['website'] ?? '') ?>">
            </div>
            <div>
                <label class="field-label">Work email domain</label>
                <input type="text" name="work_email_domain" class="field-input" value="<?= e($profile['work_email_domain'] ?? '') ?>">
            </div>
            <div>
                <label class="field-label">Contact phone</label>
                <input type="text" name="contact_phone" class="field-input" value="<?= e($profile['contact_phone'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-primary w-full">Save changes</button>
        </form>
    </div>
</div>
