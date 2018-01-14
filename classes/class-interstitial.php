<?php

if ( !defined( 'ABSPATH' ) )
  exit; // Exit if accessed directly

class Uji_Interst extends Uji_Interst_Functions {

  var $version;
  private $file;
  public $keep;
  public $ad_ajax_html;

  /**
   * __construct function.
   * 
   */
  public function __construct( $file ) {
    $this->token = 'ujinter';
    $this->plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );

    $this->load_plugin_textdomain();
    add_action( 'init', array( &$this, 'load_localisation' ), 0 );

    // Setup post types.
    require_once( 'class-interstitial-admin-settings.php' );
    require_once( 'class-interstitial-admin.php' );
    $this->admin_inter = new Uji_Interst_Admin( $file );

    //Add style
    add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_styles' ) );
    //Add scripts
    add_action( 'wp_footer', array( &$this, 'enqueue_scripts' ) );

    //Add Ads Ajax
    if ( $this->is_cached() ) {
      add_action( 'wp_ajax_inter_ads_action', array( &$this, 'inter_ajax_ads' ) );
      add_action( 'wp_ajax_nopriv_inter_ads_action', array( &$this, 'inter_ajax_ads' ) );
    }
    //Add Ads PHP
    add_action( 'wp_footer', array( &$this, 'inter_ads' ) );
  }

  // End__construct()

  /**
   * Load the plugin's localisation file.
   * @since 1.0
   */
  public function load_localisation() {
    load_plugin_textdomain( 'interstitial-ads', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
  }

  // End load_localisation()

  /**
   * Load the plugin textdomain from the main WordPress "languages" folder.
   * @since  1.0
   */
  public function load_plugin_textdomain() {
    $domain = 'interstitial-ads';
    // The "plugin_locale" filter is also used in load_plugin_textdomain()
    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
  }

  // End load_plugin_textdomain()

  /**
   * Register frontend CSS files.
   * @since  1.0
   * @return void
   */
  public function enqueue_styles() {
    wp_register_style( $this->token . '-interads', esc_url( $this->plugin_url . 'css/interads.css' ), '', '1.0', 'all' );
  }

  // End enqueue_styles()

  /**
   * Register frontend JS files.
   * @since  1.0
   * @return void
   */
  public function enqueue_scripts() {
    wp_register_script( $this->token . '-count', esc_url( $this->plugin_url . 'js/jquery.countdown.js' ), array( 'jquery' ), '1.4.0', true );
    wp_register_script( $this->token . '-interads', esc_url( $this->plugin_url . 'js/interads.js' ), array( 'jquery' ), '1.0', true );
  }

  // End register_script()

  /**
   * Check trigger it
   * @since  1.0
   */
  public function inter_ads( $id = NULL, $ajax = NULL ) {
    wp_reset_postdata();
    if ( $id ) {
      $id_post = $id;
    } else {
      global $post;
      $id_post = $post->ID;
    }
    //AD id
    $ad_id = $this->is_interads( $id_post, $ajax );

    $settings = array(
      'bar_color' => 'bar_color',
      'title_color' => 'title_color',
      'back_color' => 'back_color',
      'cont_width' => 'cont_width',
      'close_name' => 'but_close',
      'show_timer' => 'show_timer',
      'countdown_time' => 'countdown_time',
      'wait_time' => 'wait_time',
      'tra_close' => 'tra_close',
      'tra_wait' => 'tra_wait',
      'tra_seconds' => 'tra_seconds',
      'tra_minutes' => 'tra_minutes',
      'tra_until' => 'tra_until'
    );

    foreach ( $settings as $set => $name ) {
      ${$name} = $this->int_option( $set );
    }

    //Countdown
    $timer = $this->get_interad( $ad_id, 'timer' );
    //Wait time
    $wait = $this->get_interad( $ad_id, 'wait' );
    //Impression
    $this->impression( $ad_id );
    
    $JSinterAds = array(
      'id_post' => $id_post
    );
    if (
      !empty( $wait_time ) &&
      (int) $wait_time > 0 &&
      $wait
    ) {
      $JSinterAds['is_wait'] = $wait_time;
    }

    // ADD val if cached
    if ( $this->is_cached() ) {

      //Timing
      $JSinterAds['is_cached'] = 'true';
      $JSinterAds['is_front'] = is_front_page();
      $JSinterAds['ajaxurl'] = admin_url( 'admin-ajax.php' );

      //Countdown		
      if ( $timer && $show_timer === 'yes') {
        if ( 
          !empty( $countdown_time ) &&
          (int) $countdown_time > 0
        ) {
          wp_enqueue_script( $this->token . '-count' );
          $JSinterAds['is_count'] = $countdown_time;
          $add_wait = (!empty( $wait_time ) && (int) $wait_time > 0 && $wait ) ? (int) $wait_time : 0;
          //$countdown = time() + $add_wait + ( int ) $countdown_time;
          $countdown = $countdown_time;
        }
        if ( !empty( $tra_seconds ) ) {
          $JSinterAds['seconds'] = $tra_seconds;
        }
        if ( !empty( $tra_minutes ) ) {
          $JSinterAds['minutes'] = $tra_minutes;
        }
      }

      wp_enqueue_style( $this->token . '-interads' );
      wp_enqueue_script( $this->token . '-interads' );
      wp_localize_script( $this->token . '-interads', 'interAds', $JSinterAds );
    } // end if cached

    if ( $ad_id ) {
      $html_ad = $style_main = $style_cont = '';

      $cont_width = !empty( $cont_width ) ? 'width:' . $cont_width . 'px;' : '';
      $back_color = !empty( $back_color ) ? 'background:' . $back_color . ';' : '';
      $bar_color  = !empty( $bar_color ) ? 'background:' . $bar_color . ';' : '';
      $tit_color  = !empty( $title_color ) ? 'color:' . $title_color . ';' : '';
      $is_wait    = (!empty( $wait_time ) && (int) $wait_time > 0 && $wait) ? 'display: none;' : '';

      $col_style = !empty( $tit_color ) ? 'style="' . $tit_color . '"' : '';
      $bor_style = !empty( $title_color ) ? 'style="border-color:' . $title_color . ';"' : '';
      $close = $this->get_interad( $ad_id, 'close' ); 
      $bor_style = (!$close ) ? 'style="border:none;"' : 'style="border-color:' . $title_color. ';"';
      $noclose = (!$close ) ? ' iads-noclose' : '';

      if ( !empty( $back_color ) || !empty( $is_wait ) ) {
        $style_main = 'style="' . $back_color . $is_wait . '"';
      }

      if ( !empty( $cont_width ) ) {
        $style_cont = 'style="' . $cont_width . '"';
      }

      if ( !empty( $bar_color ) || !empty( $tit_color ) ) {
        $style_bar = 'style="' . $bar_color . $tit_color . '"';
      }

      //Not caching
      if ( !$this->is_cached() ) {

        //Countdown	
        if ( $timer && $show_timer === 'yes' ) {
          if (
            !empty( $countdown_time ) &&
            (int) $countdown_time > 0
          ) {
            wp_enqueue_script( $this->token . '-count' );
            $JSinterAds['is_count'] = $countdown_time;
            $add_wait = (!empty( $wait_time ) && (int) $wait_time > 0 && $wait ) ? (int) $wait_time : 0;
            //$countdown = time() + $add_wait + ( int ) $countdown_time;
            $countdown = $countdown_time;
          }
          if ( !empty( $tra_seconds ) ) {
            $JSinterAds['seconds'] = $tra_seconds;
          }
          if ( !empty( $tra_minutes ) ) {
            $JSinterAds['minutes'] = $tra_minutes;
          }
        }

        //add JS var for timing
        if ( !empty( $JSinterAds ) ) {
          wp_localize_script( $this->token . '-interads', 'interAds', $JSinterAds );
        }

        wp_enqueue_style( $this->token . '-interads' );
        wp_enqueue_script( $this->token . '-interads' );
      }

      $html_ad .= '<div id="interads" ' . $style_main . '>
        <div id="interads-bar" ' . $style_bar . '>
        <div id="interads-tit" class="interads">' . $this->get_interad( $ad_id, 'title' ) . '</div>
        <div class="interads-close'.$noclose.'">';

      if ( $timer && !empty( $show_timer ) && $show_timer == 'yes' )
        $html_ad .= '<div id="inter-mess" ' . $bor_style . '>
        <span> ' . $tra_wait . ' </span>
        <span data-seconds="' . $countdown . '" class="interads-kkcount-down"></span>
        <span> ' . $tra_until . ' </span>
        </div>';

      if ( $close ) 
        $html_ad .= '<a href="javascript:void(0)" onclick="interads_close();" ' . $col_style . '> ' . $tra_close . ' <span class="ujicon-x1"></span></a>';
      $html_ad .= '</div>
        </div>
        <div id="interads-cnt" ' . $style_cont . '>
        ' . $this->get_interad( $ad_id ) . '
        </div>
        </div>';

      //Post AD content
      if ( !empty( $html_ad ) && !$this->is_cached() ) {
        echo $html_ad; 
      } else if ( !empty( $html_ad ) && $this->is_cached() ) {
        return $html_ad; 
      }
    }
  }
} // End Class
