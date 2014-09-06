<?php

class morcegocms_functions_global  {
  function action( $cadena ) {
        $cadena = stripslashes( $cadena );
        $aCadena = explode(":", $cadena);
        if ( defined( '_DO_NOT_PROCESS_GLOBALS' )  ) {
		$resultado = '¿-=-'  . $cadena .  '-=-?' ;
	} else {
		$resultado = '{pagina:index:variable:' . $aCadena[1] . '}' ;
	}
	return $resultado  ;
    }

}
?>