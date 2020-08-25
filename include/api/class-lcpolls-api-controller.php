<?php
/**
 * LcPolls_Api_Controller
 *
 * Controller for api routes
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Api_Controller
{
    private static $instance = null;

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance ()
    {

        if ( null == self::$instance ) {
            self::$instance = new LcPolls_Api_Controller();
        }

        return self::$instance;
    }

    /**
     * Saves poll votes for the particular poll question and returns statistics.
     */
    public static function send_poll_answer(WP_REST_Request $request)
    {
        $pollId = sanitize_text_field($request->get_param('pollId'));
        $prevPollId = sanitize_text_field($request->get_param('prevPollId'));
        $newerPollId = sanitize_text_field($request->get_param('newerPollId'));
        $answers = $request->get_param('answers');
        $statistics = unserialize(LcPolls::lcpolls_get_post_meta('lc_polls_statistics', $pollId));
        $savedQuestionOptions = unserialize(LcPolls::lcpolls_get_post_meta('lc_poll_answers', $pollId));
        $startDate = LcPolls::lcpolls_get_post_meta('lc_polls_start_date', $pollId);
        $comments_count = get_comments_number($pollId);


        $commentArgs = array(
            'status'  => 'approve',
            'number'  => '5',
            'post_id' => $pollId,
        );
        $comments = get_comments( $commentArgs );


        $savedAnswers = (array)$savedQuestionOptions->options;

        if(!$statistics) {

            $statistics = self::setInitialStatistics($answers, $savedAnswers);
            update_post_meta( $pollId, 'lc_polls_statistics', serialize((object)$statistics['raw']));

            $userData = [ 'answers'=> $statistics['userAnswers' ], 'pollId'=>$pollId ];

            if(is_user_logged_in()) {
                $user = wp_get_current_user();
                update_user_meta($user->ID, 'poll_' . $pollId, serialize($userData));
            } else {
                setcookie('poll_' . $pollId, htmlentities(json_encode($userData)), time() + (10 * 365 * 24 * 60 * 60), "/");
            }

            $viewData  = (object)[
                'questionID'=> $pollId,
                'answerType'=> $savedQuestionOptions->answerType,
                'questionPrivacy'=> $savedQuestionOptions->questionPrivacy,
                'question' => get_the_title($pollId),
                'options'  => $savedQuestionOptions->options,
                'results' => (object)$statistics['raw'],
                'answeredData' => [
                    'answers' => $statistics['userAnswers']
                ],
                'formatedAnsweredData' => $statistics['formatted'],
                'comments_count' => $comments_count,
                'comments' => $comments,
                'startDate' => $startDate,
                'prevPollId' => $prevPollId,
                'newerPoll' =>$newerPollId
            ];

            ob_start();
            include LCPOLLS_PLUGIN_DIR . 'views/public/shortcodes/questions/answered-question.php';
            $answeredQuestionTpl = ob_get_clean();

            ob_start();
            include LCPOLLS_PLUGIN_DIR . 'views/public/shortcodes/questions/poll-results.php';
            $statisticsTpl = ob_get_clean();

            return new WP_REST_Response(['status'=>'OK', 'message' => __('Successfully finished poll', 'lcpolls'), 'statistics'=>$statistics['formatted'], 'answeredQuestionTpl'=>$answeredQuestionTpl, 'statisticsTpl' => $statisticsTpl], 200);
        }


        $newStatistics = new stdClass();
        $newTotal = 0;
        $userAnswers = [];

        foreach ($statistics as $key=>$s) {
            if(0 === strncmp ( 'option_', $key, 7 )){
                $newStatistics->$key = $s + $answers[$key];

                if($answers[$key] === 1){
                    $userAnswers[$key] = $savedAnswers[$key];
                    $newTotal++;
                }

            }
            else if($key === 'totalUnique'){
                $newStatistics->$key = $s + 1;
            }
            else {
                $newStatistics->$key = $s;
            }
        }

        $newStatistics->total = $statistics->total + $newTotal;

        $formattedStatistics = [ 'totalUnique' => $newStatistics->totalUnique, 'total' => $newStatistics->total ];

        foreach ($answers as $key=>$answer) {
            $option = $savedAnswers[$key];
            $percent = ($newStatistics->$key / $formattedStatistics['total']) * 100;
            $formattedStatistics[$key] = ['percent' =>round($percent), 'option' => $option];
        }

        update_post_meta( $pollId, 'lc_polls_statistics', serialize($newStatistics));

        $userData = [ 'answers'=> $userAnswers, 'pollId'=>$pollId ];
        if(is_user_logged_in()) {
            $user = wp_get_current_user();
            update_user_meta($user->ID, 'poll_' . $pollId, serialize($userData));
        } else {
            setcookie('poll_' . $pollId, htmlentities(json_encode($userData)), time() + (10 * 365 * 24 * 60 * 60), "/");
        }


        $viewData  = (object)[
            'questionID'=> $pollId,
            'answerType'=> $savedQuestionOptions->answerType,
            'questionPrivacy'=> $savedQuestionOptions->questionPrivacy,
            'question' => get_the_title($pollId),
            'options'  => $savedQuestionOptions->options,
            'results' => $newStatistics,
            'answeredData' => [
                'answers' => $userAnswers
            ],
            'formatedAnsweredData' => $formattedStatistics,
            'comments_count' => $comments_count,
            'comments' => $comments,
            'startDate' => $startDate,
            'prevPollId' => $prevPollId,
            'newerPoll' => $newerPollId
        ];

        ob_start();
        include LCPOLLS_PLUGIN_DIR . 'views/public/shortcodes/questions/answered-question.php';
        $answeredQuestionTpl = ob_get_clean();

        ob_start();
        include LCPOLLS_PLUGIN_DIR . 'views/public/shortcodes/questions/poll-results.php';
        $statisticsTpl = ob_get_clean();

        return new WP_REST_Response(['status'=>'OK', 'message' => __('Successfully finished poll', 'lcpolls'), 'statistics'=>$formattedStatistics, 'answeredQuestionTpl'=>$answeredQuestionTpl, 'statisticsTpl' => $statisticsTpl], 200);

//        return new WP_REST_Response(['pollId' => $pollId, 'statistics'=>$newStatistics], 200);
    }


    /**
     * Load more comments
     */
    public static function loadMoreComments(WP_REST_Request $request)
    {
        $pollId = sanitize_text_field($request->get_param('pollId'));
        $offset = sanitize_text_field($request->get_param('comment_offset'));

        $commentArgs = array(
            'offset'  => $offset,
            'status'  => 'approve',
            'number'  => '5',
            'post_id' => $pollId,
        );

        $comments = get_comments( $commentArgs );

        ob_start();
        foreach ($comments as $comment): ?>
            <div class="lc-polls-user-commented border-bottom">
                <div class="lc-polls-commented-image">
                    <div class="avatar-lc-polls">
                        <img src="<?php echo get_avatar_url($comment->comment_author_email); ?>" alt="<?php echo $comment->comment_author; ?>">
                    </div>
                </div>
                <div class="lc-polls-commented-detauls">
                    <span class="name"><?php echo $comment->comment_author; ?></span>
                    <p><?php echo $comment->comment_content; ?></p>
                    <span class="lc-poll-commented-on"><?php echo sprintf(__('Commented %s ago', 'lcpolls'), human_time_diff(strtotime($comment->comment_date), current_time( 'U' ))); ?></span>
                </div>
            </div>
        <?php endforeach;
        $comments_tpl = ob_get_clean();
        return new WP_REST_Response(['status'=>'OK', 'comments_tpl'=>$comments_tpl, 'offset' => $offset], 200);
    }

    /**
     * VIew results action
     */
    public static function viewSavedStatistics(WP_REST_Request $request)
    {
        $pollID = sanitize_text_field($request->get_param('pollId'));
        $prevPollId = sanitize_text_field($request->get_param('prevPollId'));
        $userID = get_current_user_id();
        $statistics = unserialize(LcPolls::lcpolls_get_post_meta('lc_polls_statistics', $pollID));
        $formattedStatistics = [];
        $savedQuestionOptions = unserialize(LcPolls::lcpolls_get_post_meta('lc_poll_answers', $pollID));
        $startDate = LcPolls::lcpolls_get_post_meta('lc_polls_start_date', $pollID);
        $comments_count = get_comments_number($pollID);


        $commentArgs = array(
            'status'  => 'approve',
            'number'  => '5',
            'post_id' => $pollID,
        );
        $comments = get_comments( $commentArgs );
        $savedAnswers = (array)$savedQuestionOptions->options;

        $userAnswers = !$userID && isset($_COOKIE['poll_'.$pollID]) ? json_decode(html_entity_decode($_COOKIE['poll_'.$pollID]))->answers : unserialize(LcPolls::lcpolls_get_user_meta('poll_'.$pollID, $userID))['answers'];


        $formattedStatistics['total'] = $statistics->total;
        $formattedStatistics['totalUnique'] = $statistics->total;
        foreach ($savedAnswers as $key=>$answer) {
            $percent = ($statistics->$key / $statistics->total) * 100;
            $formattedStatistics[$key] = ['percent' =>round($percent), 'option' => $savedAnswers[$key]];
        }

        $viewData  = (object)[
            'questionID'=> $pollID,
            'answerType'=> $savedQuestionOptions->answerType,
            'questionPrivacy'=> $savedQuestionOptions->questionPrivacy,
            'question' => get_the_title($pollID),
            'options'  => $savedQuestionOptions->options,
            'results' => $statistics,
            'answeredData' => [
                'answers' => $userAnswers,
            ],
            'formatedAnsweredData' => $formattedStatistics,
            'comments_count' => $comments_count,
            'comments' => $comments,
            'startDate' => $startDate,
            'prevPollId' => $prevPollId
        ];

        ob_start();
        include LCPOLLS_PLUGIN_DIR . 'views/public/shortcodes/questions/poll-results.php';
        $statisticsTpl = ob_get_clean();

        return new WP_REST_Response(['status'=>'OK', 'statisticsTpl' => $statisticsTpl], 200);
    }

    /**
     * Post comment
     */
    public function postQuestionComment(WP_REST_Request $request)
    {
        $pollId = sanitize_text_field($request->get_param('pollId'));
        $comment = sanitize_text_field($request->get_param('comment'));
        $userID = get_current_user_id();

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if(!$userID) {
            return new WP_Error(400, _('You must be logged in to comment.', 'lcpolls'));
        }

        $args = [
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
            'comment_approved' => 1,
            'comment_author'   => get_the_author_meta('display_name', $userID),
            'comment_author_email'   => get_the_author_meta('user_email', $userID),
            'comment_author_IP'   => $ip,
            'comment_author_url'   => get_the_author_meta('user_url', $userID),
            'comment_content'   => $comment,
            'comment_post_ID'   => $pollId,
            'user_id' => $userID
        ];

        $commentID = wp_insert_comment ($args);

        if(!$commentID)
            return new WP_Error(500, _('Error inserting comment.', 'lcpolls'));

        $comment  = get_comment($commentID);

        ob_start();
        ?>
            <div class="lc-polls-user-commented border-bottom">
                <div class="lc-polls-commented-image">
                    <div class="avatar-lc-polls">
                        <img src="<?php echo get_avatar_url($comment->comment_author_email); ?>" alt="<?php echo $comment->comment_author; ?>">
                    </div>
                </div>
                <div class="lc-polls-commented-detauls">
                    <span class="name"><?php echo $comment->comment_author; ?></span>
                    <p><?php echo $comment->comment_content; ?></p>
                    <span class="lc-poll-commented-on"><?php echo sprintf(__('Commented %s ago', 'lcpolls'), human_time_diff(strtotime($comment->comment_date), current_time( 'U' ))); ?></span>
                </div>
            </div>
        <?php
        $new_comment_tpl = ob_get_clean();
        return new WP_REST_Response(['status'=>'OK', 'new_comment_tpl' => $new_comment_tpl], 200);
    }

    /**
     * Get older question
     */

    public function getOlderQuestion(WP_REST_Request $request)
    {
        $currentQuestionDate = sanitize_text_field($request->get_param('current_question_date'));
        $isNext = sanitize_text_field($request->get_param('isNext'));
        $tpl = self::getQuestionByDate($currentQuestionDate, '<', ['isNext'=>$isNext]);

        return new WP_REST_Response(['status'=>'OK', 'tpl' => $tpl], 200);
    }

    /**
     * Navigate trough the questions
     */
    public function navQuestions(WP_REST_Request $request)
    {
        $navDirection = sanitize_text_field($request->get_param('navDirection'));
        $ID = $navDirection === 'older' ? sanitize_text_field($request->get_param('olderPoll')) : sanitize_text_field($request->get_param('newerPoll'));
        $currentPoll = $navDirection === 'older' ? sanitize_text_field($request->get_param('currentPoll')): '';
        $isPopupVal = sanitize_text_field($request->get_param('isPopup'));
        $isPopup = isset($isPopupVal) && $request->get_param('isPopup') === 'isPopup';

        $statistics = unserialize(LcPolls::lcpolls_get_post_meta('lc_polls_statistics', $ID));
        $formattedStatistics = [];
        $savedQuestionOptions = unserialize(LcPolls::lcpolls_get_post_meta('lc_poll_answers', $ID));
        $startDate = LcPolls::lcpolls_get_post_meta('lc_polls_start_date', $ID);
        $comments_count = get_comments_number($ID);
        $isAnswered = false;

        $args = [
            'post_type' => 'lc_polls',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'ignore_sticky_posts' => true,
            'order' => 'DESC',
            'meta_key' => 'lc_polls_start_date',
            'orderby'   => 'meta_value date',
            'meta_query' => array(
                array(
                    'key' => 'lc_polls_start_date',
                    'value' => date("Y-m-d"),
                    'compare' => '<=',
                    'type' => 'DATE'
                )
            ),
        ];

        $questions = new WP_Query($args);



        foreach($questions->posts as $key => $value) {
            if($value->ID == $ID){
                $prevPollId = isset($questions->posts[$key + 1]) ? $questions->posts[$key + 1]->ID : '';
                $newerPollId = isset($questions->posts[$key - 1]) ? $questions->posts[$key - 1]->ID: '';
                break;
            }
        }

//        $prevPollId =  $questions->get_posts()[0];

        $commentArgs = array(
            'status'  => 'approve',
            'number'  => '5',
            'post_id' => $ID,
        );
        $comments = get_comments( $commentArgs );

        $savedAnswers = (array)$savedQuestionOptions->options;

        if(is_user_logged_in()) {
            $user = wp_get_current_user();
            $isAnswered = unserialize(LcPolls::lcpolls_get_user_meta('poll_' .$ID, $user->ID ));
            $userAnswers = unserialize(LcPolls::lcpolls_get_user_meta('poll_'.$ID, $user->ID))['answers'];
        } else {
            if(isset($_COOKIE['poll_' . $qotd->ID])) {
                $isAnswered = (array)json_decode(html_entity_decode($_COOKIE['poll_' . $ID]));
                $userAnswers = json_decode(html_entity_decode($_COOKIE['poll_'.$ID]))->answers;
            }
        }

        $formattedStatistics['total'] = $statistics->total;
        $formattedStatistics['totalUnique'] = $statistics->total;
        foreach ($savedAnswers as $key=>$answer) {
            $percent = ($statistics->$key / $statistics->total) * 100;
            $formattedStatistics[$key] = ['percent' =>round($percent), 'option' => $savedAnswers[$key]];
        }

        $data  = (object)[
            'questionID'=> $ID,
            'answerType'=> $savedQuestionOptions->answerType,
            'questionPrivacy'=> $savedQuestionOptions->questionPrivacy,
            'question' => get_the_title($ID),
            'options'  => $savedQuestionOptions->options,
            'results' => $statistics,
            'answeredData' => [
                'answers' => $userAnswers,
            ],
            'formatedAnsweredData' => $formattedStatistics,
            'comments_count' => $comments_count,
            'comments' => $comments,
            'startDate' => $startDate,
            'isNext' => 'isNext',
            'prevPollId' => $prevPollId,
            'newerPoll' => $newerPollId
        ];

        $answeredQ = $isPopup ? 'poll-results' : 'answered-question';
        $questions = $isPopup ? 'next-question' : 'question';

        if($savedQuestionOptions->questionPrivacy === 'private') {

            if(is_user_logged_in()) {
                $tpl =  $isAnswered ? LcPolls::view('shortcodes/questions/' . $answeredQ, $data) : LcPolls::view('shortcodes/questions/' . $questions, $data);
            } else {
                $tpl =  LcPolls::view('shortcodes/auth/login', $data);
            }
        } else {
            $tpl =  $isAnswered ? LcPolls::view('shortcodes/questions/' . $answeredQ, $data) : LcPolls::view('shortcodes/questions/' . $questions, $data);
        }

        return new WP_REST_Response(['status'=>'OK', 'tpl' => $tpl], 200);
    }


    private static function setInitialStatistics($answers, $savedAnswers)
    {
        $total = 0;
        $rawStatistics = [ 'totalUnique' => 1 ];
        $userAnswers = [];

        foreach ($answers as $key=>$answer) {
            $rawStatistics[$key] = $answer;

            if($answer == 1){
                $userAnswers[$key] = $savedAnswers[$key];
                $total++;
            }
        }

        $rawStatistics['total'] = $total;

        $statistics = [ 'totalUnique' => $rawStatistics['totalUnique'], 'total' => $rawStatistics['total'] ];

        foreach ($answers as $key=>$answer) {
            $option = $savedAnswers[$key];
            $percent = ($answer / $rawStatistics['total']) * 100;
            $statistics[$key] = ['percent' =>round($percent), 'option' => $option];
        }

        return [ 'raw' => $rawStatistics, 'formatted' => $statistics, 'userAnswers' => $userAnswers ];
    }

    private function getQuestionByDate($currentQuestionDate, $operator, $params =[])
    {
        $args = [
            'post_type' => 'lc_polls',
            'post_status' => 'publish',
            'posts_per_page' => 2,
            'ignore_sticky_posts' => true,
            'order' => 'DESC',
            'meta_key' => 'lc_polls_start_date',
            'orderby'   => 'meta_value date',
            'meta_query' => array(
                array(
                    'key' => 'lc_polls_start_date',
                    'value' => date("Y-m-d", strtotime($currentQuestionDate)),
                    'compare' => $operator,
                    'type' => 'DATE'
                )
            ),
        ];

        $questions = new WP_Query($args);
        $query =  $questions->get_posts();
        $qotd =  $query[0];


        if(!$qotd){
            return LcPolls::view('shortcodes/questions/no-questions');
        }

        $statistics = unserialize(LcPolls::lcpolls_get_post_meta('lc_polls_statistics', $qotd->ID));
        $formattedStatistics = [];
        $savedQuestionOptions = unserialize(LcPolls::lcpolls_get_post_meta('lc_poll_answers', $qotd->ID));
        $startDate = LcPolls::lcpolls_get_post_meta('lc_polls_start_date', $qotd->ID);
        $comments_count = get_comments_number($qotd->ID);
        $isAnswered = false;
        $prevPollId =  $query[1];

        $commentArgs = array(
            'status'  => 'approve',
            'number'  => '5',
            'post_id' => $qotd->ID,
        );
        $comments = get_comments( $commentArgs );

        $savedAnswers = (array)$savedQuestionOptions->options;

        if(is_user_logged_in()) {
            $user = wp_get_current_user();
            $isAnswered = unserialize(LcPolls::lcpolls_get_user_meta('poll_' .$qotd->ID, $user->ID ));
            $userAnswers = unserialize(LcPolls::lcpolls_get_user_meta('poll_'.$qotd->ID, $user->ID))['answers'];
        } else {
            if(isset($_COOKIE['poll_' . $qotd->ID])) {
                $isAnswered = (array)json_decode(html_entity_decode($_COOKIE['poll_' . $qotd->ID]));
                $userAnswers = json_decode(html_entity_decode($_COOKIE['poll_'.$qotd->ID]))->answers;
            }
        }

        $formattedStatistics['total'] = $statistics->total;
        $formattedStatistics['totalUnique'] = $statistics->total;
        foreach ($savedAnswers as $key=>$answer) {
            $percent = ($statistics->$key / $statistics->total) * 100;
            $formattedStatistics[$key] = ['percent' =>round($percent), 'option' => $savedAnswers[$key]];
        }

        $data  = (object)[
            'questionID'=> $qotd->ID,
            'answerType'=> $savedQuestionOptions->answerType,
            'questionPrivacy'=> $savedQuestionOptions->questionPrivacy,
            'question' => get_the_title($qotd->ID),
            'options'  => $savedQuestionOptions->options,
            'results' => $statistics,
            'answeredData' => [
                'answers' => $userAnswers,
            ],
            'formatedAnsweredData' => $formattedStatistics,
            'comments_count' => $comments_count,
            'comments' => $comments,
            'startDate' => $startDate,
            'isNext' => $params['isNext'],
            'prevPollId' => $prevPollId->ID
        ];

        if($savedQuestionOptions->questionPrivacy === 'private') {
            if(is_user_logged_in()) {
                $tpl = $isAnswered ? LcPolls::view('shortcodes/questions/poll-results', $data) : LcPolls::view('shortcodes/questions/next-question', $data);
            } else {
                $tpl = LcPolls::view('shortcodes/auth/login', $data);
            }
        } else {
            $tpl = $isAnswered ? LcPolls::view('shortcodes/questions/poll-results', $data) : LcPolls::view('shortcodes/questions/next-question', $data);
        }

        return  $tpl;
    }

    /**
     * class constructor
     */
    public function __construct ()
    {

    }

    /**
     * Initializes WordPress hooks
     */
    public function init ()
    {

    }
}