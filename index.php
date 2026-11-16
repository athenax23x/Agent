<?php get_header(); ?>
<div class="wr-container wr-pagehead">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  <article class="wr-prose">
    <h1 class="wr-display wr-display--md"><?php the_title(); ?></h1>
    <?php the_content(); ?>
  </article>
<?php endwhile; else : ?>
  <h1 class="wr-display wr-display--md">Page not found</h1>
  <p><a class="wr-btn" href="<?php echo esc_url(home_url('/')); ?>">Back to home</a></p>
<?php endif; ?>
</div>
<?php get_footer(); ?>
