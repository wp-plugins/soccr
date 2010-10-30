<?php
/*
  Plugin Name: Soccr
  Plugin URI: http://www.eracer.de/Soccr
  Description: Provides a widget to display the last or next match for a specified team. Currently supporting German Bundesliga 1-3. Powered by openligadb.de
  Author: Stevie
  Version: 0.9 Beta
  Author URI: http://www.eracer.de
 */

// References
require_once("references/OpenLigaDB.php");

// Core-Classes
class SoccrCore {

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

    private function SortStdArray($array,$index) {
        $sort=array() ;
        $return=array() ;

        for ($i=0; isset($array[$i]); $i++)
        $sort[$i]= $array[$i]->{$index};

        natcasesort($sort) ;

        foreach($sort as $k=>$v)
        $return[]=$array[$k] ;

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
            $year+=($number*3);
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
            $day+=($number*7);
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
       $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
    return $timestamp;
}

    public function GetNextMatchByTeam($teamId, $leagueShortcut) {
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();

        $currentDate = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));

        $fromDate = $this->DateAdd("h", -2, $currentDate);
        $toDate = $this->DateAdd("d", 30, $currentDate);
        
        $matches = $openLigaDB->GetMatchdataByLeagueDateTime($client, $leagueShortcut, $fromDate, $toDate);

        foreach ($matches->GetMatchdataByLeagueDateTimeResult->Matchdata as $match) {
            if ($match->idTeam1 == $teamId || $match->idTeam2 == $teamId) {

                $soccrMatch = new SoccrMatch(
                                $match->idTeam1,
                                $match->idTeam2,
                                $match->nameTeam1,
                                $match->nameTeam2,
                                $match->matchDateTimeUTC,
                                $match->iconUrlTeam1,
                                $match->iconUrlTeam2,
                                $match->location->locationCity,
                                $match->location->locationStadium,
                                $match->matchIsFinished,
                                $match->pointsTeam1,
                                $match->pointsTeam2
                );


                break;
            }
        }

        return $soccrMatch;
    }

    public function GetLastMatchByTeam($teamId, $leagueShortcut) {
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();

        $currentDate = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));
      
        $fromDate = $this->DateAdd("d", -60, $currentDate);
        $toDate = $this->DateAdd("h", +2, $currentDate);
           
        $matches = $openLigaDB->GetMatchdataByLeagueDateTime($client, $leagueShortcut, $fromDate, $toDate);
        $result = $this->SortStdArray($matches->GetMatchdataByLeagueDateTimeResult->Matchdata, "matchDateTime");
        $i = 0;
        foreach ($result as $match) {
            if ($match->idTeam1 == $teamId || $match->idTeam2 == $teamId) {

                    $soccrMatches[$i] = new SoccrMatch(
                                    $match->idTeam1,
                                    $match->idTeam2,
                                    $match->nameTeam1,
                                    $match->nameTeam2,
                                    $match->matchDateTimeUTC,
                                    $match->iconUrlTeam1,
                                    $match->iconUrlTeam2,
                                    $match->location->locationCity,
                                    $match->location->locationStadium,
                                    $match->matchIsFinished,
                                    $match->pointsTeam1,
                                    $match->pointsTeam2
                    );
                    $i = $i + 1;
                }
            }

       $lastMatch = $soccrMatches[$i-1];
        return $lastMatch;
    }

    public function GetAvailibleTeams($leagueShortcut, $season) {
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();

        $teams = $openLigaDB->GetTeamsByLeagueSaison($client, $season, $leagueShortcut);

        return $this->SortStdArray($teams->GetTeamsByLeagueSaisonResult->Team,"teamName");
    }

    public function GetAvailLeagues()
    {
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();
        $leagues = $openLigaDB->GetAvailLeagues($client);
        return $leagues->GetAvailLeaguesResult->League;
    }

}

class SoccrMatch {

    public $teamId1, $teamId2, $teamName1, $teamName2, $date, $time, $IconUrlTeam1, $IconUrlTeam2, $LocationCity, $LocationStadium, $MatchIsFinished, $GoalsTeam1, $GoalsTeam2;

    public function __construct($teamId1, $teamId2, $teamName1, $teamName2, $matchDateTimeUTC, $iconUrlTeam1, $iconUrlTeam2, $locationCity, $locationStadium, $matchIsFinished, $goalsTeam1, $goalsTeam2) {
        $this->teamId1 = $teamId1;
        $this->teamId2 = $teamId2;
        $this->teamName1 = $teamName1;
        $this->teamName2 = $teamName2;
        $this->date = $this->ParseMatchDateTime($matchDateTimeUTC, "GetDate");
        $this->time = $this->ParseMatchDateTime($matchDateTimeUTC, "GetTime");
        $this->MatchIsFinished = $matchIsFinished;
        $this->IconUrlTeam1 = $iconUrlTeam1;
        $this->IconUrlTeam2 = $iconUrlTeam2;
        $this->LocationCity = $locationCity;
        $this->LocationStadium = $locationStadium;
        $this->GoalsTeam1 = $goalsTeam1;
        $this->GoalsTeam2 = $goalsTeam2;
        $this->MatchIsFinished = $matchIsFinished;
    }

    function ParseMatchDateTime($matchDateTimeUTC, $mode) {
        //This is horrible, maybe there is another way to calculate the date and time
        date_default_timezone_set('UTC');
        $dateString = str_replace("Z", "", $matchDateTimeUTC);
        $dateTime = new DateTime($dateString);
        $timeZone = new DateTimeZone("Europe/Berlin");
        $dateTime->setTimezone($timeZone);
        $dateTimeEurope = date_format($dateTime, DATE_ATOM);
        date_default_timezone_set('Europe/Berlin');

        if ($mode == "GetDate") {
            return date("d.m.Y", strtotime($dateTimeEurope));
        } else if ($mode == "GetTime") {
            return date("H:i", strtotime($dateTimeEurope));
        } else {
            return null;
        }
    }

}

// Widgets
require_once("soccr-match-widget.php");

// Action, Hooks and other Stuff
function soccr_add_css() {
    $myStyleUrl = WP_PLUGIN_URL . '/soccr/soccr.css';
    $myStyleFile = WP_PLUGIN_DIR . '/soccr/soccr.css';
    if ( file_exists($myStyleFile) ) {
        wp_register_style('soccr', $myStyleUrl);
        wp_enqueue_style( 'soccr');
    }
}

add_action('wp_print_styles', 'soccr_add_css');
