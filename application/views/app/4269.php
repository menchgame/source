<?php

$sign_i__id = ( isset($_GET['i__id']) && $_GET['i__id'] > 0 ? $_GET['i__id'] : 0 );
$next_url = ( isset($_GET['url']) ? urldecode($_GET['url']) : ($sign_i__id > 0 ? '/' . $sign_i__id : home_url()) );

//Check to see if they are previously logged in?
if(superpower_unlocked()) {

    //Lead member and above, go to console:
    js_php_redirect($next_url, 13);

} elseif(isset($_COOKIE['auth_cookie'])){

    //Authenticate Cookie:
    $cookie_parts = explode('ABCEFG',$_COOKIE['auth_cookie']);

    $es = $this->E_model->fetch(array(
        'e__id' => $cookie_parts[0],
        'e__access IN (' . join(',', $this->config->item('n___7358')) . ')' => null, //ACTIVE
    ));

    if(count($es) && $cookie_parts[2]==md5($cookie_parts[0].$cookie_parts[1].view_memory(6404,30863))){

        //Assign session & log transaction:
        $this->E_model->activate_session($es[0], false, true);

    } else {

        //Cookie was invalid
        cookie_delete();

    }

    js_php_redirect($next_url, 13);

} else {


    if($sign_i__id || isset($_GET['url'])){
        //Assign Session variable so we can detect upon social login:
        $session_data = $this->session->all_userdata();
        if($sign_i__id){
            $session_data['login_i__id'] = $_GET['i__id'];
        }
        if(isset($_GET['url'])){
            $session_data['redirect_url'] = urldecode($_GET['url']);
        }
        $this->session->set_userdata($session_data);
    }


    if(0 && !isset($_GET['active'])){

        //Disable for now:
        js_php_redirect('/-14436', 13);

        echo '<div class="center-info">';
        echo '<div class="text-center platform-large">'.get_domain('m__cover').'</div>';
        echo '<p style="margin-top:13px; text-align: center;">'.view_shuffle_message(12694).'</p>';
        echo '</div>';

    } else {

        $e___4269 = $this->config->item('e___4269');
        $e___11035 = $this->config->item('e___11035'); //NAVIGATION

        $this_attempt = array(
            'x__type' => ( $sign_i__id > 0 ? 7560 : 7561 ),
            'x__left' => $sign_i__id,
        );

        $current_sign_i_attempt = array(); //Will try to find this...
        $current_sign_i_attempts = $this->session->userdata('sign_i_attempts');
        if(is_array($current_sign_i_attempts) && count($current_sign_i_attempts) > 0){
            //See if any of the current sign-in attempts match this:
            foreach($current_sign_i_attempts as $sign_i_attempt){
                $all_match = true;
                foreach(array('x__left') as $sign_i_attempt_field){
                    if(intval($this_attempt[$sign_i_attempt_field]) != intval($sign_i_attempt[$sign_i_attempt_field])){
                        $all_match = false;
                        break;
                    }
                }
                if($all_match){
                    //We found a match!
                    $current_sign_i_attempt = $sign_i_attempt;
                    break;
                }
            }
        } else {
            $current_sign_i_attempts = array();
        }


        //See what to do based on current matches:
        if(count($current_sign_i_attempt) == 0){

            //Log transaction:
            $current_sign_i_attempt = $this->X_model->create($this_attempt);

            //Grow the array:
            array_push($current_sign_i_attempts, $current_sign_i_attempt);

            //Add this sign-in attempt to session:
            $this->session->set_userdata(array('sign_i_attempts' => $current_sign_i_attempts));

        }
        ?>

        <script type="text/javascript">

            function load_away(){
                $('.login-content').html('<div class="center"><span class="icon-block"><i class="far fa-yin-yang fa-spin"></i></span></div>');
            }

            //Disable social login for Instagram as it has a bug within auth0
            $(document).ready(function () {
                var ua = navigator.userAgent || navigator.vendor || window.opera;
                var isInstagram = (ua.indexOf('Instagram') > -1) ? true : false;
                if (document.documentElement.classList ){
                    if (isInstagram) {
                        $('.social-frame').addClass('hidden');
                    }
                }
            });

            //COde Input
            const ELS_pinEntry = document.querySelectorAll(".pinEntry");
            const selectAllIfFull = (evt) => {
                const EL_input = evt.currentTarget;
                if (EL_input.value.length >= 4) EL_input.select();
            };
            ELS_pinEntry.forEach(el => {
                el.addEventListener("focusin", selectAllIfFull);
            });


            var go_next_icon = '<?= $e___11035[26104]['m__cover'] ?>';
            var sign_i__id = <?= $sign_i__id ?>;
            var referrer_url = '<?= @$_GET['url'] ?>';
            var logged_messenger = false;
            var logged_website = false;
            var step_count = 0;

            $(document).ready(function () {


                const ELS_pinEntry = document.querySelectorAll(".pinEntry");
                const selectAllIfFull = (evt) => {
                    const EL_input = evt.currentTarget;
                    if (EL_input.value.length >= 4) EL_input.select();
                };
                ELS_pinEntry.forEach(el => {
                    el.addEventListener("focusin", selectAllIfFull);
                });


                //Watch for email address change:
                $('#account_email_phone').on('input',function(e){
                    if($(this).length){
                        $('#step2buttons').removeClass('hidden');
                    } else {
                        $('#step2buttons').addClass('hidden');
                    }
                });

                goto_step(2);

                $(document).keyup(function (e) {
                    //Watch for action keys:
                    if (e.keyCode == 13) {
                        if(step_count==2){
                            contact_search();
                        } else if(step_count==3){
                            contact_auth();
                        }
                    }
                });
            });

            function goto_step(this_count){

                //Update read count:
                step_count = this_count;

                $('.signup-steps').addClass('hidden');
                $('#step'+step_count).removeClass('hidden');

                setTimeout(function () {
                    $('#step'+step_count+' .white-border:first').focus();
                }, 144);

            }


            var email_searching = false;
            function contact_search(){

                if(email_searching){
                    return false;
                }

                //Lock fields:
                email_searching = true;
                $('#email_check_next').html('<span class="icon-block"><i class="far fa-yin-yang fa-spin"></i></span>');
                $('#account_email_phone').prop('disabled', true);
                $('#sign_code_errors').html('');
                $('#flash_message').html(''); //Delete previous errors, if any

                //Check email and validate:
                $.post("/e/contact_search", {

                    account_email_phone: $('#account_email_phone').val(),
                    sign_i__id: sign_i__id,

                }, function (data) {

                    //Release field lock:
                    email_searching = false;
                    $('#email_check_next').html(go_next_icon);
                    $('#account_email_phone').prop('disabled', false);

                    if (data.status) {

                        //Update email:
                        $('#account_email_phone_errors').html('');
                        $('#account_id').val(data.account_id);
                        $('#account_email_phone').val(data.clean_contact);
                        $('.code_sent_to').html(data.clean_contact);

                        if(!data.account_id){
                            //Allow to create new account with email/phone
                            $('.new_account').removeClass('hidden');
                        } else {
                            $('.new_account').addClass('hidden');
                        }

                        //Go to final step:
                        goto_step(3);

                    } else {

                        //Show errors:
                        $('#account_email_phone_errors').html('<b class="css__title zq6255"><span class="icon-block"><i class="fas fa-exclamation-circle"></i></span>' + data.message + '</b>').hide().fadeIn();
                        $('#account_email_phone').focus();

                    }

                });

            }


            var code_checking = false;
            function contact_auth(){

                if(code_checking){
                    return false;
                }

                //Lock fields:
                code_checking = true;
                $('#code_check_next').html('<span class="icon-block"><i class="far fa-yin-yang fa-spin"></i></span>');
                $('#input_code').prop('disabled', true);

                //Check email/phone and validate:
                $.post("/e/contact_auth", {
                    account_email_phone: $('#account_email_phone').val(),
                    account_id: $('#account_id').val(), //Might be zero if new account
                    new_account_title: $('#new_account_title').val(),
                    input_code: $('#input_code').val(),
                    referrer_url: referrer_url,
                    sign_i__id: sign_i__id,
                }, function (data) {

                    if (data.status) {

                        js_redirect(data.sign_url);

                    } else {

                        //Release field lock:
                        code_checking = false;
                        $('#code_check_next').html(go_next_icon);
                        $('#input_code').prop('disabled', false).focus();
                        $('#sign_code_errors').html('<b class="css__title zq6255"><span class="icon-block"><i class="fas fa-exclamation-circle"></i></span>' + data.message + '</b>').hide().fadeIn();

                    }

                });

            }

        </script>


        <div class="center-info">

            <div class="text-center platform-large"><?= get_domain('m__cover') ?></div>

            <div class="login-content" style="margin-top:40px;">


                <div>
                    <?php
                    //Back only if coming from an idea:
                    $intro_message = $e___4269[7561]['m__message']; //Assume No Idea
                    if ($sign_i__id > 0) {
                        $sign_i = $this->I_model->fetch(array(
                            'i__access IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
                            'i__id' => $sign_i__id,
                        ));
                        if (count($sign_i)) {
                            $intro_message = str_replace('%s','<br /><a href="/' . $sign_i__id . '"><u>'.$sign_i[0]['i__title'].'</u></a>', $e___4269[7560]['m__message']);
                        }
                    }
                    ?>
                </div>

                <!-- Step 1: Enter Email -->
                <div id="step2" class="signup-steps hidden">

                    <?= '<p style="margin-top:13px; text-align: center; padding-bottom: 34px;">'.$intro_message.'</p>'; ?>

                    <span class="css__title" style="padding-bottom: 3px; display:block;"><?= '<span class="icon-block">'.$e___4269[32079]['m__cover'].'</span>'.$e___4269[32079]['m__title'] ?></span>
                    <div class="form-group"><input type="text" placeholder="your@email.com or 7781234567" id="account_email_phone" <?= isset($_GET['account_email_phone']) ? ' value="'.$_GET['account_email_phone'].'" ' : '' ?> class="form-control border white-border white-border"></div>
                    <div id="account_email_phone_errors" class="zq6255 margin-top-down hideIfEmpty"></div>
                    <span id="step2buttons" class="<?= isset($_GET['account_email_phone']) ? '' : ' hidden ' ?>" >
                    <a href="javascript:void(0)" onclick="contact_search()" id="email_check_next" class="controller-nav round-btn pull-right" title="<?= $e___11035[26104]['m__title'] ?>"><?= $e___11035[26104]['m__cover'] ?></a>
                    <div class="doclear">&nbsp;</div>
                    </span>

                    <?php

                    //SOCIAL LOGIN:
                    if(strlen(website_setting(14881)) && strlen(website_setting(14882))){
                        echo '<div class="social-frame">';
                        echo '<div class="mid-text-line"><span>OR</span></div>';
                        echo '<div class="full-width-btn center top-margin"><a href="/-14436" onclick="load_away()" class="btn btn-large btn-default">';
                        echo $e___11035[14436]['m__title'].' '.$e___11035[14436]['m__cover'];
                        echo '</a></div>';
                        echo '</div>';
                    }



                    //GUEST LOGIN:
                    if(view_memory(6404,6197)){
                        echo '<div class="social-frame">';
                        echo '<div class="mid-text-line"><span>OR</span></div>';
                        echo '<div class="full-width-btn center top-margin"><a href="/-14938?i__id='.$sign_i__id.'" onclick="load_away()" class="btn btn-large btn-default">';
                        echo $e___11035[14938]['m__title'].' '.$e___11035[14938]['m__cover'];
                        echo ( strlen($e___11035[14938]['m__message']) ? ': '.$e___11035[14938]['m__message'] : '' );
                        echo '</a></div>';
                        echo '</div>';
                    }
                    ?>

                </div>


                <!-- Step 3: Enter Sign in Code (and Maybe signup if not found) -->
                <div id="step3" class="signup-steps hidden">

                    <!-- To be updated to >0 IF account was found -->
                    <input type="hidden" id="account_id" value="0" />


                    <!-- New Account ( If not found) -->
                    <div class="margin-top-down new_account hidden">

                        <div class="css__title"><span class="icon-block"><?= $e___4269[14026]['m__cover'] ?></span><?= $e___4269[14026]['m__title'] ?></div>

                        <!-- Enter Full Name -->
                        <span class="css__title" style="padding:34px 0 3px; display:block;"><?= '<span class="icon-block">'.$e___4269[13025]['m__cover'].'</span>'.$e___4269[13025]['m__title'] ?></span>
                        <div class="form-group"><input type="text" placeholder="<?= $e___4269[13025]['m__message'] ?>" id="new_account_title" maxlength="<?= view_memory(6404,6197) ?>" class="form-control border css__title white-border"></div>

                    </div>



                    <div style="padding:8px 0;">Enter the <?= $e___4269[32078]['m__title'] ?> sent to <span class="code_sent_to"></span>:</div>
                    <!-- Sign in Code -->
                    <div class="pinBox"><input maxlength="4" autocomplete="off" type="number"step="1" id="input_code" class="pinEntry"></div>
                    <div id="sign_code_errors" class="zq6255 margin-top-down hideIfEmpty"></div>
                    <div class="doclear">&nbsp;</div>



                    <div id="step3buttons">
                        <a href="javascript:void(0)" data-toggle="tooltip" data-placement="bottom" onclick="goto_step(2)" class="controller-nav round-btn pull-left" title="<?= $e___11035[12991]['m__title'] ?>"><?= $e___11035[12991]['m__cover'] ?></a>
                        <a href="javascript:void(0)" onclick="contact_auth()" id="code_check_next" class="controller-nav round-btn pull-right" title="<?= $e___11035[26104]['m__title'] ?>"><?= $e___11035[26104]['m__cover'] ?></a>
                    </div>

                    <div class="doclear">&nbsp;</div>

                </div>

            </div>
        </div>

        <?php

    }
}