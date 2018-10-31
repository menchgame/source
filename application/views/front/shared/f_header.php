<?php 
//Attempt to fetch session variables:
$udata = $this->session->userdata('user');
$fb_settings = $this->config->item('fb_settings');
$url_part_1 = $this->uri->segment(1);
?><!doctype html>
<html lang="en">
<head>
    <!--

    WELCOME TO MENCH SOURCE CODE 😻​

    INTERESTED IN HELPING US BUILD THE FUTURE OF EDUCATION?

    YOU CAN WORK WITH US FROM ANYWHERE IN THE WORLD

    EMAIL YOUR RESUME TO SUPPORT@MENCH.COM

    -->
	<meta charset="utf-8" />
	<link rel="icon" type="image/png" href="/img/bp_16.png">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title><?= ( isset($title) ? $title.' | ' : '' ).'Mench' ?></title>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	<?php $this->load->view('front/shared/header_resources' ); ?>
	<script src="/js/front/global.js?v=v<?= $this->config->item('app_version') ?>" type="text/javascript"></script>
	<?php /* if(isset($udata['u_email'])){ ?>
	    <script> zE( function () { zE.identify({name: '<?= $udata['u_full_name'] ?>', email: '<?= $udata['u_email'] ?>'}); }); </script>
	<?php } */ ?>
</head>

<body class="landing-page">

    <div class="fb-customerchat" minimized="true" ref="SUBSCRIBE10_6623" greeting_dialog_display="hide" theme_color="#2f2739" page_id="<?= $fb_settings['page_id'] ?>"></div>

    <nav class="navbar navbar-warning navbar-fixed-top navbar-color-on-scroll <?= ( isset($landing_page) ? 'navbar-transparent': 'no-adj') ?>">
    	<div class="container">
        	<!-- Brand and toggle get grouped for better mobile display -->
        	<div class="navbar-header">
        		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navigation-example">
            		<span class="sr-only">Toggle navigation</span>
		            <span class="icon-bar"></span>
		            <span class="icon-bar"></span>
		            <span class="icon-bar"></span>
        		</button>
        		<a class="navbar-brand" href="/"><img src="/img/bp_128.png" /><span style="text-transform: lowercase; color: #2f2739;">Mench</span></a>
        	</div>

        	<div class="collapse navbar-collapse">
        		<ul class="nav navbar-nav navbar-right">
    				<?php
                    if(isset($udata['u_id'])){
                        echo '<li id="isloggedin"><a href="/intents/'.( isset($c['c_id']) ? $c['c_id'] : 6623 ).'">Console <i class="fas fa-chevron-circle-right"></i></a></li>';
                    } elseif(isset($udata['u__ws']) && count($udata['u__ws'])>0){
                        echo '<li id="isloggedin"><a href="/my/actionplan">Student Hub <i class="fas fa-chevron-circle-right"></i></a></li>';
                    } else {
                        if(!($url_part_1=='login')) {
                            //This is the login page, show the Launch Button:
                            echo '<li><a href="/login"><i class="fas fa-sign-in"></i> Login</a></li>';
                        }
                    }
    				?>
        		</ul>
        	</div>
    	</div>
    </nav>
    
<?php
//Any landing pages?
if(isset($landing_page)){
    
	//Yes, load the page:
    $this->load->view($landing_page , ( isset($lp_variables) ? $lp_variables : null ) );
    
} else {
	//Regular content page:
	echo '<div class="main main-raised main-plain">';
	echo '<div class="container body-container">';
	
	$hm = $this->session->flashdata('hm');
	if($hm){
	    echo $hm;
	}
}

if(isset($message)){
    echo $message;
}
?>