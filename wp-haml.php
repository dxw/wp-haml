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
 
add_action('template_redirect', 'wphaml_template_redirect');

function wphaml_parse_template($template)
{
   global $wpdb, $wp_query;

   $parser = new HamlParser(TEMPLATEPATH, COMPILED_TEMPLATES);
   echo $parser->setFile($template);
}


function wphaml_template_redirect()
{
   if(is_robots())
   {
      do_action('do_robots');   
   }
   else if ( is_feed() )
   {
      do_feed();
   } 
   else if(is_trackback())
   {
      $template = ABSPATH . 'wp-trackback.php';
   }
   else if(is_404())
   {
      $template = get_404_template();
   }
   else if(is_search())
   {
      $template = get_search_template();
   }
   else if(is_tax())
   {
      $template = get_taxonomy_template();
   }
   else if(is_home())
   {
      $template = get_home_template();
   } 
   else if(is_attachment() && $template = get_attachment_template())
   {
      remove_filter('the_content', 'prepend_attachment');   
   } 
   else if(is_single())
   {
      $template = get_single_template();
   } 
   else if(is_page())
   {
      $template = get_page_template();
   }
   else if(is_category())
   {
      $template = get_category_template();
   }
   else if(is_tag())
   {
      $template = get_tag_template();
   }
   else if(is_author())
   {
      $template = get_author_template();
   }
   else if(is_date())
   {
      $template = get_date_template();
   }
   else if(is_archive())
   {
      $template = get_archive_template();
   }
   else if(is_comments_popup())
   {
      $template = get_comments_popup_template();
   }
   else if(is_paged())
   {
      $template = get_paged_template();
   }
   else
   {
      $template = TEMPLATEPATH . "/index.php";
   }
   
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
