<?php
namespace App\Service;

use App\Repository\CompagneRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DonorPredictionService
{
    public function __construct(
        private CompagneRepository $compagneRepository,
        private HttpClientInterface $client,
        private string $googleApiKey
    ) {}

    public function predictNextCampaignDonors(array $futureCampaignInfo = []): array
    {
        $history = $this->compagneRepository->getFinishedCampaignsWithDonorCount();

        if (empty($history)) {
            return [
                'prediction' => 0,
                'explanation' => "Aucune campagne terminée pour établir une prédiction.",
                'chartUrl' => null
            ];
        }

        $nbDonneurs = array_column($history, 'nb_donneurs');
        $moyenne = array_sum($nbDonneurs) / count($nbDonneurs);

        $prompt = "Voici l'historique des campagnes terminées et leur nombre de donneurs :\n";
        foreach ($history as $row) {
            $prompt .= "- {$row['titre']} ({$row['date_debut']->format('Y-m-d')} au {$row['date_fin']->format('Y-m-d')}): {$row['nb_donneurs']} donneurs\n";
        }

        if (!empty($futureCampaignInfo)) {
            $prompt .= "\nLa prochaine campagne aura lieu du {$futureCampaignInfo['date_debut']} au {$futureCampaignInfo['date_fin']}.";
        }

        $prompt .= "\nPrédire le nombre de donneurs attendus pour la prochaine campagne et expliquer brièvement la tendance.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->googleApiKey;

        $response = $this->client->request('POST', $url, [
            'json' => [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]
        ]);

        $result = $response->toArray();
        $iaPrediction = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // ✅ QuickChart
        $labels = array_map(function($row) {
            return $row['date_debut']->format('M Y');
        }, $history);

        $chartConfig = [
            "type" => "line",
            "data" => [
                "labels" => $labels,
                "datasets" => [[
                    "label" => "Nombre de donneurs",
                    "data" => $nbDonneurs,
                    "borderColor" => "#e63939",
                    "backgroundColor" => "rgba(230,57,57,0.2)",
                    "fill" => true,
                    "tension" => 0.4
                ]]
            ]
        ];

        $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig));

        return [
            'prediction' => round($moyenne),
            'explanation' => $iaPrediction,
            'chartUrl' => $chartUrl
        ];
    }
}