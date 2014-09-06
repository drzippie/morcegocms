<?php 
/**
* Administración de botones ... 
*
* Inicio: 2003-Oct-12
* Autor: Antonio Cortés <Dr Zippie> (antonio@antoniocortes.com)
*
*
*/

include_once( dirname(__FILE__) . '/../../includes/morcegoCMS/morcegocms_functions_fichero.php' );
include_once( dirname(__FILE__) . '/../../includes/morcegoCMS/morcegocms_cls_boton.php' );
class  adm_botones  extends adm_class {

function adm_botones(  $html  ) {
        $this->oHtml  = $html ;
        global $DB, $DB_prefijo, $aArgumentos ;    
            if (!isset( $aArgumentos[1] )) {
                $aArgumentos[1] = '' ;
            }
            switch ($aArgumentos[1]){
                case 'list':
                    $this->show_botones();
                    break;
                case 'add':
                    $this->add_boton();
                    break; 
                case 'edit':
                    $this->edit_boton();
                    break;                    
                    
                case 'add_ok':
                    $this->add_boton_ok();
                    break;                    
                    
                default:
                    $this->show_botones();
            }
    }
    
    
    function add_boton_ok() {
        global $DB, $DB_prefijo, $aArgumentos, $varsCMS;
        /**  
        * primero debemos comprobar si se ha subido algun archivo, esos archivos son:
        * idimagenizquierdanew
        * idimagencentronew
        * idimagenderechanew
        * idttfnew
        *
        */
        if ( !empty(  $_POST['idboton'] )) {
            if (is_uploaded_file($_FILES['idimagenderechanew']['tmp_name'])) {
                $_POST['idimagenderecha'] =  $_POST['idboton'] . '_' . 'imagen_derecha';
                $this->save_file( $_POST['idimagenderecha'] , 'idimagenderechanew' );
            }
        
            if (is_uploaded_file($_FILES['idttfnew']['tmp_name'])) {
                $_POST['idttf'] =  $_POST['idboton'] . '_' . 'TTF';
                $this->save_file( $_POST['idttf'] , 'idttfnew' );
            }
            
            if (is_uploaded_file($_FILES['idimagenizquierdanew']['tmp_name'])) {
                $_POST['idimagenizquierda'] =  $_POST['idboton'] . '_' . 'imagen_izquierda';
                $this->save_file( $_POST['idimagenizquierda'] , 'idimagenizquierdanew' );
            }
    
            if (is_uploaded_file($_FILES['idimagencentronew']['tmp_name'])) {
                $_POST['idimagencentro'] =  $_POST['idboton'] . '_' . 'imagen_centro';
                $this->save_file( $_POST['idimagencentro'] , 'idimagencentronew' );
            }
            
    
            // grabamos el botón
            $DB->Replace( $DB_prefijo . 'botones' , 
                array( 
                   'idboton'                => $_POST['idboton'],
                   'idimagencentro'         => $_POST['idimagencentro'],
                   'idimagenderecha'        => $_POST['idimagenderecha'],
                   'idimagenizquierda'      => $_POST['idimagenizquierda'],
                   'idttf'                  => $_POST['idttf'],
                   'ttfsize'                => $_POST['ttfsize'],
                   'ancho'                  => $_POST['ancho'],
                   'colortexto'             => $_POST['colortexto'],
                   'colorfondo'             => $_POST['colorfondo'],
                   'colortransparente'      => $_POST['colortransparente'],
                   'correccionx'            => $_POST['correccionx'],
                   'correcciony'            => $_POST['correcciony'],
                   'descripcion'            => $_POST['descripcion'],
                   'iduser'                 => $_SESSION['iduser'],
                   'fecha'                   => $DB->DBTimeStamp( time())
                   ),
                'idboton',
                true );
        }
        // Borramos los botones ...
        morcegocms_utils::EmptyCacheFiles( 'boton' );
        
        
        header( "Location: admin.php?q=botones");
        die();
    
    }
    function save_file( $idfile, $control ) {
        global $DB, $DB_prefijo, $aArgumentos, $varsCMS;
        $path_fichero = dirname( __FILE__ ) . '/../../' . $varsCMS->path_repository  . '/cache.fichero.upload'; 
        move_uploaded_file ($_FILES[$control ]['tmp_name'], $path_fichero);
        $DB->Replace( $DB_prefijo . 'files' , 
            array( 
               'idfile'             => $idfile,
               'category'           => 'botones',
               'description'        => '',
               'mimetype'           => $_FILES[$control]['type'],
               'size'               => $_FILES[$control]['size'],
               'internal'           => 1,
               'iduser'             => $_SESSION['iduser'],
               'date'               => $DB->DBTimeStamp( time()),
               'original_file'      => strtolower( $_FILES[$control]['name'] )
               ),
            'idfile',
            true );
        $DB->updateblobfile( $DB_prefijo . 'files',
            'content',
            $path_fichero,
            "idfile = '{$idfile}'");    
        unlink( $path_fichero );
    }
    
    
    function edit_boton( ) {
        global $DB, $DB_prefijo, $aArgumentos ;    
        
        $comando_sql = "select idboton from {$DB_prefijo}botones where uid = {$aArgumentos[2]}";
        $recordset = $DB->execute( $comando_sql);
        $idboton = $recordset->fields['idboton'];
        $this->render(  $this->form_boton( $idboton ), "Editando Botón [{$idboton}] " );
    
    }
    
    
    
    
    
    function add_boton() {

        echo $this->render( $this->form_boton(), 'Alta de botones');
    
    }
    function form_boton( $idboton = '') {
        global $DB, $DB_prefijo, $aArgumentos;

        $HTML = new htmlcontainer();
        
        $HTML->add('script', array(
          'src' => 'js/picker.js' ) ) ;
        $form =& $HTML->add( 'FORM', array(
            'name'      => 'formulario',
            'method'    => 'post',
            'action'    => 'admin.php?q=botones/add_ok',
            'enctype'   => 'multipart/form-data'));


        if ( ! empty( $idboton )) {
            $comando_sql = "select * from {$DB_prefijo}botones where idboton = '{$idboton}' " ;
            $recordset = $DB->execute( $comando_sql );
            $campos =& $recordset->fields;
	    $form->add( customHTML::inputLine( 'ID Botón:', 
		new htmlobject('strong', '', $campos['idboton'] )));
	    
	    
            $form->add( 'input', array (
                'name'              => 'idboton',
                'type'              => 'hidden',
                'value'             => $campos['idboton']));
        }  else {
	    $form->add( customHTML::inputLine( 'ID Botón:', 
		new htmlobject( 'input', array (
                'name'              => 'idboton',
                'type'              => 'text',
                'value'             => '')) ));
            
        }
	$control = new htmlcontainer();

        // $form->add_text('Imagen izquierda: ');
        $control->add_object( $this->select_extension( 'idimagenizquierda', 
            (isset( $campos['idimagenizquierda'] )) ? $campos['idimagenizquierda'] : '' ,
            'png', true) );
        
        $control->add('br');
        $control->add_text( 'Nueva imagen:' );
        $control->add( 'input', array (
            'name'              => 'idimagenizquierdanew',
            'type'              => 'file',            
            'value'             => ''));
        $form->add( customHTML::inputLine( 'Imagen izquierda:', $control ) );
        
        
	$control = new htmlcontainer();
        $control->add_object( $this->select_extension( 'idimagencentro', 
            (isset( $campos['idimagencentro'] )) ? $campos['idimagencentro'] : '' ,
            'png', true) );
        $control->add('br');
        $control->add_text( 'Nueva imagen:' );
        $control->add( 'input', array (
            'name'              => 'idimagencentronew',
            'type'              => 'file',            
            'value'             => ''));
        $form->add( customHTML::inputLine( 'Imagen central:', $control ) );
        
        $control = new htmlcontainer();
        $control->add_object( $this->select_extension( 'idimagenderecha', 
            (isset( $campos['idimagenderecha'] )) ? $campos['idimagenderecha'] : '' ,
            'png', true) );
        $control->add('br');
        $control->add_text( 'Nueva imagen:' );
        $control->add( 'input', array (
            'name'              => 'idimagenderechanew',
            'type'              => 'file',            
            'value'             => ''));
	$form->add( customHTML::inputLine( 'Imagen derecha:', $control ) );
	$control = new htmlcontainer();
        
            $control->add('img', array(
                'id' => 'idimagenizquierda_img',
                'name' => 'idimagenizquierda_img'
                ));
            $control->add_text('+');                                
            $control->add('img', array(
                'id' => 'idimagencentro_img',
                'name' => 'idimagencentro_img'
                ));
            $control->add_text('+');                
            $control->add('img', array(
                'id' => 'idimagenderecha_img',
                'name' => 'idimagenderecha_img'
                ));
	$form->add( customHTML::inputLine( 'Ejemplo:', $control ) );

	$control = new htmlcontainer();

        
        $control->add_object( $this->select_extension( 'idttf', 
            (isset( $campos['idttf'] )) ? $campos['idttf'] : '' ,
            'ttf') );
	$control->add('br');
        $control->add_text( 'Nuevo Fichero TTF:' );
        $control->add( 'input', array (
            'name'              => 'idttfnew',
            'type'              => 'file',            
            'value'             => ''));
        $form->add( customHTML::inputLine( 'Fuente TrueType:', $control ) );
        
	
	$form->add( customHTML::inputLine( 'Tamaño Fuente:', customHTML::numeric( 
                'ttfsize', 
                ((isset( $campos['ttfsize'] )) ? $campos['ttfsize'] : '10'),
                true)));
        $control = new htmlcontainer() ;
	$control->add( 'input', array (
            'name'              => 'ancho',
            'type'              => 'text',            
            'value'             => (isset( $campos['ancho'] )) ? $campos['ancho'] : '' ));
	$control->add_text( ' ' );
	$control->add('em', '', '(en pixeles) 0 para ancho dinámico');
        $form->add( customHTML::inputLine( 'Ancho del Botón:', $control  ) );
	
        
        // $form->add_text('Color Texto: ');
	$control = new htmlcontainer() ;
        $control->add( 'input', array (
            'name'              => 'colortexto',
            'type'              => 'text',    
            'size'              => '6',
            'value'             => (isset( $campos['colortexto'] )) 
                ? str_pad($campos['colortexto'], 6, '0', STR_PAD_LEFT)  
                  : '000000'));
        $control->add_object( customHTML::ImageLink( 
          'images/color.gif', 
          '', 
          'Seleccionar color', 
          '', 
          "TCP.popup(document.forms['formulario'].elements['colortexto'], 0)" 
          ));
        $form->add( customHTML::inputLine( 'Color del texto:', $control  ) );
        
	$control = new htmlcontainer() ;
        
        $control->add( 'input', array (
            'name'              => 'colortransparente',
            'type'              => 'text',    
            'size'              => 6,
            'value'             => (isset( $campos['colortransparente'] )) 
              ? str_pad($campos['colortransparente'], 6, '0', STR_PAD_LEFT) 
              : 'FFFFFF'));
        $control->add_object( customHTML::ImageLink( 
          'images/color.gif', 
          '', 
          'Seleccionar color', 
          '', 
          "TCP.popup(document.forms['formulario'].elements['colortransparente'], 0)" 
          ));
	  
        $form->add( customHTML::inputLine( 'Color transparente:', $control  ) );
        
	$control = new htmlcontainer() ;
        
        
        $control->add( 'input', array (
            'name'              => 'colorfondo',
            'type'              => 'text',    
            'size'              => 6,
            'value'             => (isset( $campos['colorfondo'] )) ? 
              str_pad($campos['colorfondo'], 6, '0', STR_PAD_LEFT) 
              : 'FFFFFF'));
        $control->add_object( customHTML::ImageLink( 
          'images/color.gif', 
          '', 
          'Seleccionar color', 
          '', 
          "TCP.popup(document.forms['formulario'].elements['colorfondo'], 0)" 
          ));
	
	$form->add( customHTML::inputLine( 'Color de Fondo:', $control  ) );
        
	$control = new htmlcontainer() ;
        
        
        // $form->add_text('Correccion X: ');
        $control->add( 'input', array (
            'name'              => 'correccionx',
            'type'              => 'text',    
            'size'              => 4,
            'value'             => (isset( $campos['correccionx'] )) ? $campos['correccionx'] : ''));
        $control->add( 'i', '', ' en pixeles');
        $form->add( customHTML::inputLine( 'Corrección horizontal:', $control  ) );
        
	$control = new htmlcontainer() ;

        $control->add( 'input', array (
            'name'              => 'correcciony',
            'type'              => 'text',    
            'size'              => 4,
            'value'             => (isset( $campos['correcciony'] )) ? $campos['correcciony'] : ''));
        $control->add( 'i', '', ' en pixeles');
	$form->add( customHTML::inputLine( 'Corrección vertical:', $control  ) );
        
	$control = new htmlcontainer() ;
        
        
        $control->add( 'textarea', array (
            'name'              => 'descripcion'),
        (isset( $campos['descripcion'] )) ? $campos['descripcion'] : ''       );
	$form->add( customHTML::inputLine( 'Descripción:', $control  ) );
        
	$control = new htmlcontainer() ;
        
        $control->add( CustomHTML::botonAdmin("Grabar" , 'Grabar', '#', 
            "document.formulario.submit();" ) );
        $control->add( CustomHTML::botonAdmin('Cancelar', "Cancelar Edición", "", "window.location.href='admin.php?q=botones';") );
        $form->add( customHTML::inputLine( ' ', $control  ) );
        
	
        $form->add('script' , '', 
        "srcimagen( 'idimagenizquierda_img', '../lar/cache.fichero.' + document.formulario.idimagenizquierda.value + '.png');".
        "srcimagen( 'idimagencentro_img', '../lar/cache.fichero.' + document.formulario.idimagencentro.value + '.png');".
        "srcimagen( 'idimagenderecha_img', '../lar/cache.fichero.' + document.formulario.idimagenderecha.value + '.png');"
        );
        




        return $HTML ;
    }
    
    
    
    function show_botones() {
        global $DB, $DB_prefijo, $aArgumentos;
        $inicio = (!isset($aArgumentos[3])) ? 0 : $aArgumentos[3];
        $registros = (!isset($aArgumentos[2]) || $aArgumentos[2] < 20 ) ? 20  : $aArgumentos[2];
        // traemos los usuarios 
        $comando_sql = "select uid, idboton, descripcion from {$DB_prefijo}botones";
        $resultado = $DB->execute( $comando_sql);
         $HTML = new HtmlContainer( ) ;
        $HTML->add( 'DIV', array( 'style' => 'float: right; vertical-align: middle'), '', 
	                CustomHTML::botonAdmin( "Nuevo Botón" , 'Añadir Botón', 'admin.php?q=botones/add') );
           $tabla =& $HTML->add( 'TABLE',array( 'class' => 'ruler wide botones') );
        $tr =& $tabla->add( 'TR' );
        $tr->add('th','', '&nbsp;');
        $tr->add('th','', 'ID Botón');
        $tr->add('th','', 'Ejemplo');
		$tdClass = 'non';
        while (!$resultado->EOF) {
			$tdClass = ($tdClass === 'non') ? false : 'non' ;
	
            $tr =& $tabla->add( 'TR' , array( 'class' => $tdClass ));

            $td =& $tr->add( 'td');
            $td->add_object( CustomHTML::Boton16x16( 
            _ADM_BOTON_EDITAR , 
            "admin.php?q=botones/edit/{$resultado->fields['uid']}",  
            "Editar Boton [{$resultado->fields['idboton']}]" ) );
            if (!empty( $resultado->fields['descripcion'] )) {
            
		// TODO:!!  info del botón
	    
            }
            
            $td =& $tr->add('td', '', $resultado->fields['idboton']);
        
            $td =& $tr->add( 'td');
                $td->add_text( $this->example( $resultado->fields['idboton'],  'Texto' ));
             
            
            $resultado->MoveNext();
        }
        
        echo $this->render( $HTML, 'Administración de botones dinámicos');
                              
    }
    function select_extension( $control, $value, $extension , $preview = false ) {
        global $DB, $DB_prefijo ;
        
        $html = new htmlcontainer();
        $select =&  $html->add( 'select', array ( 
            'name'              => $control , 
            'onchange'          => ($preview === true) ?
                "srcimagen( '{$control}_img', '../lar/cache.fichero.' + this.value + '.png');" :
                False
            ));
        $comando_sql = "select idfile, original_file from {$DB_prefijo}files where category = 'botones' and original_file like'%.{$extension}' ";
        $resultado = $DB->execute( $comando_sql);
        while( !$resultado->EOF ) {
            $idfile =& $resultado->fields['idfile'];
            $original_file =& $resultado->fields['original_file'];
            $select->add( 'option', array(
                'value'         => $idfile,
                'selected'       => ( $idfile == $value ) ? true : false ),
                "{$original_file} [$idfile]");
            // para pasar el archivo al cache
            morcegocms_functions_fichero::url( $idfile ) ;
            $resultado->MoveNext();
        }
        return $html;
    
    }
    function example( $idboton, $texto ) {
        // $texto =  strtr( $texto,"àèìòùáéíóúçñäëïöüÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ", 'aeiouaeiouaeiouAEIOUAEIOUAEIOU');
        
        $file_boton = 'cache.boton.' . md5( $idboton . $texto ) . '.png' ;
        
        $url_boton = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ?
                     $GLOBALS['configCMS']->get_var('rutaweb') .  "botones/" .  md5( $idboton . $texto ) . ".png" : 
                     $GLOBALS['configCMS']->get_var('rutaweb') .  "lar/{$file_boton}" ;
        
        
        if ( !file_exists( dirname( __FILE__ ) . '/../../lar/' . $file_boton )) {
            $comando_sql = "select * from {$GLOBALS['DB_prefijo']}botones where idboton = \"{$idboton}\"";
            $recordset = $GLOBALS['DB']->execute( $comando_sql );
            if ( $recordset->EOF ) {
                $value = '<!-- boton ' . $idboton . ' no encontraddo -->';
            } else {
                $aBoton = array (
                    'nombre'             =>  $idboton ,
                    'grafico'            => 1,
                    'cache'              => 1,
                    'centro'             => morcegocms_functions_fichero::path( $recordset->fields['idimagencentro'] ),
                    'izquierda'          => morcegocms_functions_fichero::path( $recordset->fields['idimagenizquierda'] ),
                    'derecha'            => morcegocms_functions_fichero::path( $recordset->fields['idimagenderecha'] ),
                    'ttf'                => morcegocms_functions_fichero::path( $recordset->fields['idttf'] ),
                    'ttf_size'           => $recordset->fields['ttfsize'],
                    'ancho'              => $recordset->fields['ancho'],
                    'color_texto'        => $recordset->fields['colortexto'] ,
                    'color_transparente' => $recordset->fields['colortransparente'],
                    'color_fondo'        => $recordset->fields['colorfondo'],
                    'correccion_x'       => $recordset->fields['correccionx'],
                    'correccion_y'       => $recordset->fields['correcciony']
                ) ;
                $boton = new cls_boton($aBoton, $texto );
                $resultado = $boton->render_boton();
                unset( $boton);
                // print_r( $aBoton ) ;

            }
        }
         $resultado = "<img src=\"{$url_boton}?" . rand(0, 10000) . "\" border=\"0\" alt=\"{$texto}\"/>";
    return  $resultado ;
    }
    
    
}


?>
