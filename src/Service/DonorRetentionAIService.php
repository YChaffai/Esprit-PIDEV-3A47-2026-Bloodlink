<?php

namespace App\Service;

use App\Entity\DossierMed;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;

// 🔥 Le nouveau nom de ta classe
class DonorRetentionAIService
{
    /**
     * Analyse un dossier et prédit la probabilité de fidélité du donneur.
     */
    public function predictRetention(DossierMed $dossier, int $nombreDons): array
    {
        // 1. LES DONNÉES D'ENTRAÎNEMENT (Features)
        $samples = [
            [22, 65, 1, 5],
            [45, 90, 0, 1],
            [30, 75, 0, 10],
            [19, 52, 1, 1],
            [55, 80, 0, 2],
            [28, 68, 1, 4],
            [35, 85, 0, 1],
            [40, 70, 1, 8],
        ];

        // 2. LES LABELS
        $labels = [
            'Fidèle', 
            'Risque Abandon', 
            'Fidèle', 
            'Risque Abandon', 
            'Risque Abandon', 
            'Fidèle', 
            'Risque Abandon', 
            'Fidèle'
        ];

        // 3. ENTRAÎNEMENT DU MODÈLE
        $dataset = new Labeled($samples, $labels);
        $estimator = new KNearestNeighbors(3); 
        $estimator->train($dataset);

        // 4. ANALYSE DU PATIENT ACTUEL
        $sexeValeur = (strtolower($dossier->getSexe() ?? '') === 'femme') ? 1 : 0;
        $patientData = [
            $dossier->getAge() ?? 25,
            $dossier->getPoid() ?? 70,
            $sexeValeur,
            $nombreDons
        ];

        $unlabeled = new Unlabeled([$patientData]);

        // 5. PRÉDICTION & PROBABILITÉS
        $probabilites = $estimator->proba($unlabeled);
        $predictionLabel = $estimator->predict($unlabeled)[0];

        return [
            'prediction' => $predictionLabel,
            'confidence' => round($probabilites[0][$predictionLabel] * 100),
        ];
    }
}