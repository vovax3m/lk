
<span title="Выбрать клиента" class="title hand" onclick="$('#choose').toggle()" >Клиент > </span>
<section id="choose"   class=" hidden chooseblock">
<?php
//get company list
$list=file_get_contents('http://deploy.sip64.ru/un/getlist/1');
$opt='';
foreach(json_decode($list) as $k=> $v){
	$opt.="<option value='{$k}' class='choose' >{$v}</option>";
};
 ?>
 
 <select class="form_option"  id="list "title="клиенты УН" size="5"  class="choose" onchange="$('#selected').append('<option  class=\'choose\'  value='+this.value+'>'+this.options[selectedIndex].text+'</option>');$('#selected').show();$('#submit').show();"> 
				<?php echo $opt?>
				</select>
<select class="form_option" name="selected"  id="selected" title="клиенты УН" size="5"   class="choose" onchange='$("#selected :selected").remove();hide()' >
	<?php
		foreach($s as $one){
			echo"<option value='{$one[2]}' class='choose' >{$one[0]}</option>";
		}
	?>
</select>
<textarea  style="display:none"  id="sel"></textarea>
<textarea  style="display:none"  id="uid"><?php echo $this->input->cookie('auth_id', TRUE); ?></textarea>
<input type="submit"   name="submit" id="submit" class="choose" value="Сохранить" onclick="$('#sel').empty();$('#selected option').each(function(){ $('#sel').append(this.value+',');});apply();">
</select>
</section>