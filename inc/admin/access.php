<?php
/**
 * Limit access to certaing parts of dashboard.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:15:47
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-23 12:13:44
 *
 * @package air-helper
 */

/**
 * Clean up admin menu from stuff we usually don't need.
 *
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_helper_remove_admin_menu_links', 999 )`
 * Modify list by using `add_filter( 'air_helper_helper_remove_admin_menu_links', 'myprefix_override_air_helper_helper_remove_admin_menu_links' )`
 *
 * @since  0.1.0
 */
add_action( 'admin_init', 'air_helper_helper_remove_admin_menu_links' );
function air_helper_helper_remove_admin_menu_links() {
  $remove_items = apply_filters( 'air_helper_helper_remove_admin_menu_links', [
    'edit-comments.php',
    'themes.php?page=editcss',
    'admin.php?page=jetpack',
  ] );

  foreach ( $remove_items as $item ) {
    remove_menu_page( $item );
  }

  $remove_items = apply_filters( 'air_helper_helper_remove_admin_submenu_links', [
    'index.php' => [
      'update-core.php',
    ],
    'themes.php' => [
      'widgets.php',
    ],
    'options-general.php' => [
      'mailgun-lists'
    ]
  ] );

  foreach ( $remove_items as $parent => $items ) {
    foreach ( $items as $item ) {
      remove_submenu_page( $parent, $item );
    }
  }
} // end air_helper_helper_remove_admin_menu_links

/**
 *  Remove plugins page from admin menu, execpt for users with spesific domain or override in user meta.
 *
 *  Turn off by using `remove_filter( 'air_helper_helper_remove_admin_menu_links', 'air_helper_maybe_remove_plugins_from_admin_menu' )`
 *
 *  @since  1.3.0
 *  @param  array $menu_links pages to remove from admin menu.
 */
add_filter( 'air_helper_helper_remove_admin_menu_links', 'air_helper_maybe_remove_plugins_from_admin_menu' );
function air_helper_maybe_remove_plugins_from_admin_menu( $menu_links ) {
  $current_user = get_current_user_id();
  $user = new WP_User( $current_user );
  $domain = apply_filters( 'air_helper_dont_remove_plugins_admin_menu_link_from_domain', 'dude.fi' );
  $meta_override = get_user_meta( $user->ID, '_airhelper_admin_show_plugins', true );

  if ( 'true' === $meta_override ) {
    return $menu_links;
  }

  if ( strpos( $user->user_email, "@{$domain}" ) === false ) {
    $menu_links[] = 'plugins.php';
  }

  return $menu_links;
} // end air_helper_maybe_remove_plugins_from_admin_menu

/**
 * Remove plugins page from multisite admin menu, execpt for users with spesific domain or override in user meta.
 *
 * Turn off by using `remove_filter( 'admin_bar_menu', 'air_helper_maybe_remove_plugins_from_network_admin_menu', 999 )`
 *
 * @since 2.11.0
 */
add_action( 'admin_bar_menu', 'air_helper_maybe_remove_plugins_from_network_admin_menu', 999 );
function air_helper_maybe_remove_plugins_from_network_admin_menu() {
  $current_user = get_current_user_id();
  $user = new WP_User( $current_user );
  $domain = apply_filters( 'air_helper_dont_remove_plugins_admin_menu_link_from_domain', 'dude.fi' );
  $meta_override = get_user_meta( $user->ID, '_airhelper_admin_show_plugins', true );

  if ( strpos( $user->user_email, "@{$domain}" ) !== false ) {
    return;
  }

  if ( 'true' === $meta_override ) {
    return;
  }

  global $wp_admin_bar;
  $wp_admin_bar->remove_node( 'network-admin-p' );
} // end air_helper_maybe_remove_plugins_from_network_admin_menu
