jQuery(document).ready(function ($) {
    // Send poll answer
    $('body').on('submit', '.lc_poll_form', function (e) {
        e.preventDefault();
        var pollId = $(this).attr('data-poll-id');
        var prevPollId = $(this).attr('data-prev-poll-id');
        var isNext = $(this).attr('data-poll-is-next');
        var newerPollId = $('.lc-left.poll-nav').attr('data-newer-poll');

        var isVaild = [];
        $(this).find('input').each(function(){
            if ($(this).prop('checked')!==true){
                isVaild.push($(this))
            }
        });

        if(isVaild.length === $(this).find('input').length){
            $('.no-selection-error').css('display', 'block');
            return false;
        }

        var answers = generatePollAnswer($(this).find('input'));
        $('#lc_poll_submit').prop('disabled', true);
        $(this).children('.loadingBtn').css('display', 'inline-block');

        lcRequest.endpoint = '/send-poll-answer'
        lcRequest.method = 'post';
        lcRequest.addPostParam('pollId', pollId);
        lcRequest.addPostParam('prevPollId', prevPollId);
        lcRequest.addPostParam('answers', answers);
        lcRequest.addPostParam('newerPollId', newerPollId);
        lcRequest.headers = { 'Content-Type': 'application/json', 'X-WP-Nonce' : window.restNonce };
        lcRequest.execute( function (response) {
             $('html,body').animate({ scrollTop: 0 }, 'slow');
            if(isNext !== 'isNext'){
                $('#poll_container').html(response.data.answeredQuestionTpl);
            }
            $('#poll_statistics_popup .popup-content').html(response.data.statisticsTpl);
            $('#poll_statistics_popup').fadeIn();
            $('.poll_statistics_popup_shadow').fadeIn().css('display', 'flex');
        });
    });

    $('body').on('click', '.close-statistics-popup', function (e) {
        e.preventDefault();
        $('.poll_statistics_popup_shadow').fadeOut();
        $('#poll_statistics_popup').fadeOut();
        setTimeout(function () {
            $('#poll_statistics_popup .popup-content').html('');
        }, 500);
    });

    $('body').on('click', '.lc-statistics-open-comments', function (e) {
        e.preventDefault();
         $('html,body').animate({ scrollTop: 0 }, 'slow');
        var additionalHeight = $('.lc-polls-comments-container').outerHeight();
        $('#lc-comment-list').addClass('active');
        $('#poll_statistics_popup').css('height', additionalHeight + 100);
    });

    $('body').on('click', '#lc-open-add-comment', function (e) {
        e.preventDefault();
        var heightContainer = $(this).attr('data-container');
         $('html,body').animate({ scrollTop: 0 }, 'slow');
        var additionalHeight = $(heightContainer).outerHeight();
        $('#lc-add-comment').addClass('active');
        $('#poll_statistics_popup').css('height', additionalHeight + 120);
    });


    $('body').on('click', '.lc-back-to-stats', function (e) {
        e.preventDefault();
        $('#lc-comment-list').removeClass('active');
        $('#poll_statistics_popup').css('height','');
    });

    $('body').on('click', '.lc-back-to-comments', function (e) {
        e.preventDefault();
        var heightContainer = $(this).attr('data-container');
         $('html,body').animate({ scrollTop: 0 }, 'slow');
        var additionalHeight = $(heightContainer).outerHeight();
        $('#lc-add-comment').removeClass('active');
        $('#poll_statistics_popup').css('height', additionalHeight + 100);
    });



    $('body').on('click', '.lc-polls-load-more-comments', function (e) {
        e.preventDefault();
        var pollId = $(this).attr('data-poll-id');
        var offset = $(this).attr('data-poll-offset');
        var self = $(this);
        $(this).children('.loadingBtn').css('display', 'inline-block');
        lcRequest.endpoint = '/load-more-comments'
        lcRequest.method = 'get';
        lcRequest.addGetParam('pollId', pollId);
        lcRequest.addGetParam('comment_offset', offset);
        lcRequest.headers = { 'Content-Type': 'application/json', 'X-WP-Nonce' : window.restNonce };
        lcRequest.execute(function (results) {
            var newOffset = parseInt(offset) + 5;

            self.attr('data-poll-offset', newOffset);

            if(results.data.comments_tpl === '') {
                self.before( "<p>"+ window.lcpollsTranslations.noMoreComments +"</p>" );
                self.hide();
                return;
            }
            self.before( results.data.comments_tpl );

            var additionalHeight = $('.lc-polls-comments-container').outerHeight();
            $('#poll_statistics_popup').css('height', additionalHeight + 100);
        });
    });

    $('body').on('click', '#lc_poll_submit_answered', function (e) {
        e.preventDefault();
        var pollId = $(this).attr('data-poll-id');
        var prevPollId = $(this).attr('data-prev-poll-id');
        var self = $(this);
        self.prop('disabled', true);
        $(this).children('.loadingBtn').css('display', 'inline-block');
        lcRequest.endpoint = '/view-results'
        lcRequest.method = 'post';
        lcRequest.addPostParam('pollId', pollId);
        lcRequest.addPostParam('prevPollId', prevPollId);
        lcRequest.headers = { 'Content-Type': 'application/json', 'X-WP-Nonce' : window.restNonce };
        lcRequest.execute(function (response) {
             $('html,body').animate({ scrollTop: 0 }, 'slow');
            $('#poll_statistics_popup .popup-content').html(response.data.statisticsTpl);
            $('.poll_statistics_popup_shadow, #poll_statistics_popup').fadeIn();
            $('.poll_statistics_popup_shadow').css('display', 'flex')
            self.prop('disabled', false);
            // $('body').addClass('lc-fixed-body');
        });
    });

    $('body').on('click', '#lc-poll-post-comment', function (e) {
        e.preventDefault();
        var comment = $('#lc-poll-comment-field').val();

        if(comment === '') {
            return $('#lc-poll-comment-field').css('border', '1px solid red');
        }
        $('#lc-poll-comment-field').css('border', 'unset');
        var pollId = $(this).attr('data-poll-id');
        var self = $(this);
        self.prop('disabled', true);
        $(this).children('.loadingBtn').css('display', 'inline-block');
        lcRequest.endpoint = '/post-question-comment'
        lcRequest.method = 'post';
        lcRequest.addPostParam('pollId', pollId);
        lcRequest.addPostParam('comment', comment);
        lcRequest.headers = { 'Content-Type': 'application/json', 'X-WP-Nonce' : window.restNonce };
        lcRequest.execute(function (response) {
             $('html,body').animate({ scrollTop: 0 }, 'slow');
            if($('.no-comments-atm').length){
                $('.no-comments-atm').remove();
            }
            var currentCount = parseInt($('.lc-stats-comment-count').html().replace('(', '').replace(')', ''));
            var newCommentCount = currentCount + 1;
            $('#lc-comment-list p.lc-poll-question').after(response.data.new_comment_tpl);
            $('#lc-add-comment').removeClass('active');
            $('.lc-statistics-open-comments').trigger('click');
            $('.lc-stats-comment-count').text('(' + newCommentCount + ')');
            $('#lc-poll-comment-field').val('');
            self.prop('disabled', false);
        });
    });

    $('body').on('change', '.lc-polls-option-input', function () {
        $('.no-selection-error').css('display', 'none');
    });


    // $('body').on('click', '#lc-polls-next-q', function (e) {
    //     e.preventDefault();
    //     var date = $(this).attr('data-current-question-date');
    //     var self = $(this);
    //     var prevPollId = $(this).attr('data-prev-poll-id');
    //     self.prop('disabled', true);
    //     $(this).children('.loadingBtn').css('display', 'inline-block');
    //     lcRequest.endpoint = '/get-old-question'
    //     lcRequest.method = 'get';
    //     lcRequest.addGetParam('current_question_date', date);
    //     lcRequest.addGetParam('isNext', true);
    //     lcRequest.addGetParam('prevPollId', prevPollId);
    //     lcRequest.headers = { 'Content-Type': 'application/json', 'X-WP-Nonce' : window.restNonce };
    //     lcRequest.execute(function (response) {
    //         $('.popup-content').html(response.data.tpl);
    //         self.prop('disabled', false);
    //     });
    // });

    $('body').on('click', '#lc-polls-next-q', function (e) {
        e.preventDefault();
        var self = $(this);
        var navDirection = self.attr('data-nav-direction');
        var olderPoll = self.attr('data-older-poll');
        var newerPoll = self.attr('data-newer-poll');
        var currentPoll = self.attr('data-current-poll');
        self.prop('disabled', true);
        $(this).children('.loadingBtn').css('display', 'inline-block');

        if(navDirection === 'older' && olderPoll === '')
            return;

        if(navDirection !== 'older' && newerPoll === '')
            return;

        lcRequest.endpoint = '/nav-questions'
        lcRequest.method = 'get';
        lcRequest.addGetParam('olderPoll', olderPoll);
        lcRequest.addGetParam('currentPoll', currentPoll);
        lcRequest.addGetParam('newerPoll', newerPoll);
        lcRequest.addGetParam('navDirection', navDirection);
        lcRequest.addGetParam('isPopup', 'isPopup');
        lcRequest.headers = { 'Content-Type': 'application/json', 'X-WP-Nonce' : window.restNonce };
        lcRequest.execute(function (response) {
            $('.popup-content').html(response.data.tpl);
            self.prop('disabled', false);
        });
    });

    $('body').on('click', '.poll-nav', function (e) {
        e.preventDefault();
        var self = $(this);
        var navDirection = self.attr('data-nav-direction');
        var olderPoll = self.attr('data-older-poll');
        var newerPoll = self.attr('data-newer-poll');
        var currentPoll = self.attr('data-current-poll');


        if(navDirection === 'older' && olderPoll === '')
            return;

        if(navDirection !== 'older' && newerPoll === '')
            return;


        $('.prev-next.loadingBtn').css('display', 'block');
        self.prop('disabled', true);

        lcRequest.endpoint = '/nav-questions'
        lcRequest.method = 'get';
        lcRequest.addGetParam('olderPoll', olderPoll);
        lcRequest.addGetParam('currentPoll', currentPoll);
        lcRequest.addGetParam('newerPoll', newerPoll);
        lcRequest.addGetParam('isPopup', 'notPopup');
        lcRequest.addGetParam('navDirection', navDirection);
        lcRequest.headers = { 'Content-Type': 'application/json', 'X-WP-Nonce' : window.restNonce };
        lcRequest.execute(function (response) {
            $('#lc_polls_from').html(response.data.tpl);
            self.prop('disabled', false);
        });
    });
});