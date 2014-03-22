<?php
/*
  Plugin Name: Soccr
  Plugin URI: http://www.eracer.de/soccr
  Description: Provides a widget to display the last or next match for a specified team. Currently supporting German Bundesliga 1-3. Powered by openligadb.de
  Author: Stevie
  Version: 1.0
  Author URI: http://www.eracer.de
 */

define("SOCCR_PLUGIN_DIR", plugin_dir_path(__FILE__));
define("SOCCR_PLUGIN_URL", plugin_dir_url(__FILE__));
define("SOCCR_PLUGIN_RELATIVE_DIR", dirname(plugin_basename(__FILE__)));

require_once(SOCCR_PLUGIN_DIR . "references/OpenLigaDB.php");
require_once(SOCCR_PLUGIN_DIR . "controller/class-plugin-controller.php");
require_once(SOCCR_PLUGIN_DIR . "controller/class-ajax-controller.php");

require_once(SOCCR_PLUGIN_DIR . "models/class-status-response-model.php");


require_once(SOCCR_PLUGIN_DIR . "widgets/class-soccr-widget.php");

require_once(SOCCR_PLUGIN_DIR . "classes/class-soccr.php");
require_once(SOCCR_PLUGIN_DIR . "classes/class-constants.php");


new SOCCR_PluginController();






?>