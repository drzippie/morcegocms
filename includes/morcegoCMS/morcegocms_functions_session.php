<?php
class morcegocms_functions_session {
    /**
    * Establece (set) o devuelve (get) el valor de una variable de sesión
    */
     function action( $cadena ) {
        $aCadena = explode(":", $cadena);
        $element =& $aCadena[1] ;
        
        switch ( $element ) {
            case 'get':
                if (!isset($aCadena[3] ) ) {
			$valor = '' ;
		} else {
			$valor = $aCadena[3] ;
		}
		return ( isset( $_SESSION['runtime'][ $aCadena[2] ]))?  $_SESSION['runtime'][ $aCadena[2] ] :  $valor ;
            case 'set':
		if (!isset($aCadena[3] ) ) {
			$valor = '' ;
		} else {
			$valor = $aCadena[3] ;
		}
		 $_SESSION['runtime'][ $aCadena[2]] = $valor ;
                return '';
        }
	return '';
   }
    

}

?>