<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitor extends CI_Controller { 
		public function __construct(){
			parent::__construct();
			$this->load->library('cab_auth');  // подключаем библиотеку авторизации
			$this->load->library('funcs');        // подключаем библиотеку доп функций
			$this->load->model('book_model');        // подключаем библиотеку доп функций
			$this->load->model('auth_model');        // подключаем библиотеку доступности функций
			//проверка авторизации
			if(!$this->cab_auth->check()){
				$bu= base_url();  // определяем базовый путь
				setcookie('STAT', 'Пожалуйста, войдите', time()+86400); // записываем в куку сообщение
				header('Location: '.$bu.'auth/login') ; // переадресовываем на страницу с формой входа
			}
		}
		public function index(){
			
			//возвращает базовый путь сайта  используется для переадресации и подключения скриптов и стилей
			$bu=$h['base_url']=base_url();
			
			//получаем список абонентов
			$ext= $this->funcs->GetNums();
			//print_r($ext);
			
			$list='';
			foreach ($ext as  $v){
				$name=$this->book_model->get($v,1);
				$names[$v]['n']=$name['name'];
				$names[$v]['t']=$name['type'];
				$names[$v]['ip']=false;
				$list.=$v.'|';
				//$ext[$k]['n']=$name;
			}
			$list=substr($list,0,-1);

			
			//print_r($names);
			 $data['kolvo']=count($names);
			 $state=$this->funcs->Getstate($list);
			 foreach ($state as $n => $ip){
				$sorted[$n]['ip']=$ip;
				$sorted[$n]['n']=$names[$n]['n'];
				$sorted[$n]['t']=$names[$n]['t'];
				unset($names[$n]);
				
			 };
			// print_r($sorted);
			 
			//echo '<hr>';
			// print_r($names);
			 $data['ext']=$sorted;
			 $data['ext_off']=$names;
			 //print_r($data['ext']);
			 //print_r($names);
			//отображаем html
			$conv=$this->funcs->Getconv($list);
			foreach($conv as $k=>$one){
				if( in_array(trim($one['A']),$ext)) $conv[$k]['dir']='out';
				
				$conv[$k]['name1']=$this->book_model->get($one['A'],1);
				$conv[$k]['name2']=$this->book_model->get($one['B'],1);
				$conv[$k]['T']=$this->funcs->sec2hms($conv[$k]['T']);
			}
			$data['conv']=$conv;
			//print_r($conv);
			$this->load->view('/templates/header',$h);
			$this->load->view('/monitor/monitor',$data);
			$this->load->view('/templates/footer',$h);
			
		}
		
		
		
		
//class end			
}