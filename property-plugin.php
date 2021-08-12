<?php 
/**
 * Plugin Name: Property Plugin
 * Description: A Beautifully plugin.
 * Author: Sudip
 */

function wpt_property_post_type() {

	$labels = array(
		'name'               => __( 'Properties' ),
		'singular_name'      => __( 'Property' ),
		'add_new'            => __( 'Add New Property' ),
		'add_new_item'       => __( 'Add New Property' ),
		'edit_item'          => __( 'Edit Property' ),
		'new_item'           => __( 'Add New Property' ),
		'view_item'          => __( 'View Property' ),
		'search_items'       => __( 'Search Property' ),
		'not_found'          => __( 'No properties found' ),
		'not_found_in_trash' => __( 'No properties found in trash' )
	);
//'editor',
	$supports = array(
		'title',
		
		'thumbnail',
		'comments',
		'revisions',
	);

	$args = array(
		'labels'               => $labels,
		'supports'             => $supports,
		'public'               => true,
		'rewrite'              => array( 'slug' => 'properties' ),
		'has_archive'          => true,
		'menu_position'        => 30,
		'menu_icon'            => 'dashicons-calendar-alt',
	);

	register_post_type( 'properties', $args );
    register_taxonomy( 'categories', array('properties'), array(
        'hierarchical' => true, 
        'label' => 'Categories', 
        'singular_label' => 'Category', 
        'rewrite' => array( 'slug' => 'categories', 'with_front'=> false )
        )
    );

    register_taxonomy_for_object_type( 'categories', 'properties' );
}
add_action( 'init', 'wpt_property_post_type' );

add_action( 'add_meta_boxes', 'wpt_add_property_metaboxes' );
function wpt_add_property_metaboxes() {

	add_meta_box(
		'wpt_properties_year',
		'Year completed',
		'wpt_properties_year',
		'properties',
		'normal',
		'high'
	);

}

function wpt_properties_year() {
	global $post;

	
	wp_nonce_field( basename( __FILE__ ), 'property_fields' );

	
	$year_completed = get_post_meta( $post->ID, 'year_completed', true );

	echo '<input type="text" name="year_completed" value="' . esc_textarea( $year_completed )  . '" class="widefat">';

}

function wpt_save_properties_meta( $post_id, $post ) {

	
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	
	if ( ! isset( $_POST['year_completed'] ) || ! wp_verify_nonce( $_POST['property_fields'], basename(__FILE__) ) ) {
		return $post_id;
	}

	
	$properties_meta['year_completed'] = esc_textarea( $_POST['year_completed'] );

	
	foreach ( $properties_meta as $key => $value ) :

		
		if ( 'revision' === $post->post_type ) {
			return;
		}

		if ( get_post_meta( $post_id, $key, false ) ) {
			
			update_post_meta( $post_id, $key, $value );
		} else {
			
			add_post_meta( $post_id, $key, $value);
		}

		if ( ! $value ) {
			
			delete_post_meta( $post_id, $key );
		}

	endforeach;

}
add_action( 'save_post', 'wpt_save_properties_meta', 1, 2 );
/*shortcode */

function register_shortcodes() {
    add_shortcode( 'property', 'shortcode_property' );
}
add_action( 'init', 'register_shortcodes' );

function shortcode_property( $atts ) {
    global $wp_query,
        $post;

    $atts = shortcode_atts( array(
        'count' => 5
    ), $atts );

    $loop = new WP_Query( array(
        'posts_per_page'    => $count,
        'post_type'         => 'properties',
        'orderby'           => 'menu_order title',
        'order'             => 'ASC',
        ) 
    );

    if( ! $loop->have_posts() ) {
        return false;
    }

   ob_start();
        ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" integrity="sha256-mmgLkCYLUQbXn0B1SRqzHar6dCnv9oZFPEC1g1cwlkk=" crossorigin="anonymous" />

<div class="container">
<div class="row">
    <div class="col-12 col-sm-12 col-md-12">
        <div class="card">
            <div class="card-header">
                <h4> Property List</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive" id="proTeamScroll" tabindex="2" style=" overflow: hidden; outline: none;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Property name</th>
                                <th>Category</th>
                                <th>Thumb image</th>
                                <th>Year completed</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                             while( $loop->have_posts() ) {
                                 $loop->the_post();
                            ?>
                            <tr>
                                <td class="table-img">
                                <?php echo get_the_title();?>
                                </td>

                                <td>
                                <?php $terms = get_the_terms( $post->ID , 'categories' );
                                    if(!empty($terms)){
                                        foreach ( $terms as $term ) {
                                            echo $term->name . ', ';
                                        }
                                    } ?>
                                    
                                </td>

                                <td>
                                <?php 
                                if(has_post_thumbnail()) {  ?>
                                
                                    <?php the_post_thumbnail(); ?>
                                
                            <?php }
                                ?>
                                </td>

                                <td class="text-truncate">
                                    <?php echo get_post_meta( get_the_ID(), 'year_completed', true );?>
                                </td>
                                
                            </tr>
                           <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
        <?php
        
    

    wp_reset_postdata();
    return ob_get_clean();
}

?>