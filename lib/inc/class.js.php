<?php

class Meerkat16_Js {
	private static $instance;
	var $use_build_file = false;
	var $code, $tablesorter_code, $tablesorter_pager_code, $addthis_code, $ga_links_code, $js;
	
	/**
	 * Returns the singleton instance of this class.
	 *
	 * @return Meerkat16_Js The singleton instance.
	 */
	public static function instance() {
		if ( ! static::$instance ) {
			static::$instance = new static();
		}
		
		return static::$instance;
	}
	
	protected function __construct() {
		add_action( 'wp_footer', array( &$this, 'load_frontend_js' ), 100 );
		
		$this->set_tablesorter_code();
		$this->set_tablesorter_pager_code();
		$this->set_addthis_code();
		$this->set_ga_links();
		$this->set_js();
	}
	
	protected function set_tablesorter_code() {
		$this->tablesorter_code = <<< EOD
$('table.tablesorter, table#wms-jquery-tablesorter').each(function() {
    $(this).tablesorter({
        sortList: [
            [0, 0]
        ]
    });
});

EOD;
	}

    protected function set_tablesorter_pager_code(){
        $this->tablesorter_pager_code = <<< EOD
var pager = jQuery('<div class="pager">'+
'<form>'+
'<span class="first"></span>'+
'<span class="prev"></span>'+
'<input type="text" class="pagedisplay"/>'+
'<span class="next"></span>'+
'<span class="last"></span>'+
'<select class="pagesize">'+
'<option value="10">10</option>'+
'<option value="50">50</option>'+
'<option value="100">100</option>'+
'<option value="99999" selected>All</option>'+
'</select>'+
'</form>'+
'</div>')
    .prependTo('table.tablesorter.tablesorter-pager');
jQuery('table.tablesorter').tablesorterPager({container: pager, size: 9999, positionFixed: false});

EOD;
	}
	
	protected function set_addthis_code() {
		$this->addthis_code = <<< EOD
var addthis_config = {
    "data_track_clickback": false,
    "data_track_addressbar": false,
    "data_track_textcopy": false,
    "ui_atversion": "300"
};
var addthis_product = "wpp-3.0.3";

EOD;
    }

    protected function set_ga_links(){
        $do_ga_links = true;

        $ga_links        = get_field( 'ga_links', 'option' );
        $sanitized_links = array();
        if( $do_ga_links ){
            if( is_array( $ga_links ) ){
                for( $i = 0; $i < count( $ga_links ); $i ++ ){
                    array_push( $sanitized_links, preg_replace( "(http[s]?:\/\/)", "", $ga_links[ $i ][ 'ga_link' ] ) ); //remove protocol
                }
            }
            $sanitized_links = json_encode( $sanitized_links );
            $referrer        = $_SERVER[ 'SERVER_NAME' ] . $_SERVER[ 'REQUEST_URI' ];
        }

        $this->ga_links_code = <<<EOD
!function ($) {
	var ga_links = $sanitized_links;
	for (var i = 0; i < ga_links.length; i++) {
		var selector = '';
		// this regex matches URLs without 'http'.
		if (!!(ga_links[i].match(/[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/))) {
			selector = "a[href*='" + ga_links[i] + "']";
		} else {
			selector = ga_links[i];
		}
		$(selector).click(function () {
			var linkTextStr = $(this).text().trim() || $($(this).html().trim()).attr('src');
			var referrerStr = '$referrer';
			var targetStr = $(this).attr('href');
			var eventStr = [referrerStr, targetStr, linkTextStr].join(' | ');
			_gaq.push(['_trackEvent', 'Special Links', 'click', eventStr]);
		});
	}
}(jQuery);

EOD;
    }

    /**
     * Use in twig templates to enable specific script on the fly
     * e.g. {{ js.load_js_src('addthis') }}
     *
     * @param $key
     */
    public function load_js_src( $key ){
        $this->js[ $key ][ 'load' ] = true;
    }

    public function get_js(){
        return $this->js;
    }

    /**
     *  Order keys in the order the files should be loaded.
     */
    protected function set_js(){
        //wp_deregister_script( 'jquery' );

        $this->js = array(
            /*'requirejs'          => array(
                'load'  => true,
                'src'   => $this->use_build_file ? JS_URL . 'build/build.js' : '//cdnjs.cloudflare.com/ajax/libs/require.js/2.1.15/require.min.js',
                'deps'  => array( 'jquery', 'jquery-ui-core' ),
                'v'     => '20160614',
                'local' => array(
                    'myAjax'  => array(
                        'ajaxurl' => admin_url( 'admin-ajax.php' )
                    ),
                    'loc'     => array(
                        'domain' => WMS_Server::instance()->domain
                    ),
                    'require' => $this->use_build_file ? null : array(
                        'baseUrl' => JS_URL,
                        'deps'    => array( JS_URL . 'require-config.js' )
                    ),
                ),
                'code'  => $this->ga_links_code
            ),*/
            'modernizr'           => array(
                'load' => true,
                'src'  => 'https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js',
                'deps' => array(),
                'v'    => '20160913'
            ),
            'browser'             => array(
                'load' => true,
                'src'  => JS_URL . 'lib/browser.js',
                'deps' => array( 'modernizr' ),
                'v'    => '1.0'
            ),
            'cookie'              => array(
                'load' => true,
                'src'  => JS_URL . 'vendor/jquery.cookie.js',
                'deps' => array( 'jquery' ),
                'v'    => '1.0'
            ),
            'purl'                => array(
                'load' => true,
                'src'  => JS_URL . 'vendor/purl.js',
                'deps' => array( 'jquery' ),
                'v'    => '1.0'
            ),
            'detect-swipe'        => array(
                'load' => true,
                'src'  => JS_URL . 'vendor/jquery.detect_swipe.js',
                'deps' => array( 'jquery' ),
                'v'    => '2.1.3'
            ),
            'featherlight'        => array(
                'load' => true,
                'src'  => JS_URL . 'vendor/featherlight.min.js',
                'deps' => array( 'jquery', 'detect-swipe' ),
                'v'    => '1.5.0'
            ),
            'featherlight-config' => array(
                'load' => true,
                'src'  => JS_URL . 'modules/featherlight.js',
                'deps' => array( 'featherlight' ),
                'v'    => '1.5.0'
            ),
            'common_lib'          => array(
                'load' => true,
                'src'  => JS_URL . 'modules/common.js',
                'deps' => array(
                    'jquery',
                    'jquery-ui-core',
                    'jquery-ui-tooltip',
                    'jquery-ui-widget',
                    'jquery-ui-mouse',
                    'purl'
                ),
                'v'    => '1.0.0'
            ),
            'main'                => array(
                'load'  => true,
                'src'   => JS_URL . 'lib/main.js',
                'deps'  => array( 'jquery', 'featherlight-config' ),
                'v'     => '1.73',
                'local' => array(
                    'myAjax' => array(
                        'ajaxurl' => str_replace( 'https', 'http', admin_url( 'admin-ajax.php' ) )
                    ),
                    'loc'    => array(
                        'domain' => Wms_Server::instance()->domain
                    )
                ),
                'code'  => $this->ga_links_code
            ),
            'accordion_tabs'      => array(
                'load' => true,
                'src'  => JS_URL . 'modules/accordion_tabs.js',
                'deps' => array( 'common_lib' ),
                'v'    => '1.0.0'
            ),
            'navigation'          => array(
                'load' => true,
                'src'  => JS_URL . 'modules/navigation.js',
                'deps' => array( 'common', 'jquery-ui-core', 'jquery-ui-tooltip' ),
                'v'    => '1.0.0'
            ),
            'quicklinks'          => array(
                'load' => true,
                'src'  => JS_URL . 'lib/quicklinks.js',
                'deps' => array(
                    'jquery-ui-draggable',
                    'jquery-ui-droppable',
                    'jquery-ui-sortable',
                    'cookie',
                    'featherlight-config',
                    'common_lib'
                ),
                'v'    => '1.0.5',
            ),
            'cycle'               => array(
                'load' => false,
                'src'  => JS_URL . 'vendor/jquery.cycle2.min.js',
                'deps' => array( 'jquery' ),
                'v'    => '2.15'
            ),
            'directory'           => array(
                'load' => true,
                'src'  => JS_URL . 'lib/directory.js',
                'deps' => array( 'jquery', 'common_lib' ),
                'v'    => '2.2',
            ),
            'tablesorter'         => array(
                'load' => true,
                'src'  => WMS_LIB_JS . '/jquery.tablesorter/jquery.tablesorter.min.js',
                'deps' => array( 'jquery' ),
                'v'    => '1.0',
                'code' => $this->tablesorter_code
            ),
            'tablesorter-pager'   => array(
                'load'   => true,
                'src'    => WMS_LIB_JS . '/jquery.tablesorter/addons/pager/jquery.tablesorter.pager.min.js',
                'styles' => array(
                    array(
                        'handle' => 'tablesorter-pager-style',
                        'src'    => WMS_LIB_JS . '/jquery.tablesorter/addons/pager/jquery.tablesorter.pager.min.css'
                    )
                ),
                'deps'   => array( 'jquery', 'tablesorter' ),
                'v'      => '1.0',
                'code'   => $this->tablesorter_pager_code
            ),
            'tablesorter-filter'  => array(
                'load'   => true,
                'src'    => WMS_LIB_JS . '/jquery.tablesorter/addons/filter/jquery.filter.min.js',
                'styles' => array(
                    array(
                        'handle' => 'tablesorter-filter-style',
                        'src'    => WMS_LIB_JS . '/jquery.tablesorter/addons/filter/jquery.filter.min.css'
                    )
                ),
                'deps'   => array( 'jquery', 'tablesorter' ),
                'v'      => '1.0'
            ),
            'printfriendly'       => array(
                'load' => true,
                'src'  => 'https://pf-cdn.printfriendly.com/ssl/main.js',
                'v'    => '1.0',
            ),
            'addthis'             => array(
                'load' => false,
                'src'  => 'http://s7.addthis.com/js/300/addthis_widget.js#pubid=' . ADDTHIS_ID,
                'v'    => '1.0',
                'code' => $this->addthis_code,
            ),
            'bootstrap'           => array(
                'load' => false,
                'src'  => JS_URL . 'lib/bootstrap.min.js',
                'deps' => array( 'jquery' ),
                'v'    => '1.0'
            ),
            'magazine'            => array(
                'load' => false,
                'src'  => JS_URL . 'lib/magazine.js',
                'deps' => array( 'jquery' ),
                'v'    => '1.0'
            )
        );
    }

    /**
     * @param $handle
     * @param $data
     *
     * @return string
     */
    protected function do_load( $handle, $data ){
        global $is_localist;

        // avoid loading jquery for localist template
        $deps = $is_localist ? array() : $data[ 'deps' ];

        //$handle = get_template() . '-' . $handle;

        if( $data[ 'src' ] ){
            wp_register_script( $handle, $data[ 'src' ], $deps, $data[ 'v' ], true );
        }

        if( $data[ 'local' ] ){ // always an array
            foreach( $data[ 'local' ] as $obj => $arr ){
                wp_localize_script( $handle, $obj, $arr );
            }
        }

        wp_print_scripts( $handle );

        if( $data[ 'styles' ] ){
            foreach( $data[ 'styles' ] as $style ){
                echo '<link rel="stylesheet" id="' . $style[ 'handle' ] . '" href="' . $style[ 'src' ] . '">';
                // wp_enqueue_style($style['handle'], , null, $data['v']);
            }
        }

        return $data[ 'code' ] ? $data[ 'code' ] : '';
    }

    public function load_frontend_js(){
        $code = '';
        foreach( $this->js as $handle => $data ){
            if( $data[ 'load' ] ){
                $code .= $this->do_load( $handle, $data );
            }
        }

        if( $code ){
            echo <<<EOD
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$code
				});
			</script>

EOD;
        }
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * singleton instance.
     *
     * @return void
     */
    private function __clone(){
    }

    /**
     * Private unserialize method to prevent unserializing of the singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup(){
    }
}

Meerkat16_Js::instance();