<?php

/**
 * Post Meta Management for DMG Anchor Link
 *
 * @package DMG_Anchor_Link
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Saves or removes post meta when the DMG Anchor Link block
 * is added to or removed from a post.
 *
 * Uses wp_after_insert_post hook for better performance.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated.
 */
function dmg_anchor_link_manage_post_meta($post_id, $post, $update)
{
	// Only process on actual updates, not initial creation
	if (!$update) {
		return;
	}

	// Skip autosaves and revisions
	if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
		return;
	}

	// Check if the block is present in the post content
	if (has_block('create-block/dmg-anchor-link', $post)) {
		update_post_meta($post_id, 'dmg-read-more', '1');
	} else {
		delete_post_meta($post_id, 'dmg-read-more');
	}
}
add_action('wp_after_insert_post', 'dmg_anchor_link_manage_post_meta', 10, 3);
