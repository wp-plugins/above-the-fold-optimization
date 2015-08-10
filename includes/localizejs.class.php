<?php

/**
 * Localize Javascript
 *
 * @since      2.3
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */


class Abovethefold_LocalizeJS {

	/**
	 * Above the fold controller.
	 *
	 * @since    2.0
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
	 * Curl
	 *
	 * @since    2.0
	 * @access   public
	 * @var      bool
	 */
	public $curl = false;

	/**
	 * Modules
	 *
	 * @since    2.3
	 * @access   public
	 * @var      array
	 */
	public $modules;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0
	 * @var      object    $CTRL       The above the fold admin controller..
	 */
	public function __construct( &$CTRL ) {

		$this->CTRL =& $CTRL;
		$this->options =& $CTRL->options;

		/**
		 * Curl
		 */
		if (function_exists('curl_version')) {
			require_once(plugin_dir_path( realpath(dirname( __FILE__ ) . '/') ) . 'includes/curl.class.php');
			$this->curl = new Abovethefold_Curl( $this );
		} else if (!ini_get('allow_url_fopen')) {
			$this->CTRL->set_notice('PHP lib Curl should be installed or <em>allow_url_fopen</em> should be enabled.','error');
			$this->curl = false;
			return;
		} else {
			$this->curl = 'file_get_contents';
		}

		$this->load_modules( );

	}

	/**
	 * Get modules
	 *
	 * @todo add custom module support from theme-directory
	 *
	 * @since    2.3
	 */
	public function get_modules( $active = false ) {

		$dirs = array(
			plugin_dir_path( realpath(dirname( __FILE__ ) . '/') ) . 'modules/localizejs/',
			get_template_directory() . '/abovethefold/localizejs/'
		);

		$modules = array();

		foreach ($dirs as $dir) {

			if (!is_dir($dir)) {
				continue 1;
			}

			$files = scandir($dir);
			foreach ($files as $file) {

				if (is_file($dir . $file)
					&& substr($file,-7) === 'inc.php'
				) {

					$hash = md5($file);

					// Verify data
					$data = file_get_contents($dir . $file);
					$classname = str_replace(array('.inc.php'),array(''),$file);

					$parts = explode('-',$classname);
					$classname = '';
					foreach ($parts as $part) {
						$classname .= ucfirst($part);
					}
					if ($active && !$this->options['localizejs'][$classname]['enabled']) {
						continue 1;
					}

					if (strpos($data,'Abovethefold_LocalizeJSModule_'.$classname) !== false) {

						$modules[$hash] = $dir . $file;

					}

				}
			}
		}

		$modules = array_values($modules);
		sort($modules);

		return array_values($modules);

	}

	/**
	 * Load modules
	 *
	 * @since    2.3
	 */
	public function load_modules( ) {

		$modules = $this->get_modules( true );

		foreach ($modules as $module_file) {
			$this->load_module( $module_file );
		}

	}

	/**
	 * Load module
	 *
	 * @since    2.3
	 */
	public function &load_module( $module_file ) {

		if ( !file_exists($module_file) ) {
			return false;
		}

		$file = basename($module_file);

		// Verify data
		$data = file_get_contents($module_file);
		$classname = str_replace(array('.inc.php'),array(''),$file);
		$parts = explode('-',$classname);
		$classname = '';
		foreach ($parts as $part) {
			$classname .= ucfirst($part);
		}

		if (strpos($data,'Abovethefold_LocalizeJSModule_'.$classname) === false) {
			return false;
		}

		if (isset($this->modules[$classname])) {
			return $this->modules[$classname];
		}

		require_once($module_file);

		$classnameName = 'Abovethefold_LocalizeJSModule_' . $classname;
		$this->modules[$classname] = new $classnameName( $this->CTRL );
		return $this->modules[$classname];

	}


	/**
	 * Parse HTML
	 */
	public function parse_html( $html ) {

		foreach ($this->CTRL->localizejs->modules as $module) {
			$html = $module->parse_html( $html );
		}

		return $html;

	}

	/**
	 * Parse Javascript
	 */
	public function parse_js( $js ) {

		foreach ($this->CTRL->localizejs->modules as $module) {
			$js = $module->parse_js( $js );
		}

		return $js;

	}

	/**
	 * Download javascript
	 */
	public function download_script( $url ) {

		if ($this->curl === 'file_get_contents') {
			$data = file_get_contents($url);
		} else {
			$data = $this->curl->get($url);
		}

		return $data;

	}

}
