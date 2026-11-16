</main>
<footer class="wr-footer">
  <div class="wr-container">
    <div class="wr-footer__top">
      <div class="wr-footer__signup">
        <p class="wr-footer__lede">Letters from the studio. New pieces, restocks, nothing else.</p>
        <form class="wr-signup" action="<?php echo esc_url(wr_page_url('contact')); ?>" method="get">
          <label class="screen-reader-text" for="wr-email">Email address</label>
          <input id="wr-email" type="email" name="email" placeholder="Email address" required>
          <button type="submit">Subscribe</button>
        </form>
      </div>
      <div class="wr-footer__cols">
        <div>
          <h4>Shop</h4>
          <ul>
            <li><a href="<?php echo esc_url(wr_wc_url('shop')); ?>">All products</a></li>
            <?php foreach (wr_categories() as $slug => $name) : ?>
              <li><a href="<?php echo esc_url(wr_cat_url($slug)); ?>"><?php echo esc_html($name); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div>
          <h4>Help</h4>
          <ul>
            <li><a href="<?php echo esc_url(wr_page_url('faq')); ?>">FAQ</a></li>
            <li><a href="<?php echo esc_url(wr_page_url('shipping-policy')); ?>">Shipping</a></li>
            <li><a href="<?php echo esc_url(wr_page_url('refund-and-returns')); ?>">Refund &amp; Returns</a></li>
            <li><a href="<?php echo esc_url(wr_page_url('contact')); ?>">Contact</a></li>
            <li><a href="<?php echo esc_url(wr_wc_url('myaccount')); ?>">My Account</a></li>
          </ul>
        </div>
        <div>
          <h4>Company</h4>
          <ul>
            <li><a href="<?php echo esc_url(wr_page_url('about-us')); ?>">About Us</a></li>
            <li><a href="<?php echo esc_url(wr_page_url('terms-and-conditions')); ?>">Terms &amp; Conditions</a></li>
            <li><a href="<?php echo esc_url(wr_page_url('privacy-policy')); ?>">Privacy Policy</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="wr-footer__mark" aria-hidden="true">Wright Parel</div>
    <div class="wr-footer__base">
      <span>&copy; <?php echo esc_html(date('Y')); ?> Wright Parel</span>
      <span>wrightparel.works</span>
    </div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
