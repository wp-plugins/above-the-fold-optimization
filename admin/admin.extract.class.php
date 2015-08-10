<?php

	/**
	 * Paths
	 */
	$options = array(
		'<option value="/">/ - Root</option>'
	);

	// Get random post
	$args = array( 'post_type' => 'post', 'numberposts' => -1 );
	query_posts($args);
	if (have_posts()) {
		$options[] = '<optgroup label="'.__('Posts').'">';
		while (have_posts()) {
			the_post();
			$options[] = '<option value="'.str_replace(get_option('siteurl'),'',get_permalink($post->ID)).'">' . str_replace(get_option('siteurl'),'',get_permalink($post->ID)) . '</option>';
		}
		$options[] = '</optgroup>';
	}

	// Get random page
	$args = array( 'post_type' => 'page', 'numberposts' => -1 );
	query_posts($args);
	if (have_posts()) {
		$options[] = '<optgroup label="'.__('Pages').'">';
		while (have_posts()) {
			the_post();
			$options[] = '<option value="'.str_replace(get_option('siteurl'),'',get_permalink($post->ID)).'">' . str_replace(get_option('siteurl'),'',get_permalink($post->ID)) . '</option>';
		}
		$options[] = '</optgroup>';
	}

	// Random category
	$taxonomy = 'category';
	$terms = get_terms($taxonomy);
	shuffle ($terms);
	if ($terms) {
		$options[] = '<optgroup label="'.__('Categories').'">';
		foreach($terms as $term) {
			$options[] = '<option value="'.str_replace(get_option('siteurl'),'',get_category_link( $term->term_id )).'">' . str_replace(get_option('siteurl'),'',get_category_link( $term->term_id )) . '</option>';
		}
		$options[] = '</optgroup>';
	}

?>
<form method="post" action="<?php echo admin_url('admin-post.php?action=abovethefold_extractcss'); ?>" class="clearfix">
	<?php wp_nonce_field('abovethefold'); ?>
	<div class="wrap abovethefold-wrapper">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">
					<div class="postbox">
						<h3 class="hndle">
							<span><?php _e( 'Extract Full CSS', 'abovethefold' ); ?></span>
						</h3>
						<div class="inside">

							<p>You can extract the full CSS from any url by adding the query string <code><strong>?extract-css=<?php print md5(SECURE_AUTH_KEY . AUTH_KEY); ?>&amp;output=print</strong></code>.</p>
							<br />
							<div>
								<select id="fullcsspages"><?php print implode('',$options); ?></select>

								<button type="button" onclick="document.location.href=jQuery('#fullcsspages').val() + '?extract-css=<?php print md5(SECURE_AUTH_KEY . AUTH_KEY); ?>&output=download';" class="button button-large">Download</button>
								<button type="button" onclick="window.open(jQuery('#fullcsspages').val() + '?extract-css=<?php print md5(SECURE_AUTH_KEY . AUTH_KEY); ?>&output=print');" class="button button-large">Print</button>
							</div>
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
