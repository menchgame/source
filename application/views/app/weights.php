<?php

$obj = ( isset($_GET['obj']) ? $_GET['obj'] : false );

//Update object weight

$stats = array(
    'start_time' => time(),
    'i_scanned' => 0,
    'i_updated' => 0,
    'i_total_weights' => 0,
    'e_scanned' => 0,
    'e_changed' => 0,
);

if(!$obj || $obj==12273){

    //Update the weights for active ideas
    foreach($this->Idea_cache->fetch(array(
        'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
    )) as $in) {
        $stats['i_scanned']++;
        $stats['i_updated'] += i__weight_calculator($in);
    }

}


if(!$obj || $obj==12274){
    //Update the weights for active sources:
    foreach($this->Source_cache->fetch(array(
        'e__privacy IN (' . join(',', $this->config->item('n___7358')) . ')' => null, //ACTIVE
    )) as $en) {
        $stats['e_scanned']++;
        $stats['e_changed'] += e__weight_calculator($en);
    }
}

$stats['end_time'] = time();
$stats['total_seconds'] = $stats['end_time'] - $stats['start_time'];
$stats['total_items'] = $stats['e_scanned'] + $stats['i_scanned'];
if($stats['total_seconds'] > 0){
    $stats['millisecond_speed'] = round(($stats['total_seconds'] / $stats['total_items'] * 1000), 3);
}

//Return results:
view__json($stats);