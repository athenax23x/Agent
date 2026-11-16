<?php
define('WR_VERSION', '4.1.0');

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('woocommerce', [
        'thumbnail_image_width' => 600,
        'single_image_width' => 900,
        'product_grid' => ['default_columns' => 4, 'default_rows' => 3],
    ]);
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    register_nav_menus(['primary' => 'Primary', 'footer' => 'Footer']);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('wrightparel', get_stylesheet_uri(), [], WR_VERSION);
    wp_enqueue_script('wrightparel', get_theme_file_uri('assets/js/site.js'), [], WR_VERSION, ['strategy' => 'defer', 'in_footer' => true]);
    wp_localize_script('wrightparel', 'wrData', [
        'cartUrl'  => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('cart') : home_url('/cart/'),
        'addToCart' => class_exists('WC_AJAX') ? WC_AJAX::get_endpoint('add_to_cart') : home_url('/?wc-ajax=add_to_cart'),
        'strings'  => [
            'adding' => 'Adding…',
            'added'   => 'Added to cart',
            'failed'  => 'Sorry, that could not be added.',
            'viewCart' => 'View cart',
        ],
    ]);
});

add_action('wp_head', function () {
    foreach (['inter-latin-wght-normal', 'instrument-serif-latin-400-normal'] as $f) {
        echo '<link rel="preload" href="' . esc_url(get_theme_file_uri("assets/fonts/$f.woff2")) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
}, 1);

// Theme CSS replaces WooCommerce's general styles; keep its layout + block styles.
add_filter('woocommerce_enqueue_styles', function ($styles) {
    unset($styles['woocommerce-general'], $styles['woocommerce-smallscreen']);
    return $styles;
});

add_filter('woocommerce_placeholder_img_src', fn() => wr_asset_url('p-tee.jpg'));

/* Helpers */
/**
 * Theme image URL with a version query string.
 *
 * Hostinger's CDN caches by full URL, so replacing an image's contents under the same
 * filename keeps serving the old file. Versioning the URL makes a new file a new resource.
 */
function wr_asset_url($file) {
    return add_query_arg('ver', WR_VERSION, get_theme_file_uri('assets/img/' . $file));
}

function wr_page_url($slug) {
    $p = get_page_by_path($slug);
    return $p ? get_permalink($p) : home_url('/' . $slug . '/');
}
function wr_wc_url($page) {
    if (function_exists('wc_get_page_id') && wc_get_page_id($page) > 0) return get_permalink(wc_get_page_id($page));
    return home_url('/' . $page . '/');
}
function wr_cat_url($slug) {
    $t = get_term_by('slug', $slug, 'product_cat');
    return $t ? get_term_link($t) : home_url('/product-category/' . $slug . '/');
}
/**
 * Privacy policy link. Prefers the page designated in Settings → Privacy, but falls back to
 * the page with the real policy when the designated one is still the empty placeholder that
 * WordPress ships (Shop Managers cannot edit the designated page).
 */
function wr_privacy_url() {
    $id = (int) get_option('wp_page_for_privacy_policy');
    if ($id && get_post_status($id) === 'publish') {
        $text = trim(wp_strip_all_tags((string) get_post_field('post_content', $id)));
        if (strlen($text) > 80) return get_permalink($id);
    }
    return wr_page_url('privacy');
}
function wr_cart_count() {
    return (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
}
function wr_categories() {
    return ['tops' => 'Tops', 'bottoms' => 'Bottoms', 'outerwear' => 'Outerwear', 'accessories' => 'Accessories'];
}

// Live cart count in the header after AJAX add-to-cart.
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $fragments['span.wr-cart-count'] = '<span class="wr-cart-count">' . wr_cart_count() . '</span>';
    return $fragments;
});

/* WooCommerce layout */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
add_action('woocommerce_before_main_content', fn() => print('<div class="wr-container wr-shop">'), 10);
add_action('woocommerce_after_main_content', fn() => print('</div>'), 10);

add_filter('woocommerce_breadcrumb_defaults', function ($d) {
    $d['delimiter'] = '<span class="sep">/</span>';
    $d['wrap_before'] = '<nav class="woocommerce-breadcrumb wr-crumbs" aria-label="Breadcrumb">';
    return $d;
});

// Category filter row on shop/category archives.
add_action('woocommerce_before_shop_loop', function () {
    $current = is_product_category() ? get_queried_object()->slug : '';
    echo '<nav class="wr-filters" aria-label="Categories"><a href="' . esc_url(wr_wc_url('shop')) . '"' . ($current === '' ? ' class="is-active"' : '') . '>All</a>';
    foreach (wr_categories() as $slug => $name) {
        echo '<a href="' . esc_url(wr_cat_url($slug)) . '"' . ($current === $slug ? ' class="is-active"' : '') . '>' . esc_html($name) . '</a>';
    }
    echo '</nav>';
}, 5);

// Cleaner product cards: image, category, title, price. No loop buttons.
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
add_action('woocommerce_shop_loop_item_title', function () {
    global $product;
    $terms = get_the_terms($product->get_id(), 'product_cat');
    if ($terms && !is_wp_error($terms)) echo '<span class="wr-card-cat">' . esc_html($terms[0]->name) . '</span>';
}, 5);

add_filter('woocommerce_output_related_products_args', function ($a) {
    $a['posts_per_page'] = 4;
    $a['columns'] = 4;
    return $a;
});
add_filter('loop_shop_per_page', fn() => 12);
add_filter('woocommerce_sale_flash', fn() => '<span class="onsale">Sale</span>');

// Trust notes under the add-to-cart button.
add_action('woocommerce_single_product_summary', function () {
    echo '<ul class="wr-assurances"><li>Free shipping on orders over $150</li><li>Free returns within 30 days</li><li>Dispatched in 2&ndash;5 business days</li></ul>';
}, 35);

require_once __DIR__ . '/inc/seed-content.php';
require_once __DIR__ . '/inc/storefront.php';
require_once __DIR__ . '/inc/contact-form.php';
require_once __DIR__ . '/inc/seo.php';
require_once __DIR__ . '/inc/legal.php';
require_once __DIR__ . '/inc/theme-folder.php';
