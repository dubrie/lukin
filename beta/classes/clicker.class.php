<?php
require_once('baseclass.class.php');

class clicker extends baseclass {
	protected $id;
	protected $table;
	protected $column;
	protected $value;
	protected $userID;

	public function clicker() {
		$this->reset();
	}

	public function parse($val) {
		$debris = explode("_",$val);
		$this->table 	= $debris[0];
		$this->column 	= $debris[1];
		$this->id 		= $debris[2];
	}

	public function update() {
		/* Update the given column on the given table with the given value */
		$date = date("Y-m-d H:i:s");

		$query=mysql_query("update ".$this->table." set ".$this->column." = '".mysql_escape_string($this->value)."', modified_by = ".$this->userID.", modified='".$date."' where id=".$this->id);

	}

}
?>