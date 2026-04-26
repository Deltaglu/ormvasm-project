<?php
namespace Database\Seeders;

use App\Models\Agriculteur;
use App\Models\Prestation;
use App\Models\Setting;
use App\Models\TitreRecette;
use App\Models\User;
use App\Services\PaiementService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantAdminSeeder extends Seeder
{
    public function run(): void
    {
        Setting::current()->update(['penalty_percentage' => 5]);

        User::query()->updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'agent@test.com'],
            [
                'name' => 'Agent ORMVASM',
                'password' => Hash::make('password'),
                'role' => User::ROLE_AGENT,
            ]
        );

        $agriculteur = Agriculteur::query()->updateOrCreate(
            ['cin' => 'EE123456'],
            [
                'nom' => 'El Fassi',
                'prenom' => 'Mounir',
                'adresse' => 'Douar Al Amal',
                'telephone' => '0611223344',
                'email' => 'mounir@example.test',
            ]
        );

        Agriculteur::query()->updateOrCreate(
            ['cin' => 'FF789012'],
            [
                'nom' => 'Berrada',
                'prenom' => 'Salma',
                'adresse' => 'Centre agricole',
                'telephone' => '0677889900',
                'email' => 'salma@example.test',
            ]
        );

        Prestation::query()->updateOrCreate(
            ['code' => 'PR-ETUDE'],
            [
                'libelle' => 'Etude dossier',
                'tarif' => 1500,
                'unite' => 'dossier',
                'description' => 'Frais de traitement administratif.',
            ]
        );

        Prestation::query()->updateOrCreate(
            ['code' => 'PR-CONTROLE'],
            [
                'libelle' => 'Controle terrain',
                'tarif' => 2500,
                'unite' => 'mission',
                'description' => 'Controle et validation sur site.',
            ]
        );

        $titre = TitreRecette::query()->updateOrCreate(
            ['numero' => 'TR-2026-0001'],
            [
                'date_emission' => now()->toDateString(),
                'date_echeance' => now()->subDays(7)->toDateString(),
                'montant_total' => 12000,
                'montant_paye' => 0,
                'solde_restant' => 12000,
                'montant_penalite' => 0,
                'penalite_appliquee' => false,
                'statut' => 'PARTIEL',
                'objet' => 'Recouvrement frais de prestations',
                'agriculteur_id' => $agriculteur->id,
            ]
        );

        if (! $titre->paiements()->exists()) {
            app(PaiementService::class)->create([
                'reference' => 'PAY-2026-0001',
                'date_paiement' => now()->toDateString(),
                'montant' => 3000,
                'type_paiement' => 'ESPECES',
                'statut' => 'VALIDE',
                'numero_cheque' => null,
                'titre_recette_id' => $titre->id,
            ]);
        }

        $titre->refresh()->calculatePenalty();
    }
}

