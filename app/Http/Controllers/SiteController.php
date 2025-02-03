<?php

namespace App\Http\Controllers;

use App\Services\SitesService;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function __construct(private SitesService $sitesService) {}
    public function getSites()
    {
        $result = $this->sitesService->getSites();
        dd('sites');
    }
}
