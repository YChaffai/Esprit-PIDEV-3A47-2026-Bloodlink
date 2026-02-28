<?php
namespace App\Service;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

class GoogleCalendarService
{
    private $client;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->client = new Client();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
    }

    public function addEvent(string $token, string $summary, string $location, string $description, \DateTime $start, \DateTime $end)
{
    $this->client->setAccessToken(['access_token' => $token, 'expires_in' => 3600]);
    $service = new Calendar($this->client);

    $event = new Event();
    $event->setSummary($summary);
    $event->setLocation($location);
    $event->setDescription($description);

    // Date de début
    $startDateTime = new \Google\Service\Calendar\EventDateTime();
    $startDateTime->setDateTime($start->format(\DateTime::RFC3339));
    $startDateTime->setTimeZone('Africa/Tunis');
    $event->setStart($startDateTime);

    // Date de fin
    $endDateTime = new \Google\Service\Calendar\EventDateTime();
    $endDateTime->setDateTime($end->format(\DateTime::RFC3339));
    $endDateTime->setTimeZone('Africa/Tunis');
    $event->setEnd($endDateTime);

    return $service->events->insert('primary', $event);
}
}