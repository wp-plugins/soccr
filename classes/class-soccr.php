<?php

class SOCCR_Core {

    private function GetWebserviceClient() {
        $options = array('encoding' => 'UTF-8',
            'connection_timeout' => 5,
            'exceptions' => 1,
        );

        $location = 'http://www.OpenLigaDB.de/Webservices/Sportsdata.asmx?WSDL';

        try {
            $client = new SoapClient($location, $options);
        } catch (SoapFault $e) {
            die($e->faultcode . ': ' . $e->faultstring);
        } catch (Exception $e) {
            die($e->getCode() . ': ' . $e->getMessage());
        }
        return $client;
    }

    private function IsOpenLigaDbUp() {
        $default = ini_get("default_socket_timeout");
        ini_set("default_socket_timeout", "05");
        set_time_limit(5);
        $f = fopen("http://www.OpenLigaDB.de", "r");
        $r = fread($f, 1000);
        fclose($f);
        ini_set("default_socket_timeout", $default);

        if (strlen($r) > 1) {
            return true;
        } else {
            return false;
        }
    }

    private function SortStdArray($array, $index) {
        $sort = array();
        $return = array();

        for ($i = 0; isset($array[$i]); $i++)
            $sort[$i] = $array[$i]->{$index};

        natcasesort($sort);

        foreach ($sort as $k => $v)
            $return[] = $array[$k];

        return $return;
    }

    private function DateAdd($interval, $number, $date) {

        $date_time_array = getdate($date);
        $hours = $date_time_array['hours'];
        $minutes = $date_time_array['minutes'];
        $seconds = $date_time_array['seconds'];
        $month = $date_time_array['mon'];
        $day = $date_time_array['mday'];
        $year = $date_time_array['year'];

        switch ($interval) {

            case 'yyyy':
                $year+=$number;
                break;
            case 'q':
                $year+=($number * 3);
                break;
            case 'm':
                $month+=$number;
                break;
            case 'y':
            case 'd':
            case 'w':
                $day+=$number;
                break;
            case 'ww':
                $day+=($number * 7);
                break;
            case 'h':
                $hours+=$number;
                break;
            case 'n':
                $minutes+=$number;
                break;
            case 's':
                $seconds+=$number;
                break;
        }
        $timestamp = mktime($hours, $minutes, $seconds, $month, $day, $year);
        return $timestamp;
    }

    private function GetMatchdataByLeagueDateTime($leagueShortcut, $fromDate, $toDate) {
        $cupShortcut = apply_filters("soccr_cup_shortcut", SOCCR_OLDB_CUP_SHORTCUT);
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();

        $allMatches = array();

        if ($this->IsOpenLigaDbUp()) {
            //league matches

            $league_matches_data = $openLigaDB->GetMatchdataByLeagueDateTime($client, $leagueShortcut, $fromDate, $toDate);
            $leaguesMatches = isset($league_matches_data->GetMatchdataByLeagueDateTimeResult->Matchdata) ? $league_matches_data->GetMatchdataByLeagueDateTimeResult->Matchdata : array();

            //national cup matches            
            $cup_matches_data = $openLigaDB->GetMatchdataByLeagueDateTime($client, $cupShortcut, $fromDate, $toDate);
            $cupMatches = isset($cup_matches_data->GetMatchdataByLeagueDateTimeResult->Matchdata) ? $cup_matches_data->GetMatchdataByLeagueDateTimeResult->Matchdata : array();

            $allMatches = array_merge($allMatches, $leaguesMatches);
            $allMatches = array_merge($allMatches, $cupMatches);
        }

        return $allMatches;
    }

    private function GetMatchdataByLeagueDateTimeTeam($leagueShortcut, $teamId, $fromDate, $toDate) {

        if ($this->IsOpenLigaDbUp()) {
            $allMatches = $this->GetMatchdataByLeagueDateTime($leagueShortcut, $fromDate, $toDate);
        } else {
            $allMatches = null;
        }

        if ($allMatches != null):
            $soccrMatches = array();
            foreach ($allMatches as $match):

                if ($match->idTeam1 == $teamId || $match->idTeam2 == $teamId) {

                    $soccrMatch = new SOCCR_Match(
                            $match->idTeam1, $match->idTeam2, $match->nameTeam1, $match->nameTeam2, $match->matchDateTimeUTC, $match->iconUrlTeam1, $match->iconUrlTeam2, isset($match->location->locationCity) ? $match->location->locationCity : "", isset($match->location->locationStadium) ? $match->location->locationStadium : "", $match->matchIsFinished, $match->pointsTeam1, $match->pointsTeam2, $match->goals
                    );

                    array_push($soccrMatches, $soccrMatch);
                }
            endforeach;

            if (sizeof($soccrMatches) == 0) {
                return null;
            }

            return $this->SortStdArray($soccrMatches, "matchDateTimeUTC");
        else:
            return null;
        endif;
    }

    // public functions
    public function GetNextMatchByTeam($teamId, $leagueShortcut) {
        $currentDate = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));
        $fromDate = $this->DateAdd("h", -3, $currentDate);
        $toDate = $this->DateAdd("d", 60, $currentDate);

        $matches = $this->GetMatchdataByLeagueDateTimeTeam($leagueShortcut, $teamId, $fromDate, $toDate);

        if ($matches == null) {
            return null;
        }

        return reset($matches);
    }

    public function GetLastMatchByTeam($teamId, $leagueShortcut) {
        $currentDate = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));
        $fromDate = $this->DateAdd("d", -60, $currentDate);
        $toDate = $this->DateAdd("h", -3, $currentDate);

        $matches = $this->GetMatchdataByLeagueDateTimeTeam($leagueShortcut, $teamId, $fromDate, $toDate);

        if ($matches == null) {
            return null;
        }

        return end($matches);
    }

    public function GetAvailibleTeams($leagueShortcut, $season) {
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();

        $status_response = $openLigaDB->GetTeamsByLeagueSaison($client, $season, $leagueShortcut);

        if ($status_response->get_status() === SOCCR_Status::SUCCESS):
            return new SOCCR_StatusResponse(SOCCR_Status::SUCCESS, $this->SortStdArray($status_response->get_response_object()->GetTeamsByLeagueSaisonResult->Team, "teamName"));
        else:
            return $status_response;
        endif;
    }

    public function GetAvailLeagues() {
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();
        $leagues = $openLigaDB->GetAvailLeagues($client);
        return $leagues->GetAvailLeaguesResult->League;
    }

}
