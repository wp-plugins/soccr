<?php

class SOCCR_PluginController {

    public function __construct() {
        add_action("init", array(&$this, "init"));
        add_action('widgets_init', array(&$this, "widgets_init"));
        add_action('admin_print_scripts-widgets.php', array(&$this, "admin_print_scripts_widgets"), 20);
        add_action('wp_enqueue_scripts', array(&$this, "enqueue_styles"));
        new SOCCR_AjaxController();
    }

    public function init() {
       load_plugin_textdomain("soccr", true, SOCCR_PLUGIN_RELATIVE_DIR . '/languages/');
       
    }

    public function widgets_init() {
        register_widget('Soccr_Widget_Match');
    }

    public function admin_print_scripts_widgets() {
        wp_enqueue_script("soccr-widget-forms", SOCCR_PLUGIN_URL . "assets/admin-soccr-widget.js", array("jquery"));
        wp_localize_script("soccr-widget-forms", "soccr_widget", array("text" => array("teams_loading" => __("Loading...Please wait"), "no_teams_available" => __("No Teams available", "soccr"))));
    }

    public function enqueue_styles() {
        if (is_active_widget(false, false, "soccr")):
            wp_enqueue_style('soccr', SOCCR_PLUGIN_URL . "assets/soccr.css");
        endif;
    }

}
