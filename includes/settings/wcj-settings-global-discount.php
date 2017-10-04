<?php
/**
 * Booster for WooCommerce - Settings - Global Discount
 *
 * @version 3.1.3
 * @since   2.8.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$is_multiselect_products     = ( 'yes' === get_option( 'wcj_list_for_products', 'yes' ) );
$products                    = ( $is_multiselect_products ? wcj_get_products() : false );
$product_cats                = wcj_get_terms( 'product_cat' );
$product_tags                = wcj_get_terms( 'product_tag' );

$settings = array(
	array(
		'title'    => __( 'Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_global_discount_options',
	),
	array(
		'title'    => __( 'Total Groups', 'woocommerce-jetpack' ),
		'id'       => 'wcj_global_discount_groups_total_number',
		'default'  => 1,
		'type'     => 'custom_number',
		'desc_tip' => __( 'Press Save changes after you change this number.', 'woocommerce-jetpack' ),
		'desc'     => apply_filters( 'booster_get_message', '', 'desc' ),
		'custom_attributes' => is_array( apply_filters( 'booster_get_message', '', 'readonly' ) ) ?
			apply_filters( 'booster_get_message', '', 'readonly' ) : array( 'step' => '1', 'min'  => '1', ),
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_global_discount_options',
	),
);
for ( $i = 1; $i <= apply_filters( 'booster_get_option', 1, get_option( 'wcj_global_discount_groups_total_number', 1 ) ); $i++ ) {
	wcj_maybe_convert_and_update_option_value( array(
		array( 'id' => 'wcj_global_discount_sale_products_incl_' . $i, 'default' => '' ),
		array( 'id' => 'wcj_global_discount_sale_products_excl_' . $i, 'default' => '' ),
	), $is_multiselect_products );
	$settings = array_merge( $settings, array(
		array(
			'title'    => __( 'Discount Group', 'woocommerce-jetpack' ) . ' #' . $i,
			'type'     => 'title',
			'id'       => 'wcj_global_discount_options_' . $i,
		),
		array(
			'title'    => __( 'Enabled', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Enabled/disables the discount group.', 'woocommerce-jetpack' ),
			'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
			'id'       => 'wcj_global_discount_sale_enabled_' . $i,
			'default'  => 'yes',
			'type'     => 'checkbox',
		),
		array(
			'title'    => __( 'Type', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Can be fixed or percent.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_global_discount_sale_coefficient_type_' . $i,
			'default'  => 'percent',
			'type'     => 'select',
			'options'  => array(
				'percent' => __( 'Percent', 'woocommerce-jetpack' ),
				'fixed'   => __( 'Fixed', 'woocommerce-jetpack' ),
			),
		),
		array(
			'title'    => __( 'Value', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Must be negative number.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_global_discount_sale_coefficient_' . $i,
			'default'  => 0,
			'type'     => 'number',
			'custom_attributes' => array( 'max' => 0, 'step' => 0.0001 ),
		),
		array(
			'title'    => __( 'Product Scope', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Possible values: all products, only products that are already on sale, only products that are not on sale.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_global_discount_sale_product_scope_' . $i,
			'default'  => 'all',
			'type'     => 'select',
			'options'  => array(
				'all'              => __( 'All products', 'woocommerce-jetpack' ),
				'only_on_sale'     => __( 'Only products that are already on sale', 'woocommerce-jetpack' ),
				'only_not_on_sale' => __( 'Only products that are not on sale', 'woocommerce-jetpack' ),
			),
		),
		array(
			'title'    => __( 'Include Product Categories', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Set this field to apply discount to selected product categories only. Leave blank to apply to all products.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_global_discount_sale_categories_incl_' . $i,
			'default'  => '',
			'class'    => 'chosen_select',
			'type'     => 'multiselect',
			'options'  => $product_cats,
		),
		array(
			'title'    => __( 'Exclude Product Categories', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Set this field to NOT apply discount to selected product categories. Leave blank to apply to all products.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_global_discount_sale_categories_excl_' . $i,
			'default'  => '',
			'class'    => 'chosen_select',
			'type'     => 'multiselect',
			'options'  => $product_cats,
		),
		array(
			'title'    => __( 'Include Product Tags', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Set this field to apply discount to selected product tags only. Leave blank to apply to all products.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_global_discount_sale_tags_incl_' . $i,
			'default'  => '',
			'class'    => 'chosen_select',
			'type'     => 'multiselect',
			'options'  => $product_tags,
		),
		array(
			'title'    => __( 'Exclude Product Tags', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Set this field to NOT apply discount to selected product tags. Leave blank to apply to all products.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_global_discount_sale_tags_excl_' . $i,
			'default'  => '',
			'class'    => 'chosen_select',
			'type'     => 'multiselect',
			'options'  => $product_tags,
		),
		wcj_get_settings_as_multiselect_or_text(
			array(
				'title'    => __( 'Include Products', 'woocommerce-jetpack' ),
				'desc_tip' => __( 'Set this field to apply discount to selected products only. Leave blank to apply to all products.', 'woocommerce-jetpack' ),
				'id'       => 'wcj_global_discount_sale_products_incl_' . $i,
				'default'  => '',
				'class'    => 'widefat',
			),
			$products,
			$is_multiselect_products
		),
		wcj_get_settings_as_multiselect_or_text(
			array(
				'title'    => __( 'Exclude Products', 'woocommerce-jetpack' ),
				'desc_tip' => __( 'Set this field to NOT apply discount to selected products. Leave blank to apply to all products.', 'woocommerce-jetpack' ),
				'id'       => 'wcj_global_discount_sale_products_excl_' . $i,
				'default'  => '',
				'class'    => 'widefat',
			),
			$products,
			$is_multiselect_products
		),
		array(
			'type'     => 'sectionend',
			'id'       => 'wcj_global_discount_options_' . $i,
		),
	) );
}
return $settings;
