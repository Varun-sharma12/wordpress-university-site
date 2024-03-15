<?php
require get_theme_file_path('inc/like-route.php');
require get_theme_file_path('inc/search-route.php');

//Adding custom field in the rest api of post .
function university_custom_rest()
{
  register_rest_field(
    'post',
    'authorName',
    array(
      "get_callback" => function () {
        return get_the_author();
      }
    )
  );
  register_rest_field(
    'note',
    'userNoteCount',
    array(
      "get_callback" => function () {
        return count_user_posts(get_current_user_id(), 'note');
      }
    )
  );
}
add_action('rest_api_init', 'university_custom_rest');

//Enqueuing the Scripts.
function university_files()
{
  wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
  wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
  wp_enqueue_style('font-awesome', "//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css");
  wp_enqueue_style('custom-google-fonts', "//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i");
  wp_enqueue_script('main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), 1.0, true);
  // wp_enqueue_script('googleMap', '//maps.googlepis.com/maps/api/js?key=', null, 1.0, true);
//Localizing the  script and providing the base url in object to use in search file
  wp_localize_script(
    'main-university-js',
    'universityData',
    array(
      'root_url' => get_site_url(),
      'nonce' => wp_create_nonce('wp_rest')
    )
  );
}
add_action('wp_enqueue_scripts', 'university_files');

//Adding the theme suppor option for activating various options on backend
function university_features()
{
  // register_nav_menu('headerMenuLocation','Header Menu Location');
  // register_nav_menu('footerLocationOne','Footer Location One');
  // register_nav_menu('footerLocationTwo','Footer Location Two');
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_image_size('professorLandscape', 400, 260, true);
  add_image_size('professorPortrait', 480, 680, true);
  add_image_size('pageBanner', 1500, 350, true);
}
add_action('after_setup_theme', 'university_features');

//Customizing the custom queries Which are not native to wordpress.
function university_adjust_queries($query)
{
  if (!is_admin() and is_post_type_archive('campsus') and is_main_query()) {

    $query->set('posts_per_page', -1);
  }
  if (!is_admin() and is_post_type_archive('program') and is_main_query()) {
    $query->set('orderby', 'title');
    $query->set('order', 'ASC');
    $query->set('posts_per_page', -1);
  }
  if (!is_admin() and is_post_type_archive('event') and $query->is_main_query()) {
    $today = date('Ymd');
    $query->set('meta_key', 'event_date');
    $query->set('orderby', 'meta_value_num');
    $query->set('order', 'ASC');
    $query->set(
      'meta_query',
      array(
        array(
          'key' => 'event_date',
          'compare' => '>=',
          'value' => $today,
          'type' => 'numeric'
        )
      )
    );
  }
}
add_action('pre_get_posts', 'university_adjust_queries');


//Making the pageBanner function global to use it in various pages and rendering the data conditionally.
function pageBanner($args = NULL)
{

  if (!isset($args['title'])) {
    $args['title'] = get_the_title();
  }

  if (!isset($args['subtitle'])) {
    $args['subtitle'] = get_field('page_banner_subtitle');
  }

  if (!isset($args['photo'])) {
    if (get_field('page_banner_background_image') and !is_archive() and !is_home()) {
      $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
    } else {
      $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
    }
  }

  ?>
  <div class="page-banner">
    <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
    <div class="page-banner__content container container--narrow">
      <h1 class="page-banner__title">
        <?php echo $args['title'] ?>
      </h1>
      <div class="page-banner__intro">
        <p>
          <?php echo $args['subtitle']; ?>
        </p>
      </div>
    </div>
  </div>
<?php }

//Adding the map api key by using the acf filter.
function universityMapKey($api)
{
  $api['key'] = '';
  return $api;
}

add_action('acf/fields/google_map/api', 'universityMapKey');

//Redirect subscriber account out of admin and onto homepage
add_action('admin_init', 'redirectSubsToFrontend');
function redirectSubsToFrontend()
{
  $ourCurrentUser = wp_get_current_user();
  if (count($ourCurrentUser->roles) == 1 and $ourCurrentUser->roles[0] == 'subscriber') {
    wp_redirect(site_url('/'));
    exit;
  }
}

//Removing the admin bar for the subscriber account
add_action('wp_loaded', 'noSubsAdminBar');
function noSubsAdminBar()
{
  $ourCurrentUser = wp_get_current_user();
  if (count($ourCurrentUser->roles) == 1 and $ourCurrentUser->roles[0] == 'subscriber') {
    show_admin_bar(false);
  }
}
// Customize login screen
add_filter('login_headerurl', 'ourHeaderUrl');
function ourHeaderUrl()
{
  return esc_url(Site_url('/'));
}

//Enqueue the styles to change the layout of login screen
add_action('login_enqueue_scripts', 'ourLoginCSS');
function ourLoginCSS()
{
  wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
  wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
  wp_enqueue_style('font-awesome', "//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css");
  wp_enqueue_style('custom-google-fonts', "//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i");
}

//Changing the text of login screen header title
add_filter('login_headertext', 'ourLoginTitle');
function ourLoginTitle()
{
  return get_bloginfo('name');
}

//Force note posts to be private
add_filter('wp_insert_post_data', 'makeNotePrivate', 10, 2);
function makeNotePrivate($data, $postarr)
{
  // print_r($data);
  //checking if the post is note 
  if ($data['post_type'] == 'note') {
    //Checking whether user exceeds the limit of making notes $postarr is to check for edit and delete
    if (count_user_posts(get_current_user_id(), 'note') > 4 and !$postarr['ID']) {
      die("You have reached your note limit.");
    }
    //Sanitizing the title and content of notes for safety purpose
    $data['post_content'] = sanitize_textarea_field($data['post_content']);
    $data['post_title'] = sanitize_text_field($data['post_title']);
  }
  //changing the post status to private on server side if the post is note type 
  if ($data['post_type'] == 'note' and $data['post_status'] != 'trash') {
    $data['post_status'] = "private";
  }
  return $data;
}

