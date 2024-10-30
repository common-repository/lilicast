<?php
  add_submenu_page( 'lilicast-top-level', 'Generate shortcode (flix)', 'Generate shortcode (flix)',
      'manage_options', 'lilicast-netflix-shortcode', function() {
        ?>
        <?php

          $lilicast_shows = LilicastApiWrapper::get_shows();
          // error_log("shows:");
          // error_log(print_r($lilicast_shows, true));
          $lilicast_shows = $lilicast_shows['result'];
          

          $description_style= 'style="font-weight: 400; color: rgba(0, 0, 0, 0.5);"';
          $td_style = 'style="vertical-align: top; padding-top: 45px"';
        ?>
        <div class="wrap">
          <h1>Generate shortcode (flix Style)</h1>
          <p>Create a shortcode for embedding a list of your Lilicasts inside of desired page or post with a collapsable player.</p>
          <table class="form-table">
            <tbody>
              <tr>
                <th scope="row">
                  <label>Categories</label>
                  <p <?php echo $description_style; ?>>Filter videos by selecting categories</p>
                </th>
                <td <?php echo $td_style; ?>>
                  <?php
                    $categories = get_categories( array(
                        'hide_empty' => false,
                        'orderby' => 'name',
                        'order'   => 'ASC'
                    ));
                    foreach( $categories as $category ) {
                      $cat_name = strtolower($category->cat_name);
                      ?>
                        <input type="checkbox" class="cat" value="<?php echo $cat_name;  ?>" />
                        <?php echo $cat_name; ?>
                        <br />
                      <?php
                    }
                  ?>
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <label>Tags</label>
                  <p <?php echo $description_style; ?>>Filter videos by entering comma separated list of tags</p>
                </th>
                <td <?php echo $td_style; ?>>
                  <input type="text" class="regular-text" id="tag" />
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <label>Maximum videos per page</label>
                  <p <?php echo $description_style; ?>>How many videos to show per page on video collection (default: 12).</p>
                </th> 
                <td <?php echo $td_style; ?>>
                  <input type="number" class="regular-text" id="page_max" />
                </td>
              </tr>
              <!-- <tr>
                <th scope="row">
                  <label>Display post title</label>
                </th>
                <td>
                  <input type="checkbox" class="regular-text" id="display_title" />
                </td>
              </tr> -->
              <tr>
                <th scope="row">
                  <label>LiLiCAST Show filter</label>
                </th>
                <td>
                  <!-- <input type="input" class="regular-text" id="show_id" value="4b4f32a8-c6b8-4861-8285-e8db883e1b9e"/> -->
                  <select id="show_id" name="show_id">
                    <option value="" selected="selected" disabled="disabled">Choose an option&hellip;</option>
                    <?php
                        foreach ( $lilicast_shows as $i => $show ) {
                          printf( '<option value="%1$s">%2$s</option>',$show->id, $show->name);
                        }
                  ?></select>
                </td>
              </tr>
              <tr>
               <th><label for="industry">Bransje</label></th>
               <td>
                 
               </td>
             </tr>
              <!-- <tr>
                <th scope="row">
                  <label>Hide "show all" button</label>
                </th>
                <td>
                  <input type="checkbox" class="regular-text" id="hide_show_all" />
                </td>
              </tr> -->
              <tr>
                <th scope="row">
                  <label>Shortcode</label>
                  <p <?php echo $description_style; ?>>Copy and paste this shortcode to any page or post</p>
                </th>
                <td <?php echo $td_style; ?>>
                  <p id="output">[lilicast_flix_list]</p>
                </td>
              </tr>
            </tbody>
          </div>
        </div>
        <script>
          var tags = '';
          var cats = [];
          var embed_max = null;
          var page_max = null;
          var display_title = null;
          var show_id = null;

          function generateNetflixShortcode() {
            var shortcode = '[lilicast_flix_list';
            if (cats.length>0) { shortcode = shortcode + ' ' + 'categories="' + cats + '"'; }
            if (tags) { shortcode = shortcode + ' ' + 'tags="' + tags + '"'; }
            if (embed_max) { shortcode = shortcode + ' ' + 'embed_max="' + embed_max + '"'; }
            if (page_max) { shortcode = shortcode + ' ' + 'posts_per_page="' + page_max + '"'; }
            if (display_title) { shortcode = shortcode + ' ' + 'display_title=1'; }
            // if (hide_show_all) { shortcode = shortcode + ' ' + 'hide_show_all=1'; }
            if (show_id) { shortcode = shortcode + ' ' + 'show_id=' + show_id; }
            shortcode = shortcode + ']';
            document.getElementById("output").innerHTML = shortcode;
          }

          var catArr = document.getElementsByClassName('cat');

          for (var a = 0; a < catArr.length; a++) {
            catArr[a].addEventListener('change', function(e) {
              if (e.target.checked) {
                cats.push(e.target.value);
              } else {
                var catIndex;
                for (var i = 0; i < cats.length; i++) {
                  console.log
                  if (cats[i]==e.target.value) {
                    catIndex = i;
                    break;
                  }
                }
                cats.splice(catIndex, 1);
              }
              generateNetflixShortcode();
            });
          }

          document.getElementById("tag").addEventListener('input', function(e) {
            tags = e.target.value;
            generateNetflixShortcode();
          });

          // document.getElementById("embed_max").addEventListener('input', function(e) {
          //   embed_max = e.target.value;
          //   generateNetflixShortcode();
          // });

          document.getElementById("page_max").addEventListener('input', function(e) {
            page_max = e.target.value;
            generateNetflixShortcode();
          });

          // document.getElementById("display_title").addEventListener('input', function(e) {
          //   display_title = e.target.checked;
          //   generateNetflixShortcode();
          // });

          // document.getElementById("hide_show_all").addEventListener('input', function(e) {
          //   hide_show_all = e.target.checked;
          //   generateNetflixShortcode();
          // });

          document.getElementById("show_id").addEventListener('input', function(e) {
            show_id = e.target.value;
            generateNetflixShortcode();
          });

          document.addEventListener("DOMContentLoaded", generateNetflixShortcode);

        </script>
        <?php
      });
?>