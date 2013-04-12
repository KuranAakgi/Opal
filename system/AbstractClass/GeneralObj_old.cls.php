<?php
abstract class GeneralObj
{
	private $db;
	protected $FIELDS = array();
	protected $defaultFields = array();
	protected $tablename = '';
	
	function __construct($tablename, $id = '')
	{	
		global $db;
		$this->db = $db;
		$this->tablename =  $tablename;
		$sql = "DESCRIBE $this->tablename";
		$result = $this->db->query($sql, PDO::FETCH_ASSOC);
		foreach($result as $row)
		{
			if($row['Key'] == 'PKI')
				$this->FIELDS[$row['Field']] = '';
			else{
				$this->FIELDS[$row['Field']] = $row['Default'];
                                if(!empty($row['Default']))
                                    $this->defaultFields[$row['Field']] = $row['Default'];
                        }
		}
		
		$this->FIELDS['ID'] = '';
	
		if($id != '')
			$this->get($id);	
	}
	
	function getTablename()
	{
		return $this->tablename;
	}
	
	function getID() {
		return $this->FIELDS["ID"];
	}

	function getField($column){
		return $this->FIELDS[$column];
	}
	
	function getFields()
	{
		return $this->FIELDS;
	}
	
	function get($id)
	{
		$sql = "SELECT * FROM $this->tablename WHERE ID = :id";
		$PDOStatement = $this->db->prepare($sql);
		$PDOStatement->bindParam(":id", $id);
		$PDOStatement->execute();
		
		if($PDOStatement->rowCount() != 1)
			return false;
		
		$result = $PDOStatement->fetch(PDO::FETCH_ASSOC);

		foreach($result as $key => $value)
			$this->FIELDS[$key] = $value;
		return true;
	}	
	
	function insert()
	{
		$sql = "INSERT INTO $this->tablename SET ";
		
		$fieldsCopy = $this->FIELDS;
		unset($fieldsCopy['ID']);
		
		$tmp_arr = array();
		foreach($fieldsCopy as $key => $value)
			$tmp_arr[] = " $key = :$key";
		$sql .= implode(',', $tmp_arr);
		$PDOStatement = $this->db->prepare($sql);
		
		foreach($fieldsCopy as $key => $value)
                    if(array_key_exists($key, $this->defaultFields) && $value === $this->defaultFields[$key])
                        $PDOStatement->bindValue(":$key", null, PDO::PARAM_NULL);
                    else
			$PDOStatement->bindParam(":$key", $value);
			
		return $PDOStatement->execute() or die(print_r($this->db->errorInfo(), true));
	}
	
	function update()
	{
		$sql = "UPDATE $this->tablename SET ";
		
		$tmp_arr = array();
		foreach($this->FIELDS as $key => $value)
			$tmp_arr[] = " $key = :$key ";
		$sql .= implode(',',$tmp_arr);
		$sql .= ' WHERE ID = :ID';
		
		$PDOStatement = $this->db->prepare($sql);
		
		foreach($this->FIELDS as $key => $value)
			$PDOStatement->bindParam(":$key", $value);
			
		return $PDOStatement->execute();
	}
	
	function delete()
	{
		$PDOStatement = $this->db->prepare("DELETE FROM $this->tablename WHERE ID = :id");
		$PDOStatement->bindParam(':id', $this->FIELDS['ID']);
		
		return $PDOStatement->execute();
	}

	function setFields($arr)
	{
  		foreach($arr as $key => $value)
			if(array_key_exists($key, $this->FIELDS))
				$this->FIELDS[$key] = trim(addslashes($value));
	}
  
	function setID($newId) {
		$this->FIELDS['ID'] = trim(addslashes($newId));
	}

	function searchID($keyword, $fieldname_array='', $operator = 'LIKE', $join_operator = 'OR')
	{
		if($operator == 'LIKE')
			$keyword = "%$keyword%";
		if($fieldname_array == '')
			$fieldname_array = array_keys($fieldname_array);
		
		$sql = "SELECT ID FROM $this->tablename WHERE ";
		
		foreach($fieldname_array as $key => $fieldname)
			$fieldname_array[$key] = "$fieldname $operator :keyword";
		$sql .= implode(" $join_operator ", $fieldname_array);
		
		$PDOStatement = $this->db->prepare($sql);
		$PDOStatement->bindParam(':keyword', $keyword);
		$PDOStatement->execute();
		
		$idList = array();
		while ($row = $PDOStatement->fetch(PDO::FETCH_ASSOC))
			$idList[] = $row['ID'];
		
		return $$idList;
	}
}