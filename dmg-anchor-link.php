<?php

/**
 * Plugin Name:       DMG Anchor Link
 * Description:       Add stylized anchor links to your WordPress content.
 * Version:           0.1.0
 * Author:            Magnus J. Orr
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dmg-anchor-link
 *
 * @package DMG_Anchor_Link
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function dmg_anchor_link_block_init()
{
	register_block_type(__DIR__ . '/build');
}
add_action('init', 'dmg_anchor_link_block_init');

/**
 * Load post meta management
 */
require_once __DIR__ . '/includes/post-meta.php';

/**
 * Load WP-CLI commands
 */
require_once __DIR__ . '/includes/cli-commands.php';
