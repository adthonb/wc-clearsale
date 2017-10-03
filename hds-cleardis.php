<?php 
/*
Plugin Name: HDs Remove Sale Price
Plugin URI: https://www.hardwaredatastore.com
Description: Click on button to remove all discount price in product by specify category.
Version: 1.4
Author: AdthonB
Author URI: https://www.facebook.com/animatorwithyou

Copyright 2016 AdthonB.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('admin_menu', 'hds_cleardis');

function hds_cleardis() {
    add_management_page( 'HDs Clear Discount Page', 'HDs Clear discount', 'manage_options', 'hds-cleardis', 'hds_cleardis_page');
}
function hds_cleardis_page() {
    if ( !current_user_can('manage_options')) {
        wp_die( __('You do not have permission to do this') );
    }
    hds_cleardis_form();  
}

function hds_cleardis_form() { ?>
    <div id="respond">
    <form name="hds-cleardis" action="<?php the_permalink(); ?>" method="post">
       <input type="hidden" name="submitted-clear" value="1">
       <input type="submit" name="clear" value="<?php esc_attr_e('Clear') ?>" />
   </form>
   <?php do_action('hds_cleardis_status'); ?>
   </div>
<?php }

if ( isset($_POST['submitted-clear']) ) {
    add_action('hds_cleardis_status', 'hds_clear_once' );
}

/* remove sale price all product in category summer-sale-2016 */
function hds_clear_once() {
	$args = array(
		'post_type' => 'product',
		'tax_query' => array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => '105'
			),
		),
	);
	$all_products = new WP_Query( $args );
	if ( $all_products->have_posts() ) :
        $i = 1;
		while ( $all_products->have_posts() ) : $all_products->the_post();
            $id = get_the_ID();
            echo $i . ' - ' . get_the_title() . ' Updated !<br/>';
            $product = new WC_Product_Variable( $id );
            $childs = $product->get_children( true );
            echo '<pre>';
            print_r($childs);
            echo '</pre>';
            $prices[$i][] = $product->get_variation_regular_price();
            $prices[$i][] = $product->get_variation_regular_price( 'max' );
            echo '<pre>Price: ';
            print_r($prices[$i]);
            echo '</pre>';

            update_post_meta( $childs[0], '_price', $prices[$i][0] );
            update_post_meta( $childs[0], '_regular_price', $prices[$i][0] );
            update_post_meta( $childs[0], '_sale_price', '' );
            update_post_meta( $childs[1], '_price', $prices[$i][1] );
            update_post_meta( $childs[1], '_regular_price', $prices[$i][1] );
            update_post_meta( $childs[1], '_sale_price', '' );
            wc_update_product_stock_status( $id, 'instock');
            $status = wp_remove_object_terms( $id, $args['tax_query']['terms'], 'product_cat' );
            echo $status;
            $product->sync( $id );
            $i++; 
		endwhile;
        wp_reset_postdata();
    endif;
}
?>