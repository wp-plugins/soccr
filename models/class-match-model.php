<?php

class SOCCR_Match {

    public $teamId1, $teamId2, $teamName1, $teamName2, $date, $time, $IconUrlTeam1, $IconUrlTeam2, $LocationCity, $LocationStadium, $MatchIsFinished, $GoalsTeam1, $GoalsTeam2, $SoccrGoals;

    public function __construct($teamId1, $teamId2, $teamName1, $teamName2, $matchDateTimeUTC, $iconUrlTeam1, $iconUrlTeam2, $locationCity, $locationStadium, $matchIsFinished, $goalsTeam1, $goalsTeam2, $goals) {

        $soccrGoals = array();

        $goals = isset($goals->Goal) ? $goals->Goal : null;

        if ($goals !== null):
            foreach ($goals as $goal) {

                $goal_score_team1 = isset($goal->goalScoreTeam1) ? $goal->goalScoreTeam1 : null;
                $goal_score_team2 = isset($goal->goalScoreTeam2) ? $goal->goalScoreTeam2 : null;
                $goal_getter_name = isset($goal->goalGetterName) ? $goal->goalGetterName : null;
                $goal_match_minute = isset($goal->goalMatchMinute) ? $goal->goalMatchMinute : null;
                $is_own_goal = isset($goal->goalOwnGoal) ? $goal->goalOwnGoal : null;
                $is_penalty = isset($goal->goalPenalty) ? $goal->goalPenalty : null;



                $soccrGoal = new SOCCR_Goal(
                        $goal_score_team1, $goal_score_team2, $goal_getter_name, $goal_match_minute, $is_own_goal, $is_penalty);

                array_push($soccrGoals, $soccrGoal);
            }
        endif;

        $xx = $soccrGoals;

        $this->teamId1 = $teamId1;
        $this->teamId2 = $teamId2;
        $this->teamName1 = $teamName1;
        $this->teamName2 = $teamName2;
        $this->date = $this->parse_match_datetime($matchDateTimeUTC, "GetDate");
        $this->time = $this->parse_match_datetime($matchDateTimeUTC, "GetTime");
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

    private function parse_match_datetime($matchDateTimeUTC, $mode) {
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
