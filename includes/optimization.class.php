<?php

/**
 * Abovethefold optimization functions and hooks.
 *
 * This class provides the functionality for optimization functions and hooks.
 *
 * @since      1.0
 * @package    abovethefold
 * @subpackage abovethefold/includes
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */
class Abovethefold_Optimization {

	/**
	 * Optimization class.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      object    $plugin_name
	 */
	protected $OPTIMIZE;

	/**
	 * Buffer type
	 *
	 * @since    1.0
	 * @access   public
	 * @var      string   $buffertype W3 Total Cache buffer or regular buffer
	 */
	public $buffertype;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0
	 * @var      object    $Optimization       The Optimization class.
	 */
	public function __construct( &$OPTIMIZE ) {

		$this->OPTIMIZE =& $OPTIMIZE;

	}


	/**
	 * Init hook
	 *
	 * @since    1.0
	 */
	public function init( ) {

		if (function_exists('w3tc_add_ob_callback')) {
			w3tc_add_ob_callback('pagecache', array($this,'rewrite_callback'));
			$this->buffertype = 'w3tc';
		} else {
			$this->buffertype = 'ob';
		}

	}

	/**
	 * WordPress Header hook
	 *
	 * Parse and modify WordPress header. This part includes inline Javascript and CSS and controls the renderproces.,
	 *
	 * @since    1.0
	 */
    public function header() {
		if ($this->OPTIMIZE->noop) { return; }

		if ($this->buffertype === 'ob') {
			ob_start(array($this,'rewrite_callback'));
		}

?>
<style type="text/css">
/*!
 * Above the fold Optimization 0.1.0 (2015-02-11, 23:53)
 * By info@optimalisatie.nl / https://optimalisatie.nl/
 */
<?php print get_option('abovethefold_criticalcss'); ?></style>
<script type="text/javascript"><?php print file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . 'public/js/abovethefold.min.js'); ?> var CRITICALCSS;
<?php if (is_admin() && intval(get_option('abovethefold_debug')) === 1) { print 'window.abovethefold.debug = true;'; } ?>
</script>
<?php //
	}

	/**
	 * Buffer end
	 *
	 * @since    1.0
	 */
	public function bufferend( ) {
		if ($this->buffertype === 'ob') {
			ob_end_flush();
		}
	}

	/**
	 * Rewrite callback
	 *
	 * @since    1.0
	 * @var      string    $buffer       HTML output
	 */
	public function rewrite_callback(&$buffer) {
		if ($this->OPTIMIZE->noop) { return $buffer; }

		$optimize_delivery = (intval(get_option('abovethefold_cssdelivery')) === 1) ? 1 : 0;
		if ($optimize_delivery === 0) {
			return $buffer;
		}

		$rows = preg_split('|\n|Ui',get_option('abovethefold_cssdelivery_ignorelist'));
		$ignorelist = array();
		foreach ($rows as $row) {
			if (trim($row) === '') {
				continue 1;
			}
			$ignorelist[] = trim($row);
		}

		$search = array();
		$replace = array();

		/**
		 * Parse CSS links
		 */
		$_styles = array();
		if (preg_match_all('#(<\!--\[if[^>]+>)?([\s|\n]+)?<link([^>]+)href=[\'|"]([^\'|"]+)[\'|"]([^>]+)?>#is',$buffer,$out)) {
			foreach ($out[4] as $n => $file) {
				if (trim($out[1][$n]) != '' || strpos($out[3][$n],'stylesheet') === false) {
					continue;
				}
				if (!empty($ignorelist)) {
					$ignore = false;
					foreach ($ignorelist as $_file) {
						if (strpos($file,$_file) !== false) {
							$ignore = true;
							break 1;
						}
					}
					if ($ignore) {
						continue;
					}
				}
				$_styles[] = $file;

				$search[] = '|<link[^>]+'.preg_quote($file).'[^>]+>|Ui';
				$replace[] = '';
			}
		}

		$search[] = '|\.css\(CRITICALCSS\);|Ui';
		$replace[] = '.css('.json_encode($_styles).');';

		$buffer = preg_replace($search,$replace,$buffer);

		return $buffer;
	}


	/**
	 * WordPress Footer hook
	 *
	 * Parse and modify WordPress footer.
	 *
	 * @since    1.0
	 */
	public function footer() {
		if ($this->OPTIMIZE->noop) { return; }

		print '<script type="text/javascript">';
		print "if (window['abovethefold']) { window['abovethefold'].css(CRITICALCSS); }";
		print '</script>';

	}

}
