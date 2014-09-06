<?php 
include  dirname( __FILE__ ) . '/../includes/core_admin.php';
global $oUser; if ( !$oUser->isAdmin() ) die( 'Debe estar logueado como Administrador para poder gestionar esta web');
include_once  dirname( __FILE__ ) . '/drzippie-rs.php';

   class procesos_admin extends drzippie_rs {
        function idpagina_exists( $parameters)  {
   	    global $DB, $DB_prefijo;
            $idpagina = $_POST['nuevo'];
            $comando_sql ="select count(*) as total from {$DB_prefijo}paginas where idpagina = \"{$idpagina}\" ";
            $recordset = $DB->execute( $comando_sql ) ;
            return $recordset->fields['total'];
        }
        function idtemplate_exists( $parameters)  {
            global $DB, $DB_prefijo;
	    $metodo = $_POST['metodo'];
            $idtemplate = $_POST['idtemplate'];
            $comando_sql ="select count(*) as total from {$DB_prefijo}templates where idtemplate= \"{$idtemplate}\" ";
            $recordset = $DB->execute( $comando_sql ) ;
            return ($metodo == 'continue' &&  $recordset->fields['total'] == 0 ) ? -1 : $recordset->fields['total'];
        }
	function mostrar_archivo( $parameters ) {
		$idfichero = base64_decode( $parameters[0] );
		include_once( dirname(__FILE__) . '/../../includes/morcegoCMS/morcegocms_functions_fichero.php' );
		return morcegocms_functions_fichero::url( $idfichero )  ;
	}
	function ficheromasinfo( $parameters)  {
            global $DB, $DB_prefijo;
            $idfichero = $_POST['cadena'];
            $comando_sql ="select original_file, size, description, date, iduser from {$DB_prefijo}files where idfile= \"" . base64_decode($idfichero) ."\" ";
            $recordset = $DB->execute( $comando_sql ) ;
	    $resultado = '';
	    $resultado .= '<strong>Descripción:</strong> ' . $recordset->fields['description'] . '<br />'  . 
	  '<strong>Nombre Original:</strong> ' . $recordset->fields['original_file'] . '<br />'  . 
		'<strong>Tamaño:</strong> ' . round($recordset->fields['size'] /1024 , 2) . ' KB'  . '<br />'. 
	  	  '<strong>Fecha de Modificación:</strong> ' . $recordset->fields['date']    . '<br />'  
	  ;
	  return implode('~', array( $idfichero, urlencode(  $resultado )));

	//   return $resultado ;
	// return ($parameters[2] == 'continue' &&  $recordset->fields['total'] == 0 ) ? -1 : $recordset->fields['total'];
        }

        function switch_pagina( $parameters ) {
            global $DB, $DB_prefijo;
            $uid = $_POST['uid'];
            $comando_sql ="select activa from {$DB_prefijo}paginas where uid = \"{$uid}\"";
            $recordset = $DB->execute( $comando_sql ) ;
            $activa = ($recordset->fields['activa']  == 1) ? 0 : 1 ;
            $DB->Replace( $DB_prefijo . 'paginas ' , 
                array( 
                'uid' => $uid,
                'activa' => $activa ),
                'uid',
                true  );
            return implode('~', array( "$activa", "$uid"));
        }
        function templates4tipo($parameters){
        
            global $DB, $DB_prefijo;
	    global $_POST;
            switch ( $_POST['tipo']) {
                case 0 :
                    $prefijo = 'template_';
                    break;
                case 1 :
                    $prefijo = 'content_';
                    break;
                default :
                    $prefijo = 'template_';                    break;    

            }
            $comando_sql ="select idtemplate from {$DB_prefijo}templates where idtemplate like \"{$prefijo}%\" ";
            $recordset = $DB->execute( $comando_sql );
            $resultado = array();
            while( !$recordset->EOF ) {
                $resultado[count( $resultado) ] = $recordset->fields['idtemplate'] ;
                $recordset->movenext();
            }
            return implode('~', $resultado ) ;
        }    
        function borrar_cache( $parameters ) {
            global $varsCMS ;
            $cache_path = dirname( __FILE__). '/../../' . $varsCMS->path_repository  ;
            $hd=opendir($cache_path );
            $i = 0;
            while ($file = readdir($hd)) {
                if ( substr( $file, 0, 6) == 'cache.') {
                    unlink( $cache_path . '/'. $file );
                    $i++;
                   // echo "*";
                } 
            }
            closedir($hd); 
            $GLOBALS['DB']->execute( "delete from " . $GLOBALS['configCMS']->get_var('dbprefijo') ."objects " );
            morcegocms_utils::log( "INFO;CACHE;Se ha borrado la cache ({$i} Elementos eliminados)" );
            return urlencode( "El caché ha sido eliminado.\n [{$i}] Ficheros han sido eliminados." );
        }
        function change_conf( $parameters)  {
            global $DB, $DB_prefijo;
            $conf_key = $_POST['key'];
            $conf_value = $_POST['value'];
            
            if ( $conf_key == 'includes') {
                $aIncludes = explode("\n", $conf_value );
                $includes = '';
                foreach ($aIncludes as $fichero) {
                    $fichero = str_replace("\r", '', $fichero );
                    $fichero = str_replace("\n", '', $fichero );            
                    if (!empty($fichero)){
                        $includes .= "{$fichero};";
                    }
                }
                $conf_value =$includes;
            }
            
            $DB->Replace($DB_prefijo . 'config' , 
                array( 
                'idconfig' =>  $conf_key ,
                'configvalue' => $conf_value ,
                'iduser' => $_SESSION['iduser'],
                'date' => $DB->DBTimeStamp( time() ) )  ,
                'idconfig',
                true    );
            $DB->execute( 'delete from ' . $DB_prefijo . 'objects where idobject="config"' );
            return 'Valor Modificado';
        }
        function template_info( $parameters ) {
            // global $varsCMS ;
            $comando_sql = "select descripcion from {$GLOBALS['DB_prefijo']}templates where idtemplate=\"{$parameters[0]}\"";
            $recordset = $GLOBALS['DB']->execute( $comando_sql );
            if (!isset( $recordset->fields['descripcion']) || empty( $recordset->fields['descripcion'] )) {
                $str_out = 'La plantilla especificada no dispone de una descripción';
            } else {
                $str_out = $recordset->fields['descripcion'];
            }
            return $str_out;
        }




        
}
    
    


    $oRS = new procesos_admin( array( 
        "templates4tipo",
        "idpagina_exists",
        "switch_pagina",
        "borrar_cache",
        "idtemplate_exists",
        "change_conf",
        'template_info',
	'ficheromasinfo',
	'mostrar_fichero'
        ));
    $oRS->action();



    
?>