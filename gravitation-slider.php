<?php
/*
Plugin Name: Gravitation Slider
Plugin URI: https://github.com/UlisesFreitas/gravitation-slider
Description: Gravitation Slider, is a plugin to display slider on your site, with shortcodes
Author: Ulises Freitas
Version: 1.0.2
Author URI: https://disenialia.com/
License: GPLv2
*/
/*-----------------------------------------------------------------------------*/
/*
	Gravitation Slider
    Copyright (C) 2015 Gravitation Slider

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301
    USA


	Disenialia©, hereby disclaims all copyright interest in the
	library Gravitation Slider (a library for display slider on Wordpress)
	written by Ulises Freitas.

	Disenialia©, 21 October 2015
	CEO Ulises Freitas.
*/
/*-----------------------------------------------------------------------------*/

function gravitation_slider_install() {

    gravitation_slider_create_post_type();
    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'gravitation_slider_install' );

function gravitation_slider_deactivation() {

    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'gravitation_slider_deactivation' );


function GravitationSlider($post_id){
	echo do_shortcode('[gravitation_slider ids="'.$post_id.'"]');
}


function gv_slider_hide_add_new_button() {
  global $pagenow, $post;
  if(is_admin()){
	if($pagenow == 'edit.php'){
		$gvSliderPost = isset($post->post_type) ? $post->post_type : NULL;
		if($gvSliderPost != NULL){
			if($gvSliderPost == "gv_slider"){
				echo '<style type="text/css">h1 { display:none; }</style>';
			}


		}
	}
  }
}
add_action('admin_head','gv_slider_hide_add_new_button');

function gv_slider_add_custom_title( $title ) {

	global $pagenow,$post_type ;

	if($pagenow == 'post-new.php' && $post_type == 'gv_slider'){
		$title = 'Gravitation Slider ';
		return $title;
  	}else{
	  return $title;
  	}
}

add_filter('default_title', 'gv_slider_add_custom_title');



function gv_slider_remove_screen_options_tab() {
    return false;
}
add_filter('screen_options_show_screen', 'gv_slider_remove_screen_options_tab');




add_filter( 'post_updated_messages', 'gv_slider_updated_messages' );
function gv_slider_updated_messages( $messages ) {

	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	$messages['gv_slider'] = array(
		0  => '',
		1  => __( 'Slider updated.', 'gravitation-slider' ),
		2  => __( 'Slider updated.', 'gravitation-slider' ),
		3  => __( 'Slider deleted.', 'gravitation-slider' ),
		4  => __( 'Slider updated.', 'gravitation-slider' ),
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Slider restored to revision from %s', 'gravitation-slider' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Slider published.', 'gravitation-slider' ),
		7  => __( 'Slider saved.', 'gravitation-slider' ),
		8  => __( 'Slider submitted.', 'gravitation-slider' ),
		9  => sprintf(
			__( 'Slider scheduled for: <strong>%1$s</strong>.', 'gravitation-slider' ),
			date_i18n( __( 'M j, Y @ G:i', 'gravitation-slider' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Slider draft updated.', 'gravitation-slider' )
	);

	if ( $post_type_object->publicly_queryable && $post_type == "gv_slider") {
		$permalink = get_permalink( $post->ID );

		$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View Slider', 'gravitation-slider' ) );
		$messages[ $post_type ][1] .= $view_link;
		$messages[ $post_type ][6] .= $view_link;
		$messages[ $post_type ][9] .= $view_link;

		$preview_permalink = add_query_arg( 'preview', 'false', $permalink );
		$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview slider', 'gravitation-slider' ) );
		$messages[ $post_type ][8]  .= $preview_link;
		$messages[ $post_type ][10] .= $preview_link;
	}

	return $messages;
}




function gv_slider_add_header_info() {

    global $pagenow ,$post;
    if($pagenow == 'edit.php' || $pagenow == 'post-new.php'){
		if($post != NULL){
			if($post->post_type == 'gv_slider'){
				$output = '<style>tfoot{display:none;}thead{display:none;}</style>';
				$output .= '<div class="my-div">';
				$output .= '<img src="'.WP_PLUGIN_URL.'/gravitation-slider/img/banner-header.png" alt="Gravitation Slider">';
				$output .= '</div>';

				echo $output;
			}
		}
	}
	$gv_slider = isset($_GET['post_type']) ? $_GET['post_type'] : NULL;
	//page=gravitation-slider.php
	$gv_slider_help = isset($_GET['page']) ? $_GET['page'] : NULL;

	if($gv_slider == "gv_slider" && $pagenow == 'edit.php' && !$post && $gv_slider_help != 'gravitation-slider.php'){
				$output = '<style>#posts-filter{display:none;} h1{display:none;}</style>';
				$output .= '<div class="my-div">';
				$output .= '<img src="'.WP_PLUGIN_URL.'/gravitation-slider/img/banner-header.png" alt="Gravitation Slider">';
				$output .= '</div>';

				$output .= '<a href="post-new.php?post_type=gv_slider">Create a new Gravitation Slider</a>';

				echo $output;
	}
}
add_action('admin_notices','gv_slider_add_header_info');



add_action( 'load-edit.php', 'gv_slider_custom_list_tables');

function gv_slider_custom_list_tables(){

    if( 'edit-gv_slider' !== get_current_screen()->id ){
        return;
    }
    require_once ( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );

    class WPSE_Headless_Table extends WP_Posts_List_Table {

        public function search_box( $text, $input_id ){}
        protected function pagination( $which ){}
        protected function display_tablenav( $which ){}

    }

    $mytable = new WPSE_Headless_Table;
    $mytable->prepare_items();
    add_filter( 'views_edit-gv_slider', function( $views ) use ( $mytable ) {
        global $wp_list_table;
        $wp_list_table = clone $mytable;
        return null;
    } );
}



add_filter( 'post_row_actions', 'gv_slider_remove_row_actions' );

function gv_slider_remove_row_actions( $actions ){

		global $post;

		if( $post->post_type == 'gv_slider'){

			unset($actions['inline hide-if-no-js']);
			unset ($actions['trash']);
			unset($actions['view']);
			unset($actions['edit']);

			return $actions;
       }else{
	       	return $actions;
       }
}



function gv_slider_bulk_actions($actions){

		global $post;

		if( $post->post_type == 'gv_slider'){
	        unset( $actions['edit'] );
	        unset( $actions['trash'] );
	        return $actions;
        }else{
			return $actions;
		}
}
add_filter('bulk_actions-edit-gv_slider','gv_slider_bulk_actions');


add_action( 'admin_enqueue_scripts', 'gv_slider_admin_script' );
function gv_slider_admin_script() {
    wp_enqueue_script('gv_slider_admin_main_js', plugins_url( 'admin/js/main.js', __FILE__ ), array('jquery'), '1.0.0' , true );
}

function gv_slider_wp_admin_style() {
        wp_register_style( 'gv_slider_admin_style',  plugins_url( 'admin/css/admin-styles.css', __FILE__ ), false, '1.0.0' );
        wp_enqueue_style( 'gv_slider_admin_style' );
}
add_action( 'admin_enqueue_scripts', 'gv_slider_wp_admin_style' );

function gravitation_slider_stylesheet() {

	wp_enqueue_style( 'gravitation_slider_slitslider_main', plugins_url( 'slitslider/css/main.css', __FILE__ ) );

}
add_action( 'wp_enqueue_scripts', 'gravitation_slider_stylesheet' );

function gravitation_slider_scripts(){


		wp_register_script(
							'gravitation_slider_bootstrap_js',
							plugin_dir_url(__FILE__).'bootstrap/js/bootstrap.min.js',
							array('jquery'), // or array(), or array('jquery') if this depends on jQuery
							'3.3.6', // or your plugin version, or the version of the js file
							true   // $in_footer
		);
	    wp_enqueue_script('gravitation_slider_bootstrap_js', array('jquery'), '3.3.5', false );


	    wp_register_script(
							'gravitation_slider_modernzr_js',
							plugin_dir_url(__FILE__).'slitslider/js/modernizr.custom.79639.js',
							array('jquery'), // or array(), or array('jquery') if this depends on jQuery
							'3.3.6', // or your plugin version, or the version of the js file
							true   // $in_footer
		);
	    wp_enqueue_script('gravitation_slider_modernzr_js', array('jquery'), '3.3.5', false );

		wp_register_script(
							'gravitation_slider_ba_cond_js',
							plugin_dir_url(__FILE__).'slitslider/js/jquery.ba-cond.min.js',
							array('jquery'), // or array(), or array('jquery') if this depends on jQuery
							'3.3.6', // or your plugin version, or the version of the js file
							true   // $in_footer
		);
	    wp_enqueue_script('gravitation_slider_ba_cond_js', array('jquery'), '3.3.5', false );


		wp_register_script(
							'gravitation_slider_slitslider_js',
							plugin_dir_url(__FILE__).'slitslider/js/jquery.slitslider.js',
							array('jquery'), // or array(), or array('jquery') if this depends on jQuery
							'3.3.6', // or your plugin version, or the version of the js file
							true   // $in_footer
		);
	    wp_enqueue_script('gravitation_slider_slitslider_js', array('jquery'), '3.3.5', false );

	    wp_register_script(
							'gravitation_slider_swipe_js',
							plugin_dir_url(__FILE__).'swipe/js/jquery.touchSwipe.min.js',
							array('jquery'), // or array(), or array('jquery') if this depends on jQuery
							'1.0', // or your plugin version, or the version of the js file
							true   // $in_footer
		);
	    wp_enqueue_script('gravitation_slider_swipe_js', array('jquery'), '1.0', false );



}
add_action('wp_enqueue_scripts','gravitation_slider_scripts', 10, 2);


function gv_slider_dynamics_scripts($sliderSettings) {

   $jquery ='<script type="text/javascript">
		( function( $ ) {
			$( document ).ready(function() {

				var Page = (function() {

					var $navArrows = $( "#nav-arrows-' . $sliderSettings["sliderID"].'" ),
						$nav = $( "#nav-dots-' . $sliderSettings["sliderID"].' > span" ),
						slitslider = $( "#' . $sliderSettings["sliderID"] . '" ).slitslider( {

							speed : ' . $sliderSettings["SlideDuration"].',
							optOpacity : true,
							translateFactor : 230,
							maxAngle : 45,
							maxScale : 2,
							autoplay : ' . $sliderSettings["AutoPlay"] . ',
							keyboard : ' . $sliderSettings["ArrowKeyNavigation"] . ',
							interval : ' . $sliderSettings["Idle"] . ',


							onBeforeChange : function( slide, pos ) {

								$nav.removeClass( "nav-dot-current" );
								$nav.eq( pos ).addClass( "nav-dot-current" );

							}
						} ),

						init = function() {

							initEvents();

						},
						initEvents = function() {

							$navArrows.children( ":last" ).on( "click", function() {

								slitslider.next();
								return false;

							} );

							$navArrows.children( ":first" ).on( "click", function() {

								slitslider.previous();
								return false;

							} );';


                            if( $sliderSettings['SwipeEnabled'] == 'true' ){


                           $jquery .= '$("#' . $sliderSettings["sliderID"] . '").swipe( {

								swipeLeft:function(event, distance, duration, fingerCount, fingerData, currentDirection) {
									slitslider.next();
								},
								swipeRight:function(event, distance, duration, fingerCount, fingerData, currentDirection) {
									slitslider.previous();
								},
								threshold:15

							});';

							}

							$jquery .= '$nav.each( function( i ) {

								$( this ).on( "click", function( event ) {

									var $dot = $( this );

									if( !slitslider.isActive() ) {

										$nav.removeClass( "nav-dot-current" );
										$dot.addClass( "nav-dot-current" );

									}

									slitslider.jump( i + 1 );
									return false;

								} );

							} );

						};

						return { init : init };

				})();

				Page.init();
			});
		} )( jQuery );
	</script>';

	$css = '<style>

		    /* Custom, iPhone Retina */
		    @media only screen and (min-width : 320px) {
		        .gravitation-slider  #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: 200px;
					overflow: hidden;
					position: relative;
				}
		    }

		    /* Extra Small Devices, Phones */
		    @media only screen and (min-width : 480px) {
				.gravitation-slider  #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: 200px;
					overflow: hidden;
					position: relative;
				}
		    }

		    /* Small Devices, Tablets */
		    @media only screen and (min-width : 768px) {
				.gravitation-slider  #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: ' . $sliderSettings["SlideHeight"] . 'px;
					overflow: hidden;
					position: relative;
				}
		    }

		    /* Medium Devices, Desktops */
		    @media only screen and (min-width : 992px) {
				.gravitation-slider  #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: ' . $sliderSettings["SlideHeight"].'px;
					overflow: hidden;
					position: relative;
				}
		    }

		    /* Large Devices, Wide Screens */
		    @media only screen and (min-width : 1200px) {
				.gravitation-slider  #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: ' . $sliderSettings['SlideHeight'].'px;
					overflow: hidden;
					position: relative;
				}
		    }

		    /* Large Devices, Wide Screens */
		    @media only screen and (max-width : 1200px) {
				.gravitation-slider  #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: ' . $sliderSettings["SlideHeight"].'px;
					overflow: hidden;
					position: relative;
				}
		    }

		    /* Medium Devices, Desktops */
		    @media only screen and (max-width : 992px) {
				.gravitation-slider  #' . $sliderSettings["sliderID"] . '{
					width: 100%;
					height: ' . $sliderSettings["SlideHeight"].'px;
					overflow: hidden;
					position: relative;
				}
		    }

		    /* Small Devices, Tablets */
		    @media only screen and (max-width : 768px) {
				.gravitation-slider #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: 300px;
					overflow: hidden !important;
					position: relative !important;
				}

				.gravitation-slider  .sl-slider h2 {
					font-size: 36px;
				}

				.gravitation-slider  .sl-slider p {
					font-size: 16px;
				}
		    }

		    /* Extra Small Devices, Phones */
		    @media only screen and (max-width : 480px) {

				.gravitation-slider  #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: 200px !important;
					overflow: hidden !important;
					position: relative !important;
				}

				.gravitation-slider .sl-slider h2 {
				    font-size: 20px;
				    position: relative;
				    bottom: 25%;
				}

				.gravitation-slider .sl-slider p {
				    font-size: 12px;
				    position: relative;
				    bottom: 25%;
				}
		    }

		    /* Custom, iPhone Retina */
		    @media only screen and (max-width : 320px) {

		        .gravitation-slider  #' . $sliderSettings["sliderID"] . ' {
					width: 100%;
					height: 200px !important;
					overflow: hidden !important;
					position: relative !important;
				}

				.gravitation-slider  .sl-slider h2 {
					font-size: 36px;
				}

				.gravitation-slider  .sl-slider p {
					font-size: 16px;
				}
		    }
			';


			 if($sliderSettings["FillMode"] == 0){

				$css .= '.gravitation-slider .bg-img {
				    padding: 200px;
				    -webkit-box-sizing: content-box;
				    -moz-box-sizing: content-box;
				    box-sizing: content-box;
				    position: absolute;
				    top: -200px;
				    left: -200px;
				    width: 100%;
				    height: 100%;
				    -webkit-background-size: cover;
				    -moz-background-size: cover;
				    background-size: cover;
				    background-position: center center;
				    background-origin: content-box;
				    background-repeat: no-repeat;
				}';
			}else{
				$css .= '.gravitation-slider .bg-img {
				    padding: 200px;
				    -webkit-box-sizing: content-box;
				    -moz-box-sizing: content-box;
				    box-sizing: content-box;
				    position: absolute;
				    top: -200px;
				    left: -200px;
				    width: 100%;
				    height: 100%;
				    -webkit-background-size: contain;
				    -moz-background-size: contain;
				    background-size: contain;
				    background-position: center center;
				    background-origin: content-box;
				    background-repeat: no-repeat;
				}';
			}

		$css .= '.bg-img-size{
			height: ' . $sliderSettings["SlideHeight"].'px;
		}
	</style>';

	echo $jquery.''.$css;
}


add_filter('widget_text', 'do_shortcode');

add_filter( 'manage_posts_columns', 'gv_slider_set_edit_columns' );

add_action( 'manage_gv_slider_posts_custom_column' , 'gv_slider_column', 10, 2 );

function gv_slider_set_edit_columns($columns) {

 	global $post;
    if(get_post_type( $post->ID ) == "gv_slider"){
    unset($columns['cb']);
    unset( $columns['author'] );
    unset( $columns['date'] );

    $columns['gravitation_slider_shortcode'] = __( 'Shortcode', 'gravitation-slider' );

    	return $columns;
    }else{
    	return $columns;
    }
}

function gv_slider_column( $column, $post_id ) {

    switch ( $column ) {
        case 'gravitation_slider_shortcode' :
        	echo '[gravitation_slider ids="' . $post_id . '"]';
            break;
    }
}


function gravitation_slider_shortcode($atts, $content=null){


extract(shortcode_atts(array(

	    'ids' => '',
	    'category' => '',
		'count' => '-1',
		'order' => 'DESC',
		'orderby' => 'menu_order',

    ), $atts));

	$args = array();
	//Sliders ids [gravitation_slider ids="1"]
	if( $ids ){

		$cids = explode(',', $ids);
		$aids = array();
		foreach($cids as $key => $value){
			$aids[] = $value;
		}
		$count = count($cids);
		$args['post__in'] = implode(',', $aids);

		$args=array(

			'post_type' => 'gv_slider',
			'post__in' => $aids,
			'posts_per_page' => intval($count),
			'order' => $order,
			'orderby' => $orderby,
		);
	}

	$query = new WP_Query($args);


	if(!$count){
		$count = $query->post_count;
	}


    if ($query->have_posts()){

	    $sliderID = get_post_meta($aids[0],'gv-slider-field-4', true);
	    $sliderWidth = get_post_meta($aids[0],'gv-slider-field-5', true);
	    $FillMode = get_post_meta($aids[0],'gv-slider-field-6', true);
		$AutoPlay = get_post_meta($aids[0],'gv-slider-field-7', true);
		$Idle = get_post_meta($aids[0],'gv-slider-field-8', true);
		$SlideDuration = get_post_meta($aids[0],'gv-slider-field-9', true);
		$SlideWidth =  get_post_meta($aids[0],'gv-slider-field-10', true);
		$SlideHeight = get_post_meta($aids[0],'gv-slider-field-11', true);
	    $BulletsShow = get_post_meta($aids[0],'gv-slider-field-12', true);
	    $ArrowsShow = get_post_meta($aids[0],'gv-slider-field-13', true);
	    $ArrowKeyNavigation = get_post_meta($aids[0],'gv-slider-field-14', true);
	    $SwipeEnabled = get_post_meta($aids[0],'gv-slider-field-15', true);


	    $sliderSettings = array(
	    						"sliderID" 				=> $sliderID,
	    						"sliderWidth"        	=> $sliderWidth,
	    						"FillMode" 				=> $FillMode,
	    						"AutoPlay" 				=> $AutoPlay,
	    						"Idle"     				=> $Idle,
	    						"SlideDuration"   		=> $SlideDuration,
	    						"SlideWidth"      		=> $SlideWidth,
	    						"SlideHeight"      		=> $SlideHeight,
	    						"BulletsShow" 			=> $BulletsShow,
	    						"ArrowsShow" 			=> $ArrowsShow,
	    						"ArrowKeyNavigation" 	=> $ArrowKeyNavigation,
	    						"SwipeEnabled"          => $SwipeEnabled
	    						);


	    add_action('get_the_data','gv_slider_dynamics_scripts', 10, 1);
	    do_action('get_the_data', $sliderSettings);

	    $all_slides_meta = NULL;
		$all_slides_meta = get_post_meta( $aids[0], 'gv_slider_group', false );


		if($all_slides_meta != NULL){
		$metaSlideInfo = '';
			foreach($all_slides_meta as $field){
				$metaSlideInfo[] = $field;
			}

			?>
			<div class="gravitation-slider">
			<div id="<?php echo $sliderID; ?>" class="sl-slider-wrapper swipe">
				<div class="sl-slider swipe-wrap">
				<?php for( $i=0; $i < count($metaSlideInfo); $i++ ){

				$imageID =  $metaSlideInfo[$i]['gv-slider-gfield-1'];
				$imgUrl = $imageID;
		        if ( is_numeric( $imageID ) ) {
		            $imageAttachment = wp_get_attachment_image_src( $imageID, 'full' );
		            $imgUrl = $imageAttachment[0];
		        }

					if($i % 2 == 0){
						$slideAnimation = 'data-orientation="horizontal" data-slice1-rotation="-25" data-slice2-rotation="-25" data-slice1-scale="2" data-slice2-scale="2"';

					}else{
						$slideAnimation = ' data-orientation="vertical" data-slice1-rotation="10" data-slice2-rotation="-15" data-slice1-scale="1.5" data-slice2-scale="1.5"';
					}
				?>
				<div class="sl-slide" <?php echo $slideAnimation; ?>>
					<div class="sl-slide-inner text-center" >
						<?php echo '<div class="bg-img bg-img-size bg-img-1" style="background-image: url('.$imgUrl.');"></div>';?>

						<h2><?php echo $metaSlideInfo[$i]['gv-slider-gfield-2'];?></h2>
						<p><?php echo $metaSlideInfo[$i]['gv-slider-gfield-3'];?></p>
					</div>
				</div>
				<?php } ?>
			</div>

				<?php if($ArrowsShow == 2){ ?>
					<nav id="nav-arrows-<?php echo $sliderID;?>" class="nav-arrows">
						<span class="nav-arrow-prev"><?php __('Previous','gravitation-slider'); ?></span>
						<span class="nav-arrow-next"><?php __('Next','gravitation-slider');?></span>
					</nav>
				<?php } ?>
				<?php if( $BulletsShow == 2 ){ ?>
					<nav id="nav-dots-<?php echo $sliderID;?>" class="nav-dots">
						<span class="nav-dot-current"></span>

						<?php for( $i=0; $i < count($metaSlideInfo)-1; $i++ ){ ?>
							<span></span>
						<?php }?>
					</nav>
				<?php } ?>
			</div>
		</div>
		<?php
		}

	}

	wp_reset_query();

}
add_shortcode('gravitation_slider', 'gravitation_slider_shortcode');




add_action('admin_menu' , 'gravitation_slider_help_admin_menu');
function gravitation_slider_help_admin_menu() {
    add_submenu_page('edit.php?post_type=gv_slider', __('Help', 'gravitation-slider'), __('Help', 'gravitation-slider'), 'administrator', basename(__FILE__), 'gravitation_slider_help_page');

}

function gravitation_slider_help_page() { ?>

		<img src="<?php echo WP_PLUGIN_URL.'/gravitation-slider/img/banner-header.png'; ?>" alt="Gravitation Slider">
		<div class="wrap">
			<h2><?php esc_html_e('Help GV. Slider','gravitation-slider'); ?></h2>

				<div id="poststuff">
					<div id='post-body' class='metabox-holder columns-1'>

						<div id="post-body-content">
						<div class="left">


								<p><?php _e('For Gravitation Slider to work you have to create a Slider over <strong>"Add New Slider"</strong>','gravitation-slider'); ?></p>
								<hr>
								<h2><?php _e('Type of shortcodes:','gravitation-slider'); ?></h2>
								<h3><?php _e('Pages and Posts','gravitation-slider'); ?></h3>
								<p><?php _e('It is important that when creating the slider assign an id in the "Slider CSS ID" field.
This way you can include several Gravitation Sliders on pages or posts','gravitation-slider');?></p>
								<p><?php _e('Show all slides of this [id] Gravitation Slider: <strong>[gravitation_slider ids="89"]</strong>','gravitation-slider'); ?></p>
								<hr>
								<h2><?php _e('Including Gravitation Slider on your theme','gravitation-slider'); ?></h2>
								<p><?php _e('This code on your theme <strong>"header.php"</strong>. Note the is_front_page, this way only display Gravitation Slider on your homepage.',''); ?></p>
								<div style="border:1px solid #000; padding:10px; width:400px;background:#ddd;">
							    <p>if( is_front_page() ):</p>
								<p>		echo do_shortcode('[gravitation_slider ids="89"]');</p>
								<p>endif;</p>
								</div>



  			</div>
		</div>
		</div>
				</div>
		</div>
<?php
}


if( ! function_exists( 'gravitation_slider_create_post_type' ) ) :
	function gravitation_slider_create_post_type() {

		$labels = array(
		'name'                => _x( 'GV. Slider', 'Post Type General Name', 'gravitation-slider' ),
		'singular_name'       => _x( 'GV. Slider', 'Post Type Singular Name', 'gravitation-slider' ),
		'menu_name'           => __( 'GV. Slider', 'gravitation-slider' ),
		'name_admin_bar'      => __( 'GV. Slider', 'gravitation-slider' ),
		'parent_item_colon'   => __( 'Parent slider:', 'gravitation-slider' ),
		'all_items'           => __( 'All sliders', 'gravitation-slider' ),
		'add_new_item'        => __( 'Add slider', 'gravitation-slider' ),
		'add_new'             => __( 'Add New Slider', 'gravitation-slider' ),
		'new_item'            => __( 'New slider', 'gravitation-slider' ),
		'edit_item'           => __( 'Edit slider', 'gravitation-slider' ),
		'update_item'         => __( 'Update slider', 'gravitation-slider' ),
		'view_item'           => __( 'View slider', 'gravitation-slider' ),
		'search_items'        => __( 'Search slider', 'gravitation-slider' ),
		'not_found'           => __( 'Slider Not found', 'gravitation-slider' ),
		'not_found_in_trash'  => __( 'Slider Not found in Trash', 'gravitation-slider' ),
	);

	$args = array(
		'label'               => __( 'GV. Slider', 'gravitation-slider' ),
		'description'         => __( 'GV. Slider Creator simple responsive slider items', 'gravitation-slider' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-format-gallery',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'rewrite'             => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => false,
		'query_var' => true,
		'capability_type'     => 'post'
		//'register_meta_box_cb' => 'gravitation_slider_add_post_type_metabox'
	);

		register_post_type( 'gv_slider', $args );
		//flush_rewrite_rules();

}

add_action( 'init', 'gravitation_slider_create_post_type' );



function gravitation_slider_add_post_type_metabox() { // add the meta box
	add_meta_box( 'gravitation_slider_metabox', 'Additionl information about this slider', 'gravitation_slider_metabox', 'gv_slider', 'normal' );
}

function gravitation_slider_metabox() {

		global $post;
		echo '<input type="hidden" name="slider_post_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

}

function gravitation_slider_post_save_meta( $post_id, $post ) {

		 if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		  return;

		if ( ! isset( $_POST['slider_post_noncename'] ) ) {
			return;
		}

		if( !wp_verify_nonce( $_POST['slider_post_noncename'], plugin_basename(__FILE__) ) ) {
			return $post->ID;
		}

		if( !wp_verify_nonce( $_POST['slider_post_noncename'], plugin_basename(__FILE__) ) ) {
			return $post->ID;
		}

		if( ! current_user_can( 'edit_post', $post->ID )){
			return $post->ID;
		}
}
add_action( 'save_post', 'gravitation_slider_post_save_meta', 1, 2 );

endif;


function gravitation_slider_replace_submit_meta_box(){

  $items = array( 'gv_slider' => 'Slider' );

  foreach( $items as $item => $value ){
     remove_meta_box('submitdiv', $item, 'core');
     add_meta_box('submitdiv', sprintf( __('Save/Update %s'), $value ), 'gravitation_slider_submit_meta_box', $item, 'side', 'high');
  }
}
add_action( 'admin_menu', 'gravitation_slider_replace_submit_meta_box' );

function gravitation_slider_submit_meta_box() {
	global $action, $post;
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);
	$items = array( 'gv_slider' => 'Slider' );
	$item = $items[$post_type];

	echo '<div class="submitbox" id="submitpost">';
	echo '<div id="major-publishing-actions">';
	do_action( 'post_submitbox_start' );
	echo '<div id="delete-action">';

	 if ( current_user_can( "delete_post", $post->ID ) ) {
	   if ( !EMPTY_TRASH_DAYS ){
	        $delete_text = __('Delete Slider');
	   }else{
	        $delete_text = __('Delete Slider');
	   }
	   echo  '<a style="position: relative;right: -150px;margin-bottom: 10px;" class="submitdelete deletion button button-large" href="' . get_delete_post_link($post->ID) . '">' . $delete_text . '</a>';
	}

	echo '</div>';
	echo '<div id="publishing-action"><span class="spinner"></span>';

	 if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
	       if ( $can_publish ) :
	        	echo '<input name="original_publish" type="hidden" id="original_publish" value="Save Changes" />';
				submit_button( sprintf( __( 'Save Changes %' ), $item ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) );
			endif;
	} else {
		echo '<input name="original_publish" type="hidden" id="original_publish" value="Update Changes" />';
	    echo '<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="Update Changes" />';
	}
	echo '</div><div class="clear"></div></div></div>';

}

require_once( 'custom-meta-boxes/custom-meta-boxes.php' );

function cmb_sample_metaboxes( array $meta_boxes ) {

	$fields = array(
		array( 'id' => 'gv-slider-field-1',  'name' => 'Slide Image', 'type' => 'image', 'size' => 'height=200&width=690&crop=1', 'show_size' => true, 'cols' => 12 ),
		array( 'id' => 'gv-slider-field-2',  'name' => 'Slide title', 'type' => 'text','cols' => 12 ),
		array( 'id' => 'gv-slider-field-3',  'name' => 'Slide Caption', 'type' => 'wysiwyg', 'options' => array( 'editor_height' => '100' ), 'sortable' => false ,'cols' => 12),

	);

	$group_fields = $fields;
	foreach ( $group_fields as &$field ) {
		$field['id'] = str_replace( 'field', 'gfield', $field['id'] );
	}




	$meta_boxes[] = array(
		'title' => 'Add Images to the Slider',
		'pages' => array('gv_slider'),
		'fields' => array(
			array(
				'id' => 'gv_slider_group',
				'name' => 'Slide settings',
				'type' => 'group',
				'repeatable' => true,
				'sortable' => false,
				'fields' => $group_fields,
				'desc' => '',
				'style' => '',
			)
		)
	);


		$settingsFields = array(

			array( 'id' => 'gv-slider-field-4',   'name' => 'Slider CSS ID', 'type' => 'text', 'default' => 'slider-1-container' ),
			array( 'id' => 'gv-slider-field-5',   'name' => 'Slider  Width', 'type' => 'text_small' , 'default' => '1920'),
			array( 'id' => 'gv-slider-field-6',   'name' => 'Fill mode', 'type' => 'select', 'options' => array( '0'=>'Cover','1'=>'Contain' ) ),
			array( 'id' => 'gv-slider-field-7',   'name' => 'Slider Autoplay', 'type' => 'select', 'options' => array( 'true'=>'Yes','false'=>'No' ) ),
		    array( 'id' => 'gv-slider-field-8',   'name' => 'Slider interval in milliseconds', 'type' => 'text_small' , 'default' => '4000' ),
		    array( 'id' => 'gv-slider-field-9',   'name' => 'Slide duration in milliseconds', 'type' => 'text_small' , 'default' => '800' ),
		    array( 'id' => 'gv-slider-field-10',  'name' => 'Slide Width', 'type' => 'text_small', 'default' => '1920' ),
		    array( 'id' => 'gv-slider-field-11',  'name' => 'Slide Height', 'type' => 'text_small', 'default' => '500' ),
		    array( 'id' => 'gv-slider-field-12',  'name' => 'Show bullets', 'type' => 'select', 'options' => array( '2'=>'Yes','0'=>'No' ) ),
		    array( 'id' => 'gv-slider-field-13',  'name' => 'Show arrows', 'type' => 'select', 'options' => array( '2'=>'Yes','0'=>'No' ) ),
			array( 'id' => 'gv-slider-field-14',  'name' => 'Allow keyboard keys', 'type' => 'select', 'options' => array( 'true'=>'Yes','false'=>'No' ), ),
			array( 'id' => 'gv-slider-field-15',  'name' => 'Swipe touch enabled', 'type' => 'select', 'options' => array( 'true'=>'Yes','false'=>'No' ), ),

		);

		$meta_boxes[] = array(
			'title' => 'Slider Settings',
			'pages' => 'gv_slider',
			'context' => 'side',
			'priority' => 'low',
			'fields' => $settingsFields,
		);

	global $pagenow ;
	$post_id = isset($_GET['post']) ? $_GET['post'] : NULL;

	if($pagenow == 'post.php' && $post_id != NULL){
		$shortCodeValue = '[gravitation_slider ids="'.$post_id.'"]';
		$shortCodeField = array(
			array( 'id' => 'gv-slider-field-16',  'name' => 'Slider shortcode', 'type' => 'text', 'readonly' => true, 'default' => $shortCodeValue ),
		);

		 $meta_boxes[] = array(
			'title' => 'Shortcode',
			'pages' => 'gv_slider',
			'context' => 'side',
			'priority' => 'high',
			'fields' => $shortCodeField,
		);
	}

	return $meta_boxes;

}
add_filter( 'cmb_meta_boxes', 'cmb_sample_metaboxes' );