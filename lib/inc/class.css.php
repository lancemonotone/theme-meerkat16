<?php
/**
* Class Meerkat16_Css
 *
 * Conacten
 */
class Meerkat16_Css {
   private static $instance;
	
	public function __construct() {

	}
	
	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Meerkat16_Images The *Singleton* instance.
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}
	
	/**
    * Minifies CSS for live server
    *
    * @param $path
    *
    * @return int
    */
	public function do_shrinkwrap($path){
	    $shrinkwrapped = $path . 'style.min.css';
		if(!file_exists($shrinkwrapped) || $_SERVER['QUERY_STRING'] == 'shrinkwrap'){
		    $styles = '@charset "UTF-8";';
			$styles .= file_get_contents( $path . 'style.css' );

		    // remove comments
			$styles = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $styles );
		    // remove whitespace
			$styles = str_replace( array( "\r\n", "\r", "\n", "\t", '    ', '    '), '', $styles );

			// write to file
			if ( ! file_put_contents( $shrinkwrapped, $styles )){
		        $message = 'Did not shrinkwrap';
			}else {
                $message = 'Successful shrinkwrap';
			}
			echo <<< EOD
				<script type="javascript">console.log($message)</script>
EOD;
		}
    return filemtime( $shrinkwrapped );
	}
	
	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}
	
	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {
	}
}