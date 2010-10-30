<?php
    class Soccr_Widget_Match extends WP_Widget {
        // Constructor
     
        function Soccr_Widget_Match() {
            $widget_ops = array('description' => __('Displays last or next match from a specified team', 'wp-soccr'));
            $this->WP_Widget('soccr', __('Soccr Match Widget'), $widget_ops);
            
        }

        // Display Widget
        function widget($args, $instance) {
            $cacheKey = "soccr_match_" . $instance['widgettype'];
            $match = wp_cache_get($cacheKey);
            
            if($match == null)
            {
                $SoccrCore = new SoccrCore();

                if($instance['widgettype'] == "next")
                {
                    $match = $SoccrCore->GetNextMatchByTeam($instance['team'], $instance["leagueShortcut"]);
                }
                else
                {
                    $match = $SoccrCore->GetLastMatchByTeam($instance['team'], $instance["leagueShortcut"]);
                }

                wp_cache_add($cacheKey, $match);
            }

        
        ?>

        <li id="recruitments_widget" class="widget-container">
            <h3 class="widget-title"><?php echo  $instance['title'] ?></h3>
            <ul>
             
                    <table class="soccr_match_widget" border="0" style="border: none;">
                        <tr>
                            <td colspan="3" style="text-align: center; padding-bottom: 5px;"><?php echo $match->date; ?> - <?php echo $match->time; ?> Uhr</td>
                        </tr>

                        <?php if($match->LocationStadium != ""): ?>
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
                        <?php endif; ?>
                        </table>
                   
                </ul>
        </li>
        <?php
            }

        function update($new_instance, $old_instance) {
            $instance = $old_instance;
            //update_option("soccr_next_match_team", $new_instance['soccr_next_match_team_option']);

            if($new_instance['team'] != null);
                {
                    $instance['team'] = intval($new_instance['team']);
                    $instance['title'] = $new_instance['title'];
                    $instance['widgettype'] = $new_instance['widgettype'];
                    $instance['leagueShortcut'] = $new_instance['leagueShortcut'];
                    $instance['season'] = $new_instance['season'];
                    return $instance;
                }

        }

        // DIsplay Widget Control Form
        function form($instance) {
            $SoccrCore = new SoccrCore();
            $currentYear = date("Y");

            if($instance['leagueShortcut'] == null || $instance['leagueShortcut'] == ""):
                $instance['leagueShortcut'] = "bl1";
            endif;

             if($instance['season'] == null || $instance['season'] == ""):
                $instance['season'] = $currentYear;
            endif;

            $teams = $SoccrCore->GetAvailibleTeams($instance['leagueShortcut'], $instance['season']);

            if($teams == null)
            {
                $displayNoTeamsInfo = true;
            }
            else
            {
                $displayNoTeamsInfo = false;
            }
            $instance = wp_parse_args((array) $instance, array('title' => 'Match', 'widgettype' => 'next'));
            $title = esc_attr($instance['title']);
            $instance['teams'] = $teams;           
            $instance["team"] = 79;
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'Nächstes Spiel'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('widgettype'); ?>"></label>
                <select class="widefat" id="<?php echo $this->get_field_id('widgettype'); ?>" name="<?php echo $this->get_field_name('widgettype'); ?>">
                    <option value="next">Next Match</option>
                    <option value="last">Last Match</option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('leagueShortcut'); ?>">Liga:</label>
                <select onchange="soccr_get_availible_teams('<?php echo $this->get_field_id('leagueShortcut'); ?>','<?php echo $this->get_field_id('team'); ?>','<?php echo $this->get_field_id('season'); ?>');"  class="widefat" id="<?php echo $this->get_field_id('leagueShortcut'); ?>" name="<?php echo $this->get_field_name('leagueShortcut'); ?>">
                    <option value="bl1"<?php if($instance['leagueShortcut'] == "bl1"): ?>selected="selected"<?php endif;?>>1. Bundesliga</option>
                    <option value="bl2"<?php if($instance['leagueShortcut'] == "bl2"): ?>selected="selected"<?php endif;?>>2. Bundesliga</option>
                    <option value="bl3"<?php if($instance['leagueShortcut'] == "bl3"): ?>selected="selected"<?php endif;?>>3. Bundesliga</option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('season'); ?>">Season:</label>
                <select onchange="soccr_get_availible_teams('<?php echo $this->get_field_id('leagueShortcut'); ?>','<?php echo $this->get_field_id('team'); ?>','<?php echo $this->get_field_id('season'); ?>');" class="widefat" id="<?php echo $this->get_field_id('season'); ?>" name="<?php echo $this->get_field_name('season'); ?>">
                    <?php for($s=$currentYear-1; $s<=$currentYear+1; $s++): ?>
                        <?php
                            $selected = "";
                            if ($instance["season"] == $s):
                                $selected = " selected='selected'";
                            endif;
                        ?>
                        <option value="<?php echo $s; ?>"<?php echo $selected ?>><?php echo $s; ?></option>
                    <?php endfor;?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('team'); ?>">Team:</label>
                <select class="widefat" id="<?php echo $this->get_field_id('team'); ?>" name="<?php echo $this->get_field_name('team'); ?>">
                    <?php
                        foreach ($instance['teams'] as $team):
                        $selected = "";
                        if ($instance["team"] == $team->teamID):
                            $selected = " selected='selected'";
                        endif;
                    ?>
                    <option value="<?php echo $team->teamID ?>"<?php echo $selected ?>><?php echo $team->teamName ?></option>
                    <?php endforeach; ?>
                </select>
                <span id="<?php echo $this->get_field_id('soccr_next_match_team_option_na'); ?>" style="display: <?php if($displayNoTeamsInfo): echo "block"; else: echo "none"; endif;?>;">No Teams for this League and Season avaibile. Widget can't be saved</span>
            </p>
            <?php
        }
    }

    // Ajax Functions for Widget Control
    function soccr_get_availible_teams() {
        $SoccrCore = new SoccrCore();
        $teams = $SoccrCore->GetAvailibleTeams($_GET["leagueShortcut"], $_GET["season"]);
        $i = 0;
        foreach($teams as $team):
            $arr[$i]["optionValue"] = $team->teamID;
            $arr[$i]["optionDisplay"] = $team->teamName;
            $i = $i + 1;
        endforeach;
        echo json_encode($arr);
        die();
    }

    function soccr_get_availible_teams_ajax() { ?>
        <script type="text/javascript" >
            function soccr_get_availible_teams(senderId, targetId, seasonId) {
              
                var data = {
                    action: 'soccr_get_availible_teams',
                    leagueShortcut: jQuery('#' + senderId).val(),
                    season: jQuery('#' + seasonId).val()
                };

                jQuery.getJSON(ajaxurl,data, function(j){

                        if(j != null)
                            {
                                var options = '';
                                for (var i = 0; i < j.length; i++) {
                                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                                }
                                jQuery("#" + targetId + "_na").hide();
                                jQuery("#" + targetId).show();
                                jQuery("#" + targetId).html(options);
                            }
                        else
                           {
                                jQuery("#" + targetId).html("");
                                jQuery("#" + targetId).hide();
                                jQuery("#" + targetId + "_na").show();
                           }
       
                });
            }
        </script>
    <?php }

    function widget_soccr_match_init() {
        register_widget('Soccr_Widget_Match');
    }

    // Actions
    add_action('widgets_init', 'widget_soccr_match_init');
    add_action('admin_head', 'soccr_get_availible_teams_ajax');
    add_action('wp_ajax_soccr_get_availible_teams', 'soccr_get_availible_teams');
?>
