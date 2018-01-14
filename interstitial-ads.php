<?php
/*
Plugin Name: Interstitial Ads (Biology Online)
Plugin URI: http://wpmanage.com/interstitial-ads
Description: Allows you to show Interstitial Ads on any WordPress site
Version: 2.0
Author: WPmanage
Author URI: http://wpmanage.com/
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Uji_Interst' ) ) {
  define( 'UJI_INTER_FILE', plugin_dir_path( __FILE__ ) );

  //Functions
  require_once( 'classes/class-interstitial-functions.php' );
  //Interstate Ads Front
  require_once( 'classes/class-interstitial.php' );
}

global $ujinter;
$ujinter = new Uji_Interst( __FILE__ );
$ujinter->version = '2.0';
