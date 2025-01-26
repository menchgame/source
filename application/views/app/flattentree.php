<?php

if(!isset($_GET['i__hashtag'])){
    die('Missing Idea ID i__hashtag');
}


//Generate list & settings:
$list_settings = list_settings($_GET['i__hashtag']);
echo '<h1>' . view__i_title($list_settings['i']) . '</h1>';


//Display idea info:
$recursive_down_ids = $this->Idea_cache->recursive_down_ids($list_settings['i'], 'ALL');


//Main Idea:
echo '<h2><a href="'.view__memory(42903,33286).$list_settings['i']['i__hashtag'].'">'.view__i_title($list_settings['i'], true).'</a> '.count($recursive_down_ids['recursive_i_ids']).' IDEAS</h2>';

echo '<div class="row justify-content">';
foreach($recursive_down_ids['recursive_i_ids'] as $recursive_down_id){
    foreach($this->Idea_cache->fetch(array(
        'i__id' => $recursive_down_id,
    ), 0) as $this_i){
        echo view__card_i(12273, $this_i);
    }
}
echo '</div>';