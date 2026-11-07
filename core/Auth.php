<?php

namespace Core;

class Auth
{
    private static ?array $userCache = null;
    private static bool $userLoaded = false;

    public static function attempt(string $email, string $password): array|false
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || $user['password_hash'] === null) {
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        if ($user['status'] !== 'approved') {
            return false;
        }

        self::login($user);
        return $user;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        self::$userCache = $user;
        self::$userLoaded = true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        session_regenerate_id(true);
        self::refresh();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function user(): ?array
    {
        if (self::$userLoaded) {
            return self::$userCache;
        }
        self::$userLoaded = true;

        if (empty($_SESSION['user_id'])) {
            return self::$userCache = null;
        }

        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;

        return self::$userCache = $user;
    }

    /** Clears the cached user row so a just-changed status/role is re-read on next access. */
    public static function refresh(): void
    {
        self::$userLoaded = false;
        self::$userCache = null;
    }

    /**
     * Status messages shown when a live session belongs to an account that is
     * no longer `approved` (suspended/rejected after logging in).
     */
    private const STATUS_MESSAGES = [
        'suspended' => 'Your account has been suspended by an administrator. Contact support if you think this is a mistake.',
        'rejected' => 'Your account registration was not approved, so you can no longer use TalentFit.',
        'pending' => 'Your account is awaiting administrator approval.',
    ];

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Router::redirect('/login');
        }

        // Login already refuses a non-approved account (::attempt), but the
        // status can change *while* a session is live — an admin suspending a
        // recruiter mid-session must not leave them able to keep posting jobs
        // or advancing applicants, and a suspended candidate must not be able
        // to keep applying/withdrawing. The user row is re-read from the DB on
        // every request, so re-check it here rather than trusting the session.
        $status = self::user()['status'] ?? null;
        if ($status !== 'approved') {
            self::logout();
            View::flash('error', self::STATUS_MESSAGES[$status] ?? 'Your account is no longer active.');
            Router::redirect('/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../app/views/errors/403.php';
            exit;
        }
    }

    public static function dashboardPath(): string
    {
        return match (self::role()) {
            'admin' => '/admin/dashboard',
            'recruiter' => '/recruiter/dashboard',
            'candidate' => '/candidate/dashboard',
            default => '/login',
        };
    }
}
