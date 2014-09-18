<?php

class Soccr_Widget_Match extends WP_Widget {

    public function __construct() {
        parent::__construct(
                'soccr', __('Soccr Widget', 'soccr'), array('description' => __('Displays last or next match from a specified team', 'soccr'),)
        );
    }

    function form($instance) {
        $SoccrCore = new SOCCR_Core();
        $currentYear = date("Y");

        $instance_league_shortcut = isset($instance['leagueShortcut']) ? $instance['leagueShortcut'] : "bl1";
        $instance_season = isset($instance['season']) ? $instance['season'] : $currentYear - 1;
        $instance_title = isset($instance['title']) ? $instance['title'] : __("Next Match", "soccr");
        $instance_widgettype = isset($instance['widgettype']) ? $instance['widgettype'] : "next";
        $instance_team = isset($instance['team']) ? $instance['team'] : 79;

        $status_response = $SoccrCore->GetAvailibleTeams($instance_league_shortcut, $instance_season);

        $teams = SOCCR_Status::SUCCESS ? $status_response->get_response_object() : array();

        $locale = get_locale();
        switch ($locale):
            case "de_DE":
                $paypal_button_code = "SFWCKQL47C4S2";
                break;
            case "en_US":
                $paypal_button_code = "B2WSC5FR2L8MU";
                break;
            default:
                $locale = "en_US";
                $paypal_button_code = "B2WSC5FR2L8MU";
                break;
        endswitch;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', "soccr"); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance_title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('widgettype'); ?>"><?php _e('Mode', "soccr"); ?>:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('widgettype'); ?>" name="<?php echo $this->get_field_name('widgettype'); ?>">
                <option value="next" <?php selected($instance_widgettype, "next"); ?>><?php _e("Next Match", "soccr"); ?></option>
                <option value="last" <?php selected($instance_widgettype, "last"); ?>><?php _e("Last Match", "soccr"); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('leagueShortcut'); ?>"><?php _e("League", "soccr"); ?>:</label>
            <select class="widefat soccer-season-option" data-widgetid="<?php echo $this->id; ?>" id="<?php echo $this->get_field_id('leagueShortcut'); ?>" name="<?php echo $this->get_field_name('leagueShortcut'); ?>">
                <option value="bl1"<?php if ($instance_league_shortcut == "bl1"): ?>selected="selected"<?php endif; ?>>1. Bundesliga</option>
                <option value="bl2"<?php if ($instance_league_shortcut == "bl2"): ?>selected="selected"<?php endif; ?>>2. Bundesliga</option>
                <option value="bl3"<?php if ($instance_league_shortcut == "bl3"): ?>selected="selected"<?php endif; ?>>3. Bundesliga</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('season'); ?>"><?php _e("Season", "soccr"); ?>:</label>
            <select class="widefat soccer-season-option" data-widgetid="<?php echo $this->id; ?>" id="<?php echo $this->get_field_id('season'); ?>" name="<?php echo $this->get_field_name('season'); ?>">
                <?php for ($s = $currentYear - 1; $s <= $currentYear; $s++): ?>
                    <option value="<?php echo $s; ?>" <?php selected($instance_season, $s) ?>><?php echo $s; ?>/<?php echo $s+1; ?></option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('team'); ?>"><?php _e("Team", "soccr"); ?>:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('team'); ?>" name="<?php echo $this->get_field_name('team'); ?>">
                <?php foreach ($teams as $team): ?>         
                    <option value="<?php echo $team->teamID ?>"<?php selected($instance_team, $team->teamID) ?>><?php echo $team->teamName ?></option>
                <?php endforeach; ?>
            </select> 
        </p>
        <p>
            <?php _e("Please support Plugin Development with a small donation", "soccr"); ?>
        </p>
        <p>
            <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=<?php echo $paypal_button_code; ?>" target="_blank"><img src="<?php echo SOCCR_PLUGIN_URL ?>assets/images/paypal_donate_<?php echo $locale ?>.gif" alt="Plugin Entwicklung unterstÃ¼tzen und Spenden" /></a>
        </p>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;

        if ($new_instance['team'] != null):
            $transient_key = SOCCR_Constants::TRANSIENT_WIDGET_MATCH_CACHE . "_" . $this->id;
            delete_transient($transient_key);
            $instance['team'] = intval($new_instance['team']);
            $instance['title'] = $new_instance['title'];
            $instance['widgettype'] = $new_instance['widgettype'];
            $instance['leagueShortcut'] = $new_instance['leagueShortcut'];
            $instance['season'] = $new_instance['season'];
            return $instance;
        endif;
    }

    function widget($args, $instance) {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $transient_key = SOCCR_Constants::TRANSIENT_WIDGET_MATCH_CACHE . "_" . $this->id;

        $match = get_transient($transient_key);
 
        if ($match === false || $match === null) {
            $SoccrCore = new SOCCR_Core();

            if ($instance['widgettype'] == "next") {
                $match = $SoccrCore->GetNextMatchByTeam($instance['team'], $instance["leagueShortcut"]);
            } else {
                $match = $SoccrCore->GetLastMatchByTeam($instance['team'], $instance["leagueShortcut"]);
            }

            set_transient($transient_key, $match, 7200);
        }

        echo $before_widget;
        if (!empty($title)):
            echo $before_title . $title . $after_title;
        endif;
        ?>
        <ul>
            <?php if ($match == null): ?>
                <?php echo __("No match available", "soccr"); ?>
            <?php else: ?>
                <table class="soccr_match_widget" border="0" style="border: none;">
                    <tr>
                        <td colspan="3" style="text-align: center; padding-bottom: 5px;"><?php echo $match->date; ?> - <?php echo $match->time; ?> Uhr</td>
                    </tr>

                    <?php if ($match->LocationStadium != ""): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding-bottom: 5px;"><?php echo $match->LocationCity; ?> - <?php echo $match->LocationStadium; ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="text-align: center; vertical-align: middle;"><img src="<?php echo $match->IconUrlTeam1; ?>" /></td>
                        <td style="width: 15px;"></td>
                        <td style="text-align: center;"><img src="<?php echo $match->IconUrlTeam2; ?>" /></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;"><?php echo $match->teamName1; ?></td>
                        <td style="text-align: center; vertical-align: middle;">:</td>
                        <td style="text-align: center;"><?php echo $match->teamName2; ?></td>
                    </tr>
                    <?php if ($match->MatchIsFinished): ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $match->GoalsTeam1; ?></td>
                            <td style="text-align: center;"></td>
                            <td style="text-align: center;"><?php echo $match->GoalsTeam2; ?></td>
                        </tr>
                        <?php if (1 == 2): ?>
                            <tr>
                                <td colspan="3" align="center" style="padding-top: 5px;">
                                    <table border="0" style="border: none;">
                                        <?php foreach ($match->SoccrGoals as $soccrGoal): ?>
                                            <tr>
                                                <td><?php echo $soccrGoal->GoalScoreTeam1 ?>:<?php echo $soccrGoal->GoalScoreTeam2 ?>&nbsp;</td>
                                                <td><?php echo $soccrGoal->GoalGetterName ?> (<?php echo $soccrGoal->GoalMinute ?>.)</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </table>
            <?php endif; ?>


        </ul>

        <?php
        echo $after_widget;
    }

}
