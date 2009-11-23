<?php
/*
Plugin Name: WP-HAML
Plugin URI: http://thedextrousweb.com/wp-haml
Description: Allows you to write Wordpress themes using HAML
Author: Harry Metcalfe
Version: 1.0
Author URI: http://thedextrousweb.com

   This plugin allows you to write your Wordpress theme templates using HAML instead of a mish-mash of HTML and PHP.
   
   It overrides Wordpress's template loader and uses <a href="http://wphaml.sourceforge.net/">wphaml</a> to parse the HAML
   and emit the results.
   
   See the README in the plugin directory for more information.
   
*/

/*
 * Config
 */
 
define('COMPILED_TEMPLATES', WP_CONTENT_DIR . '/compiled-templates');
   

/*
 * Setup and teardown
 */

register_activation_hook(__FILE__, 'wphaml_activate');
register_deactivation_hook(__FILE__, 'wphaml_deactivate');

function wphaml_activate()
{
   if(!file_exists(COMPILED_TEMPLATES) && !mkdir(COMPILED_TEMPLATES))
   {  
      add_action('admin_notices', 'wphaml_warning');
   }
}

function wphaml_deactivate()
{
}

function wphaml_warning() 
{
   echo "<div class='updated fade'><p>php-haml will currently work: you need to create <em>" . COMPILED_TEMPLATES . "</em> and make sure it's writeable by your webserver</p></div>";
}

/*
 * Plugin logic
 */

require_once  dirname(__FILE__) . '/phphaml/includes/haml/HamlParser.class.php';
 
function wphaml_parse_template($template)
{
   global $wpdb, $wp_query;

   $parser = new HamlParser(TEMPLATEPATH, COMPILED_TEMPLATES);
   echo $parser->setFile($template);
}


add_action('template_include', 'wphaml_template_include');

function wphaml_template_include($template)
{
   // Is there a haml template?
   $haml_template = str_replace(".php", ".haml.php", $template);
   
   if(file_exists($haml_template))
   {
      wphaml_parse_template($haml_template);
   
      // Don't do Wordpress's template handling
      die();
   }
}


?>
