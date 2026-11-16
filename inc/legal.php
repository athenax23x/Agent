<?php
/**
 * Legal pages: layout helpers, live shipping-rate shortcodes, payment note, privacy page.
 */
if (!defined('ABSPATH')) exit;

/** Policy pages in display order: slug => label. */
function wr_legal_pages() {
    return [
        'terms-and-conditions' => 'Terms & Conditions',
        'privacy'              => 'Privacy Policy',
        'shipping-policy'      => 'Shipping Policy',
        'refund-and-returns'   => 'Refund & Returns',
    ];
}

function wr_legal_slug($post = null) {
    $post = get_post($post);
    if (!$post || $post->post_type !== 'page') return '';
    $slug = $post->post_name;
    if ($slug === 'privacy-policy') $slug = 'privacy';
    return isset(wr_legal_pages()[$slug]) ? $slug : '';
}

function wr_is_legal_page($post = null) {
    return wr_legal_slug($post) !== '';
}

function wr_legal_url($slug) {
    return $slug === 'privacy' ? wr_privacy_url() : wr_page_url($slug);
}

/**
 * Adds ids to the h2 headings and returns [content, toc], where toc is a list of
 * [id, text] pairs. The "Last updated" line is dropped because the layout shows the
 * page's own modified date instead.
 */
function wr_legal_prepare($content) {
    $content = preg_replace('#^\s*<p>\s*<em>\s*Last updated[^<]*</em>\s*</p>\s*#i', '', $content);
    $toc = [];
    $used = [];
    $content = preg_replace_callback('#<h2([^>]*)>(.*?)</h2>#is', function ($m) use (&$toc, &$used) {
        $text = trim(wp_strip_all_tags($m[2]));
        if ($text === '') return $m[0];
        if (preg_match('#\sid=["\']([^"\']+)["\']#', $m[1], $idm)) {
            $id = $idm[1];
            $attrs = $m[1];
        } else {
            $base = sanitize_title(preg_replace('#^\d+\.\s*#', '', $text)) ?: 'section';
            $id = $base;
            $n = 2;
            while (isset($used[$id])) $id = $base . '-' . $n++;
            $attrs = $m[1] . ' id="' . esc_attr($id) . '"';
        }
        $used[$id] = true;
        $toc[] = [$id, $text];
        return '<h2' . $attrs . '>' . $m[2] . '</h2>';
    }, $content);
    return [$content, $toc];
}

/** WordPress's placeholder privacy page cannot be edited by the store account; send visitors to the real policy. */
add_action('template_redirect', function () {
    if (!is_page('privacy-policy')) return;
    $real = get_page_by_path('privacy');
    if ($real && $real->post_status === 'publish') {
        wp_safe_redirect(get_permalink($real), 301);
        exit;
    }
});

/**
 * Behind the scenes WordPress designates one page as the privacy policy, and shop managers
 * cannot edit it. The published policy lives on /privacy/, so point the front end
 * (footer, checkout notice, login forms) there. Admin and REST are left alone so the
 * page that is actually designated stays editable where it should be.
 */
add_filter('option_wp_page_for_privacy_policy', function ($value) {
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) return $value;
    static $resolved = null;
    if ($resolved === null) {
        $resolved = 0;
        $page = get_page_by_path('privacy');
        if ($page && $page->post_status === 'publish') $resolved = (int) $page->ID;
    }
    return $resolved ?: $value;
});

/* ---------- Shipping rates, read from WooCommerce so policy and checkout cannot drift ---------- */

function wr_shipping_methods() {
    if (!class_exists('WC_Shipping_Zone')) return [];
    $zone_ids = [0];
    if (class_exists('WC_Shipping_Zones')) {
        foreach (WC_Shipping_Zones::get_zones() as $zone) $zone_ids[] = (int) $zone['zone_id'];
    }
    $out = [];
    foreach (array_unique($zone_ids) as $zone_id) {
        $zone = new WC_Shipping_Zone($zone_id);
        foreach ($zone->get_shipping_methods(true) as $method) {
            if ($method->id === 'local_pickup') continue;
            $out[] = [
                'type'  => $method->id,
                'title' => $method->get_title(),
                'cost'  => $method->get_option('cost'),
                'min'   => $method->get_option('min_amount'),
                'req'   => $method->get_option('requires'),
            ];
        }
    }
    return $out;
}

function wr_shipping_summary() {
    $methods = wr_shipping_methods();
    $free = $flat = null;
    foreach ($methods as $m) {
        if ($m['type'] === 'free_shipping' && !$free) $free = $m;
        if ($m['type'] === 'flat_rate' && !$flat) $flat = $m;
    }
    $parts = [];
    $free_min = $free && in_array($free['req'], ['min_amount', 'either', 'both'], true) && is_numeric($free['min']) ? (float) $free['min'] : null;
    if ($free && $free_min === null) return 'Free on all orders';
    if ($flat && is_numeric($flat['cost'])) {
        $parts[] = $free_min !== null
            ? 'free on orders of ' . wp_strip_all_tags(wc_price($free_min, ['decimals' => 0])) . ' or more, otherwise ' . wp_strip_all_tags(wc_price((float) $flat['cost']))
            : wp_strip_all_tags(wc_price((float) $flat['cost'])) . ' flat rate';
    } elseif ($free_min !== null) {
        $parts[] = 'free on orders of ' . wp_strip_all_tags(wc_price($free_min, ['decimals' => 0])) . ' or more';
    }
    return $parts ? ucfirst(implode('; ', $parts)) : 'Calculated at checkout';
}

add_shortcode('wr_shipping_rates', function ($atts) {
    if (!function_exists('wc_price')) return '';
    $atts = shortcode_atts(['format' => 'table'], $atts);
    if ($atts['format'] === 'inline') return esc_html(wr_shipping_summary());

    $methods = wr_shipping_methods();
    if (!$methods) return '<p>Shipping rates are shown at checkout before you pay.</p>';

    $free_min = null;
    foreach ($methods as $m) {
        if ($m['type'] === 'free_shipping' && in_array($m['req'], ['min_amount', 'either', 'both'], true) && is_numeric($m['min'])) {
            $free_min = (float) $m['min'];
        }
    }
    $rows = '';
    foreach ($methods as $m) {
        if ($m['type'] === 'free_shipping') {
            $when = $free_min !== null ? 'Orders of ' . wp_strip_all_tags(wc_price($free_min, ['decimals' => 0])) . ' or more' : 'All orders';
            $cost = 'Free';
        } elseif ($m['type'] === 'flat_rate') {
            $when = $free_min !== null ? 'Orders under ' . wp_strip_all_tags(wc_price($free_min, ['decimals' => 0])) : 'All orders';
            $cost = is_numeric($m['cost']) ? wp_strip_all_tags(wc_price((float) $m['cost'])) : 'Calculated at checkout';
        } else {
            $when = 'See checkout';
            $cost = 'Calculated at checkout';
        }
        $rows .= '<tr><th scope="row">' . esc_html($m['title']) . '</th><td>' . esc_html($when) . '</td><td>' . esc_html($cost) . '</td></tr>';
    }
    return '<div class="wr-table-wrap"><table class="wr-size-table"><thead><tr><th>Method</th><th>Applies to</th><th>Cost</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
});

/** Once a free-shipping rate applies, offer only that, which is what the policy promises. */
add_filter('woocommerce_package_rates', function ($rates) {
    $free = [];
    foreach ($rates as $id => $rate) {
        if ($rate->method_id === 'free_shipping') $free[$id] = $rate;
    }
    if (!$free) return $rates;
    foreach ($rates as $id => $rate) {
        if ($rate->method_id === 'local_pickup') $free[$id] = $rate;
    }
    return $free;
}, 100);

/* ---------- Payment note shown in the footer ---------- */

/** Gateway ids that take card payments. Extend with the wr_card_gateway_ids filter. */
function wr_card_gateway_ids() {
    return apply_filters('wr_card_gateway_ids', [
        'stripe', 'stripe_cc', 'ppcp-gateway', 'ppcp-credit-card-gateway', 'woocommerce_payments',
        'square_credit_card', 'authorize_net_cim_credit_card', 'braintree_credit_card',
        'paypal', 'payfast', 'mollie_wc_gateway_creditcard',
    ]);
}

/** Card brands to name. Edit through the wr_card_brands filter once you know which you take. */
function wr_card_brands() {
    return apply_filters('wr_card_brands', ['Visa', 'Mastercard', 'American Express', 'Discover']);
}

/**
 * Describes what checkout actually offers right now, so the footer never claims a payment
 * method that is not switched on. Returns ['cards' => bool, 'other' => string[]].
 */
function wr_payment_methods() {
    $info = ['cards' => false, 'other' => []];
    if (!function_exists('WC') || !WC()->payment_gateways()) return $info;
    $card_ids = wr_card_gateway_ids();
    foreach (WC()->payment_gateways()->payment_gateways() as $gateway) {
        if ($gateway->enabled !== 'yes') continue;
        if (in_array($gateway->id, $card_ids, true)) {
            $info['cards'] = true;
        } else {
            $title = trim(wp_strip_all_tags($gateway->get_title()));
            if ($title !== '') $info['other'][] = $title;
        }
    }
    return $info;
}
