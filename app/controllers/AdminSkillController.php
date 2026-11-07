<?php

namespace App\Controllers;

use App\Models\Skill;
use App\Models\SkillRequest;
use Core\Auth;
use Core\Csrf;
use Core\Router;
use Core\View;

class AdminSkillController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        View::render('admin/skills/index', [
            'grouped' => Skill::grouped(),
            'requests' => SkillRequest::listAll(),
            'usage' => Skill::usageMap(),
        ]);
    }

    public function create(): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/skills');
        }

        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        if ($name === '' || $category === '') {
            View::flash('error', 'Name and category are required.');
        } elseif (Skill::nameExists($name)) {
            View::flash('error', 'That skill already exists.');
        } else {
            Skill::create($name, $category);
            View::flash('success', 'Skill added to the taxonomy.');
        }
        Router::redirect('/admin/skills');
    }

    public function delete(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/skills');
        }
        $skill = Skill::find($id);
        if (!$skill) {
            Router::redirect('/admin/skills');
        }

        // `job_skills` / `candidate_skills` both cascade on delete, so removing
        // a skill that's in use would silently rewrite postings' requirements
        // and candidates' profiles — and every stored application's
        // score_breakdown would then reference a requirement the job no longer
        // has. An in-use skill stays in the taxonomy.
        $usage = Skill::usage($id);
        if ($usage['total'] > 0) {
            View::flash('error', sprintf(
                '"%s" is in use by %d job requirement(s) and %d candidate profile(s) — it can\'t be removed without rewriting those postings and profiles. Remove it from them first.',
                $skill['name'],
                $usage['jobs'],
                $usage['candidates']
            ));
            Router::redirect('/admin/skills');
        }

        Skill::delete($id);
        View::flash('success', 'Skill removed from the taxonomy.');
        Router::redirect('/admin/skills');
    }

    public function approveRequest(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/skills');
        }

        $request = SkillRequest::find($id);
        if ($request && $request['status'] === 'pending') {
            if (!Skill::nameExists($request['name'])) {
                Skill::create($request['name'], $request['category']);
            }
            SkillRequest::approve($id, Auth::id());
            View::flash('success', 'Skill request approved and added to the taxonomy.');
        }
        Router::redirect('/admin/skills');
    }

    public function rejectRequest(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/skills');
        }

        $request = SkillRequest::find($id);
        if ($request && $request['status'] === 'pending') {
            SkillRequest::reject($id, Auth::id());
            View::flash('success', 'Skill request rejected.');
        }
        Router::redirect('/admin/skills');
    }
}
