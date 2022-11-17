<?php
namespace Classes;
use Interfaces\OpeningHoursInterface;

date_default_timezone_set('Europe/Paris');

class OpeningHours implements OpeningHoursInterface
{
    public $openHours = [
        'Mon' => ['09:00' => '18:00'],
        'Tue' => ['09:00' => '18:00'],
        'Wed' => ['09:00' => '21:00'],
        'Thu' => ['09:00' => '21:00'],
        'Fri' => ['09:00' => '21:00'],
        'Sat' => ['09:00' => '15:00'],
        'Sun' => ['13:00' => '16:00'],
    ];

    public function isOpen(\DateTime $clientDate)
    {
        $workingTimeClient = [];

        $workingTimeLocal = $this->openHours;

        //Get current Weekday
        $currentDateLocal = new \DateTimeImmutable();
        $localWeekDay = $currentDateLocal->format('D');

        //Get the client timezone
        $clientTz = $clientDate->getTimezone();

        foreach ($workingTimeLocal as $day => $period) {

            if(empty($period))
            {
                $workingTimeClient[$day] = [
                    'workTime' => ['Weekend']
                ];
            }
            else
            {
                foreach ($period as $from => $to) {
                    $timeOpenFromLocal = new \DateTime($from);
                    $timeOpenFromClient = $timeOpenFromLocal->setTimezone($clientTz);
                    $timeOpenToLocal = new \DateTime($to);
                    $timeOpenToClient = $timeOpenToLocal->setTimezone($clientTz);

                    $workingTimeClient[$day] = [
                        'workTime' => [
                            'from' => $timeOpenFromClient->format('H:i'),
                            'to' => $timeOpenToClient->format('H:i')
                        ]
                    ];

                    if ($day == $localWeekDay) {
                        if ($currentDateLocal > $timeOpenFromLocal && $currentDateLocal < $timeOpenToLocal) {
                            $workingTimeClient[$day]['active'] = true;
                        }
                    }
                }
            }
        }

        return $workingTimeClient;
    }

    public function nextOpening(\DateTime $clientDate)
    {
        $workingTimeLocal = $this->openHours;

        //Get current Weekday
        $currentDateLocal = new \DateTimeImmutable();
        $localWeekDay = $currentDateLocal->format('D');

        //Get the client timezone
        $clientWeekDay = $clientDate->format('D');
        $clientTz = $clientDate->getTimezone();

        $nextOpeningClient = false;
        $isOpen = false;
        $willBeOpenTodayAt = false;

        //Check if shop is open now
        foreach ($workingTimeLocal as $day => $period) {
            foreach ($period as $from => $to) {
                if ($day == $localWeekDay) {

                    $timeOpenFromLocal = new \DateTime($from);
                    $timeOpenToLocal = new \DateTime($to);

                    if ($currentDateLocal > $timeOpenFromLocal && $currentDateLocal < $timeOpenToLocal) {
                        $isOpen = true;
                    }
                    elseif($currentDateLocal < $timeOpenFromLocal)
                    {
                        $willBeOpenTodayAt = $from;
                    }
                }
            }
        }

        //If it closed
        if(!$isOpen)
        {
            if($willBeOpenTodayAt)
            {
                $nextOpeningLocal = $currentDateLocal->modify($willBeOpenTodayAt);
            }
            else
            {
                //Find next opening day for the case if shop not working on weekends for example
                $nextWorkingDayKey = array_search($localWeekDay,array_keys($workingTimeLocal)) + 1;
                if(!array_key_exists($nextWorkingDayKey, array_values($workingTimeLocal)))
                {
                    $nextWorkingDayKey = 0;
                }

                $dayByKey = array_keys($workingTimeLocal)[$nextWorkingDayKey];
                $workTime = key($workingTimeLocal[$dayByKey]);

                $nextOpeningLocal = $currentDateLocal->modify('next '.$dayByKey.' '.$workTime);
            }

            $nextOpeningClient = $nextOpeningLocal->setTimezone($clientTz);

            $nextOpenClientText = ($clientWeekDay == $nextOpeningClient->format('D')) ? 'Today' : $nextOpeningClient->format('l');

            $nextOpeningClient =
                [
                    'datestring' => $nextOpeningLocal->setTimezone($clientTz),
                    'text' => $nextOpenClientText.' at '.$nextOpeningLocal->setTimezone($clientTz)->format('H:i')
                ];
        }

        return $nextOpeningClient;
    }
}