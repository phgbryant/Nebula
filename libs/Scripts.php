<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Scripts') ){
	trait Scripts {
		public $brain;

		public function hooks(){
			add_action( 'wp_enqueue_scripts', array($this, 'register_scripts'));
			add_action( 'admin_enqueue_scripts', array($this, 'register_scripts'));
			add_action( 'login_enqueue_scripts', array($this, 'register_scripts'));
			add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_action( 'login_enqueue_scripts', array($this, 'login_enqueue_scripts'));
			add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

			if ( $this->is_debug() || !empty($GLOBALS['wp_customize']) ){
				add_filter('style_loader_src', array($this, 'add_debug_query_arg'), 500, 1);
				add_filter('style_loader_src', array($this, 'add_debug_query_arg'), 500, 1);
			}
		}

		//Register scripts
		public function register_scripts(){
			//Stylesheets
			//wp_register_style($handle, $src, $dependencies, $version, $media);
			wp_register_style('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/6.1.1/jquery.mmenu.all.css', null, '6.1.1', 'all'); //@todo "Nebula" 0: This is causing a weird slowdown on the homepage on WebPageTest.org when testing using the Oregon server... Not a huge issue, but curious: https://github.com/chrisblakley/Nebula/issues/1313
			wp_register_style('nebula-main', get_template_directory_uri() . '/style.css', array('nebula-bootstrap', 'nebula-mmenu'), null, 'all');
			wp_register_style('nebula-login', get_template_directory_uri() . '/assets/css/login.css', null, null);
			wp_register_style('nebula-admin', get_template_directory_uri() . '/assets/css/admin.css', null, null);
			if ( $this->get_option('google_font_url') ){
				wp_register_style('nebula-google_font', $this->get_option('google_font_url'), array(), null, 'all');
			}
			$this->bootstrap('css');
			wp_register_style('nebula-font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', null, '4.7.0', 'all');
			wp_register_style('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css', null, '1.10.15', 'all'); //Datatables is called via main.js only as needed.
			wp_register_style('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.7.0/chosen.min.css', null, '1.7.0', 'all');
			wp_register_style('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.structure.min.css', null, '1.12.1', 'all');
			wp_register_style('nebula-pre', get_template_directory_uri() . '/assets/css/pre.css', null, null);
			wp_register_style('nebula-flags', get_template_directory_uri() . '/assets/css/flags.css', null, null);

			//Scripts
			//Use CDNJS to pull common libraries: http://cdnjs.com/
			//nebula_register_script($handle, $src, $exec, $dependencies, $version, $in_footer);
			$this->jquery();
			$this->bootstrap('js');
			$this->register_script('nebula-modernizr_dev', get_template_directory_uri() . '/assets/js/vendor/modernizr.dev.js', 'defer', null, '3.3.1', false);
			$this->register_script('nebula-modernizr_local', get_template_directory_uri() . '/assets/js/vendor/modernizr.min.js', 'defer', null, '3.3.1', false);
			$this->register_script('nebula-modernizr', 'https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js', 'defer', null, '2.8.3', false); //https://github.com/cdnjs/cdnjs/issues/6100
			$this->register_script('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', 'defer', null, '1.12.1', true);
			$this->register_script('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/6.1.1/jquery.mmenu.all.js', 'defer', null, '6.1.1', true);
			$this->register_script('nebula-vimeo', 'https://player.vimeo.com/api/player.js', null, null, null, true);
			$this->register_script('nebula-tether', 'https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js', 'defer', null, '1.4.0', true); //This is not enqueued or dependent because it is called via main.js only as needed.
			$this->register_script('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js', 'defer', null, '1.10.15', true); //Datatables is called via main.js only as needed.
			$this->register_script('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.7.0/chosen.jquery.min.js', 'defer', null, '1.7.0', true);
			$this->register_script('nebula-autotrack', 'https://cdnjs.cloudflare.com/ajax/libs/autotrack/2.4.1/autotrack.js', 'async', null, '2.4.1', true);
			$this->register_script('performance-timing', get_template_directory_uri() . '/assets/js/libs/performance-timing.js', 'defer', null, null, false);
			$this->register_script('nebula-main', get_template_directory_uri() . '/assets/js/main.js', 'defer', array('nebula-bootstrap', 'jquery-core', 'nebula-jquery_ui', 'nebula-mmenu'), null, true);
			$this->register_script('nebula-login', get_template_directory_uri() . '/assets/js/login.js', null, array('jquery-core'), null, true);
			$this->register_script('nebula-admin', get_template_directory_uri() . '/assets/js/admin.js', 'defer', null, null, true);

			global $wp_scripts, $wp_styles, $upload_dir;
			$upload_dir = wp_upload_dir();

			//Prep Nebula styles for JS object
			$nebula_styles = array();
			foreach ( $wp_styles->registered as $handle => $data ){
				if ( strpos($handle, 'nebula-') !== false && strpos($handle, 'admin') === false && strpos($handle, 'login') === false ){ //If the handle contains "nebula-" but not "admin" or "login"
					$nebula_styles[str_replace(array('nebula-', '-'), array('', '_'), $handle)] = str_replace(array('?defer', '?async'), '', $data->src);
				}
			}

			//Prep Nebula scripts for JS object
			$nebula_scripts = array();
			foreach ( $wp_scripts->registered as $handle => $data ){
				if ( strpos($handle, 'nebula-') !== false && strpos($handle, 'admin') === false && strpos($handle, 'login') === false ){ //If the handle contains "nebula-" but not "admin" or "login"
					$nebula_scripts[str_replace(array('nebula-', '-'), array('', '_'), $handle)] = str_replace(array('?defer', '?async'), '', $data->src);
				}
			}

			//Be careful changing the following array as many JS functions use this data!
			$this->brain = array(
				'site' => array(
					'name' => get_bloginfo('name'),
					'directory' => array(
						'root' => get_site_url(),
						'template' => array(
							'path' => get_template_directory(),
							'uri' => get_template_directory_uri(),
						),
						'stylesheet' => array(
							'path' => get_stylesheet_directory(),
							'uri' => get_stylesheet_directory_uri(),
						),
					),
					'home_url' => home_url(),
					'sw_url' => $this->sw_location(),
					'cache' => $this->get_sw_cache_name(),
					'domain' => $this->url_components('domain'),
					'protocol' => $this->url_components('protocol'),
					'language' => get_bloginfo('language'),
					'ajax' => array(
						'url' => admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('nebula_ajax_nonce'),
					),
					'upload_dir' => $upload_dir['baseurl'],
					'ecommerce' => false,
					'options' => array(
						'sw' => $this->get_option('service_worker'),
						'gaid' => $this->get_option('ga_tracking_id'),
						'nebula_cse_id' => $this->get_option('cse_id'),
						'nebula_google_browser_api_key' => $this->get_option('google_browser_api_key'),
						'facebook_url' => $this->get_option('facebook_url'),
						'facebook_app_id' => $this->get_option('facebook_app_id'),
						'twitter_url' => $this->get_option('twitter_url'),
						'google_plus_url' => $this->get_option('google_plus_url'),
						'linkedin_url' => $this->get_option('linkedin_url'),
						'youtube_url' => $this->get_option('youtube_url'),
						'instagram_url' => $this->get_option('instagram_url'),
						'manage_options' => current_user_can('manage_options'),
						'debug' => $this->is_debug(),
						'visitors_db' => $this->get_option('visitors_db'),
						'hubspot_api' => ( $this->get_option('hubspot_api') )? true : false,
						'sidebar_expanders' => get_theme_mod('sidebar_accordion_expanders', true),
					),
					'resources' => array(
						'css' => $nebula_styles,
						'js' => $nebula_scripts,
					),
				),
				'post' => ( is_search() )? null : array( //Conditional prevents wrong ID being used on search results
					'id' => get_the_id(),
					'permalink' => get_the_permalink(),
					'title' => urlencode(get_the_title()),
					'excerpt' => $this->excerpt(array('words' => 100, 'more' => '', 'ellipsis' => false, 'strip_tags' => true)),
					'author' => urlencode(get_the_author()),
					'year' => get_the_date('Y'),
				),
				'dom' => null,
			);

			//Check for session data
			if ( isset($_SESSION['nebulaSession']) && json_decode($_SESSION['nebulaSession'], true) ){ //If session exists and is valid JSON
				$this->brain['session'] = json_decode($_SESSION['nebulaSession'], true); //Replace nebula.session with session data
			} else {
				$this->brain['session'] = array(
					'ip' => $_SERVER['REMOTE_ADDR'],
					'id' => $this->nebula_session_id(),
					'flags' => array(
						'adblock' => false,
						'gablock' => false,
					),
				);
			}

			$user_info = get_userdata(get_current_user_id());

			//User Data
			$this->brain['user'] = array(
				'id' => get_current_user_id(),
				'data' => array(
					'display_name' => $user_info->data->display_name,
					'email' => $user_info->data->user_email,
				),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'nid' => $this->get_nebula_id(),
				'cid' => $this->ga_parse_cookie(),
				'client' => array( //Client data is here inside user because the cookie is not transferred between clients.
					'bot' => $this->is_bot(),
					'remote_addr' => $_SERVER['REMOTE_ADDR'],
					'device' => array(
						'full' => $this->get_device('full'),
						'formfactor' => $this->get_device('formfactor'),
						'brand' => $this->get_device('brand'),
						'model' => $this->get_device('model'),
						'type' => $this->get_device('type'),
					),
					'os' => array(
						'full' => $this->get_os('full'),
						'name' => $this->get_os('name'),
						'version' => $this->get_os('version'),
					),
					'browser' => array(
						'full' => $this->get_browser('full'),
						'name' => $this->get_browser('name'),
						'version' => $this->get_browser('version'),
						'engine' => $this->get_browser('engine'),
						'type' => $this->get_browser('type'),
					),
				),
			);
		}

		//Enqueue frontend scripts
		function enqueue_scripts($hook){
			//Stylesheets
			wp_enqueue_style('nebula-bootstrap');
			wp_enqueue_style('nebula-mmenu');
			wp_enqueue_style('nebula-main');
			wp_enqueue_style('nebula-font_awesome');
			if ( $this->get_option('google_font_url') ){
				wp_enqueue_style('nebula-google_font');
			}
			wp_enqueue_style('nebula-jquery_ui');

			//Scripts
			wp_enqueue_script('jquery-core');
			wp_enqueue_script('nebula-jquery_ui');

			if ( $this->get_option('device_detection') ){
				//wp_enqueue_script('nebula-modernizr_dev');
				//wp_enqueue_script('nebula-modernizr_local'); //@todo "Nebula" 0: Switch this back to CDN when version 3 is on CDNJS
			}

			wp_enqueue_script('nebula-mmenu');
			wp_enqueue_script('nebula-bootstrap');
			wp_enqueue_script('nebula-autotrack');
			wp_enqueue_script('nebula-main');

			//Localized objects (localized to jquery to appear in <head>)
			wp_localize_script('jquery-core', 'nebula', $this->brain);

			//Conditionals
			if ( $this->is_debug() ){ //When ?debug query string is used
				wp_enqueue_script('nebula-performance_timing');
				//wp_enqueue_script('nebula-mmenu_debugger');
			}

			if ( is_page_template('tpl-search.php') ){ //Form pages (that use selects) or Advanced Search Template. The Chosen library is also dynamically loaded in main.js.
				wp_enqueue_style('nebula-chosen');
				wp_enqueue_script('nebula-chosen');
			}
		}

		//Enqueue login scripts
		function login_enqueue_scripts($hook){
			//Stylesheets
			wp_enqueue_style('nebula-login');
			echo '<style>
	div#login h1 a {background: url(' . get_theme_file_uri('/assets/img/logo.png') . ') center center no-repeat; width: auto; background-size: contain;}
		.svg div#login h1 a {background: url(' . get_theme_file_uri('/assets/img/logo.svg') . ') center center no-repeat; background-size: contain;}
</style>';

			//Scripts
			wp_enqueue_script('jquery-core');
			wp_enqueue_script('nebula-login');
		}

		//Enqueue admin scripts
		function admin_enqueue_scripts($hook){
			$current_screen = get_current_screen();

			//Stylesheets
			wp_enqueue_style('nebula-admin');
			wp_enqueue_style('nebula-font_awesome');

			if ( $this->ip_location() ){
				wp_enqueue_style('nebula-flags');
			}

			//Scripts
			wp_enqueue_script('nebula-admin');

			//Nebula Options page
			$current_screen = get_current_screen();
			if ( $current_screen->base === 'appearance_page_nebula_options' || $current_screen->base === 'options' ){
				wp_enqueue_style('nebula-bootstrap');
				$this->override_bootstrap_tether();
				wp_enqueue_script('nebula-bootstrap');
			}

			//Nebula Visitors Data page
			if ( $current_screen->base === 'appearance_page_nebula_visitors_data' ){
				wp_enqueue_style('nebula-bootstrap');
				wp_enqueue_style('nebula-pre');
				wp_enqueue_style('nebula-datatables');
				wp_enqueue_script('nebula-datatables');
			}

			//User Profile edit page
			if ( $current_screen->base === 'profile' ){
				wp_enqueue_style('thickbox');
				wp_enqueue_script('thickbox');
				wp_enqueue_script('media-upload');
			}

			//Localized objects (localized to jquery to appear in <head>)
			wp_localize_script('jquery-core', 'nebula', $this->brain);
		}

		//Get fresh resources when debugging
		public function add_debug_query_arg($src){
			return add_query_arg('debug', str_replace('.', '', $this->version('raw')) . '-' . rand(1000, 9999), $src);
		}
	}
}