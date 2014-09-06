<?php
/**
* 
* @package Core
* @author Antonio Cortés <zippie@dr-zippie.net> 
* @copyright Copyright &copy; 2003-2006 Antonio Cortés
* @version 1.0 
* @license BSD
*/
class morcegocms_index {
   function main() {
        ob_start();
        header('P3P: CP="NOI NID ADMa OUR IND UNI COM NAV"');
        global $uid;
        global $_SERVER;
        global $t;
        global $pagina;
        $funcion = $GLOBALS['configCMS']->get_var('prefijofunciones') . 'init' ; 
        if (function_exists( $funcion ) ) {
            $funcion();
        }
        /* Creamos ahora el objeto que contendra todos los valores de la página*/
        if ( $GLOBALS['statsCMS']->parametros[0] == '404' ) {
            $pagina = new pagina ('404', false, false, true );
            /* no tenemos una pagina con idpagina = 404 ... que es la de error 404 */
            if ( $pagina->ok == false ) {
	    
                die( 'Page not found');
            }
        } else  {
            $pagina = new pagina ( $GLOBALS['statsCMS']->parametros[0], true, false, true );
        }
        if ( $pagina->ok == false  ) {
               /* No se ha encontrado */

            unset( $pagina ) ;
            $ToUrl =  (( $GLOBALS['configCMS']->get_var('mod_rewrite') == 'true') ? $GLOBALS['configCMS']->get_var('rutaweb')  : $GLOBALS['configCMS']->get_var('rutaweb') . '?' ) .
                '404' . '/'  .
                 implode('/', $GLOBALS['statsCMS']->parametros );
            Header   ( "Location: {$ToUrl}");
            die();

        } elseif ( $pagina->Campos['idpagina'] != '404' ) {
            // eliminadas las estadísticas
        }
        // establecemos en la variable uid el uid de la página actual
        
        $uid = $pagina->Campos['uid'];
        $t = new Template_morcegoCMS( $pagina, 
            $GLOBALS['configCMS']->get_var('cachetimming'));
       if ( !$GLOBALS['oUser']->islogged() ) {
//             $GLOBALS['oUser']->logout_user();
        }

        $t->mostrar();
        ob_end_flush();
    }
}




/**
* Gestionará los tiempos de vida del caché su lectura y grabación.
* 
* @package Core
* @author Antonio Cortés <zippie@dr-zippie.net> 
* @copyright Copyright &copy; 2003-2006 Antonio Cortés
* @version 1.0 
* @license BSD
*/
class cls_cache{
    /**
    * Nos devuelve para un identificador de caché el tiempo que una página lleva cacheada
    * 
    * @parameter string $idcache identificador de página creada
    * @return integer Tiempo en segundos de la página en caché.
    */
    function date_cache($idcache){
        $resultado = 99999;
        $last_modified = ( file_exists( $idcache )) ? filemtime($idcache) : -1 ; 
        return ($last_modified == -1 ) ? 99999 : time() - $last_modified ;
    }
    /**
    * Nos devuelve para un identificador de caché el mimetype
    * 
    * @parameter string $idcache identificador de página creada
    * @return string mimetype.
    */
    function mimetype_cache($idcache){
        $resultado = 'text/html';
        return $resultado;
    }
    /**
    * Guarda en cache una página
    * 
    * @parameter string $idcache identificador de pagina caché
    * @parameter string $content contenido (texto) de la página
    * @parameter string $mimetype mimetype de la página
    * @parameter boolean $compress indica si será comprimido o no en la caché
    */
    function save_cache($idcache, $content, $mimetype = 'text/html', $compress = true){
        $hf = fopen($idcache, 'w') ;
        fwrite($hf, $content);
        fclose($hf);
    }
    /**
    * Nos devuelve el contenido de una página cacheada
    * 
    * @parameter string $idcache identificador de pagina caché
    * @return string texto de la página recuperada del caché
    */
    function read_cache($idcache){
        $resultado = implode("\n", file($idcache)) ;
        return $resultado;
    }
    /**
    * Nos muestra el contenido de una página cacheada y finaliza el script
    * 
    * @parameter string $idcache identificador de pagina caché
    */
    function render_cache($idcache){
        die($this -> read_cache($idcache));
    }
}



?>