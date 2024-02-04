<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class I extends CI_Controller {

    function __construct()
    {
        parent::__construct();

        $this->output->enable_profiler(FALSE);

        auto_login();

    }




    function i_copy(){

        //Auth member and check required variables:
        $member_e = superpower_unlocked(10939);

        if (!$member_e) {
            return view_json(array(
                'status' => 0,
                'messagCloe' => view_unauthorized_message(10939),
            ));
        } elseif (!isset($_POST['i__id']) || intval($_POST['i__id']) < 1) {
            return view_json(array(
                'status' => 0,
                'message' => 'Invalid Following Source',
            ));
        } elseif (!isset($_POST['do_recursive'])) {
            return view_json(array(
                'status' => 0,
                'message' => 'Missing template parameter',
            ));
        }

        return view_json($this->I_model->recursive_clone(intval($_POST['i__id']), intval($_POST['do_recursive']), $member_e['e__id']));

    }


    function i_layout($i__hashtag){

        //Validate/fetch Idea:
        $is = $this->I_model->fetch(array(
            'LOWER(i__hashtag)' => strtolower($i__hashtag),
        ));
        if ( count($is) < 1) {

            //See if we can find via ID?
            if(0 && is_numeric($i__hashtag)){
                foreach($this->I_model->fetch(array(
                    'i__id' => $i__hashtag,
                )) as $go){
                    return redirect_message('/'.$go['i__hashtag']);
                }
            }

            return redirect_message(home_url(), '<div class="alert alert-danger" role="alert"><span class="icon-block"><i class="fas fa-exclamation-circle zq6255"></i></span>IDEA #' . $i__hashtag . ' Not Found</div>');

        }

        $member_e = superpower_unlocked(10939); //Idea Pen?
        if(!$member_e){
            if(in_array($is[0]['i__privacy'], $this->config->item('n___31871'))){
                return redirect_message('/'.$i__hashtag);
            } else {
                return redirect_message(home_url(), '<div class="alert alert-danger" role="alert"><span class="icon-block"><i class="fas fa-exclamation-circle"></i></span>IDEA #' . $i__hashtag . ' is not published yet.</div>');
            }
        }

        //Import Discoveries?
        $flash_message = '';
        if(isset($_GET['e__handle'])){
            foreach($this->E_model->fetch(array(
                'LOWER(e__handle)' => strtolower($_GET['e__handle']),
            )) as $e_append){
                $completed = 0;
                foreach($this->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                    'x__left' => $is[0]['i__id'],
                ), array(), 0) as $x){
                    if(!count($this->X_model->fetch(array(
                        'x__up' => $e_append['e__id'],
                        'x__down' => $x['x__creator'],
                        'x__message' => $x['x__message'],
                        'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                    )))){
                        //Add source link:
                        $completed++;
                        $this->X_model->create(array(
                            'x__creator' => ($member_e ? $member_e['e__id'] : $x['x__creator']),
                            'x__up' => $e_append['e__id'],
                            'x__down' => $x['x__creator'],
                            'x__message' => $x['x__message'],
                            'x__type' => 4230,
                        ));
                    }
                }

                $flash_message = '<div class="alert alert-warning" role="alert"><span class="icon-block"><i class="fas fa-exclamation-circle"></i></span> '.$completed.' sources who played this idea added to @'.$e_append['e__handle'].'</div>';
            }
        }

        $e___14874 = $this->config->item('e___14874'); //Mench Cards

        //Load views:
        $this->load->view('header', array(
            'title' => view_i_title($is[0], true).' | '.$e___14874[12273]['m__title'],
            'flash_message' => $flash_message,
        ));
        $this->load->view('i_layout', array(
            'focus_i' => $is[0],
            'member_e' => $member_e,
        ));
        $this->load->view('footer');

    }




    function i__add()
    {

        /*
         *
         * Either creates a IDEA transaction between focus_id & link_i__id
         * OR will create a new idea with outcome i__message and then transaction it
         * to focus_id (In this case link_i__id=0)
         *
         * */

        //Authenticate Member:
        $member_e = superpower_unlocked(10939);
        if (!$member_e) {
            return view_json(array(
                'status' => 0,
                'message' => view_unauthorized_message(10939),
            ));
        } elseif (!isset($_POST['x__type']) || !isset($_POST['focus_id']) || !isset($_POST['focus_card'])) {
            return view_json(array(
                'status' => 0,
                'message' => 'Missing Core Variables',
            ));
        } elseif (!isset($_POST['new_i__message']) || !isset($_POST['link_i__id'])) {
            return view_json(array(
                'status' => 0,
                'message' => 'Missing either Idea Outcome OR Follower Idea ID',
            ));
        }

        $validate_i__message = validate_i__message($_POST['new_i__message']);
        if(!$validate_i__message['status']){
            //We had an error, return it:
            return view_json($validate_i__message);
        }


        if(!$_POST['link_i__id'] && view_valid_handle_i($_POST['new_i__message'])){
            foreach($this->I_model->fetch(array(
                'LOWER(i__hashtag)' => strtolower(view_valid_handle_i($_POST['new_i__message'])),
            )) as $i){
                $_POST['link_i__id'] = $i['i__id'];
            }
        }

        if($_POST['link_i__id'] > 0){
            //Fetch transaction idea to determine idea type:
            $x_i = $this->I_model->fetch(array(
                'i__id' => intval($_POST['link_i__id']),
                'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
            ));
            if(!count($x_i)){
                //validate Idea:
                return view_json(array(
                    'status' => 0,
                    'message' => 'Idea #'.$_POST['link_i__id'].' is not active.',
                ));
            }
        }

        //All seems good, go ahead and try to create/link the Idea:
        return view_json($this->I_model->create_or_link($_POST['focus_card'], $_POST['x__type'], trim($_POST['new_i__message']), $member_e['e__id'], $_POST['focus_id'], $_POST['link_i__id']));

    }

    function view_body_i(){
        //Authenticate Member:
        if (!isset($_POST['i__id']) || intval($_POST['i__id']) < 1 || !isset($_POST['counter']) || !isset($_POST['x__type']) || intval($_POST['x__type']) < 1) {
            echo '<div class="alert alert-danger" role="alert"><span class="icon-block"><i class="fas fa-exclamation-circle zq6255"></i></span>Missing core variables</div>';
        } else {
            echo view_body_i($_POST['x__type'], $_POST['counter'], $_POST['i__id']);
        }
    }

    function i_load_cover(){

        if (!isset($_POST['i__id']) || !isset($_POST['x__type']) || !isset($_POST['first_segment']) || !isset($_POST['counter'])) {
            echo '<div class="alert alert-danger" role="alert"><span class="icon-block"><i class="fas fa-exclamation-circle zq6255"></i></span>Missing core variables</div>';
        } else {

            if(in_array($_POST['x__type'], $this->config->item('n___42376')) && !write_privacy_i(null, $_POST['i__id'])){

                echo '<div class="alert alert-danger" role="alert"><span class="icon-block"><i class="fas fa-lock"></i></span>Private</div>';

            } else {

                $ui = '';
                $listed_items = 0;
                if(in_array($_POST['x__type'], $this->config->item('n___42261')) || in_array($_POST['x__type'], $this->config->item('n___42284'))){

                    //SOURCES
                    $e___6177 = $this->config->item('e___6177'); //Source Types
                    $e___4593 = $this->config->item('e___4593'); //Transaction Types
                    $current_e__handle = view_valid_handle_e($_POST['first_segment']);
                    foreach(view_i_covers($_POST['x__type'], $_POST['i__id'], 1, false) as $e_e) {
                        if(isset($e_e['e__id'])){
                            $ui .= view_card('/@'.$e_e['e__handle'], $current_e__handle && $e_e['e__handle']==$current_e__handle, $e_e['x__type'], $e_e['e__privacy'], view_cover($e_e['e__cover'], true), $e_e['e__title'], $e_e['x__message']);
                            $listed_items++;
                        }
                    }

                } elseif(in_array($_POST['x__type'], $this->config->item('n___11020'))){

                    //IDEAS
                    $e___4737 = $this->config->item('e___4737'); //Idea Types
                    $e___4593 = $this->config->item('e___4593'); //Transaction Types
                    $current_i__hashtag = ( substr($_POST['first_segment'], 0, 1)=='~' ? substr($_POST['first_segment'], 1) : false );

                    foreach(view_i_covers($_POST['x__type'], $_POST['i__id'], 1, false) as $next_i) {
                        if(isset($next_i['i__id'])){
                            $ui .= view_card('/~'.$next_i['i__hashtag'], $next_i['i__hashtag']==$current_i__hashtag, $next_i['x__type'], null, ( in_array($next_i['i__type'], $this->config->item('n___32172')) ? $e___4737[$next_i['i__type']]['m__cover'] : '' ), view_i_title($next_i), $next_i['x__message']);
                            $listed_items++;
                        }
                    }

                }

                if($listed_items < $_POST['counter']){
                    //We have more to show:
                    foreach($this->I_model->fetch(array(
                        'i__id' => $_POST['i__id'],
                    )) as $i){
                        $ui .= view_more('/~'.$i['i__hashtag'], false, '&nbsp;', '&nbsp;', '&nbsp;', 'View all '.number_format($_POST['counter'], 0));
                    }
                }

                echo $ui;

            }
        }
    }


    function editor_load_i()
    {

        $member_e = superpower_unlocked();
        if (!$member_e) {
            return view_json(array(
                'status' => 0,
                'message' => view_unauthorized_message(),
            ));
        } elseif (!isset($_POST['i__id']) || !isset($_POST['x__id']) || !isset($_POST['current_i__type'])) {
            return view_json(array(
                'status' => 0,
                'message' => 'Missing Core IDs',
            ));
        }


        $i__id = 0; //New idea
        $i__type = intval($_POST['current_i__type']);
        $created_i__id = 0;

        if($_POST['i__id'] > 0){

            $is = $this->I_model->fetch(array(
                'i__id' => $_POST['i__id'],
            ));
            if (!count($is)) {
                return view_json(array(
                    'status' => 0,
                    'message' => 'Idea is no longer active',
                ));
            } elseif (!write_privacy_i($is[0]['i__hashtag'])) {
                return view_json(array(
                    'status' => 0,
                    'message' => 'You are missing permission to edit this idea',
                ));
            }

            $i__id = intval($is[0]['i__id']);
            if(!$i__type){
                $i__type = intval($is[0]['i__type']);
            }

        } else {

            //Create a new idea:
            $i_new = $this->I_model->create(array(
                'i__message' => 'Placeholder Text',
                'i__type' => $_POST['current_i__type'],
                'i__privacy' => 42636, //Pre-drafting idea
            ), $member_e['e__id']);

            $i__id = $i_new['i__id'];
            $created_i__id = $i__id;

        }

        //Fetch dynamic data based on idea type:
        $return_inputs = array();
        $e___4737 = $this->config->item('e___4737'); // Idea Status
        $e___42179 = $this->config->item('e___42179'); //Dynamic Input Fields
        $e___11035 = $this->config->item('e___11035'); //Summary



        foreach(array_intersect($this->config->item('n___'.$i__type), $this->config->item('n___42179')) as $dynamic_e__id){

            //Let's first determine the data type:
            $data_types = array_intersect($e___42179[$dynamic_e__id]['m__following'], $this->config->item('n___4592'));

            if(count($data_types)!=1) {
                //This is strange, we are expecting 1 match only report this:
                $this->X_model->create(array(
                    'x__type' => 4246, //Platform Bug Reports
                    'x__creator' => $member_e['e__id'],
                    'x__up' => 42179, //Dynamic Input Fields
                    'x__down' => $dynamic_e__id,
                    'x__right' => $i__id,
                    'x__reference' => $_POST['x__id'],
                    'x__message' => 'Found ' . count($data_types) . ' Data Types (Expecting exactly 1) for @' . $dynamic_e__id . ': Check @4592 to see what is wrong',
                ));
                continue; //Go to the next dynamic data type
            }

            //We found 1 match as expected:
            foreach($data_types as $data_type_this){
                $data_type = $data_type_this;
                break;
            }
            $is_required = in_array($dynamic_e__id, $this->config->item('n___42174')); //Required Settings

            if(in_array($data_type, $this->config->item('n___42188'))){

                //Single or Multiple Choice:
                array_push($return_inputs, array(
                    'd__id' => $dynamic_e__id,
                    'd__is_radio' => 1,
                    'd_x__id' => 0,
                    'd__html' => view_instant_select($dynamic_e__id, 0, $i__id),
                    'd__value' => '',
                    'd__type_name' => '',
                    'd__placeholder' => '',
                    'd__profile_header' => '',
                ));

            } else {

                $this_data_type = $this->config->item('e___'.$data_type);
                $e___4592 = $this->config->item('e___4592'); //Data types
                $e___6177 = $this->config->item('e___6177'); //Source Privacy
                $e___42179 = $this->config->item('e___42179'); //Dynamic Input Field
                $e___11035 = $this->config->item('e___11035'); //Summary

                //Fetch the current value:
                $counted = 0;
                $unique_values = array();
                if($i__id > 0){ //Must have an original ID to possibly have a value...
                    foreach($this->X_model->fetch(array(
                        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                        'x__type IN (' . join(',', $this->config->item('n___42252')) . ')' => null, //Plain Link
                        'x__right' => $i__id,
                        'x__up' => $dynamic_e__id,
                    ), array('x__up')) as $curr_val){
                        if(strlen($curr_val['x__message']) && !in_array($curr_val['x__message'], $unique_values)){
                            $counted++;
                            array_push($unique_values, $curr_val['x__message']);
                            array_push($return_inputs, array(
                                'd__id' => $dynamic_e__id,
                                'd__is_radio' => 0,
                                'd_x__id' => $curr_val['x__id'],
                                'd__html' => '<span class="icon-block">'.$e___42179[$dynamic_e__id]['m__cover'].'</span>'.$e___42179[$dynamic_e__id]['m__title'].': '.( !in_array($curr_val['e__privacy'], $this->config->item('n___33240')) ? '<span title="'.$e___6177[$curr_val['e__privacy']]['m__title'].'" data-toggle="tooltip" class="grey" data-placement="top">'.$e___6177[$curr_val['e__privacy']]['m__cover'].'</span>' : '' ).( isset($e___11035[$dynamic_e__id]) && strlen($e___11035[$dynamic_e__id]['m__message']) ? '<span class="icon-block-xs" title="'.$e___11035[$dynamic_e__id]['m__message'].'" data-toggle="tooltip" data-placement="top">'.$e___11035[11035]['m__cover'].'</span>' : '' ).( $is_required ? ' <b title="Required Field" style="color:#FF0000;">*</b>' : '' ),
                                'd__value' => $curr_val['x__message'],
                                'd__type_name' => html_input_type($data_type),
                                'd__placeholder' => ( strlen($this_data_type[$dynamic_e__id]['m__message']) ? $this_data_type[$dynamic_e__id]['m__message'] : $e___4592[$data_type]['m__title'].'...' ),
                                'd__profile_header' => '',
                            ));
                        }
                    }
                }


                if(!$counted){
                    foreach($this->E_model->fetch(array(
                        'e__id' => $dynamic_e__id,
                    )) as $curr_val){
                        array_push($return_inputs, array(
                            'd__id' => $dynamic_e__id,
                            'd__is_radio' => 0,
                            'd_x__id' => 0,
                            'd__html' => '<span class="icon-block">'.$e___42179[$dynamic_e__id]['m__cover'].'</span>'.$e___42179[$dynamic_e__id]['m__title'].': '.( !in_array($curr_val['e__privacy'], $this->config->item('n___33240')) ? '<span title="'.$e___6177[$curr_val['e__privacy']]['m__title'].'" data-toggle="tooltip" class="grey" data-placement="top">'.$e___6177[$curr_val['e__privacy']]['m__cover'].'</span>' : '' ).( isset($e___11035[$dynamic_e__id]) && strlen($e___11035[$dynamic_e__id]['m__message']) ? '<span class="icon-block-xs" title="'.$e___11035[$dynamic_e__id]['m__message'].'" data-toggle="tooltip" data-placement="top">'.$e___11035[11035]['m__cover'].'</span>' : '' ).( $is_required ? ' <b title="Required Field" style="color:#FF0000;">*</b>' : '' ),
                            'd__value' => '',
                            'd__type_name' => html_input_type($data_type),
                            'd__placeholder' => ( strlen($this_data_type[$dynamic_e__id]['m__message']) ? $this_data_type[$dynamic_e__id]['m__message'] : $e___4592[$data_type]['m__title'].'...' ),
                            'd__profile_header' => '',
                        ));
                    }
                }
            }
        }

        $return_array = array(
            'status' => 1,
            'return_inputs' => $return_inputs,
            'created_i__id' => $created_i__id,
        );

        //Log Modal View:
        $this->X_model->create(array(
            'x__creator' => $member_e['e__id'],
            'x__type' => 14576, //MODAL VIEWED
            'x__up' => 31911, //Edit Idea
            'x__right' => $i__id,
            'x__reference' => $_POST['x__id'],
            'x__metadata' => $return_array,
        ));

        //Return everything we found:
        return view_json($return_array);

    }




    function editor_save_i(){


        $member_e = superpower_unlocked();
        if (!$member_e) {

            return view_json(array(
                'status' => 0,
                'message' => view_unauthorized_message(),
            ));

        } elseif(!isset($_POST['save_i__message']) || !strlen($_POST['save_i__message'])){

            return view_json(array(
                'status' => 0,
                'message' => 'Missing Idea',
            ));

        } elseif(!isset($_POST['save_i__hashtag'])){

            return view_json(array(
                'status' => 0,
                'message' => 'Missing hashtag',
            ));

        } elseif(!isset($_POST['save_i__id'])){

            return view_json(array(
                'status' => 0,
                'message' => 'Missing Idea ID',
            ));

        } elseif(!isset($_POST['link_i__id'])){

            return view_json(array(
                'status' => 0,
                'message' => 'Missing Link ID',
            ));

        } elseif(!isset($_POST['save_x__id']) || !isset($_POST['save_x__message'])){

            return view_json(array(
                'status' => 0,
                'message' => 'Missing Transaction Data',
            ));

        } elseif (!isset($_POST['save_i__type']) || !in_array($_POST['save_i__type'], $this->config->item('n___4737'))) {
            return view_json(array(
                'status' => 0,
                'message' => 'Invalid idea Type',
            ));
        } elseif (!isset($_POST['save_media']) || !isset($_POST['save_i__privacy']) || !in_array($_POST['save_i__privacy'], $this->config->item('n___31004'))) {
            return view_json(array(
                'status' => 0,
                'message' => 'Invalid idea Privacy',
            ));
        }



        if($_POST['save_i__id'] > 0){

            $is = $this->I_model->fetch(array(
                'i__id' => $_POST['save_i__id'],
            ));
            if(!count($is)){
                return view_json(array(
                    'status' => 0,
                    'message' => 'Idea Not Valid',
                ));
            }
            //Might be new if pre-drafting:
            $is_new_idea = ( $is[0]['i__privacy']==42636 );

        } else {
            //Must be new:
            $is_new_idea = true;
        }






        //Validate Idea Message:
        $validate_i__message = validate_i__message($_POST['save_i__message']);
        if(!$validate_i__message['status']){
            return view_json(array(
                'status' => 0,
                'message' => $validate_i__message['message'],
            ));
        }


        //Validate Dynamic Inputs:
        $e___42179 = $this->config->item('e___42179'); //Dynamic Input Fields

        //Process dynamic inputs if any:
        if($_POST['save_i__id'] > 0){
            for ($p = 1; $p <= view_memory(6404,42206); $p++) {

                if(!isset($_POST['save_dynamic_' . $p])){
                    break; //Nothing more to process
                }

                $input_parts = explode('____', $_POST['save_dynamic_' . $p], 3);
                $d_x__id = $input_parts[0];
                $dynamic_e__id = $input_parts[1];
                $dynamic_value = trim($input_parts[2]);

                //Required fields must have an input:
                if(in_array($dynamic_e__id, $this->config->item('n___42174')) && !strlen($dynamic_value) && !in_array($dynamic_e__id, $this->config->item('n___33331')) && !in_array($dynamic_e__id, $this->config->item('n___33332'))){
                    return view_json(array(
                        'status' => 0,
                        'message' => 'Missing Required Field: '.$e___42179[$dynamic_e__id]['m__title'],
                    ));
                }

                //Validate input based on its data type, if provided:
                if (strlen($dynamic_value)) {
                    foreach(array_intersect($e___42179[$dynamic_e__id]['m__following'], $this->config->item('n___4592')) as $data_type_this){
                        $data_type_validate = data_type_validate($data_type_this, $dynamic_value, $e___42179[$dynamic_e__id]['m__title']);
                        if (!$data_type_validate['status']) {
                            //We had an error:
                            return view_json($data_type_validate);
                        }
                    }
                }


                //Fetch the current value:
                if($d_x__id > 0){
                    $values = $this->X_model->fetch(array(
                        'x__id' => $d_x__id,
                    ));
                }

                if(!$d_x__id || !count($values)){
                    $values = $this->X_model->fetch(array(
                        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                        'x__type IN (' . join(',', $this->config->item('n___42252')) . ')' => null, //Plain Link
                        'x__right' => $is[0]['i__id'],
                        'x__up' => $dynamic_e__id,
                    ));
                }


                //Update if needed:
                if(!strlen($dynamic_value)){

                    //Remove Link if we have one:
                    if(count($values)){
                        $this->X_model->update($values[0]['x__id'], array(
                            'x__privacy' => 6173, //Transaction Removed
                        ), $member_e['e__id'], 42175 /* Dynamic Link Content Removed */);
                    }

                } elseif(!count($values)){

                    //Create New Link:
                    $this->X_model->create(array(
                        'x__creator' => $member_e['e__id'],
                        'x__type' => 4983, //Co-Author
                        'x__up' => $dynamic_e__id,
                        'x__right' => $is[0]['i__id'],
                        'x__message' => $dynamic_value,
                        'x__weight' => number_x__weight($dynamic_value),
                    ));

                } elseif($values[0]['x__message']!=$dynamic_value){

                    //Update Link:
                    $this->X_model->update($values[0]['x__id'], array(
                        'x__message' => $dynamic_value,
                        'x__weight' => number_x__weight($dynamic_value),
                    ), $member_e['e__id'], 42176 /* Dynamic Link Content Updated */);

                }
            }
        }



        //Validate Idea Hashtag & save if needed:
        $attemp_update = 0;
        if($is_new_idea && $_POST['save_i__id'] > 0){

            //Update new idea fields:
            $this->I_model->update($is[0]['i__id'], array(
                'i__type' => $_POST['save_i__type'],
                'i__privacy' => $_POST['save_i__privacy'],
            ), true, $member_e['e__id']);
            $is[0]['i__type'] = trim($_POST['save_i__type']);
            $is[0]['i__privacy'] = trim($_POST['save_i__privacy']);

        } elseif($is[0]['i__hashtag'] !== trim($_POST['save_i__hashtag'])){

            $validate_handle = validate_handle($_POST['save_i__hashtag'], $is[0]['i__id'], null);
            if(!$validate_handle['status']){
                return view_json(array(
                    'status' => 0,
                    'message' => $validate_handle['message'],
                ));
            }

            //Save hashtag since changed:
            $this->I_model->update($is[0]['i__id'], array(
                'i__hashtag' => trim($_POST['save_i__hashtag']),
            ), true, $member_e['e__id']);

            //Now Handles everywhere they are referenced:
            foreach ($this->X_model->fetch(array(
                'x__left' => $is[0]['i__id'],
                'x__type IN (' . join(',', $this->config->item('n___42341')) . ')' => null, //Idea References
                'x__privacy IN (' . join(',', $this->config->item('n___7360')) . ')' => null, //ACTIVE
            ), array('x__right')) as $ref) {
                $attemp_update++;
                view_sync_links(str_replace('#'.$is[0]['i__hashtag'], '#'.trim($_POST['save_i__hashtag']), $ref['i__message']), true, $ref['i__id']);
            }

            //Assign new value:
            $is[0]['i__hashtag'] = trim($_POST['save_i__hashtag']);

        }


        //Also have to add as a comment to another idea?
        if(intval($_POST['link_i__id'])>0){
            $this->X_model->create(array(
                'x__creator' => $member_e['e__id'],
                'x__left' => $_POST['link_i__id'],
                'x__right' => $is[0]['i__id'],
                'x__type' => 30901, //Reply
            ));
        }


        //Do we have a link reference message that need to be saved?
        if($_POST['save_x__id']>0 && $_POST['save_x__message']!='IGNORE_INPUT'){
            //Fetch transaction:
            foreach($this->X_model->fetch(array(
                'x__id' => $_POST['save_x__id'],
            )) as $this_x){

                $is[0] = array_merge($is[0], $this_x);

                if($this_x['x__message'] != trim($_POST['save_x__message'])){
                    $this->X_model->update($this_x['x__id'], array(
                        'x__message' => trim($_POST['save_x__message']),
                        'x__weight' => number_x__weight(trim($_POST['save_x__message'])),
                    ), $member_e['e__id'], 42171);
                }
            }
        }


        //Update Links based on save_i__message / Sync Idea Synonym & Source References links:
        $view_sync_links = view_sync_links($_POST['save_i__message'], true, $is[0]['i__id']);
        $is[0]['i__message'] = trim($_POST['save_i__message']);
        $is[0]['i__cache'] = $view_sync_links['i__cache'];


        //Update Search Index:
        flag_for_search_indexing(12273, $is[0]['i__id']);


        return view_json(array(
            'status' => 1,
            'return_i__cache' => $view_sync_links['i__cache'],
            'return_i__cache_links' => view_i_links($is[0]),
            'redirect_idea' => ( isset($is[0]['i__hashtag']) ? '/~'.$is[0]['i__hashtag'] : null ),
            'message' => $attemp_update.' Attempt Updated | '.$view_sync_links['sync_stats']['old_links_removed'].' old links removed, '.$view_sync_links['sync_stats']['old_links_kept'].' old links kept, '.$view_sync_links['sync_stats']['new_links_added'].' new links added.',
        ));

    }

}