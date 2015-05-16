<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @since      2.0
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */
class Abovethefold_Admin {

	/**
	 * Above the fold controller
	 *
	 * @since    1.0
	 * @access   public
	 * @var      object    $CTRL
	 */
	public $CTRL;

	/**
	 * Options
	 *
	 * @since    2.0
	 * @access   public
	 * @var      array
	 */
	public $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( &$CTRL ) {

		$this->CTRL =& $CTRL;
		$this->options =& $CTRL->options;

		$this->CTRL->loader->add_filter('plugin_action_links_above-the-fold-optimization/abovethefold.php', $this, 'settings_link' );

		$this->CTRL->loader->add_action('admin_post_abovethefold_update', $this,  'update_settings');
		$this->CTRL->loader->add_action('admin_post_abovethefold_generate', $this,  'generate_critical_css');

		$this->CTRL->loader->add_action( 'admin_notices', $this, 'show_notices' );

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
		global $submenu;

		if( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {

			/**
			 * Add settings link to Performance tab of W3 Total Cache
			 */
			array_splice( $submenu['w3tc_dashboard'], 2, 0, array(
				array(__('Above The Fold', 'abovethefold'), 'manage_options',  admin_url('admin.php?page=abovethefold'), __('Above The Fold Optimization', 'abovethefold'))
			) );

			add_submenu_page(null, __('Above The Fold', 'abovethefold'), __('Above The Fold Optimization', 'abovethefold'), 'manage_options', 'abovethefold', array(
				&$this,
				'settings_page'
			));

		} else {

			/**
			 * Add settings link to Settings tab
			 */
			add_options_page( __('Above The Fold', 'abovethefold'), __('Above The Fold Optimization', 'abovethefold'), 'manage_options', 'abovethefold', array(
				&$this,
				'settings_page'
			));

		}
	}

	public function register_settings() {

		// Register settings (data-storage)
		register_setting('abovethefold_group', 'abovethefold'); // Above the fold options

	}

    /**
	 * Update settings
	 */
	public function update_settings() {
		check_admin_referer('abovethefold');

		$options = get_option('abovethefold');
		if (!is_array($options)) {
			$options = array();
		}

		$input = $_POST['abovethefold'];
		if (!is_array($input)) {
			$input = array();
		}

		$options['cssdelivery'] = (isset($input['cssdelivery']) && intval($input['cssdelivery']) === 1) ? true : false;
		$options['cssdelivery_ignore'] = trim(sanitize_text_field($input['cssdelivery_ignore']));
		$options['debug'] = (isset($input['debug']) && intval($input['debug']) === 1) ? true : false;

		$css = trim(sanitize_text_field($input['css']));

		$cssfile = $this->CTRL->cache_path() . 'inline.min.css';
		file_put_contents($cssfile,$css);

		update_option('abovethefold',$options);

		wp_redirect(admin_url('admin.php?page=abovethefold'));
		exit;
    }

    /**
	 * Generate Critical CSS
	 */
	public function generate_critical_css() {

		$options = get_site_option('abovethefold');
		if (!is_array($options)) {
			$options = array();
		}

		$input = $_POST['abovethefold'];
		if (!is_array($input)) {
			$input = array();
		}

		$options['dimensions'] = trim(sanitize_text_field($input['dimensions']));
		$options['phantomjs_path'] = trim(sanitize_text_field($input['phantomjs_path']));
		$options['cleancss_path'] = trim(sanitize_text_field($input['cleancss_path']));
		$options['remove_datauri'] = (isset($input['remove_datauri']) && intval($input['remove_datauri']) === 1) ? true : false;

		$urls = array();
		$_urls = explode("\n",$input['urls']);
		foreach ($_urls as $url) {
			if (trim($url) === '') { continue 1; }

			$url = str_replace(get_option('siteurl'),'',$url);

			if (preg_match('|^http(s)?:|Ui',$url)) {
				add_settings_error(
					'abovethefold',                     // Setting title
					'urls_texterror',            // Error ID
					'Invalid URL: ' . $url,     // Error message
					'error'                         // Type of message
				);
				$error = true;
			} else {
				if (!preg_match('|^/|Ui',$url)) {
					$url = '/' . $url;
				}
				$urls[] = $url;
			}
		}
		if (empty($urls)) {
			add_settings_error(
				'abovethefold',                     // Setting title
				'urls_texterror',            // Error ID
				'You did not enter any paths.',     // Error message
				'error'                         // Type of message
			);
			$error = true;
		} else {
			$options['urls'] = implode("\n",$urls);
		}
		$urls = implode($options['urls']);

		update_option('abovethefold',$options);

		$this->options = $options;

		if ($error) {
			return;
		}

		/**
		 * Generate Crtical CSS
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/penthouse.class.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cleancss.class.php';

		$this->generator = new Abovethefold_Generator_Penthouse( $this );
		$this->cleancss = new Abovethefold_CleanCSS( $this );

		if (isset($_REQUEST['generate_cli'])) {
			$CLI = $this->generator->generate(true);
			$this->set_notice('Use the following command to generate Critical Path CSS.<hr /><textarea style="width:100%;height:400px;">'.$CLI.'</textarea>');
		} else {

			$criticalCSS = $this->generator->generate();

			if ($criticalCSS) {

				$cssfile = $this->CTRL->cache_path() . 'inline.min.css';
				file_put_contents($cssfile,$criticalCSS);

				$this->set_notice('Critical CSS generated and stored in the inline CSS container file.');
			}
		}

		wp_redirect(admin_url('admin.php?page=abovethefold'));
		exit;
	}

	public function settings_page() {
		global $pagenow;

		$options = get_site_option('abovethefold');
		if (!is_array($options)) {
			$options = array();
		}

		$inlinecss = '';
		$cssfile = $this->CTRL->cache_path() . 'inline.min.css';
		if (file_exists($cssfile)) {
			$inlinecss = file_get_contents($cssfile);
		}

		/**
		 * Load default paths
		 */
		$default_paths = array(
			'/' // root
		);

		// Get random post
		$args = array( 'post_type' => 'post', 'numberposts' => 2, 'orderby' => 'rand' );
		query_posts($args);
		if (have_posts()) {
			while (have_posts()) {
				the_post();
				$default_paths[] = str_replace(get_option('siteurl'),'',get_permalink($post->ID));
			}
		}

		// Get random page
		$args = array( 'post_type' => 'page', 'numberposts' => 2, 'orderby' => 'rand' );
		query_posts($args);
		if (have_posts()) {
			while (have_posts()) {
				the_post();
				$default_paths[] = str_replace(get_option('siteurl'),'',get_permalink($post->ID));
			}
		}

		// Random category
		$taxonomy = 'category';
        $terms = get_terms($taxonomy);
        shuffle ($terms);
        if ($terms) {
        	foreach($terms as $term) {
        		$default_paths[] = str_replace(get_option('siteurl'),'',get_category_link( $term->term_id ));
        		break;
        	}
        }

?>
<div class="wrap"></div>

<h2 class="option_title"><?php _e('Above The Fold Optimization', 'abovethefold') ?></h2>
<p>This plugin enables to pass the "<em>Eliminate render-blocking JavaScript and CSS in above-the-fold content</em>"-rule from <a href="https://developers.google.com/speed/pagespeed/insights/" target="_blank">Google PageSpeed Insights</a> to be able to obtain a 90+ score using other optimization plugins such as <a href="plugin-install.php?tab=search&s=w3+total+cache">W3 Total Cache</a>.</p>

<div class="wrap abovethefold-wrapper">
	<div class="metabox-holder">
		<div id="post-body-content" style="padding-bottom:0px;margin-bottom:0px;">
			<div class="postbox">
				<div class="inside" style="margin:0px;">
					<p>Developed by <strong><a href="https://optimalisatie.nl/#utm_source=wordpress&utm_medium=link&utm_term=optimization&utm_campaign=Above%20the%20fold" target="_blank">Optimalisatie.nl</a></strong> - Website Optimization and Internationalisation
					<br />Contribute via <a href="https://github.com/optimalisatie/wordpress-above-the-fold-optimization" target="_blank">Github</a> &dash; <a href="https://github.com/optimalisatie/wordpress-above-the-fold-optimization/issues" target="_blank">Report a bug</a> &dash; <a href="https://wordpress.org/support/view/plugin-reviews/above-the-fold-optimization?rate=5" target="_blank">Review this plugin</a></p>
				</div>
			</div>
		</div>
	</div>
</div>

<form method="post" action="<?php echo admin_url('admin-post.php?action=abovethefold_update'); ?>" class="clearfix" style="margin-top:0px;">
	<?php wp_nonce_field('abovethefold'); ?>
	<div class="wrap abovethefold-wrapper">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">
					<div class="postbox">
						<h3 class="hndle">
							<span><?php _e( 'Critical Path CSS', 'abovethefold' ); ?></span><a name="criticalcss">&nbsp;</a>
						</h3>
						<div class="inside">

							<table class="form-table">
								<tr valign="top">
									<th scope="row">Inline CSS<?php if (trim($inlinecss) !== '') { print '<div style="font-size:11px;font-weight:normal;">'.size_format(strlen($inlinecss),2).'</div>'; } ?></th>
									<td>
										<textarea style="width: 100%;height:250px;font-size:11px;" name="abovethefold[css]"><?php echo htmlentities($inlinecss); ?></textarea>
										<p class="description"><?php _e('Enter the Critical Path CSS-code to be inserted inline into the <code>&lt;head&gt;</code> of the page. You can generate Critical Path CSS online via Penthouse.js <a href="http://jonassebastianohlsson.com/criticalpathcssgenerator/" target="_blank">here</a>.', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Optimize CSS-delivery</th>
									<td>
										<label><input type="checkbox" name="abovethefold[cssdelivery]" value="1"<?php if (!isset($options['cssdelivery']) || intval($options['cssdelivery']) === 1) { print ' checked'; } ?> onchange="if (jQuery(this).is(':checked')) { jQuery('.cssdeliveryoptions').show(); } else { jQuery('.cssdeliveryoptions').hide(); }"> Enabled</label>
										<p class="description">When enabled, CSS files are moved to the footer, loaded asynchronously and rendered via <code>requestAnimationFrame</code> API following the <a href="https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery" target="_blank">recommendations by Google</a>.</p>
									</td>
								</tr>
								<tr valign="top" class="cssdeliveryoptions" style="<?php if (isset($options['cssdelivery']) && intval($options['cssdelivery']) !== 1) { print 'display:none;'; } ?>">
									<th scope="row">CSS-delivery Ignore List</th>
									<td>
										<textarea style="width: 100%;height:50px;font-size:11px;" name="abovethefold[cssdelivery_ignore]"><?php echo htmlentities($options['cssdelivery_ignore']); ?></textarea>
										<p class="description">Enter CSS-files to ignore in CSS delivery optimization.</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Debug-modus</th>
									<td>
                                        <label><input type="checkbox" name="abovethefold[debug]" value="1"<?php if (!isset($options['debug']) || intval($options['debug']) === 1) { print ' checked'; } ?>> Enabled</label>
                                        <p class="description">Show debug info in browser console for logged in admin-users.</p>
									</td>
								</tr>
							</table>
							<hr />
							<?php
								submit_button( __( 'Save' ), 'primary large', 'is_submit', false );
							?>
						</div>
					</div>

					<!-- End of #post_form -->

				</div>
			</div> <!-- End of #post-body -->
		</div> <!-- End of #poststuff -->
	</div> <!-- End of .wrap .nginx-wrapper -->
</form>

<form method="post" action="<?php echo admin_url('admin-post.php?action=abovethefold_generate'); ?>" class="clearfix">
	<?php wp_nonce_field('abovethefold'); ?>
	<div class="wrap abovethefold-wrapper">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">
					<div class="postbox">
						<h3 class="hndle">
							<span><?php _e( 'Critical Path CSS generation', 'abovethefold' ); ?></span>
						</h3>
						<div class="inside">

							<p>The integrated Critical Path CSS generator is based on <a href="https://github.com/pocketjoso/penthouse" target="_blank">Penthouse.js</a> by <a href="https://github.com/pocketjoso" target="_blank">Jonas Ohlsson</a>. Other generators are <a href="https://github.com/addyosmani/critical" target="_blank">Critical</a> and <a href="https://github.com/filamentgroup/criticalcss" target="_blank">Critical CSS</a> which are available as Node.js and Grunt.js modules.<p>

							<strong>How it works</strong>
							<br />The functionality of the Critical Path CSS generator is described <a href="https://github.com/pocketjoso/penthouse" target="_blank">here</a>.
							The plugin will execute Penthouse.js to generate Critical Path CSS for multiple responsive dimensions and pages, combines the resulting CSS-code and then compresses the CSS-code via Clean-CSS to achieve the smallest CSS-code to insert inline into the <code>&lt;head&gt;</code> of the page.
							When <em>Optimize CSS Delivery</em> is enabled all other CSS links are loaded asynchronously following the <a href="https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery" target="_blank">recommendations by Google</a>.
							<br />

							<blockquote style="border:solid 1px #dfdfdf;background:#F8F8F8;padding:10px;padding-bottom:0px;">
								<strong style="font-style:normal;">Automated CSS generation</strong>
								<br />
								Automated generation requires <a href="https://github.com/ariya/phantomjs" target="_blank">PhantomJS</a> to be installed on the server and executable by PHP. <strong><font color="red">This can be a security risk.</font></strong>
								<br />It also requires <a href="https://github.com/jakubpawlowicz/clean-css" target="_blank">Clean-CSS</a> to be installed on the server and executeable by PHP.
								<p>As an alternative to automated generation you can select the option <a href="javascript:void(0);" class="button button-small">Generate CLI command</a> which will result in a command-line string that can be executed via SSH. The resulting CSS code can then be copied into the <a href="#criticalcss">Criticial CSS field</a>.</p>
							</blockquote>

							<table class="form-table">
								<tr valign="top">
									<th scope="row">Responsive CSS Dimensions</th>
									<td>
										<input type="text" name="abovethefold[dimensions]" value="<?php echo esc_attr( ((isset($options['dimensions'])) ? $options['dimensions'] : '1600x1200, 720x1280, 320x480') ); ?>" style="width:100%;" />
										<p class="description"><?php _e('Enter the (responsive) dimensions to generate Critical CSS for, e.g. <code>1600x1200, 720x1280, 320x480</code>', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										URL-paths
										<div style="margin-top:10px;">
											<a href="javascript:void(0);" class="button button-small" onclick="jQuery('#abovethefold_urls').val(jQuery('#defaultpaths').html());">Load random paths</a>
											<div id="defaultpaths" style="display:none;"><?php print implode("\n",$default_paths); ?></div>
										</div>
									</th>
									<td>
										<p style="margin-bottom:4px;">All paths must be located on the siteurl of the blog <code><?php print get_option('siteurl'); ?><strong><font color="blue">/path</font></strong></code> and execute the Above the fold plugin. The plugin will output JSON for the Critical CSS generator.</p>
										<textarea name="abovethefold[urls]" id="abovethefold_urls" style="width:100%;height:100px;" /><?php echo esc_attr( ((isset($options['urls'])) ? $options['urls'] : '') ); ?></textarea>
										<p class="description"><?php _e('Enter the paths to generate Critical Path CSS for. The resulting CSS-code for each URL is merged and compressed te create Critical Path CSS that is compatible for each page.', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">PhantomJS path</th>
									<td>
										<input type="text" name="abovethefold[phantomjs_path]" value="<?php echo esc_attr( ((isset($options['phantomjs_path'])) ? $options['phantomjs_path'] : '/usr/local/bin/phantomjs') ); ?>" style="width:100%;" />
										<p class="description"><?php _e('Enter the path to <a href="https://github.com/ariya/phantomjs" target="_blank">PhantomJS</a> on the server. Install via the CLI-command <code>npm install -g phantomjs</code>.', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Clean-CSS path</th>
									<td>
										<input type="text" name="abovethefold[cleancss_path]" value="<?php echo esc_attr( ((isset($options['cleancss_path'])) ? $options['cleancss_path'] : '/usr/local/bin/cleancss') ); ?>" style="width:100%;" />
										<p class="description"><?php _e('Enter the path to <a href="https://github.com/jakubpawlowicz/clean-css" target="_blank">Clean-CSS</a> on the server. Install via the CLI-command <code>npm install -g clean-css</code>.', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Remove data-uri</th>
									<td>
                                        <label><input type="checkbox" name="abovethefold[remove_datauri]" value="1"<?php if (!isset($options['remove_datauri']) || intval($options['remove_datauri']) === 1) { print ' checked'; } ?>> Enabled</label>
                                        <p class="description"><?php _e('Strip data-uri from inline-CSS.', 'abovethefold'); ?></p>
									</td>
								</tr>
							</table>
							<hr />
							<?php
								submit_button( __( 'Generate Critical CSS', 'abovethefold' ), 'primary large', 'generate_css', false );
							?>
							&nbsp;
							<?php
								submit_button( __( 'Generate CLI Command', 'abovethefold' ), 'large', 'generate_cli', false );
							?>
						</div>
					</div>

	<!-- End of #post_form -->

				</div>
			</div> <!-- End of #post-body -->
		</div> <!-- End of #poststuff -->
	</div> <!-- End of .wrap .nginx-wrapper -->
</form>
<?php
		if (isset($_REQUEST['hideadd']) && intval($_REQUEST['hideadd']) === 1) {
			update_option('abovethefold_hidead', 1);
		}
/*
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
		}*/
	}

	/**
	 * Show admin notices
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function show_notices() {

		settings_errors( 'abovethefold' );

		$notices = get_option( 'abovethefold_notices', '' );
		$persisted_notices = array();
		if ( ! empty( $notices ) ) {

			$noticerows = array();
			foreach ($notices as $notice) {
				switch(strtoupper($notice['type'])) {
					case "ERROR":
						$noticerows[] = '<div class="error">
							<p>
								<strong>Above The Fold:</strong> '.__($notice['text'], 'abovethefold').'
							</p>
							<p style="font-size:10px;">
								'.((isset($notice['date'])) ? '<span style="color:#999;">'.date_i18n( 'j F Y, H:i', $notice['date'] ).'</span> - ' : '').'
							</p>
						</div>';

						/**
						 * Error notices remain visible for 1 minute
						 */
						if (isset($notice['date']) && $notice['date'] > (time() - 60)) {
							$persisted_notices[] = $notice;
						}

					break;
					default:
						$noticerows[] = '<div class="updated"><p>
							<strong>Above The Fold:</strong> '.__($notice['text'], 'abovethefold').'
						</p></div>';
					break;
				}
			}
			?>
			<div>
				<?php print implode('',$noticerows); ?>
			</div>
			<?php

			update_option( 'abovethefold_notices', $persisted_notices );
		}

	}

	/**
	 * Set notice
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function set_notice($notice,$type = 'NOTICE') {

		$notices = get_option( 'abovethefold_notices', '' );
		if (!is_array($notices)) {
			$notices = array();
		}
		if ( empty( $notice ) ) {
			delete_option( 'abovethefold_notices' );
		} else {
			array_unshift($notices,array(
				'text' => $notice,
				'type' => $type
			));
			update_option( 'abovethefold_notices', $notices );
		}

	}

}