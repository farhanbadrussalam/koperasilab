<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class dashboardSkeletonController extends Controller
{
    public function summaryCards() {
        return response()->json(['html' => view('components.dashboard.skeleton.summary-skeleton')->render()]);
    }

    public function statisticsLayanan() {
        return response()->json(['html' => view('components.dashboard.skeleton.service-chart-skeleton')->render()]);
    }

    public function deliveryStats() {
        return response()->json(['html' => view('components.dashboard.skeleton.delivery-stats-skeleton')->render()]);
    }

    public function jobsPenyelia() {
        return response()->json(['html' => view('components.dashboard.skeleton.jobs-penyelia-skeleton')->render()]);
    }

    public function monitorPenyeliaan() {
        return response()->json(['html' => view('components.dashboard.skeleton.service-chart-skeleton')->render()]);
    }

    public function myJobsList() {
        return response()->json(['html' => view('components.dashboard.skeleton.my-jobs-skeleton')->render()]);
    }

    public function financeChartService() {
        return response()->json(['html' => view('components.dashboard.skeleton.service-chart-skeleton')->render()]);
    }
}
