<?php

namespace App\Service;

use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Psr\Log\LoggerInterface;

class SmsService
{
    private TexterInterface $texter;
    private LoggerInterface $logger;

    public function __construct(TexterInterface $texter, LoggerInterface $logger)
    {
        $this->texter = $texter;
        $this->logger = $logger;
    }

    public function sendRendezVousConfirmation(string $numero, string $nomClient, string $date, string $lieu): void
    {
        // Nettoyage basique du numéro : on enlève les espaces éventuels
        $numero = str_replace(' ', '', $numero);

        $messageContent = sprintf(
            "Bonjour %s, votre RDV de don de sang est confirmé pour le %s au centre %s. Merci pour votre engagement avec BloodLink !",
            $nomClient,
            $date,
            $lieu
        );

        $sms = new SmsMessage(
            $numero,
            $messageContent
        );

        try {
            $this->texter->send($sms);
            $this->logger->info("SMS envoyé avec succès à " . $numero);
        } catch (\Exception $e) {
            // On log l'erreur pour ne pas bloquer l'application si l'envoi échoue
            $this->logger->error("Erreur d'envoi SMS Twilio : " . $e->getMessage());
        }
    }
}