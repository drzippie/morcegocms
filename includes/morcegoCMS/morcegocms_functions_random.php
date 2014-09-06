<?php
class morcegocms_functions_random{

	function action( $cadena ) {
		$aCadena = explode(":", $cadena);
		$element =& $aCadena[1] ;
		switch ( $element ) {
			case 'get':
			
			        if (!isset($aCadena[3] ) ) {
					$valueMax = 1000 ;
				} else {
					$valueMax = (int)  $aCadena[3] ;
				}
				
				if (!isset($aCadena[2] ) ) {
					$valueMin = 1 ;
				} else {
					$valueMin = (int) $aCadena[2] ;
				}
				
				return rand( $valueMin, $valueMax ) ;
				
			break ;

		}
	
		return '';
	}
}
?>