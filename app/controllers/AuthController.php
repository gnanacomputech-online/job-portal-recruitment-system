<?php

namespace App\Controllers;

use App\Models\CandidateProfile;
use App\Models\RecruiterProfile;
use App\Models\User;
use Core\Auth;
use Core\Csrf;
use Core\Mailer;
use Core\Router;
use Core\Validator;
use Core\View;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Router::redirect(Auth::dashboardPath());
        }
        View::render('auth/login');
    }

    public function login(): void
    {
        if (!Csrf::verifyRequest()) {
            View::flash('error', 'Your session expired, please try again.');
            Router::redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = Auth::attempt($email, $password);
        if (!$user) {
            $existing = User::findByEmail($email);
            if ($existing && $existing['status'] !== 'approved' && password_verify($password, (string) $existing['password_hash'])) {
                $msg = match ($existing['status']) {
                    'pending' => 'Your recruiter account is still pending admin approval.',
                    'rejected' => 'Your recruiter registration was not approved. Contact support for details.',
                    'suspended' => 'Your account has been suspended. Contact support for details.',
                    default => 'Unable to log in.',
                };
                View::flash('error', $msg);
            } else {
                View::flash('error', 'Invalid email or password.');
            }
            Router::redirect('/login');
        }

        Router::redirect(Auth::dashboardPath());
    }

    public function logout(): void
    {
        if (!Csrf::verifyRequest()) {
            Router::redirect('/');
        }
        Auth::logout();
        Router::redirect('/login');
    }

    public function showRegisterChoice(): void
    {
        if (Auth::check()) {
            Router::redirect(Auth::dashboardPath());
        }
        View::render('auth/register_choice');
    }

    public function showRegisterCandidate(): void
    {
        if (Auth::check()) {
            Router::redirect(Auth::dashboardPath());
        }
        View::render('auth/register_candidate');
    }

    public function registerCandidate(): void
    {
        if (!Csrf::verifyRequest()) {
            Router::redirect('/register/candidate');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
        ];

        $v = new Validator($data);
        $v->required('name')->required('email')->email('email')->unique('email', 'users', 'email')
            ->required('password')->minLength('password', 8)
            ->matches('password_confirm', 'password', 'Passwords do not match.');

        if ($v->fails()) {
            $_SESSION['errors'] = $v->firstErrors();
            $_SESSION['old'] = $data;
            Router::redirect('/register/candidate');
        }

        $userId = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'candidate',
            'status' => 'approved',
            'source' => 'self_registered',
        ]);

        $user = User::findById($userId);
        Auth::login($user);
        View::flash('success', 'Welcome to TalentFit! Build your profile to start applying.');
        Router::redirect('/candidate/profile');
    }

    public function showRegisterRecruiter(): void
    {
        if (Auth::check()) {
            Router::redirect(Auth::dashboardPath());
        }
        View::render('auth/register_recruiter');
    }

    public function registerRecruiter(): void
    {
        if (!Csrf::verifyRequest()) {
            Router::redirect('/register/recruiter');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'company_name' => trim($_POST['company_name'] ?? ''),
            'work_email_domain' => trim($_POST['work_email_domain'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('name')->required('email')->email('email')->unique('email', 'users', 'email')
            ->required('password')->minLength('password', 8)
            ->matches('password_confirm', 'password', 'Passwords do not match.')
            ->required('company_name');

        if ($v->fails()) {
            $_SESSION['errors'] = $v->firstErrors();
            $_SESSION['old'] = $data;
            Router::redirect('/register/recruiter');
        }

        $userId = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'recruiter',
            'status' => 'pending',
            'source' => 'self_registered',
        ]);

        RecruiterProfile::create($userId, [
            'company_name' => $data['company_name'],
            'work_email_domain' => $data['work_email_domain'] ?: null,
            'contact_name' => $data['name'],
            'contact_phone' => $data['contact_phone'] ?: null,
        ]);

        View::flash('success', 'Registration received. Your company account is pending admin approval — you will be able to log in once approved.');
        Router::redirect('/login');
    }

    public function showForgotPassword(): void
    {
        View::render('auth/forgot_password');
    }

    public function forgotPassword(): void
    {
        if (!Csrf::verifyRequest()) {
            Router::redirect('/forgot-password');
        }

        $email = trim($_POST['email'] ?? '');
        $user = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            User::setResetToken((int) $user['id'], $token, $expires);

            $link = Router::url('/reset-password/' . $token);
            Mailer::send(
                $user['email'],
                $user['name'],
                'TalentFit — Reset your password',
                "<p>Hi " . e($user['name']) . ",</p><p>Click the link below to reset your password (valid for 1 hour):</p><p><a href=\"{$link}\">{$link}</a></p>"
            );
        }

        View::flash('success', 'If that email is registered, a reset link has been sent.');
        Router::redirect('/login');
    }

    public function showResetPassword(string $token): void
    {
        $user = User::findByValidResetToken($token);
        if (!$user) {
            View::flash('error', 'That reset link is invalid or has expired.');
            Router::redirect('/forgot-password');
        }
        View::render('auth/reset_password', ['token' => $token]);
    }

    public function resetPassword(): void
    {
        if (!Csrf::verifyRequest()) {
            Router::redirect('/forgot-password');
        }

        $token = $_POST['token'] ?? '';
        $user = User::findByValidResetToken($token);
        if (!$user) {
            View::flash('error', 'That reset link is invalid or has expired.');
            Router::redirect('/forgot-password');
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        if (strlen($password) < 8 || $password !== $confirm) {
            View::flash('error', 'Passwords must match and be at least 8 characters.');
            Router::redirect('/reset-password/' . $token);
        }

        User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        User::clearResetToken((int) $user['id']);

        View::flash('success', 'Password updated. You can now log in.');
        Router::redirect('/login');
    }
}
