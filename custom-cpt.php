<?php
/*
Plugin Name: Widgetized CPTs
Plugin URI: 
Description: Display a link list to your custom post type in a widgetized area. Choose how many and how to order them
Author: Jose Castaneda
Author URI: http://blog.josemcastaneda.com
Version: 0.1.0
Text Domain: wpcts

=====================================================================================
Copyright (C) 2015 Jose Castaneda

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
=====================================================================================

*/

class Widgetized_CPTs extends WP_Widget
{
	// Create the instance
	public function __construct() {
		load_plugin_textdomain( 'wcpts', false, '/wcpts/lang' );
		parent::__construct( 'wcpts', __( 'Display Custom Post Types', 'wcpts' ), array( 
			'description' => __( 'Display your custom post type content in a widgetized area', 'wcpts' ),
			'classname' => 'widgetized-cpt'
			) );
	}
	
	/**
	 * Gets the available posts types and creates an array 
	 */
	private function get_types(){
		return array_diff( get_post_types( array( 'public' => true ), 'names' ), array( 'post', 'page', 'attachment' ) );
	}
	
	// The output of our widget
	public function widget( $args, $instance ){
		
		
		// Get the post types
		$types = $this->get_types();
		
		$type = isset( $instance['type'] ) ? $instance['type'] : 'page';
		
		$count = ( isset( $instance['count'] ) && is_integer( intval( $instance['count'] ) ) ) ? intval( $instance['count'] ) : 3;
		
		$order = isset( $instance['orderby'] ) ? $instance['orderby'] : 'rand';
		
		if ( in_array( $type, $types ) ):
		
			echo $args['before_widget'];
		
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			
			$query = new WP_Query(
				array(
					'post_type' => $type,
					'posts_per_page' => $count,
					'orderby' => $order,
					 ) );
			if ( $query->have_posts() ):
				echo '<ul>';
				while( $query->have_posts() ): $query->the_post();
				echo '<li>';
				echo wp_kses_post( apply_filters( 'wcpts_item', '<a href="' . esc_url( get_permalink() ) . '">' . get_the_title() . '</a>' ) );
				echo '</li>';
				endwhile;
				echo '</ul>';
			wp_reset_postdata();
			else:
				_e( 'Appears nothing was found', 'wcpts' );
			endif;
		
			echo $args['after_widget'];
		else:
			if( current_user_can( 'manage_options' ) ):
		printf( __( 'Appears there are no custom post types to display. Why not create <a href="%s">some</a>?', 'wpcts' ), esc_url( admin_url() ) );
			endif;
		endif; // has type...
	}
	
	// what gets saved to the DB
	public function update( $new_instance, $old_instance ){
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['type'] = ( in_array( $new_instance['type'], $this->get_types() ) ) ? $new_instance['type'] : 'page';
		$instance['count'] = is_integer( intval( $new_instance['count'] ) ) ? intval( $new_instance['count'] ) : 3;
		$instance['orderby'] = in_array( $new_instance['orderby'], array( 'rand', 'date' ) ) ? $new_instance['orderby'] : 'rand';
		
		return $instance;
	}
	
	// the form on the /wp-admin side
	public function form( $instance ){
		
		// Get the post types
		$types = $this->get_types();
		
		// If no custom post types, be abort the mission, Captain!
		if ( !$types ){ ?>
			 <p><?php _e( 'It appears there are no custom post types to select', 'wcpts' ); ?></p>
			<?php return;
		}
		
		// get the instance settings, if none we create them
		$instance = wp_parse_args( (array) $instance, array( 'title'=> '', 'type' => key( $types ), 'count' => 3, 'orderby' => 'rand' ) );
		
		// if there are post types we continue ?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wcpts' ); ?>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Post Type:', 'wcpts' ); ?>
		<select name="<?php echo $this->get_field_name( 'type' ); ?>">
		<?php
		foreach( $types as $type ){
			// Get da name of the post type
			$name = get_post_type_object( $type )->labels->name;
			// display the options ?>
			<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $instance['type'], $type ); ?>><?php echo esc_html( $name ); ?></option>
			<?php
		} ?>
		</select></label></p>
		<p><label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'How many would you like to show?', 'wcpts' ); ?></label></p>
		<p><input class="widefat" type="text" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order by', 'wcpts' ); ?>
		<select name="<?php echo $this->get_field_name( 'orderby' ); ?>" id="<?php echo $this->get_field_id( 'order' ); ?>">
		<?php 
		foreach( 	array(
			'rand' => _x( 'Random', 'sort randomly','wcpts' ),
			'date' => _x( 'Date', 'sort by date', 'wcpts' ),
			) as $key => $value ) { ?>
			<option <?php selected( $instance['orderby'], $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
		<?php } ?>
		</select>
		</label></p>
		<?php 
	}

}

add_action( 'widgets_init', 'wcpts_register' );
function wcpts_register(){
	register_widget( 'Widgetized_CPTs' );
}