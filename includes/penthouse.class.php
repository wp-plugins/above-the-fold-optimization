<?php

/**
 * Penthouse.js Critical CSS generator
 *
 * @link https://github.com/pocketjoso/penthouse
 * @author https://github.com/pocketjoso
 * @version 0.3.2
 *
 * @since      2.0
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */


class Abovethefold_Generator_Penthouse {

	/**
	 * Above the fold controller.
	 *
	 * @since    2.0
	 * @access   public
	 * @var      object    $CTRL
	 */
	public $CTRL;

	/**
	 * Path to phantomjs
	 *
	 * @link https://github.com/Medium/phantomjs
	 *
	 * Phantomjs should be installed on the server and be executable via PHP exec()
	 *
	 * @link http://php.net/manual/en/function.exec.php
	 *
	 * @since    2.0
	 * @access   protected
	 * @var      string
	 */
	protected $phantomjs = 'phantomjs';

	/**
	 * Path to penthouse library
	 *
	 * The library is located in the plugin directory (node_modules/penthouse/)
	 *
	 * @link https://github.com/pocketjoso/penthouse
	 *
	 * @since    2.0
	 * @access   protected
	 * @var      string
	 */
	protected $penthouse;

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
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0
	 * @var      object    $CTRL       The above the fold admin controller..
	 */
	public function __construct( &$CTRL ) {

		$this->CTRL =& $CTRL;
		$this->options =& $CTRL->options;

		/**
		 * Set path to penthouse
		 */
		$this->penthouse = plugin_dir_path( realpath(dirname( __FILE__ ) . '/') ) . 'node_modules/penthouse/penthouse.js';

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

		$this->tmpdir = $this->CTRL->CTRL->cache_path() . 'tmp/';
		if (!is_dir($this->tmpdir)) {
			mkdir($this->tmpdir, 0775, true);
			chmod($this->tmpdir,0775);
		}

		if (!empty($this->options['phantomjs_path'])) {
			$this->phantomjs = $this->options['phantomjs_path'];
		}

	}

	/**
	 * Critical CSS generator
	 *
	 * @since    2.0
	 * @var      string    $code       The javascript string to minify
	 */
	public function generate( $return_cli = false ) {

		if (!$this->curl) {
			return '';
		}

		$cli = array();

		$siteurl = get_option('siteurl');

		$urlarr = explode("\n",$this->options['urls']);
		$dimensions = explode(',',$this->options['dimensions']);

		// Temp files to be deleted upon completion
		$tmpfiles = array();

		$urls = array();
		foreach ($urlarr as $url) {
			if (trim($url) === '') {
				continue 1;
			}
			$urls[] = $siteurl . trim($url);
		}

		if (empty($urls)) {
			$this->CTRL->set_notice('No URL\'s configured for Critical CSS generation.','error');
			return '';
		}
		if (empty($dimensions)) {
			$this->CTRL->set_notice('No dimensions configured for Critical CSS generation.','error');
			return '';
		}

		$GLOBALS['extracthash'] = md5(SECURE_AUTH_KEY . AUTH_KEY);

		function append_css_querystring($url) {
			if (!preg_match('|^http(s)?://|Ui',$url)) {
				return false;
			}
			$original = $url;
			if (strpos($url,'?') !== false) {
				$url .= '&';
			} else {
				$url .= '?';
			}
			$url .= http_build_query(array(
				'extract-css' => $GLOBALS['extracthash']
			));
			return array($original,$url);
		}
		$parsed_urls = array_map('append_css_querystring',$urls);

		$urls = array();
		foreach($parsed_urls as $url) {
			if (!$url) { continue 1; }
			$urls[] = $url;
		}

		if (empty($urls)) {
			$this->CTRL->set_notice('No URL\'s configured for Critical CSS generation.','error');
			return '';
		}

		$outputfiles = array();
		$criticalCSS = '';

		$errors = array();

		foreach ($urls as $url) {

			if ($this->curl === 'file_get_contents') {
				$data = file_get_contents($url[1]);
			} else {
				$data = $this->curl->get($url[1]);
			}

			/**
			 * Verify security hash (to prevent extracting data outside the plugin)
			 */
			if (!$data || !preg_match('|EXTRACT-CSS-'.preg_quote($GLOBALS['extracthash']).'|Ui',$data)) {
				$this->CTRL->set_notice('Failed to extract JSON for URL '.$url[0].'. Is the URL located and reachable on the live WordPress installation?','error');
				return;
			}
			$data = trim(preg_replace('|EXTRACT-CSS-([a-z0-9]{32})|Ui','',$data));
			$data = @json_decode($data,true);
			if (!is_array($data) || !isset($data['css']) || !isset($data['html'])) {
				$this->CTRL->set_notice('Extracted CSS for URL '.$url[0].' returned an invalid response. Please contact the administrator of the plugin.','error');
				return;
			}

			$fullcss = $this->tmpdir . md5($url[0]) . '.css';
			file_put_contents($fullcss,$data['css']);
			chmod($fullcss,0775);
			$tmpfiles[] = $fullcss;

			foreach ($dimensions as $dim) {

				$dim = explode('x',$dim);
				if (count($dim) !== 2 || !is_numeric($dim[0]) || !is_numeric($dim[1])) {
					continue 1;
				}

				$cssoutput = $this->tmpdir . md5($url[0]) . '-critical-'.intval($dim[0]).'x'.intval($dim[1]).'.css';
				if (file_exists($cssoutput)) {
					@unlink($cssoutput);
				}
				$tmpfiles[] = $cssoutput;

				$width = intval($dim[0]);
				$height = intval($dim[1]);

				$exec = $this->phantomjs;
				$exec .= ' ' . $this->penthouse;
				$_url = $url[0];
				$cmd = "{$exec} {$_url} {$fullcss} --width {$width} --height {$height} > {$cssoutput}";

				if ($return_cli) {
					$cli[] = $cmd;
				} else {
					exec($cmd, $output, $return);
					if (file_exists($cssoutput)) {
						chmod($cssoutput,0775);

						$result = file_get_contents($cssoutput);
						$rows = explode("\n",$result);
						$css = array();
						$err = false;
						foreach ($rows as $row) {
							if (trim($row) === '') { continue 1; }
							if (strpos($row,'phantomjs:') !== false) {
								$errors[] = $row;
								$err = true;
							} else if (strpos($row,'command not found') !== false) {
								$errors[] = $row;
								$err = true;
							} else if (
								strpos($row,'__ESCAPED_SOURCE_END_CLEAN_CSS__') !== false
							) {

							} else {
								$css[] = $row;
							}
						}
						if ($err) {
							$errors[] = '<div style="font-size:11px;">'.$cmd.'</div>';
						}

						$criticalCSS .= implode("\n",$css);
					} else {
						$errors[] = 'Failed to generate Critical Path CSS for URL '.$url[0].' @'.$width.'x'.$height.'<div style="font-size:11px;">'.$cmd.'</div>';
					}
				}

			}
		}

		/**
		 * Merge generated critical CSS
		 */
		$res = $this->CTRL->cleancss->minify($criticalCSS, array(), $return_cli);
		if ($return_cli) {
			$cli[] = $res;
			foreach ($tmpfiles as $f) {
				$cli[] = 'rm -f '.$f;
			}
			return implode(";\n",$cli);
		} else {
			if (!empty($res['errors']) || !empty($errors)) {
				foreach ($res['errors'] as $error) {
					$errors[] = $error;
				}
				$this->CTRL->set_notice('Failed to compress CSS after Critical CSS generation.
					Errors: <ol style="font-size:16px;"><li>'.implode('</li><li>',array_unique($errors)).'</li></ol>
					Command: <textarea style="width:100%;height:70px;">'.$res['cmd'].'</textarea>
					Output: <textarea style="width:100%;height:70px;">'.implode("\n",$res['output']).'</textarea>','error');

				$criticalCSS = '';
			} else {
				$criticalCSS = $res['css'];
			}

			// Delete tmpfiles for inline code
			array_map('unlink', $tmpfiles);

			return $criticalCSS;
		}

	}

	/**
	 * Extract Full CSS
	 *
	 * @since    2.0
	 * @var      string    $code       The javascript string to minify
	 */
	public function extract_fullcss( $return_cli = false ) {

		if (!$this->curl) {
			return '';
		}

		$cli = array();

		$siteurl = get_option('siteurl');

		$urlarr = explode("\n",$this->options['genurls']);

		// Temp files to be deleted upon completion
		$tmpfiles = array();

		$urls = array();
		foreach ($urlarr as $url) {
			if (trim($url) === '') {
				continue 1;
			}
			$urls[] = $siteurl . trim($url);
		}

		if (empty($urls)) {
			$this->CTRL->set_notice('No URL\'s configured for Critical CSS generation.','error');
			return '';
		}

		$GLOBALS['extracthash'] = md5(SECURE_AUTH_KEY . AUTH_KEY);

		function append_css_querystring($url) {
			if (!preg_match('|^http(s)?://|Ui',$url)) {
				return false;
			}
			$original = $url;
			if (strpos($url,'?') !== false) {
				$url .= '&';
			} else {
				$url .= '?';
			}
			$url .= http_build_query(array(
				'extract-css' => $GLOBALS['extracthash']
			));
			return array($original,$url);
		}
		$parsed_urls = array_map('append_css_querystring',$urls);

		$urls = array();
		foreach($parsed_urls as $url) {
			if (!$url) { continue 1; }
			$urls[] = $url;
		}

		if (empty($urls)) {
			$this->CTRL->set_notice('No URL\'s configured for Critical CSS generation.','error');
			return '';
		}

		$fullCSS = '';

		$errors = array();

		foreach ($urls as $url) {

			if ($this->curl === 'file_get_contents') {
				$data = file_get_contents($url[1]);
			} else {
				$data = $this->curl->get($url[1]);
			}

			/**
			 * Verify security hash (to prevent extracting data outside the plugin)
			 */
			if (!$data || !preg_match('|EXTRACT-CSS-'.preg_quote($GLOBALS['extracthash']).'|Ui',$data)) {
				$this->CTRL->set_notice('Failed to extract JSON for URL '.$url[0].'. Is the URL located and reachable on the live WordPress installation?','error');
				return;
			}
			$data = trim(preg_replace('|EXTRACT-CSS-([a-z0-9]{32})|Ui','',$data));
			$data = @json_decode($data,true);
			if (!is_array($data) || !isset($data['css'])) {
				$this->CTRL->set_notice('Extracted CSS for URL '.$url[0].' returned an invalid response. Please contact the administrator of the plugin.','error');
				return;
			}

			$fullCSS .= $data['css'];

		}

		return $fullCSS;

	}
}
