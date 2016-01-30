<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->library('cab_auth');  // подключаем библиотеку авторизации
		$this->load->library('funcs');        // подключаем библиотеку доп функций
		//проверка авторизации
		if(!$this->cab_auth->check()){
			$bu= base_url();  // определяем базовый путь
			setcookie('STAT', 'Пожалуйста, войдите', time()+86400); // записываем в куку сообщение
			header('Location: '.$bu.'auth/login') ; // переадресовываем на страницу с формой входа
		}
	}
	/**
	* ФУНКИЯ ЭКСПОРТА списка звонков В ЭКСЕЛЬ
	*  принимаем querystring и на основе нее получаем список звонков, 
		разбираем фильтр,оформаляем в экселе, потом шапку звонков и список
	*/
		
	function export_xlsx(){
			ini_set('memory_limit', '1024M');
			//error_reporting( E_ERROR | E_WARNING | E_PARSE );	
			
			//$CI =&get_instance();
		    $this->load->library('excel');//загружаем модуль phpexcel
			$this->load->library('iofactory');//загружаем модуль phpexcel
			$xls = new PHPExcel(); //инициализация phpexcel
			//var_dump($xls);
			$xls->setActiveSheetIndex(0); //выбираем активный лист
			$sheet = $xls->getActiveSheet();//выбираем активный лист
			//var_dump($sheet);
			//$title=$this->config->item('klient_name'); //получаем из конфига  название клиента
			$s=(json_decode($this->funcs->getsaldo(),TRUE));
			asort($s);
			$title='';
			foreach($s as $one){
							$title.=$one[0].', ';
						}
			$sheet->setTitle($s[0][0]);  //устанавливаем титл
			$sheet->setCellValue("A1", substr($title,0,-2));//устанавливаем заголовок
			$sheet->getStyle('A1')->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID); //заливка
			$sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE'); //фон
			$sheet->getStyle('A1')->getFont()->setName('Arial'); //шрифт
			$sheet->getStyle('A1')->getFont()->setSize('14'); // размер
			$sheet->mergeCells('A1:F1'); // объединенние ячеек
			$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // выравнивание по центру
			
			//получаем айпишнек ватс из конфига
			 $filter=json_decode($this->input->cookie('fltr', TRUE));
			// print_r($filter);
			 // проверяем наличие  параметров фильтров
			foreach($filter as $k=>$v){
				$data['fltr'][$k]=$fltr[$k] = $v; 
				if($v){
					$filter.=$k.'='.$v.'&';
				};
			}
			$rawdata=$this->funcs->getcalls(false,$filter);
			 $out_total= $rawdata['total'];  // общее количество записей
			//print_r($rawdata);
			//$sheet->setCellValue("B2", $_SERVER['QUERY_STRING']);
			$sheet->setCellValue('A2',"всего записей: ".$out_total); // заполняем ячейку
			//$sheet->setCellValue('A3',"Фильтр: ");
			//$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			//$sheet->getStyle('A3')->getFill()->getStartColor()->setRGB('EEEEEE');
			//$sheet->getStyle('A3')->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID); //заливка
			//$sheet->mergeCells('A3:B3');
			$rowno=4; // строка по умолчанию для фильтра
				
			
			
			
			
			//($rawdata);
			
			/*
			// разбираем квери стринг
			$params=explode('&',$_SERVER['QUERY_STRING']);  
			// перебираем параметры, переименовываем в понятное название
			foreach($params as $one){
				$par=explode('=',$one);
					if($par[0]=='export_xlsx') continue;
					if($par[1]){
						$name=$this->funcs->get_fltr_name($par[0]); 
						if($par[0]=='durtype'){
							switch($par[1]){
								case 'mo':
								$val ="Больше";
								break;
								case 'le':
								$val ="Меньше";
								break;
								case 'is':
								$val ="Равно";
								break; 
							}	
						}elseif($par[0]=='anstype'){
							switch($par[1]){
								case 'ans':
								$val ="Отвеченные";
								break;
								case 'noans':
								$val ="Не отвеченные";
								break;
								case 'busy':
								$val ="Занятые";
								break;
								case 'fail':
								$val ="Неудачные";
								break;
								case 'unk':
								$val ="Другие";
								break;
							}	
						}else{
							$val=$par[1];
						}
							// записываем название
							$sheet->setCellValue("A$rowno",$name.':');
							$sheet->getStyle("A$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT); 
							// записываем значение
							$sheet->setCellValue("B$rowno",$val);
							$sheet->getStyle("B$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
							$rowno++; // перебираем параметры фильтра
					}
			
			}
			*/
			
			$rowno++; // пропускаем строку
			$sheet->setCellValue("A$rowno", 'Дата '); 
			$sheet->getColumnDimension('A')->setAutoSize(true) ;
			 // выравнивание по центру
			$sheet->getStyle("A$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$sheet->getStyle("B$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$sheet->getStyle("C$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$sheet->getStyle("D$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle("E$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 			
			$sheet->getStyle("F$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			//$sheet->getStyle("G$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			//фон
			$sheet->getStyle("A$rowno")->getFill()->getStartColor()->setRGB('EEEEEE');
			$sheet->getStyle("B$rowno")->getFill()->getStartColor()->setRGB('EEEEEE');
			$sheet->getStyle("C$rowno")->getFill()->getStartColor()->setRGB('EEEEEE');
			$sheet->getStyle("D$rowno")->getFill()->getStartColor()->setRGB('EEEEEE');
			$sheet->getStyle("E$rowno")->getFill()->getStartColor()->setRGB('EEEEEE');
			$sheet->getStyle("F$rowno")->getFill()->getStartColor()->setRGB('EEEEEE');
			//$sheet->getStyle("G$rowno")->getFill()->getStartColor()->setRGB('EEEEEE');
			//заливка
			$sheet->getStyle("A$rowno")->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID);  
			$sheet->getStyle("B$rowno")->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID); 
			$sheet->getStyle("C$rowno")->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID); 
			$sheet->getStyle("D$rowno")->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID); 
			$sheet->getStyle("E$rowno")->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID); 
			$sheet->getStyle("F$rowno")->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID); 
			//$sheet->getStyle("G$rowno")->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID); 
			
			$sheet->setCellValue("B$rowno", 'Направление');
			$sheet->getColumnDimension('B')->setAutoSize(true) ;
			$sheet->setCellValue("C$rowno", 'Источник');
			$sheet->getColumnDimension('C')->setAutoSize(true) ;
			$sheet->setCellValue("D$rowno", 'Назначение');
			$sheet->getColumnDimension('D')->setAutoSize(true) ;
			$sheet->setCellValue("E$rowno", 'Статус');
			$sheet->getColumnDimension('E')->setAutoSize(true) ;
			$sheet->setCellValue("F$rowno", 'Длительность');
			$sheet->getColumnDimension('F')->setAutoSize(true) ;
			//$sheet->setCellValue("G$rowno", 'Линия');
			//$sheet->getColumnDimension('G')->setAutoSize(true) ;
			$rowno++;
			//перебираем по аналогии с конроллером history
			//print_r($rawdata);
			if(!$rawdata['total'])exit;
			foreach($rawdata as $k=>$s){ 
				if($s['total']){
				 	$data['total']=$s;
					continue;
				};
				
				
				
				$num=$s['A'];
				$dnum=$s['B'];
				if($s['Dir']=='out'){
					$out_direction='Исходящий';
					$out_dst=$s['B'];
				}else{
					$out_direction='Входящий'; 
					$out_dst=$dnum;
				}
				$out_calldate=$s['Date'];
				$out_src=$s['A'];
				if($s['Cause']=='16' and $s['Dur']==0){ 
						$out_status='не отвечен';
				}elseif($s['Cause']=='16' and $s['Dur']>0){ 
						$out_status='отвечен';
				}elseif($s['Cause']=='17'){
						$out_status='занят';
				}
				else{
						$out_status='ошибка';
				}
				//$account=$s['account'];
				$out_dur=$this->funcs->dur2min($s['Dur']);
				// записываем в ячейки
				$sheet->setCellValue("A$rowno",$out_calldate);
				$sheet->getStyle("A$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
				$sheet->setCellValue("B$rowno",$out_direction);
				$sheet->getStyle("B$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
				$sheet->setCellValue("C$rowno",$out_src);
				$sheet->getStyle("C$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
				$sheet->setCellValue("D$rowno",$out_dst);
				$sheet->getStyle("D$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
				$sheet->setCellValue("E$rowno",$out_status);
				$sheet->getStyle("E$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
				$sheet->setCellValue("F$rowno",$out_dur );
				$sheet->getStyle("F$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
				//$sheet->setCellValue("G$rowno",$account );
				//$sheet->getStyle("G$rowno")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
				$rowno++;
	
			}
			// сохраняем в файл
			$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
			$id=$this->input->cookie('auth_id');
			$time=time(); 
			$fn="{$id}_xlsx_".$time; // назание файла
            $fname="files/$fn.xlsx";//задаем имя нового файла
		    $objWriter -> save($fname);//сохраняем файл	
			// получаем базовый путь (доменное имя)
			$path=$this->config->item('base_url');
			// выводим ссылку на файл
			echo 'Сохранить файл: <a href="'.$path.'/'.$fname.'">'.$fn.'.xlsx </a>';
		
	}
	/**
	* ФУНКИЯ ЭКСПОРТА списка звонков В CSV разделеные запятыми столбцы
	*  тоже самое что эксель, без вывода  фильтра, только звонки
	*/
	function export_csv(){
		//error_reporting( E_ERROR | E_WARNING | E_PARSE );	
			ini_set('memory_limit', '1024M');
			
			 $filter=json_decode($this->input->cookie('fltr', TRUE));
			 //var_dump($filter);
	
	
			// проверяем наличие  параметров фильтров
			foreach($filter as $k=>$v){
				$data['fltr'][$k]=$fltr[$k] = $v; 
				if($v){
					$filter.=$k.'='.$v.'&';
				};
			}
			$rawdata=$this->funcs->getcalls(false,$filter);
			// перебираем строки и пишем их в файл
			foreach($rawdata as $k=>$s){
				if($s['total']){
					continue;
				};
				$num=$s['A'];
				$dnum=$s['B'];
				if($s['Dir']=='out'){
					$out_direction='Исходящий';
					$out_dst=$s['B'];
				}else{
					$out_direction='Входящий'; 
					$out_dst=$dnum;
				}
				$out_calldate=$s['Date'];
				$out_src=$s['A'];
				if($s['Cause']=='16' and $s['Dur']==0){ 
						$out_status='не отвечен';
				}elseif($s['Cause']=='16' and $s['Dur']>0){ 
						$out_status='отвечен';
				}elseif($s['Cause']=='17'){
						$out_status='занят';
				}
				else{
						$out_status='ошибка';
				}
				
				$out_dur=$this->funcs->dur2min($s['Dur']);
				// готовим стороку для записи в файл
				$to_file.=$out_calldate.','.iconv('UTF-8',"windows-1251",@$out_direction).','.$out_src.','.$out_dst.','.iconv('UTF-8',"windows-1251",@$out_status).','.$out_dur."\r\n";
			
	
			}
			$id=$this->input->cookie('auth_id');
			$time=time(); 
			$fn="{$id}_csv_".$time; // назание файла
			$fname="files/$fn.csv";//задаем имя нового файла
			$h=fopen($fname,'c'); // создаем файл
			// записываем в файл
			fwrite($h,$to_file);
			fclose($h);
			$path=$this->config->item('base_url');  
			// выводим ссылку на файл
			echo 'Сохранить файл: <a href="'.$path.'/'.$fname.'">'.$fn.'.csv </a>';
			
		}	
		
		function downloadivr(){
		//	print_r($_GET);
			if($_GET['fn']){
				$fn=$_GET['fn'];
				$vats = $this->config->item('vats');
				if($_GET['cdr'] and $_GET['date']){
					$getfile = file_get_contents("http://".$vats."/cabinet/save.php?fn=".$fn.'&cdr=true&date='.$_GET['date']);
				}else{
					$getfile = file_get_contents("http://".$vats."/cabinet/save.php?fn=".$fn);
				}
				if($getfile){
					$content = file_get_contents("http://".$vats."/cabinet/files/".$getfile);
					if(file_put_contents('files/'.$fn, $content)){
						$path=$this->config->item('base_url');
						echo '<a href="'.$path.'/files/'.$getfile.'" download="'.$getfile.'">Сохранить файл '.$getfile.'</a>';
					}
				}
			}
		}
		/*
		 генерим звонок
		*/
		function click2call($a,$b){
			$vats = $this->config->item('vats');
			if($a and $b){
				$getfile = file_get_contents("http://".$vats."/cabinet/call.php?a=".$a.'&b='.$b);
				echo $getfile;
			}
			//return ($getfile: true ? false);
			echo $getfile;
		}

//end of class		
}		