<?php
class Book_model extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}
	
	public function get($nomer,$only=False) 
	{	
		$id=$this->input->cookie('auth_id');
		if($nomer){
			$data['nomer'] = $nomer;
			$data['authid'] = $id;
			$this->db->where($data);
			$query = $this->db->get('book'); 
			if ($query->num_rows() > 0){
				if($only){
					return $query->row_array();
				}else{
					$row=$query->row_array(); 
					return '<br><span class="book_name" >'.$row['name'].' '.$row['type'].'</span>';
				}
				
			}
		}else{
			$this->db->order_by('id','desc'); 
			$data['authid'] = $id;
			$this->db->where($data);
			$query = $this->db->get('book'); 
			return $query->result_array(); 
		}
		
	}
	public function search($what) {
			$id=$this->input->cookie('auth_id');
			$data['authid'] = $id;
			$this->db->where($data);
			$this->db->like('nomer',$what); 
			$this->db->or_like('name',$what); 
			$this->db->or_like('type',$what); 
			$this->db->order_by('id','desc'); 
			$query = $this->db->get('book'); 
			return $query->result_array(); 
	}
	public function find($what) {
			$id=$this->input->cookie('auth_id');
			$data['authid'] = $id;
			$this->db->where($data);
			$this->db->like('name',$what);
			//$this->db->or_like('type',$what); 
			$query = $this->db->get('book'); 
			print_r($query->result_array());
			return $query->result_array(); 
	}
	public function set($id,$nomer,$name,$type) {
		$id=$this->input->cookie('auth_id');
		$data['authid'] = $id;
		$data['nomer'] = $nomer;
		$data['name'] = $name;
		$data['type'] = $type;
		$this->db->where('id', $id);
		return $this->db->update('book', $data);
	}
	public function add($nomer,$name,$type) {
		$id=$this->input->cookie('auth_id');
		$data['authid'] = $id;
		$data['nomer'] = $nomer;
		$data['name'] = $name;
		$data['type'] = $type;
		return $this->db->insert('book', $data);
	}
	public function del($id) {
		
		$this->db->where('id', $id);
		return $this->db->delete('book');
	}
	public function import($array) {
			$id=$this->input->cookie('auth_id');
			$data['authid'] = $id;		
			foreach($array as $k=>$str){
				$this->db->where($data);
				$this->db->where('nomer', $str['nomer']);
				$query = $this->db->get('book'); 
				if($query->num_rows()!=0){
					$exist[]=$str['nomer'];
					unset($array[$k]);
				}
			}
		/*
		if($exist){	
			echo 'Данные номера уже занесены в справочник :';
			foreach($exist as $nomer){
				echo $nomer.',';
			}
			echo '<br>';
		}
		*/
		if($array){
			$this->db->insert_batch('book', $array);
		}
		return $this->db->affected_rows();
	}
}