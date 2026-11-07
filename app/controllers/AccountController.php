<?php

namespace App\Controllers;

use App\Models\User;
use Core\Auth;
use Core\Csrf;
use Core\Router;
use Core\Validator;
use Core\View;

/** "Manage own account" — shared across all three personas. */
class AccountController
{
    public function show(): void
    {
        Auth::requireLogin();
        View::render('account/show', ['user' => Auth::user()]);
    }

    public function updateProfile(): void
    {
        Auth::requireLogin();
        if (!Csrf::verifyRequest()) {
            Router::redirect('/account');
        }

        $user = Auth::user();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('name')->required('email')->email('email')
            ->unique('email', 'users', 'email', (int) $user['id']);

        if ($v->fails()) {
            $_SESSION['errors'] = $v->firstErrors();
            Router::redirect('/account');
        }

        User::updateProfile((int) $user['id'], $data['name'], $data['email']);
        Auth::refresh();
        View::flash('success', 'Account details updated.');
        Router::redirect('/account');
    }

    public function updatePassword(): void
    {
        Auth::requireLogin();
        if (!Csrf::verifyRequest()) {
            Router::redirect('/account');
        }

        $user = Auth::user();
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirm'] ?? '';

        if (!password_verify($current, (string) $user['password_hash'])) {
            View::flash('error', 'Current password is incorrect.');
            Router::redirect('/account');
        }
        if (strlen($new) < 8 || $new !== $confirm) {
            View::flash('error', 'New password must be at least 8 characters and match confirmation.');
            Router::redirect('/account');
        }

        User::updatePassword((int) $user['id'], password_hash($new, PASSWORD_DEFAULT));
        View::flash('success', 'Password changed.');
        Router::redirect('/account');
    }
}
