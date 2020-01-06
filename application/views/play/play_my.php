
<script src="/application/views/play/play_my.js?v=v<?= config_var(11060) ?>" type="text/javascript"></script>

<div class="container">

<?php

$en_all_11035 = $this->config->item('en_all_11035'); //MENCH PLAYER NAVIGATION

if($session_en) {

    //See how this user is connected to Mench
    $messenger_activated = in_array(6196, $this->session->userdata('session_parent_ids'));
    $web_email_activated = in_array(12103, $this->session->userdata('session_parent_ids'));

    echo '<h1 class="play pull-left inline-block"><span class="icon-block-xlg icon_photo">' . echo_en_icon($session_en['en_icon']) . '</span>' . one_two_explode('',' ',$session_en['en_name']) . '</h1>';

    echo '<div class="pull-right inline-block side-margin">';

        echo '<a href="/play/' . $session_en['en_id'] . '" class="btn btn-play btn-five icon-block-lg ' . superpower_active(10983) . '" style="padding-top:10px;" data-toggle="tooltip" data-placement="bottom" title="' . $en_all_11035[12205]['m_name'] . '">' . $en_all_11035[12205]['m_icon'] . '</a>';

        if (!intval($this->session->userdata('session_6196_signin'))) {
            //Only give signout option if NOT logged-in from Messenger
            echo '<a href="/play/signout" class="btn btn-play btn-five icon-block-lg" style="padding-top:10px;" data-toggle="tooltip" data-placement="bottom" title="' . $en_all_11035[7291]['m_name'] . '">' . $en_all_11035[7291]['m_icon'] . '</a>';
    }

    echo '</div>';

    echo '<div class="doclear">&nbsp;</div>';

    echo '<div class="accordion" id="MyPlayerAccordion" style="margin-bottom:34px;">';

    //Display account fields ordered with their player links:
    foreach ($this->config->item('en_all_6225') as $acc_en_id => $acc_detail) {

        if(in_array(6196, $acc_detail['m_parents']) && !$messenger_activated){
            //Messenger Setting but player is not connected via Messenger
            continue;
        }

        //Keep all closed for now:
        $expand_by_default = false;

        //Print header:
        echo '<div class="card">
    <div class="card-header" id="heading' . $acc_en_id . '">
    <button class="btn" type="button" data-toggle="collapse" data-target="#openEn' . $acc_en_id . '" aria-expanded="' . ($expand_by_default ? 'true' : 'false') . '" aria-controls="openEn' . $acc_en_id . '">
      <span class="icon-block-lg">' . $acc_detail['m_icon'] . '</span><b class="montserrat doupper ' . extract_icon_color($acc_detail['m_icon']) . '" style="padding-left:5px;">' . $acc_detail['m_name'] . '</b>
    </button>
    </div>
    
    <div id="openEn' . $acc_en_id . '" class="collapse ' . ($expand_by_default ? ' show ' : '') . '" aria-labelledby="heading' . $acc_en_id . '" data-parent="#MyPlayerAccordion">
    <div class="card-body">';


        //Show description if any:
        echo(strlen($acc_detail['m_desc']) > 0 ? '<p>' . $acc_detail['m_desc'] . '</p>' : '');


        //Print account fields that are either Single Selectable or Multi Selectable:
        $is_multi_selectable = in_array(6122, $acc_detail['m_parents']);
        $is_single_selectable = in_array(6204, $acc_detail['m_parents']);
        if ($acc_en_id == 10956 /* AVATARS */) {

            $player_icon_parts = explode(' ',one_two_explode('class="', '"', $session_en['en_icon']));

            echo '<div class="doclear">&nbsp;</div><div class="btn-group pull-right" role="group" style="margin:-48px 8px 0 0;">
                      <a href="javascript:void(0)" onclick="avatar_switch(\'far\')" class="btn"><i class="far fa-paw play"></i></a>
                      <a href="javascript:void(0)" onclick="avatar_switch(\'fad\')" class="btn active"><i class="fad fa-paw play"></i></a>
                      <a href="javascript:void(0)" onclick="avatar_switch(\'fas\')" class="btn"><i class="fas fa-paw play"></i></a>
                    </div><div class="doclear">&nbsp;</div>';


            foreach ($this->config->item('en_all_10956') as $en_id => $m) {

                $avatar_icon_parts = explode(' ',one_two_explode('class="', '"', $m['m_icon']));
                $avatar_type_match = ($player_icon_parts[0] == $avatar_icon_parts[0]);

                echo '<a href="javascript:void(0);" onclick="update_avatar(' . $en_id . ')" class="list-group-item itemplay avatar-item item-square avatar-type-'.$avatar_icon_parts[0].' avatar-name-'.$avatar_icon_parts[1].' ' .( $avatar_type_match ? '' : ' hidden ' ). ( $avatar_type_match && $player_icon_parts[1] == $avatar_icon_parts[1] ? ' active ' : '') . '"><div class="avatar-icon">' . $m['m_icon'] . '</div></a>';
            }
            echo '<div class="doclear">&nbsp;</div>';

        } elseif ($is_multi_selectable || $is_single_selectable) {

            echo echo_radio_players($acc_en_id, $session_en['en_id'], ($is_multi_selectable ? 1 : 0));

        } elseif ($acc_en_id == 6197 /* Name */) {

            echo '<span class="white-wrapper"><input type="text" id="en_name" class="form-control border play doupper montserrat" value="' . $session_en['en_name'] . '" /></span>
                    <a href="javascript:void(0)" onclick="account_update_name()" class="btn btn-play">Save</a>
                    <span class="saving-account save_full_name"></span>';

        } elseif ($acc_en_id == 3288 /* Email */) {

            $user_emails = $this->READ_model->ln_fetch(array(
                'ln_status_play_id IN (' . join(',', $this->config->item('en_ids_7359')) . ')' => null, //Link Statuses Public
                'ln_child_play_id' => $session_en['en_id'],
                'ln_type_play_id' => 4255, //Linked Players Text (Email is text)
                'ln_parent_play_id' => 3288, //Mench Email
            ));

            echo '<span class="white-wrapper"><input type="email" id="en_email" class="form-control border" value="' . (count($user_emails) > 0 ? $user_emails[0]['ln_content'] : '') . '" placeholder="you@gmail.com" /></span>
                    <a href="javascript:void(0)" onclick="account_update_email()" class="btn btn-play">Save</a>
                    <span class="saving-account save_email"></span>';

        } elseif ($acc_en_id == 3286 /* Password */) {

            echo '<span class="white-wrapper"><input type="password" id="input_password" class="form-control border" data-lpignore="true" autocomplete="new-password" placeholder="New Password..." /></span>
                    <a href="javascript:void(0)" onclick="account_update_password()" class="btn btn-play">Save</a>
                    <span class="saving-account save_password"></span>';

        }

        //Print footer:
        echo '</div></div></div>';

    }

    echo '</div>'; //End of accordion

} else {

    echo '<div style="padding:10px 0 20px;"><a href="/signin" class="btn btn-play montserrat">'.$en_all_11035[4269]['m_name'].'<span class="icon-block">'.$en_all_11035[4269]['m_icon'].'</span></a> to start playing.</div>';


}
?>

<!-- Top Players -->
<h1 class="montserrat play"><span class="icon-block-xlg icon_photo"><?= $en_all_11035[11087]['m_icon'] ?></span><?= $en_all_11035[11087]['m_name'] ?></h1>

    <div class="one-pix">
        <div id="load_top_players"></div>
    </div>

</div>