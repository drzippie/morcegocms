<?php
class morcegocms_functions_get  {
    /**
    *  Nos devuelve contenido de una variable enviada por POST
    */
     function action( $cadena ) {
        $aCadena = explode(":", $cadena);
        $element =& $aCadena[1] ;
        $value = (isset( $_GET[ $element ])) ?  
            $_GET[$element ] :
            '';
         $value = str_replace( array('{', '}'), array('%!-%' , '%-!%') ,  $value ) ;
        return $value ;
    }
}

?>