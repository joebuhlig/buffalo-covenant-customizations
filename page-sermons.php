<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Buffalo_Covenant_Theme
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<article>
				<h1>Sermon custom page</h1>
				<div><?php echo get_query_var('audio_url') ?></div>
			</article>
			<div class="sidebar">
				<?php dynamic_sidebar( 'pages-sidebar' ); ?>
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
