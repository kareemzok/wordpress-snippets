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
	global $wp_query; //this is used based on our case you might use $post isntead
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


/*
*
*Below are snippet for woo commerce plugins 
*
*
*/

/*
* Show msg based on billing and shipping countries instead or with order button
* Feel free to customize the code to suits your needs
*/

add_filter('woocommerce_order_button_html', '_woo_hide_place_order_button');

function _woo_hide_place_order_button($button_html)
{

	$button_html = '
		<button type="submit" class="button alt wp-element-button" name="woocommerce_checkout_place_order" id="place_order" value="Place order" data-value="Place order">Place order</button>
		';

	if ((WC()->customer->billing_country == 'LB' &&  WC()->customer->shipping_country == 'LB') || WC()->customer->shipping_country == 'LB') {
		return $button_html;
	} else {

		$button_html = '<div class="shipping-notice woocommerce-info" >any message can be displayed insead of the place button</div>';
	}
	return $button_html;
}

/**
 * Remove tabs from you ecommerce  (in our case seller tab)
 * Feel free to customize the code to suits your needs
 */
add_filter('woocommerce_product_tabs', '_woo_remove_product_tabs', 98);

function _woo_remove_product_tabs($tabs)
{

	unset($tabs['seller']);      	// Remove the seller tab

	return $tabs;
}

/**
 * Function for `woocommerce_created_customer` action-hook.
 * Process logic code after user creation
 * below example of adding a record in a custom table for the user 

 */
function _wooc_user_creation($customer_id)
{

	$data = array(
		'user_id' => $customer_id,
		'email' => $email,
		'custom_phone' => $phone_number,
		'logging' => $response,

	);

	$inserted = $wpdb->insert($wpdb->prefix . 'my_table', $data);
}

add_action('user_register', '_wooc_user_creation');

/*
*
* Below are snippet for forminator plugins 
*
*/

/**
 * Function for `forminator_custom_form_submit_before_set_fields` action-hook.
 * Process logic of after fields submittion to update submission fields value

 */
add_action('forminator_custom_form_submit_before_set_fields',  '_forminator_after_save');


function _forminator_after_save($entry, $form_id, $form_data_array)
{


	$form_data = array();
	if ($form_id == 111) { // specific form for example
		if (!empty($form_data_array)) {
			//   print_r($entry);
			foreach ($form_data_array as $key => $field) {
				//print_r($field);
				if (!str_contains($field['name'], 'upload')) {

					if (str_contains($field['name'], 'select')) {
						foreach ($field['field_array']['options'] as $key => $field_value) {
							if ($field['value'] == $field_value['value']) {
								$form_data[$field['name']] = $field_value['label'];
							}
						}
					} else {
						echo $field['name'] . ' >> ' . $field['value'] . '<br>';
						$form_data[$field['name']] = $field['value'];
					}
				} else {
					echo $field['name'] . '(no vale as its array )' . '<br>';
				}
			}
		}
	}
}
/**
 * define the forminator_custom_form_thankyou_message callback 
 * below example of showing/updating  a message after submitting the form
 */

add_filter('forminator_custom_form_thankyou_message', '_forminator_custom_form_thankyou_message');

function _forminator_custom_form_thankyou_message($message, $custom_form)
{

	if ($custom_form->id == 1234) { // a specific form 

		if ($word_count > 100) {
			$message = "Please email us at ..@..com for a customized quote";
		} else {
			$total = 50;
			$message = "Estimated price: " . $total . "$";
		}

		return $message;
	}
	return $message;
}

/**
 * define the forminator_field_markup callback 
 * below example of updating field markup ( below for description )
 */
add_filter('forminator_field_markup', array($this, '_forminator_field_markup'), 10, 2);


function _forminator_field_markup($html, $field) {



	if (isset($field['description'])) {

		$field_id = 'forminator-field-' . $field['element_id'];
		$description = $field['description'];

		$markup = sprintf(
				'<span class="forminator-description" aria-describedby="%s">aa%s</span>',
				$field_id,
				esc_html($description)
		);

		//$html = str_replace($markup, '', $html);
		$html = str_replace('<input type="text"', '<input type="text" id="' . $field_id . '" data-tippy-content="' . esc_html($description) . '" ', $html);
		return $html . '<i class="fa fa-info-circle" aria-hidden="true" data-tippy-content="' . esc_html($description) . '"></i>';
	}

	return $html;
}