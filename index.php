<?php get_header(); ?>

<h1><?php echo get_the_title(get_option('page_for_posts')); ?></h1>
<div class="blog">
<?php if(have_posts()){ while (have_posts()){ the_post(); // start the loop ?>

	<div class="subpage">
		<a href="<?php the_permalink(); ?>">
			<h2><?php the_title(); ?></h2>
		</a>
		<?php if(has_excerpt()){ ?>
			<p class="excerpt"><?php echo get_the_excerpt();?></p>		
		<?php } ?>
		<span class="editdate"><?php the_time('F, \'y') ?></span>
	</div><!--.postlink-->

<?php } } // end the loop ?>

</div><!--.blog-->

<?php // print footer
get_footer();
?>
