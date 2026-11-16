<?php
get_header();
$is_store = function_exists('is_woocommerce') && (is_cart() || is_checkout() || is_account_page());
while (have_posts()) : the_post(); ?>
<header class="wr-pagehead wr-container">
  <h1 class="wr-display wr-display--md"><?php the_title(); ?></h1>
</header>
<div class="wr-container">
  <article class="<?php echo $is_store ? 'wr-store-page' : 'wr-prose'; ?>">
    <?php the_content(); ?>
  </article>
</div>
<?php endwhile;
get_footer();
