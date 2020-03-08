<?php get_header(); ?>
<h1>Workshops & Events</h1>

<?php 
$query = new WP_Query( array( 
	'post_type' => 'mwn_event',
	'orderby' => 'meta_value',
	'meta_key' => 'start'
)); 

// The Loop
if ( $query->have_posts() ) {
	echo '<div id="events">';
	while ( $query->have_posts() ) {
		$query->the_post();
		echo mwn_event_short_div($post->ID);
	}
	echo '</div><!--#events-->';
} else {
	// no posts found
}
?>

<?php get_footer(); ?>
