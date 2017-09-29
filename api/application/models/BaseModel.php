<?php

class BaseModel extends CI_Model {

	public $table = "";
	public $soft_delete = false;
	public $order_by_priority = false;

	/* $relations expects = [
		'table' => {table},
		'key' => {table_key ex user_id},
		'joins' => [
			['table' => {table}, 'key' => {key}]
		]

		$filters expects = [
			['col','value'],
			['col operator','value']
		]
	] */
	public $relations = [];

	private function appendChildren($result, $relations) {
		//has relations, so we'll iterate each declared child and nest them into the result with a key of {table_name}

		//iterate each declared child relation
		if ($result) {
			foreach ($relations as $rel) {

				if (isset($rel['model'])) {
					$this->load->model($rel['model']);

					$model = $rel['model'];

					if (isset($rel['hasMany']) && $rel['hasMany'] == true) {
						$filters = [
							[$rel['table'].".".$rel['key'],$result['id']]
						];

						$child = $this->$model->get(false, $filters);
					}
					else {
						$child = $this->$model->find($rel['table'].".id", $result[$rel['key']]);
					}	

				}
				else {
					$this->db
						->select($rel['table'].".*")
						->from($rel['table']);

					//if any joins are declared, join and merge them into the child record
					if (isset($rel['joins'])) { //child of child is set
						foreach ($rel['joins'] as $join) {
							$this->db->join($join['table'],$rel['table'].".".$join['key']." = ".$join['table'].".id",'left');
						}
					}

					if (isset($rel['hasMany']) && $rel['hasMany']) {
						if (isset($rel['model'])) {
							$this->load->model($rel['model']);

							$filters = [
								[$rel['table'].".".$rel['key'],$result['id']]
							];

							$model = $rel['model'];

							$child = $this->$model->get(false, $filters);

						}
						else {
							$child = $this->db
								->where($rel['table'].".".$rel['key'],$result['id'])
								->get()->result_array();
						}
					
					}
					else {
						$child = $this->db
							->where($rel['table'].".id",$result[$rel['key']])
							->get()->row_array();
					}
				}
					
				$result[$rel['table']] = $child;
			}
		}

		return $result;
	}

	public function get($include_deleted = false, $filters = [], $order = [], $include_children = true)
	{
		$this->db
			->select($this->table.".*")
			->from($this->table);

		if ($this->order_by_priority)
			$this->db->order_by("priority","ASC");

		if (!empty($order)) {
			$this->db->order_by($order[0],$order[1]);
		}

		//do not include deleted records if soft delete enabled on model
		if ($this->soft_delete)
			$filters[] = [$this->table.'.deleted',0];

		if ($this->relations) {
			foreach ($this->relations as $rel) {

				if (!isset($rel['hasMany']) || (isset($rel['hasMany']) && !$rel['hasMany']))
					$this->db->join($rel['table'],$this->table.".".$rel['key']." = ".$rel['table'].".id",'left');
			}
		}

		foreach ($filters as $filter) {

			if (isset($filter[2]) && strtolower($filter[2]) == 'and') {
				if (isset($filter[3]) && strtolower($filter[3]) == 'like')
					$this->db->like($filter[0],$filter[1]);
				else
					$this->db->where($filter[0],$filter[1]);
			}
			elseif (isset($filter[2]) && strtolower($filter[2]) == 'or') {
				if (isset($filter[3]) && strtolower($filter[3]) == 'like')
					$this->db->or_like($filter[0],$filter[1]);
				else
					$this->db->or_where($filter[0],$filter[1]);
			}
			else
				$this->db->where($filter[0],$filter[1]);
		}

		//var_dump($this->db->get_compiled_select());
		$results = $this->db->get()->result_array();

		$with_children = [];

		//has relations, so we'll iterate each declared child and nest them into the result with a key of {table_name}
		if ($this->relations && $include_children) {
			foreach ($results as $result) {
			
				$with_children[] = $this->appendChildren($result, $this->relations);
			}
			return $with_children;
		}
		else 
			return $results;
		

	}

	public function delete($id) {

		if ($this->soft_delete) {
			$this->db->set("deleted",1)
			->where("id",$id)
			->update($this->table);
		}
		else {
			$this->db->where("id",$id)
				->delete($this->table);
		}
		
	}

	public function find($field, $id, $include_children = true) {

		$condition["id"] = $id;

		if ($this->soft_delete)
			$condition["deleted"] = 0;

		$result = $this->db->get_where($this->table,$condition)->row_array();

		if ($this->relations && $include_children) {
			$result = $this->appendChildren($result, $this->relations);
		}

		return $result;
	}

}