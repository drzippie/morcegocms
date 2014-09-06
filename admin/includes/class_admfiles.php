<?
include_once( dirname( __FILE__ ) . '/class_fileupload.php' );
include_once( dirname(__FILE__) . '/../../includes/morcegoCMS/morcegocms_functions_fichero.php' );

class adm_files   extends adm_class {
  
    function adm_files( $html ) {
        $this->oHtml = $html ;
        $this->parameters =& $GLOBALS['aArgumentos'] ;

        $aParametros =& $this->parameters ;
        
        if (!isset( $aParametros[1] )) {
            $aParametros[1] = 'list' ;
        }
        
        switch ( $aParametros[1] ) {
            case 'add':
		if ( !isset( $aParametros[2])) {
                    $aParametros[2] =  '' ;
                }
                $this->add_file( $aParametros[2]);
                break;
            case 'edit':
                if ( !isset( $aParametros[2])) {
                    $aParametros[2] =  '' ;
                }
                 $this->edit_file( urldecode($aParametros[2] ));
                break;
            case 'delete':
                if ( !isset( $aParametros[2])) {
                    $aParametros[2] =  '' ;
                }
                 $this->delete_file( urldecode($aParametros[2] ));
                break;
            case 'content':
                 $this->edit_content( urldecode($aParametros[2] ));
                break;
            case 'content_ok':
                 $this->content_ok();
                break;                                
            case 'add_ok':
                $this->add_file_ok();
                break;
            case 'edit_ok':
                $this->edit_file_ok();
                break;                 
            default:
                if ( !isset( $aParametros[2])) {
                    $aParametros[2] =  '' ;
                }
                $this->show_files( $aParametros[2]);
            
        
        
        }


       
    
    }


    function show_files( &$category ) {
      $maxregs = 15;
      if ( empty( $category )) {
        // buscamos la última categoria modificada
        $comando_sql = "select category from {$GLOBALS['DB_prefijo']}files order by date desc";        
        $resultado = $GLOBALS['DB']->SelectLimit( $comando_sql, 1, 0 );
        $category = $resultado->fields['category'] ;
      }
	
      global $configCMS;
	
        $HTML = new htmlcontainer();
    
        global  $DB, $DB_prefijo;
        
	$div =& $HTML->add( 'div', array( 'style' => 'float: right; margin-right: 10px;' )) ;
	$form =& $div->add('form', array( 'name' => 'frmeditarfichero', 'onsubmit' => 'return false;'));
	$form->add_text(  'Edición rápida [idfichero]: ' );
	$form->add( 'input', array( 'name' => 'idfichero' ));
	$form->add_text( '  ' ) ;
	$form->add( 'input', array('type' => 'submit', 'onclick' => "window.location.href = 'admin.php?q=files/edit/' +document.frmeditarfichero.idfichero.value;", 'value' => 'Editar'));
        
        $form =& $HTML->add('form', array( 'name' => 'frmcategory'));
        $form->add( 'b', '', 'Categoría: ' );
        $form->add_text( 
            $this->combo_category( $category, 'category', "window.location.href = \"admin.php?q=files/list/\" +document.frmcategory.category.value;") 
        );
	
	/*
		Edición rápida
	*/
	$HTML->add( 'br');
        
        $comando_sql = "select count(*) as total from {$DB_prefijo}files where category='{$category}'";
	$recordset = $DB->execute( $comando_sql ) ;
	$totalRegs = $recordset->fields['total'] ;
	
	$totalPaginas = ceil($totalRegs /  $maxregs );
	$pagina = ( isset( $this->parameters[3]) ) ? (int) $this->parameters[3] : 1 ; 
	if ( $pagina < 1 ) {
		 $pagina  = 1 ;
	}
	
	$HTML->add( CustomHTML::botonAdmin("Añadir Fichero" , 'Añadir fichero' , "", "document.location.href='admin.php?q=files/add/' + document.frmcategory.category.value") );
        /*  Paginación */
	$div =& $HTML->add( 'div', array( 'class' => 'paginacion' ) );
	
	$div2=& $div->add( 'div', array( 'style' => 'float: right;' )  );
	for ($i = 1; $i <= $totalPaginas ; $i++ ) {
		
		if ( $i == $pagina ) { 
			$div2->add( 'strong', '', $i );
		} else {
		
		$div2->add('a', array(
			'href' => 'admin.php?q=files/list/' . urlencode( $category ) . '/' . $i ),
			$i );
		}
		$div2->add_text( ' ' ) ;
	}
	$div->add_text( 'Página ' . $pagina . ' de ' .  $totalPaginas ) ;
	
	$table =& $HTML->add( 'table', array( 
             'class'             => 'ruler wide ficheros'));
        $tr =& $table->add( 'tr');
            $tr->add( 'th', '', '&nbsp;');
             $tr->add( 'th', '', 'Fichero');
             $tr->add( 'th', '', 'Opciones');
         
        $comando_sql = "select idfile, original_file, date, mimetype, size, description from {$DB_prefijo}files where category='{$category}' order by date desc";
         $recordset = $DB->SelectLimit( $comando_sql, $maxregs, ($pagina -1 ) * $maxregs  );
		$tdClass = 'non';
        while ( !$recordset->EOF ) {
            $idfile             =& $recordset->fields['idfile'];
            $idfile_encoded     = urlencode( $idfile);
            $original_file      =& $recordset->fields['original_file'];
            $date               =& $recordset->fields['date'];
            $mimetype           =& $recordset->fields['mimetype'];
            $size               =& $recordset->fields['size'];
            $description        =& $recordset->fields['description'];
            $tdClass = ($tdClass === 'non') ? false : 'non' ;
			
            $tr =& $table->add( 'tr', array('class' => $tdClass));
            
            $td =& $tr->add( 'td');
                $td->add_object( 
                    CustomHTML::Boton16x16( _ADM_BOTON_EDITAR, 
                        "admin.php?q=files/edit/{$idfile_encoded}",  
                        'Modificar', '', '' ));
                
            $td =& $tr->add( 'td');
	    
            if ( $this->is_editable( $mimetype )) {
                $td->add( 'a', array( 'href' => "admin.php?q=files/content/{$idfile_encoded}"),
                    " [Editar Contenido] <strong>{$idfile}</strong> ");
            } else {
                $td->add_text( $idfile  );
            }
	    $td->add( 'div', array( 'class'  => 'masinfo' , 'id' => 'mi-' . base64_encode( $idfile )) );


            $td =& $tr->add( 'td');
	   $td->add_object( 
                    CustomHTML::boton16x16(  _ADM_BOTON_INFO , 
                        '#',  
                        'Ver más información', '', "masInfo( '" . base64_encode( $idfile ) . "');" ));   
	    
            $td->add_text( ' ' );
	      $td->add_object( 
                    CustomHTML::boton16x16(  _ADM_BOTON_VISUALIZAR , 
                        morcegocms_functions_fichero::url( $idfile ) ,  
                        'Ver archivo', '_blank'  ));  
		$td->add_text( ' ' );
                $td->add_object( 
		
                    CustomHTML::boton16x16(  _ADM_BOTON_BORRAR , 
                        "#",  
                        'Borrar', '', "confirmar('¿ Desea Borrar el fichero, esta acción no se podrá deshacer ?'".
                            ",'admin.php?q=files/delete/{$idfile_encoded}');" ));
            $recordset->MoveNext();
        }
         
    
        $this->Render( $HTML, 'Administración de ficheros', '');
    }

	
        function add_file_ok() {
            global $DB, $DB_prefijo;
            global $varsCMS;
            
             $fichero = new cls_fileupload( 'file' ) ;
	     
            // si la categoria está vacia se indicará default
            if ( empty( $_POST['list_category']) && empty( $_POST['category'])  ) {
                $_POST['category'] = 'unsorted';
            }
            if (  $fichero->ok === false   ) {
                $this->render( customHTML::DialogBox( _DIALOG_STOP , 'ERROR: Creacion de archivo',
			'Debe al menos indicar un nombre de fichero único, categoria y un archivo (de más de 0 bytes).'.
			"<br/><br/><input type='submit' onclick='history.go(-1);' value='VOLVER' />") );
		die();
            } else {
                $comando_sql = "select idfile from {$DB_prefijo}files where idfile='{$_POST['idfile']}'";
                $recordset = $DB->execute( $comando_sql ) ;
		
		if ($recordset->RecordCount() > 0 ) {
                $this->render( customHTML::DialogBox( _DIALOG_STOP , 'ERROR: Creacion de archivo',
			'El nombre de fichero especificado ya existe en la base de datos.'.
			"<br/><br/><input type='submit' onclick='history.go(-1);' value='VOLVER' />") );
			die();
		}
		
		
           
            }
            if ( empty( $_POST['category']) ) {
                $category = $_POST['list_category'];
            } else {
                $category = str_replace(' ', '_', $_POST['category']);
            }
            $mimetype = $this->mimetype($fichero->post['name'] );
            $content = $fichero->content( ) ; 
	    if (isset($_POST['internal'])) {
                $internal = '1';
            } else {
                $internal = '0';
            }
            $content = addslashes($content) ;
            $comando_sql = "insert into {$DB_prefijo}files (idfile, original_file, category, description, mimetype, size, internal, iduser, date , content) values ( '{$_POST['idfile']}',  '{$_FILES['file']['name']}', '{$category}','{$_POST['descripcion']}', '{$mimetype}', '{$_FILES['file']['size']}',  $internal, {$_SESSION['iduser']}, now(), '{$content}') ";
            $DB->execute( $comando_sql) ;
            $this->show_files( $category );
        }

        function edit_file_ok() {
            global $DB, $DB_prefijo;
            global $varsCMS;
            $fichero = new cls_fileupload( 'file' ) ;

            if ( empty( $_POST['idfile']) ||
                ( empty( $_POST['list_category']) && empty( $_POST['category'])  )
                ) {
                $this->render( customHTML::DialogBox( _DIALOG_STOP , 'ERROR: Modificación de archivo',
			'Debe al menos indicar un nombre de fichero único y categoría.'.
			"<br/><br/><input type='submit' onclick='history.go(-1);' value='VOLVER' />") );
		return ;
            }
            if ( empty( $_POST['category']) ) {
                $category = $_POST['list_category'];
            } else {
                $category = str_replace(' ', '_', $_POST['category']);
            }
            if (  $fichero->ok ) {
                $path_fichero = dirname( __FILE__ ) . '/../../' . $varsCMS->path_repository  . '/cache.fichero.' . $_POST['idfile'] . '.' . $fichero->extension( ) ;
                if (file_exists($path_fichero)) {
                    @unlink ($path_fichero);
                }
		$content = $fichero->content( ) ; 
		$new_file = true ;
                $content = addslashes( $content) ;    
		$mimetype = $this->mimetype($fichero->post['name'] );
            } else {
                $new_file = false ;
            }
	if ( isset($_POST['internal'])) {
                $internal = '1';
            } else {
                $internal = '0';
            }
         if ( $new_file) {
                $comando_sql = "update {$DB_prefijo}files  set ".
                    "idfile = '{$_POST['idfile']}', ".
                    "category = '{$category}', ".
                    "description = '{$_POST['descripcion']}', ".
                    "mimetype = '{$mimetype}', ".
                    "size = '{$_FILES['file']['size']}', " .
                    "original_file = '{$_FILES['file']['name']}', ".
                    "content = '{$content}', ".
                    "internal = {$internal}, ".
                    "iduser = {$_SESSION['iduser']},".
                    "date = now() ".
                    "where idfile = '{$_POST['idfile_old']}' ";
            } else {
                $comando_sql = "update {$DB_prefijo}files  set ".
                    "idfile = '{$_POST['idfile']}', ".
                    "category = '{$category}', ".
                    "description = '{$_POST['descripcion']}', ".
                    "internal = $internal, ".
                    "iduser = {$_SESSION['iduser']},".
                    "date = now() ".
                    "where idfile = '{$_POST['idfile_old']}' ";
            }
            $DB->execute( $comando_sql) ;
            $this->show_files( $category );
          
        }
	/**
	* @return integer
	* @desc Nos muestra un formulario para la creación de un nuevo template.
	*/
	function add_file( $category)  {
            $html = new htmlContainer();
                        
            $div =& $html->add( 'div', array( 'style'=> 'text-align: left' ));
            $form =& $div->add( 'form', array( 
                'name'          => 'formulario', 
                'method'        => 'POST',
                'action'        => 'admin.php?q=files/add_ok',
                'enctype'       => 'multipart/form-data')) ;
            
            $form->add_text('Nombre: ');
            $form->add( 'input', array(
                'type'          => 'text',
                
                'name'          => 'idfile'));
            $form->add( 'br') ;
            
            $form->add_text('Categoría: ');
            $form->add_text( $this->combo_category($category, 'list_category') );
            
			$form->add_text(' Nueva: ' );
            $form->add( 'input', array(
                'type'          => 'text',
                'style'	=> 'width: 150px;',
                'name'          => 'category'));
            $form->add( 'br') ;

            $form->add_text('Fichero: ');
            $form->add( 'input', array(
                'type'          => 'file',
                'style'	=> 'width: 300px;',
                'name'          => 'file'));
            $form->add( 'br') ;
            
            $form->add_text('Descripción: ');
            $form->add( 'br') ;
            $form->add( 'textarea', array(
            	'style'	=> 'width: 300px; height: 100px;',
                'name'          => 'descripcion'));
            $form->add( 'br') ;
            
            $form->add_text('Interno : ');
            $form->add( 'input', array(
                'type'          => 'checkbox',
                'name'          => 'internal',
                'value'         => 'on',
                'checked'       => true ));
            
            $botones = new htmlcontainer() ;
            $botones->add_object(  
                CustomHTML::botonadmin("Crear" , 
                    'Añadir el fichero a la base de datos', 
                    '#', 
                    "document.formulario.submit()") );
            $botones->add_text('&nbsp;');
            $botones->add_object(  
                CustomHTML::botonadmin("Volver" , 
                    'Cancelar', 
                    '#', 
                    "history.go(-1)") );                        
            $this->render( customHTML::DialogBox( _DIALOG_QUESTION , 'Alta de ficheros', $html , $botones ) , 
            'Administración de Archivos', '' );
	}
        
        
        
        
        function edit_file( $idfile )  {
            global $DB, $DB_prefijo;
            
            $html = new htmlContainer();
            $div =& $html->add( 'div', array( 'style'=> 'text-align: left' ));
            $comando_sql = "select idfile, description, category, internal from {$DB_prefijo}files where idfile = '{$idfile}'";
            $recordset = $DB->execute($comando_sql);
            if ($recordset->NumRows() == 0 ){
            
                $div->add_text('ERROR: El archivo especificado no existe');
                $this->render( customHTML::DialogBox( _DIALOG_STOP , 'Modificación de ficheros', $html,
                    CustomHTML::botonadmin("Volver" , 
                        'Cancelar', 
                        '#', 
                        "history.go(-1)")
                    ) , 
                'Administración de Archivos', '' );
                die();
            }
            $description = $recordset->fields['description'];
            $category = $recordset->fields['category'];
            $internal = $recordset->fields['internal'];
                        
            
            $form =& $div->add( 'form', array( 
                'name'          => 'formulario', 
                'method'        => 'POST',
                'action'        => 'admin.php?q=files/edit_ok',
                'enctype'       => 'multipart/form-data')) ;
            
            $form->add( 'input', array(
                'type'          => 'hidden',
                'name'          => 'idfile_old',
                'value'         => $idfile ));
            


            $form->add_text('Nombre: ');
            $form->add( 'input', array(
                'type'          => 'text',
                'style'	=> 'width: 300px;',
                'name'          => 'idfile',
                'value'         => $idfile));
            $form->add( 'br') ;
            
            $form->add_text('Categoría: ');
            $form->add_text( $this->combo_category( $category, 'list_category') );
            $form->add_text(' Nueva: ' );
            $form->add( 'input', array(
                'type'          => 'text',
                'style'	=> 'width: 150px;',
                'name'          => 'category'));
            $form->add( 'br') ;

            $form->add_text('Fichero: ');
            $form->add( 'input', array(
                'type'          => 'file',
                'style'	=> 'width: 300px;',
                'name'          => 'file'));
            $form->add( 'br') ;
            
            $form->add_text('Descripción: ');
            $form->add( 'br') ;
			$form->add( 'textarea', array(
            'style'	=> 'width: 300px; height: 100px;',
                'name'          => 'descripcion'), $description);
            $form->add( 'br') ;
            
            $form->add_text('Interno : ');
            $form->add( 'input', array(
                'type'          => 'checkbox',
                'name'          => 'internal',
                'checked'       => (($internal == 1) ? true : false )));
            
            
            
            $botones = new htmlcontainer() ;
            $botones->add_object(  
                CustomHTML::botonadmin("Grabar" , 
                    'Grabar y salir', 
                    '#', 
                    "document.formulario.submit()") );
 
            $botones->add_text('&nbsp;');
            $botones->add_object(  
                CustomHTML::botonadmin("Volver" , 
                    'Cancelar', 
                    '#', 
                    "history.go(-1)") );                        
                    
            
             $this->render( customHTML::DialogBox( _DIALOG_QUESTION , 'Modificación de ficheros', $html, $botones ) , 
                'Administración de Archivos', '' );
	}
        function delete_file( $idfile )  {
            global $DB, $DB_prefijo;
            $comando_sql = "select idfile from {$DB_prefijo}files where idfile = '{$idfile}'";
            $recordset = $DB->execute($comando_sql);
            if ($recordset->NumRows() == 0 ){
               $this->render( customHTML::DialogBox( _DIALOG_STOP ,  'ERROR: Borrado de Ficheros',
                'El archivo especificado no existe.'.
                "<br/><br/><input type='submit' onclick='history.go( -1 );' value='VOLVER'>"));
            } else { 
                $comando_sql = "delete from {$DB_prefijo}files where idfile = '{$idfile}'";
                $DB->execute( $comando_sql ) ;
		header( "Location: ./admin.php?q=files");
		die();

            }
	}        
    function edit_content( $idfile) {
        global $DB, $DB_prefijo;
        
        $comando_sql = "select content, original_file from {$DB_prefijo}files where idfile='{$idfile}'";
        $recordset = $DB->execute( $comando_sql );
        
        $content = htmlentities( $recordset->fields['content']);
        $original_file = $recordset->fields['original_file'];
        
        $idfile_encoded = urlencode( $idfile );
        
        $html = new htmlcontainer( );
        
        $form =& $html->add( 'form', array(
            'name'      => 'editar',
            'method'    => 'post',
            'action'    => 'admin.php?q=files/content_ok'));
        $form->add( 'input', array(
                'type'          => 'hidden',
                'name'          => 'continuar',
                'value'         => 'false' ));
            
        $form->add( 'input', array( 
            'name'      => 'idfile',
            'type'      => 'hidden',
            'value'     => $idfile ));
        $form->add( 'textarea', array(
            'name'      => 'content',
            'style'     => 'height: 400; width: 90%; font-size: 1em; font-family: verdana,helvetica,arial;'),
            $content);
        $form->add('br');
        
        $form->add_object(  
            CustomHTML::botonadmin("Grabar y salir" , 
                'Modificar el contenido del fichero', 
                '#', 
                "document.editar.submit()") );
                $form->add_text('&nbsp;');
        $form->add_object(  
          CustomHTML::botonadmin("Grabar y continuar" , 
              'Grabar y continuar', 
              '#', 
              "document.editar.continuar.value='true';document.editar.submit()") );                                    
        $form->add_text('&nbsp;');
        $form->add_object(  
            CustomHTML::botonadmin("Volver" , 
                'Cancelar', 
                '#', 
                "history.go(-1)") );                        
        $this->render( $html, " Edición del archivo:[{$idfile}] ({$original_file})", '');
    }
    function content_ok() {
        global $DB, $DB_prefijo;
        $idfile = &$_POST['idfile'];
        $size = strlen(  $_POST['content']);
        if (!get_magic_quotes_gpc()) {
            $content = addslashes($_POST['content']);
        } else  {
            $content = &$_POST['content'];
        }
        //recuperamos el nombre del archivo ... 
        $comando_sql = "update {$DB_prefijo}files set content = '{$content}'," .
            "size = {$size}, ".
            "date = now() " .
            "where idfile = '{$idfile}'";
        
        
        $tmpFile =& $this->tmpfile( $idfile ) ;
        if (file_exists($tmpFile)) {
            @unlink ($tmpFile);
        }
        
        
        $DB->execute( $comando_sql );
        
        if ( $_POST['continuar'] == 'true' ) {
          header( 'Location: admin.php?q=files/content/' . urlencode( $idfile ) );
        
        } else {
          $recordset = $DB->execute( 
            "select category from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}'" 
          );
          $this->show_files( $recordset->fields['category'] ) ;
        }
       
       
    }
    
    function combo_category( $value, $control_name, $onchange = '') {
        global $DB, $DB_prefijo ;
        $comando_sql = "select distinct category from {$DB_prefijo}files";
        $recordset = $DB->execute( $comando_sql );
        $str_out = "<select name='{$control_name}' onchange='{$onchange}'>" .
            "<option value=''>-- Seleccione -- </option>";
        while ( !$recordset->EOF ){
            $category = &$recordset->fields['category'];
            if( $category == $value ) {
                $selected = ' selected ';
            } else {
                $selected = '';
            }
            $str_out .= "<option value='{$category}' $selected >{$category}</option>" ;
            $recordset->MoveNext();
        }
        $str_out .= '</select>';
        return $str_out ;
    }
    function mimetype( $filename  ) {
        $aExtension = array( 
          'doc' => 'application/msword',
          'pdf' => 'application/pdf',
          'rtf' => 'application/rtf',
          'gz'  => 'application/x-gzip',
          'zip' => 'application/zip',
          'wav' => 'audio/x-wav',
          'mid' => 'audio/x-midi',
          'gif' => 'image/gif',
          'jpg' => 'image/jpeg',
          'jpe' => 'image/jpeg',
          'jpeg' => 'image/jpeg',
          'png' => 'image/png',
          'avi' => 'video/x-msvideo',
          'exe' => 'application/octet-stream',
          'html' => 'text/html',
          'htm' => 'text/html',
          'css' => 'text/css',
          'js'  => 'application/x-javascript',
	  'txt' => 'text/plain',
          'mov' => 'video/quicktime',
          'mpg' => 'video/mpeg',
          'mpeg' => 'video/mpeg',
          'mp3' => 'audio/mpeg',
	  'swf' => 'application/x-shockwave-flash',
          'ppt' => 'application/mspowerpoint',
          'pps' => 'application/mspowerpoint',
          'xls' => 'application/vnd.ms-excel',
          'xlm' => 'application/vnd.ms-excel',
          'xml' => 'text/xml'
        );
        $extension = $this->extension( $filename);
        if ( isset( $aExtension[$extension] )) {
            $resultado = $aExtension[$extension] ;
        } else {
            $resultado = 'application/octet-stream';
        }
        return $resultado;
    }
    function extension( $filename) {
        return substr( strtolower( $filename),  - ( strlen( $filename) - strrpos($filename , '.') - 1));
    }
    /**
    * Nos devuelve la ruta local de un archivo en la caché
    *
    */
    function tmpfile( $idfile) {
      $path = dirname( __FILE__ ) . '/../../lar/';
      include_once( dirname(__FILE__) . '/../../includes/morcegoCMS/morcegocms_functions_fichero.php' );
      $aTMP  =  explode( '/', morcegocms_functions_fichero::url( $idfile ));
      return  ( $path . $aTMP[ count( $aTMP ) - 1  ] ) ;
    }
    function is_editable( $mimetype) {
        $extension = $this->extension( $mimetype);
        // array con las extensiones editables
        $aExtension = array( 'text/plain', 
            'text/html', 
            'text/css',
            'application/x-javascript',
            'text/xml');
        if (in_array( $mimetype, $aExtension)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
    
    
    
}
	
	
	
?>
