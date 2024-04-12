<?php
/*
Plugin Name: Our Test Plugin
Description: A truly amazing plugin.
Version: 1.0
Author: Varun
Author URI:
Text Domain: wcpdomain
Domain Path: /languages
*/

class WordCountAndTimePlugin
{
  //Constructor will get invoked as we instantiate the class
  function __construct()
  {
    //adminPage function will be call on this object So no chances of conflict.
    add_action('admin_menu', array($this, 'adminPage'));

    //Calling the setting function on admin hook
    add_action('admin_init', array($this, 'settings'));

    add_filter('the_content', array($this, 'ifWrap'));

    add_action('init', array($this, 'languages'));
  }
  function languages(){
    load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
  }

  function ifWrap($content)
  {
    if (
      ((is_main_query() and is_single()) and (
        get_option('wcp_wordcount', '1') or //using 1 as second parameter is the default value when plugin gets installed first time and it did'nt exist in db.
        get_option('wcp_charactercount', '1') or
        get_option('wcp_readtime', '1')))
    ) {
      return $this->createHTML($content);
    }
    return $content;
  }

  function createHTML($content)
  {
    $html = '<h3>' . get_option('wcp_headline', 'Post Statistics') . '</h3> <p>';
    if(get_option('wcp_wordcount', 1) OR get_option('wcp_readtime', 1)){
    $wordCount = str_word_count(strip_tags($content));
    }
    if(get_option('wcp_wordcount', 1)){
    $html .= esc_html__('This post has','wcpdomain').' ' . $wordCount .' '.esc_html__('Words','wcpdomain').'.<br>';
    }
    if(get_option('wcp_charactercount', 1)){
      $html .= 'This Post has ' . strlen(strip_tags($content)) .' Characters.<br>';
    }
    if(get_option('wcp_readtime', 1)){
      $html .= 'This post has ' .round($wordCount/225). ' minute(s) to read. <br>';
    }
    if (get_option('wcp_location', '0') == '0') {
      return $html . $content;
    } else {
      return $content . $html;
    }
  }

  // Function for registering section and settings
  function settings()
  {
    //Adding the setting section.
    add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

    // Adding the setting for the location field .
    add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
    // Registering the setting of location field
    register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

    // Adding the setting for the headline field .
    add_settings_field('wcp_headline', 'Display Headline', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
    // Registering the setting of headline field
    register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

    // Adding the setting for the word count field .
    add_settings_field('wcp_wordcount', 'Word Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
    // Registering the setting of word count field
    register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    // Adding the setting for the character count field .
    add_settings_field('wcp_charactercount', 'Character Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_charactercount'));
    // Registering the setting of character count field
    register_setting('wordcountplugin', 'wcp_charactercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    // Adding the setting for the read time field .
    add_settings_field('wcp_readtime', 'Read Time', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
    // Registering the setting of read time field
    register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
  }

  // Function to render the html of readtime field
  /*
  Thre different function for each check box.
  function readtimeHTML()
  { ?>
    <input type="checkbox" name="wcp_readtime" value="1" <?php checked(get_option('wcp_readtime', '1')) ?>>
  <?php }

  // Function to render the html of charactercount field
  function charactercountHTML()
  { ?>
    <input type="checkbox" name="wcp_charactercount" value="1" <?php checked(get_option('wcp_charactercount', '1')) ?>>
  <?php }

  // Function to render the html of wordcount field
  function wordcountHTML()
  { ?>
    <input type="checkbox" name="wcp_wordcount" value="1" <?php checked(get_option('wcp_wordcount', '1')) ?>>
  <?php }
  */

//Sanitize the value so that user does'nt input malicious value from dev tools.
  function sanitizeLocation($input)
  {
    if ($input !== '0' and $input !== '1') {
      add_settings_error('wcp_location', 'wcp_location_error', 'Display Location must be either beginnening or end');
      return get_option('wcp_location');
    }
    return $input;
  }

  // Single function to be called on all three checkboxes 
  function checkboxHTML($args)
  {
    ?>
    <input type="checkbox" name="<?php echo $args['theName']; ?>" value="1" <?php checked(get_option($args['theName'], '1')) ?>>
  <?php }
  // Function to render the html of headline field
  function headlineHTML()
  { ?>
    <input type="text" name='wcp_headline' value="<?php echo esc_attr(get_option('wcp_headline')) ?>">
  <?php }

  // Function to render the html of location field
  function locationHTML()
  { ?>
    <!-- name attribute should match with the register setting arguement name -->
    <select name="wcp_location">
      <option value="0" <?php selected(get_option('wcp_location'), '0'); ?>>Beginning of post</option>
      <option value="1" <?php selected(get_option('wcp_location'), '1'); ?>>End of post</option>
    </select>
  <?php }
  function adminPage()
  {
    add_options_page(
      'Word Count Settings',
      esc_html__('Word Count', 'wcpdomain'), //For the localization purpose
      'manage_options',
      'word-count-settings-page',
      array($this, 'ourHTML')
    );
  }
  //Function that will render the UI of the plugin on dashboard.
  function ourHTML()
  { ?>
    <div class="wrap">
      <h1>Word Count Settings</h1>
      <form action="options.php" method="POST">
        <?php
        settings_fields('wordcountplugin');
        do_settings_sections('word-count-settings-page');
        submit_button();
        ?>
      </form>
    </div>
  <?php }
}
//Instantiation of the class.
$WordCountAndTimePlugin = new WordCountAndTimePlugin();

