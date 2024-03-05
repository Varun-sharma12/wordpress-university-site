<?php 
// Made the template for the professor post type 
?>
<li class="professor-card__list-item">
            <a class="professor-card" href="<?php the_permalink(); ?>">
              <img src="<?php the_post_thumbnail_url('professorLandscape'); ?>" alt="" class="professor-card__image">
              <span class="professor-card__name">
                <?php the_title(); ?>
              </span>
            </a>
          </li>