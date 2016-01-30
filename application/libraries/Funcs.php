<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// мой класс с фунциями необходимыми для работы
class Funcs {

    public function __construct() {
        //parent::__construct();
    }
// формирует timestamp с нашим часовым поясом
    	function getQS(){
		//берем строку параметров и убираем пустые
		 	$qs=$_SERVER['QUERY_STRING'];
			$qsnew='';
			$qsel=explode('&',$qs);
			foreach($qsel as $par){
				$parkv=explode('=',$par);
				if($parkv[1]){
					if($parkv[0])continue;
					$qsnew.=$parkv[0].'='.$parkv[1].'&';
					
				}
				
			}
			return $qsnew; 
		}
		
		function QS($p=False,$page){
		//$this->load->library('funcs'); 
		
		$qsnew= $this->getQS();
		//формируем ссылку на указанную страницу
		
			if(!strstr($qsnew,'page')){
				$qsnew.='page='.$p.'&';
				
			}else{
				$o='page='.$page;
				$n='page='.$p;
				$qsnew=	str_replace($o, $n, $qsnew);
				
			}
			unset($o); 
			unset($n);
			//return '<a href="'.$_SERVER['SCRIPT_NAME'].'?'.$qsnew.'">'.$p.'</a>'; 
			return '<a href="'.$_SERVER['SCRIPT_NAME'].'?'.$qsnew.'">'.$p.'</a>'; 
		}
		
		function checkbad($word){
			
			$badchars = array("`",";","'","*","/"," \ ","DROP", "SELECT", "UPDATE", "DELETE", "WHERE", "drop", "select", "update", "delete", "where",  "distinct", "having", "truncate", "replace", "handler", "like", "procedure", "limit", "order by", "group by");
			$word=str_replace( $badchars,'',$word);
			if (in_array($word, $badchars)) { 
				return ""; 
			} else { 
				return  $word; 
			}
		}
					  
		function get_fltr_name($param){
		
			switch($param){
				case 'startdate':
					return "Начальная дата";
				break;
				case 'enddate':
					return "Конечная дата";
				break;
				case 'exten':
					return "Вн. номер";
				break;
				case 'incom':
					return "Входящие вызовы";
				break;
				case 'outcom':
					return "Исходящие вызовы";
				break;
				case 'durtype':
					return "Условие длительности";
				break;
				case 'durtime':
					return "Длительность";
				break;
				case 'anstype':
					return "Статус звонка";
				break;
				case 'recyes':
					return "Наличие записи";
				break;
				case 'recno':
					return "Отсутствие записи";
				break;
			}
		}
		function sec2hms($sec){
			 $h=floor($sec/3600);
			 $m=floor(($sec-3600*$h)/60);
			 $s=floor($sec-(3600*$h+$m*60));
			 if($h<10) $h='0'.$h;
			 if($m<10) $m='0'.$m;
			 if($s<10) $s='0'.$s;
			 return $h.':'.$m.':'.$s;
		}
		/*
		получаем остаток кредитного лимита
		*/
	function getsaldo(){
			$CI =& get_instance();
			$CI->load->model('calls_model');
			$accid =str_replace(",","_",$CI->calls_model->getaccid($CI->input->cookie('auth_id')));;
			$id=$CI->input->cookie('auth_id');
			$m = new Memcached(); 
			$m->addServer('localhost', 11211);
			/*
			Смотрим в кэше сначала, если нет обращаемся к базе (через деплой)
			*/
						
			if(!$m->get($id.'saldo')){
				$res=file_get_contents('http://deploy.sip64.ru/un/getsaldo/'.$accid);
				//$res=json_encode($res);
				// раз уж получили сохраняем в кэщ на 5 минут
				$m->set($id.'saldo', $res, time() + 60);
			}
			else{
				//берем знаяение из кэша
				$res=$m->get($id.'saldo');
			}
			//print_r($res);
			return $res;
			//return round($res,0);
			
		}
		/*
		 получаем список абонентов их типов и состояния регистрации
		*/
		function GetNums(){
			$CI =& get_instance();
			$CI->load->model('calls_model');
			$accid =str_replace(",","_",$CI->calls_model->getaccid($CI->input->cookie('auth_id')));;
			$id=$CI->input->cookie('auth_id');
			//инициализируем мэмкэшд
			$m = new Memcached(); 
			$m->addServer('localhost', 11211);
			
			/*
			проверяем, если значение сохранено, то срузу отдае его, иначе получаем от ватс
			*/
			
			if(!$m->get($id.'ext')){
				//echo 'http://deploy.sip64.ru/un/getnums/'.$accid.'/'.$id;
				
				 $f=file_get_contents('http://deploy.sip64.ru/un/getnums/'.$accid);
				 $n=json_decode($f,TRUE);
				 if($n[12]){
					// echo $n[1];
					$call=$CI->calls_model->getnums($n[1],$id);
				 }else{
					$call=$n[1];
					
				 }
				$m->set($id.'ext', $call, time() + 10);			 
			}else{
				$call=$m->get($id.'ext');
			}
			
			return $call;

		}
		
		
		
		/*
		 приводим время разговорова к поминутному значению, в большую сторону
		 получаем количество секунд округляем до целых минут
		*/	
	function	 dur2min($dur){
		if($dur==0) return '';
		return ceil($dur/60);
	}
	
	function e_mail($log,$sub=false,$subj='Письмо с ЛК',$mess='',$att='',$from='no_reply@sip64.ru',$to='mironov@dialog64.ru'){
		/*
		отправка эл писем
		$log параметры письма для логов,
		$sub=false, поддомен
		$subj='Письмо с ЛК', тема сообщения
		$mess='', содержимое письма 
		$att='',путь к вложению
		$from='no_reply@dialog64.ru'  откакого имени шлем
		$to='mironov@dialog64.ru' на какой адрес шлем
		*/
		$CI =& get_instance();
		$CI->load->library('email');
		// формат письма
		$config['mailtype']='html';
		$CI->email->initialize($config);
		$CI->email->from($from, 'Диалог.Кабинет '.$sub);
		$CI->email->to($to); 
		//$this->email->cc('vovax3m@ya.ru'); 
		$CI->email->subject($subj); 
		$CI->email->message($mess);
		// если есть файл, криерепляем
		if($att){
			$CI->email->attach($att);
		};
		// если отправка не удалась
		if(!$CI->email->send()){
			$res= false;
			
			
		}else{
			$res= true;
		};
		/*
		логирование
		*/
		$ch = curl_init();
		if(!$log){
			$error='loggin failed';
		}else{
			if($res) $log.='&status=1';
			$url='http://deploy.sip64.ru/service/maillog';
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $log);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_exec($ch);	
		}
		return $res; // возвращаем результат отправки
			
	}
	function getcalls($limit,$filter){
			$CI =&get_instance();
			$CI->load->model('calls_model');
			$accid =str_replace(",","_",$CI->calls_model->getaccid($CI->input->cookie('auth_id')));
			$id=$CI->input->cookie('auth_id');
			$m = new Memcached(); 
			$m->addServer('localhost', 11211);
			//разбираем фильтр и ищем даты
			foreach(explode('&',$filter) as $par){
				$param=explode('=',$par);
				if($param[1])  $ff[$param[0]] = $param[1];
			}
			$dates=$ff['startdate'].'--'.$ff['enddate'];
			//print_r($ff['enddate']);
			/*
			проверяем, если значение сохранено, то срузу отдае его, иначе получаем от биллинга
			*/
			//сохраняем даты в кэш
			if(!$m->get('dates'.$id)){
				$m->set('dates'.$id, $dates, time() + 60);
			}
				//если уже нет в кэше
				if(!$m->get($id.'CE')){
					 // получаем и сохраняем
					 $f=file_get_contents('http://deploy.sip64.ru/un/callevent/'.$accid.'/'.$id.'/?'.$filter);
					 $m->set($id.'CE', $f, time() + 60);
				}else{
					//если есть звонки проверям что в фильтре дата не изменилась
					if($m->get('dates'.$id)==$dates){
						//берем из кэша
						$f=$m->get($id.'CE');
						//если даты не совпали, получаем звонки заново
					}else{
						$m->set('dates'.$id, $dates, time() + 60);
						$f=file_get_contents('http://deploy.sip64.ru/un/callevent/'.$accid.'/'.$id.'/?'.$filter);
						$m->set($id.'CE', $f, time() + 60);
					}
				}
			$call='';
			if(is_numeric($f)){
			//	echo $f;	
				$call=$CI->calls_model->getcalls($limit,$filter,$id);
				
				//$call['total']=$f;
			};
			//print_r($call);
			return $call;
		}
		
		function Getstate($list){
			$CI =& get_instance();
			$l = $CI->config->item('mera_login');
			$p = $CI->config->item('mera_pass');
			$h = $CI->config->item('mera_addr');
			echo $list;
			if(!($con = ssh2_connect($h, '22')))exit("не могу подключиться к мере по порту 22"); 
			if(!ssh2_auth_password($con, $l, $p)) exit("логин/пароль некоректен"); 
			$command="/mera/bin/mp_shell.x show ep | egrep '{$list}' ";
			$line=ssh2_exec($con, $command);
			//var_dump($line);
			stream_set_blocking($line, true); 
			$peers= nl2br(stream_get_contents($line));
			$peers_str=explode('<br />', $peers);
			foreach($peers_str as $string){
				$word=explode(":",$string);
				$value = is_array($word[1]) ? '' : trim($word[1]);
				//$idd= is_array($word[0]) ? '' : trim($word[0]);
				if ($value != '') {
					$reg[trim($word[2])]=$word[5];
				}
			}
			return $reg;									
		}
		function Getconv($list){
			$CI =& get_instance();
			$l = $CI->config->item('mera_login');
			$p = $CI->config->item('mera_pass');
			$h = $CI->config->item('mera_addr');
			
			if(!($con = ssh2_connect($h, '22')))exit("не могу подключиться к мере по порту 22"); 
			if(!ssh2_auth_password($con, $l, $p)) exit("логин/пароль некоректен"); 
			$command="/mera/bin/mp_shell.x show ca ta  | egrep '{$list}' ";
			$line=ssh2_exec($con, $command);
			//var_dump($line);
			stream_set_blocking($line, true); 
			$peers= nl2br(stream_get_contents($line));
			$peers_str=explode('<br />', $peers);
			//print_r($peers_str);
			
			foreach($peers_str as $string){
				$word=explode("|", trim($string));
				if($word[0]){
					$reg[]=array('A'=>$word[1],'B'=>$word[2],'T'=>$word[5]);
				}
			}
			return $reg;									
			
		}
		
//end of class		
}