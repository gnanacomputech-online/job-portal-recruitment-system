<?php

namespace App\Controllers;

use App\Models\Application;
use App\Models\RecruiterProfile;
use App\Models\User;
use Core\Auth;
use Core\Csrf;
use Core\Mailer;
use Core\Router;
use Core\Validator;
use Core\View;

class AdminRecruiterController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        $status = $_GET['status'] ?? null;
        $recruiters = User::listRecruiters($status ?: null);
        View::render('admin/recruiters/index', ['recruiters' => $recruiters, 'status' => $status]);
    }

    public function approve(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/recruiters');
        }

        $user = User::findById($id);
        if ($user && $user['role'] === 'recruiter') {
            User::updateStatus($id, 'approved');
            Mailer::send($user['email'], $user['name'], 'TalentFit — Your recruiter account is approved',
                '<p>Hi ' . e($user['name']) . ',</p><p>Your company account has been approved. You can now log in and start posting jobs.</p>');
            View::flash('success', 'Recruiter approved.');
        }
        Router::redirect('/admin/recruiters');
    }

    public function reject(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/recruiters');
        }

        $user = User::findById($id);
        if ($user && $user['role'] === 'recruiter') {
            User::updateStatus($id, 'rejected');
            Mailer::send($user['email'], $user['name'], 'TalentFit — Registration update',
                '<p>Hi ' . e($user['name']) . ',</p><p>Your company registration was not approved at this time.</p>');
            View::flash('success', 'Recruiter registration rejected.');
        }
        Router::redirect('/admin/recruiters');
    }

    public function suspend(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/recruiters');
        }
        $user = User::findById($id);
        if ($user && $user['role'] === 'recruiter') {
            User::updateStatus($id, 'suspended');
            View::flash('success', 'Recruiter suspended — their postings are now hidden from candidates and their own session is locked out.');
        }
        Router::redirect('/admin/recruiters');
    }

    public function unsuspend(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/recruiters');
        }
        $user = User::findById($id);
        if ($user && $user['role'] === 'recruiter') {
            User::updateStatus($id, 'approved');
            View::flash('success', 'Recruiter reinstated.');
        }
        Router::redirect('/admin/recruiters');
    }

    public function delete(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/recruiters');
        }
        $user = User::findById($id);
        if (!$user || $user['role'] !== 'recruiter') {
            Router::redirect('/admin/recruiters');
        }

        // Deleting the user cascades jobs -> applications -> status history, so
        // every candidate who ever applied to this company would lose their
        // application record and its snapshotted match score. Suspension is the
        // reversible action that achieves the same visible outcome.
        $applicationCount = Application::countForRecruiter($id);
        if ($applicationCount > 0) {
            View::flash('error', "This company's postings hold {$applicationCount} candidate application(s) — deleting the account would erase all of them along with their match-score history. Suspend the recruiter instead.");
            Router::redirect('/admin/recruiters');
        }

        User::delete($id);
        View::flash('success', 'Recruiter account deleted.');
        Router::redirect('/admin/recruiters');
    }

    public function showNew(): void
    {
        Auth::requireRole('admin');
        View::render('admin/recruiters/new');
    }

    public function create(): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/recruiters/new');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'company_name' => trim($_POST['company_name'] ?? ''),
        ];
        $v = new Validator($data);
        $v->required('name')->required('email')->email('email')->unique('email', 'users', 'email')->required('company_name');
        if ($v->fails()) {
            $_SESSION['errors'] = $v->firstErrors();
            $_SESSION['old'] = $data;
            Router::redirect('/admin/recruiters/new');
        }

        $password = bin2hex(random_bytes(5));
        $userId = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'recruiter',
            'status' => 'approved',
            'source' => 'admin_manual',
        ]);
        RecruiterProfile::create($userId, [
            'company_name' => $data['company_name'],
            'contact_name' => $data['name'],
        ]);

        Mailer::send($data['email'], $data['name'], 'TalentFit — Your recruiter account',
            '<p>Hi ' . e($data['name']) . ',</p><p>An admin created a recruiter account for you.</p><p>Email: ' . e($data['email']) . '<br>Temporary password: ' . e($password) . '</p>');

        View::flash('success', "Recruiter created. One-time password: {$password} (also emailed).");
        Router::redirect('/admin/recruiters');
    }

    public function showEdit(int $id): void
    {
        Auth::requireRole('admin');
        $user = User::findById($id);
        $profile = RecruiterProfile::find($id);
        if (!$user || $user['role'] !== 'recruiter') {
            Router::redirect('/admin/recruiters');
        }
        View::render('admin/recruiters/edit', ['recruiterUser' => $user, 'profile' => $profile]);
    }

    public function update(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect("/admin/recruiters/{$id}/edit");
        }

        $user = User::findById($id);
        if (!$user || $user['role'] !== 'recruiter') {
            Router::redirect('/admin/recruiters');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');

        User::updateProfile($id, $name, $email);
        RecruiterProfile::upsert($id, [
            'company_name' => $companyName,
            'company_description' => trim($_POST['company_description'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'work_email_domain' => trim($_POST['work_email_domain'] ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
        ]);

        View::flash('success', 'Recruiter updated.');
        Router::redirect('/admin/recruiters');
    }

    public function showImport(): void
    {
        Auth::requireRole('admin');
        View::render('admin/recruiters/import');
    }

    public function import(): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/recruiters/import');
        }

        if (empty($_FILES['roster']) || $_FILES['roster']['error'] !== UPLOAD_ERR_OK) {
            View::flash('error', 'Please choose a .xlsx file to upload.');
            Router::redirect('/admin/recruiters/import');
        }

        $tmpPath = $_FILES['roster']['tmp_name'];
        $originalName = $_FILES['roster']['name'];
        if (!str_ends_with(strtolower($originalName), '.xlsx')) {
            View::flash('error', 'Only .xlsx files are supported.');
            Router::redirect('/admin/recruiters/import');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
        } catch (\Throwable $e) {
            View::flash('error', 'Could not read that spreadsheet. Make sure it is a valid .xlsx file.');
            Router::redirect('/admin/recruiters/import');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        $created = [];
        $skipped = [];

        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue; // header row: company_name, contact_email, contact_name
            }
            [$companyName, $contactEmail, $contactName] = array_pad($row, 3, null);
            $companyName = trim((string) $companyName);
            $contactEmail = trim((string) $contactEmail);
            $contactName = trim((string) ($contactName ?? ''));

            if ($companyName === '' || $contactEmail === '' || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (User::emailExists($contactEmail)) {
                $skipped[] = $contactEmail;
                continue;
            }

            $password = bin2hex(random_bytes(5));
            $userId = User::create([
                'name' => $contactName !== '' ? $contactName : $companyName,
                'email' => $contactEmail,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'recruiter',
                'status' => 'approved',
                'source' => 'bulk_import',
            ]);
            RecruiterProfile::create($userId, [
                'company_name' => $companyName,
                'contact_name' => $contactName !== '' ? $contactName : null,
            ]);

            Mailer::send($contactEmail, $contactName ?: $companyName, 'TalentFit — Your recruiter account',
                '<p>An admin imported your company as a vetted TalentFit partner.</p><p>Email: ' . e($contactEmail) . '<br>Temporary password: ' . e($password) . '</p>');

            $created[] = ['company' => $companyName, 'email' => $contactEmail, 'password' => $password];
        }

        View::render('admin/recruiters/import_result', ['created' => $created, 'skipped' => $skipped]);
    }
}
