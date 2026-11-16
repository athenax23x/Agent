<?php
/**
 * One-time content seeding. Run: wp wrightparel seed
 * Safe to re-run: existing pages and products (by slug/SKU) are skipped.
 */
if (!defined('ABSPATH')) exit;

function wp_seed_page($title, $slug, $content) {
    $existing = get_page_by_path($slug);
    if ($existing) {
        // WordPress ships a draft "privacy-policy" page; publish it with our placeholder instead.
        if ($existing->post_status !== 'publish') wp_update_post(['ID' => $existing->ID, 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $content]);
        return $existing->ID;
    }
    return wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content]);
}

define('WR_SEED_VERSION', 3);

// Imports a bundled placeholder image once; later runs reuse the same attachment.
function wp_seed_image($file, $alt) {
    $map = get_option('wrightparel_seed_images', []);
    if (!empty($map[$file]) && get_post($map[$file])) return (int) $map[$file];
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $src = get_theme_file_path('assets/img/' . $file);
    if (!file_exists($src)) return 0;
    $tmp = wp_tempnam($src);
    copy($src, $tmp);
    $id = media_handle_sideload(['name' => 'wrightparel-' . $file, 'tmp_name' => $tmp], 0, $alt);
    if (is_wp_error($id)) return 0;
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    $map[$file] = $id;
    update_option('wrightparel_seed_images', $map);
    return $id;
}

function wp_seed_run() {
    $legal = '<p>Content to be added by site owner.</p>';

    $faq = [
        ['Do you make the clothes yourselves?', 'No. Wright Parel is a made-to-order label: we design and select the range, and our partner suppliers make and ship each garment to the specification and fabric we choose. See our <a href="/about-us/">About Us</a> page for how that works.'],
        ['How long does shipping take?', 'Orders are passed to our suppliers within 1 business day. They dispatch in 2 to 5 business days, and delivery takes a further 3 to 7 business days, so allow up to 14 business days in total.'],
        ['Why did my order arrive in more than one package?', 'Items can come from different suppliers, so they travel separately and may arrive on different days. You get a tracking number for each parcel and are not charged extra for a split delivery.'],
        ['Where do you ship from?', 'From our suppliers\' facilities, which are chosen for the item you order. Because of that, the packaging may not carry our branding.'],
        ['What is your return policy?', 'Unworn items can be returned within 30 days. See our Refund &amp; Returns page for details.'],
        ['How do I find my size?', 'Each product page lists measurements. If you are between sizes, we suggest sizing up.'],
        ['How should I care for my garments?', 'Wash cold, hang dry, and follow the care label on each piece.'],
        ['Can I change or cancel my order?', 'Contact us within 12 hours of ordering and we will do our best to help.'],
        ['Do you ship internationally?', 'We currently ship within the United States only. Write to us if you are outside the US and we will see what we can do.'],
    ];
    $faq_html = '<div class="faq">';
    foreach ($faq as $f) $faq_html .= '<details><summary>' . $f[0] . '</summary><p>' . $f[1] . '</p></details>';
    $faq_html .= '</div>';

    $contact = '<p>Questions about an order, sizing, fabric or anything else? Write to us, call, or use the form below and we will reply within two business days.</p>'
        . '<p><strong>Email</strong><br><a href="mailto:wrightapparel@hotmail.com">wrightapparel@hotmail.com</a></p>'
        . '<p><strong>Phone</strong><br><a href="tel:+17622489684">(762) 248-9684</a></p>'
        . '<p><strong>Opening hours</strong><br>Monday to Friday, 9am to 5pm Eastern Time</p>'
        . '<p><strong>Business address</strong><br>Wright Apparel (sole proprietorship)<br>485 Brantley Cove CIR<br>Grovetown, GA 30813<br>United States</p>'
        . '<p><strong>Website</strong><br>wrightparel.works</p>'
        . '<h2 id="wr-contact">Send a message</h2><div class="wr-contact-form">[wrightparel_contact_form]</div>';

    $about = '<p>Wright Parel is the clothing label of Wright Apparel, a small studio in Grovetown, Georgia. We work in small runs, choose fabrics for how they age, and keep our palette to black, white and gray so each piece works with the rest.</p>'
        . '<h2>Materials</h2><p>Heavyweight cotton, wool blends and washed linen, chosen for how they wear rather than how they photograph. We name the fabric and weight on every product page.</p>'
        . '<h2>Making</h2><p>Every garment is cut and sewn in limited batches, then checked by hand before it ships.</p>'
        . '<h2>Caring for your clothes</h2><p>Wash cold, inside out, and hang to dry where you can.</p>'
        . '<h2>Contact</h2><p>Write to <a href="mailto:wrightapparel@hotmail.com">wrightapparel@hotmail.com</a> or call (762) 248-9684.</p>';

    $ids = [];
    $ids['home'] = wp_seed_page('Home', 'home', '');
    $ids['about'] = wp_seed_page('About Us', 'about-us', $about);
    $ids['contact'] = wp_seed_page('Contact', 'contact', $contact);
    // v1 and v2 shipped a mailto: form; swap it for the real contact form.
    $contact_post = get_post($ids['contact']);
    if ($contact_post && strpos($contact_post->post_content, 'mailto:') !== false) {
        wp_update_post(['ID' => $ids['contact'], 'post_content' => $contact]);
    }
    $ids['faq'] = wp_seed_page('FAQ', 'faq', $faq_html);
    $ids['terms'] = wp_seed_page('Terms &amp; Conditions', 'terms-and-conditions', $legal);
    $ids['privacy'] = wp_seed_page('Privacy Policy', 'privacy-policy', $legal);
    $ids['shipping'] = wp_seed_page('Shipping Policy', 'shipping-policy', $legal);
    $ids['refund'] = wp_seed_page('Refund &amp; Returns', 'refund-and-returns', $legal);

    // Leave a front page the owner has changed since alone.
    $current_front = (int) get_option('page_on_front');
    if (!$current_front || !get_post($current_front) || get_post($current_front)->post_status !== 'publish') {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $ids['home']);
    }
    update_option('blogname', 'Wright Parel');
    update_option('blogdescription', 'Considered clothing, made to last.');
    update_option('permalink_structure', '/%postname%/');
    if (function_exists('wc_create_page') || class_exists('WooCommerce')) {
        update_option('woocommerce_terms_page_id', $ids['terms']);
        update_option('woocommerce_currency', 'USD');
        update_option('woocommerce_default_country', 'US');
        update_option('woocommerce_enable_guest_checkout', 'yes');
        update_option('woocommerce_enable_myaccount_registration', 'yes');
        // Keeps checkout usable until Stripe/PayPal is connected; disable it once a real gateway is live.
        $cod = get_option('woocommerce_cod_settings', []);
        if (empty($cod['enabled']) || $cod['enabled'] !== 'yes') {
            update_option('woocommerce_cod_settings', array_merge((array) $cod, [
                'enabled' => 'yes',
                'title' => 'Pay on delivery',
                'description' => 'Pay when your order arrives.',
            ]));
        }
    }
    update_option('wp_page_for_privacy_policy', $ids['privacy']);

    $cats = [];
    if (class_exists('WooCommerce')) {
        foreach (['Tops', 'Bottoms', 'Outerwear', 'Accessories'] as $c) {
            $t = term_exists($c, 'product_cat') ?: wp_insert_term($c, 'product_cat');
            $cats[$c] = is_array($t) ? (int) $t['term_id'] : (int) $t;
        }
        $products = [
            ['Heavyweight Tee', 'Tops', 48, 'A dense 240gsm cotton tee with a relaxed, boxy cut.', 'p-tee.jpg'],
            ['Oxford Shirt', 'Tops', 98, 'Washed cotton oxford with a soft collar and mother-of-pearl buttons.', 'p-oxford.jpg'],
            ['Merino Crewneck', 'Tops', 128, 'Fine-gauge merino knit, warm without bulk.', 'p-crewneck.jpg'],
            ['Long Sleeve Pocket Tee', 'Tops', 62, 'Midweight jersey with a single chest pocket.', 'p-pocket-tee.jpg'],
            ['Pleated Trouser', 'Bottoms', 138, 'Wool-blend trouser with a high rise and a wide leg.', 'p-trouser.jpg'],
            ['Straight Denim', 'Bottoms', 128, 'Black rigid denim in a clean straight fit.', 'p-denim.jpg'],
            ['Drawcord Short', 'Bottoms', 74, 'Lightweight cotton twill short with an elastic back waist.', 'p-short.jpg'],
            ['Wool Overcoat', 'Outerwear', 348, 'Double-faced wool coat, unlined, with hidden placket.', 'p-overcoat.jpg'],
            ['Quilted Liner Jacket', 'Outerwear', 188, 'Packable quilted layer for between seasons.', 'p-liner.jpg'],
            ['Work Jacket', 'Outerwear', 168, 'Cotton canvas chore jacket with three patch pockets.', 'p-work-jacket.jpg'],
            ['Canvas Tote', 'Accessories', 36, 'Heavy canvas tote with an internal pocket.', 'p-tote.jpg'],
            ['Wool Beanie', 'Accessories', 42, 'Ribbed merino beanie in a slim fit.', 'p-beanie.jpg'],
            ['Leather Belt', 'Accessories', 68, 'Vegetable-tanned leather with a matte steel buckle.', 'p-belt.jpg'],
            ['Cotton Socks, 3 Pack', 'Accessories', 28, 'Ribbed crew socks, three pairs.', 'p-socks.jpg'],
        ];
        $colors = ['Black', 'White', 'Gray'];
        $sizes = ['S', 'M', 'L', 'XL'];
        // One per category per row on the home page.
        $featured_skus = ['WP-001', 'WP-005', 'WP-008', 'WP-011', 'WP-002', 'WP-006', 'WP-010', 'WP-012'];
        foreach ($products as $i => $p) {
            $sku = 'WP-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $img = wp_seed_image($p[4], $p[0]);
            $is_featured = in_array($sku, $featured_skus, true);
            $existing_id = wc_get_product_id_by_sku($sku);
            if ($existing_id) {
                // Earlier versions used generic gray images; swap in the matching one.
                $ex = wc_get_product($existing_id);
                if ($ex) {
                    if ($img) $ex->set_image_id($img);
                    $ex->set_featured($is_featured);
                    $ex->set_menu_order(array_search($sku, $featured_skus, true) ?: 0);
                    $ex->save();
                }
                continue;
            }
            $wear = $p[1] !== 'Accessories';
            $prod = $wear ? new WC_Product_Variable() : new WC_Product_Simple();
            $prod->set_name($p[0]);
            $prod->set_sku($sku);
            $prod->set_short_description($p[3]);
            $prod->set_description($p[3] . ' Placeholder description: add fabric, fit and care details here.');
            $prod->set_category_ids([$cats[$p[1]]]);
            $prod->set_status('publish');
            $prod->set_catalog_visibility('visible');
            if ($img) $prod->set_image_id($img);
            $prod->set_featured($is_featured);
            $prod->set_menu_order(array_search($sku, $featured_skus, true) ?: 0);
            if (!$wear) { $prod->set_regular_price($p[2]); $prod->set_manage_stock(false); $prod->set_stock_status('instock'); }
            if ($wear) {
                $a1 = new WC_Product_Attribute(); $a1->set_name('Size'); $a1->set_options($sizes); $a1->set_visible(true); $a1->set_variation(true);
                $a2 = new WC_Product_Attribute(); $a2->set_name('Color'); $a2->set_options($colors); $a2->set_visible(true); $a2->set_variation(true);
                $prod->set_attributes([$a1, $a2]);
            }
            $pid = $prod->save();
            if ($wear) {
                foreach ($sizes as $s) foreach ($colors as $c) {
                    $v = new WC_Product_Variation();
                    $v->set_parent_id($pid);
                    $v->set_attributes(['size' => $s, 'color' => $c]);
                    $v->set_regular_price($p[2]);
                    if ($i === 1 || $i === 8) $v->set_sale_price(round($p[2] * 0.85));
                    $v->set_manage_stock(false);
                    $v->set_stock_status('instock');
                    $v->save();
                }
                WC_Product_Variable::sync($pid);
            }
        }
    }

    $primary = ['Shop' => function_exists('wc_get_page_id') && wc_get_page_id('shop') > 0 ? get_permalink(wc_get_page_id('shop')) : home_url('/shop/'),
        'About' => get_permalink($ids['about']), 'FAQ' => get_permalink($ids['faq']), 'Contact' => get_permalink($ids['contact'])];
    $footer = ['Terms &amp; Conditions' => $ids['terms'], 'Privacy Policy' => $ids['privacy'], 'Shipping Policy' => $ids['shipping'], 'Refund &amp; Returns' => $ids['refund']];

    $locations = get_theme_mod('nav_menu_locations', []);
    foreach (['Primary' => ['primary', $primary, false], 'Footer' => ['footer', $footer, true]] as $name => [$loc, $items, $isPage]) {
        $menu = wp_get_nav_menu_object($name);
        if ($menu) { foreach (wp_get_nav_menu_items($menu->term_id) ?: [] as $it) wp_delete_post($it->ID, true); $mid = $menu->term_id; }
        else $mid = wp_create_nav_menu($name);
        foreach ($items as $label => $val) {
            wp_update_nav_menu_item($mid, 0, [
                'menu-item-title' => wp_specialchars_decode($label), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom',
                'menu-item-url' => $isPage ? get_permalink($val) : $val,
            ]);
        }
        $locations[$loc] = $mid;
    }
    set_theme_mod('nav_menu_locations', $locations);
    flush_rewrite_rules();
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('wrightparel seed', function () {
        wp_seed_run();
        WP_CLI::success('Wright Parel content seeded.');
    });
}

// Dashboard one-click setup, so no SSH is needed.
add_action('admin_notices', function () {
    $done = (int) get_option('wrightparel_seeded');
    if (!current_user_can('manage_options') || $done >= WR_SEED_VERSION) return;
    if (!class_exists('WooCommerce')) {
        echo '<div class="notice notice-warning"><p><strong>Wright Parel:</strong> install and activate the WooCommerce plugin (Plugins &rarr; Add New), then come back here.</p></div>';
        return;
    }
    $url = wp_nonce_url(admin_url('admin-post.php?action=wrightparel_seed'), 'wrightparel_seed');
    $msg = $done ? 'apply the latest updates: product images, menus and the contact form.' : 'create the pages, placeholder products and menus.';
    $label = $done ? 'Update store' : 'Set up store';
    echo '<div class="notice notice-info"><p><strong>Wright Parel:</strong> ' . esc_html($msg) . ' <a class="button button-primary" href="' . esc_url($url) . '">' . esc_html($label) . '</a></p></div>';
});

add_action('admin_post_wrightparel_seed', function () {
    if (!current_user_can('manage_options')) wp_die('Not allowed.');
    check_admin_referer('wrightparel_seed');
    if (function_exists('set_time_limit')) set_time_limit(300);
    wp_seed_run();
    update_option('wrightparel_seeded', WR_SEED_VERSION);
    wp_safe_redirect(home_url('/'));
    exit;
});
