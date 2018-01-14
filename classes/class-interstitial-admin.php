<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Uji_Interst_Admin extends Uji_Interst_Admin_API{
  var $version;
  private $file;
  public static $plugin_url;
  public static $plugin_path;

  /**
   * __construct function.
   * 
   */
  public function __construct ( $file ) {
    parent::__construct(); // Required in extended classes.

    $this->token = 'ujinter';
    $this->page_slug = 'ujiinter-api';
    $this->opt_name = __( 'Interstitial Ads Options', 'ujinter' );

    $this->post_meta = 'interads_meta';

    self::$plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
    self::$plugin_path = trailingslashit( dirname( $file ) );

    $this->labels = array();
    $this->setup_post_type_labels_base();

    //Add Post Type
    add_action( 'init', array( &$this, 'add_post_type_ads' ), 100 );

    //Add Columns
    add_filter( 'manage_edit-interads_columns', array( &$this, 'add_column_headings' ), 10, 1 );
    add_action( 'manage_posts_custom_column', array( &$this, 'add_column_data' ), 10, 2 );

    //Change Ad Title Here
    add_filter( 'enter_title_here', array( &$this, 'change_default_title' ) );

    //add admin .css
    add_action( 'admin_enqueue_scripts', array( &$this, 'admin_styles_interads' ) );
    //add admin .js
    add_action( 'admin_print_scripts', array( &$this, 'admin_enqueue_scripts' ) );

    //add menu
    add_action( 'admin_menu', array( &$this, 'ujinter_menu' ) );

    //Metaboxes
    add_action( 'add_meta_boxes', array( &$this, 'interads_meta_boxes' ) );

    //AdminInit::Save Post
    add_action( 'save_post', array( &$this, 'interads_save') );	
  }


  /**
   * Setup the singular, plural and menu label names for the post types.
   * @since  1.0.0
   * @return void
   */
  private function setup_post_type_labels_base () {
    $this->labels = array( 'interads' => array() );

    $this->labels['interads'] = array( 'singular' => __( 'Ad', 'ujinter' ), 'plural' => __( 'Ads', 'ujinter' ), 'menu' => __( 'Interstitial Ads', 'ujinter' ) );
  } // End setup_post_type_labels_base()

  /**
   * Setup the "Interstitial Ads" post type
   * @since  1.0.0
   * @return void
   */
  public function add_post_type_ads () {
    $args = array(
      'labels' => $this->create_post_type_labels( 'interads', $this->labels['interads']['singular'], $this->labels['interads']['plural'], $this->labels['interads']['menu'] ),
      'public' => false,
      'publicly_queryable' => true,
      'show_ui' => true, 
      'show_in_menu' => true, 
      'query_var' => true,
      'rewrite' => array( 'slug' => 'interads', 'with_front' => false, 'feeds' => false, 'pages' => false ),
      'capability_type' => 'post',
      'has_archive' => false, 
      'hierarchical' => false,
      'menu_position' => 100, // Below "Pages"
      'menu_icon' => esc_url( self::$plugin_url . 'images/icon_interads.png' ), 
      'supports' => array( 'title' )
    );

    register_post_type( 'interads', $args );
  } // End setup_zodiac_post_type()


  /**
   * Add column headings to the "slides" post list screen.
   * @access public
   * @since  1.0.0
   */
  public function add_column_headings ( $defaults ) {
    $new_columns['cb'] = '<input type="checkbox" />';
    // $new_columns['id'] = __( 'ID' );
    $new_columns['title'] = _x( 'Ads Title', 'column name', 'ujinter' );
    $new_columns['valability'] = _x( 'Valability', 'column name', 'ujinter' );
    $new_columns['impress'] = _x( 'Impressions', 'column name', 'ujinter' );

    if ( isset( $defaults['date'] ) ) {
      $new_columns['date'] = $defaults['date'];
    }

    return $new_columns;
  } // End add_column_headings()

  /**
   * Add data for our newly-added custom columns.
   * @access public
   * @since  1.0.0
   */
  public function add_column_data ( $column_name, $id ) {
    global $wpdb, $post;

    switch ( $column_name ) {
    case 'id':
      echo $id;
      break;

    case 'impress':
      $num = get_post_meta( $id, 'ads_impressions', true );
      echo ( !empty($num) ) ? $num : 0;
      break;

    default:
      break;
    }
  } // End add_column_data()

  /**
   * Labels for post type
   * @since  1.0.0
   * @return void
   */
  private function create_post_type_labels ( $token, $singular, $plural, $menu ) {
    $labels = array(
      'name' => sprintf( _x( '%s', 'post type general name', 'ujinter' ), $plural ),
      'singular_name' => sprintf( _x( '%s', 'post type singular name', 'ujinter' ), $singular ),
      'add_new' => sprintf( _x( 'Add New %s', $token, 'ujinter' ), $singular ),
      'add_new_item' => sprintf( __( 'Add New %s', 'ujinter' ), $singular ),
      'edit_item' => sprintf( __( 'Edit %s', 'ujinter' ), $singular ),
      'new_item' => sprintf( __( 'New %s', 'ujinter' ), $singular ),
      'all_items' => sprintf( __( 'All %s', 'ujinter' ), $plural ),
      'view_item' => sprintf( __( 'View %s', 'ujinter' ), $singular ),
      'search_items' => sprintf( __( 'Search %s', 'ujinter' ), $plural ),
      'not_found' =>  sprintf( __( 'No %s found', 'ujinter' ), strtolower( $plural ) ),
      'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'ujinter' ), strtolower( $plural ) ), 
      'parent_item_colon' => '',
      'menu_name' => $menu
    );

    return $labels;
  } // End create_post_type_labels()


  /**
   * Load the global admin styles for the menu icon and the relevant page icon.
   * @access public
   * @since 1.0.0
   * @return void
   */
  public function admin_styles_interads () {
    $screen = get_current_screen();

    if (in_array( $screen->id, array( 'interads', 'interads_page_ujiinter-api' ))) {
      wp_register_style( 'admin-interads', self::$plugin_url . 'css/admin.css', '', '1.0', 'screen' );
      wp_register_style( 'bootstrap', self::$plugin_url . 'assets/bootsrap/css/bootstrap.css', '', '2.0', 'screen' );
      wp_register_style( 'colorpicker', self::$plugin_url . 'assets/colorpicker/css/colorpicker.css', '', '1.0', 'screen' );
      wp_register_style( 'datapicker', self::$plugin_url . 'assets/datepicker/css/datepicker.css', '', '1.0', 'screen' );
      wp_enqueue_style(  'admin-interads' );
      wp_enqueue_style(  'bootstrap' );
      if ( floatval(get_bloginfo('version')) >= 3.5){
        wp_dequeue_style( 'colorpicker' );
        wp_dequeue_style( 'color-picker' );
        wp_dequeue_style( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );
      } else {
        wp_enqueue_style( 'colorpicker' );
      }
      wp_enqueue_style(  'datapicker' );

    };

  } // End admin_styles_global()

  /**
   * enqueue_scripts function.
   *
   * @description Load in JavaScripts where necessary.
   */
  public function admin_enqueue_scripts () {

    $screen = get_current_screen();


    if (in_array( $screen->id, array( 'interads', 'interads_page_ujiinter-api' ))) {

      wp_enqueue_script( 'bootstrap', self::$plugin_url . 'assets/bootsrap/js/bootstrap.min.js', array( 'jquery' ), '2.0' );
      wp_enqueue_script( 'bootstrap-color', self::$plugin_url . 'assets/colorpicker/js/bootstrap-colorpicker.js', array( 'jquery' ), '1.0' );
      wp_enqueue_script( 'bootstrap-date', self::$plugin_url . 'assets/datepicker/js/bootstrap-datepicker.js', array( 'jquery' ), '1.0' );
      if ( floatval(get_bloginfo('version')) >= 3.5){
        wp_enqueue_script( 'wp-color-picker');
      }
      wp_enqueue_script( 'interads', self::$plugin_url . 'js/admin-interads.js', array( 'jquery', 'jqueryui' ), '1.0' );

    };

  } // End enqueue_scripts()

  /**
   * Change Title
   * @since  1.0
   */
  public function change_default_title( $title ){
    $screen = get_current_screen();

    if  ( 'interads' == $screen->post_type ) {
      $title = 'Enter Ad Title Here';
    }

    return $title;
  }

  /**
   * Remove it if already exist
   * @since  1.0
   */
  public function remove_add( ){
    // $screen = get_current_screen();
    // if  ( 'interads' == $screen->post_type ) {
    $published_posts = wp_count_posts( 'interads' );

    if( (int) $published_posts->publish > 0 ){
      remove_submenu_page( 'edit.php?post_type=interads', 'post-new.php?post_type=interads' );
      add_action('admin_footer', array( &$this, 'add_footer_css' ) );
    }
    //}
  }

  /**
   * Add footer CSS
   * @since  1.0
   */
  public function add_interads_css( ){
    echo '<style type="text/css">
      #favorite-actions {display:none;}
      .add-new-h2{display:none;}
  .tablenav{display:none;}
  .post-type-interads .wp-list-table tr:not(:first-child){ display: none; }
  .post-type-interads .subsubsub, .post-type-interads .search-box{ display: none; }
  </style>';
  }

  /**
   * Add footer CSS One
   * @since  1.0
   */
  public function add_interads_css_one( ){
    echo '<style type="text/css">
      #wp-admin-bar-new-interads {display:none;}

      </style>';
  }

  /**
   * Add footer CSS Two
   * @since  1.0
   */
  public function add_interads_css_two( ){
    echo '<style type="text/css">
      #wp-admin-bar-new-interads {display:none;}
      #menu-posts-interads ul li:nth-child(3) {display:none;}
      </style>';
  }


  /**
   * Add menu
   * @since  1.0
   */
  public function ujinter_menu() {
    $hook = add_submenu_page( 'edit.php?post_type=interads', __( 'Interstitial Ads', 'ujinter' ), __('Ad Options', 'ujinter'), 'manage_options', $this->page_slug, array( &$this, 'settings_screen' ) );
    if ( isset( $_GET['page'] ) && ( $_GET['page'] == $this->page_slug ) ) {
      add_action( 'admin_notices', array( &$this, 'settings_errors' ) );
    }
  }

  /**
   * Add metaboxes
   * @since  1.0
   */
  public function interads_meta_boxes() {
    global $post;

    // Excerpt
    if ( function_exists('wp_editor') ) {
      //WP 4 space
      $idp = ( floatval(get_bloginfo('version')) >= 4 ) ? 'postexcerpt_wp4' : 'postexcerpt';
      remove_meta_box( $idp, 'product', 'normal' );
      add_meta_box( $idp, __('Ad Content', 'ujinter'), array( &$this, 'interads_html' ), 'interads', 'normal' );

    }

    add_meta_box( 'postwhere', __('Where to show', 'ujinter'), array( &$this, 'interads_where' ), 'interads', 'normal' );
    add_meta_box( 'postsettings', __('Settings', 'ujiinter'), array( &$this, 'interads_settings' ), 'interads', 'normal' );
    add_meta_box( 'styles', __('Ad Style', 'ujiinter'), array( &$this, 'interads_style' ), 'interads', 'side' );
  }

  /**
   * Add HTML metaboxes
   * @since  1.0
   */
  public function interads_html( $post ) {
?>
    <ul class="nav nav-tabs" id="cont_tab">
                    <li><a href="#int-tab-1" data-toggle="tab"><?php _e("Text/Html", 'ujinter') ?></a></li>
                </ul>
       <div class="tab-content">
<?php
    //TAB1: add editor
    $include = get_post_meta( $post->ID, 'include_html', true );
?>
    <div class="tab-pane" id="int-tab-1">
        <div class="options_group tab-space">
        <p class="form-field">
          <label for="include_html"><?php _e("Included as Ad", 'ujinter') ?></label>  
          <input id="include_html" class="checkbox" type="checkbox" value="yes" name="include_html" <?php checked( $include, 'yes' ) ?>> 
        </p>
    </div>
<?php
    $settings = array(
      'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
      'textarea_name'	=> 'excerpt',
      'quicktags' 	=> true,
      'tinymce' 		=> true,
      'editor_css'	=> '<style>#wp-excerpt-editor-container .wp-editor-area{height:275px; width:100%;}</style>'
    );

    wp_editor( htmlspecialchars_decode( $post->post_excerpt ), 'excerpt', $settings );

    echo '</div>';
?>
              </div>      
<?php            
  }

  /**
   * Where to show
   * @since  1.0
   */
  public function interads_where( $post ) {
    $include = get_post_meta( $post->ID, 'where_show', true );
    $include_categ = get_post_meta( $post->ID, 'ad_post_category', false );
?>	
        <div class="tab-content">

            <!-- checkbox Home Page -->
            <div class="options_group">
                <p class="form-field">
                    <label for="_see_show_home"><?php _e( "Enable on Home Page", 'ujinter' ) ?></label>  
                    <input id="_see_show_home" class="radio" type="radio" value="show_home" name="where_show" <?php checked( $include, 'show_home' ) ?>> 
                    <span class="description"><?php _e( "Show Ad on Home Page", 'ujinter' ) ?></span>
                </p>
            </div>


            <!-- checkbox All Pages -->
            <div class="options_group">
                <p class="form-field">
                    <label for="_see_show_all"><?php _e( "Enable on All Pages", 'ujinter' ) ?></label>  
                    <input id="_see_show_all" class="radio" type="radio" value="show_all" name="where_show" <?php checked( $include, 'show_all' ) ?>> 
                    <span class="description"><?php _e( "Show Ad on entire site", 'ujinter' ) ?></span>
                </p>
            </div>

            <!-- checkbox Custom Pages -->
            <div class="options_group">
                <p class="form-field">
                    <label for="_see_show_cust"><?php _e( "Enable on Custom Pages", 'ujinter' ) ?></label>  
                    <input id="_see_show_cust" class="radio" type="radio" value="show_cust" name="where_show" <?php checked( $include, 'show_cust' ) ?>> 
                    <span class="description"><?php _e( "Show Ad on selected Pages/Posts", 'ujinter' ) ?></span>
                </p>
            </div>	   

            <!-- Select Posts/Pages -->
            <div id="show_custom" class="options_group" <?php echo ($include != 'show_cust') ? ' style="display:none"' : '' ?>>
                <p class="form-field">
                    <label for="ads_link"><?php _e( "Select Posts/Pages", 'ujinter' ) ?></label>  
                    <input type="text" name="ads_posts" class="short" id="ads_posts" value="<?php echo get_post_meta( $post->ID, 'ads_posts', true ); ?>" />  
                    <span class="description"><?php _e( "Add any pages or posts id separated by commas. ex: 312, 16, 27", 'ujinter' ) ?></span>
                </p>
            </div>   

        </div>
<?php 
  }

  /**
   * Inter settings
   * @since  1.0
   */
  public function interads_settings( $post ) {
?>	
     <div class="tab-content">

    <!-- Settings -->

            <div class="options_group">
               <p class="form-field">
                  <label for="post_random"><?php _e("Show title", 'ujinter') ?></label>  
                     <input id="show_title" class="checkbox" type="checkbox" value="yes" name="show_title" <?php checked( $this->get_opt( $post->ID, 'show_title' ), 'yes' ) ?>> 
                     <span class="description"><?php _e("Select to show Ad Title in top/left corner.", 'ujinter') ?></span>
               </p>
            </div>

            <div class="options_group options_sub">
               <p class="form-field-none">
               <label for="ads_impressions"><?php _e("Impression", 'ujinter') ?></label>  
                     <input type="text" name="ads_impressions" class="small-text" id="ads_impressions" value="<?php echo $this->get_opt( $post->ID, 'ads_impressions' ); ?>" />  
                     <span class="description"><?php _e("Reset to 0 or change value.", 'ujinter') ?></span>
               </p>
            </div>


    </div>
<?php 
  } 

  /**
   * Popup sizes
   * @since  1.0
   */
  public function interads_style( $post ) {
?>
    <div class="tab-content side-content">

      <div class="control-group chkbox2">
         <label class="size-label" for="add_close"><?php _e("Show Close Button:", 'ujipopup') ?></label>  
         <input id="add_close" class="checkbox" type="checkbox" value="yes" name="add_close" <?php checked( $this->get_opt( $post->ID, 'add_close' ), 'yes' ) ?>>
      </div>

<?php 
    $is = $this->get_sett( 'show_timer' ); 
    if( $is == "yes" ) {
?>
         <div class="control-group chkbox2">
         <label class="size-label" for="show_count"><?php _e("Show Countdown:", 'ujipopup') ?></label>  
         <input id="show_counter" class="checkbox" type="checkbox" value="yes" name="show_count" <?php checked( $this->get_opt( $post->ID, 'show_count' ), 'yes' ) ?>>
     </div>

         <div class="control-group chkbox2">
         <label class="size-label" for="on_wait_time"><?php _e("Enable Wait Time:", 'ujipopup') ?></label>  
         <input id="on_wait_time" class="checkbox" type="checkbox" value="yes" name="on_wait_time" <?php checked( $this->get_opt( $post->ID, 'on_wait_time' ), 'yes' ) ?>>
     </div>
         <?php } // endif ?>


     </div>
<?php	
  }

  /**
   * Save post
   * @since  1.0
   */
  public function interads_save( $post_id ) {
    if ( !$_POST ) {
      return $post_id;
    };
    if (
      is_int( wp_is_post_revision( $post_id ) ) ||
      is_int( wp_is_post_autosave( $post_id ) )
    ) {
      return;
    }
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
      return $post_id;
    }
    if ( !current_user_can( 'edit_post', $post_id )) {
      return $post_id;
    }
    if ( 'interads' == $_POST['post_type'] ) {
      // Save fields
      if( isset($_POST['include_html'] ) )
        update_post_meta($post_id, 'include_html', esc_html(stripslashes($_POST['include_html'])));
      else
        update_post_meta($post_id, 'include_html', '');
      if( isset($_POST['include_url'] ) )
        update_post_meta($post_id, 'include_url', esc_html(stripslashes($_POST['include_url'])));
      else
        update_post_meta($post_id, 'include_url', '');
      if( isset($_POST['show_title'] ) )
        update_post_meta($post_id, 'show_title', esc_html(stripslashes($_POST['show_title'])));
      else
        update_post_meta($post_id, 'show_title', '');
      if( isset($_POST['ads_impressions'] ) )
        update_post_meta($post_id, 'ads_impressions', esc_html(stripslashes($_POST['ads_impressions'])));
      else
        update_post_meta($post_id, 'ads_impressions', '');
      if( isset($_POST['add_posts']) )
        update_post_meta($post_id, 'add_posts', esc_html(stripslashes($_POST['add_posts'])));
      else
        update_post_meta($post_id, 'add_posts', '');
      if( isset($_POST['where_show'] ) )
        update_post_meta($post_id, 'where_show', esc_html(stripslashes($_POST['where_show'])));
      else
        update_post_meta($post_id, 'where_show', '');
      if( isset($_POST['post_category'] ) )
        update_post_meta($post_id, 'ad_post_category', $_POST['post_category'] );
      else
        update_post_meta($post_id, 'ad_post_category', '');

      for($x=1; $x<=5; $x++){	
        if(isset($_POST['ads_link'.$x]))
          update_post_meta($post_id, 'ads_link'.$x, esc_html(stripslashes($_POST['ads_link'.$x])));
        else
          update_post_meta($post_id, 'ads_link'.$x, '');
      }
      if( isset($_POST['ads_posts'] ) )
        update_post_meta($post_id, 'ads_posts', esc_html(stripslashes($_POST['ads_posts'])));
      else
        update_post_meta($post_id, 'ads_posts', '');
      if( isset($_POST['add_close'] ) )
        update_post_meta($post_id, 'add_close', esc_html(stripslashes($_POST['add_close'])));
      else
        update_post_meta($post_id, 'add_close', '');
      if( isset($_POST['show_count'] ) )
        update_post_meta($post_id, 'show_count', esc_html(stripslashes($_POST['show_count'])));
      else
        update_post_meta($post_id, 'show_count', '');
      if( isset($_POST['on_wait_time'] ) )
        update_post_meta($post_id, 'on_wait_time', esc_html(stripslashes($_POST['on_wait_time'])));
      else
        update_post_meta($post_id, 'on_wait_time', '');
    }
  }

  /**
   * settings_errors function.
   * @since 1.0.0
   */
  public function settings_errors () {
    echo settings_errors( $this->token . '-errors' );
  } // End settings_errors()

  /**
   * settings_screen function.
   * @since 1.0.0
   */
  public function settings_screen () {
?>
    <div id="ujinter" class="wrap">
        <?php screen_icon( 'interads' ); ?>
        <h2><?php echo esc_html( $this->opt_name ); ?></h2>

        <form action="options.php" method="post">
            <?php settings_fields( $this->page_slug ); ?>
            <?php do_settings_sections( $this->page_slug ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
  }
} // End Class
