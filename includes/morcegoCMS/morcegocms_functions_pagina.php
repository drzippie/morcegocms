<?php

class morcegocms_functions_pagina extends morcegocms_common {
  var $objpagina ;
  function morcegocms_functions_pagina( &$obj ) {
    $this->objpagina =& $obj->objpagina;
    
  }
  function init( &$oPagina ) {
    // si es otra pagina cambiamos el obj pagina
    
    if  ( $oPagina->objpagina->idpagina != $this->objpagina->idpagina ) {
        $this->objpagina =& $oPagina->objpagina ;
    }
    
  }
  
  function action( $cadena ) {
    $aCadena = explode(":", $cadena);
    return (isset($this -> objpagina->Campos[$aCadena[1]]))? $this -> objpagina->Campos[$aCadena[1]] : '';
    
  
  }
  function num_pages( $cadena ) {
    return  $this->objpagina->num_pages();
  }
  function num_contents( $cadena ) {
    return $this->objpagina->num_contents();
  }
  
  function meta( $cadena )  {
      $aCadena = explode(":", $cadena);  
      return (isset($aCadena[0]) && isset($this -> objpagina->metadata[$aCadena[0]])) 
        ? $this -> objpagina->metadata[$aCadena[0]] 
        : '';
  }
  function variable( $cadena ) {
      $aCadena = explode(":", $cadena);  
      return  (isset($aCadena[0]) && isset($this -> objpagina-> Campos['variable'][$aCadena[0]])) 
        ? $this -> objpagina->Campos['variable'][$aCadena[0]] 
        : '';
  }
  
  function parent( $cadena )  {
      $aCadena = explode(":", $cadena);  
      return $this->objpagina->get_parent( $aCadena[0], (isset( $aCadena[1])) ? $aCadena[1] : '');
  }
  function root( $cadena ) {
    $aCadena = explode(":", $cadena);  
    return $this->objpagina->get_root( $aCadena[0], (isset( $aCadena[1])) ? $aCadena[1] : ''    );
  }
  function index( $cadena) {
      $aCadena = explode(":", $cadena);  
      return $this->objpagina->get_index( $aCadena[0], (isset( $aCadena[1])) ? $aCadena[1] : '');
  }  
  
  function related( $cadena) {
      $aCadena = explode(":", $cadena);  
      return $this->objpagina->get_related( $aCadena[0], $aCadena[1], (isset( $aCadena[2])) ? $aCadena[2] : '');
  }  
  function fecha( $cadena  ) {
      
      /**
      * nos muestra la fecha de la página con un determinado formato
      * este formato es el de la funcion date() de php, como 3er parametro
      */
                
        $formato = ( !empty( $cadena ) ) ?  $cadena  : 'd/m/Y';
	return  date( $formato , $this->objpagina->Campos['fecha']  );
  }
  
  function encoded( $cadena )  {
    $aCadena = explode(":", $cadena);
     return htmlentities( 
        substr( strip_tags( 
              str_replace( array( '{', '}'), array('<', '/>'),  
              $this->objpagina->Campos[$aCadena[0]] )) , 0, ((isset($aCadena[1])) ? $aCadena[1] : 254 )) );
  }
  function raw( $cadena ) {
    $aCadena = explode(":", $cadena);  
      return  htmlentities( 
        strtr(    substr( strip_tags( 
            str_replace( array( '{', '}'), array('<', '/>'),  
            $this->objpagina->Campos[$aCadena[0]] )) , 0, ((isset($aCadena[1])) ? $aCadena[1] : 254 ))
                , "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ",
                  "AAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy")
                 );

  }
  function length( $cadena ) {
  	   $aCadena = explode(":", $cadena);
  	   $valor = $this->objpagina->Campos[$aCadena[0]]  ;
  	   return  "" . strlen($valor )  ;
	  
	  } 
  function thumbnail( $cadena ) {
      $aCadena = explode( ':', $cadena ) ;
      $ancho =& $aCadena[0];
      $alto =& $aCadena[1];
      $fondo = (isset( $aCadena[2]) )? $aCadena[2] : 'ffffff';
      $url_thumb = '';
   //    return $cadena . '-'. $this->pagina['idpagina']  ;
      
      if (!empty($this -> objpagina->Campos['img_mimetype'])){
        
        $nombre_imagen = '/cache.imagen.' . 
          $this -> objpagina->Campos ['idpagina'] . '.' . 
          $GLOBALS['varsCMS'] -> extension_from_mimetype($this -> objpagina -> Campos['img_mimetype']);
        $path_imagen = dirname(__FILE__) . '/../../' . $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
        $url_imagen = $GLOBALS['configCMS']->get_var('rutaweb') .  
          $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
        // creamos la imagen si no existe
        if (!file_exists($path_imagen)){
          $comando_sql = "select img_content from {$GLOBALS['DB_prefijo']}paginas where ".
            "idpagina = '{$this->objpagina->Campos['idpagina']}' ";
          $recordset = $GLOBALS['DB'] -> execute($comando_sql);
          $content = $recordset -> fields['img_content'];
          $hf = fopen($path_imagen, 'w') ;
          fwrite($hf, $content);
          fclose($hf) ;
        }
        
        // ahora comprobamos que no existe el thumbnail ... y siendo así lo crearemos :::: Siempre PNG!
        
        $nombre_thumb = '/cache.imagen.' .
          $this ->  objpagina -> Campos['idpagina'] . '.'  . 'thumb.' . $ancho . 'x' . $alto .  '.jpg' ;
        $path_thumb = dirname(__FILE__) . '/../../' . $GLOBALS['varsCMS'] -> path_repository . $nombre_thumb ;
        $url_thumb = $GLOBALS['configCMS']->get_var('rutaweb') .  
          $GLOBALS['varsCMS'] -> path_repository . $nombre_thumb ;
        if (!file_exists($path_thumb)){
          $aInfo = getimagesize( $path_imagen );
          $oAncho = $aInfo[0] ;
          $oAlto = $aInfo[1] ;
          switch ( $aInfo[2] ) {
            case 1 :
              $funcion = 'imagecreatefromgif' ;
              break;
            case 2 :
              $funcion = 'imagecreatefromjpeg' ;
              break;
            case 3 : 
              $funcion = 'imagecreatefrompng' ;
              break;
            default:
              return '';
              
          }
          
          $oim = $funcion( $path_imagen );
          
          
          $zoom = (($ancho/$oAncho) < ($alto/$oAlto) ) ?
            $ancho/$oAncho   :
            $alto/$oAlto ;
          
          
          if ( $zoom > 1 ) { 
            $zoom = 1 ;
          }
   
          
          $posX = ($ancho - ( $oAncho * $zoom )) / 2;
          $posY = ($alto  - ( $oAlto  * $zoom )) / 2;
          if ( $GLOBALS['configCMS']->get_var('GD2') === 'true' )  {
            $imagecreate = 'imagecreatetruecolor';
          } else {
            $imagecreate = 'imagecreate';
          }
          
          $im = $imagecreate($ancho, $alto );
          // colores !
          $bgcolor =ImageColorAllocate($im,
                hexdec(substr($fondo, 0, 2)),
                hexdec(substr($fondo, 2, 2)),
                hexdec(substr($fondo, 4, 2)));
          imageFill($im, 0, 0, $bgcolor);
          if ( $GLOBALS['configCMS']->get_var('GD2') === 'true' )  {
            $funcionimage = 'imageCopyResampled';
          } else {
            $funcionimage = 'imageCopyResized';
          }

          
          $funcionimage($im, $oim,  $posX, $posY, 0,0, ( $oAncho * $zoom), ( $oAlto * $zoom ), $oAncho, $oAlto);
          imageJPEG($im, $path_thumb);
        }
        $url_thumb = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ? 
                $GLOBALS['configCMS']->get_var('rutaweb') . 'img/' . 
                  
                  $this -> objpagina->idpagina . 
                  '.thumb.' . $ancho . 'x' . $alto . 
                  
                  '.jpg' 
                : $url_thumb;
        
    }
    
    return $url_thumb ;
    
  }
  
}


?>