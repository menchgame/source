<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->output->enable_profiler(FALSE);
    }


    //Cache of cron jobs as of now [keep in sync when updating cron file]
    //* * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron fn___facebook_attachment_sync
    //*/5 * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron message_drip
    //*/6 * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron fn___save_media_to_cdn
    //31 * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron intent_sync
    //30 2 * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron fn___algolia_sync b 0
    //30 4 * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron fn___algolia_sync u 0
    //30 3 * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron e_score_recursive


    function matrix_cache(){

        /*
         * This function prepares a PHP-friendly text to be copies to matrix_cache.php
         * (which is auto loaded) to provide a cache image of some entities in
         * the tree for faster application processing.
         *
         * */

        //First first all entities that have Cache in PHP Config @4527 as their parent:
        $config_ens = $this->Database_model->tr_fetch(array(
            'tr_status >=' => 0,
            'tr_en_child_id >' => 0,
            'tr_en_parent_id' => 4527,
        ), array('en_child'), 0);

        foreach($config_ens as $en){

            //Now fetch all its children:
            $children = $this->Database_model->tr_fetch(array(
                'tr_status >=' => 2,
                'en_status >=' => 2,
                'tr_en_parent_id' => $en['tr_en_child_id'],
            ), array('en_child'), 0, 0, array('en_id' => 'ASC'));


            $child_ids = array();
            foreach($children as $child){
                array_push($child_ids , $child['en_id']);
            }

            echo '<br />//'.$en['en_name'].':<br />';
            echo '$config[\'en_ids_'.$en['tr_en_child_id'].'\'] = array('.join(', ',$child_ids).');<br />';
            echo '$config[\'en_all_'.$en['tr_en_child_id'].'\'] = array(<br />';
            foreach($children as $child){

                echo '&nbsp;&nbsp;&nbsp;&nbsp; '.$child['en_id'].' => array(<br />';

                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\'en_icon\' => \''.htmlentities($child['en_icon']).'\',<br />';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\'en_name\' => \''.$child['en_name'].'\',<br />';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\'tr_content\' => \''.str_replace('\'','\\\'',$child['tr_content']).'\',<br />';

                echo '&nbsp;&nbsp;&nbsp;&nbsp; ),<br />';

            }
            echo ');<br />';
        }
    }

    function intent_sync($in_id = 7240, $update_c_table = 1)
    {
        //Cron Settings: 31 * * * *
        //Syncs intents with latest caching data:
        $sync = $this->Database_model->in_recursive_fetch($in_id, true, $update_c_table);
        if (isset($_GET['redirect']) && strlen($_GET['redirect']) > 0) {
            //Now redirect;
            header('Location: ' . $_GET['redirect']);
        } else {
            //Show json:
            fn___echo_json($sync);
        }
    }


    //I cannot update algolia from my local server so if fn___is_dev() is true I will call mench.com/cron/fn___algolia_sync to sync my local change using a live end-point:
    function fn___algolia_sync($obj, $obj_id = 0)
    {
        fn___echo_json($this->Database_model->fn___algolia_sync($obj, $obj_id));
    }


    function fn___list_duplicate_ins()
    {

        //Do a query to detect intents with the exact same title:
        $q = $this->db->query('select in1.* from table_intents in1 where (select count(*) from table_intents in2 where in2.in_outcome = in1.in_outcome) > 1 ORDER BY in1.in_outcome ASC');
        $duplicates = $q->result_array();

        $prev_title = null;
        foreach ($duplicates as $in) {
            if ($prev_title != $in['in_outcome']) {
                echo '<hr />';
                $prev_title = $in['in_outcome'];
            }

            echo '<a href="/intents/' . $in['in_id'] . '">#' . $in['in_id'] . '</a> ' . $in['in_outcome'] . '<br />';
        }
    }

    function fn___list_duplicate_ens()
    {

        $q = $this->db->query('select en1.* from table_entities en1 where (select count(*) from table_entities en2 where en2.en_name = en1.en_name) > 1 ORDER BY en1.en_name ASC');
        $duplicates = $q->result_array();

        $prev_title = null;
        foreach ($duplicates as $u) {
            if ($prev_title != $u['en_name']) {
                echo '<hr />';
                $prev_title = $u['en_name'];
            }

            echo '<a href="/entities/' . $u['en_id'] . '">#' . $u['en_id'] . '</a> ' . $u['en_name'] . '<br />';
        }
    }


    function e_score_recursive($u = array())
    {

        //Updates en_trust_score based on number/value of connections to other intents/entities
        //Cron Settings: 2 * * * 30

        //Define weights:
        $score_weights = array(
            'u__childrens' => 0, //Child entities are just containers, no score on the link

            'tr_en_child_id' => 1, //Engagement initiator
            'tr_en_credit_id' => 1, //Engagement recipient

            'x_parent_en_id' => 5, //URL Creator
            'x_en_id' => 8, //URL Referenced to them

            'tr_en_parent_id' => 13, //Action Plan Items
        );

        //Fetch child entities:
        $entities = $this->Old_model->ur_children_fetch(array(
            'tr_en_parent_id' => (count($u) > 0 ? $u['en_id'] : $this->config->item('en_primary_id')),
            'tr_status >=' => 0, //Pending or Active
            'en_status >=' => 0, //Pending or Active
        ));

        //Recursively loops through child entities:
        $score = 0;
        foreach ($entities as $$en) {
            //Addup all child sores:
            $score += $this->e_score_recursive($$en);
        }

        //Anything to update?
        if (count($u) > 0) {

            //Update this row:
            $score += count($entities) * $score_weights['u__childrens'];

            $score += count($this->Database_model->tr_fetch(array(
                    'tr_en_child_id' => $u['en_id'],
                ), array(), 5000)) * $score_weights['tr_en_child_id'];
            $score += count($this->Database_model->tr_fetch(array(
                    'tr_en_credit_id' => $u['en_id'],
                ), array(), 5000)) * $score_weights['tr_en_credit_id'];

            $score += count($this->Old_model->x_fetch(array(
                    'x_status >' => -2,
                    'x_en_id' => $u['en_id'],
                ))) * $score_weights['x_en_id'];
            $score += count($this->Old_model->x_fetch(array(
                    'x_status >' => -2,
                    'x_parent_en_id' => $u['en_id'],
                ))) * $score_weights['x_parent_en_id'];

            $score += count($this->Database_model->w_fetch(array(
                    'tr_en_parent_id' => $u['en_id'],
                ))) * $score_weights['tr_en_parent_id'];

            //Update the score:
            $this->Database_model->en_update($u['en_id'], array(
                'en_trust_score' => $score,
            ));

            //return the score:
            return $score;

        }
    }


    function fn___save_media_to_cdn()
    {

        /*
         *
         * Every time we receive a media file from Facebook
         * we need to upload it to our own CDNs using the
         * short-lived URL provided by Facebook so we can
         * access it indefinitely without restriction.
         * This process is managed by creating a @4299
         * Transaction Type which this cron job grabs and
         * uploads to Mench CDN
         *
         * */

        $max_per_batch = 20; //Max number of scans per run

        $e_pending = $this->Database_model->tr_fetch(array(
            'tr_status' => 0, //Pending
            'tr_en_type_id' => 4299, //Save media file to Mench cloud
        ), array(), $max_per_batch);


        //Lock item so other Cron jobs don't pick this up:
        $this->Database_model->tr_status_processing($e_pending);

        //Go through and upload to CDN:
        foreach ($e_pending as $u) {

            //Update engagement data:
            $this->Database_model->tr_update($ep['tr_id'], array(
                'tr_content' => $new_file_url,
                'tr_en_type_id' => fn___detect_tr_en_type_id($new_file_url),
                'tr_status' => 2, //Publish
            ));


            //Save the file to S3
            $new_file_url = fn___upload_to_cdn($u['tr_content'], $u);

            if ($new_file_url) {

                //Success! Is this an image to be added as the entity icon?
                if (strlen($u['en_icon'])<1) {
                    //Update Cover ID:
                    $this->Database_model->en_update($u['en_id'], array(
                        'en_icon' => '<img class="profile-icon" src="' . $new_file_url . '" />',
                    ), true);
                }

                //Update engagement:
                $this->Database_model->tr_update($u['tr_id'], array(
                    'tr_status' => 2, //Publish
                ));

            } else {

                //Error has already been logged in the CDN function, so just update engagement:
                $this->Database_model->tr_update($u['tr_id'], array(
                    'tr_status' => -1, //Removed
                ));

            }
        }

        fn___echo_json($e_pending);
    }

    function fn___facebook_attachment_sync()
    {

        /*
         * This cron job looks for all requests to sync
         * Media files with Facebook so we can instantly
         * deliver them over Messenger.
         *
         * Cron Settings: * * * * *
         *
         */

        $max_per_batch = 20; //Max number of syncs per cron run
        $success_count = 0; //Track success
        $en_convert_4537 = $this->config->item('en_convert_4537'); //Supported Media Types
        $tr_metadata = array();


        //Let's fetch all Media files without a Facebook attachment ID:
        $pending_urls = $this->Database_model->tr_fetch(array(
            'tr_en_type_id IN (' . join(',',array_keys($en_convert_4537)) . ')' => null,
            'tr_external_id' => 0, //Missing Facebook Attachment ID
        ), array(), $max_per_batch, 0 , array('tr_id' => 'ASC')); //Sort by oldest added first

        foreach ($pending_urls as $tr) {

            $payload = array(
                'message' => array(
                    'attachment' => array(
                        'type' => $en_convert_4537[$tr['tr_en_type_id']],
                        'payload' => array(
                            'is_reusable' => true,
                            'url' => $tr['tr_content'], //The URL to the media file
                        ),
                    ),
                )
            );

            //Attempt to sync Media to Facebook:
            $result = $this->Chat_model->fn___facebook_graph('POST', '/me/message_attachments', $payload);
            $db_result = false;

            if ($result['status'] && isset($result['tr_metadata']['result']['attachment_id'])) {
                //Save attachment to DB:
                $db_result = $this->Database_model->tr_update($tr['tr_id'], array(
                    'tr_external_id' => intval($result['tr_metadata']['result']['attachment_id']),
                ));
            }

            //Did it go well?
            if ($db_result) {

                $success_count++;

            } else {

                //Log error:
                $this->Database_model->tr_create(array(
                    'tr_en_type_id' => 4246, //Platform Error
                    'tr_content' => 'fn___facebook_attachment_sync() Failed to sync attachment using Facebook API',
                    'tr_metadata' => array(
                        'payload' => $payload,
                        'result' => $result,
                    ),
                ));

                //Also disable future attempts for this transaction:
                $this->Database_model->tr_update($tr['tr_id'], array(
                    'tr_external_id' => -1,
                ));

            }

            //Save stats:
            array_push($tr_metadata, array(
                'payload' => $payload,
                'fb_result' => $result,
            ));

        }

        //Echo message:
        fn___echo_json(array(
            'status' => ($success_count == count($pending_urls) && $success_count > 0 ? 1 : 0),
            'message' => $success_count . '/' . count($pending_urls) . ' synced using Facebook Attachment API',
            'tr_metadata' => $tr_metadata,
        ));

    }


}