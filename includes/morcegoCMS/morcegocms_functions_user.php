<?php
class morcegocms_functions_user {
    /**
    * Nos devuelve una propiedad del usuario actual
    * propiedades posibles:
    * username: login del usuario
    * name: Nombre completo del usuario
    * idgroup: nivel del usuario
    */
     function action( $cadena ) {
        $aCadena = explode(":", $cadena);
        $element =& $aCadena[1] ;
        
        switch ( $element ) {
            case 'username':
                return ( isset( $_SESSION['username']))?  $_SESSION['username'] : '';
            case 'idgroup':
                return ( isset( $_SESSION['idgroup']))?  $_SESSION['idgroup'] : '-1';            
            case 'name':
                return ( isset( $_SESSION['name']))?  $_SESSION['name'] : '';            
            default:
                return '';
        }
    }

}

?>