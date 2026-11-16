<?php
get_header();
$is_store = function_exists('is_woocommerce') && (is_cart() || is_checkout() || is_account_page());

while (have_posts()) : the_post();

    if (wr_is_legal_page()) :
        $current = wr_legal_slug();
        [$legal_content, $toc] = wr_legal_prepare(apply_filters('the_content', get_the_content()));
        ?>
<header class="wr-legal-head wr-container">
  <p class="wr-eyebrow">Legal &amp; policies</p>
  <h1 class="wr-display wr-display--md"><?php the_title(); ?></h1>
  <p class="wr-legal-meta">Wright Apparel &middot; Last updated <time datetime="<?php echo esc_attr(get_the_modified_date('c')); ?>"><?php echo esc_html(get_the_modified_date('j F Y')); ?></time></p>
</header>
<div class="wr-container wr-legal">
  <aside class="wr-legal__side" aria-label="Policy navigation">
    <nav class="wr-legal__nav" aria-label="Policies">
      <p class="wr-legal__label">Policies</p>
      <ul>
        <?php foreach (wr_legal_pages() as $slug => $label) : ?>
          <li><a href="<?php echo esc_url(wr_legal_url($slug)); ?>"<?php echo $slug === $current ? ' class="is-current" aria-current="page"' : ''; ?>><?php echo esc_html($label); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <?php if ($toc) : ?>
    <nav class="wr-legal__toc" aria-label="On this page">
      <p class="wr-legal__label">On this page</p>
      <ol>
        <?php foreach ($toc as [$id, $text]) : ?>
          <li><a href="#<?php echo esc_attr($id); ?>"><?php echo esc_html(preg_replace('#^\d+\.\s*#', '', $text)); ?></a></li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <?php endif; ?>
    <div class="wr-legal__help">
      <p class="wr-legal__label">Questions?</p>
      <p><a href="mailto:wrightapparel@hotmail.com">wrightapparel@hotmail.com</a><br><a href="tel:+17622489684">(762) 248-9684</a></p>
      <p class="wr-legal__hours">Mon&ndash;Fri, 9am&ndash;5pm Eastern</p>
    </div>
  </aside>
  <article class="wr-legal__body">
    <?php echo $legal_content; // Already passed through the_content. ?>
  </article>
</div>
    <?php else : ?>
<header class="wr-pagehead wr-container">
  <h1 class="wr-display wr-display--md"><?php the_title(); ?></h1>
</header>
<div class="wr-container">
  <article class="<?php echo $is_store ? 'wr-store-page' : 'wr-prose'; ?>">
    <?php the_content(); ?>
  </article>
</div>
    <?php endif;

endwhile;
get_footer();
