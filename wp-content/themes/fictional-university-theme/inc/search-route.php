<?php

//Making custom route for the custom search.
function universityRegisterSearch()
{
  register_rest_route(
    'university/v1',
    'search',
    array(
      'methods' => WP_REST_SERVER::READABLE,
      'callback' => 'universitySearchResults'
    )
  );
}
add_action('rest_api_init', 'universityRegisterSearch');

//Callback Function.
function universitySearchResults($data)
{
  // $relatedProfessors = new WP_Query(
  //   array(
  //     'posts_per_page' => -1,
  //     'post_type' => 'professor',
  //     'orderby' => 'title',
  //     'order' => 'ASC',
  //     'meta_query' => array(
  //       array(
  //         'key' => 'related_programs',
  //         'compare' => 'LIKE',
  //         'value' => '"' . get_the_ID() . '"'
  //       )
  //     )
  //   )
  // );
  $mainQuery = new WP_Query(
    array(
      'post_type' => array('post', 'page', 'professor', 'program', 'campsus', 'event'),
      's' => sanitize_text_field($data['term']),

    )
  );
  // print_r($mainQuery);
  $results = array(
    'generalInfo' => array(),
    'professors' => array(),
    'programs' => array(),
    'events' => array(),
    'campuses' => array()
  );

  while ($mainQuery->have_posts()):
    $mainQuery->the_post();

    if (get_post_type() === 'post' || get_post_type() === 'page') {
      array_push(
        $results['generalInfo'],
        array(
          'title' => get_the_title(),
          'permalink' => get_the_permalink(),
          'postType' => get_post_type(),
          'authorName' => get_author_name()
        )
      );

    }
    if (get_post_type() === 'professor') {
      array_push(
        $results['professors'],
        array(
          'title' => get_the_title(),
          'permalink' => get_the_permalink(),
          'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
        )
      );
    }

    if (get_post_type() === 'program') {
      //Getting the related campus to the program if a term entered matches a program
      $relatedCampuses = get_field('related_campus');
      if($relatedCampuses){
      foreach($relatedCampuses as $campus){
        array_push($results['campuses'],array(
          'title' => get_the_title($campus),
          'permalink' => get_the_permalink($campus)
        ));

      }
    }
      array_push(
        $results['programs'],
        array(
          'title' => get_the_title(),
          'id' => get_the_id(), //Getting the id to use in the relationship query to find professors
          'permalink' => get_the_permalink(),
        )
      );
    }

    if (get_post_type() === 'event') {
      $eventDate = new DateTime(get_field('event_date'));
      $description = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 25);

      array_push(
        $results['events'],
        array(
          'title' => get_the_title(),
          'permalink' => get_the_permalink(),
          'month' => $eventDate->format('M'),
          'day' => $eventDate->format('d'),
          'description' => $description
        )
      );
    }

    if (get_post_type() === 'campus') {
      array_push(
        $results['campuses'],
        array(
          'title' => get_the_title(),
          'permalink' => get_the_permalink()
        )
      );
    }

  endwhile;
  wp_reset_postdata();

  if ($results['programs']) {
    //Making the array and passing the id of searched program into it dynamically.
    $programsMetaQuery = array('relation' => 'OR');
    foreach ($results['programs'] as $item) {
      array_push($programsMetaQuery, array(
        'key' => 'related_programs',
        'compare' => 'LIKE',
        'value' => '"' . $item['id'] . '"'
      )
      );
    }
    //Making a relationship query to search for the related professror and event if the term entered is a program.
    $programRelationshipQuery = new WP_Query(
      array(
        'post_type' => array('professor', 'event'),
        'meta_query' => $programsMetaQuery
      )
    );

    while ($programRelationshipQuery->have_posts()):
      $programRelationshipQuery->the_post();
      if (get_post_type() === 'professor') {
        array_push(
          $results['professors'],
          array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
          )
        );
      }
      //Checking for event post type
      if (get_post_type() === 'event') {
        $eventDate = new DateTime(get_field('event_date'));
        $description = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 25);

        array_push(
          $results['events'],
          array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'month' => $eventDate->format('M'),
            'day' => $eventDate->format('d'),
            'description' => $description
          )
        );
      }
   
    endwhile;
    wp_reset_postdata();
    // Removing duplicate professor and events from the result 
    $results['professors'] = array_values(array_unique($results['professors'], SORT_REGULAR));
    $results['events'] = array_values(array_unique($results['events'], SORT_REGULAR));
    
  } 
  return $results;
}