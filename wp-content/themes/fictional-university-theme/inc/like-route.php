<?php


add_action('rest_api_init', 'universityLikeRoutes');

function universityLikeRoutes()
{
  //Registering rest API route for creating the like post
  register_rest_route(
    'university/v1',
    'manageLike',
    array(
      'methods' => 'POST',
      'callback' => 'createLike'
    )
  );

  //Registering rest API route for deleting the like post
  register_rest_route(
    'university/v1',
    'manageLike',
    array(
      'methods' => 'DELETE',
      'callback' => 'deleteLike'
    )
  );
}

//Funtion to create like post
function createLike($data)
{
  //If user is logged in then only he can like the professor.
  if (is_user_logged_in()) {
    //if request made with fetch API
    //   $data = $request -> get_json_params();
    // $professor = sanitize_text_field($data['professorId']);
    $professor = sanitize_text_field($data['professorID']);
    // Query to get the like post with the liked_professor_id save with the professor id.
    $existQuery = new WP_Query(
      array(
        'author' => get_current_user_id(),
        'post_type' => 'like',
        'meta_query' => array(
          array(
            'key' => 'liked_professor_id',
            'compare' => '=',
            'value' => $professor
          )
        )
      )
    );
    //Check whether the user already liked the professor or not and the id passed is of professor
    // echo $professor;
    if ($existQuery->found_posts == 0 and get_post_type($professor) == 'professor') {
      //Making the like post
      return wp_insert_post(
        array(
          'post_type' => 'like',
          'post_status' => 'publish',
          'post_title' => '2nd php post test',
          'meta_input' => array(
            'liked_professor_id' => $professor
          )
        )
      );
    } else {
      die("Invalid Professor Id");
    }

  } else {
    die("Only logged in users can create a like.");
  }


}

//Funtion to Delete like post
function deleteLike($data)
{
  $likeId = sanitize_text_field($data['like']);
  if(get_current_user_id() == get_post_field('post_author', $likeId) AND get_post_type($likeId) == "like"){
     wp_delete_post($likeId);
     return "Congrats, like deleted";
  }
  else{
die('You do not have permission to delete that');
  }
  
}

?>