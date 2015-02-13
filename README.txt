=== Above The Fold Optimization ===
Contributors: optimalisatie
Donate link: https://optimalisatie.nl/#wordpress
Tags: optimization, above the fold, critical path, css
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin enables to pass the "Eliminate render-blocking JavaScript and CSS in above-the-fold content"-rule from Google PageSpeed Insights to be able to obtain a 90+ score using other optimization plugins such as W3 Total Cache.

== Description ==

This plugin enables to pass the "`Eliminate render-blocking JavaScript and CSS in above-the-fold content`"-rule from [Google PageSpeed Insights](https://developers.google.com/speed/pagespeed/insights/) to be able to obtain a 90+ score using other optimization plugins such as [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/).

The functionality of this plugin is fairly simple. You need to generate Critical Path CSS for your main WordPress pages (e.g. the front page and blog page), combine and minify the resulting CSS and enter it into the plugin settings. The critical path CSS is inserted inline into the `<head>` of the page and CSS links are loaded asynchronously and rendered via `requestAnimationFrame API` following the [recommendations by Google](https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery).

A good Critical Path CSS generator is [Penthouse](https://github.com/pocketjoso/penthouse) which is available online via [this form](http://jonassebastianohlsson.com/criticalpathcssgenerator/).

Other generators are [Critical](https://github.com/addyosmani/critical) and [Critical CSS](https://github.com/filamentgroup/criticalcss) which are available as Node.js and Grunt.js modules.