<?php

/**
 * WP-CLI Commands for DMG Anchor Link
 *
 * @package DMG_Anchor_Link
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
	return;
}

/**
 * Search for posts or pages with DMG Anchor Link block within a date range.
 *
 * @param array $args       Positional arguments.
 * @param array $assoc_args Associative arguments.
 */
function dmg_read_more_search_command($args, $assoc_args)
{
	// Parse and validate date parameters
	$date_before = isset($assoc_args['date-before']) ? date_create_from_format('d-m-Y', $assoc_args['date-before']) : false;
	$date_after  = isset($assoc_args['date-after']) ? date_create_from_format('d-m-Y', $assoc_args['date-after']) : false;

	// Default to last 30 days if dates are not provided
	if (!$date_before && !$date_after) {
		$date_before = date_create(); // Current date/time
		$date_before->setTime(23, 59, 59); // End of current day
		$date_after  = clone $date_before;
		$date_after->modify('-30 days'); // Start of 30 days ago
		$date_after->setTime(0, 0, 0); // Start of the day
	} elseif (!$date_before) {
		$date_before = date_create(); // Current date/time
		$date_before->setTime(23, 59, 59); // End of current day
	} elseif (!$date_after) {
		$date_after = clone $date_before;
		$date_after->modify('-30 days'); // Start of 30 days ago
		$date_after->setTime(0, 0, 0); // Start of the day
	}

	// Determine post types to query
	$post_types = array('post', 'page'); // Default to both 'post' and 'page'
	if (isset($assoc_args['post-type'])) {
		$post_types = explode(',', $assoc_args['post-type']);
	}

	// Prepare WP_Query arguments
	$query_args = array(
		'post_type'      => $post_types,
		'posts_per_page' => -1, // Get all posts matching criteria
		'meta_query'     => array(
			array(
				'key'     => 'dmg-read-more',
				'value'   => '1',
				'compare' => '=',
			),
		),
		'date_query'     => array(
			'after'     => $date_after->format('Y-m-d H:i:s'),
			'before'    => $date_before->format('Y-m-d H:i:s'),
			'inclusive' => true,
		),
	);

	// Perform the query
	$posts_query = new WP_Query($query_args);

	// Check if any posts were found
	if ($posts_query->have_posts()) {
		$post_ids = array();
		while ($posts_query->have_posts()) {
			$posts_query->the_post();
			$post_ids[] = get_the_ID();
		}
		WP_CLI::line(implode(', ', $post_ids));
		WP_CLI::success('Found ' . $posts_query->found_posts . ' posts/pages with DMG Anchor Link Block within the specified date range.');
	} else {
		WP_CLI::line('No posts/pages found with DMG Anchor Link Block within the specified date range.');
	}

	// Restore global post data
	wp_reset_postdata();
}

WP_CLI::add_command('dmg-read-more-search', 'dmg_read_more_search_command', array(
	'shortdesc' => 'Searches for posts or pages with meta dmg-read-more = 1 within a specified date range.',
	'synopsis'  => array(
		array(
			'name'        => 'date-before',
			'type'        => 'assoc',
			'description' => 'Date before (format: dd-mm-yyyy). Defaults to current date.',
			'optional'    => true,
		),
		array(
			'name'        => 'date-after',
			'type'        => 'assoc',
			'description' => 'Date after (format: dd-mm-yyyy). Defaults to 30 days ago.',
			'optional'    => true,
		),
		array(
			'name'        => 'post-type',
			'type'        => 'assoc',
			'description' => 'Post types to search (comma-separated). Defaults to "post,page".',
			'optional'    => true,
		),
	),
));
