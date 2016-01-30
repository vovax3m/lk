<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stat extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->library('cab_auth'); 
		$this->load->model('book_model');        // ���������� �����
		//auth check
		if(!$this->cab_auth->check()){ $bu= base_url(); setcookie('STAT', '����������, �������', time()+86400);   header('Location: '.$bu.'auth/login') ;}
	}
	public function index(){
			$this->load->helper('url');
			$this->load->library('funcs'); 
			/* �������� �������  ���������� �������*/
			//��������� �������� �� ������������ ������� ��� ���, boolean
			$isadmin=$h['isadmin']=$data['isadmin']=$this->cab_auth->is_admin(); 
			//���������� ������� ���� �����  ������������ ��� ������������� � ����������� �������� � ������
			$bu=$h['base_url']=base_url();
			//������� ����������� ��������
			$h['is_full']=$data['is_full']=$this->auth_model->get_setting('type'); 
			// ���������� ����� ������������
			// $exten=$this->input->cookie('auth_exten', TRUE);
			 
			$filter=json_decode($this->input->cookie('fltr', TRUE));
			 // ��������� �������  ���������� ��������
			foreach($filter as $k=>$v){
				
				$data['fltr'][$k]=$fltr[$k] =$v; 
				// �� ������������ �� ��� ����� �� ��������� �������
				//if($k=='incom' or $k=='outcom'){
				//	continue;
				//}
				if($v){
					$filter.=$k.'='.$v.'&';
				}
			}
			if(!$fltr['startdate'] ) $fltr['startdate']=$data['fltr']['startdate']=date('Y-m-d');
			if(!$fltr['enddate'] ) $fltr['enddate']=$data['fltr']['enddate']=date('Y-m-d');
			 $data['extlist']=$this->funcs->GetNums();
			if($isadmin){
				$data['fltr']['exten']   =$fltr['exten'] ;
			}else{
			    $data['fltr']['exten']   =$fltr['exten'] =$this->funcs->checkbad($exten); 
				$filter.='exten='.$exten.'&';
			}
			//����������� ����� �� �����
			$data['name']=$this->book_model->get($fltr['exten'],1);
			
		//-----
		//$vats = $this->config->item('vats');
		//$f=file_get_contents('https://'.$vats.'/cabinet/cdr.php?stat=true&'.$filter);
		//$limit=false;
		$rawdata=$this->funcs->getcalls(false,$filter);
		//$data['row']=$rawdata=json_decode($f,true);
		// ----
		//counters
		$data['count_inc']=0; // ���������� ��������
		$data['count_out']=0; // ���������� ���������
		$data['count_noans']=0; // ���������� �� �����
		$data['count_ans']=0; // ���������� �����
		$data['count_ans_inc']=0; // ���������� ����� ��������
		$data['count_noans_inc']=0; // ���������� �� ����� ��������
		$data['count_ans_out']=0; // ���������� ����� ���������
		$data['count_noans_out']=0; // ���������� �� ����� ���������
		$data['count_busy']=0; // ����������  �������
		$data['count_busy_inc']=0; // ����������  ������� ����
		$data['count_busy_out']=0; // ����������  ������� �����
		$data['count_fail']=0; // ���������� ���������
		$data['count_fail_inc']=0; // ���������� ��������� ����
		$data['count_fail_out']=0; // ���������� ��������� �����
		$data['count_unk']=0; // ���������� ������
		$data['count_talk']=0; // ���������� �����
		$data['count_talktime_all']=0; //����� ������������
		$data['count_talktime_inc']=0; //����  ������������
		$data['count_talktime_out']=0; //����� ������������
		//$data['acc']=array(); // �������� ������ DID
				
		
		//������� ������, ���������� ���������
		if($rawdata['total']){
			foreach($rawdata as $k=>$s){ 
				if($s['total']){
				 	$data['total']=$s;
					continue;
				};
				
				
				
				$num=$s['A'];
				$dnum=$s['B'];
				
				/* new divisiond mode of direction calls 
				 �������� �������  � ���������� �� ���� ���������� �� ������ �� ��������
				*/
				// ���� ���� ������
				if($fltr['exten']){
					if($num==$fltr['exten']){
						//���
						$data['count_out']++;
						if($s['Cause']=='16' and $s['Dur']>0){  
							$data['count_ans_out']++;
							$data['count_talktime_out']+=$s['Dur'];
						}elseif($s['Cause']=='16' and $s['Dur']==0){
							$data['count_noans_out']++; 
						}elseif($s['Cause']=='17'){
							$data['count_busy_out']++;
						}else{
							$data['count_fail_out']++;
						}
					}
					elseif($dnum==$fltr['exten']){
						//��������
						
						$data['count_inc']++;
						/*
						// ���������� ������� �� did
						if($s['account']){
							$data['acc'][$s['account']]['count']++;
							$data['acc'][$s['account']]['dur']+=$s['Dur'];
						};
						*/
						
						// ������ �� �������
						if($s['Cause']=='16' and $s['Dur']>0){  
							$data['count_ans_inc']++;
							$data['count_talktime_inc']+=$s['Dur'];
						}elseif($s['Cause']=='16' and $s['Dur']==0){
							$data['count_noans_inc']++; 
						}elseif($s['Cause']=='17'){
							$data['count_busy_inc']++;
						}else{
							$data['count_fail_inc']++;
						}
					}
					else{
						//��������
					
						// ���������� ��������
						$data['count_inc']++;
						// ���������� ������� �� did
						if($s['account']){
							$data['acc'][$s['account']]['count']++;
							$data['acc'][$s['account']]['dur']+=$s['Dur'];
						};
						
						
						// ������ �� �������
						if($s['Cause']=='16' and $s['Dur']>0){  
							$data['count_ans_inc']++;
							$data['count_talktime_inc']+=$s['Dur'];
						}elseif($s['Cause']=='16' and $s['Dur']==0){
							$data['count_noans_inc']++; 
						}elseif($s['Cause']=='17'){
							$data['count_busy_inc']++;
						}else{
							$data['count_fail_inc']++;
						}
					}
				}
				
				// ���� �� ���������� ������ �� ��������
				else{
					
					if( $s['Dir']=='out'){
						
						// ���������
						$data['count_out']++;
						if($s['Cause']=='16' and $s['Dur']>0){  
							$data['count_ans_out']++;
							$data['count_talktime_out']+=$s['Dur'];
						}elseif($s['Cause']=='16' and $s['Dur']==0){
							$data['count_noans_out']++; 
						}elseif($s['Cause']=='17'){
							$data['count_busy_out']++;
						}else{
							$data['count_fail_out']++;
						}
					}
					elseif($s['Dir']=='inc'){
						//��������
					
						// ���������� ��������
						$data['count_inc']++;
						/*
						// ���������� ������� �� did
						if($s['Dir']='inc'){
							$data['acc'][$num]['count']++;
							$data['acc'][$num]['dur']+=$s['Dur'];
						};
							*/					
						// ������ �� �������
						if($s['Cause']=='16' and $s['Dur']>0){  
							$data['count_ans_inc']++;
							$data['count_talktime_inc']+=$s['Dur'];
						}elseif($s['Cause']=='16' and $s['Dur']==0){
							$data['count_noans_inc']++; 
						}elseif($s['Cause']=='17'){
							$data['count_busy_inc']++;
						}else{
							$data['count_fail_inc']++;
						}
					}
				}
				
				//�������� ���������
				if($s['Cause']=='16' and $s['Dur']==0){
						$data['count_noans']++;
				}elseif($s['Cause']=='16' and $s['Dur']>0){  
						$data['count_ans']++;
						$data['count_talk']++;
						$data['count_talktime_all'] += $s['Dur'];
						if(in_array($num,$data['extlist'])){
							$data['actext'][$num] += $s['Dur'];
						}
						if(in_array($dnum,$data['extlist'])){
							$data['actext'][$dnum] += $s['Dur'];
						}
						
				}elseif($s['Cause']=='17'){
						$data['count_busy']++;
				}else{
						$data['count_fail']++;
				
				}
				
				
			}
			
			foreach($data['actext'] as $exts =>$secs){
				$digitexts[$exts]=$secs;
				
			// ����� ��������	
			}
			
		//����� �����	
		}
		/*
		//����������  did
		if($data['acc']){
			arsort($data['acc']);
			//$data['acc']=array_chunk($data['acc'], 10,true); // ����� �� 5
			
		}
		*/
		// ������� ���������
		if($fltr['exten']){
			$data['rating']=$digitexts[$fltr['exten']];
		}else{
			arsort($digitexts); // ��������� �� �������� � ��������
			$li=array_chunk($digitexts, 10,true); // ����� �� 5
			
			foreach($li[0]as $k=>$v){
				$fi[$k]['no']=$k;
				$fi[$k]['dur']=$v;
				$n=$this->book_model->get($k,1);	
				$fi[$k]['name']=$n['name'].' '.$n['type'];
				
			}
			
			$data['rating']=$fi; // ����� ������ 5��
		}
		
		$h['isadmin']=$this->cab_auth->is_admin();
		$h['base_url']=base_url();
		// ������
		$this->load->view('/templates/header',$h); 
		$this->load->view('statistic',$data);
		$this->load->view('/templates/footer',$h);
	}
	
	// �������� �������
	
	function count(){
		
		$this->load->helper('url');
			$this->load->library('funcs'); 
			/* �������� �������  ���������� �������*/
			//��������� �������� �� ������������ ������� ��� ���, boolean
			$isadmin=$h['isadmin']=$data['isadmin']=$this->cab_auth->is_admin(); 
			//���������� ������� ���� �����  ������������ ��� ������������� � ����������� �������� � ������
			$bu=$h['base_url']=base_url();
			// ���������� ����� ������������
			 $exten=$this->input->cookie('auth_exten', TRUE);
			 
			 $filter=json_decode($this->input->cookie('fltr', TRUE));
			 // ��������� �������  ���������� ��������
			foreach($filter as $k=>$v){
				$data['fltr'][$k]=$fltr[$k] =$v; 
				if($v){
					$filter.=$k.'='.$v.'&';
				}
			}
			if(!$fltr['startdate'] ) $fltr['startdate']=$data['fltr']['startdate']=date('Y-m-d');
			if(!$fltr['enddate'] ) $fltr['enddate']=$data['fltr']['enddate']=date('Y-m-d');
			$data['extlist']=$this->funcs->GetExt();
			if($isadmin){
				$data['fltr']['exten']   =$fltr['exten'] ;
			}else{
			    $data['fltr']['exten']   =$fltr['exten'] =$this->funcs->checkbad($exten); 
				$filter.='exten='.$exten.'&';
			}
			
		//-----
		$vats = $this->config->item('vats');
		$f=file_get_contents('https://'.$vats.'/cabinet/cdr.php?stat=true&'.$filter);
		$data['row']=$rawdata=json_decode($f,true);
		if($rawdata['total']){
			foreach($rawdata as $k=>$s){ 
				if($s['total']){
				 	$data['total']=$s;
					continue;
				};
				
				$data['diap'][$s['uniqueid']]['start']=(strtotime($s['calldate'])-$s['billsec']);
				$data['diap'][$s['uniqueid']]['end']=strtotime($s['calldate']);
				$data['diap'][$s['uniqueid']]['dur']=$s['billsec'];
				
			}
		
		}
		print_r($data['diap']);
		
	}
	
}
