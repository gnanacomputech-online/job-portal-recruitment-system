<?php

namespace App\Controllers;

use App\Models\RecruiterProfile;
use Core\Auth;
use Core\Csrf;
use Core\Router;
use Core\Upload;
use Core\View;

class RecruiterCompanyController
{
    public function show(): void
    {
        Auth::requireRole('recruiter');
        View::render('recruiter/company/show', ['profile' => RecruiterProfile::find(Auth::id())]);
    }

    public function update(): void
    {
        Auth::requireRole('recruiter');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/recruiter/company');
        }

        RecruiterProfile::upsert(Auth::id(), [
            'company_name' => trim($_POST['company_name'] ?? ''),
            'company_description' => trim($_POST['company_description'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'work_email_domain' => trim($_POST['work_email_domain'] ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
        ]);

        if (!empty($_FILES['logo']['name'])) {
            $path = Upload::storeLogo($_FILES['logo']);
            if ($path) {
                RecruiterProfile::updateLogo(Auth::id(), $path);
            } else {
                View::flash('error', 'Company profile saved, but the logo upload failed (must be JPG/PNG/WebP under 2MB).');
                Router::redirect('/recruiter/company');
            }
        }

        View::flash('success', 'Company profile updated.');
        Router::redirect('/recruiter/company');
    }
}
