<?php
//Processing the search term and providing result usinf the wordpress default search file.
get_header();
pageBanner(array(
  'title' => 'Search Results',
  'subtitle' => 'You Searched for &ldquo;' . get_search_query() . '&rdquo;'
))
?> 
    <div class="container container--narrow page-section">
    <?php
    if(have_posts()):
    while(have_posts()):
      the_post();
     get_template_part('template-parts/content', get_post_type());
      ?>
 
    <?php endwhile; 
    else:
      echo '<h2 class="headline headline--small-plus" >No results match that search</h2>';
    endif;
    get_search_form(); //Getting the form from the search form file
    ?>
    </div>
<?php
get_footer();
?>