jQuery(document).ready(function($) {

    var soccrBindEvents = function() {
        $(".soccer-season-option").on("change", function() {

            console.log(soccr_widget.text.no_teams_available);

            var that = this;
            var option_teams_loading = '<option value="-1">' + soccr_widget.text.teams_loading + '</option>';
            var option_teams_empty = '<option value="-1">' + soccr_widget.text.no_teams_available + '</option>';
            var widget_id = $(that).data("widgetid");
            var league_shortcut = $('#widget-' + widget_id + "-leagueShortcut").val();
            var season = $('#widget-' + widget_id + "-season").val();
            var team_select = $('#widget-' + widget_id + "-team");

            team_select.html(option_teams_loading);
            team_select.prop('disabled', true);

            $.ajax({
                type: "GET",
                url: ajaxurl,
                data: {
                    action: 'soccr_get_availible_teams',
                    leagueShortcut: league_shortcut,
                    season: season
                }
            }).done(function(response)
            {
                var teams = $.parseJSON(response);

                if (teams.length > 0)
                {
                    var options = '';

                    for (var team in teams) {
                        options += '<option value="' + teams[team].team_id + '">' + teams[team].team_name + '</option>';
                    }

                    team_select.prop('disabled', false);
                    team_select.show();
                    team_select.html(options);
                }
                else
                {
                    team_select.html(option_teams_empty);
                    team_select.show();
                }
            });
        });
    }
    
    jQuery(document).ajaxSuccess(function(e, xhr, settings) {
        if (settings.data && settings.data.search('action=save-widget') !== -1 && settings.data.search('id_base=soccr') !== -1) {
            soccrBindEvents();
        }
    });
    
    soccrBindEvents();
});
