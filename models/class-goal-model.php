<?php
class SOCCR_Goal {

    public $GoalScoreTeam1, $GoalScoreTeam2, $GoalGetterName, $GoalMinute, $IsOwnGoal, $IsPenalty;

    public function __construct($goalScoreTeam1, $goalScoreTeam2, $goalGetterName, $goalMinute, $isOwnGoal, $isPenalty) {
        $this->GoalScoreTeam1 = $goalScoreTeam1;
        $this->GoalScoreTeam2 = $goalScoreTeam2;
        $this->GoalGetterName = $goalGetterName;
        $this->GoalMinute = $goalMinute;
        $this->IsOwnGoal = $isOwnGoal;
        $this->IsPenalty = $isPenalty;
    }

}