
<form method="post" action="<?php echo admin_url('admin-post.php?action=abovethefold_generate'); ?>" class="clearfix">
	<?php wp_nonce_field('abovethefold'); ?>
	<div class="wrap abovethefold-wrapper">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">
					<div class="postbox">
						<h3 class="hndle">
							<img src="<?php print plugins_url('above-the-fold-optimization/admin/ssh.png'); ?>" style="float:left;" />&nbsp;&nbsp;<span><?php _e( 'Server-side Critical Path CSS generator', 'abovethefold' ); ?></span>
						</h3>
						<div class="inside">

							<p>The integrated Critical Path CSS generator is based on <a href="https://github.com/pocketjoso/penthouse" target="_blank">Penthouse.js</a>. Other generators are <a href="https://github.com/addyosmani/critical" target="_blank">Critical</a> and <a href="https://github.com/filamentgroup/criticalcss" target="_blank">Critical CSS</a> which are available as Node.js and Grunt.js modules.<p>

							<strong>How it works</strong>
							<br />The functionality of Penthouse.js is described <a href="https://github.com/pocketjoso/penthouse" target="_blank">here</a>.
							The plugin will execute Penthouse.js to generate Critical Path CSS for multiple responsive dimensions and pages, combines the resulting CSS-code and then compresses the CSS-code via Clean-CSS.
							<br />

							<blockquote style="border:solid 1px #dfdfdf;background:#F8F8F8;padding:10px;padding-bottom:0px;">
								Automated generation from within WordPress requires <a href="https://github.com/ariya/phantomjs" target="_blank">PhantomJS</a> and <a href="https://github.com/jakubpawlowicz/clean-css" target="_blank">Clean-CSS</a> to be executable by PHP. <strong><font color="red">This can be a security risk.</font></strong>
								<p>As an alternative you can select the option <a href="javascript:void(0);" class="button button-small">Generate CLI command</a> which will result in a command-line string that can be executed via SSH.</p>
								<p><strong><font color="red">Be very careful when executing commands via SSH. If you do not know what you are doing, consult a professional or your hosting provider.</font></strong></p>
							</blockquote>

							<table class="form-table">
								<tr valign="top">
									<th scope="row">Responsive CSS Dimensions</th>
									<td>
										<input type="text" name="abovethefold[dimensions]" value="<?php echo esc_attr( ((isset($options['dimensions'])) ? $options['dimensions'] : '1600x1200, 720x1280, 320x480') ); ?>" style="width:100%;" />
										<p class="description"><?php _e('Enter the (responsive) dimensions to generate Critical CSS for, e.g. <code>1600x1200, 720x1280, 320x480</code>', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										URL-paths
										<div style="margin-top:10px;">
											<a href="javascript:void(0);" class="button button-small" onclick="jQuery('#abovethefold_urls').val(jQuery('#defaultpaths').html());">Load random paths</a>
											<div id="defaultpaths" style="display:none;"><?php print implode("\n",$default_paths); ?></div>
										</div>
									</th>
									<td>
										<textarea name="abovethefold[urls]" id="abovethefold_urls" style="width:100%;height:100px;" /><?php echo esc_attr( ((isset($options['urls'])) ? $options['urls'] : '') ); ?></textarea>
										<p class="description"><?php _e('Enter the paths to generate Critical Path CSS for. The resulting CSS-code for each URL is merged and compressed te create Critical Path CSS that is compatible for each page.', 'abovethefold'); ?></p>
										<p class="description">All paths must be located on the siteurl of the blog <code><?php print get_option('siteurl'); ?><strong><font color="blue">/path</font></strong></code> and execute the Above the fold plugin.</p>

									</td>
								</tr>
								<tr valign="top">
									<th scope="row">PhantomJS path</th>
									<td>
										<input type="text" name="abovethefold[phantomjs_path]" value="<?php echo esc_attr( ((isset($options['phantomjs_path'])) ? $options['phantomjs_path'] : '/usr/local/bin/phantomjs') ); ?>" style="width:100%;" />
										<p class="description"><?php _e('Enter the path to <a href="https://github.com/ariya/phantomjs" target="_blank">PhantomJS</a> on the server. Install via the CLI-command <code>npm install -g phantomjs</code>.', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Clean-CSS path</th>
									<td>
										<input type="text" name="abovethefold[cleancss_path]" value="<?php echo esc_attr( ((isset($options['cleancss_path'])) ? $options['cleancss_path'] : '/usr/local/bin/cleancss') ); ?>" style="width:100%;" />
										<p class="description"><?php _e('Enter the path to <a href="https://github.com/jakubpawlowicz/clean-css" target="_blank">Clean-CSS</a> on the server. Install via the CLI-command <code>npm install -g clean-css</code>.', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Remove data-uri</th>
									<td>
                                        <label><input type="checkbox" name="abovethefold[remove_datauri]" value="1"<?php if (!isset($options['remove_datauri']) || intval($options['remove_datauri']) === 1) { print ' checked'; } ?>> Enabled</label>
                                        <p class="description"><?php _e('Strip data-uri from inline-CSS.', 'abovethefold'); ?></p>
									</td>
								</tr>
							</table>
							<hr />
							<?php
								submit_button( __( 'Generate Critical CSS', 'abovethefold' ), 'primary large', 'generate_css', false );
							?>
							&nbsp;
							<?php
								submit_button( __( 'Generate CLI Command', 'abovethefold' ), 'large', 'generate_cli', false );
							?>
						</div>
					</div>

	<!-- End of #post_form -->

				</div>
			</div> <!-- End of #post-body -->
		</div> <!-- End of #poststuff -->
	</div> <!-- End of .wrap .nginx-wrapper -->
</form>

<div class="wrap abovethefold-wrapper">
	<div class="metabox-holder">
		<div id="post-body-content" style="padding-bottom:0px;margin-bottom:0px;">
			<div class="postbox">
				<div class="inside" style="margin:0px;">
					<p>Developed by <strong><a href="https://en.optimalisatie.nl/#utm_source=wordpress&utm_medium=link&utm_term=optimization&utm_campaign=Above%20the%20fold" target="_blank">Optimalisatie.nl</a></strong> - Website Optimization and Internationalization
					<br />Contribute via <a href="https://github.com/optimalisatie/wordpress-above-the-fold-optimization" target="_blank">Github</a> &dash; <a href="https://wordpress.org/support/plugin/above-the-fold-optimization" target="_blank">Report a bug</a> &dash; <a href="https://wordpress.org/support/view/plugin-reviews/above-the-fold-optimization?rate=5" target="_blank">Review this plugin</a></p>
				</div>
			</div>
		</div>
	</div>
</div>
