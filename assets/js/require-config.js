!function () {

    require.config({
        paths: {
            // 'jquery'          : 'vendor/jquery-1.11.3',
            'jquery': 'empty:',
            // 'migrate'        : 'vendor/jquery-migrate',
            'jquery_ui': 'vendor/jquery-ui.min',
            'detect_swipe': 'vendor/jquery.detect_swipe',
            'cookie': 'vendor/jquery.cookie',
            'featherlight_src': 'vendor/featherlight.min',
            // 'fancybox'       : 'vendor/jquery.fancybox/jquery.fancybox',
            'purl': 'lib/purl',
            'main': 'lib/main',
            'quicklinks': 'lib/quicklinks',
            'common': 'modules/common',
            'accordion_tabs': 'modules/accordion_tabs',
            'featherlight': 'modules/featherlight',
            'functions': 'lib/functions'
        },
        // Register jQuery plugins
        shim: {
            // 'migrate'        : ['jquery'],
            // 'fancybox'       : ['jquery'],
            'cookie': ['jquery'],
            'detect_swipe': ['jquery'],
            'featherlight_src': ['jquery', 'detect_swipe']
        },
        // If set to true, skips the data-main attribute scanning done to start module loading.
        // Useful if RequireJS is embedded in a utility library that may interact with other
        // RequireJS library on the page, and the embedded version should not do data-main loading.
        skipDataMain: true
    });

    define('jquery', function () {
        return jQuery;
    });

// Needed for dynamic-loading (non-concatenated) version.
// Not used by r.js.
    var mods = [
        'main',
        'common',
        'featherlight',
        'functions',
        'quicklinks',
        'accordion_tabs'
    ];
    require(mods, function () {
    });
}();