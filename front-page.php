<?php
get_header();
$img = fn($f) => esc_url(wr_asset_url($f));
$cat_imgs = ['tops' => 'p-tee.jpg', 'bottoms' => 'p-denim.jpg', 'outerwear' => 'p-overcoat.jpg', 'accessories' => 'p-tote.jpg'];
?>
<section class="wr-hero">
  <div class="wr-hero__text">
    <p class="wr-eyebrow">Autumn / Winter 2026</p>
    <h1 class="wr-display">Considered clothing,<br><em>made to last.</em></h1>
    <p class="wr-hero__lede">Quiet essentials in black, white and gray. Cut well, finished carefully, and designed to be worn for years.</p>
    <div class="wr-actions">
      <a class="wr-btn" href="<?php echo esc_url(wr_wc_url('shop')); ?>">Shop the collection</a>
      <a class="wr-link" href="<?php echo esc_url(wr_page_url('about-us')); ?>">Our approach</a>
    </div>
  </div>
  <figure class="wr-hero__media"><img src="<?php echo $img('hero.jpg'); ?>" alt="Linen shirt on a wooden hanger against a soft neutral wall" width="1200" height="1500" fetchpriority="high"></figure>
</section>

<div class="wr-strip" aria-hidden="true">
  <span>Heavyweight cotton</span><span>Small runs</span><span>Free returns, 30 days</span><span>Made to be worn for years</span>
</div>

<section class="wr-section wr-container">
  <header class="wr-section__head">
    <h2 class="wr-title">Shop by category</h2>
  </header>
  <div class="wr-cats">
    <?php foreach (wr_categories() as $slug => $name) :
        $t = get_term_by('slug', $slug, 'product_cat'); ?>
      <a class="wr-cat" href="<?php echo esc_url(wr_cat_url($slug)); ?>">
        <img src="<?php echo $img($cat_imgs[$slug]); ?>" alt="" loading="lazy" width="900" height="1125">
        <span class="wr-cat__label"><?php echo esc_html($name); ?><small><?php echo $t ? (int) $t->count . ' pieces' : ''; ?></small></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="wr-section wr-container">
  <header class="wr-section__head">
    <h2 class="wr-title">New arrivals</h2>
    <a class="wr-link" href="<?php echo esc_url(wr_wc_url('shop')); ?>">View all</a>
  </header>
  <?php
  // Featured products (star icon in Products list); falls back to newest.
  $featured = function_exists('wc_get_featured_product_ids') ? wc_get_featured_product_ids() : [];
  echo do_shortcode($featured ? '[products limit="8" columns="4" visibility="featured" orderby="menu_order title" order="ASC"]' : '[products limit="8" columns="4" orderby="date" order="DESC"]');
  ?>
</section>

<section class="wr-editorial">
  <figure class="wr-editorial__media"><img src="<?php echo $img('editorial.jpg'); ?>" alt="Rail of neutral garments in taupe, sage and grey" loading="lazy" width="1400" height="984"></figure>
  <div class="wr-editorial__text">
    <p class="wr-eyebrow">Our approach</p>
    <h2 class="wr-display wr-display--md">Fewer things, <em>made properly.</em></h2>
    <ol class="wr-principles">
      <li><span>01</span><div><h3>Materials first</h3><p>Dense cotton, wool blends and washed linen from mills we can name.</p></div></li>
      <li><span>02</span><div><h3>A considered fit</h3><p>Patterns refined over seasons, not reinvented every drop.</p></div></li>
      <li><span>03</span><div><h3>Built to last</h3><p>Reinforced seams and honest finishing, so each piece earns its place.</p></div></li>
    </ol>
    <a class="wr-btn wr-btn--ghost" href="<?php echo esc_url(wr_page_url('about-us')); ?>">Read our story</a>
  </div>
</section>

<section class="wr-assure wr-container">
  <div><h3>Free shipping</h3><p>On all orders over $150, worldwide.</p></div>
  <div><h3>30-day returns</h3><p>Unworn pieces, returned free of charge.</p></div>
  <div><h3>Secure checkout</h3><p>Encrypted payments, no stored card details.</p></div>
  <div><h3>Real support</h3><p>Write to us and a person replies within two days.</p></div>
</section>
<?php get_footer(); ?>
