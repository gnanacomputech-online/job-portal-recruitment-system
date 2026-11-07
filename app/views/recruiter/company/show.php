<?php use Core\Auth; use Core\Csrf; use Core\Router; ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="font-display font-semibold text-2xl text-ink">Company Profile</h1>
    <a href="<?= Router::url('/companies/' . Auth::id()) ?>" target="_blank" class="text-sm text-indigo hover:underline">View public page &rarr;</a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card-surface p-6">
        <form method="post" action="<?= Router::url('/recruiter/company') ?>" enctype="multipart/form-data" class="space-y-4">
            <?= Csrf::field() ?>
            <div>
                <label class="field-label">Company name</label>
                <input type="text" name="company_name" required class="field-input" value="<?= e($profile['company_name'] ?? '') ?>">
            </div>
            <div>
                <label class="field-label">Description</label>
                <textarea name="company_description" rows="4" class="field-input"><?= e($profile['company_description'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="field-label">Website</label>
                <input type="text" name="website" class="field-input" value="<?= e($profile['website'] ?? '') ?>">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Contact name</label>
                    <input type="text" name="contact_name" class="field-input" value="<?= e($profile['contact_name'] ?? '') ?>">
                </div>
                <div>
                    <label class="field-label">Contact phone</label>
                    <input type="text" name="contact_phone" class="field-input" value="<?= e($profile['contact_phone'] ?? '') ?>">
                </div>
            </div>
            <div>
                <label class="field-label">Work email domain</label>
                <input type="text" name="work_email_domain" class="field-input" value="<?= e($profile['work_email_domain'] ?? '') ?>">
            </div>
            <div>
                <label class="field-label">Logo <span class="text-slateink font-normal">(JPG/PNG/WebP, under 2MB)</span></label>
                <input type="file" name="logo" accept="image/*" class="field-input">
            </div>
            <button type="submit" class="btn-primary">Save company profile</button>
        </form>
    </div>
    <div class="card-surface p-6 h-fit text-center">
        <?php if (!empty($profile['logo_path'])): ?>
            <img src="<?= Router::url('/uploads/' . e($profile['logo_path'])) ?>" alt="" class="w-24 h-24 rounded-lg object-cover border hairline mx-auto">
        <?php else: ?>
            <div class="brand-mark w-24 h-24 !rounded-lg text-3xl text-white flex items-center justify-center mx-auto"><?= e(strtoupper(substr($profile['company_name'] ?? 'C', 0, 1))) ?></div>
        <?php endif; ?>
        <p class="text-sm text-slateink mt-3">This logo and description appear on your public company page, visible to every candidate.</p>
    </div>
</div>
