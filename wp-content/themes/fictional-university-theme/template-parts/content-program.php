<?php 
// Made the template for the programm post type 
?>
<div class="post-item">
  <h2 class="headline headline--medium hedline--post-title"><a href="<?php the_permalink(); ?> ">
      <?php the_title(); ?>
    </a></h2>

  <div class="generic-content">
    <?php the_excerpt() ?>
    <p><a class="btn btn--blue" href="<?php the_permalink(); ?>">View program &raquo;</a></p>
  </div>
</div>