<?php

class morcegocms_functions_fichero {
    function url($idfile){
     global $pagina;
     $str_out = '';
     if (empty($idfile)){
         morcegocms_utils::log('ERROR;url_fichero;el fichero con ID ' . $idfile . ' no existe' );
         return '';
         }
    if ( isset( $GLOBALS['configCMS']->variables['files'][$idfile] )) {
       return $GLOBALS['configCMS']->variables['files'][$idfile] ;
     }
     $comando_sql = "select original_file from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}'" ;
     $recordset = $GLOBALS['DB']->execute($comando_sql) ;
     if (isset($recordset -> fields['original_file'])){
         $filename = $recordset -> fields['original_file'];
         $extension = substr(strtolower($filename), - (strlen($filename) - strrpos($filename , '.') - 1));

         $nombre_fichero = '/cache.fichero.' . $idfile . '.' . $extension;
        $path_fichero = dirname(__FILE__) . '/../../' . $GLOBALS['varsCMS'] -> path_repository . $nombre_fichero ;
        $url_fichero = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ?  
            $GLOBALS['configCMS']->get_var('rutaweb') .  'ficheros/'. $idfile . '.' . $extension  :
            $GLOBALS['configCMS']->get_var('rutaweb') .  $GLOBALS['varsCMS'] -> path_repository . $nombre_fichero ;
        if (!file_exists($path_fichero)){
             $comando_sql = "select content from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}' ";
             $recordset = $GLOBALS['DB']->execute($comando_sql);
             $content = $recordset -> fields['content'];
             $hf = fopen($path_fichero, 'w') ;
             fwrite($hf, $content);
             fclose($hf) ;
             }
             
             
             
         $str_out = $url_fichero;
         }
     $GLOBALS['configCMS']->variables['files'][$idfile] = $str_out ;
     return $str_out ;
    }
  function path( $idfile  ) {
       global $pagina;
       $str_out = '';
       if (empty($idfile)){
           return '';
           }
       $comando_sql = "select original_file from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}'" ;
       $recordset = $GLOBALS['DB']->execute($comando_sql) ;
       if (isset($recordset -> fields['original_file'])){
          $filename = $recordset -> fields['original_file'];
          $extension = substr(strtolower($filename), - (strlen($filename) - strrpos($filename , '.') - 1));
          $nombre_fichero = '/cache.fichero.' . $idfile . '.' . $extension;
          $path_fichero = dirname(__FILE__) . '/../../' . $GLOBALS['varsCMS'] -> path_repository . $nombre_fichero ;
          if (!file_exists($path_fichero)){
               $comando_sql = "select content from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}' ";
               $recordset = $GLOBALS['DB']->execute($comando_sql);
               $content = $recordset -> fields['content'];
               $hf = fopen($path_fichero, 'w') ;
               fwrite($hf, $content);
               fclose($hf) ;
               }
           $str_out = $path_fichero;
           }
       return $str_out ;
    }
   function tag( $parametros) {
	/**
	* Parametros :
	*  idfichero:{descripcion}
	*
	*
	*/ 
	$aParametros = explode( ':', $parametros ) ;
	$idfile = $aParametros[0];
	if ( empty( $idfile )) {
		return '';
	}

	$comando_sql = "select description, mimetype from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}'" ;
        $recordset = $GLOBALS['DB']->execute($comando_sql)  ;
        if  (isset($recordset -> fields['description'])){
		// $descripcion = ( isset( $aParametros
		if ( isset( $aParametros[1] )) {
			$descripcion = implode(':', array_slice( $aParametros, 1));
		} else {
			$descripcion = $recordset -> fields['description'] ;
		}
		if ( in_array( $recordset->fields['mimetype'] ,  $GLOBALS['varsCMS']->mimetypes_images  )) {
			return '<img src="' . morcegocms_functions_fichero::url( $idfile ) . '" alt="' . $descripcion . '"/>';
		} else {
			return '<a href="' . morcegocms_functions_fichero::url( $idfile ) . '">' . $descripcion . '<a/>';
		}
	} else {
		return '';
	}
    }
       
}

?>