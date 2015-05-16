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
	 * Above the fold controller
	 *
	 * @since    1.0
	 * @access   public
	 * @var      object    $CTRL
	 */
	public $CTRL;

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
	public function __construct( &$CTRL ) {

		$this->CTRL =& $CTRL;

	}

	/**
	 * Init output buffering
	 *
	 * @since    2.0
	 */
	public function start_buffering( ) {
		if (is_feed() || is_admin()) {
			return;
		}

		if ($this->CTRL->extractcss) {

			ob_start(array($this, 'end_cssextract_buffering'));

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
		} else {

			ob_start(array($this, 'end_buffering'));

		}
	}

	/**
	 * Rewrite callback
	 *
	 * @since    1.0
	 * @var      string    $buffer       HTML output
	 */
	public function end_buffering(&$buffer) {
		if ($this->CTRL->noop) { return $buffer; }

		$optimize_delivery = (isset($this->CTRL->options['cssdelivery']) && intval($this->CTRL->options['cssdelivery']) === 1) ? 1 : 0;
		if ($optimize_delivery === 0) {
			return $buffer;
		}

		$rows = preg_split('|\n|Ui',$this->CTRL->options['cssdelivery_ignore']);
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
		 $i = array();
		$_styles = array();
		if (preg_match_all('#(<\!--\[if[^>]+>)?([\s|\n]+)?<link([^>]+)href=[\'|"]([^\'|"]+)[\'|"]([^>]+)?>#is',$buffer,$out)) {
			foreach ($out[4] as $n => $file) {
				if (trim($out[1][$n]) != '' || strpos($out[3][$n] . $out[5][$n],'stylesheet') === false) {
					$i[] = array($out[3][$n] . $out[5][$n],$file);
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

				$media = false;
				if (strpos($out[0][$n],'media=') !== false) {
                    $el = (array)simplexml_load_string($out[0][$n]);
					$media = trim($el['@attributes']['media']);
				}
				if (!$media) {
					$media = 'all';
				}
				$media = explode(',',$media);

				$_styles[] = array($media,$file);

				$search[] = '|<link[^>]+'.preg_quote($file).'[^>]+>|Ui';
				$replace[] = '';
			}
		}
		//return var_export($i,true);
		$search[] = '|var CRITICALCSS;|Ui';
		$replace[] = 'var CRITICALCSS = '.json_encode($_styles).';';

		$buffer = preg_replace($search,$replace,$buffer);

		return $buffer;
	}

	/**
	 * End CSS extract output buffer
	 *
	 * @since    1.0
	 */
	public function end_cssextract_buffering($HTML) {

		if ( stripos($HTML,"<html") === false || stripos($HTML,"<xsl:stylesheet") !== false ) {
			// Not valid HTML
			return $HTML;
		}

		$siteurl = get_option('siteurl');

		/**
		 * Load HTML into DOMDocument
		 */
		$DOM = new DOMDocument();
		$DOM->preserveWhiteSpace = false;
		@$DOM->loadHTML(mb_convert_encoding($HTML, 'HTML-ENTITIES', 'UTF-8'));

		/**
		 * Query stylesheets
		 */
		$xpath = new DOMXpath($DOM);
		$stylesheets = $xpath->query('//link[not(self::script or self::noscript)]');

		$csscode = array();

		$remove = array();
		foreach ($stylesheets as $sheet) {

			$rel = $sheet->getAttribute('rel');
			if (strtolower(trim($rel)) !== 'stylesheet') {
				continue 1;
			}
			$src = $sheet->getAttribute('href');
			$media = $sheet->getAttribute('media');

			if($media) {
				$medias = explode(',',$media);
				$media = array();
				foreach($medias as $elem) {
					if (trim($elem) === '') { continue 1; }
					$media[] = $elem;
				}
			} else {
				// No media specified - applies to all
				$media = array('all');
			}

			/**
			 * Sheet file/url
			 */
			if($src) {

				$url = $src;

				// Strip query string
				$src = current(explode('?',$src,2));

				// URL decode
				if (strpos($src,'%')!==false) {
					$src = urldecode($src);
				}

				// Normalize URL
				if (strpos($url,'//')===0) {
					if (is_ssl()) {
						$url = "https:".$url;
					} else {
						$url = "http:".$url;
					}
				} else if ((strpos($url,'//')===false) && (strpos($url,parse_url($siteurl,PHP_URL_HOST))===false)) {
					$url = $siteurl.$url;
				}

				/**
				 * External URL
				 *
				 */
				if (@parse_url($url,PHP_URL_HOST)!==parse_url($siteurl,PHP_URL_HOST)) {

					if ($this->curl === 'file_get_contents') {
						$css = file_get_contents($url);
					} else {
						$css = $this->curl->get($url);
					}
					if (trim($css) === '') {
						continue 1;
					}

					$csscode[] = array($media,$css);

				} else {
					$path = (substr(ABSPATH,-1) === '/') ? substr(ABSPATH,0,-1) : ABSPATH;
					$path .= preg_replace('|^(http(s)?:)?//[^/]+/|','/',$src);

					$csscode[] = array($media,file_get_contents($path));
				}
			}

			// Remove script from DOM
			$remove[] = $sheet;
		}

		/**
		 * Query inline styles
		 */
		$inlinestyles = $xpath->query('//style[not(self::script or self::noscript)]');
		foreach ($inlinestyles as $style) {

			$media = $style->getAttribute('media');

			if($media) {
				$medias = explode(',',strtolower($media));
				$media = array();
				foreach($medias as $elem) {
					if (trim($elem) === '') { continue 1; }
					$media[] = $elem;
				}
			} else {
				// No media specified - applies to all
				$media = array('all');
			}

			$code = $style->nodeValue;

			$code = preg_replace('#.*<!\[CDATA\[(?:\s*\*/)?(.*)(?://|/\*)\s*?\]\]>.*#sm','$1',$code);
			$csscode[] = array($media,$code);

			// Remove script from DOM
			$remove[] = $style;
		}

		/**
		 * Print CSS for extraction by Critical CSS generator
		 */
		$inlineCSS = '';
		foreach ($csscode as $code) {
			if (in_array('all',$code[0]) || in_array('screen',$code[0])) {
				$inlineCSS .= $code[1];
			}
		}

		foreach($remove as $style) {
			$style->parentNode->removeChild($style);
		}

		$output = 'EXTRACT-CSS-' . md5(SECURE_AUTH_KEY . AUTH_KEY);
		$output .= "\n" . json_encode(array(
			'css' => $inlineCSS,
			'html' => $HTML
		));

		return $output;
	}

	/**
	 * WordPress Header hook
	 *
	 * Parse and modify WordPress header. This part includes inline Javascript and CSS and controls the renderproces.,
	 *
	 * @since    1.0
	 */
    public function header() {
		if ($this->CTRL->noop) { return; }

		if ($this->buffertype === 'ob') {
			ob_start(array($this,'rewrite_callback'));
		}

		$cssfile = $this->CTRL->cache_path() . 'inline.min.css';

?>
<style type="text/css">
/*!
 * Above The Fold Optimization <?php print $this->CTRL->get_version() . "\n"; ?>
 * (c) 2015 https://optimalisatie.nl
 */
<?php if (file_exists($cssfile)) { print file_get_contents($cssfile); } ?></style>
<script type="text/javascript"><?php print file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . 'public/js/abovethefold.min.js'); ?> var CRITICALCSS;
<?php if (is_admin() && intval($this->CTRL->options['debug']) === 1) { print 'window.abovethefold.debug = true;'; } ?>
</script>
<?php //
	}

	/**
	 * Buffer end
	 *
	 * @since    1.0
	 */
	/*public function bufferend( ) {
		if ($this->buffertype === 'ob') {
			ob_end_flush();
		}
	}*/

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

	/**
	 * Skip autoptimize CSS
	 */
	public function autoptimize_skip_css($excludeCSS) {
		$excludeCSS .= ',* Above The Fold Optimization,';
		return $excludeCSS;
	}

	/**
	 * Skip autoptimize Javascript
	 */
	public function autoptimize_skip_js($excludeJS) {
		$excludeJS .= ',css(CRITICALCSS),var CRITICALCSS';
		return $excludeJS;
	}

}
