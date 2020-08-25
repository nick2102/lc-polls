<?php

/**
 * LcPolls_Question_Metaboxes
 *
 * Creating custom post types Metaboxes
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Question_Metaboxes
{
    private static $instance = null;

    private static $metaboxSettings = [];

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance()
    {

        if (null == self::$instance) {
            self::$instance = new LcPolls_Question_Metaboxes();
        }

        return self::$instance;
    }

    /**
     * class constructor
     */
    public function __construct()
    {

        // class initialization
        $this->init();
    }

    /**
     * Register metaboxes
     */

    public function lcpolls_register_questionnaire_metaboxes()
    {

        add_meta_box(
            'lc_question_answers',
            __('Question Answers', 'lcpolls'),
            [$this, 'lcpolls_question_content_html'],
            'lc_polls',
            'normal',
            'high'
        );

        add_meta_box(
            'lc_polls_start_date_box',
            __( 'Start date', 'lcpolls' ),
            [ $this, 'lc_polls_start_date_html' ],
            'lc_polls',
            'side',
            'default'
        );

    }

    /**
     * Questionnaire Content Html
     */
    public function lcpolls_question_content_html()
    {
        global $post;
        $qMeta = unserialize(LcPolls::lcpolls_get_post_meta('lc_poll_answers', $post->ID));
        $value = $qMeta ? json_encode($qMeta, JSON_UNESCAPED_SLASHES) : '';
        include LCPOLLS_PLUGIN_DIR . '/views/admin/poll-panel.php';

        echo '<input type="hidden" name="lc_poll_answers" value=\'' . $value . '\'>';
        wp_nonce_field('_lc_poll_answers_nonce', 'lc_poll_answers_nonce');
        echo '<style>#save-post,#post-preview,#minor-publishing-actions,#publish{ display: none; } </style>';
    }

    //Start date Html
    public function lc_polls_start_date_html( $post )
    {
        wp_nonce_field( '_lc_polls_start_date_nonce', 'lc_polls_start_date_nonce' ); ?>

        <p><?php _e('Select start date of the Question', 'lcpolls'); ?></p>

        <p>
            <label for="lc_polls_start_date"><?php _e( 'Start date', 'lcpolls' ); ?></label><br>
            <input required style="width: 100%;" readonly type="text" name="lc_polls_start_date" id="lc_polls_start_date" value="<?php echo LcPolls::lcpolls_get_post_meta( 'lc_polls_start_date' ); ?>">
        </p>
        <?php
    }

    /**
     * Save poll
     */
    public function lcpolls_save_questionnaire($post_id)
    {
        $statistics = LcPolls::lcpolls_get_post_meta('lc_polls_statistics', $post_id);

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! isset( $_POST['lc_poll_answers_nonce'] ) || ! wp_verify_nonce( $_POST['lc_poll_answers_nonce'], '_lc_poll_answers_nonce' ) ) return;
        if ( ! isset( $_POST['lc_polls_start_date_nonce'] ) || ! wp_verify_nonce( $_POST['lc_polls_start_date_nonce'], '_lc_polls_start_date_nonce' ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['lc_poll_answers'] ) ){
            $formData = json_decode(stripslashes($_POST['lc_poll_answers']));
            $statisticsData = new stdClass();
            $statisticsData->total = 0;
            $statisticsData->totalUnique = 0;
            foreach ($formData->options as $key=> $value){
                $statisticsData->$key = 0;
            }

            update_post_meta( $post_id, 'lc_poll_answers', serialize($formData) );
            update_post_meta( $post_id, 'lc_polls_statistics', serialize($statisticsData) );
        }

        if ( isset( $_POST['lc_polls_start_date'] ) ){
            update_post_meta( $post_id, 'lc_polls_start_date', sanitize_text_field($_POST['lc_polls_start_date']) );
        }

    }

    public function init()
    {
        add_action( 'add_meta_boxes', [ $this, 'lcpolls_register_questionnaire_metaboxes' ] );
        add_action( 'save_post', [ $this, 'lcpolls_save_questionnaire' ] );
    }
}