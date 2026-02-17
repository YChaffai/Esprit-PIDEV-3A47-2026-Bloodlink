<?php
namespace App\Service;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class GoogleCalendarService
{
    private $client;
    private $service;

    public function __construct(string $googleCredentialsPath)
    {
        // Créer un client Google pour le compte de service
        $this->client = new Google_Client();
        $this->client->setApplicationName('Google Calendar API Test');
        $this->client->setScopes(Google_Service_Calendar::CALENDAR);
        $this->client->setAuthConfig($googleCredentialsPath);  // Utiliser le chemin de la clé JSON
        $this->client->setAccessType('offline');  // Si vous avez besoin d'un refresh token

        // Créer un service Google Calendar
        $this->service = new Google_Service_Calendar($this->client);
    }

    // Méthode pour ajouter un événement à Google Calendar
    public function addEvent(string $summary, string $location, string $description, \DateTime $start, \DateTime $end)
    {
        $event = new Google_Service_Calendar_Event([
            'summary' => $summary,
            'location' => $location,
            'description' => $description,
            'start' => [
                'dateTime' => $start->format('Y-m-d\TH:i:s'),
                'timeZone' => 'Europe/Paris',
            ],
            'end' => [
                'dateTime' => $end->format('Y-m-d\TH:i:s'),
                'timeZone' => 'Europe/Paris',
            ],
        ]);

        // Utiliser le calendrier principal du compte de service
        $calendarId = 'abbec9cf9900518b282f01c09889bdf413cea87d79ddf7fc132a9bb6e44f5ef6@group.calendar.google.com';  // Le calendrier par défaut du compte de service
        $event = $this->service->events->insert($calendarId, $event);

        // Retourner le lien vers l'événement ajouté
        return $event->htmlLink;
    }
}
