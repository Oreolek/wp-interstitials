<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Uji_Interst_Admin_API{
  var $version;
  public $sections;
  public $fields;

  /**
   * __construct function.
   * 
   */
  public function __construct (  ) {
    add_action( 'admin_init', array( &$this, 'settings_fields' ) );
    $this->init_sections();
    $this->init_fields();
  }

  /**
   * init_sections function.
   * 
   * @access public
   * @since 1.0.0
   */
  public function init_sections () {

    $sections = array();

    $sections['style-options'] = array(
      'name' 			=> __( 'Styling Options', 'ujinter' ), 
      'description'	=> __( 'Customize colors for all ads.', 'ujinter' )
    );

    $sections['generate-settings'] = array(
      'name' 			=> __( 'General Setings', 'ujinter' ), 
      'description'	=> __( 'This settings apply to all active Ads', 'ujinter' )
    );

    $sections['transalte-settings'] = array(
      'name' 			=> __( 'Translate texts', 'ujinter' ), 
      'description'	=> __( 'Customise the texts', 'ujinter' )
    );		

    $this->sections = $sections;
  } // End init_sections()

  /**
   * init_fields function.
   * 
   * @access public
   * @since 1.0.0
   */
  public function init_fields () {
    $fields = array();

    $fields['bar_color'] = array(
      'name' => __( 'Top Bar Background Color', 'ujinter' ), 
      'description' => __( 'Select top bar background color. Default is black', 'ujinter' ), 
      'type' => 'color',
      'default' => '#000000', 
      'section' => 'style-options'
    );	

    $fields['title_color'] = array(
      'name' => __( 'Top Bar Text Color', 'ujinter' ), 
      'description' => __( 'Select title color. Default is white', 'ujinter' ), 
      'type' => 'color',
      'default' => '#ffffff', 
      'section' => 'style-options'
    );

    $fields['back_color'] = array(
      'name' => __( 'Content Background', 'ujinter' ), 
      'description' => __( 'Select background color. Default is white', 'ujinter' ), 
      'type' => 'color',
      'default' => '#ffffff', 
      'section' => 'style-options'
    );	

    $fields['cache_in']  = array(
      'name' => __( '<span style="color:red; font-weight: bold">Cache Plugin</span>', 'ujinter' ), 
      'description' => __( 'Enable it if using cache plugin such: WP Super Cache or W3 Total Cache. <span style="color: red">Please Delete Cached Pages after enabled this feature! </span>' , 'ujinter' ), 
      'type' => 'select',
      'default' => 'no',
      'options' => array( 'yes' => __( 'Yes', 'ujinter' ), 'no' => __( 'No', 'ujinter' ) ), 
      'section' => 'generate-settings'
    );

    $fields['show_timer']  = array(
      'name' => __( 'Show countdown', 'ujinter' ), 
      'description' => __( 'Choose to show countdown timer', 'ujinter' ), 
      'type' => 'select',
      'default' => 'yes',
      'options' => array( 'yes' => __( 'Yes', 'ujinter' ), 'no' => __( 'No', 'ujinter' ) ), 
      'section' => 'generate-settings'
    );							

    $fields['countdown_time'] = array(
      'name' => __( 'Countdown Length', 'ujinter' ), 
      'description' => __( 'Enter how long the popups ad will be shows until closing itself. Default is 15 seconds', 'ujinter' ), 
      'type' => 'select',
      'default' => '15',
      'options' => array(
        '5' => __( '5 seconds', 'ujinter' ),
        '10' => __( '10 seconds', 'ujinter' ),
        '15' => __( '15 seconds', 'ujinter' ),
        '20' => __( '20 seconds', 'ujinter' ),
        '25' => __( '25 seconds', 'ujinter' ),
        '30' => __( '30 seconds', 'ujinter' ),
        '60' => __( '1 minute', 'ujinter' ),
        '120' => __( '2 minutes', 'ujinter' )
      ), 
      'section' => 'generate-settings'
    );

    $fields['wait_time'] = array(
      'name' => __( 'Wait Time', 'ujinter' ), 
      'description' => __( 'Enter how long the popups shoult wait until will be shown. Default is 0 seconds. Shows instantly.', 'ujinter' ), 
      'type' => 'select',
      'default' => '0',
      'options' => array(
        '0' => __( '0 seconds', 'ujinter' ),
        '5' => __( '5 seconds', 'ujinter' ),
        '10' => __( '10 seconds', 'ujinter' ),
        '15' => __( '15 seconds', 'ujinter' ),
        '20' => __( '20 seconds', 'ujinter' ),
        '30' => __( '30 seconds', 'ujinter' ),
        '60' => __( '1 minute', 'ujinter' ),
        '120' => __( '2 minutes', 'ujinter' )
      ), 
      'section' => 'generate-settings'
    );	

    $fields['tra_wait']   = array(
      'name' => __( '"Wait" Text', 'ujinter' ), 
      'description' => __( 'The text to display on the "Wait" message.', 'ujinter' ), 
      'type' => 'text',
      'default' => 'Wait',
      'section' => 'transalte-settings'
    );
    $fields['tra_seconds'] = array(
      'name' => __( '"Seconds" Text', 'ujinter' ), 
      'description' => __( 'The text to display on the "seconds" message in countdown text.', 'ujinter' ), 
      'type' => 'text',
      'default' => '',
      'section' => 'transalte-settings'
    );
    $fields['tra_minutes'] = array(
      'name' => __( '"Minutes" Text', 'ujinter' ), 
      'description' => __( 'The text to display on the "minutes" message in countdown text.', 'ujinter' ), 
      'type' => 'text',
      'default' => '',
      'section' => 'transalte-settings'
    );
    $fields['tra_until'] = array(
      'name' => __( '"until will close" Text', 'ujinter' ), 
      'description' => __( 'The text to display on the "until will close" message.', 'ujinter' ), 
      'type' => 'text',
      'default' => '',
      'section' => 'transalte-settings'
    );
    $fields['tra_mess'] = array(
      'name' => __( 'Message Preview:', 'ujinter' ), 
      'type' => 'mess',
      'default' => '',
      'description' => '', 
      'section' => 'transalte-settings'
    );

    $this->fields = $fields;
  }

  /**
   * hidden field.
   * 
   * @access public
   * @since 1.0.0
   */
  public function form_field_hidden ( $args ) {
    echo '<input id="' . esc_attr( $args['key'] ) . '" name="' . $this->token . '[' . esc_attr( $args['key'] ) . ']" size="10" type="hidden" value="' . esc_attr( $args['data'] ) . '" />' . "\n";
  } // End form_field_hidden()

  /**
   * color select.
   * 
   * @access public
   * @since 1.0.0
   */
  public function form_field_color ( $args ) {

    echo '<div class="input-append color colorpicker" data-color="#000000">
      <input id="' . esc_attr( $args['key'] ) . '" name="' . $this->token . '[' . esc_attr( $args['key'] ) . ']" type="text" class="span1 wpcolorpicker" value="' . esc_attr( $args['data'] ) . '">
      <span class="add-on"><i style="background-color: rgb(255, 146, 180)"></i></span>
      </div>' . "\n";
    if ( isset( $args['desc'] ) ) {
      echo '<span class="description">' . esc_html( $args['desc'] ) . '</span>' . "\n";
    }

  } // End form_field_hidden()

  /**
   * text field.
   * 
   * @access public
   * @since 1.0.0
   */
  public function form_field_text ( $args ) {
    $sz = (isset($this->fields[$args['key']]['size']) && is_numeric($this->fields[$args['key']]['size']) ) ? $this->fields[$args['key']]['size'] : 40;
    echo '<input id="' . esc_attr( $args['key'] ) . '" name="' . $this->token . '[' . esc_attr( $args['key'] ) . ']" size="'. $sz .'" type="text" value="' . esc_attr( $args['data'] ) . '" />' . "\n";
    if ( isset( $args['desc'] ) ) {
      echo '<p><span class="description">' . esc_html( $args['desc'] ) . '</span></p>' . "\n";
    }
  } // End form_field_text()

  /**
   * form_field_select function.
   * 
   * @access public
   * @since 1.0.0
   */
  public function form_field_select ( $args ) {
    if ( isset( $args['options'] ) && ( count( (array)$args['options'] ) > 0 ) ) {
      $html = '';
      $html .= '<select id="' . esc_attr( $args['key'] ) . '" name="' . esc_attr( $this->token ) . '[' . esc_attr( $args['key'] ) . ']">' . "\n";
      foreach ( $this->fields[$args['key']]['options'] as $k => $v ) {
        $html .= '<option value="' . esc_attr( $k ) . '"' . selected( esc_attr( $args['data'] ), $k, false ) . '>' . $v . '</option>' . "\n";
      }
      $html .= '</select>' . "\n";
      echo $html;

      if ( isset( $args['desc'] ) ) {
        echo '<p><span class="description">' .  $args['desc']  . '</span></p>' . "\n";
      }
    }
  } // End form_field_select()

  /**
   * form_field_checkbox function.
   * 
   * @access public
   * @since 1.0.0
   */
  public function form_field_checkbox ( $args ) {
    if ( isset( $args['data']['description'] ) ) {

      echo '<label for="' . $this->token . '[' . esc_attr( $args['key'] ) . ']">' . "\n";
    }
    echo '<input id="' . $args['key'] . '" name="' . $this->token . '[' . esc_attr( $args['key'] ) . ']" type="checkbox" value="1"' . checked( esc_attr( $args['data'] ), '1', false ) . ' />' . "\n";
    if ( isset( $args['desc'] ) ) {
      echo '<p><span class="description">' . esc_html( $args['desc'] ) . '</span></p>' . "\n";
    }
  } // End form_field_text()

  /**
   * message field.
   * 
   * @access public
   * @since 1.0.0
   */
  public function form_field_mess ( $args ) {
    //$sz = (isset($this->fields[$args['key']]['size']) && is_numeric($this->fields[$args['key']]['size']) ) ? $this->fields[$args['key']]['size'] : 40;
    $val = get_option( $this->token);
    if(!empty($val['tra_wait']))
      echo  '<div style="background-color: #e3e3e3; padding: 4px 8px; display: inline-block;">'. $val['tra_wait'] .' ' . $val['countdown_time']. ' '. $val['tra_seconds'] . ' '. $val['tra_until'] .'</div>';
    else
      echo  '<div style="background-color: #e3e3e3; padding: 4px 8px; display: inline-block;">Wait 15 seconds until will close</div>';
  } // End form_field_text()

  /**
   * section_description function.
   * 
   * @access public
   * @return void
   */
  public function section_description ( $section ) {
    echo wpautop( esc_html( $this->sections[$section['id']]['description'] ) );

  } // End section_description_main()


  /**
   * create_sections function.
   * 
   * @access public
   * @return void
   */
  public function create_sections () {
    foreach($this->sections as $name => $arr){
      add_settings_section( $name, $arr['name'], array( &$this, 'section_description' ), $this->page_slug );
    }

  } // End create_sections()

  /**
   * create_fields function.
   * 
   * @access public
   * @return void
   */
  public function create_fields () {
    $val = get_option( $this->token);
    foreach($this->fields as $name => $arr){
      $data = (!empty($val[$name])) ? $val[$name] : $arr['default'];
      add_settings_field(
        $name,
        $arr['name'],
        array(
          &$this,
          'form_field_'.$arr['type']
        ),
        $this->page_slug,
        $arr['section'],
        array(
          'key' => $name,
          'data' => $data,
          'desc' => $arr['description'],
          'options' => (isset($arr['options'])) ? $arr['options'] : ''
        )
      );
    }
  } // End create_fields()

  public function update_mess ( $input ) {
    $valid = array();	
    $valid = $input;
    $mess = '';

    if($input['cache_in'] == 'yes')
      $mess = '<br><span style="color: red">'. __( 'Please empty cache!', 'ujinter' ) . '</span>';

    $message = sprintf(
      __( '%s updated %s', 'ujinter' ),
      $this->opt_name,
      $mess
    );
    add_settings_error( $this->token . '-errors', 'update', $message, 'updated' );

    return $valid;
  } // End settings_fields()

  /**
   * settings_fields function.
   * 
   * @access public
   * @return void
   */
  public function settings_fields () {
    register_setting( $this->page_slug, $this->token, array( &$this, 'update_mess' ));
    $this->create_sections();
    $this->create_fields();
  } // End settings_fields()

  /**
   * Get settings val
   * 
   * @access public
   * @return void
   */
  public function get_sett ($name, $def = NULL) {
    $val = get_option( $this->token);
    $data = (!empty($val[$name])) ? $val[$name] : $def;
    return $data;
  }

  /**
   * Get option val
   * 
   * @access public
   * @return void
   */
  public function get_opt ($id, $name) {
    $opt = get_post_meta( $id, $name, true );
    return $opt;
  }

} // End Class
