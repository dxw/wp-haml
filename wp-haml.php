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
 * Template handling
 */

require_once dirname(__FILE__) . "/helpers.php";
require_once  dirname(__FILE__) . '/phphaml/includes/haml/HamlParser.class.php';
 
/**
  * $template_layout is set by the template if it wishes to use a custom layout. 
  *
  * The loader compiles and executes the template, saves its output to $template_output,
  * and then compiles and executes the layout. The layout calls yield() to include the
  * content of the template.
  */

$template_layout = $template_output = '';
  

/**
  * Intercepts template includes using our new filter and looks for a HAML alternative.
  */
  
add_filter('template_include', 'wphaml_template_include');
function wphaml_template_include($template)
{
   // Globalise the Wordpress environment
   global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
   
   // Globalise the stuff we need
   global $template_output, $template_layout;
   
   // Is there a haml template?
   if(substr($template, -9) == '.haml.php')
   {
      $haml_template = $template;
   }
   else
   {
      $haml_template = str_replace(".php", ".haml.php", $template);
   }
   
   if(file_exists($haml_template))
   {
      // Execute the template and save its output
      $parser = new HamlParser(TEMPLATEPATH, COMPILED_TEMPLATES);
      $parser->setFile($haml_template);

      $template_output = $parser->render();
            
      if($template_layout == '')
      {
         $template_layout = TEMPLATEPATH . "/layout.haml.php";
      }
      
      // Execute the layout and display everything
      $parser = new HamlParser(TEMPLATEPATH, COMPILED_TEMPLATES);
      $parser->setFile($template_layout);
   
      echo $parser->render();
      
      return null;
   }
   
   return $template;
}


/*
 * Create haml alternatives for the get_* functions
 */

function use_layout($name)
{
   global $template_layout;

   $layout = TEMPLATEPATH . "/layout-$name.haml.php";
   
   if(!file_exists($layout))
   {
      trigger_error("The specified layout could not be found: <em>$layout</em>", E_USER_ERROR);
      die();
   }
   
   $template_layout = $layout;
}

function render_partial($name, $return = false)
{
   $partial_template = TEMPLATEPATH . "/partials/_$name.haml.php";
      
   if(!file_exists($partial_template))
   {
      trigger_error("The specified partial could not be found: <em>$partial_template</em>", E_USER_ERROR);
      die();
   }
   
   // Execute the template and save its output
   $parser = new HamlParser(TEMPLATEPATH, COMPILED_TEMPLATES);
   $parser->setFile($partial_template);

   $partial_output = $parser->render();
   
   if($return)
   {
      return $partial_output;
   }
   
   echo $partial_output;
}

function yield()
{
   global $template_output;
   
   if($template_output == '')
   {
      trigger_error("<tt>yield</tt> had no output to emit (\$template_output is empty). Did your template do anything?", E_USER_NOTICE);
      die();
   }
   
   echo $template_output;
}


/*
 * Warn people not to use get_header and get_footer
 */
 
add_action('get_header', 'wphaml_headfoot_warnings');
add_action('get_footer', 'wphaml_headfoot_warnings');
add_action('get_sidebar', 'wphaml_headfoot_warnings');
add_action('get_search_form', 'wphaml_headfoot_warnings');

function wphaml_headfoot_warnings()
{
   trigger_error("Eek! Don't use get_header, get_footer, get_sidebar or get_search_form. You should use layouts and partials instead: <tt>use_layout</tt> and <tt>get_partial</tt>", E_USER_WARNING);
}


?>
