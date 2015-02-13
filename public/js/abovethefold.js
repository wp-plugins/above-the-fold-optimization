/**
 * Above the fold optimization Javascript
 *
 * This javascript handles the CSS delivery optimization.
 *
 * @package    abovethefold
 * @subpackage abovethefold/public
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */

window['abovethefold'] = {

    debug: false,

    /**
     * Load CSS asynchronicly
     *
     * @link https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery
     */
    css: function(files) {

        if (window['abovethefold'].debug) {
            if (!files) {
                console.error('abovethefold.css()', 'No files');
            } else {
                console.log('abovethefold.css()', files);
            }
        }
        if (!files) {
            return;
        }

        setTimeout(function() {
            window['abovethefold'].loadCSS(files);
        },0);
    },

    /**
     * Load CSS asynchronicly
     *
     * @link https://github.com/filamentgroup/loadCSS
     */

    loadCSS: function(files) {

        var csslinks = {};
        for (s in files) {

            csslinks[files[s]] = window.document.createElement( "link" );
            var ref = window.document.getElementsByTagName( "script" )[ 0 ];
            csslinks[files[s]].rel = "stylesheet";
            csslinks[files[s]].href = files[s];

            /**
             * temporarily, set media to something non-matching to ensure it'll fetch without blocking render
             */
            csslinks[files[s]].media = "only x";

            ref.parentNode.insertBefore( csslinks[files[s]], ref );
        }

        function asyncCSSRender() {

            var completed = true;
            for (s in files) {
                var found = false;
                for( var i = 0; i < window.document.styleSheets.length; i++ ) {
                    if( window.document.styleSheets[ i ].href && window.document.styleSheets[ i ].href.indexOf( files[s] ) >= 0 ){
                        found = true;
                    }
                }
                if (!found) {
                    console.log(files[s]);
                    completed = false;
                }
            }

            if( completed ) {
                function resetCSSMedia() {
                    setTimeout(function() {
                        for (s in files) {
                            csslinks[files[s]].media = "all";
                        }
                        if (window['abovethefold'].debug) {
                            console.info('abovethefold.css()', 'rendered' );
                        }
                    },10);
                }
                var raf = requestAnimationFrame || mozRequestAnimationFrame || webkitRequestAnimationFrame || msRequestAnimationFrame;
                if (raf)  {
                    raf(resetCSSMedia);
                } else {
                    resetCSSMedia();
                }
            } else {
                setTimeout( asyncCSSRender );
            }
        }

        /**
         * Start rendering
         */
        setTimeout(function() {
            asyncCSSRender();
        },0);

    }
};