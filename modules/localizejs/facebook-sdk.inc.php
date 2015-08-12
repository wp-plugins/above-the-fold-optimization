<?php

/**
 * Google webfont.js (Google Font API)
 *
 * @since      2.3
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */


class Abovethefold_LocalizeJSModule_FacebookSdk extends Abovethefold_LocalizeJSModule {


	// The name of the module
	public $name = 'Facebook SDK (sdk.js)';
	public $link = 'https://developers.facebook.com/docs/javascript';

	public $update_interval = 86400; // once per day
	public $script_source = 'http://connect.facebook.net/%%LANG%%/sdk.js';

	public $lang = 'en_US';

	public $snippets = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0
	 * @var      object    $CTRL       The above the fold admin controller..
	 */
	public function __construct( &$CTRL ) {

		parent::__construct( $CTRL );

		if (isset($this->CTRL->options['localizejs'][$this->classname]['lang'])) {
			$this->lang = $this->CTRL->options['localizejs'][$this->classname]['lang'];
		}

		$this->source_variables = array(
			'%%LANG%%' => $this->lang
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

		wp_enqueue_script( 'facebook-sdk-js', $script_url , array(), date('Ymd', $script_time) );

	}


	/**
	 * Get script filename
	 */
	public function get_script_filename( ) {

		// hash from module file
		$hash = md5(__FILE__);
		$script_file = $this->cachepath . $hash . '-'.$this->classname.'-'.$this->lang.'.js';

		return $script_file;

	}

	/**
	 * Parse Google Analytics javascript and return original code (to replace) and file-URL.
	 *
	 * @since 2.3
	 */
	public function parse_facebook_sdk_js( $code ) {

		$current_version = $this->version;

		if (preg_match_all('|connect\.facebook\.net/([^/]+)/sdk\.js|Ui',$code,$out)) {

			foreach ($out[1] as $lang) {
				$options = get_option( 'abovethefold' );
				$options['localizejs'][$this->classname]['lang'] = $lang;
				$this->source_variables['%%LANG%%'] = $lang;
				update_option( 'abovethefold', $options );
			}

			$regex = array();

			$replace = '';
			if ($this->CTRL->options['localizejs'][$this->classname]['incmethod'] === 'replace') {

				$regex[] = '#([\'|"])((http(s)?)?(:)?//connect\.facebook\.net/([^/]+)/sdk\.js(\#[^\'"]+)?)[\'|"]#Ui';

				list($script_url,$script_time) = $this->get_script( true );
				$script_url = preg_replace('|^http(s)?:|Ui','',$script_url);
				$replace = '$1$3$4$5' . $script_url . '$1';
			} else {

				/**
				 * Remove async snippet
				 */
				$regex[] = '#\(function\(d,([\s]+)?s,([\s]+)?id\)\{[^\}]+connect\.facebook\.net/([^/]+)/sdk\.js[^\}]+\}\([^\)]+\)\)\;#is';

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
				'native' => 'WordPress native script include',
                'replace' => 'Replace URL in original code'
			),$this->options['incmethod']).'
		</select>';

		$config .= '&nbsp; Language-code: <input type="text" size="5" name="'.$this->option_name('lang').'" placeholder="(detect)" value="'.htmlentities($this->options['lang']).'" />';

		$config .= '</div>';

		parent::admin_config( $config );

	}

	/**
	 * Parse HTML
	 */
	public function parse_html( $html ) {

		$html = $this->parse_facebook_sdk_js( $html );

		return parent::parse_html( $html );

	}

	/**
	 * Parse Javascript
	 */
	public function parse_js( $js ) {

		$js = $this->parse_facebook_sdk_js( $js );

		return parent::parse_js( $js );

	}

}