<?php 
class  drzippie_rs {
    var $allowed_functions = array();
    var $method ;
    var $parameters ;
    function drzippie_rs( $allowed_functions = array())  {
        $this->allowed_functions = $allowed_functions;
        $this->method = (isset($_POST['accion']) ? $_POST['accion'] : "-1");
        $this->parameters = (isset($_POST['P']) ? $_POST['P'] : array() );
    }
    function action() {
        
        if( method_exists( $this, $this->method )) {
            $result = $this->{$this->method}( $this->parameters );
            $error = false ;
        } else {
            $result =  $this->method . " : La funcion indicada no existe  ";
            $error = true;
        }
        if ($error) {
            $this->return_error(  $result  ) ;
        } else {
            $this->return_ok( $result );
        }
    }
    function return_ok( $string ) {
        echo '0:' .  $string  ;
        exit();
    }
    function return_error( $string , $error = 1)  {
        
        echo rawurlencode( "{$error}:{$string}" )  ;
        
        exit();
    }
    function escape_string( $string ) {
        return ereg_replace( "\/" , "\\/",ereg_replace( "&", "&amp;", $string ));     
    }
}
?>