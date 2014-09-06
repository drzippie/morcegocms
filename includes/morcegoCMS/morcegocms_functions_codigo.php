<?php
class morcegocms_functions_codigo extends morcegocms_common {
    function includePHP( $cadena ) {
        $str_out = '';
        $cadena = str_replace( "\\", "/", $cadena );
        $cadena = ( strrpos( $cadena, '/') === false ) ? 
          $cadena : substr( $cadena, - ( strrpos ( $cadena, '/') + 2 ));
        $fichero = dirname(__FILE__) . '/../' . $cadena ;
        if (file_exists( $fichero  )){
            @ob_start();
            @include_once( $fichero);
            $str_out = @ob_get_contents();
            @ob_end_clean();
        }
        return $str_out;
    }
    function url_fichero( $cadena ) {
      return '{fichero:url:' . $cadena . '}' ;
     
    
    }
    /**
    * login( $parametros) {
       por post acepta username y password:
    }
    como $parametro se le indica el idpagina destino si el login es correcto ... y devuelve un 
    mensaje de error si no es así.
    * 
    *
    *
    *
    */ 
    function login( $cadena )  {
        global $pagina;
        $str_out = '';
        $destino = (empty($cadena)) ? '?' . $pagina->Campos['idpagina'] : $cadena ;
        $username = isset($_POST['login_user']) ? $_POST['login_user'] : '';
        $password = isset($_POST['login_pass']) ? $_POST['login_pass'] : '';
        if ( !isset( $_POST['login_user'] ) || !isset( $_POST['login_pass'] ) ) {
            $str_out ='';
        } else {
            if ( empty( $username ) || empty( $password )) {
                $str_out = "El nombre de usuario y la contraseña no pueden estar vacios";
            } else {
                $GLOBALS['statsCMS']->User->login_user( $username, $password);
                if ($GLOBALS['statsCMS']->User->IsLogged() ) {
                 //  die(  '<html><head><META HTTP-EQUIV=Refresh CONTENT="0; URL='. $destino .'"></head></html>' );
                   header( "Location: {$destino}");
                   die();
                } else {
                    $str_out = "Nombre de usuario y/o contraseña incorrectos";
                }
            }
        }
        return $str_out ;
    }

    function logout( $cadena )  {
        $GLOBALS['statsCMS']->User->logout_user();
        return '' ;
    }
function engadir_hit($nada ){
        global $pagina ;
        $uid = $pagina -> Campos['uid'];
        $GLOBALS['DB'] -> execute("update " . $GLOBALS['DB_prefijo'] . "paginas set hits=hits + 1 where uid = $uid");
        return '';
    }    

    function date( $cadena )  {
	$formato = ( !empty( $cadena) ) ? $cadena  : 'd/m/Y';
	return date(  $formato );
    }
    function contenidos_hijos($cadena = ''){
        global $pagina;
        $str_out = '';
        $idpagina = (!empty($cadena)) ? $cadena : $pagina -> Campos['idpagina'];
        $uid = morcegocms_utils::uidfromidpagina($idpagina);
        
	$comandoSQL = "select idpagina from ". $GLOBALS['DB_prefijo'] . "paginas where " .
            "uidparent = {$uid} and tipo = 1 and activa = 1 " .
            ' and fecha <= ' . $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy ) 
			. " order by orden asc " ;
      
	   $resultado = $GLOBALS['DB']->execute($comandoSQL);
		while(!$resultado->EOF ) {
			$str_out .=  morcegocms_functions_codigo::Contenido($resultado->fields['idpagina']);
			$resultado->MoveNext();
		}    
        return $str_out;
    }
    function contenido($idpagina){
        
        $str_out = '<!-- bloque no encontrado -->';
        if (!empty($idpagina)){
            $contenido = new pagina($idpagina);
            if ($contenido -> Campos['idpagina'] == $idpagina){
                $t2 = new Template_morcegoCMS($contenido, 0);
                $str_out = $t2->parsear();
                unset($t2);
            }
        }
        return  $str_out;
    }
    /**
     * Funcion que nos muestra SOLO EL TEXTO de una determinada página
     * Como único parametro tiene el id de pagina
     */
    function textopagina($cadena){
        $parametros =  explode ("|", $cadena) ;
        $str_out = '';
        $uid = morcegocms_utils::uidfromidpagina( $parametros[0] );
        
        if ( $uid === false )  {
            // 
        } else {
            if (isset( $GLOBALS['statsCMS']->vars['lang'] )) {
                $comando_sql = 'select texto from '. $GLOBALS['DB_prefijo'] . 'paginas_lang where '. 
                    "uid = {$uid} and lang = " . 
                    $GLOBALS['DB']->Quote( $GLOBALS['statsCMS']->vars['lang'] ) ;
                $recordset = $GLOBALS['DB']->execute( $comando_sql ) ;
            }
            if ( !isset( $recordset->fields['texto']) ) {
            
                $comando_sql = 'select texto from '. $GLOBALS['DB_prefijo'] . 'paginas where '. 
                    "uid = {$uid}";
                // echo $comando_sql ;                    
                $recordset = $GLOBALS['DB']->execute( $comando_sql ) ;
                // print_r( $recordset );
                if ( !isset( $recordset->fields['texto']) ) {    
                    $str_out = '' ;
                } else {
                    $str_out = $recordset->fields['texto'];
                }
            
            } else {
                $str_out = $recordset->fields['texto'];
            }
        }
        return stripslashes($str_out );
    }
    function buscar(   $parametros ){
        $aTMP = explode( ':', $parametros );
        $Filtro         = (isset( $aTMP[0]) ) ? $aTMP[0] : 'index' ;
        $MaxRegistros   = (isset( $aTMP[1]) && $aTMP[1] > 0 ) ? $aTMP[1] : 25  ;
        $CadenaBusqueda = (isset($_POST['busqueda'])) ? $_POST['busqueda'] : '';
        $PrefijoURL = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ? $GLOBALS['configCMS']->get_var('rutaweb')  : $GLOBALS['configCMS']->get_var('rutaweb') . '?';
        $StrOut = '';
        if (strlen( $CadenaBusqueda ) > 2){
            $comando_sql = "select uid, uidparent, tipo, idpagina, titulo, descripcion from " .
            $GLOBALS['DB_prefijo'] . "paginas where activa = 1 " . 
            ' and fecha <= ' . $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy). 
            " and  texto like '%{$CadenaBusqueda}%' order by (LENGTH(texto) " .
            " - length( replace(texto, '{$CadenaBusqueda}', '') ) ) " ;
            $recordset = $GLOBALS['DB'] -> SelectLimit("$comando_sql", $MaxRegistros, 0) ;
            $Resultados = array();
            while (!$recordset -> EOF){
                /* si es un contenido mostraremos la página padre */
                $uid            =& $recordset->fields['uid'];
                $uidparent      =& $recordset->fields['uidparent'];
                $tipo           =& $recordset->fields['tipo'];
                $idpagina       =& $recordset->fields['idpagina'];
                $titulo         =& $recordset->fields['titulo'];
                $descripcion    =& $recordset->fields['descripcion'];
                /* Si es tipo 1 (contenido) comprobamos que el padre es realmente visible */
                if( $tipo == 1 ) {
                    $comando_sql = 'select idpagina, titulo, activa, descripcion from '.
                        $GLOBALS['DB_prefijo'] . "paginas where uid ='{$uidparent}'";
                    $recordset2 = $GLOBALS['DB']->execute( $comando_sql);
                    if ( $recordset2->fields['activa'] == 1 )  {
                        $Resultados[$recordset2->fields['idpagina']] = array ( 
                            $recordset2->fields['titulo'],
                            $recordset2->fields['descripcion']);
                    }
                } else {
                    $Resultados[$idpagina] = array( 
                        $titulo, 
                        $descripcion) ;
                }
                $recordset -> MoveNext();
            }
            foreach( $Resultados as $idpagina => $Elemento  ) {
                $StrOut .= "<li><a class=\"busqueda\" href=\"{$PrefijoURL}{$idpagina}\" title=\"{$Elemento[1]}\">$Elemento[0]</a></li>" ;
            }
            if (empty($StrOut)) {
                $StrOut = "No se ha podido encontrar ninguna página con el texto: <B>$CadenaBusqueda</B>. " ;
            } else {
                $StrOut = "<h3 class=\"Busqueda\">Resultado de la búsqueda de la cadena: $CadenaBusqueda</h3>" . $StrOut ;
            }
        }else{
            if (!empty($CadenaBusqueda)){
                $StrOut = "<h3>Debe indicar una cadena de búsqueda de 3 caracteres o más</h3>" ;
            }
        }
        return $StrOut ;
    }
    function paginas_hijas($cadena){
        global $pagina ;
        
        if ( $pagina->ok == false ) return '';
        $str_out = '';
        $aParametros = explode("|", $cadena );
        
        $idpagina = (!empty($aParametros[0])) ? $aParametros[0] : $pagina -> Campos['idpagina'];
        $estilo = (isset( $aParametros[1] ) ) ?  'class="' . $aParametros[1] . '"' : '' ;
        $uid = morcegocms_utils::uidfromidpagina($idpagina);
        if ( $uid === false ) {
            morcegocms_utils::log("ERROR;paginas_hijas; El idpagina ({$idpagina}) no existe");
            return '';
        }
                
        // determinamos el texto de las hijas
        if ( $uid == $pagina->Campos['uid'] ) {
            $textohijas =& $pagina->Campos['textohijas'];
        } else {
	    $rs = $GLOBALS['DB']->execute( 'select textohijas from  '. $GLOBALS['DB_prefijo'] . 'paginas ' .
		' where uid = ' . $uid  );
           
            $textohijas = (isset( $fields['textohijas'] )) ? $fields['textohijas'] : '';
        }
        $textohijas = (!empty($textohijas)) ? "<h4 class='textohijas'>{$textohijas}</h4>" : '';
        
	$rs = $GLOBALS['DB']->execute( 'select idpagina, titulo from '. $GLOBALS['DB_prefijo'] . 'paginas  ' .
		" where uidparent = {$uid} and tipo = 0 and activa = 1 and uid != 0 " .
		' and fecha <= ' . $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy) . 
		" order by orden asc");
        if ( !$rs->EOF ) {
		while( !$rs->EOF ) {
			$str_out .= sprintf( '<div %s><a href="?%s">%s</a></div>'."\n",
				$estilo ,
				$rs->fields['idpagina'],
				$rs->fields['titulo']);
			$rs->MoveNext();
		}
            $str_out = (!empty($str_out)) ? sprintf( '%s %s',
                $textohijas,        
                $str_out) : '';
        }
        return $str_out;
    }
    /**
     * Nos muestra una linea (con enlaces) de los padres-abuelos-.. de la página
     * en la que estamos.
     */
    function familia_linea(){
	global $aArgumentos ;
	global $pagina;
        $prefijourl = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ? $GLOBALS['configCMS']->get_var('rutaweb') : $GLOBALS['configCMS']->get_var('rutaweb') .  '?';
	$uid = $pagina->Campos['uid'];
    if ( $uid == 0 ) { return '' ; }
    $uidparent = $uid ;
    $idbusqueda = $uid;
    $familia = array();
    $comando_sql = "select uidparent from " . $GLOBALS['DB_prefijo'] . "paginas where uid = $uid";
    $recordset = $GLOBALS['DB']->execute($comando_sql) ;
    if ($recordset -> RecordCount() == 0){
        $uidparent = 0 ;
    }else{
        $idbusqueda = $recordset -> fields['uidparent'];
        if ($idbusqueda == 0){
            $uidparent = 0;
        }
    }
    $recordset -> close();
    $separador = '';
    while ($uidparent != 0){
        $comando_sql = "select activa, uidparent, idpagina, titulo, descripcion from " . $GLOBALS['DB_prefijo'] . "paginas where uid = $idbusqueda";
        $recordset = $GLOBALS['DB']->execute($comando_sql) ;
        if ($recordset -> RecordCount() == 0){
            $uidparent = 0 ;
        }else{
            $uidparent         =& $recordset -> fields['uidparent'];
            $activa            =& $recordset -> fields['activa'];
            $idpagina          =& $recordset -> fields['idpagina'];
            $titulo            =& $recordset -> fields['titulo'];
            $descripcion       =& $recordset -> fields['descripcion'];
            $idbusqueda        =& $recordset -> fields['uidparent'];
            if ( $activa == true ) {
                $href= $prefijourl  . $idpagina ;
                $familia[] = "<a href=\"{$href}\" class=\"lista\" title=\"{$descripcion}\">$titulo</a>";
            }
            }
        }
    $familia[] = "<a href='" . $GLOBALS['configCMS']->get_var('rutaweb') .  "' class=\"lista\" title=\"Inicio\">Inicio</a>";
    return implode( " :: ", array_reverse($familia) );
    }
    function sendmail($cadena){
     $aCadena =  explode(':', $cadena );
     $to = $aCadena[0] ;
     $texto_ok = (isset($aCadena[1])) ? $aCadena[1] : '' ;
     $from = (isset($aCadena[2])) ? $aCadena[2] : 'webmaster@' . $_SERVER['SERVER_NAME'] ;
     $str_out = '';
     $mensaje = '';
     // print_r( $_POST );
     reset ($_POST);
     while (list ($key, $val) = each ($_POST)){
         if (substr($key, 0, 5) == 'mail_'){
            switch ( $key ) {
                case 'mail_to': 
                    $to = $val ; 
                    break;
                case 'mail_from':
                    $from = $val ;
                    break;
                default:
                    $mensaje .= '[' . substr( $key, 5 - strlen( $key ) ) . ']: ' . $val . "\n"  ;
            }
            
        }
    }
     if (!empty($mensaje)){
         $mensaje .= "\n";
         $mensaje .= str_repeat( '-', 30) . ' + Info ' . str_repeat( '-', 30) .  "\n" ;
        // $mensaje .= 'MorcegoCMS, versión: '.  _MORCEGO_VERSION . "\n" ;
         $mensaje .= 'Servidor Web: '.  $_SERVER['SERVER_NAME'] .  "\n" ;
         $mensaje .= 'Url: ' . $_SERVER["PHP_SELF"] . '?' .  $GLOBALS['pagina']->Campos['idpagina'] . "\n";
         $mensaje .= 'Fecha y Hora del servidor : ' . date('d/m/Y H:i:s') . "\n";
         $mensaje .= 'IP Origen : ' . $GLOBALS['statsCMS']->ip_client . "\n";
         $mensaje .= str_repeat( '-', 68) . "\n" ;
         //  @ini_set( 'sendmail_from', $from );
        $headers = "Return-Path: <{$from}>\r\n". 
            "From: <{$from}>\r\n" .
            "X-Mailer: MorcegoCMS v." . _MORCEGO_VERSION  . " \r\n"   ; //mailer
         if (mail ($to, 'Correo Web: Enviado desde [' . $GLOBALS['pagina']->Campos['idpagina'] . ']', $mensaje, $headers)){
             
            if ( empty( $texto_ok )) {
                $str_out = '<h5>El mensaje ha sido enviado satisfactoriamente</h5>';
            } else {
               $str_out = '<h5>' . $texto_ok . '</h5>';
            }
        }else{
             $str_out = '<h5>No se ha podido enviar el email, compruebe todos los datos</h5>';
        }
    }
     return $str_out  ;
    }

    function tag_imagen($idpagina = ''){
        global $pagina;
        if (empty($idpagina)){
         $idpagina = $pagina -> Campos['idpagina'] ;
         }
        $url_imagen = $this->url_imagen($idpagina) ;
        $size = getimagesize(dirname(__FILE__) . '/../../' . $url_imagen) ;
        
        $str_out = "<img src=\"{$url_imagen}\" border=\"0\" {$size[3]} alt=\"Imagen: {$this->Campos['titulo']}\"/>";
        return $str_out;
    }
    function url_imagen($idpagina = ''){
     global $pagina;
     if (empty($idpagina)){
         $idpagina = $pagina -> Campos['idpagina'] ;
         }
     $str_out = '';
     $comando_sql = "select img_content, img_mimetype from {$GLOBALS['DB_prefijo']}paginas where idpagina = '{$idpagina}'" ;
     $recordset = $GLOBALS['DB']->execute($comando_sql) ;
     if (isset($recordset -> fields['img_mimetype']) && !empty($recordset -> fields['img_mimetype'])){
         $nombre_imagen = '/cache.imagen.' .
         $idpagina . '.' . $GLOBALS['varsCMS'] -> extension_from_mimetype($recordset -> fields['img_mimetype']);
         $path_imagen = dirname(__FILE__) . '/../../' . $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
         $url_imagen = $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
         if (!file_exists($url_imagen)){
             $comando_sql = "select img_content, img_align from {$GLOBALS['DB_prefijo']}paginas where idpagina = '{$idpagina}' ";
             $recordset = $GLOBALS['DB']->execute($comando_sql);
             $content = $recordset -> fields['img_content'];
             $hf = fopen($path_imagen, 'w') ;
             fwrite($hf, $content);
             fclose($hf) ;
             }
         $str_out = $url_imagen;
         }
     return $str_out ;
    }


    function tag_icono($idpagina = ''){

        global $pagina;
        if (empty($idpagina)){
         $idpagina = $pagina -> Campos['idpagina'] ;
         }
        $url_icono = $this->url_icono($idpagina) ;
        $size = getimagesize(dirname(__FILE__) . '/../../' . $url_icono) ;
        $str_out = "<img src=\"{$url_icono}\" border=\"0\" {$size[3]} alt=\"Imagen: {$this->Campos['titulo']}\"/>";
        return $str_out;
    }
    
function url_icono($idpagina = ''){
     global $pagina;
     if (empty($idpagina)){
         $idpagina = $pagina -> Campos['idpagina'] ;
         }
     $str_out = '';
     $comando_sql = "select icono_content, icono_mimetype from {$GLOBALS['DB_prefijo']}paginas where idpagina = '{$idpagina}'" ;
     $recordset = $GLOBALS['DB']->execute($comando_sql) ;
     if (isset($recordset -> fields['icono_mimetype']) && !empty($recordset -> fields['icono_mimetype'])){
         $nombre_imagen = '/cache.icono.' .
         $idpagina . '.' . $GLOBALS['varsCMS'] -> extension_from_mimetype($recordset -> fields['icono_mimetype']);
         $path_imagen = dirname(__FILE__) . '/../../' . $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
         $url_imagen = $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
         if (!file_exists($url_imagen)){
             $comando_sql = "select icono_content, icono_align from {$GLOBALS['DB_prefijo']}paginas where idpagina = '{$idpagina}' ";
             $recordset = $GLOBALS['DB']->execute($comando_sql);
             $content = $recordset -> fields['icono_content'];
             $hf = fopen($path_imagen, 'w') ;
             fwrite($hf, $content);
             fclose($hf) ;
             }
         $str_out = $url_imagen;
         }
     return $str_out ;
    }
      function tag_fichero( $idfile)  {
        return '{fichero:tag:' .  $idfile  . '}' ;
    }
function path_fichero($cadena){
     return '{fichero:path:' .  $cadena  . '}' ;
     }
          
    function descripcion_fichero($idfile){
     global $pagina;
     $str_out = '';
     if (empty($idfile)){
         return '';
         }
     $comando_sql = "select description from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}'" ;
     $recordset = $GLOBALS['DB']->execute($comando_sql) ;
     if (isset($recordset -> fields['description'])){
         $str_out = & $recordset -> fields['description'];
         }
     return $str_out ;
    }
    function nombre_fichero($idfile){
    
    global $pagina;
     $str_out = '';
     if (empty($idfile)){
         return '';
         }
     $comando_sql = "select original_file as resultado  from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}'" ;
     $recordset = $GLOBALS['DB']->execute($comando_sql) ;
     if (isset($recordset -> fields['resultado'])){
         $str_out = $recordset -> fields['resultado'];
         }
     return $str_out ;
    }
    function add_content( $parameters  ) {

        global $pagina;
        $aParameters = explode('|', $parameters );
        // creamos los parámetros 
        $idpagina = (!isset( $aParameters[0]) || empty($aParameters[0])) ? $pagina->Campos['idpagina'] : $aParameters[0];
        $template = (!isset( $aParameters[1]) || empty($aParameters[1])) ? 'content_base.html' : $aParameters[1] ;
        $destino  = (!isset( $aParameters[3]) || empty($aParameters[3]) ) 
            ? $pagina->Campos['href'] 
            : $aParameters[3] ;
	$hasCaptcha = (!isset( $aParameters[4]) || empty($aParameters[4])) ? '0' : $aParameters[4] ;
      
       $texto = '';
        $descripcion = '';
        $titulo = '';
        $variables = '';

        foreach ( $_POST as $variable => $valor ) {
            if ( substr( $variable, 0, 8 ) == 'content_' ){
                switch ( $variable ) {
                    case 'content_texto' :
                        $texto = strip_tags( $valor, '<a><br><i><li><ul><b><u><strong><p>');
                        echo $texto ;
                        $texto = ereg_replace('\{|\}', '', $texto );
                        break;
                    case 'content_descripcion' :
                        $descripcion = strip_tags( $valor);
                        $descripcion = ereg_replace('\{|\}', '', $descripcion );
                        break;
                    case 'content_titulo' :
                        $titulo = strip_tags( $valor);
                        $titulo = ereg_replace('\{|\}', '', $titulo );
                        break;
                    default:
                        $valor = strip_tags( $valor);
                        $valor = ereg_replace('\{|\}', '', $valor );
                        $variable = substr( $variable, 8, strlen( $variable) - 8 );
                        $variables .= "{$variable}={$valor}\n";
                        break;
                }
            }
        }
	
	if ($hasCaptcha == '1' ) {
		if ( isset(  $_SESSION['runtime']['captcha']    ) ) {
			$captcha = '-1';
			if ( isset( $_POST['content_captcha' ]) ) {
				$captcha = $_POST['content_captcha' ] ;
			}
			if ( $captcha !=  $_SESSION['runtime']['captcha']  ) {
				unset(  $_SESSION['runtime']['captcha']  ) ;
				return ' ';
			} 
		} else {
			return '' ;
		}
		unset(  $_SESSION['runtime']['captcha']  ) ;
		
	}
	
        $variables .= "ip={$GLOBALS['statsCMS']->ip_client}";
        $str_out = '';
        if (!empty( $texto ) ) {
            $comando_sql = "select uidroot, uid, idgroup from {$GLOBALS['DB_prefijo']}paginas where idpagina='{$idpagina}'";
            $recordset = $GLOBALS['DB']->execute( $comando_sql );
            $uid = $recordset->fields['uid'];
            $uidroot = ($recordset->fields['uidroot'] == 0) ? $uid : $recordset->fields['uidroot'] ;
            $idgroup = (!isset( $aParameters[2]) || empty($aParameters[2])) ?  $recordset->fields['idgroup'] : $aParameters[2] ;
            $comando_sql = "select max(orden)+1 as orden from {$GLOBALS['DB_prefijo']}paginas where uidparent = {$uid} and tipo = 1 ";
            $recordset = $GLOBALS['DB']->execute( $comando_sql );
            if (!isset($recordset->fields['orden'])) {
                $orden = 1;
            } else {
                $orden = $recordset->fields['orden'];
            }
            $comando_sql = "select max(uid)+1 as uid from {$GLOBALS['DB_prefijo']}paginas";
            $recordset = $GLOBALS['DB']->execute( $comando_sql );
            $newuid = $recordset->fields['uid'];
            $GLOBALS['DB']->Replace($GLOBALS['configCMS'] -> get_var('dbprefijo') . 'paginas ' , 
            array( 
            'uid'       => $newuid,
            'idpagina'  => "{$idpagina}_{$orden}_{$uid}",
            'uidparent' => $uid,
            'uidroot'   => $uidroot,
            'template'  => $template,
            'texto'     => $texto,
            'variables' => $variables,
            'tipo'      => 1,
            'activa'    => 1,
            'titulo'    => $titulo,
            'idgroup'   => $idgroup,
            'orden'     => $orden,
            'fecha' => $GLOBALS['DB']->DBTimeStamp( $GLOBALS['configCMS']->hoy ) 
            ),
            'uid',
            true    );
            morcegocms_utils::EmptyCacheObjects();
            Header( "Location: {$destino}" );
            die();
        }
        return  '';
    }
   function enlace($cadena){
     $ParametrosFuncion = explode('|', $cadena);
     return sprintf('<a href="%s" %s %s >%s</a>',
         $ParametrosFuncion[0],
         (substr($ParametrosFuncion[0], 0, 7) == 'http://') ? 'target="_blank"' : '' ,
         (isset($ParametrosFuncion[2])) ? "class=\"{$ParametrosFuncion[2]}\"" : '' ,
         (isset($ParametrosFuncion[1])) ? $ParametrosFuncion[1] : $ParametrosFuncion[0]
        ) ;
    }
function lista_hijas($cadena){
     global $pagina ;
     $str_out = '';
     $ParametrosFuncion = explode('|', $cadena) ;
    
     if (isset($ParametrosFuncion[0]) && !empty($ParametrosFuncion[0])){
         $idpagina = $ParametrosFuncion[0];
         }else{
         $idpagina = $pagina -> Campos['idpagina'];
        
         }
     $uid = morcegocms_utils::uidfromidpagina($idpagina) ;
     if (!empty($uid)){
         $comando_sql = 'select idpagina, titulo from ' . $GLOBALS['DB_prefijo'] . 'paginas' ;
         $filtro_sql = "where uidparent =  $uid and tipo = 0 and activa = 1 " . 
         ' and fecha <= ' . $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy). 
         " order by orden asc " ;
         $recordset = $GLOBALS['DB']->execute("$comando_sql $filtro_sql") ;
         while (!$recordset -> EOF){
            
             $str_out .= sprintf('<div %s><a href="?%s">%s</a></div>' . "\n",
                 (isset($ParametrosFuncion[1])) ? 'style="' . $ParametrosFuncion[1] . '"' : ' ',
                 $recordset -> fields['idpagina'] ,
                 $recordset -> fields['titulo']) ;
             $recordset -> MoveNext();
             }
         }
     return $str_out;
    }
function random_content( $idpagina ) {
        
        $uid = morcegocms_utils::uidfromidpagina( $idpagina );
        if( $uid == false ) {
            return  '<!--empty-->';
        }
        $str_out = '';
	$rs = $GLOBALS['DB']->execute(  "select count(*) as total from  {$GLOBALS['DB_prefijo']}paginas " .
	" where uidparent = {$uid} and tipo = 1 and activa = 1 " . 
	' and fecha <= ' . $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy) );
	$totalContenidos = $rs->fields['total']  ;
	if ( $totalContenidos > 0 ) {
		$numRegistroAleatorio = rand(1,  $totalContenidos);
		$rs = $GLOBALS['DB']->selectLimit(  "select idpagina from  {$GLOBALS['DB_prefijo']}paginas " .
			" where uidparent = {$uid} and tipo = 1 and activa = 1 " . 
			' and fecha <= ' . $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy) 
			, 1, $numRegistroAleatorio );
		$str_out .= $this->Contenido( $rs->fields['idpagina']);
	    
	    
	}
        return $str_out;
    }   
function random_line( $idfile ) {
        $str_out = '';
        $comando_sql = "select content from {$GLOBALS['DB_prefijo']}files where idfile = '{$idfile}'";
        $oResultado = $GLOBALS['DB']->execute( $comando_sql );
        if (isset( $oResultado->fields['content'])) {
            $oLineas = explode ("\n", $oResultado->fields['content']);
            $str_out = $oLineas[rand(0, count( $oLineas ) - 1 )];
            unset( $oResultado );
        }
        return $str_out;
    }

function roll_over( $parametros )  {
        
        $aParametros = explode( '|', $parametros );
        $idpagina = $aParametros[0];
        $align = (isset( $aParametros[1])) ?$aParametros[1] : 'right';
        if ( empty( $idpagina ) ) {
            global $pagina;
           $idpagina =  $pagina->Campos['idpagina'];
        }
        $str_out = sprintf(
            '<img src="{pagina:url_imagen}" name="rollover_%s"  alt="%s"' .
                "onmouseover=\"this.src='{pagina:url_icono}'\" ".
                "onmouseout=\"this.src='{pagina:url_imagen}'\" ".
                ' border="0" align="%s" />',
            $idpagina,
            $GLOBALS['pagina']->Campos['titulo'],
            $align
        );
        return $str_out;
    }


}


?>