<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use DateTime;
use DateTimeZone;

class GoogleCalendarService
{
    protected $client;
    protected $calendarService;
    protected $calendarTimeZone = 'Asia/Makassar';

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('Your Application Name');
        $this->client->setScopes(Google_Service_Calendar::CALENDAR);
        $this->client->setAuthConfig(WRITEPATH . 'google/credentials.json');
        $this->client->setAccessType('offline');
        $this->client->setRedirectUri('http://localhost/clean/oauth2callback'); // Ensure this matches your redirect URI

        // Load previously authorized token from a file, if it exists.
        $tokenPath = WRITEPATH . 'google/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }

        // Refresh the token if it's expired.
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            } else {
                // Obtain a new token
                // You may need to redirect to the authorization URL and obtain a new token
                // This part is application-specific and might require user interaction
            }
            file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
        }

        $this->calendarService = new Google_Service_Calendar($this->client);
    }

    public function createEvent($calendarId, $eventData)
    {
        try {
            $event = new Google_Service_Calendar_Event($eventData);
            return $this->calendarService->events->insert($calendarId, $event);
        } catch (\Google\Service\Exception $e) {
            log_message('error', 'Error creating event: ' . $e->getMessage());
            throw new \RuntimeException('Error creating event: ' . $e->getMessage());
        }
    }

    public function getAvailableSlots($calendarId, $timeMin, $timeMax, $userTimeZone)
    {
        $events = $this->calendarService->events->listEvents($calendarId, [
            'timeMin' => $timeMin,
            'timeMax' => $timeMax,
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ]);

        $availableSlots = [];
        $slotDuration = 2 * 60 * 60; // 2 hours in seconds
        $currentTime = strtotime($timeMin);
        $endTime = strtotime($timeMax);

        while ($currentTime < $endTime) {
            $slotStart = date(DateTime::RFC3339, $currentTime);
            $slotEnd = date(DateTime::RFC3339, $currentTime + $slotDuration);

            $slotAvailable = true;
            foreach ($events->getItems() as $event) {
                $eventStart = strtotime($event->start->dateTime);
                $eventEnd = strtotime($event->end->dateTime);

                if (($currentTime >= $eventStart && $currentTime < $eventEnd) || 
                    ($currentTime + $slotDuration > $eventStart && $currentTime + $slotDuration <= $eventEnd)) {
                    $slotAvailable = false;
                    break;
                }
            }

            if ($slotAvailable) {
                // Convert slot times to user timezone and format as d-m-Y H:i:s
                $slotStartDT = new DateTime($slotStart, new DateTimeZone($this->calendarTimeZone));
                $slotEndDT = new DateTime($slotEnd, new DateTimeZone($this->calendarTimeZone));

                $slotStartDT->setTimezone(new DateTimeZone($userTimeZone));
                $slotEndDT->setTimezone(new DateTimeZone($userTimeZone));

                $availableSlots[] = [
                    'start' => $slotStartDT->format('d-m-Y H:i:s'),
                    'end' => $slotEndDT->format('d-m-Y H:i:s'),
                ];
            }

            $currentTime += $slotDuration;
        }

        return $availableSlots;
    }

    public function getSlotsNextDay($calendarId, $userTimeZone)
    {
        $currentDateTime = new DateTime('now', new DateTimeZone($this->calendarTimeZone));
        $nextDay = (clone $currentDateTime)->modify('+1 day')->setTime(0, 0);
        $endWeek = (clone $nextDay)->modify('+1 week');

        $timeMin = $nextDay->format(DateTime::RFC3339);
        $timeMax = $endWeek->format(DateTime::RFC3339);

        return $this->getAvailableSlots($calendarId, $timeMin, $timeMax, $userTimeZone);
    }
}
