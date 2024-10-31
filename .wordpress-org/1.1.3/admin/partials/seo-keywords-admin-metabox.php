<?php
    $seo_links_keywords = $seo_links_keywords ?? array();
    $seo_links_keywords_impressions = $seo_links_keywords_impressions ?? array();
    $seo_links_last_update = $seo_links_last_update ?? '';
    $sc_api_key = $sc_api_key ?? '';
    $seo_keywords_credits = $credits->seo_keywords ?? 0;
?>
<!-- SEO Keywords -->
<div class="tabs-panel">
    <!-- Pure CSS Loader -->
    <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
    <?php if( isset( $_GET['google_status'] ) ) :  ?>
        <?php
        if( sanitize_text_field( $_GET['google_status'] ) == 'ok' ) :
            $seo_links_last_update = date('Y-m-d H:i:s');
            update_option( 'seo_links_last_update', $seo_links_last_update );
            ?>
            <script>
                if( Seo_Keywords_isGutenbergActive() ) {
                    wp.data.dispatch('core/notices').createSuccessNotice('Google account is successfully connected, keywords updated.', {
                        isDismissible: true
                    });
                }
                // Cleaning url from data
                let url = window.location.href;
                url = url.replace(/&google_status(.)*/, '');
                window.history.pushState({}, document.title, url);
            </script>
            <div class="notice notice-success is-dismissible">
                <strong>SEO Keywords</strong>
                <p>Google account is successfully connected, keywords updated.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if(isset($_GET['google_error'])) :  ?>
        <script>
            if( Seo_Keywords_isGutenbergActive() ) {
                wp.data.dispatch('core/notices').createErrorNotice(
                    '<?php echo $_GET['google_error']; ?>',
                    {
                        isDismissible: true,
                        __unstableHTML: true
                    }
                );
            } else {
                jQuery(document).ready(function(){
                    setTimeout(function(){
                        jQuery('#seo_keywords_error_modal').modal('show');
                    }, 800);
                });
            }
        </script>
        <div class="notice notice-error is-dismissible">
            <h2>SEO Keywords</h2>
            <p style="font-size: 18px;"><?php echo stripslashes( $_GET['google_error'] ); ?></p>
            <?php if(isset( $_GET['response_status'] ) && $_GET['response_status'] == -4 ) : ?>
                <?php $credits = $this->wp_seo_plugins_get_credits(); ?>
                <p><b><i>You have <span style="color: #ba000d"><?php echo esc_html( $seo_keywords_credits ); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></b></p>
                <div id="seo_keywords_no_more_credits_modal" class="modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">No more credits available</h5>
                            </div>
                            <div class="modal-body">
                                <?php $credits = $this->wp_seo_plugins_get_credits(); ?>
                                <p><b><i>You have <span style="color: #ba000d"><?php echo esc_html( $seo_keywords_credits ); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></b></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="jQuery('#seo_keywords_no_more_credits_modal').modal('hide');">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    jQuery(document).ready(function(){
                        setTimeout(function(){
                            jQuery('#seo_keywords_no_more_credits_modal').modal('show');
                        }, 800);
                    });
                </script>
            <?php else: ?>
                <div id="seo_keywords_error_modal" class="modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">SEO Keywords</h5>
                            </div>
                            <div class="modal-body">
                                <p style="font-size: 18px;"><?php echo stripslashes( $_GET['google_error'] ); ?></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="jQuery('#seo_keywords_error_modal').modal('hide');">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <script>
            // Cleaning url from data
            let url = window.location.href;
            url = url.replace(/&google_error(.)*/, '');
            window.history.pushState({}, document.title, url);
        </script>
    <?php endif; ?>
    <div id="seo_keywords" style="max-height: 250px;overflow: scroll;">
        <?php if( $seo_links_keywords ) : ?>
            <input type="text" id="seo_keywords_input" onkeyup="seoKeywordResearch('seo_keywords')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
            <table style="margin: 8px 0;">
                <thead>
                <tr>
                    <th scope="row" style="width:70%;cursor: pointer;">Keyword</th>
                    <th scope="row" style="cursor: pointer;">Impressions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $seo_keywords = array();
                foreach( $seo_links_keywords as $seo_link ) :
                    $seo_keywords[ $seo_link ] = $seo_links_keywords_impressions[$seo_link];
                endforeach;
                arsort( $seo_keywords );
                ?>
                <?php foreach( $seo_keywords as $seo_link => $seo_position ) : ?>
                    <tr class="seo_keywords">
                        <td>
                            <?php echo esc_html( $seo_link ); ?>
                        </td>
                        <td style="text-align: center;">
                            <?php echo esc_html( $seo_position ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="margin: 8px 0;">Add those relevant keywords to your content to optimize it for search engines.</p>
            <p style="margin: 8px 0;">Click "get keywords" to receive keyword suggestions.</p>
        <?php endif; ?>
    </div>
</div>

<?php if( !empty( $seo_links_last_update )) : ?>
    <p style="margin: 24px 0 0 0;"><i>Keywords updated to <?php echo date('d F Y', strtotime($seo_links_last_update)); ?></i></p>
    <?php if(( time() - strtotime($seo_links_last_update) ) < ( 7 * 24 * 60 * 60 ) ) : ?>
        <!-- metafield Form -->
        <form method="post" action="">
            <input type="hidden" name="post_id" value="<?php if(!empty($_GET['post'])){ echo esc_attr( $_GET['post'] ); } ?>" />
            <?php if( !$seo_links_keywords ) : ?>
                <p style="margin: 24px 0 0 0;">
                    By clicking add links you will be redirected to connect your Google search console account.
                    If you did not verify your site, please follow <a href="https://youtu.be/N4PmE3LysUM">this guide</a> to set up search console.
                </p>
            <?php endif; ?>
            <p style="text-align: right;margin-top: 8px;">
                <input onclick="Seo_Keywords_UpdateContentLink()" type="button" class="button button-primary" name="button" value="Get Keywords" />
            </p>
            <p style="text-align: right;"><small><i>You have <span style="color: #ba000d"><?php echo esc_html( $seo_keywords_credits ); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></small></p>
        </form>
    <?php else : ?>
        <?php $server_uri = SEO_KEYWORDS_SITE_URL . SEO_KEYWORDS_SERVER_REQUEST_URI; ?>
        <p style="margin: 24px 0 0 0;">Your keywords are too old, please refresh them by clicking button below.</p>
        <p style="margin: 12px 0 0 0;"><a class="button button-primary" href="<?php echo esc_url_raw( WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole?api_key='.$sc_api_key.'&domain='. SEO_KEYWORDS_SITE_URL .'&remote_server_uri='.base64_encode($server_uri) ); ?>">Google Connect</a></p>
    <?php endif; ?>
<?php else : ?>
    <?php $server_uri = SEO_KEYWORDS_SITE_URL . SEO_KEYWORDS_SERVER_REQUEST_URI; ?>
    <p style="margin: 24px 0 0 0;"><a class="button button-primary" href="<?php echo esc_url_raw( WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole?api_key='.$sc_api_key.'&domain='. SEO_KEYWORDS_SITE_URL .'&remote_server_uri='.base64_encode($server_uri) ); ?>">Google Connect</a></p>
<?php endif; ?>
