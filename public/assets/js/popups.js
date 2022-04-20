(function( $ ) {
    let timer = 500;
    $.fn.showCareer = function() {
        $(this).callAction('career');
    };
    $.fn.showPresentChoice = function() {
        $(this).callAction('present_choice');
    };
    $.fn.showPastChoice = function() {
        $(this).callAction('past_choice');
    };
    $.fn.showInsurance = function() {
        $(this).callAction('insurance');
    };
    $.fn.showLottery = function() {
        $(this).callAction('lottery');
    };
    $.fn.showRealestate = function() {
        $(this).callAction('realestate');
    };
    $.fn.showLifeHappens = function() {
        $(this).callAction('lifehappens');
    };
    $.fn.divorced = function(id) {
        if(id) {
            $.ajax({
                url: APP_URL+"/lifeaction",
                type: 'POST',
                data: {'action':'divorced','activity_id':id},
                success:function(response){
                    if(response) {
                        $('.partner-balance').remove();
                        $('#common-modal').hide();
                        $(this).callback('lifehappens');
                    }
                }
            });
        }
    };
    $.fn.resetGame = function(id) {
        if(id) {
            $.ajax({
                url: APP_URL+"/lifeaction",
                type: 'POST',
                data: {'action':'died','activity_id':id},
                success:function(response){
                    if(response) {
                        $(this).setCookie('reset',1,1);
                        window.location.reload();
                    }
                }
            });
        }
    };
    $.fn.changeCareer = function(id,amount) {
        if(id && amount) {
            $.ajax({
                url: APP_URL+"/lifeaction",
                type: 'POST',
                data: {'action':'change-career','activity_id':id,'amount':amount},
                success:function(response){
                    if(response) {
                        $(this).setCookie('change-career',1,1);
                        window.location.reload();
                    }
                }
            });
        }
    };
    $.fn.checkNotification = function(notification) {
        if(notification.data('slug') == 'married') {
            var notification_action = 0;
            if(notification.data('action') == 'accept') {
                notification_action = 1;
            }
            var id = notification.closest('.btn-group').data('id');
            $.ajax({
                url: APP_URL+"/lifeaction",
                type: 'POST',
                data: {'id':id,'action':notification.data('slug')},
                success:function(response){
                    if(response) {
                        $(this).adjustCounter(notification);
                    }
                }
            });
        }
        else if(notification.data('slug') == 'bankruptcy') {
            var id = notification.closest('.btn-group').data('id');
            $.ajax({
                url: APP_URL+"/lifeaction",
                type: 'POST',
                data: {'id':id,'action':notification.data('slug')},
                success:function(response){
                    if(response) {
                        $(this).adjustCounter(notification);
                    }
                }
            });
        }
    };
    $.fn.adjustCounter = function(notification) {
        var notifications_count = $('#notification_count').html();
        notifications_count = notifications_count-1;
        if(notifications_count > 0) {
            $('#notification_count').html(notifications_count-1);
        }
        else {
            $('#notification_count').html('');
        }
        notification.closest('.notifications-item').remove();
    };
    $.fn.nextRound = function() {
        $.getJSON( APP_URL+"/nextround", function(data){

        });
    }
    $.fn.callAction = function(action) {
        setTimeout(function() {
            var url = APP_URL+"/"+action+"/"+ACTIVITY;
            if(action == 'lifehappens') {
                url = APP_URL+"/"+action+"/"+ACTIVITY+'/bankruptcy';
            }
            $.getJSON( url, function(data) {
                if(data.success) {
                    $('#common-modal').find('.modal-body').html(data.html);
                    $('#common-modal').modal('show');
                    $(this).popupValidation('common-modal','popupForm',action);
                    if(action == 'realestate') {
                        var realestate_id = $("#realestate_id").val();
                        $(this).startTimer(10*2,document.querySelector('#timer'),realestate_id,'common-modal');
                        Echo.channel('realestate.'+realestate_id)
                            .listen('RealEstateBid', function(e) {
                                $('.modal-body').find('#auctions').html(e.html);
                                $('.modal-body').find('#bid_amount').html('$'+e.next_amount);
                                $('.modal-body').find('#hid_amount').val(e.next_amount);
                            });
                    }
                }
                else {
                    next_round = 'lifehappens';
                }
            });
        },timer);
    };
    $.fn.callback = function(action) {
        var callbacks = $.Callbacks("once");
        var func;
        if(action == 'career') {
            if($(this).getCookie('change-career')) {
                $(this).eraseCookie('change-career');
                func = $(this).nextRound();
            }
            else {
                func = $(this).showPresentChoice;
            }
        }
        else if(action == 'present_choice') {
            func = $(this).showPastChoice;
        }
        else if(action == 'past_choice') {
            func = $(this).showInsurance;
        }
        else if(action == 'insurance') {
            func = $(this).showLottery;
        }
        else if(action == 'lottery') {
            if($(this).getCookie('reset')) {
                func = $(this).showLifeHappens();
                $(this).eraseCookie('reset');
            }
            else {
                func = '';
            }
        }
        else if(action == 'lifehappens') {
            func = $(this).nextRound;
        }
        if(typeof func == "function") {
            callbacks.add(func);
            callbacks.fire();
            callbacks.remove(func);
        }
        return false;
    };
    $.fn.popupValidation = function(modal,form,action) {
        $('#'+modal).on('shown.bs.modal', function(e) {
            $( "#"+form ).validate( {
                rules: {
                    amount: {
                        required: true,
                        equalTo: "#hid_amount"
                    }
                },
                messages: {
                    amount: {
                        required: "Please enter an amount",
                        equalTo: "Please enter valid amount"
                    }
                },
                errorElement: "em",
                errorPlacement: function ( error, element ) {
                    error.addClass( "invalid-feedback" );
                    if ( element.prop( "type" ) === "checkbox" ) {
                        error.insertAfter( element.next( "label" ) );
                    } else {
                        error.insertAfter( element );
                    }
                },
                highlight: function ( element, errorClass, validClass ) {
                    $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
                },
                unhighlight: function (element, errorClass, validClass) {
                    $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
                },
                submitHandler: function(form) {
                    var pathname = new URL(form.action).pathname;
                    var splitPath = pathname.split('/');
                    action = splitPath[1];
                    if ($(form).valid())
                    {
                        $.ajax({
                            url: form.action,
                            type: form.method,
                            data: $(form).serialize(),
                            success: function(response) {
                                if(response.success) {
                                    if(action !='realestate') {
                                        $('#'+modal).modal('hide');
                                        $('#'+modal).find('.modal-body').html('');
                                        //$(this).callback(action);
                                    }
                                    else {
                                        $('#amount').val('');
                                    }
                                }
                                else {
                                    $('.text-danger').html(response.html);
                                }
                            }
                        });
                    }
                }
            });
        });
    };
    $.fn.startTimer = function(duration, display,id,modal) {
        var timer = duration, minutes, seconds;
        var time_interval = setInterval(function () {
            if(timer == 0) {
                clearInterval(time_interval);
                $.getJSON( APP_URL+"/realestate/"+id+"/"+ACTIVITY, function(data){
                    $('#'+modal).modal('hide');
                    $('#'+modal).find('.modal-body').html('');

                    //$(this).showLifeHappens();
                });
            }
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            display.textContent = minutes + ":" + seconds;
            if (--timer < 0) {
                timer = duration;
            }
        }, 1000);
    };
    $.fn.setCookie = function(name,value,days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    };
    $.fn.getCookie = function(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for(let i=0;i < ca.length;i++) {
            let c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    };
    $.fn.eraseCookie = function(name) {
        document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    };
    $.fn.optout = function(area,id) {
        $.ajax({
            url: APP_URL+'/'+area+'-opt/',
            type: 'POST',
            data: {'activity_id':id},
            success: function(response) {
                if(response.success) {
                    $('#common-modal').modal('hide');
                    $('#common-modal').find('.modal-body').html('');
                    //$(this).callback(area);
                }
            }
        });
    };
    $.fn.showBiddingResult = function(activity_id,game_id,player_id) {
        $.ajax({
            url: APP_URL+'/bidwinner/',
            type: 'POST',
            data: {'activity_id':activity_id,'game_id':game_id,'player_id':player_id},
            dataType:'json',
            success: function(response) {
                if(response.success) {
                    setTimeout(function(){
                        $('#common-modal').find('.modal-body').html(response.html);
                        $('#common-modal').modal('show');
                    },1000);
                }
            }
        });
    };
    $.fn.prepareDice = function(activity_id,area) {
        $(this).setCookie('rollfor',area,0.5);
        $(this).setCookie('rollactivity',activity_id,0.5);
        $('#common-modal').modal('hide');
        $('#common-modal').find('.modal-body').html('');
    };
    $.fn.rollDice = function() {
        if($(this).getCookie('rollactivity') && $(this).getCookie('rollfor')) {
            $.ajax({
                url: APP_URL+'/roll',
                type: 'POST',
                data: {'activity_id':$(this).getCookie('rollactivity'),'area':$(this).getCookie('rollfor')},
                dataType:'json',
                success: function(response) {
                    if(response.success) {
                        $('#common-modal').find('.modal-body').html(response.html);
                        $('#common-modal').modal('show');
                    }
                }
            });
        }
    };
    $.fn.rollIt = function() {
        const dice = [...document.querySelectorAll(".die-list")];
        dice.forEach(die => {
            $(this).toggleClasses(die);
            die.dataset.roll = $(this).getRandomNumber(1, 6);
            setTimeout( function(){
                $.ajax({
                    url: APP_URL+'/roll_decision',
                    type: 'POST',
                    data: {'activity_id':$(this).getCookie('rollactivity'),'area':$(this).getCookie('rollfor'),'num':die.dataset.roll},
                    dataType:'json',
                    success: function(response) {
                        if(response.success) {
                            $('#roll_msg').html(response.html);
                            $('#roll_now').remove();
                            $('#close_popup').show();
                            $(this).eraseCookie('rollactivity');
                            $(this).eraseCookie('rollfor');
                        }
                    }
                });
            },2000);
        });
    };
    $.fn.toggleClasses = function(die) {
        die.classList.toggle("odd-roll");
        die.classList.toggle("even-roll");
    };
    $.fn.getRandomNumber = function(min,max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1)) + min;
    };
    $.fn.sellProperty = function(activity_id,realestate_id) {
        if(activity_id && realestate_id) {
            $.ajax({
                url: APP_URL+'/sell_property',
                type: 'POST',
                data: {'activity_id':activity_id,'realestate_id':realestate_id},
                dataType:'json',
                success: function(response) {
                    if(response.success) {

                    }
                }
            });
        }
    };
    $.fn.bankruptcy = function(id) {
        if(id) {
            $.ajax({
                url: APP_URL+"/lifeaction",
                type: 'POST',
                data: {'action':'bankruptcy','activity_id':id},
                success:function(response){
                    if(response) {
                        $('#common-modal').find('.modal-body').html(response.html);
                        $('#common-modal').modal('show');
                    }
                }
            });
        }
    };

}( jQuery ));
$(document).ready(function(){
    var down = false;
    var notifications_count = $('#box').find('.notifications-item').length;
    if(trigger_career) {
        next_round = 'career';
    }
    else if(trigger_present_choice) {
        next_round = 'present_choice';
    }
    else if(trigger_past_choice) {
        next_round = 'past_choice';
    }
    else if(trigger_insurance) {
        next_round = 'insurance';
    }
    else if(trigger_lottery) {
        next_round = 'lottery';
    }
    else if(trigger_lifehappens) {
        next_round = 'lifehappens';
    }
    if(notifications_count > 0) {
        $('#notification_count').html(notifications_count);
    }
    $('#bell').on('click',function(){
        if(down){
            $('#box').css('height','0px');
            $('#box').css('opacity','0');
            down = false;
        }else{
            $('#box').css('height','auto');
            $('#box').css('opacity','1');
            down = true;
        }
    });
    $(document).on('click','.notification-btn',function() {
        $(this).checkNotification($(this));
    });
    $(document).on('click','#divorced',function() {
        $(this).divorced($(this).data('id'));
    });
    $(document).on('click','#died',function() {
        $(this).resetGame($(this).data('id'));
    });
    $(document).on('click','#change-career',function() {
        $(this).changeCareer($(this).data('id'),$(this).data('amount'));
    });
    $(document).on('click','#bankruptcy',function() {
        $(this).bankruptcy($(this).data('id'));
    });
    $(document).on('click','.optout',function(){
        if($(this).data('action')) {
            $(this).optout($(this).data('action'),$('#id').val());
        }
        else {
            $('#common-modal').modal('hide');
            $('#common-modal').find('.modal-body').html('');
        }
    });
    $(document).on('click','#prepare_dice',function () {
        $(this).prepareDice($(this).data('activity'),$(this).data('rollfor'));
    });
    $(document).on('click','#close_popup',function () {
        $('#common-modal').modal('hide');
        $('#common-modal').find('.modal-body').html('');
    });
    $(document).on('click','#roll_dice',function(){
       $(this).rollDice();
    });
    $(document).on('click','#roll_now',function(){
        $(this).rollIt();
    });
    $(document).on('click','.realestate-btn',function(){
        $(this).sellProperty($(this).data('activity'),$(this).data('id'));
        $(this).closest('li').remove();
        if($(this).data('area') == 'from-lifehappens' && $('#lifehappens-sell').find('li').length == 0) {
            $('#common-modal').modal('hide');
            $('#common-modal').find('.modal-body').html('');
        }
    });
    $(document).on('click','#draw_card',function() {
        switch (next_round) {
            case 'career':
                $(this).showCareer();
                next_round = 'present_choice';
                break;
            case 'present_choice':
                $(this).showPresentChoice();
                next_round = 'past_choice';
                break;
            case 'past_choice':
                $(this).showPastChoice();
                next_round = 'insurance';
                break;
            case 'insurance':
                $(this).showInsurance();
                next_round = 'lottery';
                break;
            case 'lottery':
                $(this).showLottery();
                next_round = '';
                break;
            case 'lifehappens':
                $(this).showLifeHappens();
                next_round = '';
                break;
        }
    });
});