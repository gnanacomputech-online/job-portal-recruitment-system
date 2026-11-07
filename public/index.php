<?php

// When running under `php -S` for local testing (not Apache/XAMPP), let the
// built-in server serve real files (assets, uploads) directly instead of
// routing them through this front controller.
if (PHP_SAPI === 'cli-server') {
    $requested = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($requested !== __DIR__ . '/' && is_file($requested)) {
        return false;
    }
}

require __DIR__ . '/../core/bootstrap.php';

use App\Controllers\AccountController;
use App\Controllers\AdminApplicationController;
use App\Controllers\AdminCandidateController;
use App\Controllers\AdminDashboardController;
use App\Controllers\AdminJobController;
use App\Controllers\AdminRecruiterController;
use App\Controllers\AdminSkillController;
use App\Controllers\AuthController;
use App\Controllers\CandidateApplicationController;
use App\Controllers\CandidateDashboardController;
use App\Controllers\CandidateProfileController;
use App\Controllers\HomeController;
use App\Controllers\JobController;
use App\Controllers\RecruiterApplicantController;
use App\Controllers\RecruiterCandidateSearchController;
use App\Controllers\RecruiterCompanyController;
use App\Controllers\RecruiterDashboardController;
use App\Controllers\RecruiterJobController;
use App\Controllers\SkillRequestController;
use Core\Router;

$router = new Router();

// ---- Public / home -------------------------------------------------------
$router->get('/', [HomeController::class, 'index']);
$router->get('/jobs', [JobController::class, 'browse']);
$router->get('/jobs/{id}', [JobController::class, 'show']);
$router->get('/companies/{id}', [JobController::class, 'company']);

// ---- Auth ------------------------------------------------------------------
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/register', [AuthController::class, 'showRegisterChoice']);
$router->get('/register/candidate', [AuthController::class, 'showRegisterCandidate']);
$router->post('/register/candidate', [AuthController::class, 'registerCandidate']);
$router->get('/register/recruiter', [AuthController::class, 'showRegisterRecruiter']);
$router->post('/register/recruiter', [AuthController::class, 'registerRecruiter']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/reset-password/{token}', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

// ---- Account (any logged-in role) -----------------------------------------
$router->get('/account', [AccountController::class, 'show']);
$router->post('/account', [AccountController::class, 'updateProfile']);
$router->post('/account/password', [AccountController::class, 'updatePassword']);

// ---- Shared skill-request submission (recruiter + candidate) --------------
$router->post('/skill-requests', [SkillRequestController::class, 'submit']);

// ---- Admin ------------------------------------------------------------------
$router->get('/admin/dashboard', [AdminDashboardController::class, 'index']);

$router->get('/admin/recruiters', [AdminRecruiterController::class, 'index']);
$router->get('/admin/recruiters/new', [AdminRecruiterController::class, 'showNew']);
$router->post('/admin/recruiters', [AdminRecruiterController::class, 'create']);
$router->get('/admin/recruiters/import', [AdminRecruiterController::class, 'showImport']);
$router->post('/admin/recruiters/import', [AdminRecruiterController::class, 'import']);
$router->get('/admin/recruiters/{id}/edit', [AdminRecruiterController::class, 'showEdit']);
$router->post('/admin/recruiters/{id}', [AdminRecruiterController::class, 'update']);
$router->post('/admin/recruiters/{id}/approve', [AdminRecruiterController::class, 'approve']);
$router->post('/admin/recruiters/{id}/reject', [AdminRecruiterController::class, 'reject']);
$router->post('/admin/recruiters/{id}/suspend', [AdminRecruiterController::class, 'suspend']);
$router->post('/admin/recruiters/{id}/unsuspend', [AdminRecruiterController::class, 'unsuspend']);
$router->post('/admin/recruiters/{id}/delete', [AdminRecruiterController::class, 'delete']);

$router->get('/admin/candidates', [AdminCandidateController::class, 'index']);
$router->post('/admin/candidates/{id}/suspend', [AdminCandidateController::class, 'suspend']);
$router->post('/admin/candidates/{id}/unsuspend', [AdminCandidateController::class, 'unsuspend']);

$router->get('/admin/jobs', [AdminJobController::class, 'index']);
$router->post('/admin/jobs/{id}/flag', [AdminJobController::class, 'flag']);
$router->post('/admin/jobs/{id}/remove', [AdminJobController::class, 'remove']);
$router->post('/admin/jobs/{id}/restore', [AdminJobController::class, 'restore']);

$router->get('/admin/skills', [AdminSkillController::class, 'index']);
$router->post('/admin/skills', [AdminSkillController::class, 'create']);
$router->post('/admin/skills/{id}/delete', [AdminSkillController::class, 'delete']);
$router->post('/admin/skill-requests/{id}/approve', [AdminSkillController::class, 'approveRequest']);
$router->post('/admin/skill-requests/{id}/reject', [AdminSkillController::class, 'rejectRequest']);

$router->get('/admin/applications', [AdminApplicationController::class, 'index']);
$router->get('/admin/applications/{id}', [AdminApplicationController::class, 'show']);

// ---- Recruiter --------------------------------------------------------------
$router->get('/recruiter/dashboard', [RecruiterDashboardController::class, 'index']);

$router->get('/recruiter/jobs', [RecruiterJobController::class, 'index']);
$router->get('/recruiter/jobs/new', [RecruiterJobController::class, 'showNew']);
$router->post('/recruiter/jobs', [RecruiterJobController::class, 'create']);
$router->get('/recruiter/jobs/{id}/edit', [RecruiterJobController::class, 'showEdit']);
$router->post('/recruiter/jobs/{id}', [RecruiterJobController::class, 'update']);
$router->post('/recruiter/jobs/{id}/close', [RecruiterJobController::class, 'close']);
$router->post('/recruiter/jobs/{id}/reopen', [RecruiterJobController::class, 'reopen']);
$router->post('/recruiter/jobs/{id}/delete', [RecruiterJobController::class, 'delete']);
$router->get('/recruiter/jobs/{id}/applicants', [RecruiterApplicantController::class, 'index']);

$router->get('/recruiter/applications/{id}', [RecruiterApplicantController::class, 'show']);
$router->post('/recruiter/applications/{id}/status', [RecruiterApplicantController::class, 'updateStatus']);

$router->get('/recruiter/candidates', [RecruiterCandidateSearchController::class, 'index']);
$router->get('/recruiter/candidates/{id}', [RecruiterCandidateSearchController::class, 'preview']);

$router->get('/recruiter/company', [RecruiterCompanyController::class, 'show']);
$router->post('/recruiter/company', [RecruiterCompanyController::class, 'update']);

// ---- Candidate ----------------------------------------------------------------
$router->get('/candidate/dashboard', [CandidateDashboardController::class, 'index']);

$router->get('/candidate/profile', [CandidateProfileController::class, 'show']);
$router->post('/candidate/profile', [CandidateProfileController::class, 'updateBasics']);
$router->post('/candidate/profile/open-to-work', [CandidateProfileController::class, 'toggleOpenToWork']);
$router->post('/candidate/profile/resume', [CandidateProfileController::class, 'uploadResume']);
$router->post('/candidate/profile/photo', [CandidateProfileController::class, 'uploadPhoto']);
$router->post('/candidate/profile/experience', [CandidateProfileController::class, 'addExperience']);
$router->post('/candidate/profile/experience/{id}/delete', [CandidateProfileController::class, 'deleteExperience']);
$router->post('/candidate/profile/education', [CandidateProfileController::class, 'addEducation']);
$router->post('/candidate/profile/education/{id}/delete', [CandidateProfileController::class, 'deleteEducation']);
$router->post('/candidate/profile/skills', [CandidateProfileController::class, 'addSkill']);
$router->post('/candidate/profile/skills/{id}/delete', [CandidateProfileController::class, 'removeSkill']);

$router->get('/candidate/saved-jobs', [CandidateApplicationController::class, 'savedIndex']);
$router->post('/jobs/{id}/save', [CandidateApplicationController::class, 'save']);
$router->post('/jobs/{id}/unsave', [CandidateApplicationController::class, 'unsave']);
$router->post('/jobs/{id}/apply', [CandidateApplicationController::class, 'apply']);

$router->get('/candidate/applications', [CandidateApplicationController::class, 'index']);
$router->get('/candidate/applications/{id}', [CandidateApplicationController::class, 'show']);
$router->post('/candidate/applications/{id}/withdraw', [CandidateApplicationController::class, 'withdraw']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
