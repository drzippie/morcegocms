<?php
/*
 => Array ( [name] => postal-2006-mini.jpg [type] => image/pjpeg [tmp_name] => /var/tmp/phpBOuXpB [error] => 0 [size] => 29898 ) [orginalName] => [size] => [tmpName] => [ok] => 1 ) Páginas

*/

class cls_fileupload {
	var $post ;
	var $orginalName ;
	var $size;
	var $tmpName ;
	var $ok ;
	function cls_fileupload( $id ) {
		if ( isset( $_FILES[ $id ] )) {
			$this->post = $_FILES[$id] ;
			if ( $this->post['error'] == 0  && $this->post['size'] > 0) {
				$this->ok = true ;
			} else {
				$this->ok = false  ;
			}
			
		} else { 
			$this->ok = false  ;
		}
	}
	function content( ) {
		/* devuelve el contenido del fichero */ 
		$strOut  = '';
		if  ( $this->ok == true )  {
			$strOut = '';
			$fd = fopen ($this->post['tmp_name'], "rb");
			while (!feof( $fd) ) {
				$strOut .= fread ($fd, 1024);
			}
			fclose ($fd);
		}
		return $strOut ;
	}
	function extension() {
		/* devuelve la extensión del archivo */
		return substr( strtolower( $this->post['name']),  - ( strlen( $this->post['name'] ) - strrpos( $this->post['name'] , '.') - 1));
	}



}


?>