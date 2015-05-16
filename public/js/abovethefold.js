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

        if (this.debug) {
            if (!files) {
                console.error('abovethefold.css()', 'No files');
            } else {
                console.log('abovethefold.css()', files);
            }
        }
        if (!files) {
            return;
        }

        for (i in files) {
            m = files[i][0].join(',');
            if (this.debug) {
                if (!res[m]) { res[m] = []; }
                res[m].push(files[i][1]);
            }
            this.loadCSS(files[i][1],false,m);
        }
    },

    /**
     * loadCSS (v0.1.6) improved with requestAnimationFrame following Google guidelines.
     *
     * @link https://github.com/filamentgroup/loadCSS/
     * @link https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery
     */
    loadCSS: function( href, before, media, callback ) {

        // Arguments explained:
        // `href` is the URL for your CSS file.
        // `before` optionally defines the element we'll use as a reference for injecting our <link>
        // By default, `before` uses the first <script> element in the page.
        // However, since the order in which stylesheets are referenced matters, you might need a more specific location in your document.
        // If so, pass a different reference element to the `before` argument and it'll insert before that instead
        // note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
        var ss = window.document.createElement( "link" );
        var ref = before || window.document.getElementsByTagName( "script" )[ 0 ];
        var sheets = window.document.styleSheets;
        ss.rel = "stylesheet";
        ss.href = href;
        // temporarily, set media to something non-matching to ensure it'll fetch without blocking render
        ss.media = "only x";
        // DEPRECATED
        if( callback ) {
            ss.onload = callback;
        }

        // inject link
        ref.parentNode.insertBefore( ss, ref );
        // This function sets the link's media back to `all` so that the stylesheet applies once it loads
        // It is designed to poll until document.styleSheets includes the new sheet.
        ss.onloadcssdefined = function( cb ){
            var defined;
            for( var i = 0; i < sheets.length; i++ ){
                if( sheets[ i ].href && sheets[ i ].href.indexOf( href ) > -1 ){
                    defined = true;
                }
            }
            if( defined ){
                cb();
            }
            else {
                setTimeout(function() {
                    ss.onloadcssdefined( cb );
                });
            }
        };
        ss.onloadcssdefined(function() {
            window['abovethefold'].raf(function() {
                ss.media = media || "all";
            });
        });
    },

    raf: function(callback) {
        if (typeof requestAnimationFrame === 'function') {
            requestAnimationFrame(callback);
        } else if (typeof mozRequestAnimationFrame === 'function') {
            mozRequestAnimationFrame(callback);
        } else if (typeof webkitRequestAnimationFrame === 'function') {
            webkitRequestAnimationFrame(callback);
        } else if (typeof msRequestAnimationFrame === 'function') {
            msRequestAnimationFrame(callback);
        } else {
            jQuery(document).ready(callback);
        }
    }
};