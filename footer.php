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
            <li><a href="<?php echo esc_url(wr_privacy_url()); ?>">Privacy Policy</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="wr-footer__mark" aria-hidden="true">Wright Parel</div>
    <div class="wr-footer__trust">
      <address class="wr-footer__contact">
        <strong>Wright Apparel</strong>
        <span>485 Brantley Cove CIR, Grovetown, GA 30813, United States</span>
        <span><a href="mailto:wrightapparel@hotmail.com">wrightapparel@hotmail.com</a> &middot; <a href="tel:+17622489684">(762) 248-9684</a></span>
      </address>
      <?php $pay = wr_payment_methods(); ?>
      <div class="wr-footer__pay">
        <?php if ($pay['cards']) : ?>
          <p class="wr-footer__paylabel">Secure checkout. We accept</p>
          <ul class="wr-paybrands">
            <?php foreach (wr_card_brands() as $brand) : ?>
              <li><?php echo esc_html($brand); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <?php if ($pay['other']) : ?>
          <p class="wr-footer__paylabel"><?php echo $pay['cards'] ? 'Also available:' : 'Payment:'; ?> <?php echo esc_html(implode(', ', $pay['other'])); ?></p>
        <?php endif; ?>
        <?php if (!$pay['cards'] && !$pay['other']) : ?>
          <p class="wr-footer__paylabel">Secure checkout over an encrypted connection.</p>
        <?php endif; ?>
      </div>
    </div>
    <div class="wr-footer__base">
      <span>&copy; <?php echo esc_html(date('Y')); ?> Wright Apparel. All rights reserved.</span>
      <ul class="wr-footer__legal" aria-label="Legal">
        <?php foreach (wr_legal_pages() as $slug => $label) : ?>
          <li><a href="<?php echo esc_url(wr_legal_url($slug)); ?>"><?php echo esc_html($label); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
