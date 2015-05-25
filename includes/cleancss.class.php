<?php

/**
 * Clean-CSS CSS minifier
 *
 * @link https://github.com/jakubpawlowicz/clean-css
 * @version 3.20.0-pre
 *
 * @since      2.0
 * @package    abovethefold
 * @subpackage abovethefold/admin
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */


class Abovethefold_CleanCSS {

	/**
	 * Above the fold controller.
	 *
	 * @since    2.0
	 * @access   public
	 * @var      object    $CTRL
	 */
	public $CTRL;

	protected $cleancss = '/usr/bin/cleancss';
	protected static $exists_check = ' -V';
	protected static $options = array(
		'keep-line-breaks',
		's0','s1',
		'root',
		'output',
		'skip-import',
		'skip-rebase',
		'skip-advanced',
		'skip-aggressive-merging',
		'skip-media-merging',
		'skip-restructuring',
		'skip-shorthand-compacting',
		'rounding-precision',
		'compatibility',
		'source-map',
		'source-map-inline-sources'
	);
	protected static $option_place = 'after';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0
	 * @var      object    $CTRL       The CSS minification controller.
	 */
	public function __construct( &$CTRL ) {

		$this->CTRL =& $CTRL;

		$this->tmpdir = $this->CTRL->CTRL->cache_path() . 'tmp/';
		if (!is_dir($this->tmpdir)) {
			mkdir($this->tmpdir, 0775, true);
			chmod($this->tmpdir,0775);
		}

		if (!empty($CTRL->options['cleancss_path'])) {
			$this->cleancss = $CTRL->options['cleancss_path'];
		}

	}

	/**
	 * Mnify CSS
	 *
	 * @since    2.0
	 * @var      string    $code       The CSS string to minify
	 */
	public function minify( $code, $opts = array(), $return_cli = false ) {

		/**
		 * Remove data-uri
		 */
		if ($this->CTRL->options['remove_datauri']) {
			$code = preg_replace('|\(([^\)]{1})?data:[^;]+;[^\)]+\)|Ui', '', $code);
		}

		$cli = array();

		$tmpfiles = array();

        $opts['root'] = (substr(ABSPATH,-1) === '/') ? ABSPATH : ABSPATH . '/';
        $opts['s0'] = true;

		/**
		 * Create temporary files for inline code
		 */
		$tmpfile = $this->tmpdir . 'inline-' . md5($code) . '.css';
		file_put_contents($tmpfile,substr($code,7));
		$tmpfiles[] = $tmpfile;

		/**
		 * Output file
		 */
		$opts['output'] = $this->tmpdir . 'output-' . md5($code) . '.css';
		if (file_exists($opts['output'])) {
			@unlink($opts['output']);
		}
		$tmpfiles[] = $opts['output'];

		$files = array($tmpfile);

		$files = implode(' ', array_map(function ($file) {
			return escapeshellarg($file);
		}, $files));

		$options = $this->options_string($opts);
		$exec = $this->cleancss;
		if (static::$option_place === 'before') {
			$cmd = "{$exec} -d {$options} {$files} 2>&1";
		} else {
			$cmd = "{$exec} {$files} -d {$options} 2>&1";
		}

		if ($return_cli) {

			$cli[] = $cmd;

			$cssfile = $this->CTRL->CTRL->cache_path() . 'inline.min.css';
			$cli[] = 'rm -f ' . $cssfile;
			$cli[] = 'cp '.$opts['output'].' ' . $cssfile;

			foreach ($tmpfiles as $f) {
				$cli[] = 'rm -f '.$f;
			}

			return implode(";\n",$cli);

		} else {
			/**
			 * Execute Clean-CSS
			 */
			exec($cmd, $output, $return);

			/**
			 * Process errors
			 */
			$errors = array();
			foreach ($output as $row) {
				if (strpos($row,'[31mERROR') !== false) {
					$errors[] = preg_replace('|\[31mERROR[^:]+:|Ui','',$row);
				} else if (strpos($row,'No such file or directory') !== false) {
					$errors[] = $row;
				}
			}
			if (empty($errors) && !file_exists($opts['output'])) {
				$errors[] = 'Failed to compress CSS via Clean-CSS (no output).';
			}

			$res = array(
				'cmd' => $cmd,
				'output' => $output,
				'return' => $return,
				'errors' => $errors
			);
			if (empty($errors)) {
				$res['css'] = file_get_contents($opts['output']);
			}

			// Delete tmpfiles
			array_map('unlink', $tmpfiles);

			return $res;
		}

	}

	/**
	 * Generate options string for CleanCSS CLI
	 *
	 * @since    2.0
	 * @var      array    $opts       The CleanCSS options
	 */
	private function options_string($opts) {
		$options = array();
		foreach ($opts as $name => $value) {
			if (in_array($name, static::$options)) {
				if ($value === true) {
					$options[] = '--' . $name;
				} else if (is_string($value)) {
					$options[] = '--' . $name . ' ' . escapeshellarg($value);
				} else {
					$this->CTRL->set_notice('Value of "' . $name . '" in ' . get_called_class() . ' must be a string','error');
				}
			} else {
				$this->CTRL->set_notice('Unsupported option "' . $name . '" in ' . get_called_class(),'error');
			}
		}
		return implode(' ', $options);
	}

	/**
	 * Checks for an exit code of 0 on cleancss -V (version number)
	 * @return Boolean
	 */
	public function installed() {
		if (empty($this->cleancss)) {
			throw new \Exception('Clean-CSS not set.');
		}
		exec($this->cleancss . static::$exists_check, $output, $return);
		if ($return === 0) {
			return true;
		}
		return false;
	}

}
