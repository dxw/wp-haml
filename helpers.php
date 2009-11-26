<?php

function wph_get_stylesheet($name = 'style')
{
   return "<link rel='stylesheet' type='text/css' href='" . wph_theme_dir() . "/{$name}.css' />";
}

function wph_theme_dir()
{
   return get_bloginfo('stylesheet_directory');
}

?>
