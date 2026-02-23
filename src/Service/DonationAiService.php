<?php

namespace App\Service;

use App\Entity\Client;
use App\Repository\DonRepository;
use App\Repository\DossierMedRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DonationAiService
{
    private DonRepository $donRepo;
    private DossierMedRepository $dossierRepo;
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(
        DonRepository $donRepo, 
        DossierMedRepository $dossierRepo,
        HttpClientInterface $httpClient,
        ParameterBagInterface $params
    ) {
        $this->donRepo = $donRepo;
        $this->dossierRepo = $dossierRepo;
        $this->httpClient = $httpClient;
        
        if (!$params->has('google_gemini_key')) {
             $this->apiKey = 'MISSING_KEY';
        } else {
             $this->apiKey = $params->get('google_gemini_key');
        }
    }

    public function getAiRecommendation(Client $client): array
    {
        // 1. Fetch the latest Medical File and last Donation
        $lastDon = $this->donRepo->findOneBy(['client' => $client], ['date' => 'DESC']);
        $dossier = $this->dossierRepo->findOneBy(['client' => $client], ['id' => 'DESC']);

        // 2. Calculate Biometrics (Weight/Height/BMI)
        $stats = $this->calculateMedicalStats($dossier);
        
        $info = [
            'nom' => $client->getUser() ? ucfirst($client->getUser()->getPrenom()) : 'Donneur',
            'type_sang' => $client->getTypeSang() ?? 'Inconnu',
            'genre' => $dossier ? $dossier->getSexe() : 'Non spécifié',
            'poids' => $stats['poids'],
            'taille' => $stats['taille'],
            'bmi' => $stats['bmi'],
            'date_du_jour' => (new \DateTime())->format('d/m/Y')
        ];

        // 3. Calculate the exact next Appointment Date
        $nextDate = $this->calculateNextDate($lastDon, $info['genre']);
        $isEligible = $nextDate <= new \DateTime();

        // 4. Generate the AI Response
        try {
            if ($this->apiKey === 'MISSING_KEY') throw new \Exception("Key Missing");
            
            $message = $this->callGeminiApi($info, $isEligible, $nextDate);
            $status = 'ai_success';
        } catch (\Exception $e) {
            // High-quality Fallback if API is offline
            $message = $this->getAdvancedFallback($info, $isEligible, $nextDate);
            $status = 'fallback'; 
        }

        return [
            'status' => $isEligible ? 'eligible' : 'wait',
            'date' => $nextDate,
            'message' => $message,
            'color' => $isEligible ? 'success' : 'warning',
            'icon' => 'fa-user-doctor'
        ];
    }

    private function callGeminiApi(array $info, bool $isEligible, \DateTime $nextDate): string
    {
        $targetDate = $isEligible ? $info['date_du_jour'] : $nextDate->format('d/m/Y');
        
        $prompt = sprintf(
            "Tu es l'assistant médical IA de BloodLink. Analyse les données suivantes :
            PATIENT : %s (Groupe %s)
            BIO : Poids %s kg, Taille %s cm, IMC %s.
            ELIGIBILITÉ : %s.
            PROCHAIN RDV POSSIBLE : %s.

            CONSIGNES :
            1. Si le poids est inférieur à 50kg, réponds : 'Attention %s, votre poids de %s kg est insuffisant pour un don sécurisé. Prenez soin de vous.'
            2. Si éligible, dis : 'Excellente forme ! Avec un IMC de %s, vous pouvez réserver votre créneau pour ce %s.'
            3. Si non-éligible, dis : 'Votre corps récupère. Pour votre profil %s, votre prochain rendez-vous est possible dès le %s.'
            
            Réponds en une seule phrase courte, médicale et personnalisée.",
            $info['nom'], $info['type_sang'], $info['poids'], $info['taille'], $info['bmi'],
            $isEligible ? "OUI" : "NON", $targetDate,
            $info['nom'], $info['poids'], $info['bmi'], $targetDate, $info['type_sang'], $targetDate
        );

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->apiKey;

        $response = $this->httpClient->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [ 'contents' => [[ 'parts' => [['text' => $prompt]] ]] ]
        ]);

        if ($response->getStatusCode() !== 200) throw new \Exception("API Error");

        $data = $response->toArray();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Analyse médicale indisponible.";
    }

    private function getAdvancedFallback(array $info, bool $isEligible, \DateTime $nextDate): string
    {
        $name = $info['nom'];
        $today = $info['date_du_jour'];
        $nextStr = $nextDate->format('d/m/Y');

        if ($info['poids'] !== 'N/A' && $info['poids'] < 50) {
            return "Désolé $name, avec {$info['poids']}kg, un don en ce $today est risqué. Veuillez attendre d'atteindre 50kg.";
        }

        if ($isEligible) {
            return "Bonjour $name, votre IMC de {$info['bmi']} est favorable. Vous pouvez planifier votre don pour ce $today.";
        }

        return "Merci $name. Votre corps récupère bien. Pour votre sécurité, votre prochain don est possible à partir du $nextStr.";
    }

    private function calculateMedicalStats(?\App\Entity\DossierMed $dossier): array
    {
        if (!$dossier) return ['poids' => 'N/A', 'taille' => 'N/A', 'bmi' => 'N/A'];

        $w = $dossier->getPoid();
        $h = $dossier->getTaille();
        $bmi = ($w > 0 && $h > 0) ? round($w / (($h / 100) ** 2), 1) : 'N/A';

        return ['poids' => $w, 'taille' => $h, 'bmi' => $bmi];
    }

    private function calculateNextDate(?\App\Entity\Don $lastDon, string $gender): \DateTime
    {
        if (!$lastDon) return new \DateTime(); 
        
        $days = 90; 
        $type = mb_strtolower($lastDon->getTypeDon());

        if (str_contains($type, 'sang') && $gender === 'Femme') $days = 120;
        if (str_contains($type, 'plaquette')) $days = 28;
        if (str_contains($type, 'plasma')) $days = 14;
        
        $date = clone $lastDon->getDate();
        return $date->modify("+$days days");
    }
}