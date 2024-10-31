<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpseoplugins.org/
 * @since      1.0.0
 *
 * @package    Seo_Keywords
 * @subpackage Seo_Keywords/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Seo_Keywords
 * @subpackage Seo_Keywords/admin
 * @author     WP SEO Plugins <info@wpseoplugins.org>
 */
class Seo_Keywords_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Seo_Keywords_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Seo_Keywords_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_style( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/seo-keywords-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Seo_Keywords_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Seo_Keywords_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_script( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/seo-keywords-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->plugin_name, 'SeoKeywords', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'Seo_Keywords_SavePostNonce' => wp_create_nonce( 'Seo_Keywords_SavePostNonce' ),
            'Seo_Keywords_FolderContentsNonce' => wp_create_nonce( 'Seo_Keywords_FolderContentsNonce' ),
            'Server_Uri' => SEO_KEYWORDS_SITE_URL . SEO_KEYWORDS_SERVER_REQUEST_URI,
            'api_key' => get_option('sc_api_key'),
            'Seo_Keywords_Backend_Url' => WP_SEO_PLUGINS_BACKEND_URL,
            'Seo_Keywords_Site_Url' => SEO_KEYWORDS_SITE_URL,
            'Seo_Keywords_Remote_Server_Uri' => base64_encode( SEO_KEYWORDS_SITE_URL . SEO_KEYWORDS_SERVER_REQUEST_URI )
        ));

	}

    public function enqueue_metabox() {
        add_meta_box("seo-keywords-meta-box", "SEO Keywords", [ $this, "show_metabox"], ["post", "page"], "side", "default", null);
    }

    public function start_session(){
        if (!session_id())
            session_start();
    }

    public function settings_link( $links ){
        // Build and escape the URL.
        $url = esc_url( add_query_arg(
            'page',
            'wp-seo-plugins-login',
            get_admin_url() . 'admin.php'
        ) );
        // Create the link.
        $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
        // Adds the link to the end of the array.
        array_push(
            $links,
            $settings_link
        );
        return $links;
    }

    public function seo_keywords_redirect(){
        if (get_option('seo_keywords_do_activation_redirect', false)) {
            delete_option('seo_keywords_do_activation_redirect');
            $url = esc_url( add_query_arg(
                'page',
                'wp-seo-plugins-login',
                get_admin_url() . 'admin.php'
            ) );
            wp_redirect( $url );
        }
    }

    public function show_metabox() {
        global $post;
        $seo_links_keywords = get_post_meta( $post->ID, 'seo_links_keywords', true);
        $seo_links_keywords_related = get_post_meta( $post->ID, 'seo_links_keywords_related', true);
        $seo_links_keywords_impressions = get_post_meta( $post->ID, 'seo_links_keywords_impressions', true);
        $seo_links_keywords_impressions = $seo_links_keywords_impressions != '' ? $seo_links_keywords_impressions : array();

        $seo_links_keywords_clicks = get_post_meta( $post->ID, 'seo_links_keywords_clicks', true );
        $seo_links_keywords_clicks = $seo_links_keywords_clicks != '' ? $seo_links_keywords_clicks : array();

        $seo_links_keywords_position = get_post_meta( $post->ID, 'seo_links_keywords_position', true );
        $seo_links_keywords_position = $seo_links_keywords_position != '' ? $seo_links_keywords_position : array();

        $seo_links_last_update = get_option( 'seo_links_last_update' );
        $sc_api_key = get_option('sc_api_key');
        $credits = $this->wp_seo_plugins_get_credits();
        include_once 'partials/seo-keywords-admin-metabox.php';
    }

    public function save_post() {
        if( !current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Not enough privileges.' );
            wp_die();
        }

        if ( ! check_ajax_referer( 'Seo_Keywords_SavePostNonce', 'nonce', false ) ) {
            wp_send_json_error( 'Invalid security token sent.' );
            wp_die();
        }

        $post_id = (int) sanitize_text_field( isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0 );
        $post_content = isset( $_POST['post_content'] ) ? $_POST['post_content'] : '' ;

        if( $post_id > 0 && $post_content != '' ) {
            $_this_post = get_post( $post_id );
            $_this_post->post_content = stripslashes( apply_filters( 'the_content', $post_content ) );
            wp_update_post( $_this_post );
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public function folder_contents() {
        if( !current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Not enough privileges.' );
            wp_die();
        }

        if ( ! check_ajax_referer( 'Seo_Keywords_FolderContentsNonce', 'nonce', false ) ) {
            wp_send_json_error( 'Invalid security token sent.' );
            wp_die();
        }

        $post_id = sanitize_text_field( $_POST['post_id'] );
        $sc_api_key = get_option('sc_api_key');

        $content_post = get_post( $post_id );
        $content = $content_post->post_content;

        $content = apply_filters('the_content', $content);
        $content = str_replace("’", "'", $content);
        $content = str_replace('“', '"', $content);
        $content = str_replace('”', '"', $content);
        $content = html_entity_decode( $content );

        $title = strtolower( $content_post->post_title );
        $permalink = get_the_permalink( $post_id );

        $server_uri = SEO_KEYWORDS_SITE_URL . SEO_KEYWORDS_SERVER_REQUEST_URI;
        $remote_get = WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole/seoKeywordsLoadData?api_key=' . $sc_api_key . '&domain=' . SEO_KEYWORDS_SITE_URL . '&remote_server_uri=' . base64_encode( $server_uri );

        $args = array(
            'timeout'     => 30,
            'sslverify' => false
        );
        $data = wp_remote_get( $remote_get, $args );

        $rowData = json_decode( $data['body'] );

        if( $rowData->status == -1 || $rowData->status == -2 || $rowData->status == -3 || $rowData->status == -4 ){
            wp_die(json_encode($rowData));
        }

        $keyword_processed = array();
        $replaced = array();
        $keyword_position = array();
        $keyword_impressions = array();
        $keyword_clicks = array();
        $most_relevant_keyword = array(); // Tutte le keyword escluse quelle della url corrente
        $seo_link_keywords = array(); // Tutte le keyword della url corrente - match puntuale
        $seo_link_keywords_related = array(); // Tutte le keyword della url corrente - matchano le categorie e la root del sito
        $internal_link_keywords_filtered = array(); // Tutte le keyword che matchano il titolo con il filtro sul numero di caratteri delle parole
        $words = array();
        $ga_url_processed = array();


        function cmp($a, $b) {
            if ( strlen( $a->page ) == strlen( $b->page ) ) {
                return rand( -1, 1 );
            }
            return ( strlen( $a->page ) < strlen( $b->page ) ) ? -1 : 1;
        }

        uasort($rowData, 'cmp');

        foreach($rowData as $row){
            $ga_url = $row->page;
            $ga_key = $row->query;
            $ga_key = str_replace("’", "'", $ga_key);
            $ga_key = str_replace('“', '"', $ga_key);
            $ga_key = str_replace('”', '"', $ga_key);

            // Match just the ga_url and the permalink
            if( $ga_url == $permalink ){
                $seo_link_keywords[] = $ga_key;
            }

            $ga_url_explode = array_filter( explode('/', str_replace( site_url(), '', $ga_url )) );
			$ga_url_post_name = array_pop( $ga_url_explode );
			if( strpos( $permalink, $ga_url_post_name ) !== false ) {
				$seo_link_keywords_related[] = $ga_key;
            }

            $keyword_position[$ga_key] = $row->position;
            $keyword_impressions[$ga_key] += $row->impressions;
            $keyword_clicks[$ga_key] += $row->clicks;
            $keyword_processed[] = $ga_key;
        }

        $seo_link_keywords_related = array_diff( $seo_link_keywords_related, $seo_link_keywords );

        update_post_meta( $content_post->ID, 'seo_links_keywords', $seo_link_keywords);
        update_post_meta( $content_post->ID, 'seo_links_keywords_related', $seo_link_keywords_related);
        update_post_meta( $content_post->ID, 'seo_links_keywords_filtered', $internal_link_keywords_filtered);
        update_post_meta( $content_post->ID, 'internal_links_keywords_filtered', $internal_link_keywords_filtered);
        update_post_meta( $content_post->ID, 'seo_links_keywords_position', $keyword_position);
        update_post_meta( $content_post->ID, 'seo_links_keywords_impressions', $keyword_impressions);
        update_post_meta( $content_post->ID, 'seo_links_keywords_clicks', $keyword_clicks);
        update_post_meta( $content_post->ID, 'most_relevant_keyword', $most_relevant_keyword);
        update_post_meta( $content_post->ID, 'seo_links_all_keywords', $keyword_processed);

        die( json_encode( array(
            'status' => 0,
            'replaced' => $replaced,
            'post_content' => $content,
            'seo_links_keywords' => $seo_link_keywords,
            'seo_links_keywords_related' => $seo_link_keywords_related,
            'seo_links_keywords_filtered' => $internal_link_keywords_filtered,
            'internal_links_keywords_filtered' => $internal_link_keywords_filtered,
            'seo_links_keywords_position' => $keyword_position,
            'seo_links_keywords_impressions' => $keyword_impressions,
            'seo_links_keywords_clicks' => $keyword_clicks,
            'most_relevant_keyword' => $most_relevant_keyword,
            'seo_links_all_keywords' => $keyword_processed,
            'words' => $words,
            'ga_url_processed' => $ga_url_processed
        ) ) );
    }

    private function wp_seo_plugins_get_credits() {
        $sc_api_key = get_option('sc_api_key');
        if( !empty( $sc_api_key ) ) {
            $server_uri = ( sanitize_text_field( $_SERVER['SERVER_PORT'] ) == 80 ? 'http://' : 'https://' ) . sanitize_text_field( $_SERVER['SERVER_NAME'] )  . sanitize_text_field( $_SERVER['REQUEST_URI'] );
            $remote_get = WP_SEO_PLUGINS_BACKEND_URL . 'apikey/credits?api_key=' . $sc_api_key . '&domain=' . ( sanitize_text_field( $_SERVER['SERVER_PORT'] ) == 80 ? 'http://' : 'https://' ) . sanitize_text_field( $_SERVER['SERVER_NAME'] ) . '&remote_server_uri=' . base64_encode( $server_uri );

            $args = array(
                'timeout'     => 30,
                'sslverify' => false
            );
            $data = wp_remote_get( $remote_get, $args );
            if( is_array( $data ) && !is_wp_error( $data ) ) {
                $rowData = json_decode( $data['body'] );

                $response = $rowData->response;
                $credits = new stdClass();
                foreach( $response as $key => $val ) {
                    if( strpos( $key, 'api_limit') !== false ) {
                        $p = str_replace( 'api_limit_', '', $key );
                        $api_limit = $response->{ $key } ?? 0;
                        $api_call = $response->{ 'api_call_' . $p } ?? 0;
                        $credits->{ $p } = $api_limit - $api_call;
                    }
                }

                return $credits;
            } else {
                return 0;
            }
        }
    }

    public function settings(){
        if ( empty ( $GLOBALS['admin_page_hooks']['wp-seo-plugins-login'] ) ) {
            add_menu_page("SEO Plugins", "SEO Plugins", "manage_options", "wp-seo-plugins-login", array($this, "settings_view"), 'dashicons-analytics' );
        }
    }

    public function settings_view(){
        include_once 'partials/seo-keywords-admin-settings.php';
    }

    public function seo_keywords_settings() {
        add_submenu_page('wp-seo-plugins-login', 'Keywords', 'Keywords', 'edit_posts', 'seo-keywords', array( $this, 'seo_keywords_settings_view' ) );
    }

    public function seo_keywords_settings_view() {
        include_once 'partials/seo-keywords-admin-custom-settings.php';
    }

    public function login() {
        $nonce = sanitize_text_field($_POST['security']);
        if(!wp_verify_nonce($nonce,'wp_seo_plugins_login_nonce') || !current_user_can( 'administrator' )){
            wp_redirect( sanitize_text_field( $_SERVER["HTTP_REFERER"] ).'?error=unauthenticated' );
            exit;
        }

        $post_data = array();
        $post_data['email'] = sanitize_text_field( $_POST['email'] ) ?? '';
        $post_data['password'] = sanitize_text_field( $_POST['password'] ) ?? '';

        $args = array(
            'body'        => $post_data,
            'timeout'     => '30',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'cookies'     => array(),
        );
        $response = wp_remote_post( WP_SEO_PLUGINS_BACKEND_URL . 'login', $args );
        $data = json_decode(wp_remote_retrieve_body( $response ));

        $_SESSION['wp_seo_plugins_status'] = $data->status;
        $_SESSION['wp_seo_plugins_message'] = $data->message;

        if($data->status == 0) {
            // Generating a new api key

            $server_uri = SEO_KEYWORDS_SITE_URL;

            $args = array(
                'body'        => array('user_id' => $data->user_id ?? 0),
                'timeout'     => '30',
                'redirection' => '5',
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array(
                    'Siteurl' => $server_uri
                ),
                'cookies'     => array(),
            );
            $response = wp_remote_post( WP_SEO_PLUGINS_BACKEND_URL . 'apikey/generate', $args );
            $data = json_decode(wp_remote_retrieve_body( $response ));

            $_SESSION['wp_seo_plugins_status'] = $data->status;
            $_SESSION['wp_seo_plugins_message'] = $data->message;
            $_SESSION['wp_seo_plugins_api_key'] = $data->api_key ?? '';

            if( $_SESSION['wp_seo_plugins_api_key'] != '' ) {
                update_option('sc_api_key', sanitize_text_field( $_SESSION['wp_seo_plugins_api_key']) );
                $user = $data->user ?? new stdClass();
                update_option('wp_seo_plugins_user_display_name', $user->data->display_name );
                update_option('wp_seo_plugins_user_email', $user->data->user_email );
            }
        } else {

        }

        wp_redirect( admin_url( 'admin.php?page=wp-seo-plugins-login' ) );
        exit;
    }

    public function registration() {
        $nonce = sanitize_text_field($_POST['security']);
        if(!wp_verify_nonce($nonce,'wp_seo_plugins_registration_nonce') || !current_user_can( 'administrator' )){
            wp_redirect( sanitize_text_field( $_SERVER["HTTP_REFERER"] ).'?error=unauthenticated' );
            exit;
        }

        $server_uri = SEO_KEYWORDS_SITE_URL;

        $post_data = array();
        $post_data['name'] = sanitize_text_field( $_POST['name'] ) ?? '';
        $post_data['surname'] = sanitize_text_field( $_POST['surname'] ) ?? '';
        $post_data['email'] = sanitize_email( $_POST['email'] ) ?? '';
        $post_data['password'] = sanitize_text_field( $_POST['password'] ) ?? '';

        $args = array(
            'body'        => $post_data,
            'timeout'     => '30',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
                'Siteurl' => $server_uri
            ),
            'cookies'     => array(),
        );
        $response = wp_remote_post( WP_SEO_PLUGINS_BACKEND_URL . 'registration', $args );
        $data = json_decode(wp_remote_retrieve_body( $response ));

        $_SESSION['wp_seo_plugins_status'] = $data->status;
        $_SESSION['wp_seo_plugins_message'] = $data->message;
        $_SESSION['wp_seo_plugins_api_key'] = $data->api_key ?? '';

        if( $_SESSION['wp_seo_plugins_api_key'] != '' ) {
            update_option('sc_api_key', sanitize_text_field( $_SESSION['wp_seo_plugins_api_key'] ));
            $user = $data->user ?? new stdClass();
            update_option('wp_seo_plugins_user_display_name', $user->data->display_name );
            update_option('wp_seo_plugins_user_email', $user->data->user_email );
        }

        wp_redirect( admin_url( 'admin.php?page=wp-seo-plugins-login' ) );
        exit;
    }

    public function logout() {
        delete_option('sc_api_key');
        delete_option('wp_seo_plugins_user_display_name');
        delete_option('wp_seo_plugins_user_email');
        wp_redirect(admin_url('admin.php?page=wp-seo-plugins-login'));
        exit;
    }

    public function get_last_update() {
        $sc_api_key = get_option('sc_api_key');
        $remote_get = WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole/getLastUpdate?api_key=' . $sc_api_key . '&domain=' . SEOLI_SERVER_NAME;
        $args = array(
            'timeout'     => 30,
            'sslverify' => false
        );
        $data = wp_remote_get( $remote_get, $args );
        if( is_array( $data ) && !is_wp_error( $data ) ) {
            $rowData = json_decode( $data['body'] );

            if( $rowData->status == 0 ) {
                update_option( 'seo_links_last_update', $rowData->data->last_update);
            }
        }
    }

}
