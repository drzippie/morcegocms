<?php 

class  adm_export  extends adm_class{

    function adm_export(  $html  ) {
        $this->oHtml  = $html ;
        global $DB, $DB_prefijo, $aArgumentos ;    
            if (!isset( $aArgumentos[1] )) {
                $aArgumentos[1] = '' ;
            }
            switch ($aArgumentos[1]){
                case 'export':
                    $this->export();
                    break;
                case 'import':
                    $this->import();
                    break;
                case 'out':
                    $this->export_out();
                    break;
                case 'file':
                    $this->fichero();
                    break;
                case 'import_ok':
                    $this->import_ok();
                    break;
                case 'pagina':
                    $this->paginaMenu();
                    break;
                case 'export_data':
                    $this->export_data();
                    break;                    
                case 'del_file': 
                  $this->del_file( $aArgumentos[2]);
                  break;
                case 'import_file': 
                  $this->import_file( $aArgumentos[2]);
                  break;                  
                default:
                  $this->menu();
            }
    }
   
    function menu() {
      $html = new htmlcontainer();
      $html->add('br');
      $form =& $html ->add('form', array( 
        'method' => 'post',
        'action' => 'admin.php?q=export/export_data',
        'name' => 'export'
        
        ) ) ;
      
      $form->add( 'label', array(
        'for' => 'fichero' ) , 'Fichero de salida:'  );
      $form->add('input', array( 
        'name' => 'fichero',
        'type' => 'text' ));
      $form->add('input', array(
        'type' => 'submit',
        'name' => 'enviar',
        'value' => 'Exportar'));
      
      
      $html->add('br');
      $table =& $html->add( 'table', array(
        'class' => 'ruler',
        'width' => '700',
        'cellspacing' => 0,
        'cellpadding' => 2 
        )) ;
  
      $tr =& $table->add('tr');
        $tr->add('th', '', 'Ficheros existentes: ' );
        $tr->add('th', '', 'Tamaño bytes: ' );
        $tr->add('th', '', 'Fecha: ' );
        $tr->add('th', '', '&nbsp;' );
      
      $dir = dirname( __FILE__) . '/../../lar/';
      if (is_dir($dir)) {
         if ($dh = opendir($dir)) {
             while (($file = readdir($dh)) !== false) {
                 
                if ( array_pop( explode( '.', $file) ) == 'dat') {
                  $tr =& $table->add('tr');
                    
                    
                    $td =& $tr->add('td');
                      $td->add( 'a', array( 'href' => '../lar/' . $file,
                      'title' => 'Descargar fichero' ),
                    $file  );
                  $tr->add('td' , '', filesize( $dir . $file) );
                  $tr->add('td', '', date("d-m-Y H:i:s.", filectime($dir . $file ))) ;
                  $td =& $tr->add( 'td');            
                  $td->add( 
			CustomHTML::botonAdmin("Importar" , 'Importar Fichero', '#', 
				"confirmar('¿ Desea Importar el fichero ? Todos los datos actuales serán sustituidos por los del fichero ', 
				'admin.php?q=export/import_file/{$file}')"));

                  
                  
                  $td->add(CustomHTML::botonAdmin("Borrar" , 'Borrar Fichero', '#', 
			"confirmar('¿ Desea borrar el fichero seleccionado ?', 'admin.php?q=export/del_file/{$file}')"));

                }
             }
             closedir($dh);
         }
      }
      $this->render( $html, 'Importación / Exportación' ) ;
   
    }
    function import_file( $file ) {
      $html = new htmlcontainer() ;
      $dir = dirname( __FILE__ ) . '/../../lar/' ;
      if ( empty( $file ) && !file_exists( $dir . $file )) {
        $html->add_object( CustomHTML::DialogBox( _DIALOG_QUESTION , 'Herramienta de backup ', 'Error!, no se ha indicado un nombre de fichero válido' ) );
      } else {
        
        /*
        Abrimos el fichero ...
        */
        
        $elementos  = new MorcegoCMS_Archiver( 'read', $dir . $file  )  ;
    
        /* 
        Determinamos que registros tiene y vaciamos las tablas
        */ 
        
        // añadimos el caché de objetos
        $tablas = array('objects');
        foreach ( $elementos->files as $elemento ) {
          // determinamos la tabla por el comienzo del nombre
          $aElemento = explode('.', $elemento) ;
          $tipo =& $aElemento[0] ;
          switch ( $tipo ) {
            case 'pagina':
              $table = 'paginas';
              break;
            case 'boton':
              $table = 'botones';
              break;
            case 'config':
              $table = 'config';
              break;
            case 'file':
              $table = 'files';
              break;
            case 'template':
              $table = 'templates';
              break;
            case 'user':
              $table = 'users';
              break;
          }
          
          if ( in_array( $table, $tablas ) == false  && !empty( $table )) {
            $tablas[] = $table ;
          }
        }
        /*
        vaciamos las tablas
        */
        foreach ( $tablas as $table ) {
          $comando_sql = "delete from {$GLOBALS['DB_prefijo']}{$table}";
          $GLOBALS['DB']->execute( $comando_sql );
          
        }
        /*
        ahora importamos los valores ...
        */
        foreach ( $elementos->files as $elemento ) {
          // determinamos la tabla por el comienzo del nombre
        
          $aElemento = explode('.', $elemento) ;
          $tipo =& $aElemento[0] ;
          
          $table = '-' ;
          switch ( $tipo ) {
            case 'pagina':
              $table = 'paginas';
              break;
            case 'boton':
              $table = 'botones';
              break;
            case 'config':
              $table = 'config';
              break;
            case 'file':
              $table = 'files';
              break;
            case 'template':
              $table = 'templates';
              break;
            case 'user':
              $table = 'users';
              break;
          }
          $data = unserialize( $elementos->get_file( $elemento ) );
          $camposSQL = '' ;
          $valoresSQL = '' ;
          foreach ( $data as $campo => $valor ) {
            $valor = addslashes( $valor ) ;
            $camposSQL  .= (empty($camposSQL ) ) ?  $campo  : ", {$campo}" ;
            $valoresSQL .= (empty($valoresSQL ) ) ?  "\"{$valor}\""  : ", \"{$valor}\"" ;
          }
          $comandoSQL = "insert into {$GLOBALS['DB_prefijo']}{$table} ({$camposSQL}) values ($valoresSQL)";
          $GLOBALS['DB']->execute(  $comandoSQL );
        }
        // borramos el cache 
        morcegocms_utils::EmptyCacheObjects();
        header( 'Location: ./admin.php?q=export' );
        die();
      }
      
      
      
      
      
      $this->render( $html, 'Exportación de datos') ;
    
    
    
    
    }
    
    
    
    function del_file( $file ) {
      $html = new htmlcontainer() ;
      
      if ( empty( $file )) {
        $html->add_object( CustomHTML::DialogBox( _DIALOG_QUESTION , 'Herramienta de backup ', 'Error!, no se ha indicado un nombre de fichero válido' ) );
      } else {
      
          $dir = dirname( __FILE__ ) . '/../../lar/' ;
          unlink( $dir . $file );
          header( 'Location: ./admin.php?q=export' );
          die();
      }
      
      
      
      $this->render( $html, 'Exportación de datos');
      
       
    
    }
    
    
    function export_data() {
      $html = new htmlcontainer() ;
      
      
      $file = $_POST['fichero'];
      
      if ( strpos( $file, '/' ) === false ) {
         // 
     } else  {
        $file = array_pop( explode( '/', $file )) ;
      }
      
      
      
      if ( empty( $file )) {
        $html->add_object( CustomHTML::DialogBox( _DIALOG_QUESTION , 'Herramienta de backup ', 'Error!, no se ha indicado un nombre de fichero válido' ) );
      } else {
        $ficheroExport = new MorcegoCMS_Archiver( 'write', dirname(__FILE__) . '/../../lar/'. $file . '.dat' );
        
        // paginas ... 
        $comando_sql = "select * from {$GLOBALS['DB_prefijo']}paginas order by uid" ;
        $recordset = $GLOBALS['DB']->execute( $comando_sql  ) ;
        while (!$recordset->EOF ) {
            $fichero = 'pagina.' . $recordset->fields['uid'] ;
            $ficheroExport->add_file( $fichero, serialize( $recordset->fields ) );
            $recordset->MoveNext();
        }
        // botones
        $comando_sql = "select * from {$GLOBALS['DB_prefijo']}botones " ;
        $recordset = $GLOBALS['DB']->execute( $comando_sql  ) ;
        while (!$recordset->EOF ) {
            $fichero = 'boton.' . $recordset->fields['idboton'] ;
            $ficheroExport->add_file( $fichero,  serialize( $recordset->fields ) );
            $recordset->MoveNext();
        }
        // configuracion
        $comando_sql = "select * from {$GLOBALS['DB_prefijo']}config" ;
        $recordset = $GLOBALS['DB']->execute( $comando_sql  ) ;
        while (!$recordset->EOF ) {
            $fichero = 'config.' . $recordset->fields['idconfig'] ;
            $ficheroExport->add_file( $fichero, serialize( $recordset->fields ) );
            $recordset->MoveNext();
        }
        // ficheros 
        $comando_sql = "select * from {$GLOBALS['DB_prefijo']}files" ;
        $recordset = $GLOBALS['DB']->execute( $comando_sql  ) ;
        while (!$recordset->EOF ) {
            $fichero = 'file.' . $recordset->fields['idfile'] ;
            $ficheroExport->add_file( $fichero, serialize( $recordset->fields ) );
            $recordset->MoveNext();
        }
        // plantillas
        $comando_sql = "select * from {$GLOBALS['DB_prefijo']}templates" ;
        $recordset = $GLOBALS['DB']->execute( $comando_sql  ) ;
        while (!$recordset->EOF ) {
            $fichero = 'template.' . $recordset->fields['idtemplate'] ;
            $ficheroExport->add_file( $fichero, serialize( $recordset->fields ) );
            $recordset->MoveNext();
        }
        // usuarios
        $comando_sql = "select * from {$GLOBALS['DB_prefijo']}users" ;
        $recordset = $GLOBALS['DB']->execute( $comando_sql  ) ;
        while (!$recordset->EOF ) {
            $fichero = 'user.' . $recordset->fields['iduser'] ;
            $ficheroExport->add_file( $fichero, serialize( $recordset->fields ) );
            $recordset->MoveNext();
        }
        $ficheroExport->close_file();
        $html->add_object( CustomHTML::DialogBox( _DIALOG_QUESTION , 'Herramienta de backup ', 
          "El fichero {$file}.dat ha sido creado satisfactoriamente") );
      }
      
      
      
      
      $this->render( $html, 'Exportación de datos');
      
 
    
    }
    
    
    function paginaMenu() {
    
    
    
        $html = new htmlcontainer();
        $html->add( 'br');
        $botones = new htmlcontainer();
            $botones->add_object( CustomHTML::BotonAdmin( 'Exportar', 'Exportar Páginas', 'admin.php?q=export/export',  "")) ; 
            $botones->add_object( CustomHTML::BotonAdmin( 'Importar', 'Importar Páginas', 'admin.php?q=export/import',  "")) ; 
        
        $texto = new htmlcontainer();
            $texto->add( 'p', '', 'Seleccione la acción a realizar' );
        $html->add_object( CustomHTML::DialogBox( _DIALOG_QUESTION , 'Herramienta de backup de páginas', $texto , $botones) );
        
		$this->render( $html, 'Importación/Exportación de páginas');
 
    
    }
    
    function export() {
            
        $HTML = new htmlcontainer();
        $div =& $HTML->add( 'div', array( 'style'=> 'text-align: left' ));
        $form =& $div->add( 'form', 
            array( 'name'  => 'formulario', 'method' => 'POST' , 'target' => '_new',
                'action' => 'admin.php?q=export/out')
        ) ;
        $table =&  $form->add( 'table', array(
            'celpadding' => '0',
            'celspacing' => '0'));
         $tr =& $table->add( 'tr' ) ;
         $tr->add( 'td', array( 'style' => 'padding-left: 10px;'), ' Id&nbsp;Página:' );
         $td =& $tr->add( 'td' );
         $td->add_object( select_paginas( 0 , 'idpagina',  '', false  ) );
         $td->add('br');
         $td->add( 'b', '', 'Leyenda: ');
         $td->add_text( '(C)=Contenido, (I)=Página inactiva' );
         $tr =& $table->add( 'tr' ) ;
         $td =& $tr->add( 'td', array( 'colspan' => '2' , 'style' => 'text-align: right;') );
             $td->add_object( CustomHTML::BotonAdmin("Exportar" , 'Exporta la página indicada y sus hijas' , "", "document.formulario.submit();" ) );
  
		$this->render( $HTML, 'Exportación de páginas');
		
    }
    function export_out() {
	
        $tmp_file = dirname(__FILE__) . '/../../lar/cache.importacion.mgz';
        $out_file = 'backup.paginas.tgz';
    
        $uidparent = morcegocms_utils::uidfromidpagina( $_POST['idpagina'] );
        include_once ("includes/class_admpaginas.php");
        $obj_adm = new adm_paginas; 
        $array_hijos = array_merge( array($uidparent) , $obj_adm->array_descendientes( $uidparent ));
        $tar = new morcegoCMS_Archiver(  'create', $tmp_file );
        $datos  = array( "uid" => array() , "templates" => array() );
        $contador = 0;
        foreach( $array_hijos as $uid ) {
            $comando_sql = "select * from {$GLOBALS['DB_prefijo']}paginas where uid = {$uid}";
            $recordset = $GLOBALS['DB']->execute( $comando_sql) ;
            $fichero = $uid ;
            if ( array_search( $recordset->fields['template'], $datos['templates']  ) === false ) {
                $datos['templates'][] =  $recordset->fields['template'] ;
            }
            $datos['uid'][] = $recordset->fields['uid'] ;
            if ( $contador != 0 ) {
                $datos['tree'][$recordset->fields['uid']]['parent'] = $recordset->fields['uidparent'];  
            }
            $tar->add_File( $fichero, serialize( $recordset->fields)  );
            $contador++;
        }
        // hacemos las plantillas (lista)
        $tar->add_File( 'data', serialize( $datos) );
        $tar->close_file();
        $file_out = 'backup.morcegomcs.tgz';
        header("Content-Type: application/octet-stream; name={$file_out}");
        header("Content-Transfer-Encoding: binary");
        header("Accept-Ranges: none");
        header("Content-Disposition: attachment; filename={$file_out}\n");
        //echo $tar->parse_file();
        readfile( $tmp_file );
        unlink( $tmp_file);
        unset($tar);
        die();
    }
    function import() {
        $HTML = new htmlcontainer();
        $div =& $HTML->add( 'div', array( 'style'=> 'text-align: left' ));
        $form =& $div->add( 'form', 
            array( 'name'  => 'formulario', 'method' => 'POST' ,
                'action' => 'admin.php?q=export/file',
                'enctype' => 'multipart/form-data')
        ) ;
        $table =&  $form->add( 'table', array(
            'celpadding' => '0',
            'celspacing' => '0'));
         $tr =& $table->add( 'tr' ) ;
         $tr->add( 'td', array( 'style' => 'padding-left: 10px;'), ' Fichero:' );
         $td =& $tr->add( 'td' );
         $td->add( 'input', array (
             'type' => 'file',
             'name' => 'fichero' ));
        
         $tr =& $table->add( 'tr' ) ;
         $tr->add( 'td', array( 'style' => 'padding-left: 10px;'), ' Destino:' );
         $td =& $tr->add( 'td' );
         $td->add_object( select_paginas( 0 ) );
         $td->add('br');
         $td->add( 'b', '', 'Leyenda: ');
         $td->add_text( '(C)=Contenido, (I)=Página inactiva' );
         $tr =& $table->add( 'tr' ) ;
         $td =& $tr->add( 'td', array( 'colspan' => '2' , 'style' => 'text-align: right;') );
             $td->add_object( CustomHTML::BotonAdmin("Importar" , 'Importa las páginas contenidas en el fichero como hijas de la seleccionada' , "", "document.formulario.submit();" ) );
  
		$this->render( $HTML, 'Importación de páginas');
		
    }
    function fichero( ) {
    
        $HTML = new htmlcontainer();
        if ( !isset( $_FILES['fichero'] ) || !is_uploaded_file( $_FILES['fichero']['tmp_name'] )) {
            $HTML->add( 'br');
            $HTML->add( 'div', array(
                'class' => 'caja3d'),
                'ERROR: No se ha especificado un nombre de fichero válido');
                $page =& $this->oHtml->Elements[$this->oHtml->get_first_element_id('html')] ;
                $body =& $page->Elements[$page->get_first_element_id('body')] ;
                $body ->add_object( CustomHTML::DivEncabezado( '', 'Importación de páginas') );
                $body->add_object( CustomHTML::DivContenido(  $HTML  ) );
                echo $this->oHtml->render();
                die();
        } 
        $fichero = dirname(__FILE__) . '/../../lar/cache.importacion.mgz' ;
        move_uploaded_file( $_FILES['fichero']['tmp_name'], $fichero  );
        $tar = new morcegoCMS_Archiver('read', $fichero ) ;
        // die ($tar->get_file( 'data' ) ) ;
        
        $elementos = unserialize( $tar->get_file( 'data' ) );
        $cambios = array();
        $arbol = $elementos['tree'];
        $plantillas = array();
        
        $comando_sql = "select idtemplate from {$GLOBALS['DB_prefijo']}templates order by idtemplate " ;
        
        $recordset = $GLOBALS['DB']->Execute($comando_sql); 
        while( !$recordset->EOF ) {
            $plantillas[] = $recordset->fields['idtemplate'] ;
            $recordset->MoveNext();
        }
        $plantillas_pendientes = array();
        foreach( $elementos['templates']  as $plantilla ) {
            if ( !in_array( $plantilla,  $plantillas )) {
                $plantillas_pendientes[] = $plantilla ;
            }
        }
        $form =& $HTML->add('form', array(
            'name' => 'formulario',
            'action' => 'admin.php?q=export/import_ok',
            'method' => 'post'));
        $form->add( 'input', array( 'type' => 'hidden', 'name' => 'idpagina', 'value' => $_POST['idpagina'] ));
        $form->add( 'BR' );
            $form->add( 'div', array(
                'class' => 'caja3d'),
                'Se importarán ' . count( $elementos['uid'] ) . ' Páginas');
       $form->add( 'BR' );            
        if ( count(  $plantillas_pendientes ) > 0 ) {
            $i = 0;
            foreach( $plantillas_pendientes as $plantilla_pendiente )   {
                // echo $plantilla_pendiente ;
                $form->add( 'input', array(
                    'type' => 'hidden', 
                    'name' => 'pfrom_' . $i ,
                    'value' => $plantilla_pendiente ));
                $form->add_text( 'Sustituir :');
                $form->add( 'b',  '', $plantilla_pendiente );
                $form->add_text( ' Por : ');
                $form->add_object( $this->select_plantillas( $plantilla_pendiente, $plantillas, 'pto_' . $i));
                $form->add_object( 
                    CustomHTML::boton16x16(_ADM_BOTON_INFO,  
                        'javascript:void(0);' , 
                        'Ver notas sobre la plantilla seleccionada',
                        '',
                        "template_info( document.formulario.pto_{$i}.value );"
                    )) ;
                $form->add ( 'br');
                $i++;
            }
        }
        $form->add(CustomHTML::botonAdmin("Importar" , 'Importar las páginas', '#', "document.formulario.submit();"));
        $form->add_text( '&nbsp;');
        $form->add(CustomHTML::botonAdmin("Cancelar" , 'Cancelar', 'admin.php?q=export', ""));
         
        $this->render(   $HTML , 'Importación de páginas')  ;
     
        return;
    }
    function import_ok() {
        $HTML = new htmlcontainer();
            $HTML->add( 'br');

        $uidparent = morcegocms_utils::uidfromidpagina( $_POST['idpagina']);
        $fichero = dirname(__FILE__) . '/../../lar/cache.importacion.mgz' ;
        $tar = new morcegoCMS_Archiver('read', $fichero ) ;
        $elementos = unserialize( $tar->get_file( 'data' ) );
        $cambios = array();
        $arbol = $elementos['tree'];        

        // creamos un array con los cambios de plantillas (si son necesarios)
        
        $cambios_plantillas = array();
        foreach( $_POST as $clave => $valor ) {
            $aClave = explode('_', $clave );
            if ( $aClave[0] == 'pfrom' ) {
                $cambios_plantillas[ $valor ] = $_POST["pto_{$aClave[1]}"];
            }
        }
        // creamos un recordset vacio
        $comando_sql = "select * from {$GLOBALS['DB_prefijo']}paginas where -1";
        $empty_recordset = $GLOBALS['DB']->Execute($comando_sql); 

        foreach( $elementos['uid'] as $uid ) {
            
            $objeto = unserialize( $tar->get_file("$uid") );
            $comando_sql = "select max(uid) + 1 as total  from {$GLOBALS['DB_prefijo']}paginas" ;
            $recordset = $GLOBALS['DB']->Execute($comando_sql); 
            $newuid = $recordset->fields['total'];
            if ( isset(  $cambios["{$objeto['uidparent']}"]) &&  $cambios["{$objeto['uidparent']}"] === 0 ) {
                $cambios["{$objeto['uid']}"] =  $uidparent ;
            } else {
                $cambios["{$objeto['uid']}"] =  $newuid ;
            }
            if ( isset( $cambios["{$objeto['uidparent']}"])) {
                $objeto['uidparent'] = $cambios["{$objeto['uidparent']}"];
            } else {
                $objeto['uidparent'] = $uidparent ;
            }
            $objeto['uid'] = $newuid ;
            if ( is_numeric(Morcegocms_utils::uidfromidpagina( $objeto['idpagina'] ))){
                // el idpagina ya existe ... 
                $objeto['idpagina'] = $objeto['idpagina'] . '_' . $newuid;
            } 
            // comprobamos si la plantilla es una de las del cambio!
            if ( isset( $cambios_plantillas[$objeto['template']])) {
               //  echo "<span style='color: red'>" . $objeto['template']. " == " . $cambios_plantillas[$objeto['template']] . "</span><br>";
                $objeto['template'] = $cambios_plantillas[$objeto['template']] ;
            }
            // echo "{$objeto['uid']} - {$objeto['uidparent']} - {$objeto['idpagina']} <br>";
            $comando_sql = $GLOBALS['DB']->GetInsertSQL($empty_recordset,  $objeto);
            // echo $comando_sql ;
            $GLOBALS['DB']->execute( $comando_sql );
        }
        unlink($fichero );
            $HTML->add( 'BR' );
            $HTML->add( 'div', array(
                'class' => 'caja3d'),
                'Se han importado ' . count( $elementos['uid'] ) . ' Página(s)');
           $HTML->add( 'BR' );            
            $HTML->add( 'div', array(
            'class' => 'caja3d tip'),
            'Recuerde que si el origen de las páginas utiliza funciones definidas por el usuario propias debe importar estas o modificarlas en esta web');

        $this->render( $HTML, 'Importación de páginas') ;

    
    }
    function select_plantillas( $origen, $array_plantillas, $control ) {
        $aTMP = explode( '_', $origen);
        $prefijo =& $aTMP[0];
        $html = new htmlcontainer();
        $select =& $html->add( 'select', array( 'name' => $control ));
        foreach( $array_plantillas as $plantilla ) {
            if( substr( $plantilla, 0, strlen( $prefijo )) == $prefijo ) {
                $select->add('option', array(
                    'value' => $plantilla), $plantilla );
            }
        
        }
        return $html ;
    
    
    
    }

}
function select_paginas( $uid, $control = 'idpagina', $value = 'index', $include_index = true ) {
    if ( empty( $uid )) {
        $uid = 0;
    }
    $HTML = new htmlcontainer() ;
    $select =&  $HTML->add( 'select', array( 'name' => $control, 'style' => "font-size: 10px;" ) );
    $select->add_object( element_hijo( $uid , 0 , $value, $include_index  ) );
    return $HTML;
}
function element_hijo( $uidparent = 0, $nivel = 0, $value = '', $include_index = true   ) {
    global $DB, $DB_prefijo;    
    $comando_sql   = "select idpagina, uid, titulo, activa from " . $DB_prefijo . "paginas where uid = {$uidparent}";

    $recordset = $DB->execute("$comando_sql") ;
    $idpagina = $recordset->fields['idpagina'];
    $uid = $recordset->fields['uid'];
    $titulo = (strlen ( $recordset->fields['titulo'] ) > 30 ) ? substr( $recordset->fields['titulo'], 0, 30) . '...': $recordset->fields['titulo'] ;
    $activa = ( $recordset->fields['activa'] == 1 ) ? '' : '(I)';
    $html = new htmlcontainer();
    if ( $nivel > 0 ) {
        $prefijo =  str_repeat( '&nbsp;&nbsp;|', $nivel) . '-' ;
    } else {
        $prefijo = '';
    }
    
    if ( $include_index === true ||  $uid != 0 ) {
        $html->add( 'option', array( 
            'value' => $idpagina ),
            $prefijo .  "{$activa}[{$idpagina}] $titulo " );
        
    }
    
 
    $html->add_object( content_hijo( $uid, $nivel + 1 ) ); ;

    $comando_sql    = "select idpagina, uid, titulo, uidparent from " . $DB_prefijo . "paginas where uidparent = {$uidparent} and tipo = 0 and uid != 0 order by orden" ;
    $recordset = $DB->execute("$comando_sql") ;
    while (!$recordset->EOF) {
        $idpagina =& $recordset->fields['idpagina'];
        $uid =& $recordset->fields['uid'];
        $titulo =& $recordset->fields['titulo'];
        
        $html->add_object( element_hijo( $uid, $nivel + 1, $value ) ); ;
        $recordset->MoveNext();
    }
    return $html;
}

function content_hijo( $uidparent, $nivel = 0 ) {
    global $DB, $DB_prefijo;    
    $html = new HtmlContainer( ) ;
    $comando_sql    = "select idpagina, uid, titulo, uidparent, activa from " . $DB_prefijo . "paginas where uidparent = {$uidparent} and tipo = 1 and uid != 0 order by orden" ;
    $recordset = $DB->execute($comando_sql) ;
   if ( $nivel > 0 ) {
        $prefijo =  str_repeat( '&nbsp;&nbsp;|', $nivel) . '-' ;
    } else {
        $prefijo = '';
    }

    
    while (!$recordset->EOF) {
        $idpagina = $recordset->fields['idpagina'];
        $titulo = (strlen ( $recordset->fields['titulo'] ) > 40 ) ? substr( $recordset->fields['titulo'], 0, 40) . '...': $recordset->fields['titulo'] ;
        $activa = ( $recordset->fields['activa'] == 1 ) ? '' : '(I)';


        $html->add( 'option', array( 
        'value' => $idpagina ),
         $prefijo . "(C){$activa}[{$idpagina}] $titulo " );
        $recordset->MoveNext();
    }
    return $html;
}
/**
* @version: 1.0 
*
*
*
*
*
*/
class morcegoCMS_Archiver {
    var $elementos; // array con el contenido del archivo
    var $fichero;
    var $hf;
    var $fileContent = '';
    var $filesize;
    var $files ; // array con los archivos contenidos en el fichero (sólo para módo lectura );
    
    
    function morcegoCMS_Archiver( $accion = 'read',  $fichero = '') {
        $this->elementos = array();
        if( !empty( $fichero)) {
            $this->fichero = $fichero;
            if ( $accion == 'read' ) {
                if (  file_exists( $fichero ) ) {
                   $this->filesize = filesize( $this->fichero );
                   $this->hf = fopen( $this->fichero, 'r');
                   $this->lista_ficheros( ) ;
                   
                   
                   
                }
            } else {
               $this->hf = fopen( $this->fichero, 'w');
            }
        }
    }
    /**
    * Añade un fichero al archivo
    */ 
    function add_file($nombre, $content ) {
        // comprimimos el contenido;
        $content = gzencode( $content );
        // el nombre: 255 bytes
        fwrite( $this->hf, str_pad( $nombre , 255 ," ",STR_PAD_RIGHT), 255);
        // el tamaño: 
        fwrite( $this->hf, str_pad(strlen( $content ), 8 ,"0",STR_PAD_LEFT), 8);
        // el fichero comprimido
        fwrite( $this->hf, $content, strlen( $content ) );
        // el MD5
        fwrite( $this->hf, md5($content) , 32 );
    }
    /**
    *
    *
    *
    */
    function close_file() {
        fclose( $this->hf );
    }
    /*
    *
    * Devuelve el contenido de un fichero
    *
    */
    function get_file( $nombre ) {
        $filename = '';
        $posicion = 0 ;
        while ( $nombre !=  $filename &&  $posicion < $this->filesize ) {
            fseek( $this->hf, $posicion, SEEK_SET);
            $filename = fread( $this->hf, 255);
            $posicion  = $posicion + 255; 
            $filename = trim( $filename ) ;
            $sizefile = (int) fread( $this->hf, 8)  ; 
            $posicion  = $posicion +  8; 
            if ( $filename == $nombre ) {
                $content  =  fread( $this->hf, $sizefile );
                $md5 = fread( $this->hf, 32);
                if ( md5( $content) != $md5 ) {
                    // aqui vendría el error de crc
                } else {
                   return gzinflate(substr($content,10,-4));
                   //  return gzinflate($content);
                }
            } else {
                // para depurar
            }
            $posicion = $posicion + $sizefile + 32;
        }
        return '-1';
    }
    
    function lista_ficheros( )  {
        $posicion = 0; 
        while ($posicion < $this->filesize ) {
            fseek( $this->hf, $posicion, SEEK_SET);
            $filename = fread( $this->hf, 255);
            $posicion  = $posicion + 255; 
            $filename = trim( $filename ) ;
            $sizefile = (int) fread( $this->hf, 8)  ; 
            $posicion  = $posicion +  8; 
            $this->files[] = $filename ;
            $posicion = $posicion + $sizefile + 32;
        }
    }
    
    function leer_fichero( $contenido ) {
        
        $this->elementos  = array();
        $contenido = gzinflate(substr($contenido,10,-4));
        
        $size = strlen($contenido);
        $posicion = 0;
        while($posicion  < $size ) {
            if(substr($contenido,$posicion,512) == str_repeat(chr(0),512)) {
                break;
            }
            $nombre_fichero  = $this->ParsearNull(substr($contenido,$posicion,100));
            $size_fichero	 = octdec(substr($contenido,$posicion + 124,12));
            $checksum_fichero = octdec(substr($contenido,$posicion + 148,6));
            if($this->CheckSum_cadena(substr($contenido,$posicion,512)) != $checksum_fichero) {
                // ERROR!!! fichero posiblemente corrupto
                return false;
            }
            $this->elementos[$nombre_fichero] = substr($contenido,$posicion  + 512,$size_fichero);
            $posicion += 512 + (ceil($size_fichero / 512) * 512);
        }
    }
    function CheckSum_Cadena($cadena) {
        $numero  = 0;
        for($i=0; $i<512; $i++) { $numero+= ord($cadena[$i]);}
        for($i=0; $i<8; $i++)   {$numero -= ord($cadena[148 + $i]); }
        $numero += ord(" ") * 8;
        return $numero ;
    }
    function ParsearNULL($cadena) {
        return substr($cadena,0,strpos($cadena,chr(0)));
    }
}

?>