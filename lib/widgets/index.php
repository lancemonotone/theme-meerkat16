<?php
// relies on /web/lib/form_utils.php for form building
require_once( WMS_EXT_LIB . '/form_utils.php' );
require_once( WMS_LIB_PATH . '/class.events.php' );
include_once( THEME_WIDGETS_PATH . '/links.php' );
include_once( THEME_WIDGETS_PATH . '/weather.php' );
include_once( THEME_WIDGETS_PATH . '/gallery.php' );
include_once( THEME_WIDGETS_PATH . '/custom_menu.php' );
include_once( THEME_WIDGETS_PATH . '/pages.php' );
include_once( THEME_WIDGETS_PATH . '/text.php' );
include_once( THEME_WIDGETS_PATH . '/tweets.php' );


if ( Meerkat16::instance()->is_magazine_theme ){
	include_once( THEME_WIDGETS_PATH . '/magazines/index.php' );
}
