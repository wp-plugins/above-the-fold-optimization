
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

<form method="post" action="<?php echo admin_url('admin-post.php?action=abovethefold_update'); ?>" class="clearfix" style="margin-top:0px;">
	<?php wp_nonce_field('abovethefold'); ?>
	<div class="wrap abovethefold-wrapper">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">
					<div class="postbox">
						<h3 class="hndle">
							<span><?php _e( 'Critical Path CSS Settings', 'abovethefold' ); ?></span><a name="criticalcss">&nbsp;</a>
						</h3>
						<div class="inside">

							<table class="form-table">
								<tr valign="top">
									<th scope="row">Inline CSS<?php if (trim($inlinecss) !== '') { print '<div style="font-size:11px;font-weight:normal;">'.size_format(strlen($inlinecss),2).'</div>'; } ?></th>
									<td>
										<textarea style="width: 100%;height:250px;font-size:11px;" name="abovethefold[css]"><?php echo htmlentities($inlinecss); ?></textarea>
										<p class="description"><?php _e('Enter the Critical Path CSS-code to be inserted inline into the <code>&lt;head&gt;</code> of the page.', 'abovethefold'); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Optimize CSS-delivery</th>
									<td>
										<label><input type="checkbox" name="abovethefold[cssdelivery]" value="1"<?php if (!isset($options['cssdelivery']) || intval($options['cssdelivery']) === 1) { print ' checked'; } ?> onchange="if (jQuery(this).is(':checked')) { jQuery('.cssdeliveryoptions').show(); } else { jQuery('.cssdeliveryoptions').hide(); }"> Enabled</label>
										<p class="description">When enabled, CSS files are loaded asynchronously and rendered via <code>requestAnimationFrame</code> API following the <a href="https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery" target="_blank">recommendations by Google</a>.</p>
									</td>
								</tr>
								<tr valign="top" class="cssdeliveryoptions" style="<?php if (isset($options['cssdelivery']) && intval($options['cssdelivery']) !== 1) { print 'display:none;'; } ?>">
									<td colspan="2">

										<h3 class="hndle"><span>CSS Delivery Optimization</span></h3>

										<div class="inside">
											<table class="form-table">
												<tr valign="top">
													<th scope="row">Position</th>
													<td>
														<select name="abovethefold[cssdelivery_position]">
															<option value="header"<?php if ($options['cssdelivery_position'] === 'header') { print ' selected'; } ?>>Header</option>
															<option value="footer"<?php if (empty($options['cssdelivery_position']) || $options['cssdelivery_position'] === 'footer') { print ' selected'; } ?>>Footer</option>
														</select>
														<p class="description">Select the position where the async loading of CSS will start.</p>
													</td>
												</tr>
												<tr valign="top">
													<th scope="row">Ignore List</th>
													<td>
														<textarea style="width: 100%;height:50px;font-size:11px;" name="abovethefold[cssdelivery_ignore]"><?php echo htmlentities($options['cssdelivery_ignore']); ?></textarea>
														<p class="description">CSS-files to ignore in CSS delivery optimization. The files will be left untouched in the HTML.</p>
													</td>
												</tr>
												<tr valign="top">
													<th scope="row">Remove List</th>
													<td>
														<textarea style="width: 100%;height:50px;font-size:11px;" name="abovethefold[cssdelivery_remove]"><?php echo htmlentities($options['cssdelivery_remove']); ?></textarea>
														<p class="description">CSS-files to remove. This feature enables to include small plugin-CSS files inline.</p>
													</td>
												</tr>
											</table>
										</div>
									</td>
								</tr>

<?php

	$autoptimize_active = is_plugin_active('autoptimize/autoptimize.php');
	$gwfo_active = is_plugin_active('google-webfont-optimizer/google-webfont-optimizer.php');
?>

								<tr valign="top">
									<th scope="row">Optimize Google Fonts</th>
									<td>
										<label><input type="checkbox" name="abovethefold[gwfo]" value="1"<?php if (!isset($options['gwfo']) || intval($options['gwfo']) === 1) { print ' checked'; } ?> onchange="if (jQuery(this).is(':checked')) { jQuery('.gwfooptions').show(); } else { jQuery('.gwfooptions').hide(); }"> Enabled
											<span class="gwfooptions" style="<?php if (isset($options['gwfo']) && intval($options['gwfo']) !== 1) { print 'display:none;'; } ?>">
											<?php
												if ($autoptimize_active && $gwfo_active) {

													if (!get_option('autoptimize_css')) {
														?>
															<span style="color:red;font-weight:bold;">ERROR - Autoptimize CSS optimization is disabled. <a href="./options-general.php?page=autoptimize">Enable it</a> to use this feature.</span>
														<?php
													} else {
														?>
															<span style="color:green;font-weight:bold;">OK - Autoptimize and Google Webfont Optimizer are installed and active.</span>
														<?php
													}
												} else if (!$autoptimize_active) {
													?>
														<span style="color:red;font-weight:bold;">ERROR - Autoptimize not installed or not activated.</span>
													<?php
												} else if (!$gwfo_active) {
													?>
														<span style="color:red;font-weight:bold;">ERROR - Google Webfont Optimizer not installed or not activated.</span>
													<?php
												}
											?>
											</span>
										</label>
										<p class="description">When enabled, Google fonts found in <code>@import</code> within the CSS-code output of the plugin <a href="https://wordpress.org/plugins/autoptimize/" target="_blank">Autoptimize</a> are included in the optimized delivery by the plugin <a href="https://wordpress.org/plugins/google-webfont-optimizer/">Google Webfont Optimizer</a>. Both plugins need to be installed and active to use this feature.</p>
									</td>
								</tr>



<?php
	$modules = $this->CTRL->localizejs->get_modules( );
?>

								<tr valign="top">
									<th scope="row">Localize Javascript</th>
									<td>
										<label><input type="checkbox" name="abovethefold[localizejs][enabled]" value="1"<?php if (!isset($options['localizejs']['enabled']) || intval($options['localizejs']['enabled']) === 1) { print ' checked'; } ?> onchange="if (jQuery(this).is(':checked')) { jQuery('.localizejsoptions').show(); } else { jQuery('.localizejsoptions').hide(); }"> Enabled</label>
										<p class="description">When enabled, recognized external javascript files are stored locally to pass the <code>Leverage browser caching</code>-rule from Google PageSpeed. Custom modules can be added in the theme-directory, /THEME<strong>/abovethefold/localizejs/modulename.inc.php</strong>. Please submit new modules to <a href="mailto:info@optimalisatie.nl?subject=Submission: Above The Fold Javascript Localization Module">info@optimalisatie.nl</a>.</p>

										<div class="localizejsoptions" style="<?php if (isset($options['localizejs']['enabled']) && intval($options['localizejs']['enabled']) !== 1) { print 'display:none;'; } ?>">

<?php
	foreach ($modules as $module_file) {

		$module = $this->CTRL->localizejs->load_module($module_file);
		$module->admin_config();

	}
?>

										</div>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">Debug-modus</th>
									<td>
                                        <label><input type="checkbox" name="abovethefold[debug]" value="1"<?php if (!isset($options['debug']) || intval($options['debug']) === 1) { print ' checked'; } ?>> Enabled</label>
                                        <p class="description">Show debug info in the browser console for logged in admin-users.</p>
									</td>
								</tr>
							</table>
							<hr />
							<?php
								submit_button( __( 'Save' ), 'primary large', 'is_submit', false );
							?>
						</div>
					</div>

					<!-- End of #post_form -->

				</div>
			</div> <!-- End of #post-body -->
		</div> <!-- End of #poststuff -->
	</div> <!-- End of .wrap .nginx-wrapper -->
</form>