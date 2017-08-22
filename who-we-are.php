<?php
/**
* @package who-we-are
*/

/*
Plugin Name: who-we-are
Plugin URI: https://alexperez.ninja
Description: Composes the who-we-are page
Version: 1.0
Author: Alex Perez
Author USI: https://alexperez.ninja
License: GPLv2 or later
Text Domain: who-we-are
*/

/*
This plugin was made to allow clients to easily create and edit a pre-styled Who We Are page without using any code.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

if ( ! class_exists( 'wwa_plugin' ) ) :

class wwa_plugin {

  public static function init() {

    $wwa = new self();

  }

  public function __construct() {

    $this->define_constants();
    $this->includes();
    $this->setup_actions();
    $this->setup_filters();
    $this->setup_shortcode();
    $this->register_profile();
    $this->register_public_style();

  }

  private function define_constants() {

    define( 'WWA_VERSION',    $this->version );
    define( 'WWA_BASE_URL',   trailingslashit( plugins_url( 'who-we-are' ) ) );
    define( 'WWA_PATH',       plugin_dir_path( __FILE__ ) );

  }

  private function setup_actions() {
    add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 9001 );
  }

  private function setup_filters() {
    add_filter( 'media_view_strings', array( $this, 'custom_media_uploader_tabs' ), 5 );

  }

  private function setup_shortcode() {
    add_shortcode( 'who-we-are', array($this, 'render_public_profiles'));
  }

  private function register_public_style() {

    wp_enqueue_style( 'wwa-public-style', WWA_BASE_URL . 'public.css', false, WWA_VERSION );

  }

  /*******************
   * Define WWA classes
   *********************/
  private function plugin_classes() {

      return array(
          'wwaprofile'             => WWA_PATH . 'wwa.profile.class.php',
      );

  }

  public function render_public_profiles() {

    $args = array(
      'orderby' => 'meta_value_num',
      'meta_key' => 'order_no',
      'order' => 'ASC',
      'post_type' => 'wwa',
      'post_status' => 'publish',
    );

    $profile_query = new WP_Query( $args );

    if ( !$profile_query->have_posts() ) {
      echo "<h2 class='no-profiles'>No profiles yet! Go to the Who We Are plugin to get started.</h2>";
    }

    $html[] = '<div class="wwa-profiles" id="wwa-plugin">';

    while ( $profile_query->have_posts() ) {
      $profile_query->the_post();

      $html[] = '<div class="wwa-profile" id="wwa-id-' . $profile_query->post->ID . '">';
      $html[] = '<div class="wwa-image" style="background-image: url(' . wp_get_attachment_url( $profile_query->post->image ) . ');">';
      $html[] = '</div>';
      $html[] = '<div class="wwa-text">';
      if ($profile_query->post->name) {
        $html[] = '<div class="wwa-name">';
        $html[] = '<span>' . $profile_query->post->name . '</span>';
        $html[] = '</div>';
      }
      if ($profile_query->post->appellation) {
        $html[] = '<div class="wwa-appellation">';
        $html[] = '<span>' . $profile_query->post->appellation . '</span>';
        $html[] = '</div>';
      }
      if ($profile_query->post->email) {
        $html[] = '<div class="wwa-email">';
        $html[] = '<a href="mailto:' . $profile_query->post->email . '"><span>' . $profile_query->post->email . '</span></a>';
        $html[] = '</div>';
      }
      $html[] = '<div class="wwa-links">';
      if ($profile_query->post->pubmed) {
        $html[] = '<a href="' . $profile_query->post->pubmed . '" target="_blank"><span>Pubmed</span></a>';
      }
      if ($profile_query->post->neurotree) {
        $html[] = '<a href="' . $profile_query->post->neurotree . '" target="_blank"><span>Neurotree</span></a>';
      }
      if ($profile_query->post->scholar) {
        $html[] = '<a href="' . $profile_query->post->scholar . '" target="_blank"><span>Google Scholar</span></a>';
      }
      if ($profile_query->post->cv) {
        $html[] = '<a href="' . $profile_query->post->cv . '" target="_blank"><span>CV</span></a>';
      }
      $html[] = '</div>';
      $html[] = '</div>';
      $html[] = '</div>';

    }

    $html[] = '</div>';

    return implode( "\n", $html );

  }

  public function register_admin_menu() {
    $title = apply_filters( 'wwa_menu_title', 'Who We Are' );

    $capability = apply_filters( 'wwa_capability', 'edit_others_posts' );

    $page = add_menu_page( $title, $title, $capability, 'wwa', array(
            $this, 'render_admin_page' ) );

    add_action( 'admin_print_scripts-' . $page, array( $this, 'register_admin_scripts' ) );
    add_action( 'admin_print_styles-' . $page, array( $this, 'register_admin_styles' ) );

  }

  public function register_admin_styles() {

    wp_enqueue_style( 'wwa-admin-styles', WWA_BASE_URL . 'admin.css', false, WWA_VERSION );

    do_action( 'wwa_register_admin_styles' );

  }

  public function register_admin_scripts() {

    // media library dependencies
    wp_enqueue_media();

    // plugin dependencies
    wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
    wp_enqueue_script( 'jquery-ui-sortable', array( 'jquery', 'jquery-ui-core' ) );
    wp_enqueue_script( 'wwa-admin-script', WWA_BASE_URL . 'admin.js', array( 'jquery'), WWA_VERSION );

    $this->localize_admin_scripts();

    do_action( 'wwa_register_admin_scripts' );

  }

    /***********************************************
     * Autoload classes to reduce memory consumption
     **********************************************/
    public function autoload( $class ) {

        $classes = $this->plugin_classes();

        $class_name = strtolower( $class );

        if ( isset( $classes[$class_name] ) && is_readable( $classes[$class_name] ) ) {
            require_once( $classes[$class_name] );
        }

    }

  /***********************
   * Load required classes
   ***********************/
  private function includes() {

      $autoload_is_disabled = defined( 'WWA_AUTOLOAD_CLASSES' ) && WWA_AUTOLOAD_CLASSES === false;

      if ( function_exists( "spl_autoload_register" ) && ! ( $autoload_is_disabled ) ) {

          // >= PHP 5.2 - Use auto loading
          if ( function_exists( "__autoload" ) ) {
              spl_autoload_register( "__autoload" );
          }

          spl_autoload_register( array( $this, 'autoload' ) );

      } else {

          // < PHP5.2 - Require all classes
          foreach ( $this->plugin_classes() as $id => $path ) {
              if ( is_readable( $path ) && ! class_exists( $id ) ) {
                  require_once( $path );
              }
          }

      }

  }

  /**************************
   * Register our profile
   *************************/
  private function register_profile() {

      $profile = new WWAProfile();

  }

  /**********************
   * Localise admin script
   **********************/
  public function localize_admin_scripts() {

      wp_localize_script( 'wwa-admin-script', 'wwa', array(
              'ajaxurl' => admin_url( 'admin-ajax.php' ),
              'addprofile_nonce' => wp_create_nonce( 'wwa_addprofile' ),
              'deleteprofile_nonce' => wp_create_nonce( 'wwa_deleteprofile' ),
              'saveprofiles_nonce' => wp_create_nonce( 'wwa_saveprofiles' ),
              'replaceimage_nonce' => wp_create_nonce( 'wwa_replaceimage' )
          )
      );

  }

/*********************************************
 * Update the tab options in the media manager
 *********************************************/
 public function custom_media_uploader_tabs( $strings ) {

    //update strings
    if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'wwa' ) ) {
        $strings['insertMediaTitle'] = __( "Image", "wwa" );
        $strings['insertIntoPost'] = __( "Add to Profile", "wwa" );

        // remove unwanted menu options

        $strings_to_remove = array(
            'createVideoPlaylistTitle',
            'createGalleryTitle',
            'insertFromUrlTitle',
            'createPlaylistTitle',
        );

        foreach ($strings_to_remove as $string) {
            if (isset($strings[$string])) {
                $strings[$string] = "";
            }
        }
    }

    return $strings;
  }

  private function get_admin_profiles() {

    $args = array(
      'orderby' => 'meta_value_num',
      'meta_key' => 'order_no',
      'order' => 'ASC',
      'post_type' => 'wwa',
      'post_status' => 'publish',
    );

    $profile_query = new WP_Query( $args );

    if ( !$profile_query->have_posts() ) {
      echo "<h2 class='no-profiles'>No profiles yet! Click the Add Profile button to get started.</h2>";
    }

    while ( $profile_query->have_posts() ) {
      $profile_query->the_post();

      ?>
      <div class="wwa-profile" id="wwa-id-<?php echo $profile_query->post->ID ?>">
        <div class="wwa-image" style="background-image: url('<?php echo wp_get_attachment_url( $profile_query->post->image ) ?>');">
        </div>
        <div class="wwa-text zone">
          <input class="wwa-name" placeholder="name" name="name" value="<?php echo $profile_query->post->name ?>" />
          <input class="wwa-appellation" placeholder="appellation/name" name="appellation" value="<?php echo $profile_query->post->appellation ?>" />
          <input class="wwa-email" placeholder="email" name="email" value="<?php echo $profile_query->post->email ?>" />
        </div>
        <div class="wwa-links zone">
          <span>Pubmed</span>
          <input class="wwa-pubmed" placeholder="Pubmed" name="pubmed" value="<?php echo $profile_query->post->pubmed ?>" />
          <span>Neurotree</span>
          <input class="wwa-neurotree" placeholder="Neurotree" name="neurotree" value="<?php echo $profile_query->post->neurotree ?>" />
          <span>Google Scholar</span>
          <input class="wwa-scholar" placeholder="Google Scholar" name="scholar" value="<?php echo $profile_query->post->scholar ?>" />
          <span>CV</span>
          <input class="wwa-cv" placeholder="CV" name="cv" value="<?php echo $profile_query->post->cv ?>" />
        </div>
        <div class="wwa-buttons zone">
          <label for="order-no">Profile Sequence</label>
          <input type="number" id="order-no" min="0" name="order_no" value="<?php echo $profile_query->post->order_no ?>" />
          <button class="wwa-delete-profile-button red" value="<?php echo $profile_query->post->ID ?>">Delete Profile</button>
        </div>
        <input type="hidden" name="id" value="<?php echo $profile_query->post->ID ?>" />
      </div>

      <?php

    }

    wp_reset_query();

    return false;

  }


  public function render_admin_page() {

    ?>

    <div class="wwa-admin">
      <div class="wwa-window">
        <div class="wwa-add-profile">
          <button class="wwa-add-profile-button">Add Profile</button>
        </div>
        <div class="wwa-save-profiles">
          <button class="wwa-save-profiles-button">Save Profiles</button>
        </div>
        <form class="wwa-profiles">

          <?php $this->get_admin_profiles(); ?>

        </form>

        <div class="wwa-save-profiles">
          <button class="wwa-save-profiles-button">Save Profiles</button>
        </div>
      </div>
    </div>

    <?php
  }

}

endif;

add_action( 'plugins_loaded', array( 'wwa_plugin', 'init' ), 10 );

?>
