<div>
	<span class="title">Текущее состояние</span>
	<!--div id="rightcol" >
		<div class="search"  title="Введите номер для звонка"> 
			<input type="text" class="nomer" id="nomer" placeholder="номер">
				
			<span  class="book_button"onclick="AddToBook();"><i class="fa fa-book hand"  title="Добавить номер в книгу"></i></span>
			
		</div>
		<div class="hidden" id="shortbookform" >
			<input type="text" class="nomer" id="sbf_name" placeholder="имя">
			<input type="text" class="nomer" id="sbf_ty" placeholder="комментарий">
			<input type="submit" class="nomer" id="sbf_submit" value="добавить" onclick="book_add('1');$('#shortbookform').toggle('300');">
		</div>
	</div-->
	
</div>

<hr class="hr_title">
	<div id="extlist" class="leftpart" >
	<div align="center"><span class="minititle">Состояние Номеров</span></div>	
	<br>
	
				<?php
				$text_size='twentypx';
				foreach($ext as $k=>$param){
					$style='back-green '.$text_size;
					$title="Зарегистрирован";
					
				?> 
				<span  class="extitem <?php echo $style ?>" title="<?php echo $title ?>">
						<span class="fpart"><?php echo $k.' '.$param['n'].' '.$param['t']. '</span><span class="tpart"> '.$param['ip'];?></span> 
				</span> 
				<?php }?>
				<?php
				$text_size='twentypx';
				foreach($ext_off as $k=>$param){
					$style='back-grey '.$text_size;
					$title=" Не зарегистрирован";
				
				?> 
				<span   class="extitem <?php echo $style ?>"  title="<?php echo $title ?>">
					<span class="fpart"><?php echo $k.' '.$param['n'].' '.$param['t']. '</span><span class="tpart"> '.$param['ip'];?></span> 
				</span> 
				<?php }?>
				
				
			
		
	</div>
	
	<div id="conversations" class="rightpart"  align="center">
		<div align="center"><span class="minititle">Разговоры</span></div>
		<br>
		<div id="conv_content">
		<?php 
		$this->load->model('book_model');
		foreach($conv as $one){
		 $class=($one['dir'] == 'out' ? "arrow_left" : "arrow_right");
		?>
		
			<span class="twentypx convitem back-green <?php echo $class ?>">
				<span class='fpart'>
					<?php echo $one['A']?> <span class="black"><?php echo $one['name1']['name']; ?></span>
				
				<i class='fa fa-long-arrow-right '></i>
				
					 <?php echo $one['B']?> <span class="black"><?php echo  $one['name2']['name'];?></span>
				</span>
				<span class='tpart'>
					 <?php echo $one['T']?>
				</span>
			</span>
		<?php } ?> 
		</div>
	</div>
	