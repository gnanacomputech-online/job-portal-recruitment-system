<?php
use Core\Auth;
use Core\Router;
use Core\View;
$currentUser = Auth::user();
$role = $currentUser['role'] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TalentFit — Job Portal &amp; Recruitment System</title>
    <link rel="icon" href="data:,">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#1D2140',
                        void: '#100F24',
                        indigo: { DEFAULT: '#4536D1', dark: '#372AA8', 50: '#EEECFB' },
                        canvas: '#F5F6FB',
                        line: '#E3E5F1',
                        mint: '#17A672',
                        amber: '#B9791A',
                        rose: '#C93A54',
                        slateink: '#5B6382',
                    },
                    fontFamily: {
                        display: ['"Space Grotesk"', 'sans-serif'],
                        body: ['"IBM Plex Sans"', 'sans-serif'],
                        mono: ['"IBM Plex Mono"', 'monospace'],
                    },
                },
            },
        };
    </script>
    <link href="<?= Router::url('/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="font-body">
<nav class="tf-nav">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
        <a href="<?= Router::url($currentUser ? Auth::dashboardPath() : '/') ?>" class="flex items-center gap-2.5">
            <span class="brand-mark">
                <svg width="18" height="18" viewBox="0 0 36 36" fill="none">
                    <circle cx="18" cy="18" r="14" stroke="rgba(255,255,255,0.35)" stroke-width="4"/>
                    <path d="M18 4a14 14 0 0 1 9.9 23.9" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="font-display font-semibold text-white text-lg tracking-tight">TalentFit</span>
        </a>
        <div class="hidden md:flex items-center gap-1 text-sm font-medium">
            <?php if ($role === 'admin'): ?>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/admin/dashboard') ?>">Dashboard</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/admin/recruiters') ?>">Recruiters</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/admin/candidates') ?>">Candidates</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/admin/jobs') ?>">Jobs</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/admin/skills') ?>">Skills</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/admin/applications') ?>">Applications</a>
            <?php elseif ($role === 'recruiter'): ?>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/recruiter/dashboard') ?>">Dashboard</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/recruiter/jobs') ?>">My Postings</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/recruiter/candidates') ?>">Candidate Search</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/recruiter/company') ?>">Company Profile</a>
            <?php elseif ($role === 'candidate'): ?>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/candidate/dashboard') ?>">Dashboard</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/jobs') ?>">Browse Jobs</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/candidate/saved-jobs') ?>">Saved</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/candidate/applications') ?>">My Applications</a>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/candidate/profile') ?>">Profile</a>
            <?php else: ?>
                <a class="nav-link px-3 py-2" href="<?= Router::url('/jobs') ?>">Browse Jobs</a>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($currentUser): ?>
                <a href="<?= Router::url('/account') ?>" class="hidden sm:inline text-sm text-white/70 hover:text-white"><?= e($currentUser['name']) ?></a>
                <form method="post" action="<?= Router::url('/logout') ?>">
                    <?= \Core\Csrf::field() ?>
                    <button type="submit" class="btn-secondary btn-sm !bg-transparent !text-white/80 !border-white/20 hover:!text-white">Logout</button>
                </form>
            <?php else: ?>
                <a href="<?= Router::url('/login') ?>" class="text-sm font-medium text-white/80 hover:text-white">Login</a>
                <a href="<?= Router::url('/register') ?>" class="btn-primary btn-sm">Get started</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="max-w-6xl mx-auto px-4 py-8">
    <?php $flashSuccess = View::flash('success'); $flashError = View::flash('error'); ?>
    <?php if ($flashSuccess): ?>
        <div class="mb-5 rounded-lg border border-mint/30 bg-mint/10 text-mint px-4 py-3 text-sm font-medium"><?= e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="mb-5 rounded-lg border border-rose/30 bg-rose/10 text-rose px-4 py-3 text-sm font-medium"><?= e($flashError) ?></div>
    <?php endif; ?>
