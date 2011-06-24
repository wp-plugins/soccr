<?php
/*
  Plugin Name: Soccr
  Plugin URI: http://www.eracer.de/soccr
  Description: Provides a widget to display the last or next match for a specified team. Currently supporting German Bundesliga 1-3. Powered by openligadb.de
  Author: Stevie
  Version: 0.963 Beta
  Author URI: http://www.eracer.de
 */

// References
require_once("references/OpenLigaDB.php");

// Globals
class SoccrGlobals
{
    public static $german_cup_shortcut = "dfb11";
    public static $euroleague_shortcut = "el2010";
    public static $championsleague_shortcut = "cl1011";
    public static $cacheGroup = "SoccrCache";

}

// Core
class SoccrCore {

    // private functions
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
        ini_set("default_socket_timeout","05");
        set_time_limit(5);
        $f=fopen("http://www.OpenLigaDB.de","r");
        $r=fread($f,1000);
        fclose($f);
        ini_set("default_socket_timeout",$default);
        
        if(strlen($r)>1) {
            return true;
        } else {
            return false;
        }
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

    private function GetMatchdataByLeagueDateTime($leagueShortcut, $fromDate, $toDate) {
        $cupShortcut = SoccrGlobals::$german_cup_shortcut;
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();

        $allMatches = array();

        if($this->IsOpenLigaDbUp())
        {
            //league matches
            $leaguesMatches = (array)$openLigaDB->GetMatchdataByLeagueDateTime($client, $leagueShortcut, $fromDate, $toDate)->GetMatchdataByLeagueDateTimeResult->Matchdata;

            //national cup matches
            $cupMatches = (array)$openLigaDB->GetMatchdataByLeagueDateTime($client, $cupShortcut, $fromDate, $toDate)->GetMatchdataByLeagueDateTimeResult->Matchdata;

            $allMatches = array_merge($allMatches, $leaguesMatches);
            $allMatches = array_merge($allMatches, $cupMatches);
        }
        
        return $allMatches;
    }

    private function GetMatchdataByLeagueDateTimeTeam($leagueShortcut, $teamId, $fromDate, $toDate) {

        if($this->IsOpenLigaDbUp())
        {
            $allMatches = $this->GetMatchdataByLeagueDateTime($leagueShortcut, $fromDate, $toDate);
        }
        else
        {
            $allMatches = null;
        }
   
        if($allMatches != null):
            $soccrMatches = array();
            foreach ($allMatches as $match):
                
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
                                    $match->pointsTeam2,
                                    $match->goals
                    );

                    array_push($soccrMatches, $soccrMatch);
                }
            endforeach;
            
            if(sizeof($soccrMatches) == 0)
            {
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

        if($matches == null)
        {
            return null;
        }

        return reset($matches);
    }

    public function GetLastMatchByTeam($teamId, $leagueShortcut) {
        $currentDate = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));
        $fromDate = $this->DateAdd("d", -60, $currentDate);
        $toDate = $this->DateAdd("h", -3, $currentDate);

        $matches = $this->GetMatchdataByLeagueDateTimeTeam($leagueShortcut, $teamId, $fromDate, $toDate);

        if($matches == null)
        {
            return null;
        }

        return end($matches);

    }

    public function GetAvailibleTeams($leagueShortcut, $season) {
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();

        $teams = $openLigaDB->GetTeamsByLeagueSaison($client, $season, $leagueShortcut);

        return $this->SortStdArray($teams->GetTeamsByLeagueSaisonResult->Team,"teamName");
    }

    public function GetAvailLeagues() {
        $openLigaDB = new OpenLigaDB();
        $client = $this->GetWebserviceClient();
        $leagues = $openLigaDB->GetAvailLeagues($client);
        return $leagues->GetAvailLeaguesResult->League;
    }

}

// Match
class SoccrMatch {

    public $teamId1, $teamId2, $teamName1, $teamName2, $date, $time, $IconUrlTeam1, $IconUrlTeam2, $LocationCity, $LocationStadium, $MatchIsFinished, $GoalsTeam1, $GoalsTeam2, $SoccrGoals;

    public function __construct($teamId1, $teamId2, $teamName1, $teamName2, $matchDateTimeUTC, $iconUrlTeam1, $iconUrlTeam2, $locationCity, $locationStadium, $matchIsFinished, $goalsTeam1, $goalsTeam2, $goals) {

        $soccrGoals = array();

        $goals = $goals->Goal;

        if($goals != null):
        foreach($goals as $goal)
        {
            $soccrGoal = new SoccrGoal(
                    $goal->goalScoreTeam1,
                    $goal->goalScoreTeam2,
                    $goal->goalGetterName,
                    $goal->goalMatchMinute,
                    $goal->goalOwnGoal, 
                    $goal->goalPenalty);

            array_push($soccrGoals, $soccrGoal);
        }
        endif;

        $xx = $soccrGoals;

        $this->teamId1 = $teamId1;
        $this->teamId2 = $teamId2;
        $this->teamName1 = $teamName1;
        $this->teamName2 = $teamName2;
        $this->date = $this->ParseMatchDateTime($matchDateTimeUTC, "GetDate");
        $this->time = $this->ParseMatchDateTime($matchDateTimeUTC, "GetTime");
        $this->matchDateTimeUTC = $matchDateTimeUTC;
        $this->MatchIsFinished = $matchIsFinished;
        $this->IconUrlTeam1 = $iconUrlTeam1;
        $this->IconUrlTeam2 = $iconUrlTeam2;
        $this->LocationCity = $locationCity;
        $this->LocationStadium = $locationStadium;
        $this->GoalsTeam1 = $goalsTeam1;
        $this->GoalsTeam2 = $goalsTeam2;
        $this->MatchIsFinished = $matchIsFinished;
        $this->SoccrGoals = $soccrGoals;

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

// Goals
class SoccrGoal {
    public $GoalScoreTeam1, $GoalScoreTeam2, $GoalGetterName, $GoalMinute, $IsOwnGoal, $IsPenalty;

    public function __construct($goalScoreTeam1, $goalScoreTeam2, $goalGetterName, $goalMinute, $isOwnGoal, $isPenalty)
    {
        $this->GoalScoreTeam1 = $goalScoreTeam1;
        $this->GoalScoreTeam2 = $goalScoreTeam2;
        $this->GoalGetterName = $goalGetterName;
        $this->GoalMinute = $goalMinute;
        $this->IsOwnGoal = $isOwnGoal;
        $this->IsPenalty = $isPenalty;
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
