<?php
/**
 * @package BCC
 */
/*
Plugin Name: Buffalo Covenant Customizations
Plugin URI: https://github.com/joebuhlig/buffalo-covenant-customizations
Version: 0.1.1
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
	return $sermon;
}

function all_sermons(){
	$location = 'http://buffalocov.libsyn.com/rss';
	$xml = simplexml_load_file($location);
	$items = $xml->xpath('channel/item');
	$content = "";
	foreach($items as $item) {
		$content .= '<div class="sermon">';
		$content .= date("F j, Y" ,strtotime($item->pubDate)) . ' - <a href="' . trim(parse_url($item->link, PHP_URL_PATH), '/') . '">' . $item->title . '</a>';
		$content .= '</div>';
	}
	return $content;
}
?>