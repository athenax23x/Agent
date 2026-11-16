<?php
/**
 * Moves the site from the theme folder "wrightparel." (stray period, from the original
 * Hostinger deploy directory) to "wrightparel".
 *
 * Runs only once the clean folder holds this same release or newer, so pointing the Git
 * deploy directory at it and deploying is the only manual step. Until then it does nothing.
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    if (get_stylesheet() !== 'wrightparel.') return;

    $target = wp_get_theme('wrightparel');
    if (!$target->exists() || $target->errors()) return;
    if (version_compare((string) $target->get('Version'), (string) wp_get_theme()->get('Version'), '<')) return;

    // Menus and customizer settings are stored per theme folder.
    $mods = get_option('theme_mods_wrightparel.');
    if ($mods && !get_option('theme_mods_wrightparel')) update_option('theme_mods_wrightparel', $mods);

    switch_theme('wrightparel');
}, 20);
