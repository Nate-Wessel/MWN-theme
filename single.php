<?php
get_header();

// start loop
if(have_posts()){
	the_post();
?>
<div class="blog single">

	<h1><?php the_title(); ?></h1>
	<span class="meta"><?php the_time('F jS, Y') ?></span>

	<?php the_content(); ?>

<?php }// end if ?>
</div><!-- .blog .single -->

<?php #get_sidebar('blog'); ?>

<?php get_footer(); ?>

