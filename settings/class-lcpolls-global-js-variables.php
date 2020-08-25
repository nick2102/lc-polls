<?php
/**
 * LcPolls_Global_Js_Variables
 *
 * Set global JavaScript Variables and Translations
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Global_Js_Variables
{
    public static function setJsVars()
    {
        ?>
        <script>
            window.lcpollsApiUrl = '<?php echo get_rest_url(null, LCPOLLS_PLUGIN_API_NAMESPACE);?>';
            window.restNonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
            window.homeUrl = '<?php echo get_site_url(); ?>';

            lcpollsTranslations = {
                errorTitle: '<?php _e('Error', 'lcpolls'); ?>',
                successTitle: '<?php  _e('Success', 'trainee') ?>',
                requiredField : '<?php _e('This field is required.', 'trainee'); ?>',
                requiredFields : '<?php _e('All fields are required.', 'trainee'); ?>',
                yes: '<?php _e('Yes', 'trainee'); ?>',
                no: '<?php _e('No', 'trainee'); ?>',
                noMoreComments: '<?php _e('No more comments to be displayed.', 'trainee'); ?>',
            }
        </script>
        <?php
    }
}

