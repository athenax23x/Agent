<?php
/**
 * Titles, meta description, sharing tags, structured data, favicon fallback.
 * WooCommerce already outputs Product JSON-LD, so this adds the page-level basics.
 */
if (!defined('ABSPATH')) exit;

add_filter('pre_get_document_title', function ($title) {
    if (is_front_page()) {
        return 'Wright Parel — Considered clothing, made to last';
    }
    return $title;
});

function wr_meta_description() {
    $desc = '';
    $fallback = 'Considered clothing in black, white and gray. Made in small runs, built to last.';
    if (is_singular('product') && function_exists('wc_get_product')) {
        $product = wc_get_product(get_the_ID());
        if ($product) {
            $desc = $product->get_short_description() ?: $product->get_description();
            if ($product->get_price()) $desc = wc_price($product->get_price()) . '. ' . $desc;
        }
    } elseif (is_singular()) {
        $post = get_post();
        $desc = $post->post_excerpt ?: $post->post_content;
    } elseif (is_tax() || is_category() || is_tag()) {
        $desc = term_description();
    } elseif (is_front_page()) {
        $desc = get_bloginfo('description') ?: $fallback;
    } elseif (function_exists('is_shop') && is_shop()) {
        $desc = get_bloginfo('description') ?: $fallback;
    }
    if (!$desc && (is_front_page() || (function_exists('is_shop') && is_shop()))) $desc = $fallback;
    if (!$desc) $desc = $fallback;
    $desc = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $desc)));
    return $desc ? wp_html_excerpt($desc, 155, '…') : '';
}

add_action('wp_head', function () {
    $desc = wr_meta_description();
    $url  = is_front_page() ? home_url('/') : (is_singular() ? get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request ?? '')));
    $title = wp_get_document_title();

    if ($desc) {
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    }
    if (!is_singular()) {
        echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    }

    $image = '';
    if (is_singular('product') && function_exists('wc_get_product')) {
        $product = wc_get_product(get_the_ID());
        if ($product && $product->get_image_id()) $image = wp_get_attachment_image_url($product->get_image_id(), 'large');
    }
    if (!$image) $image = wr_asset_url('editorial.jpg');

    printf('<meta property="og:site_name" content="%s">' . "\n", esc_attr(get_bloginfo('name')));
    printf('<meta property="og:type" content="%s">' . "\n", is_singular('product') ? 'product' : 'website');
    printf('<meta property="og:title" content="%s">' . "\n", esc_attr($title));
    if ($desc) printf('<meta property="og:description" content="%s">' . "\n", esc_attr($desc));
    printf('<meta property="og:url" content="%s">' . "\n", esc_url($url));
    printf('<meta property="og:image" content="%s">' . "\n", esc_url($image));
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 5);

add_action('wp_head', function () {
    if (has_site_icon()) return;
    printf('<link rel="icon" href="%s" sizes="any">' . "\n", esc_url(wr_asset_url('favicon-48.png')));
    printf('<link rel="apple-touch-icon" href="%s">' . "\n", esc_url(wr_asset_url('favicon-192.png')));
}, 6);

add_action('wp_head', function () {
    if (!is_front_page()) return;
    $data = [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type' => 'Organization',
                'name'  => get_bloginfo('name'),
                'url'   => home_url('/'),
                'logo'  => wr_asset_url('favicon-192.png'),
            ],
            [
                '@type'           => 'WebSite',
                'name'            => get_bloginfo('name'),
                'url'             => home_url('/'),
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => home_url('/?s={search_term_string}&post_type=product'),
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 7);
