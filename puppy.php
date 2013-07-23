<?php
/*
Plugin Name: Puppy
Description: Puppy is plugin that creates a non-instrusive pop-up to send your visitors to another interesting post. Social network buttons too!
Version: 1.0.0
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
		add_action( 'init', array( &$this, 'settings' ) );
		add_action( 'init', array( &$this, 'shortcode' ) );
		add_filter( 'pless_vars', array( &$this, 'mixin' ) );

	}

	function head() {
		?>
			<script type="text/javascript">

				jQuery(document).ready(function($) {

					Socialite.load($(this)[0]);

				});

			</script>
		<?php
	}

	function show() {

		if (ploption( 'puppy_posts' )) {
			if (is_single() || is_page()) {
				$this->container();
			}
		} else {
			$this->container();
		}
	}

	function container() {

		$title = ploption( 'puppy_title' ) ? ploption( 'puppy_title' ) : '<div class="puppy-default-title">This is Puppy!</div><h5 class="puppy-title-instructions center">Go to: </br> PageLines -> Global Options -> Puppy </br> to setup Puppy.</h5>';

		?>
			<div id="puppy-container" class="hidden-phone">
				<div class="puppy-outer">
					<div class="puppy-close">X</div>
						<h3 class="puppy-title puppy-content">
							<?php echo $title; ?>
						</h3>
					<div class="puppy-inner">
						<?php if (ploption('puppy_enable_random_post')==true) { ?>
							<?php $this->random_post(); ?>
						<?php } ?>
						<?php if ( ploption('puppy_enable_social') ) { ?>
							<?php $this->social_buttons(); ?>
						<?php } ?>
						<?php if ( ploption('puppy_enable_button_modal') ) { ?>
							<?php $this->button(); ?>
						<?php } ?>
						<?php if ( ploption('puppy_custom_content') ) { ?>
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

		$jquery_easing = sprintf( '%s/%s/%s', WP_PLUGIN_URL, basename(dirname( __FILE__ )), 'js/jquery.easing.1.3.js' );

		wp_enqueue_script( 'jquery-easing', $jquery_easing );

		$script = sprintf( '%s/%s/%s', WP_PLUGIN_URL, basename(dirname( __FILE__ )), 'js/script.js' );

		wp_enqueue_script( 'puppy_script', $script );

		$params_scroll = array(
			'scroll' => ploption( 'puppy_scroll' ) ? ploption( 'puppy_scroll' ) : 300
		);

		wp_localize_script( 'puppy_script', 'puppy_script_params', $params_scroll );

		$socialite = sprintf( '%s/%s/%s', WP_PLUGIN_URL, basename(dirname( __FILE__ )), 'js/socialite.js' );

		wp_enqueue_script( 'social_excerpts_socialite', $socialite );

		$params = array(
			'seappid' => ploption( 'facebook_appid' ) ? sprintf( '&appid=%s', ploption( 'facebook_appid' ) ) : null,
			'selang' => ploption( 'facebook_language' ) ? ploption( 'facebook_language' ) : 'en_US'
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
				<?php if ( ploption('puppy_text_above_random_post') ) { ?>
					<h5 class="puppy-header">
						<?php echo ploption('puppy_text_above_random_post'); ?>
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
				<?php if (ploption('puppy_text_above_social')) { ?>
					<h5 class="puppy-header">
						<?php echo ploption('puppy_text_above_social'); ?>
					</h5>
				<?php } ?>
				<div class="puppy-buttons-wrapper">
					<ul class="social-buttons cf">
						<?php if ((ploption('puppy_enable_facebook')==true && ploption('puppy_facebook_like_link'))) { ?>
							<li class="puppy-facebook-button"><a href="http://www.facebook.com/sharer.php" class="socialite facebook-like" data-href="<?php echo ploption('puppy_facebook_like_link'); ?>" data-send="false" data-layout="button_count" data-show-faces="false" rel="nofollow" target="_blank"></a></li>
						<?php } ?>
						<?php if ((ploption('puppy_enable_linkedin')==true && ploption('puppy_linkedin_company_id'))) { ?>
							<li class="puppy-linkedin-button"><a href="http://www.linkedin.com/shareArticle?mini=true" data-id="<?php echo ploption('puppy_linkedin_company_id'); ?>" class="socialite linkedin-follow" data-counter="none" rel="nofollow" target="_blank"></a></li>
						<?php } ?>
						<?php if ((ploption('puppy_enable_twitter')==true && ploption('puppy_twitter_follow_link'))) { ?>
							<li class="puppy-twitter-button"><a href="<?php echo ploption('puppy_twitter_follow_link'); ?>" class="socialite twitter-follow" data-show-count="false" rel="nofollow" target="_blank"></a></li>
						<?php } ?>
					</ul>
				</div>
			</div>
		<?php
	}

	function button() {
		$button_text = ploption('puppy_button_text') ? ploption('puppy_button_text') : 'Please input button text in Puppy settings';
		$button_type = ploption('puppy_button_type') ? sprintf('%s', ploption('puppy_button_type') ): ' btn-primary';

		?>
			<div class="puppy-button puppy-content">
				<?php if ( ploption('puppy_text_above_button') ) { ?>
					<h5 class="puppy-header">
						<?php echo ploption('puppy_text_above_button'); ?>
					</h5>
				<?php } ?>
				<!-- Button to trigger modal -->
				<div class="puppy-button-button"><a href="#puppy_modal" role="button" class="btn<?php echo $button_type; ?>" data-toggle="modal"><?php echo $button_text; ?></a></div>

			</div>
		<?php
	}

	function modal() {
		$modal_header = ploption('puppy_modal_header') ? ploption('puppy_modal_header') : 'Please input Modal header in Puppy Settings';
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
		$puppy_modal_content = ploption( 'puppy_modal_content' );
		if ($puppy_modal_content) {
			$c = do_shortcode( $puppy_modal_content );
			echo $c;
		}
	}

	function custom() {
		$puppy_custom_content = ploption( 'puppy_custom_content' );
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

		$facebook_width = ploption( 'puppy_facebook_width') ? sprintf ('%spx', ploption( 'puppy_facebook_width' )) : '92px';
		$puppy_width = ploption( 'puppy_width') ? sprintf ('%spx', ploption( 'puppy_width' )) : '325px';


		$newvars = array(

			'puppy_facebook_width' => $facebook_width,
			'puppy_width' => $puppy_width,

		 );

		 $lessvars = array_merge($newvars, $constants);

		 return $lessvars;

	}

	function settings() {
		// options array for creating the settings tab
		$options = array(

			'puppy_scroll'  => array(
				'type'     => 'text',
				'inputlabel'  => __( 'Script fire', 'puppy' ),
				'title'      => __( 'Script fire', 'puppy' ),
				'shortexp'      => __( 'How many pixels from the bottom should the script fire? (default is 300)', 'puppy' )
			),

			'puppy_posts'  => array(
				'default'       => true,
				'type'           => 'select',
				'selectvalues'     => array(
					true => array( 'name' => __( 'Yes'   , 'puppy' )),
					false => array( 'name' => __( 'No'   , 'puppy' ))
				),
				'inputlabel'  =>  __('Only show on posts and pages? (default is "No")', 'puppy'),
				'title'      => __( 'Where to show?', 'puppy' ),
				'shortexp'      => __( 'Only show on posts and pages?', 'puppy' )
			),

			'puppy_width'  => array(
				'type'     => 'text',
				'inputlabel'  => __( 'Puppy Width', 'puppy' ),
				'title'      => __( 'Puppy Width', 'puppy' ),
				'shortexp'      => __( 'How wide do you want the container?', 'puppy' )
			),

			'puppy_title'  => array(
				'type'     => 'text',
				'inputlabel'  => __( 'Puppy title', 'puppy' ),
				'title'      => __( 'Puppy title', 'puppy' ),
				'shortexp'      => __( 'Input your title for Puppy', 'puppy' )
			),

			'puppy_random_post'  => array(
				'default'    => '',
				'type'     => 'multi_option',
				'selectvalues'   => array(
					'puppy_enable_random_post' => array(
						'default'  => false,
						'type'   => 'check',
						'inputlabel' => __( 'Enable Random Post', 'puppy' ),
					),
					'puppy_text_above_random_post'  => array(
						'inputlabel'  => __( 'Text above Random Post', 'puppy' ),
						'type'   => 'text'
					),
				),
				'title'      => __( 'Random Post Settings', 'puppy' ),
				'shortexp'      => __( 'Type in your settings', 'puppy' )
			),

			'puppy_social_buttons'  => array(
				'default'    => '',
				'type'     => 'multi_option',
				'selectvalues'   => array(
					'puppy_enable_social' => array(
						'default'  => false,
						'type'   => 'check',
						'inputlabel' => __( 'Enable Social buttons', 'puppy' ),
					),
					'puppy_text_above_social'  => array(
						'inputlabel'  => __( 'Text above social buttons', 'puppy' ),
						'type'   => 'text'
					),
					'puppy_enable_facebook' => array(
						'default'  => false,
						'type'   => 'check',
						'inputlabel' => __( 'Enable Facebook button', 'puppy' ),
					),
					'puppy_facebook_like_link'  => array(
						'inputlabel'  => __( 'Link to Like on Facebook  (remember http://)', 'puppy' ),
						'type'   => 'text'
					),
					'facebook_appid' =>  array(
						'default'   =>  '',
						'type'    =>  'text',
						'inputlabel'  =>  __('Your Facebook App ID: (App ID is used to track likes with Facebook Opengraph)', 'puppy'),
					),
					'puppy_facebook_width'  => array(
						'type'     => 'text',
						'inputlabel'  => __( 'Facebook button width', 'puppy' ),
						'shortexp'      => __( 'How wide do you want your Facebook button?', 'puppy' )
					),
					'puppy_enable_twitter' => array(
						'default'  => true,
						'type'   => 'check',
						'inputlabel' => __( 'Enable Twitter button', 'puppy' ),
					),
					'puppy_twitter_follow_link'  => array(
						'inputlabel'  => __( 'Link to your Twitter profile (remember http://)', 'puppy' ),
						'type'   => 'text'
					),
					'puppy_enable_linkedin' => array(
						'default'  => true,
						'type'   => 'check',
						'inputlabel' => __( 'Enable LinkedIn button', 'puppy' ),
					),
					'puppy_linkedin_company_id'  => array(
						'inputlabel'  => __( "Your company's LinkedIn page ID - Find your ID with <a href='https://developer.linkedin.com/apply-getting-started#company-lookup' target='_blank'>this tool</a>", 'puppy' ),
						'type'   => 'text'
					),
				),
				'title'      => __( 'Social Buttons Settings', 'puppy' ),
				'shortexp'      => __( 'Type in your settings', 'puppy' )
			),

			'puppy_button_modal'  => array(
				'default'    => '',
				'type'     => 'multi_option',
				'selectvalues'   => array(
					'puppy_enable_button_modal' => array(
						'default'  => false,
						'type'   => 'check',
						'inputlabel' => __( 'Enable Button & Modal', 'puppy' ),
					),
					'puppy_text_above_button'  => array(
						'inputlabel'  => __( 'Text above button', 'puppy' ),
						'type'   => 'text'
					),
					'puppy_button_text'  => array(
						'inputlabel'  => __( 'Button Text', 'puppy' ),
						'type'   => 'text'
					),
					'puppy_button_type'  => array(
						'default'       => ' btn-info',
						'type'           => 'select',
						'selectvalues'     => array(
							' btn-primary' => array( 'name' => __( 'Primary'   , 'puppy' )),
							' btn-info' => array( 'name' => __( 'Info'   , 'puppy' )),
							' btn-success' => array( 'name' => __( 'Success'   , 'puppy' )),
							' btn-warning' => array( 'name' => __( 'Warning'   , 'puppy' )),
							' btn-danger' => array( 'name' => __( 'Danger'   , 'puppy' )),
							' btn-inverse' => array( 'name' => __( 'Inverse'   , 'puppy' )),
							'' => array( 'name' => __( 'Grey'   , 'puppy' ))
						),
						'inputlabel'  =>  __('Button type', 'puppy'),
					),
					'puppy_modal_header'  => array(
						'inputlabel'  => __( 'Modal Header', 'puppy' ),
						'type'   => 'text'
					),
					'puppy_modal_content'  => array(
						'inputlabel'  => __( 'Modal Content', 'puppy' ),
						'type' 			=> 'textarea',
						'inputsize'		=> 'big',
					),
				),
				'title'      => __( 'Button with Modal Settings', 'puppy' ),
				'shortexp'      => __( 'Type in your settings', 'puppy' )
			),

			'puppy_custom_content'  => array(
				'inputlabel'  => __( 'Custom content', 'puppy' ),
				'type' 			=> 'textarea',
				'inputsize'		=> 'big',
				'title'      => __( 'Custom content', 'puppy' ),
				'shortexp'      => __( 'You can add custom content for Puppy here', 'puppy' )
			),

		);

		// add options page to pagelines settings
		pl_add_options_page(
			array(
				'name' => 'Puppy',
				'array' => $options
			)
		);

	}

}

new Puppy;