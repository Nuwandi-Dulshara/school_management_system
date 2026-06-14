<?php

namespace App\Http\Controllers;

use App\Services\NoticeVisibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly NoticeVisibilityService $notices) {}

    public function superAdmin(Request $request): View
    {
        return $this->dashboard($request, 'dashboards.super_admin');
    }

    public function admin(Request $request): View
    {
        return $this->dashboard($request, 'dashboards.admin');
    }

    public function teacher(Request $request): View
    {
        return $this->dashboard($request, 'dashboards.teacher');
    }

    public function student(Request $request): View
    {
        return $this->dashboard($request, 'dashboards.student');
    }

    private function dashboard(Request $request, string $view): View
    {
        $dashboardNotices = $this->notices->visiblePublishedTo($request->user())
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'important' THEN 2 ELSE 3 END")
            ->latest('publish_date')
            ->limit(5)
            ->get();

        return view($view, compact('dashboardNotices'));
    }
}
