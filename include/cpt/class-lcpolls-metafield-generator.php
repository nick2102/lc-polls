<?php

/**
 * LcPolls_Metafield_Generator
 *
 * Creating custom post types Metabox fields
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Metafield_Generator
{

    private static $instance = null;

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance ()
    {

        if ( null == self::$instance ) {
            self::$instance = new LcPolls_Metafield_Generator();
        }

        return self::$instance;
    }

    /**
     * Class construct method. Adds actions to their respective WordPress hooks.
     */
    public function __construct() {
        add_action( 'admin_footer', array( $this, 'admin_footer' ) );
    }

    /**
     * Generates the field's HTML for the meta box.
     */
    public static function generate_meta_box_field( $field )
    {
        global $post;
        $output = '';
        $label = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
        $db_value = get_post_meta( $post->ID, $field['prefix'] . $field['id'], true );
        switch ( $field['type'] ) {
            case 'checkbox':
                $input = sprintf(
                    '<input %s id="%s" name="%s" type="checkbox" value="1">',
                    $db_value === '1' ? 'checked' : '',
                    $field['id'],
                    $field['id']
                );
                break;
            case 'media':
                $img = !empty($db_value) ? '<img src="' . $db_value . '"/>' : '';
                $input = sprintf(
                    '<div id="%s_image_thumb" class="image_thumb_holder">%s</div> <input class="regular-text" id="%s_placeholder" name="%s" type="hidden" value="%s"> <button class="button rational-metabox-media" id="%s_button" type="button">%s</button>',
                    $field['id'],
                    $img,
                    $field['id'],
                    $field['id'],
                    $db_value,
                    $field['id'],
                    __('Select Image', 'trainee')
                );
                break;
            case 'file':
                $input = sprintf(
                    '<input class="regular-text" id="%s_placeholder" name="%s" type="text" readonly value="%s"> <button class="button rational-metabox-media" id="%s_button" type="button">%s</button>',
                    $field['id'],
                    $field['id'],
                    $db_value,
                    $field['id'],
                    __('Select file', 'trainee')
                );
                break;
            case 'radio':
                $input = '<fieldset>';
                $input .= '<legend class="screen-reader-text">' . $field['label'] . '</legend>';
                $i = 0;
                foreach ( $field['options'] as $key => $value ) {
                    $field_value = !is_numeric( $key ) ? $key : $value;
                    $input .= sprintf(
                        '<label><input %s id="%s" name="%s" type="radio" value="%s"> %s</label>%s',
                        $db_value === $field_value ? 'checked' : '',
                        $field['id'],
                        $field['id'],
                        $field_value,
                        $value,
                        $i < count( $field['options'] ) - 1 ? '<br>' : ''
                    );
                    $i++;
                }
                $input .= '</fieldset>';
                break;
            case 'textarea':
                $input = sprintf(
                    '<textarea class="large-text" id="%s" name="%s" rows="5">%s</textarea>',
                    $field['id'],
                    $field['id'],
                    $db_value
                );
                break;
            case 'select':
                $input = sprintf(
                    '<select id="%s" name="%s">',
                    $field['id'],
                    $field['id']
                );
                foreach ( $field['options'] as $key => $value ) {
                    $field_value = !is_numeric( $key ) ? $key : $value;
                    $input .= sprintf(
                        '<option %s value="%s">%s</option>',
                        $db_value === $field_value ? 'selected' : '',
                        $field_value,
                        $value
                    );
                }
                $input .= '</select>';
                break;
            case 'hidden':
                $input = sprintf(
                    '<input %s id="%s" name="%s" type="%s" value="%s">',
                     '',
                    $field['id'],
                    $field['id'],
                    $field['type'],
                    $db_value
                );
                break;
            case 'map':
                $input = '<div class="mapWrapper"><div class="mapSearchFields"><input type="text" id="address" placeholder="Address"> <input placeholder="city" type="text" id="city"> <a href="#" id="searchMap" class="button button-primary button-large">Search</a></div><div style="width: 100%; height: 600px"  id="mapContainer"></div></div>';
                break;
            default:
                $input = sprintf(
                    '<input %s id="%s" name="%s" type="%s" value="%s">',
                    $field['type'] !== 'color' ? 'class="regular-text"' : '',
                    $field['id'],
                    $field['id'],
                    $field['type'],
                    $db_value
                );
        }
        $output .= self::row_format( $label, $input );

        return '<div class="container-'.$field['id'].'">' . $output . '</div>';
    }

    /**
     * Generates the HTML for table rows.
     */
    private static function row_format( $label, $input ) {
        return sprintf(
            '<div class="lc_meta_box">%s</br>%s</div>',
            $label,
            $input
        );
    }

    /**
     * Sasve metabox valides
     *
     */

    public static function lc_save_meta_fields_value($post_id, $metaboxes)
    {
        foreach ($metaboxes as $metabox) {
            if ( ! isset( $_POST[ $metabox['id'].'_nonce' ] ) ){
                return $post_id;
            }

            if ( !wp_verify_nonce( $_POST[ $metabox['id'].'_nonce' ], $metabox['id'] ) )
                return $post_id;

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
                return $post_id;

            foreach ( $metabox['fields'] as $field ) {
                if ( isset( $_POST[ $field['id'] ] ) ) {
                    switch ( $field['type'] ) {
                        case 'email':
                            $_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
                            break;
                        case 'text':
                            $_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
                            break;
                    }
                    update_post_meta( $post_id, $field['prefix'] . $field['id'], $_POST[ $field['id'] ] );
                } else if ( $field['type'] === 'checkbox' ) {
                    update_post_meta( $post_id, $field['prefix'] . $field['id'], '1' );
                }
            }
        }

        return true;
    }

    public static function lc_return_fields($arrayKey, $arrayValue, $array) {
        foreach ($array as $key => $val) {
            if ($val[$arrayKey] === $arrayValue) {
                return $array[$key]['fields'];
            }
        }
        return null;
    }

    /**
     * Hooks into WordPress' admin_footer function.
     * Adds scripts for media uploader.
     */
    public function admin_footer() {
        ?><script>
            jQuery(document).ready(function($){
                if ( typeof wp.media !== 'undefined' ) {
                    $('body').on('click', '.rational-metabox-media', function(e){
                        e.preventDefault();

                        var button = $(this),
                            custom_uploader = wp.media({
                                title: '<?php _e('Insert image', 'trainee'); ?>',
                                library : {
                                    // uncomment the next line if you want to attach image to the current post
                                    // uploadedTo : wp.media.view.settings.post.id,
                                    type : 'image'
                                },
                                button: {
                                    text: '<?php _e('Use this image'); ?>' // button label text
                                },
                                multiple: false // for multiple image selection set to true
                            }).on('select', function() { // it also has "open" and "close" events
                                var attachment = custom_uploader.state().get('selection').first().toJSON();
                                var id = button.attr('id').replace('_button', '_placeholder');
                                var imgPlaceholder = button.attr('id').replace('_button', '_image_thumb');
                                $("#"+id).val(attachment.url);
                                $("#"+imgPlaceholder).html('<img src="'+ attachment.url +'" />');
                            })
                                .open();
                    });
                }
            });
        </script>
        <style>
            .lc_meta_box input {
                width: 100%;
                margin-bottom: 10px;
            }
            .image_thumb_holder img {
                width: 100%;
                height: auto;
            }
        </style>
        <?php
    }
}