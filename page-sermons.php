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
$sermon = get_sermon(get_query_var('audio_url'));
if ($sermon) {
$audio_attrs = array(
    'src'      => $sermon["link"],
    'loop'     => '',
    'autoplay' => get_query_var('autoplay', false),
    'preload' => 'none'
);
}
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<article>
				<?php if (get_query_var('audio_url')) : ?>
					<h1><?php echo $sermon["title"] ?></h1>
					<?php echo wp_audio_shortcode($audio_attrs) ?>
					<div><?php echo $sermon["description"] ?></div>
					<?php while ( have_posts() ) : the_post(); 
					the_content(); 
					endwhile;
				else : ?>
					<h1><?php echo get_the_title() ?></h1>
					<?php while ( have_posts() ) : the_post(); 
					the_content(); 
					endwhile; ?>
					<?php echo all_sermons() ?>
					<?php 
				endif; ?>
			</article>
			<div class="sidebar">
				<?php dynamic_sidebar( 'pages-sidebar' ); ?>
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
