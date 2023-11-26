<?php

/**
 * 
 * This file contains custom snippets code for wordpress 
 * Author: Kareem Zock
 * Author URI: http://www.kareemzok.com

 */


/*
* Polylang custom language switcher
* 
* Create shortcode to add polylang plugin switcher via code
* The poly lang function used is pll_the_languages() and it take $args 
* as parameters
* Check https://polylang.pro/doc/function-reference/
* Feel free to customize the code to suits your needs
* 
* 
*/


function wpc_vc_shortcode_for_poly_language()
{
	$output = '';
	$args   = [
		'show_flags' => 1,
		'show_names' => 1,
		'display_names_as' => 'name',
		'hide_current' => 1,
		'hide_if_no_translation' => 0,
		'echo'       => 0, // if this enabled the switcher will be displayed twice
	];
	$output = '<ul class="polylang_langswitcher">' . pll_the_languages($args) . '</ul>';


	return $output;
}


add_shortcode('custom-poly-lang-switcher', 'wpc_vc_shortcode_for_poly_language');

/*
* Custom breadcrumb
* 
* Create shortcode to add custom breadcrumb into your website
* Feel free to customize the code to suits your needs
*
*/

function custom_breadcrumb()
{
	global $post;
	global $wp_query;//this is used based on our case you might use $post isntead
	$seperator = " / ";

	$home_link = '<a href="' . get_home_url() . '">' . __('Home') . '</a>';


	if (is_front_page()) { // front page
		return $home_link;
	} elseif (is_category()) {
		$category = get_the_category();
		$category_link = '<a href="' . get_category_link($category[0]->term_id) . '">' . $category[0]->name . '</a>';

		return $home_link . $seperator . $category_link;
	} elseif (is_single()) { // Determines whether the query is for an existing single post
		$post_type = get_post_type();
		$archive_link = '<a href="' . get_post_type_archive_link($post_type) . '">' . $post_type . 's</a>';

		$breadcrumb = $home_link . $seperator . $archive_link . $seperator;

		if ($post->post_parent) {
			$parent = get_post($post->post_parent);
			$parent_link = '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';

			$breadcrumb .= $parent_link . $seperator;
		}

		$breadcrumb .= '<span>' . the_title('', '', false) . '</span>';

		return $breadcrumb;
	} elseif (is_page()) { //Determines whether the query is for an existing single page.

		$ancestors = get_post_ancestors($wp_query->post->ID); // Retrieves the IDs of the ancestors of a post.
		$breadcrumb = $home_link;

		foreach ($ancestors as $ancestor) {
			$ancestor_link = '<a href="' . get_permalink($ancestor) . '">' . get_the_title($ancestor) . '</a>';
			$breadcrumb .= $seperator . $ancestor_link;
		}

		$breadcrumb .= $seperator . '<span>' . get_the_title($wp_query->post->ID) . '<span>';

		return $breadcrumb;
	} else {
		return $home_link;
	}
}


add_shortcode('kareem-custom-breadcrumb', 'custom_breadcrumb');
