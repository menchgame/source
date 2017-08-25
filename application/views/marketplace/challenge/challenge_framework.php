<?php if($pid!=$challenge['c_id']){ ?>
<div class="mini-label">
	<div><a href="/marketplace/<?= $challenge['c_id'] ?>"><span class="label label-default"><?= $challenge['c_objective'] ?></span></a></div>
</div>
<?php } ?>
<h1 class="c_objective"><?= $cr['c']['c_objective'] ?></h1>
<p class="showdown"><?= $cr['c']['c_description'] ?></p>



<ul class="nav nav-pills nav-pills-primary" style="margin-top:30px; padding-top:20px; border-top:5px solid #000;">
  <li class="active"><a href="#pill1" data-toggle="tab"><?= $this->lang->line('cr_icon') ?> <?= $this->lang->line('cr_pname') ?></a></li>
  <li><a href="#pill2" data-toggle="tab"><?= $this->lang->line('i_icon') ?> <?= $this->lang->line('i_pname') ?></a></li>
  <?php if($cr['c']['c_is_grandpa']=='t' && 0){ ?>
  <li><a href="#pill3" data-toggle="tab"><?= $this->lang->line('r_icon') ?> <?= $this->lang->line('r_pname') ?></a></li>
  <?php } ?>
  <li><a href="#pill4" onlick="alert('edit')" data-toggle="tab"><i class="fa fa-pencil-square" aria-hidden="true"></i> <?= $this->lang->line('edit') ?></a></li>
</ul>
<div class="tab-content tab-space">
    <div class="tab-pane active" id="pill1">
<?php

echo '<div id="list-inbound" class="list-group">';
	if(isset($cr['inbound']) && count($cr['inbound'])>0){
		foreach($cr['inbound'] as $relation){
			echo echo_cr($challenge,$relation,'inbound');
		}
	}
echo '</div>';

//OUTTTTTTTTTTTTTTTTTTTTTTTTTTTT
echo '<div id="list-outbound" class="list-group">';
	if(isset($cr['outbound']) && count($cr['outbound'])>0){
		foreach($cr['outbound'] as $relation){
			echo echo_cr($challenge,$relation,'outbound');
		}
	}	
echo '</div>';


//Can they modify?
if(can_modify('c',$challenge['c_id'])){
	echo '<div class="list-group">';
		echo '<div class="list-group-item list_input">';
		?>
		<div class="input-group">
			<div class="form-group is-empty"><input type="text" class="form-control autosearch" id="addnode" placeholder="+ Add Objective"></div>
			<span class="input-group-addon">
				<span id="dir_handle" class="label label-primary pull-right">
					<div><span id="dir_name">OUTBOUND</span> <i class="fa fa-chevron-right"></i></div>											
					<div class="togglebutton" style="display:none; margin-top:5px;">
		            	<label>
		                	<input type="checkbox" onclick="change_direction()" />
		            	</label>
            		</div>
				</span>
			</span>
		</div>
		<?php 
		//echo '<input type="text" class="form-control autosearch" id="addnode" placeholder="+ Add Objective">';
		echo '</div>';
	echo '</div>';
}

?>	      
    </div>
    <div class="tab-pane" id="pill2">
    
    </div>
	<div class="tab-pane" id="pill3">
		<?php
		if(count($challenge['runs'])>0){
			echo '<div class="list-group">';
				foreach($challenge['runs'] as $run){
					echo '<a href="/marketplace/'.$challenge['c_id'].'/run/'.$run['r_version'].'" class="list-group-item"><span class="label label-primary pull-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></span>'.run_icon($run['r_version']).' '.( time_ispast($run['r_kickoff_time']) ? $this->lang->line('started') : $this->lang->line('starts')).' '.time_format($run['r_kickoff_time']).' '.status_bible('r',$run['r_status']).'</a>';
				}
			echo '</div>';
			
		} else {
			//Notify that there are no runs!
			echo '<div class="alert alert-warning" role="alert">'.$this->lang->line('r_none_message').'</div>';
		}
		?>
		<?= ( can_modify('c',$challenge['c_id']) ? '<a href="/marketplace/'.$challenge['c_id'].'/run/new" type="submit" class="btn btn-primary btn-raised btn-round"><i class="fa fa-plus"></i> '.$this->lang->line('new').'</a>' : '' ) ?>
    </div>
    <div class="tab-pane" id="pill4">
    
    		<input type="hidden" id="save_c_id" value="<?= $cr['c']['c_id'] ?>" />
    		<div class="title"><h4>Objective <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Make it measurable, attainable, relevant, rewarding & trackable."></i></h4></div>
			<div class="form-group label-floating is-empty">
			    <input type="text" id="save_c_objective" value="<?= $cr['c']['c_objective'] ?>" class="form-control">
			    <span class="material-input"></span>
			</div>
			
    		
			<div class="title"><h4>Overview <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Give more context on the challenge and the oppurtunity."></i> <span style="font-size:0.6em; color:#AAA;">(Supports <a href="/guides/showdown_markup">Markup Syntax</a>)</span></h4></div>
			<div class="form-group label-floating is-empty">
			    <textarea class="form-control" rows="5" id="save_c_description" style="height:200px;"><?= $cr['c']['c_description'] ?></textarea>
			    <span class="material-input"></span>
			</div>
		
		  
		    <div class="row">
			  <div class="col-xs-6"><a href="javascript:save_c();" class="btn btn-primary">Save</a> <span id="save_c_results"></span></div>
			  <div class="col-xs-6 action-right">
			  <?php 
			  if($cr['c']['c_is_grandpa']=='f'){
			  	//echo '<a href="javascript:delete_c('.$challenge['c_id'].','.$cr['c']['c_id'].',\''.$cr['c']['c_objective'].'\')"><i class="fa fa-times-circle" aria-hidden="true"></i> Delete</a>';
			  } else {
			    //echo '<span data-toggle="tooltip" title="Delete protected."><i class="fa fa-lock" aria-hidden="true"></i> Protected</span>';
			  }
			  ?>
			  </div>
			</div>


		  
    </div>
</div>
