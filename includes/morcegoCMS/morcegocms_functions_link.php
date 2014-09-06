<?php
class morcegocms_functions_link {

  function action( $cadena ) {
    $aCadena = explode( ':', $cadena );
    /*
    {link:idpagina o url|texto|clase_css}
    */
    $parametros = implode(':', array_slice( $aCadena, 1));
    // dividimos ahora los parametros en sus componentes:
    $aLink = explode('|', $parametros ) ;
    /*
    0 -> url o idpagina
    1 -> texto
    2 -> clase css
    */
    $clase_css = (!isset( $aLink[2] )) ? 'link' : $aLink[2] ;
    $texto = (!isset( $aLink[1] )) ? '' : $aLink[1] ;
    if (!isset( $aLink[0] ) && empty($aLink[0]) ) {
        $resultado = '';
    } else {
        $enlace =& $aLink[0];
        if ( substr( strtolower( $enlace), 0, 7 ) == 'http://' ) {
            if ( empty( $texto )) {
                $texto = substr( $enlace, 7 );
            }
            $resultado = "<a href=\"{$enlace}\" title=\"{$texto}\" target=\"_blank\" class=\"{$clase_css}\">{$texto}</a>";
        } else {
            /* 
                Tratamos de determinar si es un idpagina válido
            */
            $aIdpagina = explode( '/', $enlace );
            $idpagina =& $aIdpagina[0] ;
            $titulopagina = morcegocms_utils::titulofromidpagina($idpagina); 
            if ( empty( $titulopagina ) ) {
                // no es una página válida !
                $texto = (empty($texto )) ? $enlace : $texto ;
                $resultado = "<a href=\"{$enlace}\" title=\"{$texto}\" class=\"{$clase_css}\">{$texto}</a>";
            } else {
                $texto = (empty($texto )) ? $titulopagina : $texto ;
                $prefijourl = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ? $GLOBALS['configCMS']->get_var('rutaweb') : $GLOBALS['configCMS']->get_var('rutaweb') .  '?';
                $enlace = $prefijourl . $enlace . pagina::url_vars() ;
                $resultado = "<a href=\"{$enlace}\" title=\"{$texto}\" class=\"{$clase_css}\">{$texto}</a>";
            }
        }
    }

    //                 $resultado = "*{$parametros}*";
    
    
      return $resultado ;
  }
}
?>