<?php
get_header();
while (have_posts()):
  the_post();
  pageBanner();
  ?>

  <div class="container container--narrow page-section">
    <div class="generic-content">
      <div class="row group">
        <div class="one-third">
          <?php the_post_thumbnail('professorPortrait'); ?>
        </div>
        <div class="two-thirds">
          <?php
          $likeCount = new WP_Query(
            array(
              'post_type' => 'like',
              'meta_query' => array(
                array(
                  'key' => 'liked_professor_id',
                  'compare' => '=',
                  'value' => get_the_id()
                )
              )
            )
          );

          $existStatus = 'no';
          //If User is logged in then only execute the query
          if (is_user_logged_in()) {
            //Query to get the like post in which the liked professor id has the id of current professor id.
            $existQuery = new WP_Query(
              array(
                'author' => get_current_user_id(),
                'post_type' => 'like',
                'meta_query' => array(
                  array(
                    'key' => 'liked_professor_id',
                    'compare' => '=',
                    'value' => get_the_id()
                  )
                )
              )
            );
            if ($existQuery->found_posts) {
              $existStatus = 'yes';
            }
          }

         
          ?>
          <span class="like-box" data-professor="<?php the_id(); ?>" data-like="<?php  echo isset($existQuery->posts[0]->ID) ?  $existQuery->posts[0]->ID : "" ;?>" data-exists="<?php echo $existStatus ?>">
            <i class="fa fa-heart-o" aria-hidden="true"></i>
            <i class="fa fa-heart" aria-hidden="true"></i>
            <span class="like-count">
              <?php echo $likeCount->found_posts; ?>
            </span>
          </span>
          <?php the_content(); ?>
        </div>
      </div>
    </div>
    <?php
    $relatedPrograms = get_field('related_programs');
    if ($relatedPrograms):
      echo '<hr class="section-break">';
      echo '<h2 class="headline headline--medium">Subject(s) Taught</h2>';
      echo '<ul class="link-list min-list">';
      foreach ($relatedPrograms as $program): ?>
        <li><a href="<?php echo get_the_permalink($program); ?>">
            <?php echo get_the_title($program); ?>
          </a></li>

        <?php
      endforeach;
      echo '</ul>';
    endif ?>

  </div>
<?php endwhile;
get_footer();
?>