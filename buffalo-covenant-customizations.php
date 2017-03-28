<?php
/**
 * @package BCC
 */
/*
Plugin Name: Buffalo Covenant Customizations
Plugin URI: https://github.com/joebuhlig/buffalo-covenant-customizations
Version: 0.1.29
Author: Joe Buhlig
Author URI: http://joebuhlig.com
GitHub Plugin URI: https://github.com/joebuhlig/buffalo-covenant-customizations
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: buffalo-covenant-theme
*/

add_filter( 'query_vars', 'wpse26388_query_vars' );
function wpse26388_query_vars( $query_vars ){
    $query_vars[] = 'audio_url';
    $query_vars[] = 'autoplay';
    return $query_vars;
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
     register_widget( 'Menu_Item_Description_Widget' );
     register_widget( 'Menu_Image_Widget' );
     register_widget( 'Menu_Latest_Sermon_Widget' );
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
			
			<input id="<?php echo $this->get_field_id( 'featured_event_image' ); ?>" type="text" name="<?php echo $this->get_field_name( 'featured_event_image' ); ?>" value="<?php echo $featured_event_image ?>"><br>

			<label for="<?php echo $this->get_field_id( 'featured_event_title' ); ?>"><?php _e( 'Title:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'featured_event_title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'featured_event_title' ); ?>" value="<?php echo $featured_event_title ?>"><br>

			<label for="<?php echo $this->get_field_id( 'featured_event_text' ); ?>"><?php _e( 'Text:' ); ?></label> <br>
			<textarea class="widefat" rows="4" cols="20" id="<?php echo $this->get_field_id('featured_event_text'); ?>" name="<?php echo $this->get_field_name('featured_event_text'); ?>"><?php echo esc_textarea( $featured_event_text ); ?></textarea>

			<label for="<?php echo $this->get_field_id( 'featured_event_link' ); ?>"><?php _e( 'Link:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'featured_event_link' ); ?>" type="text" name="<?php echo $this->get_field_name( 'featured_event_link' ); ?>" value="<?php echo $featured_event_link ?>"><br>

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

class Menu_Item_Description_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Menu_Item_Description_Widget', // Base ID
			__('Menu Item Description Widget', 'text_domain'), // Name
			array('description' => __( 'Adds a description to the menu.', 'text_domain' ),) // Args
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
			echo '<div class="menu-description">';
			echo '<div class="menu-description-title">' . $instance[ 'menu_description_title' ] . '</div>';
			echo '<div class="menu-description-text">' . $instance[ 'menu_description_text' ] . '</div>';
			if ($instance['menu_description_button_text']) { echo '<div class="menu-description-button"><a href="' . $instance['menu_description_link'] . '"><button>' . $instance['menu_description_button_text'] . '</button></a></div>';};
			
		if ( array_key_exists('after_widget', $args) ) echo $args['after_widget'];
	}

	public function form( $instance ) {
		$menu_description_title = ( isset( $instance[ 'menu_description_title' ] ) ) ? $instance[ 'menu_description_title' ] : "";
		$menu_description_link = ( isset( $instance[ 'menu_description_link' ] ) ) ? $instance[ 'menu_description_link' ] : "";
		$menu_description_text = ( isset( $instance[ 'menu_description_text' ] ) ) ? $instance[ 'menu_description_text' ] : "";
		$menu_description_button_text = ( isset( $instance[ 'menu_description_button_text' ] ) ) ? $instance[ 'menu_description_button_text' ] : "";
		?>
		
		<div>
			<label for="<?php echo $this->get_field_id( 'menu_description_title' ); ?>"><?php _e( 'Title:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'menu_description_title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'menu_description_title' ); ?>" value="<?php echo $menu_description_title ?>"><br><br>

			<label for="<?php echo $this->get_field_id( 'menu_description_link' ); ?>"><?php _e( 'Link:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'menu_description_link' ); ?>" type="text" name="<?php echo $this->get_field_name( 'menu_description_link' ); ?>" value="<?php echo $menu_description_link ?>"><br><br>

			<label for="<?php echo $this->get_field_id( 'menu_description_button_text' ); ?>"><?php _e( 'Button Text:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'menu_description_button_text' ); ?>" type="text" name="<?php echo $this->get_field_name( 'menu_description_button_text' ); ?>" value="<?php echo $menu_description_button_text ?>"><br><br>

			<label for="<?php echo $this->get_field_id( 'menu_description_text' ); ?>"><?php _e( 'Description:' ); ?></label> 
			<p>Supports HTML tags.</p>
			<textarea class="widefat" rows="4" cols="20" id="<?php echo $this->get_field_id('menu_description_text'); ?>" name="<?php echo $this->get_field_name('menu_description_text'); ?>"><?php echo esc_textarea( $menu_description_text ); ?></textarea>

		</div>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		$instance['menu_description_title'] = ( ! empty( $new_instance['menu_description_title'] ) ) ? strip_tags( $new_instance['menu_description_title'] ) : '';
		$instance['menu_description_link'] = ( ! empty( $new_instance['menu_description_link'] ) ) ? strip_tags( $new_instance['menu_description_link'] ) : '';
		$instance['menu_description_button_text'] = ( ! empty( $new_instance['menu_description_button_text'] ) ) ? strip_tags( $new_instance['menu_description_button_text'] ) : '';
		$instance['menu_description_text'] = $new_instance['menu_description_text'];
		return $instance;
	}
} // class My_Widget

class Menu_Image_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Menu_Image_Widget', // Base ID
			__('Menu Image Widget', 'text_domain'), // Name
			array('description' => __( 'Adds an image link to the menu.', 'text_domain' ),) // Args
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
			echo '<div class="menu-image">';
			echo '<a href="' .  $instance['menu_image_link'] . '">';
			echo '<img src="' . $instance[ 'menu_image' ] . '">';
			echo '</a></div>';
			
		if ( array_key_exists('after_widget', $args) ) echo $args['after_widget'];
	}

	public function form( $instance ) {
		$menu_image_link = ( isset( $instance[ 'menu_image_link' ] ) ) ? $instance[ 'menu_image_link' ] : "";
		$menu_image = ( isset( $instance[ 'menu_image' ] ) ) ? $instance[ 'menu_image' ] : "";
		?>
		
		<div>
			<label for="<?php echo $this->get_field_id( 'menu_image_link' ); ?>"><?php _e( 'Link:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'menu_image_link' ); ?>" type="text" name="<?php echo $this->get_field_name( 'menu_image_link' ); ?>" value="<?php echo $menu_image_link ?>"><br><br>

			<label for="<?php echo $this->get_field_id( 'menu_image' ); ?>"><?php _e( 'Image URL:' ); ?></label> <br>
			
			<input id="<?php echo $this->get_field_id( 'menu_image' ); ?>" type="text" name="<?php echo $this->get_field_name( 'menu_image' ); ?>" value="<?php echo $menu_image ?>">

		</div>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		$instance['menu_image'] = ( ! empty( $new_instance['menu_image'] ) ) ? strip_tags( $new_instance['menu_image'] ) : '';
		$instance['menu_image_link'] = ( ! empty( $new_instance['menu_image_link'] ) ) ? strip_tags( $new_instance['menu_image_link'] ) : '';
		return $instance;
	}
} // class My_Widget

class Menu_Latest_Sermon_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Menu_Latest_Sermon_Widget', // Base ID
			__('Menu Latest Sermon Widget', 'text_domain'), // Name
			array('description' => __( 'Adds the latest sermon to the menu.', 'text_domain' ),) // Args
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
			$sermon = get_latest_sermon();
			echo '<div class="menu-section">';
			echo '<h2>Latest Message</h2>';
			echo '<a style="width:200px;" href="' . $sermon['link'] . '">';
			echo '<img class="sermon-thumbnail" src="' . $sermon['thumb_src'] . '">';
			echo '</a>';
			echo '</div>';
			
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
		?>
		<p>No settings for this widget.</p>
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

	register_taxonomy( 'series', array( 'sermon' ), $args );
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

	register_taxonomy( 'speakers', array( 'sermon' ), $args );
}

function create_passages_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Passages', 'taxonomy general name' ),
		'singular_name'     => _x( 'Passage', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Passages' ),
		'all_items'         => __( 'All Passages' ),
		'parent_item'       => __( 'Parent Passage' ),
		'parent_item_colon' => __( 'Parent Passage:' ),
		'edit_item'         => __( 'Edit Passage' ),
		'update_item'       => __( 'Update Passage' ),
		'add_new_item'      => __( 'Add New Passage' ),
		'new_item_name'     => __( 'New Passage Name' ),
		'menu_name'         => __( 'Passages' ),
	);

	$args = array(
		'hierarchical'      => true,
		'public'	    => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'passages' )
	);

	register_taxonomy( 'passages', array( 'sermon' ), $args );
}


 function create_sermon_posttype() {
// set up labels
	$labels = array(
 		'name' => 'Media',
    	'singular_name' => 'Media',
    	'add_new' => 'Add New Sermon',
    	'add_new_item' => 'Add New Sermon',
    	'edit_item' => 'Edit Sermon',
    	'new_item' => 'New Sermon',
    	'all_items' => 'All Sermons',
    	'view_item' => 'View Sermon',
    	'search_items' => 'Search Sermons',
    	'not_found' =>  'No Sermons Found',
    	'not_found_in_trash' => 'No Sermons found in Trash', 
    	'parent_item_colon' => '',
    	'menu_name' => 'Sermons',
    	);
  register_post_type( 'sermon',
    array(
	'labels' => $labels,
	'has_archive' => true,
	'public' => true,
	'publicly_queryable' => true,
	'query_var' => true,
	'supports' => array( 'title', 'editor', 'thumbnail'),
	'taxonomies' => array( 'speakers', 'series', 'passages', 'post_tag' ),	
	'exclude_from_search' => false,
	'capability_type' => 'post',
	'rewrite' => array( 'slug' => 'media' ),
    	'menu_icon' => 'dashicons-video-alt3',
    )
  );
}

/* Meta box setup function. */
function sermon_meta_boxes_setup() {

  /* Add meta boxes on the 'add_meta_boxes' hook. */
  add_action( 'add_meta_boxes', 'sermon_add_post_meta_boxes' );
}

function sermon_add_post_meta_boxes() {

  add_meta_box(
    'sermon',      // Unique ID
    esc_html__( 'Sermon Settings', 'example' ),    // Title
    'sermon_meta_box',   // Callback function
    'sermon',         // Admin page (or post type)
    'normal',         // Context
    'high'         // Priority
  );
}

/* Display the post meta box. */
function sermon_meta_box( $object, $box ) { ?>

  <?php wp_nonce_field( basename( __FILE__ ), 'sermon_nonce' ); ?>

  <p>
    <label for="vimeo-link"><?php _e( "Vimeo ID", 'example' ); ?></label>
    <br />
    <input type="text" name="vimeo-link" id="vimeo-link" value="<?php echo esc_attr( get_post_meta( $object->ID, 'vimeo_link', true ) ); ?>" size="30" />
    <br />
    <label for="video-duration"><?php _e( "Video Duration", 'example' ); ?></label>
    <br />
    <input type="text" name="video-duration" id="video-duration" value="<?php echo esc_attr( get_post_meta( $object->ID, 'video_duration', true ) ); ?>" size="30" placeholder="hh:mm:ss" />
    <br />
    <label for="podcast-guid"><?php _e( "Podcast Episode", 'example' ); ?></label>
    <br />
    <select name="podcast-guid" id="podcast-guid"><?php
    	$location = 'http://buffalocov.libsyn.com/rss';
		$xml = simplexml_load_file($location);
		$items = $xml->xpath('channel/item');
		$episode = esc_attr( get_post_meta( $object->ID, 'podcast_guid', true ) );
		if ($episode){
			?><option value="">-- None --</option><?php
		}
		else {
			?><option value="" selected>-- None --</option><?php
		};
		foreach($items as $item) {
			?><option value="<?php echo $item->guid ?>" <?php if ($episode == $item->guid) {echo "selected";} ?>><?php  echo date("F j, Y" ,strtotime($item->pubDate)) . " - " . $item->title ?></option><?php
		};?>
    </select> 
    </p>
<?php }

/* Save the meta box's post metadata. */
function sermon_save_post_class_meta( $post_id ) {
  global $post;
  
  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['sermon_nonce'] ) || !wp_verify_nonce( $_POST['sermon_nonce'], basename( __FILE__ ) ) )
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
  $new_podcast_guid_value = ( isset( $_POST['podcast-guid'] ) ? $_POST['podcast-guid'] : '' );


  update_sermon_meta($post->ID, 'vimeo_link', $new_vimeo_link_value);
  update_sermon_meta($post->ID, 'video_duration', $new_video_duration_value);
  update_sermon_meta($post->ID, 'video_sort', $new_video_sort_value);
  update_sermon_meta($post->ID, 'podcast_guid', $new_podcast_guid_value);
}

function update_sermon_meta($post_id, $meta_key, $new_meta_value){
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

add_action( 'init', 'create_sermon_posttype' );
add_action( 'init', 'create_series_taxonomies', 0 );
add_action( 'init', 'create_speakers_taxonomies', 0 );
add_action( 'init', 'create_passages_taxonomies', 0 );
add_action( 'load-post.php', 'sermon_meta_boxes_setup' );
add_action( 'load-post-new.php', 'sermon_meta_boxes_setup' );
add_action('save_post', 'sermon_save_post_class_meta');

function create_staff_role_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Staff Roles', 'taxonomy general name' ),
		'singular_name'     => _x( 'Staff Role', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Staff Roles' ),
		'all_items'         => __( 'All Staff Roles' ),
		'parent_item'       => __( 'Parent Staff Role' ),
		'parent_item_colon' => __( 'Parent Staff Role:' ),
		'edit_item'         => __( 'Edit Staff Role' ),
		'update_item'       => __( 'Update Staff Role' ),
		'add_new_item'      => __( 'Add New Staff Role' ),
		'new_item_name'     => __( 'New Staff Role Name' ),
		'menu_name'         => __( 'Staff Roles' ),
	);

	$args = array(
		'hierarchical'      => true,
		'public'	    => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => false,
		'rewrite'           => array( 'slug' => 'staff-roles' )
	);

	register_taxonomy( 'staff-roles', array( 'staff' ), $args );
}

function create_staff_posttype() {
// set up labels
	$labels = array(
 		'name' => 'Staff Members',
    	'singular_name' => 'Staff Member',
    	'add_new' => 'Add New Staff Member',
    	'add_new_item' => 'Add New Staff Member',
    	'edit_item' => 'Edit Staff Member',
    	'new_item' => 'New Staff Member',
    	'all_items' => 'All Staff Members',
    	'view_item' => 'View Staff Member',
    	'search_items' => 'Search Staff Members',
    	'not_found' =>  'No Staff Members Found',
    	'not_found_in_trash' => 'No Staff Members found in Trash', 
    	'parent_item_colon' => '',
    	'menu_name' => 'Staff',
    	);
  register_post_type( 'staff',
    array(
	'labels' => $labels,
	'has_archive' => false,
	'public' => true,
	'publicly_queryable' => false,
	'query_var' => false,
	'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes'),
	'taxonomies' => array( 'staff-roles'),	
	'exclude_from_search' => true,
	'capability_type' => 'post',
	'rewrite' => array( 'slug' => 'staff' ),
    	'menu_icon' => 'dashicons-groups',
    )
  );
}

/* Meta box setup function. */
function staff_meta_boxes_setup() {

  /* Add meta boxes on the 'add_meta_boxes' hook. */
  add_action( 'add_meta_boxes', 'staff_add_post_meta_boxes' );
}

function staff_add_post_meta_boxes() {

  add_meta_box(
    'staff',      // Unique ID
    esc_html__( 'Staff Settings', 'example' ),    // Title
    'staff_meta_box',   // Callback function
    'staff',         // Admin page (or post type)
    'normal',         // Context
    'high'         // Priority
  );
}

/* Display the post meta box. */
function staff_meta_box( $object, $box ) { ?>

  <?php wp_nonce_field( basename( __FILE__ ), 'staff_nonce' ); ?>

  <p>
    <label for="staff-title"><?php _e( "Staff Member Title", 'example' ); ?></label>
    <br />
    <input type="text" name="staff-title" id="staff-title" value="<?php echo esc_attr( get_post_meta( $object->ID, 'staff_title', true ) ); ?>" size="30" />
    </p>
<?php }

/* Save the meta box's post metadata. */
function staff_save_post_class_meta( $post_id ) {
  global $post;
  
  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['staff_nonce'] ) || !wp_verify_nonce( $_POST['staff_nonce'], basename( __FILE__ ) ) )
    return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_staff_title_value = ( isset( $_POST['staff-title'] ) ? $_POST['staff-title'] : '' );

  update_sermon_meta($post->ID, 'staff_title', $new_staff_title_value);
}

add_action( 'init', 'create_staff_posttype' );
add_action( 'init', 'create_staff_role_taxonomies', 0 );
add_action( 'load-post.php', 'staff_meta_boxes_setup' );
add_action( 'load-post-new.php', 'staff_meta_boxes_setup' );
add_action('save_post', 'staff_save_post_class_meta');


/* Meta box setup function. */
function bcc_page_meta_boxes_setup() {

  /* Add meta boxes on the 'add_meta_boxes' hook. */
  add_action( 'add_meta_boxes', 'bcc_page_add_post_meta_boxes' );
}

function bcc_page_add_post_meta_boxes() {

  add_meta_box(
    'bcc_page',      // Unique ID
    esc_html__( 'BCC Settings', 'example' ),    // Title
    'bcc_page_meta_box',   // Callback function
    'page',         // Admin page (or post type)
    'normal',         // Context
    'high'         // Priority
  );
}

/* Display the post meta box. */
function bcc_page_meta_box( $object, $box ) { ?>

  <?php wp_nonce_field( basename( __FILE__ ), 'bcc_page_nonce' ); ?>

  <p>
    <label for="hide-page-title"><?php _e( "Hide Page Title?", 'example' ); ?></label>
    <input type="checkbox" name="hide-page-title" id="hide-page-title" <?php if (get_post_meta( $object->ID, 'hide_page_title', true ) ): ?>checked<?php endif; ?> /><br>

    <label for="hide-page-sidebar"><?php _e( "Hide Page Sidebar?", 'example' ); ?></label>
    <input type="checkbox" name="hide-page-sidebar" id="hide-page-sidebar" <?php if (get_post_meta( $object->ID, 'hide_page_sidebar', true ) ): ?>checked<?php endif; ?> /><br>

    <label for="mobile-header"><?php _e( "Mobile Header URL", 'example' ); ?></label>
    <input type="text" name="mobile-header" id="mobile-header" value="<?php echo esc_attr( get_post_meta( $object->ID, 'mobile_header', true ) ); ?>" size="30" /><br>
    </p>
<?php }

/* Save the meta box's post metadata. */
function bcc_page_save_post_class_meta( $post_id ) {
  global $post;
  
  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['bcc_page_nonce'] ) || !wp_verify_nonce( $_POST['bcc_page_nonce'], basename( __FILE__ ) ) )
    return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_hide_page_title_value = isset( $_POST['hide-page-title']);
  $new_hide_page_sidebar_value = isset( $_POST['hide-page-sidebar']);
  $new_mobile_header_value = ( isset( $_POST['mobile-header'] ) ? $_POST['mobile-header'] : '' );

  update_bcc_page_meta($post->ID, 'hide_page_title', $new_hide_page_title_value);
  update_bcc_page_meta($post->ID, 'hide_page_sidebar', $new_hide_page_sidebar_value);
  update_bcc_page_meta($post->ID, 'mobile_header', $new_mobile_header_value);
}

function update_bcc_page_meta($post_id, $meta_key, $new_meta_value){
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

add_action( 'load-post.php', 'bcc_page_meta_boxes_setup' );
add_action( 'load-post-new.php', 'bcc_page_meta_boxes_setup' );
add_action('save_post', 'bcc_page_save_post_class_meta');
?>