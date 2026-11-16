<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="wr-skip" href="#content">Skip to content</a>
<div class="wr-announce">Free shipping on orders over $150 &middot; Free returns within 30 days</div>
<header class="wr-header">
  <div class="wr-header__inner">
    <button class="wr-menu-toggle" aria-expanded="false" aria-controls="wr-nav"><span></span><span></span><span class="screen-reader-text">Menu</span></button>
    <nav class="wr-nav" id="wr-nav" aria-label="Primary">
      <?php
      if (has_nav_menu('primary')) {
          wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'depth' => 1]);
      } else {
          echo '<ul><li><a href="' . esc_url(wr_wc_url('shop')) . '">Shop</a></li><li><a href="' . esc_url(wr_page_url('about-us')) . '">About</a></li><li><a href="' . esc_url(wr_page_url('faq')) . '">FAQ</a></li><li><a href="' . esc_url(wr_page_url('contact')) . '">Contact</a></li></ul>';
      }
      ?>
    </nav>
    <a class="wr-brand" href="<?php echo esc_url(home_url('/')); ?>">Wright Parel</a>
    <div class="wr-tools">
      <button type="button" class="wr-search-toggle" aria-expanded="false" aria-controls="wr-search"><span class="screen-reader-text">Search</span></button>
      <a href="<?php echo esc_url(wr_wc_url('myaccount')); ?>"><?php echo is_user_logged_in() ? 'Account' : 'Sign in'; ?></a>
      <a href="<?php echo esc_url(wr_wc_url('cart')); ?>" class="wr-cart-link">Cart <span class="wr-cart-count"><?php echo (int) wr_cart_count(); ?></span></a>
    </div>
  </div>
  <div class="wr-search" id="wr-search" hidden>
    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
      <label class="screen-reader-text" for="wr-search-field">Search products</label>
      <input id="wr-search-field" type="search" name="s" placeholder="Search for a piece" value="<?php echo esc_attr(get_search_query()); ?>">
      <input type="hidden" name="post_type" value="product">
      <button type="submit" class="wr-btn">Search</button>
    </form>
  </div>
</header>
<main id="content" class="wr-main">
