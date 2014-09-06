<?php
/*
Version 1.0
*/
class modelDb {
	var $primaryKey = null ;
	var $fields = array();
	var $DB = null ;
	var $tableName = '';
	var $filter = '';
	var $order = '' ; 
	var $alias = array();
	function modelDb( &$DB, $prefixDb = '' ) {
		$this->DB =& $DB ; 
		$this->tableName = $prefixDb . $this->tableName ;
	}
	function showAlias( $fieldName ) {
	
		return ( isset( $this->alias[$fieldName] )) ? $this->alias[$fieldName] : $fieldName;
	}

	function read( $id ) {
		$rs = $this->readRecordset( $id ) ;
		// $this->fields =  $rs->fields ;
		if (  $rs->EOF ) {
			return false  ;
		} else {
			return $rs->fields ;
		}
	}
	function readRecordset( $id ) {
		// returns recordset 
		$searchValue = (  gettype( $id ) == 'integer')  ? $id :	'"' . $id  . '"' ;
		$sql = "select * from {$this->tableName} where {$this->primaryKey}={$searchValue} {$this->filter}"   ; 
		$rs = $this->DB->execute( $sql ) ;
		return $rs  ;
	}
	function validate( $data, $action = 'edit' ) {
		return array( true, array() );
	}
	function emptyFields( ) {
		$rs = $this->emptyRecordset();
		$data = array();
		for ( $i = 0; $i < $rs->FieldCount(); $i++ )  {
			$campo = $rs->FetchField( $i )  ;
			$data[$campo->name] = '' ;
		}
		return $data ;
	}
	function emptyRecordset() {
		$sql =  "select * from {$this->tableName} where  0"  ;
		return $this->DB->execute( $sql ) ;
	}
	function save( $data ) {
		$result = $this->validate( $data, 'edit' ) ;
		if ( $result[0] == true ) {
			$originalValues = $this->readRecordset( $data[ $this->primaryKey ] );
			$sql = $this->DB->GetUpdateSQL(  $originalValues , $data );
			if ( !empty( $sql ) ) {
				$this->DB->Execute($sql); 
			}
		}
		
		return $result ;
	}
	function Execute( $fields , $where = '1' ) {
		$sql = "select {$fields} from {$this->tableName} where  {$where} {$this->filter}"   ;
		$rs = $this->DB->execute( $sql ) ;
		return $rs->fields ;
	}
	function delete( $id ) {
		$searchValue = (  gettype( $id ) == 'integer')  ? $id :	'"' . $id  . '"' ;
		$sql = "delete from {$this->tableName} where {$this->primaryKey}={$searchValue} {$this->filter}"   ; 
		// echo $sql . '<br/>';
		$this->DB->execute( $sql ) ;
	}
	function insert( $data ) {
		$result = $this->validate ( $data, 'add' ) ;
		if ( $result[0] == true ) {
			$rs = $this->emptyRecordset() ;
			$sql = $this->DB->GetInsertSQL(  $rs , $data );
			$this->DB->execute( $sql ) ;
		} 
		
		return $result ;
	}
	function findAll( $where = '1') {
		/* 
		Nos devuelve todos los registros segun el filtro
		*/
		$resultado = array();
		$rs = $this->findAllRecordset( $where = '1' ) ;
		while( !$rs->EOF ) {
			$resultado[] = $rs->fields ;
			$rs->MoveNext();
		}
		return $resultado ;
	}
	function findAllRecordset( $where = '1') {
		/* 
		Nos devuelve todos los registros segun el filtro
		*/
		$resultado = array();
		$sql = "select * from {$this->tableName} where {$where} {$this->filter} "   ;
		if ( !empty ( $this->order ) ) {
			$sql .= " order by {$this->order} ";
		}
		$rs = $this->DB->execute( $sql ) ;
		return $rs ;
	}
}

?>