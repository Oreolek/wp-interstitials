<?php

if ( !defined( 'ABSPATH' ) )
  exit; // Exit if accessed directly

class Uji_Interst_Functions {

  /**
   * Select the ad to show.
   *
   * @param int $id current page ID
   * @param array $ajax an array of AJAX parameters
   *
   * @return int|void returns the ad ID if a suitable ad found
   */
  protected function is_interads( $id, $ajax = null ) {
    // check the timeout for this IP
    if (!$this->check_ad_timeout()) {
      return;
    }

    $args = array(
      'post_type' => 'interads',
      'post_status' => 'publish',
    );

    $queryin = new WP_Query( $args );

    $ad_candidates = array();
    while ( $queryin->have_posts() ) {
      $queryin->the_post();

      //Selected
      $is_as_html = get_post_meta( get_the_ID(), 'include_html', true );
      $is_as_url = get_post_meta( get_the_ID(), 'include_url', true );
      $is_as_post = get_post_meta( get_the_ID(), 'add_posts', true );
      if( empty( $is_as_html ) && empty( $is_as_url ) && empty( $is_as_post ) ) {
        continue;
      }

      //Home Page
      $where = get_post_meta( get_the_ID(), 'where_show', true );

      if ( $where == 'show_home' ) {
        if ( !is_front_page() && !isset($ajax['is_home']) ) {
          continue;
        }elseif ( isset($ajax['is_home']) && !$ajax['is_home'] ) {
          continue;
        }
      }

      //CUSTOM PAGE			
      if (
        $where === 'show_cust' &&
        !is_home() &&
        !is_front_page()
      ) {
        $ads_posts = get_post_meta( get_the_ID(), 'ads_posts', true );
        if ( !empty( $ads_posts ) ) {
          $ids = explode( ",", str_replace(' ', '', $ads_posts) );
          if ( !in_array( $id, $ids ) || isset($ajax['is_home']) ) {
            continue;
          }
        }
      }

      //CUSTOM PAGE NOT HOME
      if (
        $where === 'show_cust' &&
        ( is_home() || is_front_page())
      ) {
        continue;
      }

      $ad_candidates[] = get_the_ID();
    } // endwhile
    if (!empty($ad_candidates)) {
      return $ad_candidates[rand(0, count($ad_candidates) - 1)];
    }
    wp_reset_postdata();
  }

  /**
   * Add impression
   * @since  1.0
   */
  protected function impression( $id ) {
    $num = get_post_meta( $id, 'ads_impressions', true );
    $num = (!empty( $num )) ? (int) $num + 1 : 1;
    update_post_meta( $id, 'ads_impressions', $num );
  }

  /**
   * Get Option
   * @since  1.0
   */
  protected function int_option( $name, $default = NULL ) {
    $val = get_option( $this->token );

    if ( !empty( $val[$name] ) ) {
      return $val[$name];
    } elseif ( $default && !empty( $val[$name] ) ) {
      return $default;
    } else {
      return '';
    }
  }

  /**
   * Is Cache Plugin
   * @since  1.0
   */
  public function is_cached() {
    $is = $this->int_option( 'cache_in', 'no' );
    $chached = ($is == 'yes') ? true : false;
    return $chached;
  }

  /**
   * Ad content with Cache Plugin
   * @since  1.0
   */
  public function inter_ajax_ads() {
    $id = $_POST['id_post'];
    $ajax = ( isset($_POST['is_front']) && $_POST['is_front'] == 1 ) ? array( 'is_home' => true ) : NULL;

    $ad_id = $this->is_interads( $id, $ajax );
    $mess = $this->inter_ads( $id, $ajax );

    if ( !empty( $mess ) && $ad_id ) {
      if( !$this->is_cached() ) $this->impression( $ad_id );
      echo $mess;
    } else if ( empty( $mess ) || !$ad_id ) {
      echo 'none_interads';
    }

    wp_die();
  }

  /**
   * Add impression +
   * @since  1.0
   */
  public function inter_ajax_impress() {
    $id = $_POST['id_ad'];
    $this->impression( $id , true);
    wp_die();
  }

  /**
   * Get Ad Contents
   * @since  1.0
   *
   * @param int $id Ad ID
   * @param string $return Return type.
   *  Can be one of: "content", "title", "close", "timer", "wait".
   *  The "content" type is the default.
   */
  protected function get_interad( $id, $return = 'content' ) {

    switch ( $return ) {
    case 'title':
      $show_it = get_post_meta( $id, 'show_title', true );
      if ( $show_it == 'yes' ) {
        $get_ad = get_post( $id );
        return $get_ad->post_title;
      }
      break;
    case 'close':
      $close = get_post_meta( $id, 'add_close', true );
      return ($close == "yes") ? true : false;
      break;
    case 'timer':
      $timer = get_post_meta( $id, 'show_count', true );
      return ($timer == "yes") ? true : false;
      break;
    case 'wait':
      //$wtimer  = get_post_meta( $id, 'show_count', true );
      $swait = get_post_meta( $id, 'on_wait_time', true );
      return ( $swait == 'yes' ) ? true : false;
      break;
    default:
      return $this->get_content( $id );
    }
  }

  /**
   * Get Ad Contents
   * @since  1.0
   */
  private function get_content( $id ) {

    $cnt_html = get_post_meta( $id, 'include_html', true );
    $cnt_url = get_post_meta( $id, 'include_url', true );
    $cnt_post = get_post_meta( $id, 'add_posts', true );
    $types = array( 'include_html' );


    //is HTML
    if ( $cnt_html ) {
      $get_ad = get_post( $id );
      return do_shortcode( $get_ad->post_excerpt );
    }
    //is HTML
    if ( $cnt_url ) {
      $find_url = false;
      $url_ad = '';
      for ( $x = 1; $x <= 5; $x++ ) {
        $url = get_post_meta( $id, 'ads_link' . $x, true );
        if ( !empty( $url ) && $find_url == false ) {
          $url_ad = $url;
          $find_url = true;
        }
      }
      if ( !empty( $url_ad ) ) {
        $content = '<iframe src="' . $url_ad . '" height="100%" frameborder="0" scrolling="no" id="interads_frame"></iframe> ';
        return $content;
      } else {
        return _( 'None URL Ad found', 'ujinter' );
      }
    }
    //include POST
    if ( $cnt_post ) {
      $post_ids = explode( ",", $cnt_post );
      $post_id = trim( $post_ids[0] );
      $page = get_post( $post_id );
      $content = '<h2>' . $page->post_title . '</h2>';
      $content .= '<p>' . do_shortcode( $page->post_content ) . '</p>';
      return $content;
    }

  }

  /**
   * Checks if the current IP didn't see any ads during the specified timeout.
   * Ensures the user won't be swarmed with ads.
   *
   * @return bool "true" if it's okay to continue
   */
  protected function check_ad_timeout() {
    $ip = (string) $this->get_user_ip();
    $timeout = 24 * 60 * 60; // 1 day
    if (!extension_loaded('memcached')) {
      wp_die('Please install Memcached extension. -- interstitial ads plugin');
      return false;
    }
    $m = new Memcached('ip_pool');
    $m->addServer('localhost', 11211);
    $saw = $m->get('seen_interads_'.$ip);
    if ($saw === false || (time() - $saw) >= $timeout) {
      $m->set('seen_interads_'.$ip, time(), $timeout);
      return true;
    } else {
      return false;
    }
  }

  /**
   * Returns user IP.
   */
  protected function get_user_ip() {
    $ip=$_SERVER['REMOTE_ADDR'];
    // check ip from share internet
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    // check ip is passed from proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $ip;
  }
}
