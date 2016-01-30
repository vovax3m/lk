<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Book extends CI_Controller { 
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
			$isadmin=$h['isadmin']=$data['isadmin']=$this->cab_auth->is_admin();
			$h['is_full']=$data['is_full']=$this->auth_model->get_setting('type');
			//возвращает базовый путь сайта  используется для переадресации и подключения скриптов и стилей
			$bu=$h['base_url']=base_url();
			$search=$this->input->cookie('book_search', TRUE);
			if($search){
				$data['book']=$this->book_model->search($search);
				$data['search']=$search;
				setcookie('book_search', '', -3600); 
			}else{
				$data['book']=$this->book_model->get();
			}
			
			
			$this->load->view('/templates/header',$h);
			$this->load->view('/book/view',$data);
			$this->load->view('/templates/footer',$h);
		}
		function search(){
			$w=$_POST['what'];
			
			if($w){
				//setcookie('book_search', $w, time()+86400); // записываем в куку сообщение
				$this->input->set_cookie('book_search', $w,time()+86400);
				echo 'write to cook = ' .$w;
			}
			//header('Location: '.$bu.'/book') ; // переадресовываем на страницу с книгой
		}
		function set(){
			$this->book_model->set($_POST['id'],$_POST['no'],$_POST['na'],$_POST['ty']);
		}
		function add(){
			if (!is_numeric($_POST['no'])){
				echo 'wrong';
				exit;
			}
			$check=$this->book_model->get($_POST['no'],true);
		
			if($check['nomer']==$_POST['no']){
				echo 'exist';
			}else{
				$this->book_model->add($_POST['no'],$_POST['na'],$_POST['ty']);
				$check=$this->book_model->get($_POST['no'],true);
				// во звращаем ид записи
				echo $check['id'];
			}
		}
		function del(){
			$this->book_model->del($_POST['id']);
		}
		function pass(){
			$p=$_GET['pass'];
			echo sha1($p);
		}
		function export(){
			$id=$this->input->cookie('auth_id');
			$un=$this->input->cookie('auth_username');
			$time=time(); 
			$fname="{$id}_book_{$un}_{$time}.csv";//задаем имя нового файла
			$h=fopen("files/".$fname,'c'); // создаем файл
			if($h){
			ini_set('memory_limit', '1024M');
			$search=$_POST['what'];
			
			
			if($search){
				$book=$this->book_model->search($search);
			}else{
				 $book=$this->book_model->get();
				
			}
				$to_file='';
			foreach($book as $row){
				$to_file.=@$row['nomer'].','.iconv('UTF-8',"windows-1251",@$row['name']).','.iconv('UTF-8',"windows-1251",@$row['type'])."\r\n";
			}
			
			 
			// записываем в файл
			fwrite($h,$to_file);
			fclose($h);
			$path=$this->config->item('base_url');  
			// выводим ссылку на файл
				echo '<a href="'.$path.'/files/'.$fname.'">Сохранить файл '.$fname.' </a>';
			 }else{
				echo 'Не удалось сформировать файл'; 
			 }
			
		}
		function import(){
			$file = $_FILES['file'];
			move_uploaded_file($_FILES["file"]["tmp_name"], 'files/' . $_FILES["file"]["name"]);
			//echo 'файл загружен '.$_FILES["file"]["name"].'<br>';
			$f=file('files/'.$_FILES["file"]["name"]);
			$array=array();
			$id=$this->input->cookie('auth_id');
			foreach($f as $k=>$val){
				$str=explode(',',trim($val));
				if(!$str[0]) continue;
				if(!is_numeric($str[0])) continue;
				
				$array[$k]['nomer']=$str[0];
				$array[$k]['name']=iconv("windows-1251",'utf-8',@$str[1]);
				$array[$k]['type']=iconv("windows-1251",'utf-8',@$str[2]);
				$array[$k]['authid']=$id;
			}

		//	print_r($array);
			$book=$this->book_model->import($array);
			echo 'Вставлено: '.$book.' строк</br>';
			$del=unlink('files/'.$_FILES["file"]["name"]);
			if($del){
				//echo 'файл успешно удален<br>';
			}
		}
//end of class
}