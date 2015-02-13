<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */
class Abovethefold_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, &$config ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->config =& $config;

		add_filter('plugin_action_links_abovethefold/abovethefold.php', array( $this, 'settings_link' ) );

	}

	/**
	 * Settings link on plugin overview.
	 *
	 * @since    1.0
	 * @param $links
	 * @return mixed
	 */
	public function settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=abovethefold">'.__('Settings').'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	/**
	 * Admin menu option.
	 *
	 * @since    1.0
	 */
	public function admin_menu() {

		$menuname = __('Above the fold', 'abovethefold');

		if( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {

			/**
			 * Add settings link to Performance tab of W3 Total Cache
			 */
			add_submenu_page('w3tc_dashboard', $menuname, $menuname, 'manage_options', 'abovethefold', array(
				&$this,
				'settings_page'
			));
		}

		/**
		 * Add settings link to Settings tab
		 */
		add_options_page( $menuname, $menuname, 'manage_options', 'abovethefold', array(
			&$this,
			'settings_page'
		));
	}

	public function register_settings() {

		// Register settings (data-storage)
		register_setting('abovethefold_group', 'abovethefold_criticalcss'); // Critical CSS
		register_setting('abovethefold_group', 'abovethefold_cssdelivery'); // Optimize CSS delivery
		register_setting('abovethefold_group', 'abovethefold_cssdelivery_ignorelist'); // Ignore CSS files
		register_setting('abovethefold_group', 'abovethefold_hidead'); // Hide ad

	}

	public function settings_page() {

		if (isset($_REQUEST['hideadd']) && intval($_REQUEST['hideadd']) === 1) {
			update_option('abovethefold_hidead', 1);
		}

		?>
        	<div class="wrap"></div>


                <h2><?php print __('Above the fold optimization', 'abovethefold') ?></h2>
                <p>This plugin enables to pass the "<em>Eliminate render-blocking JavaScript and CSS in above-the-fold content</em>"-rule from <a href="https://developers.google.com/speed/pagespeed/insights/" target="_blank">Google PageSpeed Insights</a> to be able to obtain a 90+ score using other optimization plugins such as <a href="plugin-install.php?tab=search&s=w3+total+cache">W3 Total Cache</a>.</p>
<?php
	if (intval(get_option('abovethefold_hidead')) !== 1) {
?>
	<div class="updated fade" style="position:relative;">
		<img src="/wp-content/plugins/abovethefold/admin/google-pagespeed-100.png" style="float:right; margin-top:10px;">
		<p style="font-size:16px;">Need help obtaining a Google PageSpeed 100-score for mobile and desktop?<br />Contact me at <a href="mailto:info@optimalisatie.nl?subject=Above%20the%20fold">info@optimalisatie.nl</a> for paid optimization service.</p>
		<p style="position:absolute;bottom:0px;right:10px;"><a href="admin.php?page=abovethefold&amp;hideadd=1" class="button">hide this message</a></p>
        <p>Jan Jaap Hakvoort &dash; <a href="https://optimalisatie.nl/#utm_source=wordpress&utm_medium=link&utm_term=optimization&utm_campaign=Above%20the%20fold" target="_blank">https://optimalisatie.nl/</a> &dash; <a href="https://github.com/optimalisatie" target="_blank">Github</a> &dash; <a href="https://wordpress.org/support/plugin/above-the-fold-optimization" target="_blank">Report a bug</a> &dash; <a href="https://wordpress.org/support/view/plugin-reviews/above-the-fold-optimization?rate=5" target="_blank">Review this plugin</a></p>
	</div>
<?php
	} else {
?>
	<div class="updated fade" style="position:relative;">
		<p><em>Developed by:</em><br />Jan Jaap Hakvoort &dash; <a href="https://optimalisatie.nl/#utm_source=wordpress&utm_medium=link&utm_term=optimization&utm_campaign=Above%20the%20fold" target="_blank">https://optimalisatie.nl/</a> &dash; <a href="https://github.com/optimalisatie" target="_blank">Github</a> &dash; <a href="https://wordpress.org/support/plugin/above-the-fold-optimization" target="_blank">Report a bug</a> &dash; <a href="https://wordpress.org/support/view/plugin-reviews/above-the-fold-optimization?rate=5" target="_blank">Review this plugin</a></p>
	</div>
<?php
	}
        	// Check permissions before continuing
        	if(!current_user_can('manage_options')) {
        		wp_die(__('You do not have sufficient permissions to access this configuration.'));
        	}

        	if($_POST) {

        		update_option('abovethefold_criticalcss', trim(stripslashes($_POST['abovethefold_criticalcss'])));
        		update_option('abovethefold_cssdelivery', (intval($_POST['abovethefold_cssdelivery']) === 1) ? 1 : 0);
        		update_option('abovethefold_debug', (intval($_POST['abovethefold_debug']) === 1) ? 1 : 0);
        		update_option('abovethefold_cssdelivery_ignorelist', trim(stripslashes($_POST['abovethefold_cssdelivery_ignorelist'])));
        ?>
        		<div id="message" class="updated">
        			<p><strong><?php _e('Settings saved.') ?></strong></p>
        		</div>
        <?php
        	}
        ?>

        	<form method="post" action="admin.php?page=abovethefold">

        		<h2>Critical Path CSS</h2>
        		<p>Generate Critical Path CSS for your main WordPress pages (e.g. the front page and blog page), combine and minify the resulting CSS and enter it into the field below. The critical path CSS is inserted inline into the <code>&lt;head&gt;</code> of the page and when <em>Optimize CSS Delivery</em> is enabled, all other CSS links are loaded asynchronously.</p>
        		<p>A good Critical Path CSS generator is <a href="https://github.com/pocketjoso/penthouse" target="_blank">Penthouse</a> which is available online via <a href="http://jonassebastianohlsson.com/criticalpathcssgenerator/" target="_blank">this form</a>.<br />
        		Other generators are <a href="https://github.com/addyosmani/critical" target="_blank">Critical</a> and <a href="https://github.com/filamentgroup/criticalcss" target="_blank">Critical CSS</a> which are available as Node.js and Grunt.js modules.</p>
        		<textarea style="width: 95%;height:250px;font-size:11px;" name="abovethefold_criticalcss"><?php echo htmlentities(get_option('abovethefold_criticalcss')); ?></textarea>
        		<br />

        		<h2>Optimize CSS Delivery</h2>
				<label style="display:block;margin-bottom:2px;"><input type="checkbox" name="abovethefold_cssdelivery" value="1"<?php if (get_option('abovethefold_cssdelivery') === '' || intval(get_option('abovethefold_cssdelivery')) === 1) { print ' checked'; } ?>> Enabled</label>
				When enabled, CSS files are moved to the footer, loaded asynchronously and rendered via <code>requestAnimationFrame</code> API following the <a href="https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery" target="_blank">recommendations by Google</a>.

        		<h4 style="margin-bottom:2px;">Ignore-list</h4>
        		<p style="margin-top:2px;margin-bottom:4px;">Enter files to ignore in CSS delivery optimization.</p>
        		<textarea style="width: 200px;height:70px;" name="abovethefold_cssdelivery_ignorelist"><?php echo htmlentities(get_option('abovethefold_cssdelivery_ignorelist')); ?></textarea>
        		<br /><br />
        		<label><input type="checkbox" name="abovethefold_debug" value="1"<?php if (get_option('abovethefold_debug') === '' || intval(get_option('abovethefold_debug')) === 1) { print ' checked'; } ?>> Show debug info in browser console for logged in admin-users.</label>

        		<br /><br />
        		<input type="submit" class="button-primary" value="<?php _e('Save Options'); ?>" />
        	</form>

        <?php
	}

}