<?php
namespace App\Services;

use App\Models\Paiement;
use App\Models\Quittance;
use App\Models\TitreRecette;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaiementService
{
    public function create(array $data): Paiement
    {
        return DB::connection('tenant')->transaction(function () use ($data) {
            $paiement = Paiement::query()->create($data);
            $titreRecette = TitreRecette::query()->with('agriculteur')->findOrFail($data['titre_recette_id']);

            $this->syncTitreRecetteTotals($titreRecette);
            $quittance = $this->generateQuittance($paiement->load('titreRecette.agriculteur'));

            return $paiement->load(['titreRecette.agriculteur', 'quittance'])->setRelation('quittance', $quittance);
        });
    }

    public function update(Paiement $paiement, array $data): Paiement
    {
        return DB::connection('tenant')->transaction(function () use ($paiement, $data) {
            $originalTitreId = $paiement->titre_recette_id;

            $paiement->update($data);
            $paiement->refresh();

            $this->syncTitreRecetteTotals(TitreRecette::query()->findOrFail($paiement->titre_recette_id));

            if ($originalTitreId !== $paiement->titre_recette_id) {
                $this->syncTitreRecetteTotals(TitreRecette::query()->findOrFail($originalTitreId));
            }

            if ($paiement->quittance) {
                $this->regenerateQuittance($paiement->load('titreRecette.agriculteur'));
            }

            return $paiement->load(['titreRecette.agriculteur', 'quittance']);
        });
    }

    public function delete(Paiement $paiement): void
    {
        DB::connection('tenant')->transaction(function () use ($paiement) {
            $titreRecetteId = $paiement->titre_recette_id;

            if ($paiement->quittance?->chemin_pdf) {
                Storage::disk('public')->delete($paiement->quittance->chemin_pdf);
            }

            $paiement->quittance()?->delete();
            $paiement->delete();

            $this->syncTitreRecetteTotals(TitreRecette::query()->findOrFail($titreRecetteId));
        });
    }

    private function syncTitreRecetteTotals(TitreRecette $titreRecette): void
    {
        $montantPaye = (float) $titreRecette->paiements()->sum('montant');
        $soldeRestant = max((float) $titreRecette->montant_total_avec_penalite - $montantPaye, 0);

        $titreRecette->update([
            'montant_paye' => $montantPaye,
            'solde_restant' => $soldeRestant,
            'statut' => $soldeRestant == 0.0 ? 'SOLDE' : 'PARTIEL',
        ]);

        $titreRecette->refresh()->calculatePenalty();
    }

    private function generateQuittance(Paiement $paiement): Quittance
    {
        $quittance = Quittance::query()->create([
            'numero' => $this->nextQuittanceNumber(),
            'date_generation' => now(),
            'chemin_pdf' => '',
            'montant_paye' => $paiement->montant,
            'paiement_id' => $paiement->id,
        ]);

        return $this->regenerateQuittance($paiement->load('titreRecette.agriculteur'), $quittance);
    }

    public function regenerateQuittance(Paiement $paiement, ?Quittance $quittance = null): Quittance
    {
        $quittance ??= $paiement->quittance()->firstOrFail();
        $pdfPath = 'quittances/'.$quittance->numero.'.pdf';

        $pdf = Pdf::loadView('quittances.pdf', [
            'quittance' => $quittance->load('paiement.titreRecette.agriculteur'),
        ]);

        Storage::disk('public')->put($pdfPath, $pdf->output());

        $quittance->update([
            'date_generation' => now(),
            'chemin_pdf' => $pdfPath,
            'montant_paye' => $paiement->montant,
        ]);

        return $quittance->fresh(['paiement.titreRecette.agriculteur']);
    }

    private function nextQuittanceNumber(): string
    {
        $prefix = 'QUIT-'.now()->format('Y').'-';
        $lastNumero = Quittance::withTrashed()
            ->where('numero', 'like', $prefix.'%')
            ->orderByDesc('numero')
            ->value('numero');

        $next = $lastNumero ? ((int) substr($lastNumero, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}