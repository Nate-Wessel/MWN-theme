<?php get_header(); 
// start loop
if(have_posts()){
	the_post();
?>
<div id="event">
<h1 class="page-title"><?php the_title(); ?></h1>
<?php
	# get and parse post metadata 
	$city = get_post_meta($post->ID,'city',true);
	$start = mwn_date_parse(
		get_post_meta($post->ID,'start',true)
	);
	
	$duration = get_post_meta($post->ID,'duration',true);
	$seats = get_post_meta($post->ID,'seats',true);
	$closed = ( // event is closed or over
		get_post_meta($post->ID,'registration-closed',true) == 'true'
		|| ! mwn_event_is_yet($post->ID)
	);
	#$end = mwn_date_parse( get_post_meta($post->ID,'end',true) );
	$extLink = get_post_meta($post->ID,'external-link',true);
 	# print metadata if any
	if($city != '' || $extLink != '' || $start != '' || $end != ''){
		echo "<div id='meta'>";
		if($closed){
			echo "<div class='closed'>Registration is closed</div>";
		}else{
			echo "<div class='open'>Registration is OPEN</div>";
		}
		echo $city ? "<div class='city'>$city</div>" : '';
		echo $start ? "<div class='date start'> Starts $start</div>" : '';
		echo $duration ? "<div class='duration'>Duration: $duration</div>" : '';
		echo $extLink ? "<div class='external-link'><a href='$extLink'>Tickets, location,  <i>etc.</i></a></div>\n": '';
		echo ($seats && ! $closed) ? "<div class='seats'>$seats spots available</div>" : '';
		#echo $end != '' ?     "<span class='date end'>$end</span><br>" : '';
		echo "</div>";
	}
?>

<?php 
	the_content(); 
}
?>
</div><!--#event-->


<?php get_footer(); ?>
