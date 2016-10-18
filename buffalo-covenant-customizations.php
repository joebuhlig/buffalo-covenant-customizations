<?php
/**
 * @package BCC
 */
/*
Plugin Name: Buffalo Covenant Customizations
Plugin URI: https://github.com/joebuhlig/buffalo-covenant-customizations
Version: 0.1.9
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

function create_series_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Series', 'taxonomy general name' ),
		'singular_name'     => _x( 'Series', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Series' ),
		'all_items'         => __( 'All Series' ),
		'parent_item'       => __( 'Parent Series' ),
		'parent_item_colon' => __( 'Parent Series:' ),
		'edit_item'         => __( 'Edit Series' ),
		'update_item'       => __( 'Update Series' ),
		'add_new_item'      => __( 'Add New Series' ),
		'new_item_name'     => __( 'New Series Name' ),
		'menu_name'         => __( 'Series' ),
	);

	$args = array(
		'hierarchical'      => true,
		'public'	    => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'series' )
	);

	register_taxonomy( 'series', array( 'message' ), $args );
}

function create_speakers_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Speakers', 'taxonomy general name' ),
		'singular_name'     => _x( 'Speaker', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Speakers' ),
		'all_items'         => __( 'All Speakers' ),
		'parent_item'       => __( 'Parent Speaker' ),
		'parent_item_colon' => __( 'Parent Speaker:' ),
		'edit_item'         => __( 'Edit Speaker' ),
		'update_item'       => __( 'Update Speaker' ),
		'add_new_item'      => __( 'Add New Speaker' ),
		'new_item_name'     => __( 'New Speaker Name' ),
		'menu_name'         => __( 'Speakers' ),
	);

	$args = array(
		'hierarchical'      => true,
		'public'	    => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'speakers' )
	);

	register_taxonomy( 'speakers', array( 'message' ), $args );
}

 function create_message_posttype() {
// set up labels
	$labels = array(
 		'name' => 'Messages',
    	'singular_name' => 'Message',
    	'add_new' => 'Add New Message',
    	'add_new_item' => 'Add New Message',
    	'edit_item' => 'Edit Message',
    	'new_item' => 'New Message',
    	'all_items' => 'All Messages',
    	'view_item' => 'View Message',
    	'search_items' => 'Search Messages',
    	'not_found' =>  'No Messages Found',
    	'not_found_in_trash' => 'No Messages found in Trash', 
    	'parent_item_colon' => '',
    	'menu_name' => 'Messages',
    	);
  register_post_type( 'message',
    array(
	'labels' => $labels,
	'has_archive' => true,
	'public' => true,
	'publicly_queryable' => true,
	'query_var' => true,
	'supports' => array( 'title'),
	'taxonomies' => array( 'speakers', 'series', 'post_tag' ),	
	'exclude_from_search' => false,
	'capability_type' => 'post',
	'rewrite' => array( 'slug' => 'messages' ),
    	'menu_icon' => 'dashicons-video-alt3',
    )
  );
}

/* Meta box setup function. */
function message_meta_boxes_setup() {

  /* Add meta boxes on the 'add_meta_boxes' hook. */
  add_action( 'add_meta_boxes', 'message_add_post_meta_boxes' );
}

function message_add_post_meta_boxes() {

  add_meta_box(
    'message',      // Unique ID
    esc_html__( 'Message Settings', 'example' ),    // Title
    'message_meta_box',   // Callback function
    'message',         // Admin page (or post type)
    'normal',         // Context
    'high'         // Priority
  );
}

/* Display the post meta box. */
function message_meta_box( $object, $box ) { ?>

  <?php wp_nonce_field( basename( __FILE__ ), 'message_nonce' ); ?>

  <p>
    <label for="vimeo-link"><?php _e( "Vimeo ID", 'example' ); ?></label>
    <br />
    <input type="text" name="vimeo-link" id="vimeo-link" value="<?php echo esc_attr( get_post_meta( $object->ID, 'vimeo_link', true ) ); ?>" size="30" />
    <br />
    <label for="video-duration"><?php _e( "Video Duration", 'example' ); ?></label>
    <br />
    <input type="text" name="video-duration" id="video-duration" value="<?php echo esc_attr( get_post_meta( $object->ID, 'video_duration', true ) ); ?>" size="30" placeholder="hh:mm:ss" />
    </p>
<?php }

/* Save the meta box's post metadata. */
function message_save_post_class_meta( $post_id ) {
  global $post;
  
  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['message_nonce'] ) || !wp_verify_nonce( $_POST['message_nonce'], basename( __FILE__ ) ) )
    return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_vimeo_link_value = ( isset( $_POST['vimeo-link'] ) ? $_POST['vimeo-link'] : '' );
  $new_video_duration_value = ( isset( $_POST['video-duration'] ) ? $_POST['video-duration'] : '' );
  $new_video_sort_value = ( isset( $_POST['video-sort'] ) ? $_POST['video-sort'] : '' );


  update_message_meta($post->ID, 'vimeo_link', $new_vimeo_link_value);
  update_message_meta($post->ID, 'video_duration', $new_video_duration_value);
  update_message_meta($post->ID, 'video_sort', $new_video_sort_value);
}

function update_message_meta($post_id, $meta_key, $new_meta_value){
  /* Get the meta value of the custom field key. */
  $meta_value = get_post_meta( $post_id, $meta_key, true );

  /* If a new meta value was added and there was no previous value, add it. */
  if ( $new_meta_value && '' == $meta_value )
    add_post_meta( $post_id, $meta_key, $new_meta_value, true );

  /* If the new meta value does not match the old value, update it. */
  elseif ( $new_meta_value && $new_meta_value != $meta_value )
    update_post_meta( $post_id, $meta_key, $new_meta_value );

  /* If there is no new meta value but an old value exists, delete it. */
  elseif ( '' == $new_meta_value && $meta_value )
    delete_post_meta( $post_id, $meta_key, $meta_value );
}

function get_custom_post_type_template($single_template) {
     global $post;

     if ($post->post_type == 'message') {
          $single_template = dirname( __FILE__ ) . '/single-message.php';
     }
     return $single_template;
}

add_filter( 'single_template', 'get_custom_post_type_template' );
add_action( 'init', 'create_message_posttype' );
add_action( 'init', 'create_series_taxonomies', 0 );
add_action( 'init', 'create_speakers_taxonomies', 0 );
add_action( 'load-post.php', 'message_meta_boxes_setup' );
add_action( 'load-post-new.php', 'message_meta_boxes_setup' );
add_action('save_post', 'message_save_post_class_meta');

?>