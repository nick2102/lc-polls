<div id="poll_container">
    <div id="lc_polls_from" class="question-of-the-day">
        <h2 class="lc-poll-heading"><?php _e('Question of the day'); ?></h2>
        <p class="lc-poll-question"><?php echo $viewData->question; ?></p>

        <div class="statistics-results-count">
            <?php
            if($viewData->results):
                $answ = $viewData->results->total === 1 ? __('answer', 'lcpolls') : __('answers', 'lcpolls');
                echo sprintf(__('Results from %s %s', 'lcpolls'), $viewData->results->total, $answ);
            else:
                _e('No answers for this question yet. Be the first one to answer?',  'lcpolls');
            endif;
            ?>
        </div>

        <?php if ($viewData->answeredData['answers']): ?>
            <p class="user-answers-statistics"> <?php _e('Your answers are:'); ?>
            <ul class="user-answers-list">
                <?php foreach ($viewData->answeredData['answers'] as $a): ?>
                    <li><?php echo $a; ?></li>
                <?php endforeach; ?>
            </ul>
            </p>
        <?php endif; ?>

        <div class="lc-polls-statistics">
            <?php foreach ($viewData->formatedAnsweredData as $key=>$a):
                if(0 === strncmp ( 'option_', $key, 7 )): ?>
                <div>
                    <div class="lc-polls-stats-answer"><?php echo $a['option']; ?></div>
                    <div class="lc-polls-stats-charts">
                        <div class="lc-polls-chart-bar">
                            <div style="width: <?php echo $a['percent']; ?>%"></div>
                        </div>
                        <div class="lc-polls-chart-percent"><?php echo $a['percent']; ?>%</div>
                    </div>
                </div>
            <?php endif;
            endforeach; ?>
        </div>

        <hr>

        <div class="statistics-footer">
            <div>
                <button class="lc-polls-btn lc-statistics-open-comments"><?php _e('View Comments', 'lcpolls') ?> <span class="lc-stats-comment-count">(<?php echo $viewData->comments_count; ?>)</span></button>
                <div>
                    <?php $lastComment = $viewData->comments[0];
                    if($lastComment):?>
                    <div class="lc-polls-user-commented">
                        <div class="lc-polls-commented-image">
                            <div class="avatar-lc-polls">
                                <img src="<?php echo get_avatar_url($lastComment->comment_author_email); ?>" alt="<?php echo $lastComment->comment_author; ?>">
                            </div>
                        </div>
                        <div class="lc-polls-commented-detauls">
                            <span class="name" style="margin-bottom: 0;"><?php echo $lastComment->comment_author; ?></span>
                            <span class="lc-poll-commented-on"><?php echo sprintf(__('Commented %s ago', 'lcpolls'), human_time_diff(strtotime($lastComment->comment_date), current_time( 'U' ))); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <?php if($viewData->prevPollId != ''): ?>
                    <button id="lc-polls-next-q"
                            data-nav-direction="older"
                            data-older-poll="<?php echo $viewData->prevPollId; ?>"
                            data-current-poll="<?php echo $viewData->questionID; ?>"
                            data-prev-post-id="<?php echo $viewData->prevPollId ?>" data-current-question-date="<?php echo $viewData->startDate; ?>" class="lc-polls-btn"><?php _e('Next Question', 'lcpolls') ?>
                        <img src="<?php echo LCPOLLS_PLUGIN_URL; ?>/assets/images/angle-right.svg" alt="">
                        <div class="lc-polls-loader loadingBtn"></div>
                    </button>
                    <p class="prev-question-title"><?php echo get_the_title($viewData->prevPollId); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div id="lc-comment-list" class="lc-poll-comments">
            <div class="statistics-footer">
                <div>
                    <div class="lc-back-to-stats"><img src="<?php echo LCPOLLS_PLUGIN_URL; ?>/assets/images/chevron-left-lc.svg" alt=""></div>
                    <span class="comments-back-text"><?php _e('Comments', 'lcpolls') ?></span>
                </div>
                <div>
                    <?php if(is_user_logged_in()): ?>
                        <button data-container="#lc-add-comment .lc-polls-comments-container" id="lc-open-add-comment" class="lc-polls-btn-primary lc-polls-btn"><?php _e('Add Comment', 'lcpolls') ?></button>
                    <?php else: ?>
                        <p class="lc-poll-commented-on"><?php _e('Please first login so you can leave a comment.', 'lcpolls'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lc-polls-comments-container">
                <p class="lc-poll-question"><?php echo $viewData->question; ?></p>
                <?php foreach ($viewData->comments as $comment): ?>
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
                <?php endforeach; ?>

                <?php if(count((array)$viewData->comments) >= 5) : ?>
                <button data-poll-id="<?php echo $viewData->questionID; ?>" data-poll-offset="5" class="lc-polls-btn lc-polls-load-more-comments">
                    <?php _e('Load More Comments', 'lcpolls'); ?>
                    <div class="lc-polls-loader loadingBtn"></div>
                </button>
                <?php elseif(count((array)$viewData->comments) == 0) : ?>
                    <p class="no-comments-atm lc-poll-commented-on"><?php _e('No comments at the moment.', 'lcpolls') ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div id="lc-add-comment" class="lc-poll-comments lc-polls-add-comment">
            <div class="statistics-footer">
                <div style="width: 100%">
                    <a data-container="#lc-comment-list .lc-polls-comments-container" class="lc-back-to-comments"><img src="<?php echo LCPOLLS_PLUGIN_URL; ?>/assets/images/chevron-left-lc.svg" alt=""></a>
                    <span class="comments-back-text"><?php _e('Add comment for', 'lcpolls') ?></span>
                </div>
            </div>
            <div class="lc-polls-comments-container">
                <p class="lc-poll-question"><?php echo $viewData->question; ?></p>

                <div class="lc-comment-box">
                    <p><?php _e('Write your comment here', 'lcpolls') ?></p>

                    <textarea id="lc-poll-comment-field"></textarea>

                    <div>
                        <button data-container="#lc-comment-list .lc-polls-comments-container" data-poll-id="<?php echo $viewData->questionID; ?>" id="lc-poll-post-comment" class="lc-polls-btn-primary lc-polls-btn"><?php _e('Post Comment', 'lcpolls') ?>
                            <div class="lc-polls-loader loadingBtn"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
