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
		$this->CTRL->loader->add_action('admin_post_abovethefold_extract', $this,  'download_fullcss');

		$this->CTRL->loader->add_action( 'admin_notices', $this, 'show_notices' );

        $this->CTRL->loader->add_action( 'admin_bar_menu', $this, 'admin_bar', 100 );

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

		}

		/**
		 * Add settings link to Settings tab
		 */
		add_submenu_page( 'tools.php',  __('Above The Fold Optimization', 'abovethefold'), __('Above The Fold', 'abovethefold'), 'manage_options', 'abovethefold', array(
			&$this,
			'settings_page'
		));
	}
	
	
	/**
	 * Admin bar option.
	 *
	 * @since    1.0
	 */
	public function admin_bar($admin_bar) {

		$settings_url = add_query_arg( array( 'page' => 'abovethefold' ), '/wp-admin/admin.php' );
		$nonced_url = wp_nonce_url( $settings_url, 'abovethefold' );
		$admin_bar->add_menu( array(
			'id' => 'abovethefold',
			'title' => __( 'PageSpeed', 'abovethefold' ),
			'href' => $nonced_url,
			'meta' => array( 'title' => __( 'PageSpeed', 'abovethefold' ) )
		) );

		if (is_admin()
			|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			|| in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))
		) {
			$currenturl = site_url();
		} else {
			$currenturl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		$admin_bar->add_node( array(
			'id'     => 'abovethefold-check',
			'parent' => 'abovethefold',
			'title' => __( 'Tests', 'abovethefold' ),
			'meta'   => array( 'title' => __( 'Tests', 'abovethefold' ) )
		) );
		$admin_bar->add_node( array(
			'parent' => 'abovethefold-check',
			'id' => 'abovethefold-check-pagespeed',
			'title' => __( 'Google PageSpeed', 'abovethefold' ),
			'href' => 'https://developers.google.com/speed/pagespeed/insights/?url='.urlencode($currenturl).'',
			'meta' => array( 'title' => __( 'Google PageSpeed', 'abovethefold' ), 'target' => '_blank' )
		) );
		$admin_bar->add_node( array(
			'parent' => 'abovethefold-check',
			'id' => 'abovethefold-check-google-mobile',
			'title' => __( 'Google Mobile', 'abovethefold' ),
			'href' => 'https://www.google.com/webmasters/tools/mobile-friendly/?url='.urlencode($currenturl).'',
			'meta' => array( 'title' => __( 'Google Mobile', 'abovethefold' ), 'target' => '_blank' )
		) );
		$admin_bar->add_node( array(
			'parent' => 'abovethefold-check',
			'id' => 'abovethefold-check-pingdom',
			'title' => __( 'Pingdom Tools', 'abovethefold' ),
			'href' => 'http://tools.pingdom.com/fpt/?url='.urlencode($currenturl).'',
			'meta' => array( 'title' => __( 'Pingdom Tools', 'abovethefold' ), 'target' => '_blank' )
		) );
		$admin_bar->add_node( array(
			'parent' => 'abovethefold-check',
			'id' => 'abovethefold-check-webpagetest',
			'title' => __( 'WebPageTest.org', 'abovethefold' ),
			'href' => 'http://www.webpagetest.org/?url='.urlencode($currenturl).'',
			'meta' => array( 'title' => __( 'WebPageTest.org', 'abovethefold' ), 'target' => '_blank' )
		) );
		$admin_bar->add_node( array(
			'parent' => 'abovethefold-check',
			'id' => 'abovethefold-check-gtmetrix',
			'title' => __( 'GTMetrix', 'abovethefold' ),
			'href' => 'http://gtmetrix.com/?url='.urlencode($currenturl).'',
			'meta' => array( 'title' => __( 'GTMetrix', 'abovethefold' ), 'target' => '_blank' )
		) );
		$admin_bar->add_node( array(
			'parent' => 'abovethefold-check',
			'id' => 'abovethefold-check-ssllabs',
			'title' => __( 'SSL Labs', 'abovethefold' ),
			'href' => 'https://www.ssllabs.com/ssltest/analyze.html?d='.urlencode($currenturl).'',
			'meta' => array( 'title' => __( 'SSL Labs', 'abovethefold' ), 'target' => '_blank' )
		) );
		$admin_bar->add_node( array(
			'parent' => 'abovethefold-check',
			'id' => 'abovethefold-check-intodns',
			'title' => __( 'Into DNS', 'abovethefold' ),
			'href' => 'http://www.intodns.com/'.urlencode(parse_url($currenturl, PHP_URL_HOST)).'',
			'meta' => array( 'title' => __( 'Into DNS', 'abovethefold' ), 'target' => '_blank' )
		) );
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

		if ( get_magic_quotes_gpc() ) {
			$_POST = array_map( 'stripslashes_deep', $_POST );
			$_GET = array_map( 'stripslashes_deep', $_GET );
			$_COOKIE = array_map( 'stripslashes_deep', $_COOKIE );
			$_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
		}

		$options = get_option('abovethefold');
		if (!is_array($options)) {
			$options = array();
		}

		$input = $_POST['abovethefold'];
		if (!is_array($input)) {
			$input = array();
		}

		$options['cssdelivery'] = (isset($input['cssdelivery']) && intval($input['cssdelivery']) === 1) ? true : false;
		$options['gwfo'] = (isset($input['gwfo']) && intval($input['gwfo']) === 1) ? true : false;

		$options['cssdelivery_position'] = trim(sanitize_text_field($input['cssdelivery_position']));
		$options['cssdelivery_ignore'] = trim(sanitize_text_field($input['cssdelivery_ignore']));
		$options['cssdelivery_remove'] = trim(sanitize_text_field($input['cssdelivery_remove']));
		$options['debug'] = (isset($input['debug']) && intval($input['debug']) === 1) ? true : false;

		/**
		 * Localize Javascript Settings
		 */
		$options['localizejs'] = (is_array($input['localizejs'])) ? $input['localizejs'] : array();

		$css = trim(sanitize_text_field(stripslashes($input['css'])));

		$cssfile = $this->CTRL->cache_path() . 'inline.min.css';
		file_put_contents($cssfile,$css);

		update_option('abovethefold',$options);

		wp_redirect(admin_url('admin.php?page=abovethefold'));
		exit;
    }

    /**
	 * Download Full CSS
	 */
    public function download_fullcss() {

    	$options = get_site_option('abovethefold');
		if (!is_array($options)) {
			$options = array();
		}

		if ( get_magic_quotes_gpc() ) {
			$_POST = array_map( 'stripslashes_deep', $_POST );
			$_GET = array_map( 'stripslashes_deep', $_GET );
			$_COOKIE = array_map( 'stripslashes_deep', $_COOKIE );
			$_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
		}

		$input = $_POST['abovethefold'];
		if (!is_array($input)) {
			$input = array();
		}

		$urls = array();
		$_urls = explode("\n",$input['genurls']);
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
			$options['genurls'] = implode("\n",$urls);
		}

		update_option('abovethefold',$options);

		$this->options = $options;

		if ($error) {
			return;
		}

		/**
		 * Generate Crtical CSS
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/penthouse.class.php';

		$this->generator = new Abovethefold_Generator_Penthouse( $this );

		$fullCSS = $this->generator->extract_fullcss();

		ob_end_clean();

		header('Content-disposition: attachment; filename=full-css-'.date('c').'.css');
        header('Content-type: text/plain');
        header('Content-length: '.strlen($fullCSS).'');

        die($fullCSS);
    }

    /**
	 * Generate Critical CSS
	 */
	public function generate_critical_css() {

		$options = get_site_option('abovethefold');
		if (!is_array($options)) {
			$options = array();
		}

		if ( get_magic_quotes_gpc() ) {
			$_POST = array_map( 'stripslashes_deep', $_POST );
			$_GET = array_map( 'stripslashes_deep', $_GET );
			$_COOKIE = array_map( 'stripslashes_deep', $_COOKIE );
			$_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
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
			$this->set_notice('Use the following command to generate Critical Path CSS.<br />
			<strong><font color="red">Warning:</font></strong> Be very careful when entering commands via SSH.
			<hr /><textarea style="width:100%;height:400px;">'.$CLI.'</textarea>');
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

	public function settings_tabs( $current = 'homepage' ) {
        $tabs = array(
        	'settings' => 'Critical Path CSS Settings',
        	'online' => 'Online Generator',
			'server' => 'Server-side Generator',
			'extract' => 'Extract Full CSS'
        );
        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=abovethefold&tab=$tab'>$name</a>";

        }
        echo '</h2>';
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
		$args = array( 'post_type' => 'post', 'numberposts' => 1, 'orderby' => 'rand' );
		query_posts($args);
		if (have_posts()) {
			while (have_posts()) {
				the_post();
				$default_paths[] = str_replace(get_option('siteurl'),'',get_permalink($post->ID));
				break;
			}
		}

		// Get random page
		$args = array( 'post_type' => 'page', 'numberposts' => 1, 'orderby' => 'rand' );
		query_posts($args);
		if (have_posts()) {
			while (have_posts()) {
				the_post();
				$default_paths[] = str_replace(get_option('siteurl'),'',get_permalink($post->ID));
				break;
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
<p><strong>Note:</strong> This plugin is intended to achieve the best possible result, not easy usage. It is intended for advanced WordPress users and optimization professionals. If you need help you can post a request on the <a href="https://wordpress.org/support/plugin/above-the-fold-optimization" target="_blank">support forum</a>.</p>
<?php

		if ( !isset ( $_GET['tab'] ) ) {
			$_GET['tab'] = 'settings';
		}

		$this->settings_tabs($_GET['tab']);

		switch(strtolower(trim($_GET['tab']))) {

			case "settings":

				require_once('admin.settings.class.php');

			break;

			case "online":

				require_once('admin.online.class.php');

			break;

			case "server":

				require_once('admin.server.class.php');

			break;

			case "extract":

				require_once('admin.extract.class.php');

			break;
		}

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