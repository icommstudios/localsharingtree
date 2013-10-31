<?php

/**
 * GBS UI related: widgets, resources, etc..
 *
 * @package GBS
 * @subpackage Theme
 */
class Group_Buying_Widgets extends Group_Buying_Controller {
	private static $instance;

	final public static function init() {
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_FinePrint");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_Highlights");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_Locations");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_Location");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_AllCategories");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_Categories");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_Tags");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_RecentDeals");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_RelatedDeals");' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("GroupBuying_Share_and_Earn");' ) );
	}
}

/**
 * GBS Fineprint Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_FinePrint extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function GroupBuying_FinePrint() {
		$widget_ops = array( 'description' => gb__( 'Can only be used on the Deal Page, otherwise we will gracefully hide the widget.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Deal Fine Print' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_fine_print', $args, $instance );
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$post_type = get_query_var( 'post_type' );
		if ( is_single() && $post_type == gb_get_deal_post_type() ) {
			echo $before_widget;
			ob_start();
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
?>
				<div class="deal-widget-inner"><?php gb_fine_print(); ?></div>
				<?php

			$view = ob_get_clean();
			print apply_filters( 'gb_fine_print_widget', $view );
			echo $after_widget;
		}
		do_action( 'post_fine_print', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

/**
 * GBS Highlights Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_Highlights extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_Highlights() {
		$widget_ops = array( 'description' => gb__( 'Can only be used on the Deal Page, otherwise we will gracefully hide the widget.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Deal Highlights' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_highlights', $args, $instance );
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$post_type = get_query_var( 'post_type' );
		if ( is_single() && $post_type == gb_get_deal_post_type() ) {
			echo $before_widget;
			ob_start();
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
?>
				<div class="deal-widget-inner"><?php gb_highlights(); ?></div>
				<?php

			$view = ob_get_clean();
			print apply_filters( 'gb_highlights_widget', $view );
			echo $after_widget;
		}
		do_action( 'post_highlights', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

/**
 * GBS Locations Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_Locations extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_Locations() {
		$widget_ops = array( 'description' => gb__( 'Can only be used on the Deal Page, otherwise we will gracefully hide the widget.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Locations' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_locations', $args, $instance );
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$post_type = get_query_var( 'post_type' );
		if ( is_single() && $post_type == gb_get_deal_post_type() ) {
			echo $before_widget;
			ob_start();
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
?>
				<div class="deal-widget-inner"><?php gb_deal_locations(); ?></div>
				<?php

			$view = ob_get_clean();
			print apply_filters( 'gb_locations_widget', $view );
			echo $after_widget;
		}
		do_action( 'post_locations', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

/**
 * GBS All Categories Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_AllCategories extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_AllCategories() {
		$widget_ops = array( 'description' => gb__( 'Displays a list of all deal categories.  Can be used in any widget area.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: All Categories' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_allcategories', $args, $instance );
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$post_type = get_query_var( 'post_type' );
		echo $before_widget;
		ob_start();
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		$args = array( 'taxonomy' => Group_Buying_Deal::CAT_TAXONOMY );
		$terms = get_terms(Group_Buying_Deal::CAT_TAXONOMY, $args);
		$count = count($terms); $i=0;
		if ($count > 0) {
			$term_list = '<ul class="deal_allcategories clearfix">';
			foreach ($terms as $term) {
				$term_list .= '<li><a href="' . get_term_link( $term ) . '" title="' . sprintf(__('View all post filed under %s', 'my_localization_domain'), $term->name) . '">' . $term->name . ' (' . $term->count . ')</a></li>';
			}
			$term_list .= '</ul>';
			echo $term_list;
		}

		$view = ob_get_clean();
		print apply_filters( 'gb_allcategories_widget', $view );
		echo $after_widget;
		do_action( 'post_allcategories', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

/**
 * GBS Categories Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_Categories extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_Categories() {
		$widget_ops = array( 'description' => gb__( 'Can only be used on the Deal Page, otherwise we will gracefully hide the widget.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Categories' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_categories', $args, $instance );
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$post_type = get_query_var( 'post_type' );
		if ( is_single() && $post_type == gb_get_deal_post_type() ) {
			echo $before_widget;
			ob_start();
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
?>
				<div class="deal-widget-inner"><?php gb_deal_categories(); ?></div>
				<?php

			$view = ob_get_clean();
			print apply_filters( 'gb_categories_widget', $view );
			echo $after_widget;
		}
		do_action( 'post_categories', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

/**
 * GBS Tags Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_Tags extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_Tags() {
		$widget_ops = array( 'description' => gb__( 'Can only be used on the Deal Page, otherwise we will gracefully hide the widget.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Tags' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_tags', $args, $instance );
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$post_type = get_query_var( 'post_type' );
		if ( is_single() && $post_type == gb_get_deal_post_type() ) {
			echo $before_widget;
			ob_start();
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
?>
				<div class="deal-widget-inner"><?php gb_deal_tags( ); ?></div>
				<?php

			$view = ob_get_clean();
			print apply_filters( 'gb_tags_widget', $view );
			echo $after_widget;
		}
		do_action( 'post_tags', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

/**
 * GBS Location Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_Location extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_Location() {
		$widget_ops = array( 'description' => gb__( 'Can only be used on the Deal Page, otherwise we will gracefully hide the widget.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Map' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_location', $args, $instance );
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		$post_type = get_query_var( 'post_type' );
		if ( is_single() && $post_type == gb_get_deal_post_type() ) {
			echo $before_widget;
			ob_start();
			if ( !empty( $title ) ) { echo $before_title . $title. $after_title; };
?>
				<div class="deal-widget-inner"><?php gb_map(); ?></div>
				<?php

			$view = ob_get_clean();
			print apply_filters( 'gb_location_widget', $view );
			echo $after_widget;
		}
		do_action( 'post_location', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

/**
 * GBS Share and Earn Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_Share_and_Earn extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_Share_and_Earn() {
		$widget_ops = array( 'description' => gb__( 'Display a "Share and Earn" widget.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Share and Earn' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_share_and_earn', $args, $instance );
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title. $after_title; };

		if ( is_single() && get_post_type( get_the_ID() ) == gb_get_deal_post_type() ):
			Group_Buying_Controller::load_view( 'widgets/share-earn.php', array() );
		endif;

		echo $after_widget;
		do_action( 'post_share_and_earn', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

/**
 * GBS Recent Deals Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_RecentDeals extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_RecentDeals() {
		$widget_ops = array( 'description' => gb__( 'Creates an attractive display of recent deals.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Recent Deals' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_recent_deals', $args, $instance );
		global $gb, $wp_query;
		$temp = null;
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$buynow = empty( $instance['buynow'] ) ? 'Buy Now' : $instance['buynow'];
		$deals = apply_filters( 'gb_recent_deals_widget_show', $instance['deals'] );
		if ( is_single() ) {
			$post_not_in = $wp_query->post->ID;
		}
		$count = 1;
		$deal_query= null;
		$args=array(
			'post_type' => gb_get_deal_post_type(),
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => '_expiration_date',
					'value' => array( 0, current_time( 'timestamp' ) ),
					'compare' => 'NOT BETWEEN'
				) ),
			'posts_per_page' => $deals,
			'post__not_in' => array( $post_not_in )
		);

		$deal_query = new WP_Query( $args );
		if ( $deal_query->have_posts() ) {
			echo $before_widget;
			echo $before_title . $title . $after_title;
			while ( $deal_query->have_posts() ) : $deal_query->the_post();

			Group_Buying_Controller::load_view( 'widgets/recent-deals.php', array( 'buynow'=>$buynow ) );

			endwhile;
			echo $after_widget;
		}
		$deal_query = null; $deal_query = $temp;
		wp_reset_query();
		do_action( 'post_recent_deals', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['buynow'] = strip_tags( $new_instance['buynow'] );
		$instance['deals'] = strip_tags( $new_instance['deals'] );
		$instance['show_expired'] = strip_tags( $new_instance['show_expired'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$buynow = esc_attr( $instance['buynow'] );
		$deals = esc_attr( $instance['deals'] );
		$show_expired = esc_attr( $instance['show_expired'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id( 'buynow' ); ?>"><?php gb_e( 'Buy now link text:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'buynow' ); ?>" name="<?php echo $this->get_field_name( 'buynow' ); ?>" type="text" value="<?php echo $buynow; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id( 'deals' ); ?>"><?php gb_e( 'Number of deals to display:' ); ?>
            	<select id="<?php echo $this->get_field_id( 'deals' ); ?>" name="<?php echo $this->get_field_name( 'deals' ); ?>">
					<option value="1">1</option>
					<option value="2"<?php if ( $deals=="2" ) {echo ' selected="selected"';} ?>>2</option>
					<option value="3"<?php if ( $deals=="3" ) {echo ' selected="selected"';} ?>>3</option>
					<option value="4"<?php if ( $deals=="4" ) {echo ' selected="selected"';} ?>>4</option>
					<option value="5"<?php if ( $deals=="5" ) {echo ' selected="selected"';} ?>>5</option>
					<option value="10"<?php if ( $deals=="10" ) {echo ' selected="selected"';} ?>>10</option>
					<option value="15"<?php if ( $deals=="15" ) {echo ' selected="selected"';} ?>>15</option>
					<option value="-1"<?php if ( $deals=="-1" ) {echo ' selected="selected"';} ?>>All</option>
				 </select>
            </label></p>
        <?php
	}
}

/**
 * GBS Related Deals Widget
 *
 * @package GBS
 * @subpackage Theme
 */
class GroupBuying_RelatedDeals extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function GroupBuying_RelatedDeals() {
		$widget_ops = array( 'description' => gb__( 'Creates an attractive display of related deals. Relationships are based on user&rsquo;s preferred location or a single term from the deal shown.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Related Deals' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_related_deals', $args, $instance );
		global $wp_query, $post;

		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$buynow = empty( $instance['buynow'] ) ? gb__('Buy Now') : $instance['buynow'];
		$qty = $instance['deals'];
		$location = '';

		if ( isset( $_COOKIE[ 'gb_location_preference' ] ) && $_COOKIE[ 'gb_location_preference' ] != '' ) {
			$location = $_COOKIE[ 'gb_location_preference' ];
		}
		if ( $location == '' ) {
			$locations = array();
			$terms = get_the_terms( $post->ID, gb_get_deal_location_tax() );
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$locations[] = $term->slug;
				}
			}
			if ( isset( $locations[0] ) ) {
				$location = $locations[0];
			}
		}
		if ( $location != '' ) {
			$args = array(
				'post_type' => gb_get_deal_post_type(),
				'post_status' => 'publish',
				gb_get_deal_location_tax() => apply_filters( 'gb_related_deals_widget_location', $location, $locations ),
				'meta_query' => array(
					array(
						'key' => '_expiration_date',
						'value' => array( 0, current_time( 'timestamp' ) ),
						'compare' => 'NOT BETWEEN'
					) ),
				'posts_per_page' => $qty
			);
			if ( is_single() ) {
				$args = array_merge( $args, array( 'post__not_in' => array( $wp_query->post->ID ) ) );
			}

			$related_deal_query = new WP_Query( apply_filters( 'gb_related_deals_widget_args', $args) );
			if ( $related_deal_query->have_posts() ) {
				echo $before_widget;
				echo $before_title . $title . $after_title;
					while ( $related_deal_query->have_posts() ) : $related_deal_query->the_post();

						Group_Buying_Controller::load_view( 'widgets/related-deals.php', array( 'buynow'=>$buynow ) );

					endwhile;
				echo $after_widget;
			}
			wp_reset_query();
			do_action( 'post_related_deals', $args, $instance );
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['buynow'] = strip_tags( $new_instance['buynow'] );
		$instance['deals'] = strip_tags( $new_instance['deals'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$buynow = esc_attr( $instance['buynow'] );
		$deals = esc_attr( $instance['deals'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id( 'buynow' ); ?>"><?php gb_e( 'Buy now link text:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'buynow' ); ?>" name="<?php echo $this->get_field_name( 'buynow' ); ?>" type="text" value="<?php echo $buynow; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id( 'deals' ); ?>"><?php gb_e( 'Number of deals to display:' ); ?>
            	<select id="<?php echo $this->get_field_id( 'deals' ); ?>" name="<?php echo $this->get_field_name( 'deals' ); ?>">
					<option value="1">1</option>
					<option value="2"<?php if ( $deals=="2" ) {echo ' selected="selected"';} ?>>2</option>
					<option value="3"<?php if ( $deals=="3" ) {echo ' selected="selected"';} ?>>3</option>
					<option value="4"<?php if ( $deals=="4" ) {echo ' selected="selected"';} ?>>4</option>
					<option value="5"<?php if ( $deals=="5" ) {echo ' selected="selected"';} ?>>5</option>
					<option value="10"<?php if ( $deals=="10" ) {echo ' selected="selected"';} ?>>10</option>
					<option value="15"<?php if ( $deals=="15" ) {echo ' selected="selected"';} ?>>15</option>
					<option value="-1"<?php if ( $deals=="-1" ) {echo ' selected="selected"';} ?>>All</option>
				 </select>
            </label></p>
        <?php
	}

}
