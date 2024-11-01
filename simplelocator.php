<?php
/*
Plugin Name: Simple Locator
Plugin URI: http://locatewp.com/
Description: Location search in WordPress, made simple. Can be used for store or any other type of location. Simply add the shortcode [simple_locator] to add the locator.
Version: 2.0.3
Author: Kyle Phillips
Author URI: https://github.com/kylephillips
Text Domain: simple-locator
Domain Path: /languages/
License: GPLv2 or later.
*/

/*  Copyright 2022 Kyle Phillips

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Check versions before Instantiating Plugin Class
register_activation_hook( __FILE__, 'wpsimplelocator_check_versions' );
function wpsimplelocator_check_versions( $wp = '4.4', $php = '5.4.0' ) {
	global $wp_version;
	if ( version_compare( PHP_VERSION, $php, '<' ) ) $flag = 'PHP';
	elseif ( version_compare( $wp_version, $wp, '<' ) ) $flag = 'WordPress';
	else return;
	$version = 'PHP' == $flag ? $php : $wp;
	deactivate_plugins( basename( __FILE__ ) );
	wp_die('<p><strong>Simple Locator</strong> plugin requires'.$flag.'  version '.$version.' or greater.</p>','Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
}

if ( !class_exists('Bootstrap') ) :
	define('SIMPLELOCATOR_DIR', __DIR__);
    define('SIMPLELOCATOR_URI', __FILE__);
	wpsimplelocator_check_versions();
	require('vendor/autoload.php');
	require_once('app/SimpleLocator.php');
	SimpleLocator::init();
endif;