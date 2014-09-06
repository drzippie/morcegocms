<?php
class adm_config  extends adm_class  {
    function adm_config(  $html  ) {
        $this->oHtml  = $html ;

      if (isset( $_POST['accion'] ) && $_POST['accion'] == 'edit_config' ) {
        $HTML = new htmlcontainer();
        $HTML->add_text( $this->mod_config() ) ;
        
      } else {
        $HTML = new htmlcontainer();
        $HTML->add_text( $this->show_config() ) ;
        
      }
      echo $this->render( $HTML , 'Configuración') ;
    }

    function show_config( $cadena = '') {
      $str_out = '';
      /*
      Esta función nos mostrará la configuración de la web
      */
      global $configCMS;
      $IcoModificar = "<img src=\"" . _ADM_BOTON_GRABAR  ."\" height=\"16\" width=\"16\" align=\"middle\">";
      $titulo_seccion = '<tr><th colspan="2">%s</th></tr>';
      $elemento = '<tr><td>%s</td><td><input type="text" name="%s" value="%s"/>'.
          "</td></tr>\n";
      $elemento2 = '<tr><td>%s</td><td><input type="text" name="%s" value="%s"/>'.
          "<a href=\"javascript:change_conf('%s', %s );\" title=\"Modificar Valor\">{$IcoModificar}</a></td></tr>\n";
          
      $elemento_pwd = '<tr><td>%s</td><td><input type="password" name="%s" value="%s"/></td></tr>' . "\n";
      $elemento_select = '<tr><td>%s</td><td>%s</td></tr>'  . "\n";
      $elemento_select2 = '<tr><td>%s</td><td>%s' .
      " <a href=\"javascript:change_conf('%s', %s );\" title=\"Modificar Valor\">{$IcoModificar}</a> ".
      "</td></tr>\n";
      $elemento_textarea = '<tr><td>%s</td><td><textarea rows="6" cols="60" name="%s">%s</textarea></td></tr>'."\n";
      $elemento_textarea2 = '<tr><td>%s</td><td><textarea rows="6" cols="60" name="%s">%s</textarea>' .
      " <a href=\"javascript:change_conf('%s', %s );\" title=\"Modificar Valor\">{$IcoModificar}</a></td></tr>\n";
      
      
        //$str_out .= '<h3>Configuración de la web</h3><BR>';
       $str_out = '';
      
	$html = new htmlContainer() ;
	$divSolapas =& $html->add('div', array('id' => 'divSolapas' ));
		
	$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
	$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Base de datos'  ));
	$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
	$contenidoSolapa =& $div2->add('DIV') ;
		
			if ( !empty( $cadena )) {
			$str_out = "<span class='tituloerror'>{$cadena}</span>";
			}
			$str_out .= '<form action="admin.php?q=edit_config" method="POST" name="formulario" >';
			$str_out .= '<table cellspacing="0" cellpadding="0"  class="config">';
			$str_out .= '<input type="hidden" name="accion" value="edit_config"> ';
			$str_out .= sprintf( $titulo_seccion  , 'Acceso a Datos' );
			$str_out .= sprintf( $elemento_select, 'Tipo de Base de Datos', $this->html_select_dbtipo( $configCMS->get_var('dbtipo') ));
			$str_out .= sprintf( $elemento, 'Servidor', 'dbservidor', $configCMS->get_var('dbservidor') );
			$str_out .= sprintf( $elemento, 'Usuario', 'dbusuario', $configCMS->get_var('dbusuario') );
			$str_out .= sprintf( $elemento_pwd, 'Contraseña', 'dbpassword', '');
			$str_out .= sprintf( $elemento_pwd, 'Repetir Contraseña', 'dbpassword2', '');
			$str_out .= sprintf( $elemento, 'Base de Datos', 'dbbasedatos', $configCMS->get_var('dbbasedatos') );
			$str_out .= sprintf( $elemento, 'Prefijo de Tablas', 'dbprefijo', $configCMS->get_var('dbprefijo') );
			$str_out .= '</table>';
			$botones = new htmlcontainer();
			$botones->add( CustomHTML::botonAdmin("Modificar" , 'Modificar Configuración' , '', 'submit()'))  ;
			$str_out .= $botones->render();
			$str_out .= '</form>';
	$contenidoSolapa->add_text( $str_out ) ;

	$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
	$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Configuración'  ));
	$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
	$contenidoSolapa =& $div2->add('DIV') ;
	
	
		$str_out = '<form action="" method="POST" name="form2" >';
		$str_out .= '<table  cellspacing="0" cellpadding="0" class="config">';
		$str_out .= sprintf( $titulo_seccion  , 'General' ); 
		$str_out .= sprintf( $elemento2, 'Prefijo Título Web', 'prefijotituloweb', $configCMS->get_var('prefijotituloweb'), 'prefijotituloweb', 'document.form2.prefijotituloweb.value' );

		$str_out .= sprintf( $titulo_seccion  , 'Caché' );
		$str_out .= sprintf( $elemento2, 'Ruta de la caché', 'cachepath', $configCMS->get_var('cachepath'), 'cachepath',
		'document.form2.cachepath.value');
		$str_out .= sprintf( $elemento_select2,  'Tiempo de Caché', $this->html_select_cache_timming($configCMS->get_var('cachetimming') ), 'cachetimming', 'document.form2.cachetimming.value');
		$str_out .= sprintf( $titulo_seccion  , 'Funciones' );
		$str_out .= sprintf( $elemento2, 'Prefijo Funciones', 'prefijofunciones', $configCMS->get_var('prefijofunciones'),
		'prefijofunciones', 'document.form2.prefijofunciones.value');
		$includes = implode( "\n", explode( ';',$configCMS->get_var('includes')));
		$str_out .= sprintf( $elemento_textarea2, 'Includes<BR><I>Ficheros separados por INTRO</I>', 'includes', 
		$includes, 'includes', 'document.form2.includes.value' );
		$str_out .= sprintf( $elemento_select2,  'Mod_Rewrite', 
		$this->html_select_boolean($configCMS->get_var('mod_rewrite'), 'mod_rewrite' ), 
		'mod_rewrite', 'document.form2.mod_rewrite.value');
		$str_out .= sprintf( $elemento2, 'Ruta Web', 'rutaweb', $configCMS->get_var('rutaweb'),
		'rutaweb', 'document.form2.rutaweb.value');
		$str_out .= sprintf( $elemento_select2,  'Idioma por defecto', 
		$this->html_select_lang($configCMS->get_var('lang'), 'lang' ), 
		'lang', 'document.form2.lang.value');
		$str_out .= sprintf( $elemento_select2,  'Soporte GD 2 (TrueColor)', 
		$this->html_select_boolean($configCMS->get_var('GD2'), 'GD2' ), 
		'GD2', 'document.form2.GD2.value');                
		$str_out .= '</table>';
		$str_out .= '</form>';
	
   	$contenidoSolapa->add_text( $str_out ) ;
      return $html->render();
            
    }
    function mod_config() {
        global $configCMS;
        If ( !empty($_POST['dbpassword']) && ($_POST['dbpassword'] != $_POST['dbpassword2']))   {
            // Mostramos error ... contraseña diferente.
            return $this->show_config('La contraseña no es igual en los 2 casos');
            
            // die();
        } else { 
            // ponemos ahora la contraseña en una variable
            if (!empty($_POST['dbpassword'])) {
                $password = $_POST['dbpassword'];
            } else {
                $password = $configCMS->get_var('dbpassword');
            }
            $password_crypt = $configCMS->crypt->encrypt( $password);

            // comprobaciones !!!
            // el servidor de datos!
            $DB2  = @ADONewConnection($_POST['dbtipo' ] );
            $resultado_conexion = @$DB2->Connect(
                $_POST['dbservidor'], 
                $_POST['dbusuario'], 
                $password, 
                $_POST['dbbasedatos']);
            if (!$resultado_conexion) {
                return  $this->show_config("El servidor de datos no es accesible compruebe los datos.");
                // die();
            }
            // directorio del cache 
            /* if (!is_dir( dirname( __FILE__) . '/../../' . $_POST['cachepath'])) {
                $this->show_config("El directorio para la caché no existe.");
                die();
            }
            */
            /*
            // directorio de las plantillas
            if (!is_dir( dirname( __FILE__) . '/../../' . $_POST['templatespath'])) {
                $this->show_config("El directorio de las plantillas no existe.");
                die();
            }
            */


            $cadena = "; <?php die('[Registro de Seguridad]:Intento de Acceso no autorizado registrado.'); /*
[datos]
dbtipo=\"{$_POST['dbtipo']}\"
dbservidor=\"{$_POST['dbservidor']}\"
dbusuario=\"{$_POST['dbusuario']}\"
dbpassword=\"{$password_crypt}\"
dbbasedatos=\"{$_POST['dbbasedatos']}\"
dbprefijo=\"{$_POST['dbprefijo']}\"
; */ ?>
";

/*        if (! copy ( dirname( __FILE__) . '/../../includes/config.ini.php' , 
            dirname( __FILE__) . '/../../includes/config.ini.old.php' )) {
            $this->show_config("Compruebe los permisos, no se puede realizar backup de la configuración.");
            die();
        }
*/
        $hf = fopen( dirname( __FILE__) . '/../../includes/config.ini.php', 'w' );
        fwrite( $hf, $cadena ) ;
        fclose( $hf);
        global $configCMS ;
        $configCMS = new config_morcegocms;
        return $this->show_config("La configuracion de acceso a datos ha sido grabada");
        // die();
        }
        
        
        
        
    }
    
    function html_select_cache_timming( $value ) {
    
        $aValores = array( '0'  => 'Sin Caché',
            '60'        => '1 Minuto',
            '300'       => '5 Minutos',
            '1500'      => '15 Minutos',
            '3000'      => '30 Minutos',
            '6000'      => '1 Hora',
            '12000'     => '2 Horas',
            '30000'     => '5 Horas',
            '86400'     => '1 Día' );
        $str_out = '<select name="cachetimming">';
        while (list ($key, $val) = each ($aValores)) {
            $str_out .= "<option value='{$key}'";
            if ( "$value" == $key ) {
                $str_out .= ' selected ';
            }
            $str_out .= ">{$val}</option>";
        }
        return $str_out . '</select>';
    }
    function html_select_dbtipo( $value ) {
    
        $aValores = array( 'MySQL'  => 'MySQL');
        $str_out = '<select name="dbtipo">';
        while (list ($key, $val) = each ($aValores)) {
            $str_out .= "<option value='{$key}'";
            if ( "$value" == $key ) {
                $str_out .= ' selected ';
            }
            $str_out .= ">{$val}</option>";
        }
        return $str_out . '</select>';
    }   
    function html_select_boolean( $value, $control ) {
    
        $aValues = array( 'SI'  => 'true',
            'NO' => 'false');
        $str_out = "<select name=\"{$control}\">";
        while (list ($key, $val) = each ($aValues)) {
            $str_out .= "<option value='{$val}'";
            if ( "$value" == $val ) {
                $str_out .= ' selected ';
            }
            $str_out .= ">{$key}</option>";
        }
        return $str_out . '</select>';
    }
    function html_select_lang( $value, $control ) {
        $aValues = $idiomas = morcegocms_lang::get_array_lang();
        $str_out = "<select name=\"{$control}\">";
        while (list ($key, $val) = each ($aValues)) {
            $str_out .= "<option value='{$key}'";
            if ( "$value" == $key ) {
                $str_out .= ' selected ';
            }
            $str_out .= ">{$val}</option>";
        }
        return $str_out . '</select>';
    }

   
}    
    
    

?>