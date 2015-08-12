<?php

/**
 * Hotjar (Heatmaps)
 *
 * @since      2.3
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */


class Abovethefold_LocalizeJSModule_Hotjar extends Abovethefold_LocalizeJSModule {


	// The name of the module
	public $name = 'Hotjar Heatmaps (hotjar.js)';
	public $link = 'https://www.hotjar.com/';

	public $update_interval = 86400; // once per day
	public $script_source = 'http://static.hotjar.com/c/hotjar-%%ID%%.js?sv=%%SV%%';

	public $sv = '';
	public $id = '';

	public $snippets = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0
	 * @var      object    $CTRL       The above the fold admin controller..
	 */
	public function __construct( &$CTRL ) {

		parent::__construct( $CTRL );

		if (isset($this->CTRL->options['localizejs'][$this->classname]['sv'])) {
			$this->sv = $this->CTRL->options['localizejs'][$this->classname]['sv'];
		}
		if (isset($this->CTRL->options['localizejs'][$this->classname]['id'])) {
			$this->id = $this->CTRL->options['localizejs'][$this->classname]['id'];
		}

		$this->source_variables = array(
			'%%SV%%' => $this->sv,
			'%%ID%%' => $this->id
		);

		if ($this->CTRL->options['localizejs'][$this->classname]['enabled']) {
			switch ($this->CTRL->options['localizejs'][$this->classname]['incmethod']) {
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

		if (!$this->source_variables['%%SV%%'] || !$this->source_variables['%%ID%%']) {
			return false;
		}

		list($script_url, $script_time) = $this->get_script( true );

		wp_enqueue_script( 'hotjar-js', $script_url , array(), $this->version );

	}


	/**
	 * Get script filename
	 */
	public function get_script_filename( ) {

		// hash from module file
		$hash = md5(__FILE__);
		$script_file = $this->cachepath . $hash . '-'.$this->classname.'-id'.$this->id.'-sv'.$this->sv.'.js';

		return $script_file;

	}


	/**
	 * Parse javascript code
	 *
	 * @since 2.3
	 */
	public function parse_hotjar_js( $code ) {

		$current_sv = $this->sv;

		if (strpos($code, '.hotjar.com') !== false && preg_match_all('|\{hjid:([^,]+),hjsv:([^\}]+)\}|is',$code,$out)) {

			$options = get_option( 'abovethefold' );

			foreach ($out[2] as $n => $sv) {
				if (version_compare ( $current_sv , $sv ) < 0) {
					$options['localizejs'][$this->classname]['id'] = intval($out[1][$n]);
					$options['localizejs'][$this->classname]['sv'] = $sv;
					$this->source_variables['%%ID%%'] = $out[1][$n];
					$this->source_variables['%%SV%%'] = $sv;
					update_option( 'abovethefold', $options );
				}
			}

			if (!isset($options['localizejs'][$this->classname]['id'])) {
				$options['localizejs'][$this->classname]['id'] = intval($out[1][0]);
				$options['localizejs'][$this->classname]['sv'] = $out[2][0];
				$this->source_variables['%%ID%%'] = $out[1][$n];
				$this->source_variables['%%SV%%'] = $out[2][0];
				update_option( 'abovethefold', $options );
			}

			$regex = array();

			$replace = '';

			/**
			 * Remove async snippet
			 */
			$regex[] = '#\(function\(h,o,t,j,a,r\)\{.*\}\)[^\)]+static\.hotjar\.com/c/hotjar[^\)]+\);#is';

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
				'native' => 'WordPress native script include'
			),$this->options['incmethod']).'
		</select>';

		$config .= '&nbsp; ID: <input type="text" size="5" name="'.$this->option_name('id').'" placeholder="(detect)" value="'.htmlentities($this->options['id']).'" />';
		$config .= '&nbsp; SV: <input type="text" size="5" name="'.$this->option_name('sv').'" placeholder="(detect)" value="'.htmlentities($this->options['sv']).'" />';

		$config .= '</div>';

		parent::admin_config( $config );

	}

	/**
	 * Parse HTML
	 */
	public function parse_html( $html ) {

		$html = $this->parse_hotjar_js( $html );

		return parent::parse_html( $html );

	}

	/**
	 * Parse Javascript
	 */
	public function parse_js( $js ) {

		$js = $this->parse_hotjar_js( $js );

		return parent::parse_js( $js );

	}

}