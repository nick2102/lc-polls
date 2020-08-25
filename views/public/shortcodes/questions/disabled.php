<div id="lc_polls_from" class="question-of-the-day">
    <h2 class="lc-poll-heading"><?php _e('Question of the day'); ?></h2>
    <p class="lc-poll-question"><?php echo $viewData->question; ?></p>
    <p><?php _e('Poll is closed for new responses.'); ?></p>
    <p class="user-answers-statistics">
        <?php _e('Your answers are:'); ?>
    <ul class="user-answers-list">
        <?php foreach ($viewData->answeredData['answers'] as $a): ?>
            <li><?php echo $a; ?></li>
        <?php endforeach; ?>
    </ul>
    </p>

    <button style="margin: 20px 0;" data-poll-id="<?php echo $viewData->questionID; ?>" id="lc_poll_submit_answered" type="submit" class="lc-polls-btn lc-polls-btn-primary"><?php _e('See Results', 'lcpolls'); ?></button>
    <div class="lc-polls-footer-results results lc-polls-gray-text">
        <?php
        if($viewData->results):
            $answ = $viewData->results->total === 1 ? __('answer', 'lcpolls') : __('answers', 'lcpolls');
            echo sprintf(__('We received %s %s', 'lcpolls'), $viewData->results->total, $answ);
        else:
            _e('No answers for this question yet. Be the first one to answer?',  'lcpolls');
        endif;
        ?>
    </div>
</div>
