<?php
/**
 * @package BCC
 */
/*
Plugin Name: Buffalo Covenant Customizations
Plugin URI: https://github.com/joebuhlig/buffalo-covenant-customizations
Version: 0.1.6
Author: Joe Buhlig
Author URI: http://joebuhlig.com
GitHub Plugin URI: https://github.com/joebuhlig/buffalo-covenant-customizations
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: buffalo-covenant-theme
*/


add_action( 'init', 'wpse26388_rewrites_init' );
function wpse26388_rewrites_init(){
    add_rewrite_rule(
        '^sermons/([a-zA-Z0-9\-]+)/?',
        'index.php?pagename=sermons&audio_url=$matches[1]',
        'top' );
}

add_filter( 'query_vars', 'wpse26388_query_vars' );
function wpse26388_query_vars( $query_vars ){
    $query_vars[] = 'audio_url';
    $query_vars[] = 'autoplay';
    return $query_vars;
}

add_filter( 'page_template', 'wpa3396_page_template' );
function wpa3396_page_template( $page_template )
{
    if ( is_page( 'sermons' ) ) {
        $page_template = dirname( __FILE__ ) . '/page-sermons.php';
    }
    return $page_template;
}


function sidebar_shortcode($atts, $content="null"){
	extract(shortcode_atts(array('name' => ''), $atts));
	
	ob_start();
    dynamic_sidebar($name);
    $sidebar= ob_get_contents();
    ob_end_clean();
	
	return $sidebar;
	
	
}
add_shortcode('get_sidebar', 'sidebar_shortcode');

function get_sermon($audio_url){
	$location = 'http://buffalocov.libsyn.com/rss';
	$xml = simplexml_load_file($location);
	$items = $xml->xpath('channel/item');
	$sermon = [];
	if ($audio_url == "latest"){
		$item = $items[0];
		$link = $item->link;
		$sermon["title"] = $item->title;
		$sermon["description"] = $item->description;
		$url = explode('?', $item->enclosure["url"]);
		$url = reset($url);
		$sermon["link"] = $url;
	}
	else {
		foreach($items as $item) {
			$link = $item->link;
			if ($link == "http://buffalocov.libsyn.com/" . $audio_url) {
				$sermon["title"] = $item->title;
				$sermon["description"] = $item->description;
				$url = explode('?', $item->enclosure["url"]);
				$url = reset($url);
				$sermon["link"] = $url;
			}
		}
	};
	return $sermon;
}

function all_sermons(){
	$location = 'http://buffalocov.libsyn.com/rss';
	$xml = simplexml_load_file($location);
	$items = $xml->xpath('channel/item');
	$content = "";
	foreach($items as $item) {
		$content .= '<div class="sermon">';
		$content .= '<a href="' . trim(parse_url($item->link, PHP_URL_PATH), '/') . '">' . $item->title . '</a>';
		$content .= '<div class="sermon-meta">';
		$content .= '<span class="sermon-meta-date">' . date("F j, Y" ,strtotime($item->pubDate)) . '</span>';
		$itunes = $item->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
		$content .= ' • <span class="sermon-meta-date">' . $itunes->duration . '</span>';
		$content .= '</div>';
		$content .= '</div>';
	}
	return $content;
}


add_action( 'widgets_init', function(){
     register_widget( 'Featured_Event_Widget' );
});	

class Featured_Event_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Featured_Event_Widget', // Base ID
			__('Featured Event Widget', 'text_domain'), // Name
			array('description' => __( 'Adds an event to the homepage.', 'text_domain' ),) // Args
		);
	}
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
				
		if ( array_key_exists('before_widget', $args) ) echo $args['before_widget'];
		
			echo '<a href="' . $instance[ 'featured_event_link' ] . '"><div class="featured-event-image"><img src="' . urldecode($instance[ 'featured_event_image' ]) . '"></div>';
			echo '<div class="featured-event-title">' . $instance[ 'featured_event_title' ] . '</div></a>';
			echo '<div class="featured-event-text">' . $instance[ 'featured_event_text' ] . '</div>';
			
		if ( array_key_exists('after_widget', $args) ) echo $args['after_widget'];
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if ( isset( $instance[ 'featured_event_image' ] ) ) {
			$featured_event_image = $instance[ 'featured_event_image' ];
		}
		else {
			$featured_event_image = "";
		}

		if ( isset( $instance[ 'featured_event_title' ] ) ) {
			$featured_event_title = $instance[ 'featured_event_title' ];
		}
		else {
			$featured_event_title = "";
		}

		if ( isset( $instance[ 'featured_event_text' ] ) ) {
			$featured_event_text = $instance[ 'featured_event_text' ];
		}
		else {
			$featured_event_text = "";
		}
		if ( isset( $instance[ 'featured_event_link' ] ) ) {
			$featured_event_link = $instance[ 'featured_event_link' ];
		}
		else {
			$featured_event_link = "";
		}
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'featured_event_image' ); ?>"><?php _e( 'Image URL:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'featured_event_image' ); ?>" type="text" name="<?php echo $this->get_field_name( 'featured_event_image' ); ?>" value="<?php echo $instance[ 'featured_event_image' ] ?>"><br>

			<label for="<?php echo $this->get_field_id( 'featured_event_title' ); ?>"><?php _e( 'Title:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'featured_event_title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'featured_event_title' ); ?>" value="<?php echo $instance[ 'featured_event_title' ] ?>"><br>

			<label for="<?php echo $this->get_field_id( 'featured_event_text' ); ?>"><?php _e( 'Text:' ); ?></label> <br>
			<textarea class="widefat" rows="4" cols="20" id="<?php echo $this->get_field_id('featured_event_text'); ?>" name="<?php echo $this->get_field_name('featured_event_text'); ?>"><?php echo esc_textarea( $instance['featured_event_text'] ); ?></textarea>

			<label for="<?php echo $this->get_field_id( 'featured_event_link' ); ?>"><?php _e( 'Link:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'featured_event_link' ); ?>" type="text" name="<?php echo $this->get_field_name( 'featured_event_link' ); ?>" value="<?php echo $instance[ 'featured_event_link' ] ?>"><br>

		</p>
		<?php 
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		$instance['featured_event_image'] = ( ! empty( $new_instance['featured_event_image'] ) ) ? strip_tags( $new_instance['featured_event_image'] ) : '';
		$instance['featured_event_title'] = ( ! empty( $new_instance['featured_event_title'] ) ) ? strip_tags( $new_instance['featured_event_title'] ) : '';
		$instance['featured_event_text'] = $new_instance['featured_event_text'];
		$instance['featured_event_link'] = ( ! empty( $new_instance['featured_event_link'] ) ) ? strip_tags( $new_instance['featured_event_link'] ) : '';
		return $instance;
	}
} // class My_Widget
?>