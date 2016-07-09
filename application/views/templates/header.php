
<?php
	/* define list of companies and saldos*/
	$this->load->library('funcs');
	$s=(json_decode($this->funcs->getsaldo(),TRUE));
	asort($s);
?>
<!doctype html>
<html>
<head>
	<title>Диалог.Кабинет</title>
	<meta charset="utf-8"/>
	<script type="text/javascript" src="<?php echo $base_url;?>static/js/jquery.js"></script>  
	<script type="text/javascript" src="<?php echo $base_url;?>static/js/main.js"></script>   
	<!--if full version-->
	<script type="text/javascript" src="<?php echo $base_url;?>static/js/ajaxupload.js"></script>  
	<!--<script type="text/javascript" src="<?php echo $base_url;?>static/js/ajaxupload.min.js"></script>   -->
	<script type="text/javascript" src="<?php echo $base_url;?>static/js/book.js"></script>   
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> 


</head>
<body>
<div id="pagewidth">
	<div id="header" >
	<div class="lefthead">
		<a href="<?php echo $base_url;?>" title="Переход к главной странице"><img src="static/img/dialog_logo2.png" class="logo"></a>
		<?php 
		# choose other company to view if suffix =di_
		$suff=substr($this->input->cookie('auth_username', TRUE),0,3);
		if($suff=='di_' or $suff=='DI_' or $suff=='Di_'){
			include 'choise.php';
		}
		?>
		</div>
		<div class="exithead" > 
			
			<?php 
			/* проверка если пользователь с админскими правами то показываем баланс*/
			if($isadmin): ?>
				
			<?php endif;?>
			    <span class="border-ccc">
					<!--i class="fa fa-phone green "></i-->
					
					<span class="extension "  title="Ваш номер (имя)">
						<!--span class="balans"><?php echo $this->input->cookie('auth_exten', TRUE); ?></span-->
						<i class="fa fa-user "></i>&nbsp;<?php echo $this->input->cookie('auth_username', TRUE); ?>
					</span>
				</span>
				&nbsp;&nbsp;
				<span  class="border-ccc"  title="Выход из кабинета">
				<a href="/auth/logout"><i class="fa fa-sign-out exitbutton" ></i> Выход</a>
				</span>
				<br>
				<span class="company_name hand" onclick="$('#rekvizit').toggle();">
					
						<?php
						//print_r($s);
						echo '<span title="Название компании">'.$s[0][0].'</span>&nbsp;<span class="balans" title="Остаток кредитного лимита">'.$s[0][1].'</span>&nbsp;<span class="rouble">
						<i class="fa fa-rub"></i>
					</span>';
						?>
					
				</span>
				<div class="hidden floatblock" id="rekvizit"  onclick="$('#rekvizit').hide();">
					<div><?php
						foreach($s as $one){
							echo $one[0].'&nbsp;'.$one[1].'&nbsp;р.<br>';
						}
						?>
					
					</div>
				</div>
				
				
			</span>
		</div>
		<!--
		<div class="righthead" title="Название Вашей организации">
			
			
		</div>
		-->
	</div>
	<div id="wrapper" class="clearfix">
	<div id="twocols">
	<!--<div id="maincol"> -->
	