<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiReportService
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client, string $geminiApiKey)
    {
        $this->client = $client;
        $this->apiKey = $geminiApiKey;
    }

    public function generateReport(array $stats, ?string $feedback = null): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;
// Si pas de données, on ne sollicite même pas l'API inutilement
    if ($stats['total_rendez_vous'] === 0 && !$feedback) {
        return "# RÉSUMÉ EXÉCUTIF\nAucune donnée disponible pour cette date.\n# ANALYSE DES STOCKS\nVisibilité nulle.\n# PLAN D'ACTION\nRelancer les collectes.";
    }
        // On prépare le contexte des données
        $jsonStats = json_encode($stats);
        
        // Construction du prompt intelligent
        $prompt = "Agis en tant qu'expert BloodLink. Analyse ces données : $jsonStats. \n";
        
        if ($feedback) {
            // $prompt .= "L'utilisateur souhaite modifier le rapport précédent avec cette consigne : $feedback. Réponds directement en intégrant ce changement.";
            $prompt .= "Consigne de modification : '$feedback'. \n";
            $prompt .= "Génère un nouveau rapport complet en intégrant cette consigne tout en gardant les sections : # RÉSUMÉ EXÉCUTIF, # ANALYSE DES STOCKS et # PLAN D'ACTION.";
        } else {
            $prompt .= "Structure ton rendu en Markdown :
            # RÉSUMÉ EXÉCUTIF (Analyse âge/poids)
            # ANALYSE DES STOCKS (Répartition groupes sanguins)
            # PLAN D'ACTION (Actions concrètes)";
        }

        $response = $this->client->request('POST', $url, [
            'json' => [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]
        ]);

        $result = $response->toArray();
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Erreur lors de la génération.";
    }
}