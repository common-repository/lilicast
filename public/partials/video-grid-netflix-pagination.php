<!-- Pagination -->
<div class='lc-video-grid-paginator'>
  <nav class="mx-auto" style="display: flex;">
    <ul class="pagination mx-auto">
      <?php
      if ( isset($lc_postslist)) {
        $prev_string = '<svg
          width="14"
          height="14"
          class="feather-svg"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round">
          <use xlink:href="' . $plugin_uri . 'assets/feather/feather-sprite.svg#chevrons-left' . '"/>
        </svg>';
        $next_string = '<svg
          width="14"
          height="14"
          class="feather-svg"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round">
          <use xlink:href="' . $plugin_uri . 'assets/feather/feather-sprite.svg#chevrons-right' . '"/>
        </svg>';
        $pagination =  paginate_links( array(
          'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
          'total'        => $lc_postslist->max_num_pages,
          'current'      => max( 1, get_query_var( 'paged' ) ),
          'format'       => '?paged=%#%',
          'show_all'     => false,
          'type'         => 'array',
          'end_size'     => 2,
          'mid_size'     => 1,
          'prev_next'    => true,
          'prev_text'    => sprintf( '<i></i> %1$s', __( $prev_string, 'text-domain' ) ),
          'next_text'    => sprintf( '%1$s <i></i>', __( $next_string, 'text-domain' ) ),
          'add_args'     => false,
          'add_fragment' => '',
        ));
      }
      if ( isset($pagination) && count($pagination) >0 ) {
        foreach ($pagination as $key => $page_link) { ?>
          <?php if ($paged ===1 && $key===0) { ?>
          <li class='page-item disabled'>
            <span class="page-link"><?php echo $prev_string; ?></span>
          </li>                    
          <?php }
            $page_link = str_replace('page-numbers', 'page-link', $page_link);
            $is_arrow = strpos($page_link, 'prev')||strpos($page_link, 'next');
            $is_current = !!strpos($page_link, 'current');
          ?>
          <li class='page-item <?php if ($is_current&&!$is_arrow) { echo 'active'; } ?>'>
            <?php echo $page_link; ?>
          </li>
          <?php if ($key===$paged&&$key===count($pagination)-1) { ?>
            <li class='page-item disabled'>
              <span class="page-link"><?php echo $next_string; ?></span>
            </li>  
          <?php }
        }
      }?>
    </ul>
  </nav>
</div>