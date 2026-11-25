<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportDocumentController extends Controller
{
    public function download(Client $client)
    {
        $client->load(['kblis','credentials','onboardingItems','knowledgeBases','technicalChecklists']);
        $pdf = Pdf::loadView('exports.master-onboarding-pdf', compact('client'));
        return $pdf->download('Master-Onboarding-'.$client->brand_name.'.pdf');
    }
}
