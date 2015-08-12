<?php

/**
 * Google webfont.js (Google Font API)
 *
 * @since      2.3
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */


class Abovethefold_LocalizeJSModule_GoogleWebfont extends Abovethefold_LocalizeJSModule {


	// The name of the module
	public $name = 'Google Webfont API (webfont.js)';
	public $link = 'https://github.com/typekit/webfontloader';

	public $update_interval = 86400; // once per day
	public $script_source = 'http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
	public $version = '1';

	public $snippets = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0
	 * @var      object    $CTRL       The above the fold admin controller..
	 */
	public function __construct( &$CTRL ) {

		parent::__construct( $CTRL );

		if (isset($this->CTRL->options['localizejs'][$this->classname]['version'])) {
			$this->version = $this->CTRL->options['localizejs'][$this->classname]['version'];
		}

		$this->source_variables = array(
			'%%VERSION%%' => $this->version
		);

		if ($this->CTRL->options['localizejs'][$this->classname]['enabled']) {
			switch ($this->CTRL->options['localizejs'][$this->classname]['incmethod']) {
				case "replace":

				break;
				default:
					$this->CTRL->loader->add_action('wp_enqueue_scripts', $this, 'enqueue_script', -1);
				break;
			}
		}

	}

	/**
	 * Include script
	 */
	public function enqueue_script( ) {

		list($script_url, $script_time) = $this->get_script( true );

		wp_enqueue_script( 'google-webfont-js', $script_url , array(), $this->version );

	}


	/**
	 * Get script filename
	 */
	public function get_script_filename( ) {

		// hash from module file
		$hash = md5(__FILE__);
		$script_file = $this->cachepath . $hash . '-'.$this->classname.'-v'.$this->version.'.js';

		return $script_file;

	}

	/**
	 * Parse Google Analytics javascript and return original code (to replace) and file-URL.
	 *
	 * @since 2.3
	 */
	public function parse_webfont_js( $code ) {

		$current_version = $this->version;

		if (preg_match_all('|googleapis\.com/[a-z\/]+/([^/]+)/webfont\.js|Ui',$code,$out)) {

			foreach ($out[1] as $version) {
				if (version_compare ( $current_version , $version ) < 0) {
					$options = get_option( 'abovethefold' );
					$options['localizejs'][$this->classname]['version'] = $version;
					$this->source_variables['%%VERSION%%'] = $version;
					update_option( 'abovethefold', $options );
				}
			}

			$regex = array();

			$replace = '';
			if ($this->CTRL->options['localizejs'][$this->classname]['incmethod'] === 'replace') {

				$regex[] = '#([\'|"])((http(s)?)?(:)?//ajax\.googleapis\.com/[a-z\/]+/([^/]+)/webfont\.js)[\'|"]#Ui';

				list($script_url,$script_time) = $this->get_script( true );
				$script_url = preg_replace('|^http(s)?:|Ui','',$script_url);
				$replace = '$1$3$4$5' . $script_url . '$1';
			} else {

				/**
				 * Remove async snippet
				 */
				$regex[] = '#\(function\(\)[\s]+?\{[^\}]+googleapis\.com/[a-z\/]+/([^/]+)/webfont\.js[^\}]+\}\)\(\)\;#is';

			}

			foreach ($regex as $str) {
				$code = preg_replace($str,$replace,$code);
			}

		}

		return $code;

	}

	/**
	 * Admin configuration options
	 */
	public function admin_config( $extra_config = '' ) {

		$config = '<div class="inside">';

		$config .= 'Include method: <select name="'.$this->option_name('incmethod').'">
			'.$this->select_options(array(
                'replace' => 'Replace URL in original code',
				'native' => 'WordPress native script include'
			),$this->options['incmethod']).'
		</select>';

		$config .= '&nbsp; Version: <input type="text" size="5" name="'.$this->option_name('version').'" placeholder="(detect)" value="'.htmlentities($this->options['version']).'" />';

		$config .= '</div>';

		parent::admin_config( $config );

	}

	/**
	 * Parse HTML
	 */
	public function parse_html( $html ) {

		$html = $this->parse_webfont_js( $html );

		return parent::parse_html( $html );

	}

	/**
	 * Parse Javascript
	 */
	public function parse_js( $js ) {

		$js = $this->parse_webfont_js( $js );

		return parent::parse_js( $js );

	}

}