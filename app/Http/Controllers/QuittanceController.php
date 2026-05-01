<?php
namespace App\Http\Controllers;

use App\Models\Quittance;
use App\Services\PaiementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QuittanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Quittance::query()->with(['paiement.titreRecette.agriculteur']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $terms = explode(' ', trim($search));
            foreach ($terms as $term) {
                if (!empty($term)) {
                    $query->where(function($q) use ($term) {
                        $q->where('numero', 'like', '%' . $term . '%')
                          ->orWhereHas('paiement', function($q) use ($term) {
                              $q->where('reference', 'like', '%' . $term . '%')
                                ->orWhereHas('titreRecette', function($q) use ($term) {
                                    $q->where('numero', 'like', '%' . $term . '%')
                                      ->orWhereHas('agriculteur', function($q) use ($term) {
                                          $q->where('nom', 'like', '%' . $term . '%')
                                            ->orWhere('prenom', 'like', '%' . $term . '%')
                                            ->orWhere('cin', 'like', '%' . $term . '%');
                                      });
                                });
                          });
                    });
                }
            }
        }
        
        $quittances = $query->latest()->paginate(15)->appends($request->all());
        
        // Get quittances from last 10 days for RG8 export
        $rg8Quittances = Quittance::with(['paiement.titreRecette.agriculteur'])
            ->whereHas('paiement', function($q) {
                $q->where('date_paiement', '>=', now()->subDays(10));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('quittances.index', compact('quittances', 'rg8Quittances'));
    }

    public function show(Quittance $quittance): View
    {
        $quittance->load(['paiement.titreRecette.agriculteur']);

        return view('quittances.show', compact('quittance'));
    }

    public function pdf(Quittance $quittance)
    {
        // Regenerate PDF if missing
        if (!$quittance->chemin_pdf || !Storage::disk('public')->exists($quittance->chemin_pdf)) {
            $quittance = app(PaiementService::class)->regenerateQuittance($quittance->paiement, $quittance);
        }

        abort_unless($quittance->chemin_pdf && Storage::disk('public')->exists($quittance->chemin_pdf), 404);

        return Storage::disk('public')->response($quittance->chemin_pdf, 'quittance-'.$quittance->numero.'.pdf', ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="quittance-'.$quittance->numero.'.pdf"']);
    }

    public function download(Quittance $quittance)
    {
        if (!$quittance->chemin_pdf || !Storage::disk('public')->exists($quittance->chemin_pdf)) {
            $quittance = app(PaiementService::class)->regenerateQuittance($quittance->paiement, $quittance);
        }

        abort_unless($quittance->chemin_pdf && Storage::disk('public')->exists($quittance->chemin_pdf), 404);

        return Storage::disk('public')->download($quittance->chemin_pdf, 'quittance-'.$quittance->numero.'.pdf');
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $terms = explode(' ', trim($query));
        $resultsQuery = Quittance::query()->with(['paiement.titreRecette.agriculteur']);

        foreach ($terms as $term) {
            if (!empty($term)) {
                $resultsQuery->where(function($q) use ($term) {
                    $q->where('numero', 'like', '%' . $term . '%')
                      ->orWhereHas('paiement', function($q) use ($term) {
                          $q->where('reference', 'like', '%' . $term . '%')
                            ->orWhereHas('titreRecette', function($q) use ($term) {
                                $q->where('numero', 'like', '%' . $term . '%')
                                  ->orWhereHas('agriculteur', function($q) use ($term) {
                                      $q->where('nom', 'like', '%' . $term . '%')
                                        ->orWhere('prenom', 'like', '%' . $term . '%')
                                        ->orWhere('cin', 'like', '%' . $term . '%');
                                  });
                            });
                      });
                });
            }
        }

        $results = $resultsQuery->limit(10)->get(['id', 'numero', 'paiement_id']);

        return response()->json($results);
    }
    
    public function rg8()
    {
        $quittances = Quittance::with(['paiement.titreRecette.agriculteur'])
            ->whereHas('paiement', function($q) {
                $q->where('date_paiement', '>=', now()->subDays(10));
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        // If no quittances, return empty PDF
        if ($quittances->isEmpty()) {
            $pdf = Pdf::loadView('quittances.rg8', ['quittances' => $quittances, 'total' => 0, 'periodStart' => now()->subDays(10)->format('d/m/Y'), 'periodEnd' => now()->format('d/m/Y')]);
            return $pdf->stream('RG8_Quittances_' . now()->format('Y-m-d') . '.pdf');
        }

        // Initialize PDF merger
        $pdfMerger = new \setasign\Fpdi\Tcpdf\Fpdi();
        
        foreach ($quittances as $quittance) {
            // Regenerate PDF if missing
            if (!$quittance->chemin_pdf || !Storage::disk('public')->exists($quittance->chemin_pdf)) {
                $quittance = app(PaiementService::class)->regenerateQuittance($quittance->paiement, $quittance);
            }
            
            // Add PDF to merger if it exists
            if ($quittance->chemin_pdf && Storage::disk('public')->exists($quittance->chemin_pdf)) {
                $pdfPath = Storage::disk('public')->path($quittance->chemin_pdf);
                $pageCount = $pdfMerger->setSourceFile($pdfPath);
                
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdfMerger->importPage($pageNo);
                    $size = $pdfMerger->getTemplateSize($templateId);
                    
                    // Add page with appropriate orientation
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $pdfMerger->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdfMerger->useTemplate($templateId);
                }
            }
        }
        
        // Generate output filename
        $filename = 'RG8_Quittances_' . now()->format('Y-m-d') . '.pdf';
        
        // Stream the merged PDF
        return response($pdfMerger->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }
}