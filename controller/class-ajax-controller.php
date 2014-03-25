<?php
class SOCCR_AjaxController {

    public function __construct() {
        add_action('wp_ajax_soccr_get_availible_teams', array(&$this, 'get_availible_teams'));
    }

    public function get_availible_teams() {

        $response = array();

        $SoccrCore = new SOCCR_Core();
        $status_response = $SoccrCore->GetAvailibleTeams($_GET["leagueShortcut"], $_GET["season"]);

        if (SOCCR_Status::SUCCESS === $status_response->get_status()):
            foreach ($status_response->get_response_object() as $team):
                array_push($response, array("team_id" => $team->teamID, "team_name" => $team->teamName));
            endforeach;
        endif;




        echo json_encode($response);


        die();
    }

}
