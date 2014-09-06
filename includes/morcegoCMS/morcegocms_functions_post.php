<?php
class morcegocms_functions_post  {
    /**
    *  Nos devuelve contenido de una variable enviada por POST
    */
     function action( $cadena ) {
        $aCadena = explode(":", $cadena);
        $element =& $aCadena[1] ;
        $value = (isset( $_POST[ $element ])) ?  
            $_POST[$element ] :
            '';
         $value = str_replace( array('{', '}'), array('%!-%' , '%-!%') ,  $value ) ;
        return $value ;
    }
}

?>