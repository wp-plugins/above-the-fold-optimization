<?php

/**
 * Base Localize Javascript Module:
 *
 * @since      2.3
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */


class Abovethefold_LocalizeJSModule {

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
	 * Cache path
	 *
	 * @since    2.3
	 * @access   public
	 * @var      string
	 */
	public $cachepath;

	/**
	 * Source url variables
	 *
	 * @since    2.3
	 * @access   public
	 * @var      array
	 */
	public $source_variables = array();

	/**
	 * Download retry interval
	 */
	public $retry_interval = 30;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0
	 * @var      object    $CTRL       The above the fold admin controller..
	 */
	public function __construct( &$CTRL ) {

		$this->classname = str_replace('Abovethefold_LocalizeJSModule_','',get_called_class());
		if (!$this->name) {
			$this->name = $this->classname;
		}

		if (!isset($CTRL->options['localizejs'])) {
			$CTRL->options['localizejs'] = array();
		}

		$this->CTRL =& $CTRL;
		$this->options = $CTRL->options['localizejs'][$this->classname];

		$this->cachepath = $this->CTRL->cache_path() . 'localizejs/';
		if (!is_dir($this->cachepath)) {
			mkdir($this->cachepath,0775);
		}
        
	}

	/**
	 * Get script filename
	 */
	public function get_script_filename( ) {

		// hash from module file
		$hash = md5(__FILE__);
		$script_file = $this->cachepath . $hash . '-'.$this->classname.'.js';

		return $script_file;

	}

	/**
	 * Get script
	 */
	public function get_script( $return_url = false ) {

		// hash from module file
		$hash = md5(__FILE__);

		$script_file = $this->get_script_filename( );
		$script_time = $this->update_script( $script_file );

		if ($return_url) {
			$script_file = str_replace(ABSPATH, rtrim(get_option('siteurl'),'/') . '/', $script_file);
		}

		return array($script_file,$script_time);

	}

	/**
	 * Localize javascript file
	 *
	 * @since    2.3
	 * @var      object    $CTRL       The above the fold admin controller..
	 */
	public function localize( $url ) {

	}

	/**
	 * Admin configuration options
	 *
	 * @example
	 *   parent::admin_config('extra config HTML');
	 *
	 * Stored in $localizejs[ModuleName][optionkey]
	 *
	 * ModuleName is based on the filename with uppercase first letters and split by dash (-)
	 */
	public function admin_config( $extra_config = '' ) {

?>
<h3 class="hndle"><label><input type="checkbox" name="<?php print $this->option_name('enabled'); ?>" value="1"<?php if (isset($this->options['enabled']) && intval($this->options['enabled']) === 1) { print ' checked'; } ?> onchange="if (jQuery(this).is(':checked')) { jQuery('.localizejs-opt-<?=$this->classname;?>').show(); } else { jQuery('.localizejs-opt-<?=$this->classname;?>').hide(); }">&nbsp;<span><?php print $this->name; ?></span></label>
<?php if ($this->link) { print '&nbsp; <a href="'.htmlentities($this->link).'" target="_blank"><img src="' . plugins_url('above-the-fold-optimization/admin/link16.png') . '" align="absmiddle" border="0" /></a>'; } ?></h3>
<?php
		if ($extra_config) {
			print '<div class="inside localizejs-opt-'.$this->classname.'" style="'.((isset($this->options['enabled']) && intval($this->options['enabled']) === 1) ? '' : 'display:none;').'">' . $extra_config . '</div>';
		}

	}

	/**
	 * Parse HTML
	 */
	public function parse_html( $html ) {
		return $html;
	}

	/**
	 * Parse Javascript
	 */
	public function parse_js( $js ) {
		return $js;
	}

	/**
	 * Select options
	 */
	public function select_options( $options, $selected = false ) {

		$optstr = '';
		foreach ($options as $key => $value) {
			$optstr .= '<option value="'.htmlentities($key,ENT_COMPAT,'utf-8').'"'. ( ($selected === $key) ? ' selected="true"' : '' ) .'>'.htmlentities($value,ENT_COMPAT,'utf-8').'</option>';
		}
		return $optstr;

	}

	/**
	 * Option name
	 */
	public function option_name( $key ) {

		if (is_array($key)) {
			$key = '[' . implode('][',$key) .']';
		} else {
			$key = '[' . $key .']';
		}

		return 'abovethefold[localizejs]['.$this->classname.']'.$key;

	}

	/**
	 * Download script
	 */
	public function download_script( $script_source, $script_file ) {
		return $this->CTRL->localizejs->download_script( $script_source, $script_file );
	}

	/**
	 * Script update check
	 */
	 public function update_script( $script_file ) {

	 	$s = array(); $r = array();
	 	foreach ($this->source_variables as $_s => $_r) {
	 		$s[] = $_s;
	 		$r[] = $_r;
	 	}

	 	$script_source = str_replace($s,$r,$this->script_source);

	 	$interval = ($this->update_interval) ? $this->update_interval : 86400;

	 	$update = false;

	 	if (file_exists($script_file)) {
			$script_time = filemtime($script_file.'.check');
			if ($script_time < (time() - $this->update_interval)) {
				$update = true;
			} else {

				// Retry in 30 seconds
				$status = file_get_contents($script_file.'.check');
				if (is_numeric($status)) {
					if ($status < (time() - $this->retry_interval)) {
						$update = true;
					}
				}
			}
		} else {

			if (file_exists($script_file.'.check')) {

				// Retry in 30 seconds
				$status = file_get_contents($script_file.'.check');
				if (is_numeric($status)) {
					if ($status < (time() - $this->retry_interval)) {
						$update = true;
					}
				}
			} else {
				$update = true;
			}
		}

		if ($update) {

			$script_time = time();

			// Prevent double query
			file_put_contents($script_file.'.check', time());

			$source = $this->download_script( $script_source, $script_file );

			if (!$source && (
				!file_exists($script_file)
				|| filesize(file_exists($script_file)) === 0
			)) {
				if ($return_url) {
					$script_file = $this->script_source;
				}
			} else {
				if (!file_exists($script_file) || md5($source) !== md5_file($script_file)) {
					file_put_contents($script_file, $source);
				}

				file_put_contents($script_file.'.check', 'completed');
			}
		}

		return $script_time;

	 }

}