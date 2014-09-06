<?php

class  morcegocms_functions_parameter {
    /**
    *  Nos devuelve el texto de un parámetro
    */
    function action( $cadena ) {
    
        $aCadena = explode(":", $cadena);
        $numParametro = (integer) $aCadena[1] ;
        $defaultValue = (isset( $aCadena[2] )) ?  $aCadena[2]  : '' ;
        return  (isset( $GLOBALS['statsCMS']->parametros[ $numParametro ])) ?  
            $GLOBALS['statsCMS']->parametros[ $numParametro ] :
            $defaultValue ;
     
     }


}


?>