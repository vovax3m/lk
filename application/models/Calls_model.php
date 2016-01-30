<?php
class Calls_model extends CI_Model {

	public function __construct()
	{
		$this->load->database(); 
	}
	 public function getaccid($id){
		 $data['id'] = $id ;
		$this->db->where($data);
		$query = $this->db->get('auth');
		if ($query->num_rows() > 0){
			$row = $query->row_array(); 
			return $row['accid'];
  		}
	 }
	 public function getcalls($limit,$filter=false,$id){
		// echo $filter;
		
		if(!$filter){
			$q='SELECT * FROM callevent_'.$id.' ORDER BY id DESC LIMIT '.$limit;
		}else{
			
			$f=explode('&',$filter);
			foreach($f as $par){
				$param=explode('=',$par);
				if($param[1]=='zero' and ($param[0]=='durtime' or $param[0]=='durtime_sec')){
					$ff[$param[0]] = '0';
					//$data['fltr'][$k]='0'; 
					//$fltr[$k]='zero';
				}else{
						if($param[1])  $ff[$param[0]] = $param[1];
				}
			
				
			}
			//print_r($ff);
		}
		
				
				
					if($ff['incom']=='on') {
						$where.="AND Direct = 'inc'  ";
					
					}
					if($ff['outcom']=='on') {
						$where.="AND Direct = 'out'  ";
						
					}
					if($ff['durtype'] and ($ff['durtime'] or $ff['durtime']=='0' )) {
						switch($ff['durtype']){
							case 'mo':
								$where.=' AND (Duration > '.$ff['durtime'].')';
							break;
							case 'le':
								$where.=' AND (Duration < '.$ff['durtime'].')';
							break;
							case 'is':
								$min =$ff['durtime']-59;
								$where.=' AND (Duration between '.$min.' and '.$ff['durtime'].')';
							break;
						}
					
					}
					if($ff['anstype']){
						switch($ff['anstype']){
							case 'ans':
								$where.=" AND DisconnectCause = 16 AND Duration > 0 ";
							break;
							case 'noans':
								$where.=" AND DisconnectCause = 16 AND Duration = 0 ";
							break;
							case 'fail':
								$where.=" AND DisconnectCause != 16 AND DisconnectCause != 17 ";
							break;
							case 'busy':
									$where.=" AND DisconnectCause = 17 ";
							break;
							case 'unk':
								$where.=" AND DisconnectCause != 16 AND DisconnectCause != 17 ";
							break;
						}
					}
					if($ff['exten']){
						$where.=" AND (Called = '{$ff['exten']}' OR Calling = '{$ff['exten']}') ";
					}					
						
						//поля  поиска 
						// искомое
						if(isset($ff['find'])){
							$having=' HAVING ';
							
							if(isset($ff['FindByName'])){
							$fbn=true;
							$nums=explode(',',$ff['FindByName']);
							}else{
							$fbn=false;
								$nums[]=$ff['find'];
							}
							
							//$where.= ' AND ';
							foreach($nums as $find){
							if(!$find)continue;
							$res.=$find.',';
							}
							$find=substr($res,0,-1);
							//позиция в тексте
							if($fbn!=true){
								if(isset($ff['findplace'])){
									$place=" like '%$find%'";
									// начало строки
									if($ff['findplace']=='end'){
									$place=" like '%$find'";
										}
									// конец строки
									if($ff['findplace']=='begin'){
									$place=" like '$find%'";
									}
									// в любом месте строки
									if($ff['findplace']=='incl'){
									$place=" like '%$find%'";
									}
									// точное совпадение
									if($ff['findplace']=='equal'){
									$place=" = '$find'";
									}
								}
								else{
									$place=" like '%$find%'";
								}
							}
							else{
							$place=" in ($find)";
							}
							// столбец
							if(isset($ff['findfield'])){
								//поиск по источнику
								if($ff['findfield']=='src'){
									$having.=" (Calling $place)";
								}
								//поиск по назначению
								if($ff['findfield']=='dst'){
									$having.=" (Called $place)";
								}
								//поиск по обоим полям
								if($ff['findfield']=='both'){
									$having.=" (Calling $place or Called $place)";
								}
							}
							else{
								$having.=" (Calling $place or Called $place)";
							}
						}
		   if(!$limit){
			   $q='SELECT CallDate as Date,Called as B,Calling as A, DisconnectCause as Cause, Duration as Dur, Direct as Dir FROM callevent_'.$id.' WHERE id>0 '.$where.' '.$having ;
		   }
			else{
				$q='SELECT CallDate as Date,Called as B,Calling as A, DisconnectCause as Cause, Duration as Dur, Direct as Dir FROM callevent_'.$id.' WHERE id>0 '.$where.' '.$having .' ORDER BY id DESC LIMIT '.$limit;
			}
		   $query = $this->db->query($q);
		   $calls= $query->result_array();
		   
		    $q='SELECT count(id) as total FROM (SELECT id,Called,Calling FROM callevent_'.$id.' WHERE id>0 '.$where.' '.$having.') as res';
			$call = $this->db->query($q);
			$count=$call->result_array();
			$calls['total']=$count[0]['total'];
			//echo $count['total'];
			
			return $calls;
	}
	function getnums($json,$id){
		
		$j=$json;
		$list='';
		foreach($j as $no){
			$list.="'".$no."',";
		};
		$list=substr($list,0,-1); 
		
		$q='SELECT DISTINCT Called FROM callevent_'.$id.' WHERE Direct ="inc" AND Called NOT IN ('.$list.')';
		//echo $q;
		$query = $this->db->query($q);
		//print_r($query->result_array());
		$inc=$query->result_array();
		$q='SELECT DISTINCT Calling FROM callevent_'.$id.' WHERE Direct ="out" AND Calling NOT IN ('.$list.')';
		$query = $this->db->query($q);
		//print_r($query->result_array());
		$out=$query->result_array();
		$all=array_merge($inc,$out);
		foreach($all as $no){
				if($no['Calling']){
					$j[]=$no['Calling'];
				}else{
					$j[]=$no['Called'];
				};
		};
		return ($j);
	}
}