<?php
/**
 * Shop-grid quick add, size guide, empty-cart and 404 helpers.
 */
if (!defined('ABSPATH')) exit;

/**
 * Size guide, shown under the add-to-cart form. Placeholder measurements.
 */
function wr_size_guide() {
    $order = ['XS', 'S', 'M', 'L', 'XL'];
    $rows  = [
        'XS' => [86, 71, 66],
        'S'  => [91, 76, 69],
        'M'  => [97, 81, 71],
        'L'  => [102, 86, 74],
        'XL' => [107, 91, 76],
    ];
    echo '<details class="wr-sizeguide"><summary>Size guide</summary>';
    echo '<table><caption>Measurements in centimetres, garment laid flat. Placeholder figures: replace with your own.</caption>';
    echo '<thead><tr><th>Size</th><th>Chest</th><th>Waist</th><th>Length</th></tr></thead><tbody>';
    foreach ($order as $size) {
        printf('<tr><th scope="row">%s</th><td>%d</td><td>%d</td><td>%d</td></tr>', esc_html($size), $rows[$size][0], $rows[$size][1], $rows[$size][2]);
    }
    echo '</tbody></table><p class="wr-sizeguide__note">Between two sizes? We suggest the larger one. Write to us if you would like a second opinion.</p></details>';
}
add_action('woocommerce_single_product_summary', 'wr_size_guide', 32);

/**
 * Hover panel on shop cards: add a simple product, or pick a size for a variable one.
 */
function wr_quick_add() {
    global $product;
    if (!$product || !is_a($product, 'WC_Product') || !$product->is_purchasable() || !$product->is_in_stock()) return;

    $id = $product->get_id();

    if ($product->is_type('simple')) {
        printf(
            '<div class="wr-quick" data-name="%s"><button type="button" class="wr-quick__btn wr-quick__add" data-add-id="%d">Add to cart</button></div>',
            $id,
            esc_attr($product->get_name())
        );
        return;
    }
    if (!$product->is_type('variable')) return;

    $variation_attributes = $product->get_variation_attributes();
    $size_key = $color_key = '';
    foreach (array_keys($variation_attributes) as $name) {
        if ($size_key === '' && stripos($name, 'size') !== false) $size_key = $name;
        elseif ($color_key === '' && stripos($name, 'colo') !== false) $color_key = $name;
    }
    if ($size_key === '') return;

    $size_lookup = 'attribute_' . sanitize_title($size_key);
    $color_lookup = $color_key !== '' ? 'attribute_' . sanitize_title($color_key) : '';

    $by_size = [];
    foreach ($product->get_available_variations() as $variation) {
        $attrs = isset($variation['attributes']) ? $variation['attributes'] : [];
        $size  = isset($attrs[$size_lookup]) ? $attrs[$size_lookup] : '';
        if ($size === '' || isset($by_size[$size])) continue;
        $color = $color_lookup !== '' && isset($attrs[$color_lookup]) ? $attrs[$color_lookup] : '';
        $by_size[$size] = ['id' => $variation['variation_id'], 'color' => $color, 'attrs' => $attrs];
    }
    if (!$by_size) return;

    $options = isset($variation_attributes[$size_key]) ? $variation_attributes[$size_key] : array_keys($by_size);
    printf('<div class="wr-quick" data-name="%s"><span class="wr-quick__title">Quick add</span><div class="wr-quick__sizes">', esc_attr($product->get_name()));
    foreach ($options as $option) {
        if (!isset($by_size[$option])) continue;
        $label = $by_size[$option]['color'] !== '' ? $option . ' / ' . $by_size[$option]['color'] : $option;
        printf(
            '<button type="button" class="wr-quick__btn" data-add-id="%d" data-label="%s" aria-label="Add size %s to cart">%s</button>',
            $by_size[$option]['id'],
            esc_attr($label),
            esc_attr($label),
            esc_html($option)
        );
    }
    echo '</div></div>';
}
add_action('woocommerce_after_shop_loop_item', 'wr_quick_add', 10);

/**
 * Empty cart: say what to do next instead of a dead end.
 */
add_action('woocommerce_cart_is_empty', function () {
    echo '<div class="wr-empty"><p>Your cart is empty.</p><a class="wr-btn wr-btn--ghost" href="' . esc_url(wc_get_page_permalink('shop')) . '">Browse the collection</a></div>';
    echo '<h2 class="wr-empty__title">Start with these</h2>';
    echo do_shortcode('[products limit="4" columns="4" orderby="menu_order" order="ASC" visibility="featured"]');
}, 20);

/**
 * "No products found" state for empty archives and searches.
 */
add_action('woocommerce_no_products_found', function () {
    echo '<div class="wr-empty"><p>Nothing matched. Try a different search or browse the full collection.</p>'
       . '<a class="wr-btn wr-btn--ghost" href="' . esc_url(wc_get_page_permalink('shop')) . '">All products</a></div>';
}, 5);

/**
 * Products to suggest on a 404 page.
 */
function wr_suggest_products() {
    if (function_exists('wc_get_products')) {
        echo do_shortcode('[products limit="4" columns="4" orderby="menu_order" order="ASC" visibility="featured"]');
    }
}
