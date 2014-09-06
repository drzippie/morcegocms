<?php
class morcegocms_functions_captcha {

	function action( $cadena ) {
	$aCadena = explode(":", $cadena);
        $element =& $aCadena[1] ;
        switch ( $element ) {
		case 'set':
			if (!isset($aCadena[2] ) ) {
				$valor = 0 ;
			} else {
				$valor = $aCadena[2] ;
			}
			$_SESSION['runtime']['captcha'] = $valor ;
			$diferencia = rand( 1 , 5) ;
			$valor = $valor + $diferencia ;
			$formula  = ( $valor ) . ' - ' . $diferencia  ;
			return $formula ;
			break;
		case 'get': 
			break;
	
	}
	return '';
	}


}
?>