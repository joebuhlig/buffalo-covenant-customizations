<?php
/**
 * The template for displaying all single message posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Buffalo_Covenant_Theme
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<article>
					<?php the_content(); ?>
			</article>
			<div class="sidebar">
				<?php dynamic_sidebar( 'pages-sidebar' ); ?>
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();