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

    public function generateReport(array $data): string
{// On récupère le feedback s'il existe
    $feedback = $data['user_instruction'] ?? null;
    $stats = $data['json_stats'];

    $prompt = "Tu es l'expert BloodLink. Voici les données actuelles : $stats. ";
    
    if ($feedback) {
        $prompt .= "\n\nIMPORTANT : L'utilisateur a une demande spécifique. Modifie ton analyse en suivant cette consigne : " . $feedback;
    } else {
        $prompt .= "\n\nGénère un rapport stratégique complet (Résumé, Stocks, Actions).";
    }


    
    $instruction = $data['user_instruction'] 
        ? "L'utilisateur souhaite modifier le rapport précédent avec cette consigne : " . $data['user_instruction']
        : "Génère une analyse initiale.";

    $prompt = "Données : " . $data['json_stats'] . "\n" .
              "Consigne : " . $instruction . "\n" .
              "Agis en expert BloodLink. Réponds en Markdown de manière concise.";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

    // Utilisation de <<<PROMPT pour éviter les erreurs de guillemets
    $prompt = <<<PROMPT
Agis en tant qu'expert BloodLink. Analyse UNIQUEMENT ces données : 
{$data['json_stats']}

Structure ton rendu pour une interface Canvas :
# 📝 RÉSUMÉ EXÉCUTIF
Analyse l'âge moyen et le poids des donneurs.

# 🩸 ANALYSE DES STOCKS
Analyse la répartition des groupes sanguins.

# 🚀 PLAN D'ACTION
Donne 2 actions concrètes basées sur les rendez-vous en attente.
PROMPT;

    $response = $this->client->request('POST', $url, [
        'json' => [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]
    ]);

    $result = $response->toArray();
    return $result['candidates'][0]['content']['parts'][0]['text'];
}
}