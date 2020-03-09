<?php get_header(); 
// start loop
if(have_posts()){
	the_post();
?>
<div id="event">
<h1 class="page-title"><?php the_title(); ?></h1>
<?php
	# get and parse post metadata 
	$city    = mwn_date_parse( get_post_meta($post->ID,'city',true) );
	$start   = mwn_date_parse( get_post_meta($post->ID,'start',true) );
	#$end     = mwn_date_parse( get_post_meta($post->ID,'end',true) );
	$extLink = get_post_meta($post->ID,'external-link',true);
 	# print metadata if any
	if($city != '' || $extLink != '' || $start != '' || $end != ''){
		echo "<p id='meta'>";
		echo $city != '' ?    "<span class='city'>$city</span>" : '';
		echo $start != '' ?   "<span class='date start'>$start</span><br>" : '';
		echo $extLink != '' ? "<span class='external-link'><a href='$extLink'>Tickets, location,  <i>etc.</i></a></span><br>\n": '';
		#echo $end != '' ?     "<span class='date end'>$end</span><br>" : '';
		echo "</p>";
	}
?>

<?php 
	the_content(); 
}
?>
</div><!--#event-->


<?php get_footer(); ?>
