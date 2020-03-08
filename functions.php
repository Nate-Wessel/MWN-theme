<?php 
// Add the nav menu
add_theme_support('menus');
register_nav_menu( 'main', "The main nav menu" );

// remove unecessary links from wp_head()
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');

# enable thumbnails
add_theme_support('post-thumbnails');

add_post_type_support( 'page', 'excerpt' );


#
# add tags to media and enable galleries by image tag
#


# add categories to media attachments allowing 
# galleries by category embedded with shortcodes
function nw_add_tags_to_attachments() {
	register_taxonomy(
		'gallery_tag',
		'attachment',
		array(
			'label' => __( 'Gallery Tags' ),
			'labels' => array(
				'name' => 'Gallery Tags',
				'singular_name' => 'Gallery Tag',
				'add_new_item' => 'Add new gallery tag'
			),
			'capabilities' => array('manage_terms','edit_terms','assign_terms'),
			# show this in the admin list menu
			'show_admin_column' => true,
			# count 'unpublished' attachments too
			'update_count_callback' => '_update_generic_term_count',
			'query_var' => 'gallery_tag',
		)
	);
}
add_action('init','nw_add_tags_to_attachments');

function mwn_gallery_handler( $atts ){
	$tags = $atts['tag'];
	# get any attachments with this tag (or these tags)
	# https://developer.wordpress.org/reference/classes/wp_query
	$wpq = new WP_Query(array(
		'post_type' => 'attachment',
		'gallery_tag' => $tags,
		'post_status' => 'any',
		'orderby' => 'rand',
		'nopaging' => true
	));
	if( $wpq->found_posts == 0 ){ 
		return "\n<p>Alas, no images with this tag!</p>\n"; 
	}
	# start returning a list of images
	$val = "<ul class='mwn-gallery'>\n";
	foreach($wpq->posts as $i=>$post){
		$img = wp_get_attachment_image($post->ID,'thumbnail');
		$url = wp_get_attachment_url( $post->ID );
		$val .= "\n<li><a href='$url'>$img</a></li>";
	}
	$val .= "</ul>\n";
	return $val;
}
add_shortcode( 'mwn_gallery', 'mwn_gallery_handler' );



#
# EVENT custom post types
#

function mwn_upcoming_events_handler( $atts ){
	# handles the mwn_upcoming_events shortcode by listing upcoming events
	$events = get_posts(array( 
		'post_type'=>'mwn_event', 'numberposts'=>-1,
		'orderby'=>'meta_value', 'meta_key=start'
	));
	$val = '';
	foreach($events as $event){
		if(mwn_event_is_yet($event->ID)){
			$val .= mwn_event_short_div($event->ID);
		}
	}
	return $val != '' ? $val : '<p>No upcoming events found. Alas!</p>';
}
add_shortcode( 'mwn_upcoming_events', 'mwn_upcoming_events_handler' );

function register_mwn_event_post_type(){
	$args = array(
		'label'=>'Events',
		'labels'=>array(
			'name'=>'Events',
			'singular_name'=>'Event',
			'add_new_item'=>'Add New Event',
			'edit_item'=>'Edit Event',
			'view_item'=>'View Event'
		),
		'description' => 'Event, class, workshop, etc.',
		'public' => true,
		'show_ui' => true,
		'show_in_rest' => true,
		'supports' => array('title','editor','revisions','excerpt'), # 'thumbnail'
		'rewrite' => array('slug'=>'event'),
		'has_archive' => 'events',
		'register_meta_box_cb' => 'add_mwn_event_meta_box',
	);
	register_post_type('mwn_event',$args);
}
add_action( 'init', 'register_mwn_event_post_type' );

function add_mwn_event_meta_box(){
	add_meta_box(
		"mwn_event_meta_box", # ID
		"Event Dates",        # metabox title
		"mwn_event_meta_box_markup", # callback function to display box contents
		"mwn_event",          # post type effected
		"side", "low",        # location, priority
		null                  # callback args
	);
}

function mwn_event_meta_box_markup($object){
	# function handles content of events dates metabox ?>
	<div>
		<label for="city">City</label><br>
		<input name="city" type="text" value="<?php echo get_post_meta($object->ID, "city", true); ?>"><br>
		<label for="external-link">External link</label><br>
		<input name="external-link" type="text" value="<?php echo get_post_meta($object->ID, "external-link", true); ?>"><br>
		<label for="start">Event Start Time</label><br>
		<input name="start" type="text" value="<?php echo get_post_meta($object->ID, "start", true); ?>"><br>
		<label for="end">Event End Time</label><br>
		<input name="end" type="text" value="<?php echo get_post_meta($object->ID, "end", true); ?>"><br>
		<p>Time format should be as follows: <br>'YYYY-MM-DD HH:MM:SS'</p>
	</div>
<?php 
}

add_action("save_post", "mwn_save_event_meta_box");
function mwn_save_event_meta_box($post_id){
	if( get_post_type($post_id) != 'mwn_event' ){ return; }
	if(isset($_POST['city'])){
		update_post_meta($post_id, 'city', $_POST['city']);
	}
	if(isset($_POST['start'])){
		update_post_meta($post_id, 'start', $_POST['start']);
	}
	if(isset($_POST['end'])){
		update_post_meta($post_id, 'end', $_POST['end']);
	}
	if(isset($_POST['external-link'])){
		update_post_meta($post_id, 'external-link', $_POST['external-link']);
	}
}

function mwn_event_is_yet($ID){
	# is this event scheduled for the future? yes = true, else false
	# return false for invalid date formats
	$datestring = get_post_meta($ID, 'start',true);
	if($datestring ==''){return false;}
	$tz = new DateTimeZone('America/Toronto');
	$startdate = date_create_from_format('Y-m-d H:i:s',$datestring,$tz);
	if(!$startdate){return false;}
	if( $startdate < new DateTime("last week") ){ return false; };
	return true;
}

function mwn_date_parse($datestring){
	# parse a date string in YYYY-MM-DD HH:MM:SS
	$tz = new DateTimeZone('America/Toronto');
	if( $date = date_create_from_format('Y-m-d H:i:s',$datestring,$tz) ){
		return date_format($date,'M d, Y - g:sa');
	}
	return $datestring;
}

function mwn_event_short_div($ID){
	# return the HTML for a short event description - to be used in shortcodes
	# and archive pages and so on
	if( get_post_type($ID) != 'mwn_event' ){ return ''; }
	$link = get_the_permalink($ID);
	$title = get_the_title($ID);
	$val = "<div class='event'><a href='$link' title='Event details'><h3>$title</h3></a>";
	$val .= "<p class='meta'>";
	# get and parse post metadata 
	$city  = mwn_date_parse( get_post_meta($ID,'city',true) );
	$start = mwn_date_parse( get_post_meta($ID,'start',true) );
	$val .= "<span class='city'>$city</span> -\n";
	$val .= "<span class='start'>$start</span>\n";
	$val .= "</p>\n"; # .meta
	if(has_excerpt($ID)){
		$excerpt = get_the_excerpt($ID);
		$val .= "<p class='excerpt'>$excerpt</p>\n";
	}
	$val .= "</div>"; # .event
	return $val;
}


?>
