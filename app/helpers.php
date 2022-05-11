<?php

    function getUTC($dateString = '') {
        $date = new \DateTime($dateString, new \DateTimeZone('Europe/Amsterdam'));
        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date;
    }

    function getAmsterdamTimeZone($dateString = '') {
        $date = new \DateTime($dateString);
        $date->setTimezone(new \DateTimeZone('Europe/Amsterdam'));

        return $date;
    }

    function getAmsterdamTimeZoneFormat(){
        return 'Europe/Amsterdam';
    }

     function postSlackMessage($message, $logEmoji="", $emoji=":robot_face:", $isAction = false){
        $token          = getenv('slack_token');
        $domain         = 'slack';
        $files_list_url = 'https://' . $domain . '.slack.com/api/chat.postMessage';

        if($logEmoji!=""){
            $message = $logEmoji." ".$message;
        }

        $channel = getenv("slack_channel");
        if($isAction===true){
            $channel = getenv("slack_action_channel");
        }

        $fields = array(
            'token' => $token,
            'channel' => $channel,
            'text' => $message,
            'username' => 'Slack Care bot',
            'icon_emoji' => $emoji
        );

        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $files_list_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        $replyRaw = curl_exec($ch);
    }


    function CareAnalytics($event, $person, $data = []) {
        return;
    }

    function parseDate(\DateTime $date) {
        $day = $date->format('d');
        $month = $date->format('m');
        $year = $date->format('Y');

        $hours = $date->format('H');
        $minutes = $date->format('i');

        if ($minutes % 15 !== 0) {
            if($minutes >= 30) {
                $date->setTime(8, 30, 0);
            } else {
                $date->setTime(8, 0, 0);
            }
            $minutes = $date->format('i');
        }
        if ($hours < 6) {
            $hours = 8;
        }
        return [
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'hours' => $hours,
            'minutes' => $minutes
        ];
    }

    function convertNumberToDay($number) {
        $days = array('Monday','Tuesday', 'Wednesday', 'Thursday','Friday','Saturday','Sunday');
        return $days[$number-1];
    }


