=== Above The Fold Optimization ===
Contributors: optimalisatie
Donate link: https://optimalisatie.nl/
Tags: optimization, above the fold, critical path, css
Requires at least: 3.0.1
Tested up to: 4.2.2
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin enables to pass the "Eliminate render-blocking JavaScript and CSS in above-the-fold content"-rule from Google PageSpeed Insights to be able to obtain a high PageSpeed score using other optimization plugins such as W3 Total Cache.

== Description ==

This plugin enables to pass the "`Eliminate render-blocking JavaScript and CSS in above-the-fold content`"-rule from [Google PageSpeed Insights](https://developers.google.com/speed/pagespeed/insights/).

The functionality of this plugin is simple and light-weight. The plugin simply inserts Critical Path CSS code inline, helps to create it and optionally optimizes the delivery of the full website CSS.

The plugin is intended to work together with other optimization plugins such as [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/), [WP Super Cache](https://wordpress.org/plugins/wp-super-cache/) and [Autoptimize](https://wordpress.org/plugins/autoptimize/).

Using just 3 plugins it is possible to achieve a PageSpeed 100-score. The following demo is an original WordPress blog ``v4.2.2`` with the default theme, [Autoptimize](https://wordpress.org/plugins/autoptimize/) javascript and CSS optimization, [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/) for full-page cache and the Above The Fold Optimization plugin.

https://abovethefold.optimalisatie.nl/

https://developers.google.com/speed/pagespeed/insights/?url=https%3A%2F%2Fabovethefold.optimalisatie.nl%2F&tab=mobile

### Automated Critical Path CSS generation

The plugin enables automated Critical Path CSS generation via [Penthouse.js](https://github.com/pocketjoso/penthouse). The plugin will execute Penthouse.js to generate Critical Path CSS for multiple responsive dimensions and pages, combines the resulting CSS-code and then compresses the CSS-code via [Clean-CSS](https://github.com/jakubpawlowicz/clean-css) to achieve the smallest CSS-code to insert inline into the ``<head>`` of the page.

If custom installation of software is not possible on the server it is possible to use the online Critical Path CSS generator based on Penthouse.js on the following address:

http://jonassebastianohlsson.com/criticalpathcssgenerator/

Other Critical Path CSS generators are [Critical](https://github.com/addyosmani/critical) and [Critical CSS](https://github.com/filamentgroup/criticalcss) which are available as Node.js and Grunt.js modules.

== Installation ==

To make use of automated Critical Path CSS generation it is required to install the following software on the server:

1. [PhantomJS](http://phantomjs.org/): ``npm install -g phantomjs``
2. [Clean-CSS](https://github.com/jakubpawlowicz/clean-css): ``npm install -g clean-css``

To be able to generate Critical Path CSS from within the WordPress admin both software need to be executable from PHP which may pose a security risk. An alternative option is to generate a CLI command to execute via SSH so that the software does not need to be executeable from PHP.

Next install the WordPress plugin.

1. Upload the `above-the-fold-optimization/` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the plugin settings-page
4. Configure and start the Critical Path Generator

== Screenshots ==

1. Automated Critical Path CSS generation from within the WordPress admin.
2. Inline CSS configuration and optimization.

== Changelog ==

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
