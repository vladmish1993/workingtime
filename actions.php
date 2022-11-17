<?php

include('Interfaces\OpeningHoursInterface.php');
include('Classes\OpeningHours.php');

if(!empty($_GET) && array_key_exists('action', $_GET) && $_GET['action'] == 'get_timezones')
{
    $timeZones = [
        'Europe/London',
        'Europe/Paris',
        'Europe/Kiev',
        'Asia/Dubai',
        'Asia/Singapore',
        'Asia/Tokyo',
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Los_Angeles'
    ];

    echo json_encode($timeZones);
}
elseif(!empty($_GET) && array_key_exists('action', $_GET) && $_GET['action'] == 'get_work_time')
{
    $userTimezone = filter_var($_GET['userTimezone'], FILTER_SANITIZE_STRING);

    $currentDate = new \DateTime();
    $timeZone = new \DateTimeZone($userTimezone);
    $currentDate = $currentDate->setTimezone($timeZone);
    $openTime = new Classes\OpeningHours();
    $workingTime = $openTime->isOpen($currentDate);
    echo json_encode($workingTime, JSON_FORCE_OBJECT);
}
elseif(!empty($_GET) && array_key_exists('action', $_GET) && $_GET['action'] == 'get_open')
{
    $userTimezone = filter_var($_GET['userTimezone'], FILTER_SANITIZE_STRING);

    $currentDate = new \DateTime();
    $timeZone = new \DateTimeZone($userTimezone);
    $currentDate = $currentDate->setTimezone($timeZone);
    $openTime = new Classes\OpeningHours();
    $nextOpening = $openTime->nextOpening($currentDate);
    echo json_encode($nextOpening, JSON_FORCE_OBJECT);
}