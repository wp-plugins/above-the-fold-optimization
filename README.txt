=== Above The Fold Optimization ===
Contributors: optimalisatie
Donate link: https://en.optimalisatie.nl/
Tags: optimization, above the fold, critical path, css, localization, javascript, minification, minify, minify css, minify stylesheet, optimize, speed, stylesheet, pagespeed, google pagespeed
Requires at least: 3.0.1
Tested up to: 4.2.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin enables to pass the "Eliminate render-blocking JavaScript and CSS in above-the-fold content"-rule from Google PageSpeed Insights to be able to obtain a high PageSpeed score using other optimization plugins such as W3 Total Cache.

== Description ==

This plugin enables to pass the "`Eliminate render-blocking JavaScript and CSS in above-the-fold content`"-rule from [Google PageSpeed Insights](https://developers.google.com/speed/pagespeed/insights/).

The functionality of this plugin is simple and light-weight. The plugin simply inserts Critical Path CSS code inline, helps to create it and optionally optimizes the delivery of the full website CSS.

The plugin is intended to work together with other optimization plugins such as [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/) and [Autoptimize](https://wordpress.org/plugins/autoptimize/).

**Note:** *The plugin is intended to achieve the best possible result, not easy usage. It is intended for advanced WordPress users and optimization professionals.*

### Critical Path CSS generation

The plugin enables automated Critical Path CSS generation via [Penthouse.js](https://github.com/pocketjoso/penthouse). The plugin will execute Penthouse.js to generate Critical Path CSS for multiple responsive dimensions and pages. It then combines the resulting CSS-code and then compresses the CSS-code via [Clean-CSS](https://github.com/jakubpawlowicz/clean-css).

### Full CSS extraction

The plugin enables the extraction of the full CSS from pages for use in critical path CSS generation.

### Javascript localization

The plugin enables the localization of external javascript resources such as Google Analytics and Facebook SDK to pass the "[Leverage browser caching](https://developers.google.com/speed/docs/insights/LeverageBrowserCaching)"-rule from Google PageSpeed Insights.

== Installation ==

### WordPress plugin installation

1. Upload the `above-the-fold-optimization/` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the plugin settings-page
4. Generate Critical Path CSS

To make use of automated Critical Path CSS generation it is required to install the following software on the server:

1. [PhantomJS](http://phantomjs.org/): ``npm install -g phantomjs``
2. [Clean-CSS](https://github.com/jakubpawlowicz/clean-css): ``npm install -g clean-css``

To be able to generate Critical Path CSS from within the WordPress admin the software needs to be executable from PHP which may pose a security risk. An alternative option is to generate a CLI command to execute via SSH.


== Screenshots ==

1. Critical Path CSS settings, Google font optimization, javascript localization
2. Automated critical path CSS generation via Penthouse.js
3. Full CSS extraction

== Changelog ==

= 2.3 =
* Added option to include Google fonts from ``@import`` within the CSS-code in [Google Webfont Optimizer](https://nl.wordpress.org/plugins/google-webfont-optimizer/).
* Added option to localize external javascript files.
* Enhanced full-CSS extraction.

= 2.2.1 =
* Added option to remove CSS files
* CSS extraction bug (old PHP versions).

= 2.2 =
* Improved admin
* Online generator instructions
* Full CSS extraction

= 2.1.1 =
* Addslashes bug.

= 2.1 =
* Code improvements.

= 2.0 =
* Automated Critical Path CSS generation via [Penthouse.js](https://github.com/pocketjoso/penthouse).
* Automated inline CSS optimization via [Clean-CSS](https://github.com/jakubpawlowicz/clean-css).
* Improved CSS delivery optimization.
* Improved configuration.
* Sourcecode published on [Github](https://github.com/optimalisatie/wordpress-above-the-fold-optimization).

= 1.0 =
* The first version.

== Upgrade Notice ==

= 2.0 =
The upgrade requires a new configuration of Critical Path CSS. The configuration from version 1.0 will not be preserved.
