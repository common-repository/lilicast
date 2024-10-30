<?php

    /* This file is responsible for rendering and functionality of
     * the overview/settings page of the plugin
     */
    include_once dirname( __FILE__ ) . '/lilicast-logo-string.php';

    add_menu_page('Lilicast', 'LiLiCAST', 'manage_options', 'lilicast-top-level',

        // REFACTOR: extract into a class & a template
        function() {
            // Retry Hook ...
            if (isset($_GET['retry_video_id']) && isset($_GET['id'])) {
                $retry_video_id = sanitize_text_field($_GET['retry_video_id']);
                $id = sanitize_text_field($_GET['id']);
                LilicastSyncFromApp::retryVideoUpload($retry_video_id, $id);
            }

            $api_key = LilicastApiWrapper::getApiKey();
            $api_key_hint = substr($api_key, 0, 4);

            $api_select = get_option('lilicast_api_select');

            // Basic rendering 
            ?>
            <h2>LiLiCAST Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'overview_settings_group' ); ?>
                <?php do_settings_sections( 'overview_settings_group' ); ?>
                <table class="form-table">                     
                    <tr valign="top">
                        <th scope="row">
                            API key
                        </th>
                        <td>
                            <input type="password" name="lilicast_api_key" value="" />
                            <p class="description">
                                <?php
                                    if ($api_key_hint !== '') {
                                        echo 'You are currently using key starting with "' . $api_key_hint . '".';
                                    };
                                ?>
                                <br />
                                You can find your API key under the settings of your organisation in the LiLiCAST app. Contact our team if you have difficulties obtaining the API key!
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            API (expert only)
                        </th>
                        <td>
                            <label for="default" class="radio-inline">REAL (default)</label>
                            <input type="radio" id="default" name="lilicast_api_select" value="default"
                                <?php 
                                    if (empty($api_select) || $api_select == 'default'){ 
                                        echo "checked=\"true\"";
                                    } 
                                ?>
                            >
                            <label for="qa" class="radio-inline">DEBUG</label>
                            <input type="radio" id="qa" name="lilicast_api_select" value="qa"
                                <?php 
                                    if ($api_select == 'qa'){ 
                                        echo "checked=\"true\"";
                                    } 
                                ?>
                            >
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>

            </form>

            <?php
                $api_response = LilicastApiWrapper::get_shows(); 
            
                if (isset($api_response['error_message'])) {?>
                
                  <h3>API Test <span class="warning">(error)</span></h3>
                  
                  <span class="field-notice alert"> 
                  Can not get the shows from LiLiCAST app. Make sure you have a right API key.</span> 
                  <br/> 
                  <span class="field-notice alert"> Error: 
                    <?php echo $api_response['error_message'];?>
                  </span>
                <?php
                } else {
                ?>
                    <h3>API Test <span class="success">(OK)</span></h3>
                    
                    <p><b>Show on LiLiCAST App:</b>
                        <?php
                            foreach ($api_response['result'] as $i => $show) {
                                if ($i == 0){
                                    echo $show->name;
                                    echo ' (id: ' . $show->id . ')';
                                } else {
                                    echo ', '.($show->name);
                                    echo ' (id: ' . $show->id . ')';
                                }
                            }; echo ".";
                        ?>
                    </p>
                <?php 
                }
            
            // $lilicastTable = new Lilicast_Import_Table();
            // $lilicastTable->prepare_items(); 
            // $lilicastTable->display(); 
        },
        $lilicast_logo
    ); /*  lilicast activation end */
?>