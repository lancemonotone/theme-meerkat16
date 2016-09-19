({
    /*
     * https://github.com/requirejs/r.js/blob/master/build/example.build.js
     */

    //By default, all modules are located relative to this path. If baseUrl
    //is not explicitly set, then all modules are loaded relative to
    //the directory that holds the build file. If appDir is set, then
    //baseUrl should be specified as relative to the appDir.
    baseUrl: './',

    //By default all the configuration for optimization happens from the command
    //line or by properties in the config file, and configuration that was
    //passed to requirejs as part of the app's runtime "main" JS file is *not*
    //considered. However, if you prefer the "main" JS file configuration
    //to be read for the build so that you do not have to duplicate the values
    //in a separate configuration, set this property to the location of that
    //main JS file. The first requirejs({}), require({}), requirejs.config({}),
    //or require.config({}) call found in that file will be used.
    //As of 2.1.10, mainConfigFile can be an array of values, with the last
    //value's config take precedence over previous values in the array.
    mainConfigFile: 'require-config.js',

    //If you only intend to optimize a module (and its dependencies), with
    //a single file as the output, you can specify the module options inline,
    //instead of using the 'modules' section above. 'exclude',
    //'excludeShallow', 'include' and 'insertRequire' are all allowed as siblings
    //to name. The name of the optimized file is specified by 'out'.
    name: 'vendor/almond',
    include: [
        'functions',
        'main',
        'common',
        'featherlight',
        'quicklinks',
        'accordion_tabs'
        // 'grid',
    ],
    excludeShallow: [
        'jquery'
    ],
    out: 'build/build.js',
    /*paths: {
        jquery: define(function () {
            return jQuery;
        })
    },*/

    //Wrap any build bundle in a start and end text specified by wrap.
    //Use this to encapsulate the module code so that define/require are
    //not globals. The end text can expose some globals from your file,
    //making it easy to create stand-alone libraries that do not mandate
    //the end user use requirejs.
    /*
     wrap: {
     start: "(function() {",
     end: "}());"
     },
     */

    //Another way to use wrap, but uses default wrapping of:
    //(function() { + content + }());
    // wrap: true,
    wrap: {
        startFile: "wrap.start.js",
        endFile: "wrap.end.js"
    },

    //When the optimizer copies files from the source location to the
    //destination directory, it will skip directories and files that start
    //with a ".". If you want to copy .directories or certain .files, for
    //instance if you keep some packages in a .packages directory, or copy
    //over .htaccess files, you can set this to null. If you want to change
    //the exclusion rules, change it to a different regexp. If the regexp
    //matches, it means the directory will be excluded. This used to be
    //called dirExclusionRegExp before the 1.0.2 release.
    //As of 1.0.3, this value can also be a string that is converted to a
    //RegExp via new RegExp().
    fileExclusionRegExp: /^(r|build)\.js$/,

    //How to optimize all the JS files in the build output directory.
    //Right now only the following values
    //are supported:
    //- "uglify": (default) uses UglifyJS to minify the code. Before version
    //2.2, the uglify version was a 1.3.x release. With r.js 2.2, it is now
    //a 2.x uglify release.
    //- "uglify2": in version 2.1.2+. Uses UglifyJS2. As of r.js 2.2, this
    //is just an alias for "uglify" now that 2.2 just uses uglify 2.x.
    //- "closure": uses Google's Closure Compiler in simple optimization
    //mode to minify the code. Only available if running the optimizer using
    //Java.
    //- "closure.keepLines": Same as closure option, but keeps line returns
    //in the minified files.
    //- "none": no minification will be done.
    optimize: "none",

    //If using UglifyJS2 for script optimization, these config options can be
    //used to pass configuration values to UglifyJS2. As of r.js 2.2, UglifyJS2
    //is the only uglify option, so the config key can just be 'uglify' for
    //r.js 2.2+.
    //For possible `output` values see:
    //https://github.com/mishoo/UglifyJS2#beautifier-options
    //For possible `compress` values see:
    //https://github.com/mishoo/UglifyJS2#compressor-options
    uglify2: {
        //Example of a specialized config. If you are fine
        //with the default options, no need to specify
        //any of these properties.
        output: {
            beautify: true
        }
    },

    //If set to true, any files that were combined into a build bundle will be
    //removed from the output folder.
    removeCombined: true,

    //If skipModuleInsertion is false, then files that do not use define()
    //to define modules will get a define() placeholder inserted for them.
    //Also, require.pause/resume calls will be inserted.
    //Set it to true to avoid this. This is useful if you are building code that
    //does not use require() in the built project or in the JS files, but you
    //still want to use the optimization tool from RequireJS to concatenate modules
    //together.
    skipModuleInsertion: false,

    //As of 2.1.11, shimmed dependencies can be wrapped in a define() wrapper
    //to help when intermediate dependencies are AMD have dependencies of their
    //own. The canonical example is a project using Backbone, which depends on
    //jQuery and Underscore. Shimmed dependencies that want Backbone available
    //immediately will not see it in a build, since AMD compatible versions of
    //Backbone will not execute the define() function until dependencies are
    //ready. By wrapping those shimmed dependencies, this can be avoided, but
    //it could introduce other errors if those shimmed dependencies use the
    //global scope in weird ways, so it is not the default behavior to wrap.
    //To use shim wrapping skipModuleInsertion needs to be false.
    //More notes in http://requirejs.org/docs/api.html#config-shim
    wrapShim: true,

    //By default, comments that have a license in them are preserved in the
    //output when a minifier is used in the "optimize" option.
    //However, for a larger built files there could be a lot of
    //comment files that may be better served by having a smaller comment
    //at the top of the file that points to the list of all the licenses.
    //This option will turn off the auto-preservation, but you will need
    //work out how best to surface the license information.
    //NOTE: As of 2.1.7, if using xpcshell to run the optimizer, it cannot
    //parse out comments since its native Reflect parser is used, and does
    //not have the same comments option support as esprima.
    preserveLicenseComments: false
});