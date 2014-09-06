<?php
include_once( dirname( __FILE__ ) . '/class_model.php' ) ;
class usersModel extends modelDb  {
	var $tableName = 'users';
	var $primaryKey = 'iduser';
	function emptyFields( ) {
		$data = parent::emptyFields();
		$data['email'] = '@' ;
		$data['idgroup'] = 0 ;
		return $data ;
	}
}

?>