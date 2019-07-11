<?php

/**
 * External Media plugin class.
 */

/**
 * Kuvapankki API External Media Plugin
 */
class KuvapankkiMediaExternalMediaPlugin extends WP_ExternalPluginBase {

  protected static $kuvapankki_loaded = false;

  /**
   * Implements __construct().
   */
  public function __construct() {
    add_action( 'admin_head', array( &$this, 'assets' ) );
  }

  /**
   * {@inheritdoc}
   */
  public function name() {
    return __(KUVAPANKKI_MEDIA_INTEGRATION_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public function weight() {
    return -10;
  }

  /**
   * {@inheritdoc}
   */
  public function importLabel() {
    return __('Import from Kuvapankki');
  }

  /**
   * {@inheritdoc}
   */
  public function chooserLabel() {
    return __('Link to Kuvapankki');
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return 'kuvapankki-file-chooser';
  }

  /**
   * {@inheritdoc}
   */
  public function attributes( $items ) {
    $attributes = array();
    foreach ( $items as $attribute => $value ) {
      if ( $attribute == 'mime-types' ) {
        $mime_types = array();
        foreach ( $value as $exts => $mime_type ) {
          $e = explode( '|', $exts );
          foreach ( $e as $ext ) {
            $mime_types[] = '.' . $ext;
          }
        }
        $attributes[$attribute] = join( ',', $mime_types );
      }
      else {
        $attributes[$attribute] = $value;
      }
    }
    return $this->renderAttributes( $attributes );
  }

  /**
   * {@inheritdoc}
   */
  public function assets() {
    if ( $this::$kuvapankki_loaded ) {
      return;
    }
  
    $this::$kuvapankki_loaded = true;
  }

  /**
   * {@inheritdoc}
   */
  public function configForm() {
    $elements['kuvapankki_url'] = array(
      '#title' => __('Kuvapankki URL'),
      '#type' => 'textfield',
      '#description' => __(''),
      '#placeholder' => __('Kuvapankki URL'),
    );

    $elements['kuvapankki_username'] = array(
      '#title' => __('Kuvapankki Username'),
      '#type' => 'textfield',
      '#description' => __(''),
      '#placeholder' => __('Username'),
    );

    $elements['kuvapankki_password'] = array(
      '#title' => __('Kuvapankki Password'),
      '#type' => 'password',
      '#description' => __(''),
      '#placeholder' => __('Password'),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function download( $file, $filename ) {
    $attachment_id = $this->save_remote_file( $file, get_class($this), $filename );
    if ( ! $attachment = wp_prepare_attachment_for_js( $attachment_id ) ) {
      wp_send_json_error();
    }
    wp_send_json_success( $attachment );
  }

}
