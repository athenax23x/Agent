<?php get_header(); ?>
<div class="wr-container wr-pagehead">
  <article class="wr-prose">
    <p class="wr-eyebrow">Error 404</p>
    <h1 class="wr-display wr-display--md">That page has moved on.</h1>
    <p>The link may be old, or the piece may have sold out. Try a search, or start from the collection.</p>
    <form class="wr-inline-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
      <label class="screen-reader-text" for="wr-404-search">Search products</label>
      <input id="wr-404-search" type="search" name="s" placeholder="Search products">
      <input type="hidden" name="post_type" value="product">
      <button class="wr-btn" type="submit">Search</button>
    </form>
    <p><a class="wr-link" href="<?php echo esc_url(home_url('/')); ?>">Back to home</a></p>
  </article>
</div>
<?php if (function_exists('wr_suggest_products')) : ?>
<div class="wr-container wr-section">
  <h2 class="wr-title">In the meantime</h2>
  <div class="wr-suggest"><?php wr_suggest_products(); ?></div>
</div>
<?php endif; ?>
<?php get_footer(); ?>
