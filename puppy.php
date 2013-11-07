<?php
/*
Plugin Name: Puppy
Description: Puppy is plugin that creates a non-instrusive pop-up to send your visitors to another interesting post. Social network buttons too!
Version: 2.1
Author: Aleksander Hansson
Author URI: http://ahansson.com
Demo: http://puppy.ahansson.com
Tags: extension
v3: true
*/

class Puppy {

	function __construct() {

		add_action( 'wp_footer', array( &$this, 'show' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'js' ) );
		add_action( 'template_redirect', array( &$this,'custom_less' ) );
		add_action( 'wp_head', array( &$this,'head' ) );
		add_action( 'init', array( &$this, 'shortcode' ) );
		add_filter( 'pless_vars', array( &$this, 'mixin' ) );
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'init', array( &$this, 'ah_updater_init' ) );

	}

	function ah_updater_init() {

		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/plugin-updater.php' );

		$config = array(
			'base'      => plugin_basename( __FILE__ ), 
			'repo_uri'  => 'http://shop.ahansson.com',  
			'repo_slug' => 'puppy',
		);

		new AH_Puppy_Plugin_Updater( $config );
	}

	function init() {

		add_filter( 'pl_settings_array', array(&$this, 'options') );

	}

	function head() {

		?>
			<script type="text/javascript">

				jQuery(document).ready(function() {

				    jQuery(".puppy-fire").waypoint(function() {
						jQuery('#puppy-container').animate({
							'bottom': 10
						}, 2000, 'easeOutBounce').css({'display':'block'});
					}, {
						offset: 'bottom-in-view'
					});

					jQuery('.puppy-close').click(function () {
						jQuery('#puppy-container').css({'visibility':'hidden','opacity':'0','transition':'visibility 0s 1s, opacity 1s linear'});
					});

					Socialite.load(jQuery(this)[0]);

				});

			</script>
		<?php
	}

	function show() {

		$array = ( pl_setting( 'puppy_posts_array' ) ) ? array( pl_setting( 'puppy_posts_array' ) ) : '';

		if ( pl_setting( 'puppy_posts' ) == 'specific' ) {
			if ($array) {
				if ( is_single($array) || is_page($array) ) {
					$this->container();
				}
			}
		} elseif ( pl_setting( 'puppy_posts' ) == 'posts' ) {
			if ( is_single() || is_page() ) {
				$this->container();
			}
		} elseif ( pl_setting( 'puppy_posts' ) == 'frontpage' ) {
			if ( is_front_page() ) {
				$this->container();
			}
		} else {
			$this->container();
		}
	}

	function container() {

		$title = pl_setting( 'puppy_title' ) ? pl_setting( 'puppy_title' ) : '<div class="puppy-default-title">This is Puppy!</div><h5 class="puppy-title-instructions center">Go to: </br> Global Options -> Puppy </br> to setup Puppy.</h5>';

		?>
			<div id="puppy-container" class="hidden-phone">
				<div class="puppy-outer">
					<div class="puppy-close">X</div>
						<h3 class="puppy-title puppy-content">
							<?php echo $title; ?>
						</h3>
					<div class="puppy-inner">
						<?php if (pl_setting('puppy_enable_random_post')==true) { ?>
							<?php $this->random_post(); ?>
						<?php } ?>
						<?php if ( pl_setting('puppy_enable_social') ) { ?>
							<?php $this->social_buttons(); ?>
						<?php } ?>
						<?php if ( pl_setting('puppy_enable_button_modal') ) { ?>
							<?php $this->button(); ?>
						<?php } ?>
						<?php if ( pl_setting('puppy_custom_content') ) { ?>
							<div class="puppy-custom">
								<?php $this->custom(); ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php
		$this->modal();
	}

	function js() {

		wp_enqueue_script( 'jquery' );

		$socialite = sprintf( '%s/%s/%s', WP_PLUGIN_URL, basename(dirname( __FILE__ )), 'js/socialite.js' );

		wp_enqueue_script( 'social_excerpts_socialite', $socialite );

		$viewport = sprintf( '%s/%s/%s', WP_PLUGIN_URL, basename(dirname( __FILE__ )), 'js/viewport.min.js' );

		wp_enqueue_script( 'jquery-viewport', $viewport );

		$params = array(
			'seappid' => pl_setting( 'facebook_appid' ) ? sprintf( '&appid=%s', pl_setting( 'facebook_appid' ) ) : null,
			'selang' => pl_setting( 'facebook_language' ) ? pl_setting( 'facebook_language' ) : 'en_US'
		);

		wp_localize_script( 'social_excerpts_socialite', 'social_excerpts', $params );

	}

	function custom_less() {

		$file = sprintf( '%sstyle.less', plugin_dir_path( __FILE__ ) );

		pagelines_insert_core_less( $file );

	}

	function random_post() {

		$post_id = get_the_ID();

		$args = array(
		   'orderby' => 'rand',
		   'posts_per_page' => 1,
		   'post_not_in' => array( $post_id )
		);

		$rand_query = new WP_Query( $args );

		?>
			<div class="puppy-random-post puppy-content">
				<?php if ( pl_setting('puppy_text_above_random_post') ) { ?>
					<h5 class="puppy-header">
						<?php echo pl_setting('puppy_text_above_random_post'); ?>
					</h5>
				<?php } ?>
				<?php
					while ( $rand_query->have_posts() ) : $rand_query->the_post();
						echo '<a href="' . get_permalink() . '"><div class="puppy-random-link center">' . $this->thumbnail() . '</div></a>';
					endwhile;
				?>
			</div>
		<?php
	}

	function thumbnail() {
		ob_start();
		if ( has_post_thumbnail() ) {
			?>
				<li>
					<?php
						the_post_thumbnail( array(50,50) );
					?>
				</li>
				<li>
					<?php
						the_title();
					?>
				</li>
			<?php
		} else {
			?>
				<li>
					<?php
						the_title();
					?>
				</li>
			<?php
		}
		$output = ob_get_clean();
		return $output;

	}

	function social_buttons() {
		?>
			<div class="puppy-like puppy-content">
				<?php if (pl_setting('puppy_text_above_social')) { ?>
					<h5 class="puppy-header">
						<?php echo pl_setting('puppy_text_above_social'); ?>
					</h5>
				<?php } ?>
				<div class="puppy-buttons-wrapper">
					<ul class="social-buttons cf">
						<?php if ((pl_setting('puppy_enable_facebook')==true && pl_setting('puppy_facebook_like_link'))) { ?>
							<li class="puppy-facebook-button"><a href="http://www.facebook.com/sharer.php" class="socialite facebook-like" data-href="<?php echo pl_setting('puppy_facebook_like_link'); ?>" data-send="false" data-layout="button_count" data-show-faces="false" rel="nofollow" target="_blank"></a></li>
						<?php } ?>
						<?php if ((pl_setting('puppy_enable_linkedin')==true && pl_setting('puppy_linkedin_company_id'))) { ?>
							<li class="puppy-linkedin-button"><a href="http://www.linkedin.com/shareArticle?mini=true" data-id="<?php echo pl_setting('puppy_linkedin_company_id'); ?>" class="socialite linkedin-follow" data-counter="none" rel="nofollow" target="_blank"></a></li>
						<?php } ?>
						<?php if ((pl_setting('puppy_enable_twitter')==true && pl_setting('puppy_twitter_follow_link'))) { ?>
							<li class="puppy-twitter-button"><a href="<?php echo pl_setting('puppy_twitter_follow_link'); ?>" class="socialite twitter-follow" data-show-count="false" rel="nofollow" target="_blank"></a></li>
						<?php } ?>
					</ul>
				</div>
			</div>
		<?php
	}

	function button() {
		$button_text = pl_setting('puppy_button_text') ? pl_setting('puppy_button_text') : 'Please input button text in Puppy settings';
		$button_type = pl_setting('puppy_button_type') ? sprintf('%s', pl_setting('puppy_button_type') ): '';

		?>
			<div class="puppy-button puppy-content">
				<?php if ( pl_setting('puppy_text_above_button') ) { ?>
					<h5 class="puppy-header">
						<?php echo pl_setting('puppy_text_above_button'); ?>
					</h5>
				<?php } ?>
				<!-- Button to trigger modal -->
				<div class="puppy-button-button"><a href="#puppy_modal" role="button" class="btn <?php echo $button_type; ?>" data-toggle="modal"><?php echo $button_text; ?></a></div>

			</div>
		<?php
	}

	function modal() {
		$modal_header = pl_setting('puppy_modal_header') ? pl_setting('puppy_modal_header') : 'Please input Modal header in Puppy Settings';
		?>
			<!-- Modal -->
			<div id="puppy_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="puppy_modal_header"><?php echo $modal_header; ?></h3>
				</div>
				<div class="modal-body">
					<?php $this->modal_body(); ?>
				</div>
			</div>
		<?php
	}

	function modal_body() {
		$puppy_modal_content = pl_setting( 'puppy_modal_content' );
		if ($puppy_modal_content) {
			$c = do_shortcode( $puppy_modal_content );
			echo $c;
		}
	}

	function custom() {
		$puppy_custom_content = pl_setting( 'puppy_custom_content' );
		if ($puppy_custom_content) {
			$c = do_shortcode( $puppy_custom_content );
			echo $c;
		}
	}

	function random_post_shortcode_markup() {
		ob_start();
		$this->random_post();
		$output = ob_get_clean();
		return $output;
	}

	function social_buttons_shortcode_markup() {
		ob_start();
		$this->social_buttons();
		$output = ob_get_clean();
		return $output;
	}

	function button_shortcode_markup() {
		ob_start();
		$this->button();
		$output = ob_get_clean();
		return $output;
	}

	function shortcode() {
		add_shortcode('puppyrandom', array(&$this,'random_post_shortcode_markup' ));
		add_shortcode('puppysocial', array(&$this,'social_buttons_shortcode_markup' ));
		add_shortcode('puppybutton', array(&$this,'button_shortcode_markup' ));

	}

	function mixin( $constants ){

		$facebook_width = pl_setting( 'puppy_facebook_width') ? sprintf ('%spx', pl_setting( 'puppy_facebook_width' )) : '92px';
		$puppy_width = pl_setting( 'puppy_width') ? sprintf ('%spx', pl_setting( 'puppy_width' )) : '325px';


		$newvars = array(

			'puppy_facebook_width' => $facebook_width,
			'puppy_width' => $puppy_width,

		 );

		 $lessvars = array_merge($newvars, $constants);

		 return $lessvars;

	}

	function options( $settings ){

		$how_to_use = '<strong>Read the instructions below before asking for additional help:</strong></br></br>
			In update 2.0 I made some changes in the way Puppy is firing. It is now done with jQuery Waypoints which means that you need to add a custom class to a section. The script will now fire when this section enters the viewport (your screen). </br></br>
			<strong>1.</strong> Find the section you want to trigger your Puppy. </br>
			<strong>2.</strong> Edit the section option. </br>
			<strong>3.</strong> Add "puppy-fire" to the custom class field. </br>
			<strong>4.</strong> Puplish settings.</br>
			<strong>5.</strong> Done. Puppy should now fire when your chosen section becomes visible on the screen. </br></br>

			<div class="row zmb">
				<div class="span6 tac zmb">
					<a class="btn btn-info" href="http://forum.pagelines.com/71-products-by-aleksander-hansson/" target="_blank" style="padding:4px 0 4px;width:100%"><i class="icon-ambulance"></i>          Forum</a>
				</div>
				<div class="span6 tac zmb">
					<a class="btn btn-info" href="http://betterdms.com" target="_blank" style="padding:4px 0 4px;width:100%"><i class="icon-align-justify"></i>          Better DMS</a>
				</div>
			</div>
			<div class="row zmb" style="margin-top:4px;">
				<div class="span12 tac zmb">
					<a class="btn btn-success" href="http://shop.ahansson.com" target="_blank" style="padding:4px 0 4px;width:100%"><i class="icon-shopping-cart" ></i>          My Shop</a>
				</div>
			</div>

		';

        $settings['Puppy'] = array(
            'name'  => 'Puppy',
            'icon'  => ' icon-heart-empty',
            'pos'   => 5,
            'opts'  => array(

				array(
					'key' => 'puppy_help',
					'type'     => 'template',
					'template'      => $how_to_use,
					'title' => __( 'How to use:', 'puppy' ),
				),

				array(
					'key' => 'puppy_where',
					'type'     => 'multi',
					'title'      => __( 'Where to show?', 'puppy' ),
					'opts'   => array(
						array(
							'key' => 'puppy_posts',
							'default'       => 'posts',
							'type'           => 'select',
							'label'  =>  __('Where to show?', 'puppy'),
							'opts'     => array(
								'frontpage' => array( 'name' => __( 'Frontpage'   , 'puppy' )),
								'posts' => array( 'name' => __( 'Posts and Pages'   , 'puppy' )),
								'specific' => array( 'name' => __( 'Specific IDs'   , 'puppy' )),
								'everywhere' => array( 'name' => __( 'Everywhere'   , 'puppy' ))
							),
						),

						array(
							'key' => 'puppy_posts_array',
							'type'     => 'text',
							'label'  => __( 'Show on following post or page ID', 'puppy' ),
							'help'      => __( 'Only use this setting if "Specific IDs" is set above. Only show on following post IDs. For multiple use a comma seperator. Like this "17, 18, 90"', 'puppy' )
						),
					),
				),

				array(
					'key' => 'puppy_width',
					'type'     => 'text',
					'label'  => __( 'Puppy Width', 'puppy' ),
					'title'      => __( 'Puppy Width', 'puppy' ),
					'help'      => __( 'How wide do you want the container?', 'puppy' )
				),

				array(
					'key' => 'puppy_title',
					'type'     => 'text',
					'label'  => __( 'Puppy title', 'puppy' ),
					'title'      => __( 'Puppy title', 'puppy' ),
					'help'      => __( 'Input your title for Puppy', 'puppy' )
				),

				array(
					'key' => 'puppy_random',
					'type'     => 'multi',
					'title'      => __( 'Random Post Settings', 'puppy' ),
					'help'      => __( 'Type in your settings', 'puppy' ),
					'opts'   => array(
						array(
							'key' => 'puppy_enable_random_post',
							'default'  => false,
							'type'   => 'check',
							'label' => __( 'Enable Random Post', 'puppy' ),
						),
						array(
							'key' => 'puppy_text_above_random_post',
							'label'  => __( 'Text above Random Post', 'puppy' ),
							'type'   => 'text'
						),
					),
				),

				array(
					'key' => 'puppy_social',
					'type'     => 'multi',
					'title'      => __( 'Social Buttons Settings', 'puppy' ),
					'help'      => __( 'Type in your settings', 'puppy' ),
					'opts'   => array(
						array(
							'key' => 'puppy_enable_social',
							'default'  => false,
							'type'   => 'check',
							'label' => __( 'Enable Social buttons', 'puppy' ),
						),
						array(
							'key' => 'puppy_text_above_social',
							'label'  => __( 'Text above social buttons', 'puppy' ),
							'type'   => 'text'
						),
						array(
							'key' => 'puppy_enable_facebook',
							'default'  => false,
							'type'   => 'check',
							'label' => __( 'Enable Facebook button', 'puppy' ),
						),
						array(
							'key' => 'puppy_facebook_like_link',
							'label'  => __( 'Link to Like on Facebook  (remember http://)', 'puppy' ),
							'type'   => 'text'
						),
						array(
							'key' => 'facebook_appid',
							'default'   =>  '',
							'type'    =>  'text',
							'label'  =>  __('Your Facebook App ID: (App ID is used to track likes with Facebook Opengraph)', 'puppy'),
						),
						array(
							'key' => 'puppy_facebook_width',
							'type'     => 'text',
							'label'  => __( 'Facebook button width', 'puppy' ),
							'help'      => __( 'How wide do you want your Facebook button?', 'puppy' )
						),
						array(
							'key' => 'puppy_enable_twitter',
							'default'  => true,
							'type'   => 'check',
							'label' => __( 'Enable Twitter button', 'puppy' ),
						),
						array(
							'key' => 'puppy_twitter_follow_link',
							'label'  => __( 'Link to your Twitter profile (remember http://)', 'puppy' ),
							'type'   => 'text'
						),
						array(
							'key' => 'puppy_enable_linkedin',
							'default'  => true,
							'type'   => 'check',
							'label' => __( 'Enable LinkedIn button', 'puppy' ),
						),
						array(
							'key' => 'puppy_linkedin_company_id',
							'label'  => __( "Your company's LinkedIn page ID - Find your ID with <a href='https://developer.linkedin.com/apply-getting-started#company-lookup' target='_blank'>this tool</a>", 'puppy' ),
							'type'   => 'text'
						),
					),
				),

				array(
					'key' => 'puppy_modal',
					'type'     => 'multi',
					'title'      => __( 'Button with Modal Settings', 'puppy' ),
					'help'      => __( 'Type in your settings', 'puppy' ),
					'opts'   => array(
						array(
							'key' => 'puppy_enable_button_modal',
							'default'  => false,
							'type'   => 'check',
							'label' => __( 'Enable Button & Modal', 'puppy' ),
						),
						array(
							'key' => 'puppy_text_above_button',
							'label'  => __( 'Text above button', 'puppy' ),
							'type'   => 'text'
						),
						array(
							'key' => 'puppy_button_text',
							'label'  => __( 'Button Text', 'puppy' ),
							'type'   => 'text'
						),
						array(
							'key' => 'puppy_button_type',
							'default'       => 'btn-info',
							'type'           => 'select_button',
							'label'  =>  __('Button type', 'puppy'),
						),
						array(
							'key' => 'puppy_modal_header',
							'label'  => __( 'Modal Header', 'puppy' ),
							'type'   => 'text'
						),
						array(
							'key' => 'puppy_modal_content',
							'label'  => __( 'Modal Content', 'puppy' ),
							'type' 			=> 'textarea',
							'inputsize'		=> 'big',
						),
					),
				),

				array(
					'key' => 'puppy_custom_content',
					'label'  => __( 'Custom content', 'puppy' ),
					'type' 			=> 'textarea',
					'inputsize'		=> 'big',
					'title'      => __( 'Custom content', 'puppy' ),
					'help'      => __( 'You can add custom content for Puppy here', 'puppy' )
				),
			),
        );

        return $settings;
    }

}

new Puppy;