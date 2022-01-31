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
        if ( ! static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected function __construct() {
        add_action('wp_head', array(&$this, 'detect_javascript'), 0);
        add_action('wp_enqueue_scripts', array(&$this, 'load_frontend_js'), 10);

    }

    /**
     * Use in twig templates to enable specific script on the fly
     * e.g. {{ js.load_js_src('addthis') }}
     *
     * @param $key
     */
    public function load_js_src($key) {
        $this->js[ $key ]['load'] = true;
    }

    /**
     * Handles JavaScript detection.
     *
     * Adds a `js` class to the root `<html>` element when JavaScript is detected.
     */
    public function detect_javascript() {
        echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
    }

    /**
     * Wrapper to automate enqueuing of scripts and inline code.
     */
    public function load_frontend_js() {
        $this->set_js();
        $this->set_special_cases();

        foreach ($this->js as $handle => $data) {
            if ( ! isset($data['load']) || $data['load'] === true) {
                $this->do_load($handle, $data);
            }
        }
    }

    /**
     * @param $handle
     * @param $data
     *
     * @return string
     */
    public function do_load($handle, $data) {
        if (isset($data['src'])) {
            $url       = $data['src'];
            $path      = $data['path'] ?? '';
            $deps      = $data['deps'] ?? null;
            $in_footer = $data['head'] ?? true;
            $version   = $data['v'] ?? filemtime($path);

            wp_enqueue_script($handle, $url, $deps, $version, $in_footer);
        }

        if (isset($data['meta'])) {
            foreach ($data['meta'] as $meta) {
                wp_script_add_data($handle, $meta[0], $meta[1]);
            }
        }

        if (isset($data['inline'])) {
            wp_add_inline_script($handle, 'jQuery(document).ready(function($){' . $data['inline']() . '});');
        }

        if (isset($data['local'])) { // always an array
            foreach ($data['local'] as $obj => $arr) {
                wp_localize_script($handle, $obj, $arr);
            }
        }

        //wp_print_scripts($handle);

        if (isset($data['styles'])) {
            foreach ($data['styles'] as $style) {
                wp_enqueue_style($style['handle'], $style['src']);
            }
        }
    }

    public function get_js() {
        return $this->js;
    }

    /**
     *  Order keys in the order the files should be loaded.
     */
    protected function set_js() {
        $is_local    = Wms_Server::instance()->is_local();
        $protocol    = $is_local ? 'http://' : 'https://';
        $ajax_url    = $is_local ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php');
        $site_url    = Wms_Server::instance()->site_url;
        $site_domain = Wms_Server::instance()->domain;
        $this->js    = array(
            'typekit_cache'       => array(
                'src' => THEME_JS_URL . '/lib/typekit-cache.min.js',
                'v'   => '1.0.13'
            ),
            'typekit'             => array(
                'src'    => '//use.typekit.net/jwl0jgy.js',
                'deps'   => array('typekit_cache'),
                'head'   => true,
                'inline' => function() {
                    return <<< EOD
try {
    Typekit.load({ async: true });
}
catch ( e ) {}
  
EOD;
                }),
            'tag_manager'         => array(
                'src' => THEME_JS_URL . '/lib/tagmanager.js',
                'v'   => '1.0.0'
            ),
            'html5'               => array(
                'src'  => THEME_JS_URL . '/vendor/html5.js',
                'v'    => '3.7.3',
                'head' => true,
                'meta' => array(
                    array('conditional', 'lt IE 9')
                )
            ),
            'modernizr'           => array(
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js',
                'v'   => '20160913'
            ),
            'browser'             => array(
                'src'  => THEME_JS_URL . '/lib/browser.js',
                'deps' => array('modernizr'),
                'v'    => '1.0'
            ),
            'skip-link-focus-fix' => array(
                'src' => THEME_JS_URL . '/vendor/skip-link-focus-fix.js',
                'v'   => '20151112'
            ),
            'printfriendly'       => array(
                'src' => 'https://pf-cdn.printfriendly.com/ssl/main.js',
                'v'   => '1.0',
            ),
            'addthis'             => array(
                'src'    => 'https://s7.addthis.com/js/300/addthis_widget.js#pubid=' . ADDTHIS_ID,
                'v'      => '1.0',
                'inline' => function() {
                    return <<< EOD
var addthis_config = {
    "data_track_clickback": false,
    "data_track_addressbar": false,
    "data_track_textcopy": false,
    "ui_atversion": "300"
};
var addthis_product = "wpp-3.0.3";

EOD;
                }),
            'cookie'              => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/jquery.cookie.js',
                'deps' => array('jquery'),
                'v'    => '1.0'
            ),
            'purl'                => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/purl.js',
                'deps' => array('jquery'),
                'v'    => '1.0'
            ),
            'detect_swipe'        => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/jquery.detect_swipe.js',
                'deps' => array('jquery'),
                'v'    => '2.1.3'
            ),
            'featherlight'        => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/featherlight/featherlight.min.js',
                'deps' => array('jquery', 'detect_swipe'),
                'v'    => '1.5.0'
            ),
            'featherlight-config' => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/featherlight/featherlight-config.js',
                'deps' => array('featherlight'),
                'v'    => '1.5.3'
            ),
            'main'                => array(
                'src'   => THEME_JS_URL . '/main.js',
                'path'  => THEME_JS_PATH . '/main.js',
                'deps'  => array(
                    'jquery',
                    'featherlight-config',
                    'jquery-color',
                    'jquery-ui-draggable',
                    'jquery-ui-droppable',
                    'jquery-ui-sortable',
                    'jquery-ui-core',
                    'jquery-ui-tooltip',
                    'jquery-ui-widget',
                    'jquery-ui-mouse',
                    'purl'
                ),
                //'v'      => file_exists(THEME_JS_PATH . '/main.js') ? filemtime(THEME_JS_PATH . '/main.js') : time(),
                'local' => array(
                    'myAjax' => array(
                        'wwwurl'  => Wms_Server::instance()->www,
                        'ajaxurl' => $ajax_url,
                        'siteurl' => $site_url,
                        'domain'  => $site_domain,
                    )
                ),
            ),
            'expando_tables'      => array(
                'src'  => THEME_JS_URL . '/modules/expando_tables.js',
                'deps' => array('jquery'),
                'v'    => '1.0.0'
            ),
            'navigation'          => array(
                'src'  => THEME_JS_URL . '/modules/navigation.js',
                'deps' => array(
                    'jquery-ui-core',
                    'jquery-ui-tooltip',
                ),
                'v'    => '1.1.1'
            ),
            'theme_uisearch'      => array(
                'src'  => THEME_JS_URL . '/modules/uisearch.js',
                'deps' => array('jquery'),
                'v'    => '1.0.0'
            ),
            'cycle'               => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/jquery.cycle/jquery.cycle2.min.js',
                'deps' => array('jquery'),
                'v'    => '2.15'
            ),
            'directory'           => array(
                'src'  => THEME_JS_URL . '/lib/directory.js',
                'deps' => array(
                    'jquery',
                    //'common_lib'
                ),
                'v'    => '2.2',
            ),
            'tablesorter'         => array(
                'src'    => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/jquery.tablesorter.min.js',
                'deps'   => array('jquery'),
                'v'      => '1.0',
                'inline' => function() {
                    return <<< EOD
$('table.tablesorter, table#wms-jquery-tablesorter').each(function() {
    var sortArgs = {};
    if ($(this).data('sortlist')) {
        /* Table provides default sort instructions */
        sortArgs.sortList = eval($(this).data('sortlist'));
    } else {
        sortArgs.sortList = [ [0, 0] ]; /* default to first column ASC */
    }
    $(this).tablesorter(sortArgs);
});

EOD;
                }),
            /*'tablesorter-pager'   => array(
                'load'   => true,
                'src'    => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/addons/pager/jquery.tablesorter.pager.min.js',
                'styles' => array(
                    array(
                        'handle' => 'tablesorter-pager-style',
                        'src'    => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/addons/pager/jquery.tablesorter.pager.min.css'
                    )
                ),
                'deps'   => array('jquery', 'tablesorter'),
                'v'      => '1.0',
                'inline' => function() {
                    return <<< EOD
//var pager = jQuery('<div class="pager">'+
//'<form>'+
//'<span class="first"></span>'+
//'<span class="prev"></span>'+
//'<input type="text" class="pagedisplay"/>'+
//'<span class="next"></span>'+
//'<span class="last"></span>'+
//'<select class="pagesize">'+
//'<option value="10">10</option>'+
//'<option value="50">50</option>'+
//'<option value="100">100</option>'+
//'<option value="99999" selected>All</option>'+
//'</select>'+
//'</form>'+
//'</div>').prependTo('table.tablesorter.tablesorter-pager');
//jQuery('table.tablesorter.tablesorter-pager').tablesorterPager({container: pager, size: 9999, positionFixed: false});

EOD;
                }),*/
            'tablesorter-filter'  => array(
                'load'   => true,
                'src'    => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/addons/filter/jquery.filter.min.js',
                'styles' => array(
                    array(
                        'handle' => 'tablesorter-filter-style',
                        'src'    => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/addons/filter/jquery.filter.min.css'
                    )
                ),
                'deps'   => array('jquery', 'tablesorter'),
                'v'      => '1.0'
            ),
            'bootstrap'           => array(
                'load' => false,
                'src'  => THEME_JS_URL . '/lib/bootstrap.min.js',
                'deps' => array('jquery'),
                'v'    => '1.0'
            ),
            'magazine'            => array(
                'load' => false,
                'src'  => THEME_JS_URL . '/lib/magazine.js',
                'deps' => array('jquery'),
                'v'    => '1.0'
            ),
            'facet'               => array(
                'src'  => THEME_JS_URL . '/lib/facet.js',
                'deps' => array('jquery'),
                'v'    => '1.0'
            )
        );
    }

    /**
     * Load scripts based on environment.
     */
    protected function set_special_cases() {
        global $blog_id;

        // SPECIAL SCRIPT FOR ADMISSION HOMEPAGE
        if (in_array($blog_id, array(2, 26, 129))) {
            $this->js['myadmission_tracking'] =
                array(
                    'src'  => 'https://myadmission.williams.edu/ping',
                    'deps' => array(),
                    'v'    => null
                );
        }

        if (93 == $blog_id && $_SERVER['REQUEST_URI'] == '/localist-template/') {
            $this->js['tablesorter']['load']        = false;
            $this->js['tablesorter-filter']['load'] = false;
            $this->js['tablesorter-pager']['load']  = false;
        }

        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }

        if (is_singular() && wp_attachment_is_image()) {
            wp_enqueue_script('twentysixteen-keyboard-image-navigation', THEME_JS_URL . '/vendor/keyboard-image-navigation.js', array('jquery'), '20151104');
        }

    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * singleton instance.
     *
     * @return void
     */
    private function __clone() {
    }

    /**
     * Private unserialize method to prevent unserializing of the singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup() {
    }
}

Meerkat16_Js::instance();
