<?php

if (!defined('ABSPATH')) {
  exit;
}

class WWAProfile {

  public $profile = 0;

  public function set_profile( $id ) {

    $this->profile = get_post( $id );

  }

  /**
   * Register ajax actions
   */
  public function __construct() {

      add_action( 'wp_ajax_create_profile', array( $this, 'ajax_create_profile' ) );
      add_action( 'wp_ajax_delete_profile', array( $this, 'ajax_delete_profile' ) );
      add_action( 'wp_ajax_save_profiles', array( $this, 'ajax_save_profiles' ) );
      add_action( 'wp_ajax_replace_image', array( $this, 'ajax_replace_image' ) );
  }

    /**
   * Return the HTML used to display profile in admin menu
   */
  protected function get_admin_profile() {

      // get the profile image
      $image = wp_get_attachment_url( $this->profile->image );
      $profile_id = $this->profile->ID;
      $name = $this->profile->name;
      $appellation = $this->profile->appellation;
      $email = $this->profile->email;
      $order_no = $this->profile->order_no;

      // profile HTML
      $row  = "<div class='wwa-profile' id='wwa-id-{$profile_id}'>
                <div class='wwa-image' style='background-image: url({$image})'>
                </div>
                <div class='wwa-text zone'>
                  <input class='wwa-name' placeholder='name' name='name' value='{$name}' />
                  <input class='wwa-appellation' placeholder='appellation/name' name='appellation' value='{$appellation}' />
                  <input class='wwa-email' placeholder='email' name='email' value='{$email}' />
                </div>
                <div class='wwa-links zone'>
                  <span>Link URLs</span>
                  <input class='wwa-pubmed' placeholder='Pubmed' name='pubmed' value='{$pubmed}' />
                  <input class='wwa-neurotree' placeholder='Neurotree' name='neurotree' value='{$neurotree}' />
                  <input class='wwa-scholar' placeholder='Google Scholar' name='scholar' value='{$scholar}' />
                  <input class='wwa-cv' placeholder='CV' name='cv' value='{$cv}' />
                </div>
                <div class='wwa-buttons zone'>
                  <label for='order-no'>Profile Sequence</label>
                  <input type='number' id='order-no' min='0' name='order_no' value='{$order_no}' />
                  <button class='wwa-delete-profile-button red' value='{$profile_id}'>Delete Profile</button>
                </div>
                <input type='hidden' name='id' value='{$profile_id}' />
              </div>";

      return $row;

  }

  public function ajax_create_profile() {
    // security check
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'wwa_addprofile' ) ) {
        echo "<div class='wwa-error'>" . __( "Security check failed. Refresh page and try again.", 'wwa' ) . "</div>";
        wp_die();
    }

    $selection = $_POST['selection'];

    if ( is_array( $selection ) && count( $selection ) ) {

      foreach ( $selection as $image_id ) {

        $id = wp_insert_post( array(
          'post_title' => __("New Profile", "wwa"),
          'post_status' => 'publish',
          'post_type' => 'wwa'
        ));

        $this->set_profile( $id );


        add_post_meta( $id, 'image', $image_id, true);
        add_post_meta( $id, 'name', "", true);
        add_post_meta( $id, 'email', "", true);
        add_post_meta( $id, 'appellation', "", true);
        add_post_meta( $id, 'order_no', "0", true);
        add_post_meta( $id, 'pubmed', "", true);
        add_post_meta( $id, 'neurotree', "", true);
        add_post_meta( $id, 'scholar', "", true);
        add_post_meta( $id, 'cv', "", true);


        echo $this->get_admin_profile();

      }
    }

    wp_die();

  }

  public function ajax_delete_profile() {
    // security check
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'wwa_deleteprofile' ) ) {
        echo "<div class='wwa-error'>" . __( "Security check failed. Refresh page and try again.", 'wwa' ) . "</div>";
        wp_die();
    }

    $profile_id = $_POST['selection'];

    if ( ($profile_id) && get_post($profile_id) ) {

      $id = wp_delete_post( $profile_id );

      echo $profile_id;

    }

    wp_die();
  }

  public function ajax_save_profiles() {

    // security check
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'wwa_saveprofiles' ) ) {
        echo "<div class='wwa-error'>" . __( "Security check failed. Refresh page and try again.", 'wwa' ) . "</div>";
        wp_die();
    }

    $data = $_POST['data'];

    $profile_update = [];

    $profile = [];

    // organize form information

    foreach ( $data as $datum ) {
      if ( $datum['name'] == 'id' ) {
        $profile_update[$datum['value']] = $profile; // e.g. 56 => {name => 'alex', email => 'alex@email.com', appellation => 'Grand Poobah'}
        $profile = [];
      } else {
        $profile[$datum['name']] = $datum['value'];
      }
    }

    // update the profile metadata

    foreach ( $profile_update as $profile => $profile_metadata) {
      foreach ( $profile_metadata as $key => $value ) {
        update_post_meta( $profile, $key, $value );
      }
    }

    echo "Profile updates successful";

    wp_die();
  }

  public function ajax_replace_image() {

    // security check
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'wwa_replaceimage' ) ) {
        echo "<div class='wwa-error'>" . __( "Security check failed. Refresh page and try again.", 'wwa' ) . "</div>";
        wp_die();
    }

    $image = $_POST['selection'][0];

    $profile_id = $_POST['profile_id'];

    // update the image

    update_post_meta( $profile_id, 'image', $image );

    echo "<div class='wwa-image' style='background-image: url(" . wp_get_attachment_url($image) . ");'></div>";

    wp_die();
  }





}

 ?>
