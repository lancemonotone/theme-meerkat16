<?php
if ( 182 ){
   // alumni news/people magazine
   //include_once( 'alum_auth.php' );
   include_once( 'class_year_picker.php' );
   include_once( 'cover_stories.php');
   include_once( 'edition_sections.php' );
   include_once( 'class_year_nav.php' );
}
else if( 181 == Meerkat16::instance()->blog_id ) {
	// Williams Magazine
	include_once( 'edition_options.php' );
	include_once( 'edition_postnav.php' );
}


?>