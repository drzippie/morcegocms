<?php
/**
 * @package Core
 * @author Antonio Cortés <zippie@dr-zippie.net> 
 * @copyright Copyright &copy; 2002-2005 Antonio Cortés
 * @license BSD
 * @version 1.4.0 
 * @global object $DB objeto de conexión de adodb
 * @global object $ADODB_FETCH_MODE para adodb
 * @global string $DB_prefijo prefijo de las tablas del sitio
 * @global string $prefijo_funciones prefijo de las funciones de usuario del sitio
 * @global object $configCMS objeto que contiene la configuración del sitio
 * @global object $statsCMS objeto que contiene información dinámica sobre el cliente (navegador)
 * @global object $varsCMS objecto con funciones y datos internos, como tipos de ficheros, comprobaciones ...
 *
 * Include principal del motor, en el se cargan todos los archivos necesarios y configuración del site
 * 
 * Modificaciones:
 *   2005.04.27: Se han eliminado las estadíscas internas de uso (phpOpenTracker), no eran operativas. Ni formaban parte realmente del motor.
 *  [...] 
 *   2003.05.02 :  Se ha utilizado (condicion)? valor1  : valor2 ; para asignación de valores
 * 
 *   2003-05-12: cache: El identificador de pagina cacheada ahora se determina mediante la funcion serialize y
 *   2003-05-12: cache: El identificador de pagina cacheada ahora se determina mediante la funcion serialize y
 *               no realizando un print_r.
 * 
 *   2003-05-12: Se le ha indicado un nuevo parametro en el constructor de la clase páginas, 
 *              el tercero, por defecto igual a false, si true entonces no carga los valores 
 *              de la página hasta el momento del parseo. De esta forma se optimiza el método 
 *              de cacheado de páginas.
 *   2004-06-24: Añadido la función thumbnail a las páginas ... 
 *   2004.07.19 :  Añadido un array global para optimizar consultas de ficheros 
 *                     ( Mejora el uso de archivos repetidos {codigo:url_fichero} ).
 *                      $GLOBALS['configCMS']->variables['files']
 *
 */
 /*
    para grabar un error o mensaje en el log pondremos: 
        morcegocms_utils::log('el mensaje');
*/
define( '_MORCEGO_VERSION', '1.7.6');
define( '_MORCEGO_COPYRIGHT', '&copy; 2002-2006 Antonio Cortés (Dr Zippie antonio#antoniocortes.com )  ');
define( '_MORCEGO_SEND_EMAIL_ERROR', false );
define( '_MORCEGO_EMAIL_ERROR', 'antonio@antoniocortes.com');
define( '_MORCEGO_CACHE_OBJECTS', false ); 
define( '_MORCEGO_TEMPLATE_MAX_ITERATIONS', 15 ); // Numero máximo de veces que "parseará" la plantilla
define( '_MORCEGO_MAX_SESSION_TIME', 3600 );
/*  
Activa la compresión del html (quita [enters] dobles espacios, tabuladores) Consumo de CPU y memoria alto 
*/
define( '_MORCEGO_DB_LOG', false  );
define( '_MORCEGO_SESSIONS', true );
/*  El Include del adoDB */
include('adodb/adodb-errorhandler.inc.php'); 
include_once('adodb/adodb.inc.php');

/*
 Establecemos el valor de Query String, solucionando problemas con mod_security
*/
if ( isset( $_GET['q'] ) ) {
	$_SERVER["QUERY_STRING"] = urldecode( $_GET["q"] );
} else {
	$_SERVER["QUERY_STRING"] = urldecode( $_SERVER["QUERY_STRING"] );
}


/**
 * @package Core
 * @author Antonio Cortés <zippie@dr-zippie.net> 
 * @copyright Copyright &copy; 2002-2005 Antonio Cortés
 * @version 1.2.5 
 *
 *  Clase base para las funciones del motor 
*/
class morcegocms_common {
	/**
	*  Constructor
	*/ 
	function morcegocms_common() { }
	/**
	*  Devuelve un objeto de la caché de objetos (base de datos objects)
	*/ 
	function get_idobject( $idobject) {
		if ( get_class( $this ) == 'config_morcegocms' ) {
			$comando_SQL = "Select object from " . $this->get_var('dbprefijo'). 
				"objects where idobject=\"{$idobject}\"" ;
			$recordset = $this -> DB -> Execute( $comando_SQL );
			return (!isset($recordset->fields['object'])) ? false : $recordset->fields['object'];
		} else {
			$comando_SQL = "Select object from " . 
			$GLOBALS['configCMS'] -> get_var('dbprefijo') . "objects where idobject=\"{$idobject}\"" ;
			$recordset = $GLOBALS['configCMS'] -> DB -> Execute( $comando_SQL );
			return (!isset($recordset->fields['object'])) ? false : $recordset->fields['object'];
		}
	}
	/**
	*  Graba un objeto serializado en la caché
	*/ 	
	function save_serialized( $idobject) {
		if ( get_class( $this ) == 'config_morcegocms' ) {
			$this -> DB -> Replace(
			$this -> get_var('dbprefijo') . 'objects ' , 
				array(
					'idobject' => $idobject,
					'object' => serialize($this),
					'date' => $this -> DB -> DBTimeStamp(time())),
					'idobject',
					true    );    
		} else {
			$GLOBALS['configCMS'] -> DB -> Replace(
			$GLOBALS['configCMS'] -> get_var('dbprefijo') . 'objects ' , 
				array( 'idobject' => $idobject,
					'object' => serialize($this),
					'date' => $GLOBALS['configCMS'] -> DB -> DBTimeStamp(time())),
					'idobject',
					true    );    
		}
	} 
	/**
	*  Graba un objeto (sin serializar) en la caché
	*/ 	
	function save_unserialized( $idobject, $value) {
		if ( get_class( $this ) == 'config_morcegocms' ) {
			$this-> DB ->Replace($this -> get_var('dbprefijo') . 'objects ' , 
				array( 'idobject' => $idobject,
				'object' => $value,
				'date' => $this->DB->DBTimeStamp( time()) ),
				'idobject',
				true    );    
		} else {
			$GLOBALS['DB']->Replace($GLOBALS['configCMS'] -> get_var('dbprefijo') . 'objects ' , 
				array( 'idobject' => $idobject,
				'object' => $value,
				'date' => $GLOBALS['DB']->DBTimeStamp( time()) ),
				'idobject',
				true    );    
		}
	}
}
/**
 * @package Core
 * @author Antonio Cortés <zippie@dr-zippie.net> 
 * @copyright Copyright &copy; 2002-2005 Antonio Cortés
 * @license BSD
 * @version 1.2.5 
 *
 * Clase de gestión de Páginas (Capa V)
 *
 * La clase pagina guarda todos los valores de una determinada página en la
 * propiedad Campos que es un array con campo(nombre) y valor
*/
class pagina extends morcegocms_common {
	/**
	* @var array 
	*/
	var $Campos ;
	/**
	* @var array
	*/
	var $aVariables;
	/**
	* @var boolean  
	*/
	var $ok ;
	/**
	* @var boolean 
	*/
	var $soloactivas;
	/**
	* @var boolean 
	*/
	var $NoData;
	/**
	* @var integer 
	*/
	var $idpagina;
	/**
	* @var object Objeto página raiz del actual
	*/
	var $oRoot ;
	
	/**
	* @var object  Objeto página padre del actual
	*/
	var $oParent;
	/**
	* @var object Objeto página de la página de inicio
	*/
	var $oIndex;
	/**
	* @var metadata Array con los metadatos de la página
	*
	*/
	var $oRelated;
	
	
	var $metadata ;
	
	/**
	*  Constructor, crea el objeto con todas las propiedades.
	* 
	* @parameter string $idpagina El identificador de página
	* @parameter boolean $soloactivas Por seguridad se puede indicar que solo muestre las activas.
	* @parameter boolean $NoData  Si es true no genera las busquedas de las propiedades calculadas
	* @parameter boolean $Related Si es true genera tambien los objetos relacionados (padre y root) 
	*/
	function pagina($idpagina, $soloactivas = false, $NoData = false, $Related = false  ){
		$this->idpagina  =  ( empty($idpagina) ) ? morcegocms_utils::idpaginafromuid( 0 ) : $idpagina ;
		$this -> soloactivas = $soloactivas;
		$this -> NoData = $NoData ;
		$ObjectID = "pagina." . 
			md5( serialize( $GLOBALS['statsCMS']->vars ) .  $this->idpagina . 
			$GLOBALS['statsCMS']->User->idgroup .  
			(($Related == true ) ? 'complete' : 'resume'  ));
		$Serial = (_MORCEGO_CACHE_OBJECTS ) ? $this->get_idobject( $ObjectID) : false;
		if(  $Serial != false ) {
			$clon =& $this ;
			$clon = unserialize( $Serial );
			unset( $Serial );
		} else {
			$this->load_idpagina();
			if ($Related == true && $this->ok == true ) {
				$this->gen_parent();
				$this->gen_root();
				$this->gen_index();
			}
			if ( $this -> ok == true && _MORCEGO_CACHE_OBJECTS  ) {
				$this->save_serialized( $ObjectID );
			}
		}
		
	}
	/**
	* @access private 
	* 
	*  Crea la propiedad oParent a partir de los datos de la página padre
	*
	*/
	function gen_parent() {
		$this->oParent = new pagina( 
			morcegocms_utils::idpaginafromuid( $this->Campos['uidparent']) , false, false, false ) ;
	}
	/**
	*  Nos devuleve ol objeto padre, sinó existe lo crea durante la consulta
	*
	*/
	function get_parent( $value, $value2 = '' ) {
		if ( !is_object( $this->oParent  )) { 
			$this->gen_parent(); 
		}
		$object =& $this->oParent;
		switch ($value ) { 
			case 'variable':
				return ( isset( $object->Campos['variable'][$value2]  ) ) 
					? $object->Campos['variable'][$value2] 
					: '';
				break;
			case 'meta':
			return ( isset( $object->metadata[$value2]  ) ) 
				? $object->metadata[$value2] 
				: '';
				break;                
			case "num_pages" :
				return  $object->num_pages( $value2);
				break;
			case "num_contents" :
				return  $object->num_contents();
				break;
		 
			default:
				return ( isset( $object->Campos[$value]  ) ) ? $object->Campos[$value] : '';
		}
	}
    
    function gen_root() {
        $this->oRoot = new pagina( 
            morcegocms_utils::idpaginafromuid( $this->Campos['uidroot']) , false, false, false  ) ;
    }
    function get_root( $value,  $value2 = '') {
        if ( !is_object( $this->oRoot  )) { 
            $this->gen_root(); 
        }
        $object =& $this->oRoot;
        switch ( $value ) {
          case 'variable':
             return ( isset( $object->Campos['variable'][$value2]  ) ) 
                 ? $object->Campos['variable'][$value2] 
                 : '';
             break;
          case 'meta':
             return ( isset( $object->metadata[$value2]  ) ) 
                 ? $object->metadata[$value2] 
                 : '';
             break;
          default:
            return ( isset( $object->Campos[$value]  ) ) ? $object->Campos[$value] : '';
        }
        
    }
    
    function gen_index( ) {
        $this->oIndex = new pagina( 
            morcegocms_utils::idpaginafromuid( 0 ) , false, false, false   ) ;
    }
    
    function get_index( $value ,  $value2 = '' ) {
        if ( !is_object( $this->oIndex  )) { 
            $this->gen_index(); 
        }
        $object =& $this->oIndex;
        switch ( $value ) {
          case 'variable':
             return ( isset( $object->Campos['variable'][$value2]  ) ) ? $object->Campos['variable'][$value2] : '';
             break;
          case 'meta':
             return ( isset( $object->metadata[$value2]  ) ) ? $object->metadata[$value2] : '';
             break;
          default:
            return ( isset( $object->Campos[$value]  ) ) ? $object->Campos[$value] : '';
        }
    }
    
    
    
     function gen_related( $idPagina  ) {
        $this->oRelated  = new pagina( 
            $idPagina  , false, false, false   ) ;
    }
    
    function get_related($idPagina,  $value ,  $value2 = '' ) {
        if ( !is_object( $this->oRelated   ) || (isset( $this->oRelated->Campos['idpagina'])  &&   $this->oRelated->Campos['idpagina'] != $idPagina  )) { 
            $this->gen_related( $idPagina); 
        }
        $object =& $this->oRelated;
        switch ( $value ) {
          case 'variable':
             return ( isset( $object->Campos['variable'][$value2]  ) ) ? $object->Campos['variable'][$value2] : '';
             break;
          case 'meta':
             return ( isset( $object->metadata[$value2]  ) ) ? $object->metadata[$value2] : '';
             break;
          default:
            return ( isset( $object->Campos[$value]  ) ) ? $object->Campos[$value] : '';
        }
    }
    
    
    function load_idpagina() {
	
        $filtro = sprintf(' %s %s ',
             "idpagina = ".
             $GLOBALS['DB']->Quote($this->idpagina) ,
             (($this->soloactivas == true ) ? ' and activa=1 and idgroup <= ' . 
                $GLOBALS['statsCMS']->User->idgroup . ' and fecha <= ' .  
                $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy)  : '')
		
        );
        $comando_sql = "select " .
            "idpagina, tipo, titulo, texto, text_align, enlace, img_mimetype, img_width, img_height, uid, " .
            "uidroot, uidparent, fecha, hits, textohijas, textolink, orden, activa, template, " .
            "img_align, icono_align, icono_mimetype, descripcion, variables, idgroup " .
            " from " . $GLOBALS['DB_prefijo'] . "paginas where {$filtro} " ;
        $resultado = $GLOBALS['DB'] -> Execute($comando_sql);
        if ($resultado -> EOF == 1){
            $this -> ok = false;
            return ;
        }else{
            $this -> ok = true;
        }
        // Recorremos todo el recordSet
        
        foreach($resultado ->fields as $campo => $valor) {
            for ($i = 0; $i < count($resultado -> fields); $i++){
                $this -> Campos[ $campo] = $valor;
            }
        }
        // ahora seleccionamos el idioma si existe!.
        if (isset( $GLOBALS['statsCMS']->vars['lang'] )) {
            $comando_sql = "select " .
                "idpagina, titulo, texto, enlace, fecha, textohijas, textolink, template, descripcion, ".
                "variables ".
                " from " . $GLOBALS['DB_prefijo'] . "paginas_lang where idpagina = ".
                $GLOBALS['DB']->Quote($this->idpagina) . 
                " and lang = " . $GLOBALS['DB']->Quote($GLOBALS['statsCMS']->vars['lang']);
            $resultado = $GLOBALS['DB'] -> Execute($comando_sql);
            // print_r( $resultado->fields ) ;
            if ( !$resultado->EOF ) {
                foreach($resultado->fields as $campo => $valor) {
                    for ($i = 0; $i < count($resultado -> fields); $i++){
                        $this -> Campos[ $campo] = $valor;
                    }
                }
            }
        }
        $this -> Campos['texto'] = stripslashes($this -> Campos['texto']);
        $this -> Campos['titulo'] = stripslashes($this -> Campos['titulo']);
        $this -> Campos['descripcion'] = stripslashes($this -> Campos['descripcion']);
        
        $imagen = '' ;
        $url_imagen = '';
        if (!empty($this -> Campos['img_mimetype'])){
            $nombre_imagen = '/cache.imagen.' .
                $this -> Campos['idpagina'] . '.' . 
                    $GLOBALS['varsCMS'] -> extension_from_mimetype($this -> Campos['img_mimetype']);
            $path_imagen = dirname(__FILE__) . '/../../' . 
                $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
            $url_imagen = $GLOBALS['configCMS']->get_var('rutaweb') . 
                $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
            if (!file_exists($path_imagen)){
                $comando_sql = "select img_content from {$GLOBALS['DB_prefijo']}paginas where " .
                    "idpagina = '{$this->Campos['idpagina']}' ";
                $recordset = $GLOBALS['DB'] -> execute($comando_sql);
                $content = $recordset -> fields['img_content'];
                $hf = fopen($path_imagen, 'w') ;
                fwrite($hf, $content);
                fclose($hf) ;
            }
            $url_imagen = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') 
                ? $GLOBALS['configCMS']->get_var('rutaweb') . 
                    'img/' . $this -> Campos['idpagina'] . '.' .  
                    $GLOBALS['varsCMS'] -> extension_from_mimetype($this -> Campos['img_mimetype']) 
                : $url_imagen;
            $imagen = "<img src=\"{$url_imagen}\" border='0' align=\"" . 
                $this -> Campos['img_align'] . 
                "\" alt=\"Imagen  {$this->Campos['titulo']}\" />";
        }
        $this -> Campos['url_imagen'] = $url_imagen;
        $this -> Campos['tag_imagen'] = $imagen;
        $icono = '' ;
        $url_imagen = '';
        if (!empty($this -> Campos['icono_mimetype'])){
            $nombre_imagen = '/cache.icono.' .
            $this -> Campos['idpagina'] . '.' . 
                $GLOBALS['varsCMS'] -> extension_from_mimetype($this -> Campos['icono_mimetype']);
            $path_imagen = dirname(__FILE__) . '/../../' . 
                $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
            $url_imagen = $GLOBALS['configCMS']->get_var('rutaweb') .  
                $GLOBALS['varsCMS'] -> path_repository . $nombre_imagen ;
            if (!file_exists($path_imagen)){
                $comando_sql = "select icono_content from {$GLOBALS['DB_prefijo']}paginas where idpagina = '{$this->Campos['idpagina']}' ";
                $recordset = $GLOBALS['DB'] -> execute($comando_sql);
                $content = $recordset -> fields['icono_content'];
                $hf = fopen($path_imagen, 'w') ;
                fwrite($hf, $content);
                fclose($hf) ;
            }
            $url_imagen = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') 
              ? $GLOBALS['configCMS']->get_var('rutaweb') .  'icn/' . 
                  $this -> Campos['idpagina'] . '.' .  
                  $GLOBALS['varsCMS'] -> extension_from_mimetype($this -> Campos['icono_mimetype']) 
              : $url_imagen;
            $icono = "<img src=\"{$url_imagen}\" border='0' align=\"" . 
                $this -> Campos['icono_align'] . 
                "\" alt=\"Imagen  {$this->Campos['titulo']}\"/>";
        }
        $this -> Campos['tag_icono'] = $icono;
        $this -> Campos['url_icono'] = $url_imagen;
        $this -> Campos['tag_enlace'] = (empty($this -> Campos['enlace'])) 
            ? ''
            : sprintf(
                '<a href="%s" %s class="enlace">%s</a>',
                $this -> Campos['enlace'],
                (substr($this -> Campos['enlace'], 0, 4) == 'http')? 'target="new"' : '',
                (empty($this -> Campos['textolink'])) ? $this -> Campos['enlace'] : $this -> Campos['textolink']
            ) ;
        $prefijourl = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') 
            ? $GLOBALS['configCMS']->get_var('rutaweb') 
            : $GLOBALS['configCMS']->get_var('rutaweb') .  '?';
        
        $this->Campos['href'] = (empty($this -> Campos['enlace'])) 
            ? $prefijourl . $this -> Campos['idpagina'] . $this->url_vars() 
            : $this -> Campos['enlace'] ;
        $this->Campos['target'] = (substr($this -> Campos['enlace'], 0, 4) == 'http')? '_blank' : '_self';
        $this -> Campos['titulopaginaweb'] = $GLOBALS['configCMS'] -> get_var('prefijotituloweb') .

        strip_tags($resultado -> fields['titulo'] );
        
        $this -> generaVariables();
        $this->Campos['fecha'] = $resultado->UnixTimeStamp($this->Campos['fecha']);
        $this->metadata = array();
        /*
         Inhabilitados hasta que exista una administración de los metadatos
        
        $comando_sql = "select metadato, valor  from {$GLOBALS['DB_prefijo']}metadata where " . 
          "uid = \"{$resultado->fields['uid']}\"" ;
        $resultado = $GLOBALS['DB'] -> Execute($comando_sql);
        while( !$resultado->EOF ) {
          $this->metadata[$resultado->fields['metadato']] =  $resultado->fields['valor'] ;
          $resultado->MoveNext();
        }
        */
    }
    function  url_vars () {
        $str_out = '';
        foreach ( $GLOBALS['statsCMS']->vars as $variable => $valor ) {
            $str_out .= '/' . $variable . '!' . $valor ;
        }
        return $str_out ;
    }
    function num_contents() {
        $comando_sql = "select count(*) as total from {$GLOBALS['DB_prefijo']}paginas where " .
            "tipo=1 and uidparent={$this->Campos['uid']} and activa = 1  and fecha <= " . 
            $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy);
        $recordset = $GLOBALS['DB']->execute( $comando_sql ) ;
        $total =& $recordset->fields['total'];
        unset( $recordset );
        return $total ;
    }
    
    function num_pages() {
        $comando_sql = "select count(*) as total from {$GLOBALS['DB_prefijo']}paginas where " .
            "tipo=0 and uidparent={$this->Campos['uid']} and activa = 1 and fecha <= "  .  
            $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy);
        $recordset = $GLOBALS['DB']->execute( $comando_sql ) ;
        $total =& $recordset->fields['total'];
        unset( $recordset );
        return $total ;
    }
 
    
    function search_idpagina() {
        $filtro = sprintf(' %s %s ',
             (empty($this->idpagina)) 
                 ? "uid = 0"
                 : "idpagina = \"{$this->idpagina}\"" ,
             ($this->soloactivas == true ) 
                  ? 'and activa=1 and idgroup <= ' . $GLOBALS['statsCMS']->User->idgroup . 
                      ' and fecha <= ' . $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy) 
                  : ''
        );
        $comando_sql = "select idpagina, uid " .
            " from " . $GLOBALS['DB_prefijo'] . "paginas where {$filtro} " ;
        $resultado = $GLOBALS['DB']->Execute($comando_sql);
        if ($resultado -> RecordCount() < 1){
            $this -> ok = false;
        } else {
            $this -> Campos['idpagina'] = $resultado->fields['idpagina'];
            $this -> Campos['uid'] = $resultado->fields['uid'];
            $this -> ok = true;            
        }
        return;
    }
    /**
    * Esta funcion alimenta la propiedad aVariables del objeto que es una matriz con todas los datos que 
    * están en el campo variables en la forma xxxx=yyyyy.
    * Es una matriz asociativa cuyo nombre es xxxx y su valor es yyyyy.
    */
    function generaVariables(){
         // devuelve un array con los valores del campo variables
        $aTMP = explode ("\n", str_replace( '\r', '',  $this -> Campos['variables'] ) );
        $resultado = array();
        for ($i = 0; $i < count($aTMP); $i++){
            $PosIgual = strpos( $aTMP[$i], '=');
            if ($PosIgual  != false ){
                $elemento = substr( $aTMP[$i], 0, $PosIgual  );
                $valor = substr( $aTMP[$i], - ( strlen( $aTMP[$i] ) - $PosIgual ) + 1  );
                $resultado[$elemento] = $valor;
                if ( ( strlen($aTMP[$i]) -1 ) == $PosIgual ) {
                    $resultado[$elemento] = ' ';
                }
            }
        }
        $this -> Campos['variable'] = $resultado;
        $this -> aVariables = &$this -> Campos['variable'];
    }
}

class Template_MorcegoCMS extends morcegocms_common {
    var $plantilla = '';
    var $timming_cache = 0;
    var $pagina = array();
    var $cache ;
    var $dbtemplate;
    var $objpagina ;
    function Template_MorcegoCMS(&$pagina, $timming_cache , $idtemplate = '', $seccion = 'content'){
        $this -> timming_cache = $timming_cache;
        $idtemplate = (empty( $idtemplate)) 
            ? $pagina->Campos['template'] 
            : $idtemplate;
        $ObjectID = "template."  . 
            md5( serialize( $GLOBALS['statsCMS']->vars ) .
                "{$pagina->Campos['idpagina']}.{$idtemplate}. {$seccion}" );
        $Serial = (_MORCEGO_CACHE_OBJECTS ) 
            ? $this->get_idobject( $ObjectID) 
            : false;
        if(  $Serial != false ) {
	    $clon =& $this ;
            $clon = unserialize( $Serial );
            unset( $Serial );
        } else {
            if ( $seccion == 'content_header' ) {
                $metodo_template= 'read_template_header';
            } elseif ( $seccion == 'content_footer') {
               $metodo_template= 'read_template_footer';
            } else {
                $metodo_template = 'read_template';
            }
            $this -> objpagina = & $pagina;
            $this -> pagina = & $pagina -> Campos;
            // $this -> pagina->metadata =& $pagina->metadata ;
            $this -> cache = new cls_cache;
            $this -> dbtemplate = new cls_dbtemplates;
            $this -> plantilla = $this -> dbtemplate -> $metodo_template( $idtemplate );
            if ( _MORCEGO_CACHE_OBJECTS ) {
                $this->save_serialized( $ObjectID );
            }
        } 
    }

    function mostrar(){
        if ( !isset( $this->objpagina->Campos["variable"]["nocache"] )) {
            $this -> iniciar_cache();
            if ($this->objpagina->NoData == true) {
                $this->objpagina->load_idpagina();
                $this -> plantilla = &$this -> dbtemplate -> read_template($this -> pagina['template']);
            }
            echo $this -> parsear();
            $this -> finalizar_cache();		 
        } else {
            if ($this->objpagina->NoData == true) {
                $this->objpagina->load_idpagina();
                $this -> plantilla = &$this -> dbtemplate -> read_template($this -> pagina['template']);
            }
            echo $this -> parsear();
        }
        
    }
    function parsear( $procesarGlobales = true  , $cadena = ''){
	
	// $procesarGlobales = ( isset( $GLOBALS['procesarGlobales'])) ? $GLOBALS['procesarGlobales'] : true;
        /* para permitir codigo especial en el morcego se añadira 
	* {code} {/code} este codigo no se procesará
	* -- comentario
	* {comment} {/comment} <!-- -->... se eliminará
	*/
        // código {code} ...  {/code}
	if ( $procesarGlobales  === true ) {
			$this -> plantilla = preg_replace(
				array(
				  '|{global:(.*)}|ieU'
				), 
				array(
				'"{pagina:index:variable:\1}"' 
				),
				$this->plantilla );
		} else {
			$this -> plantilla = preg_replace(
				array(
					'|{global:(.*)}|ieU'
				), 
				array(
					'"¿-=-global:\1-=-?"' 
				),
				$this->plantilla );
		} 
	
        $this -> plantilla = preg_replace(
		  array(
		    '|{code}(.*){/code}|ieUs',
		    '|{comment}(.*){/comment}|ieUs',
		    '|<!--(.*)-->|ieUs','""'
		  ), 
		  array(
		    '$this->bloques_code(  addslashes( "' . '\0' . '") )',
		    '""',
		    '""'
		  ),
		  $this -> plantilla);
        /* pasamos de ieUs a sólo e */
        for ( $i = 0; $i < _MORCEGO_TEMPLATE_MAX_ITERATIONS; $i++ ) {
        /*  if ( $procesarGlobales  === true ) {
			$this -> plantilla = preg_replace(
				array(
				  '|{global:(.*)}|ieU'
				), 
				array(
				'"{pagina:index:variable:\1}"' 
				),
				$this->plantilla );
		} else {
			$this -> plantilla = preg_replace(
				array(
					'|{global:(.*)}|ieU'
				), 
				array(
					'"¿-=-global:\1-=-?"' 
				),
				$this->plantilla );
		} 
	*/
	   $this -> plantilla = preg_replace(
                array(
		   '|{comment}(.*){/comment}|ieUs',
                  '|{code}(.*){/code}|ieUs',
                  '|{(.*)}|ieU'
                ), 
                array(
		  '',
                  '$this->bloques_code(     addslashes( "'  . '\0' . '") )',
                  '$this->bloques(          addslashes( "' . '\0' . '") )'
		  
                ),
               $this->plantilla );
	      
	       
            if ( $i/3 == (int) ($i/3)) {
                $this->plantilla = str_replace( array('%!-%' ) , array('{') , $this->plantilla) ;
            }
        }
        /* reconvertimos los {}  */
        $this->plantilla = str_replace( array('%!-%' , '%-!%') , array('{', '}') , $this->plantilla) ;
	
	$this->plantilla = str_replace( array('¿-=-' , '-=-?') , array('{', '}') , $this->plantilla) ;
        return $this->plantilla;
    }
    /**
    * Esta funcion ejecuta un bloque de codigo
    */
    function bloques($cadena){
        global $pagina;
        $cadena = substr($cadena, 1, strlen($cadena) - 2);
        $resultado = '';
        /*  0.11.00 comprobamos que no sea una clase  */
        /*  Comprobamos que si existe la clase con el prefijo de usuario    */
        $resultado = '%!-%' . stripslashes( $cadena )  . '%-!%' ;
        if ( strpos( $cadena , '{') != false  ) {
            $resultado = '%!-%' . stripslashes( $cadena )  . '}' ;
        } else {
            /*  Compramos si existe la funcion de usuario         */
            $aCadena = explode(":", $cadena);
            $funcion = $GLOBALS['prefijo_funciones'] . $aCadena[1] ;
            $longitud = strlen($aCadena[0]) + 2 + strlen($aCadena[1]) ;
            $parametros = implode(':', array_slice( $aCadena, 2));
            if (function_exists($funcion)){
                $resultado = $funcion($parametros, $this -> objpagina);
            } else {
                $class = strtolower( $aCadena[0]  );
                $userClass = '' ;
                // determinamos si es una clase de usuario o del morcegocms
                if ( class_exists( $GLOBALS['configCMS'] -> get_var('prefijofunciones') . $class )) {
                    $userClass =  $GLOBALS['configCMS'] -> get_var('prefijofunciones') . $class ;
                } else {
			if (  !class_exists( 'morcegocms_functions_'. $class )
				&& file_exists( dirname(__FILE__) . '/morcegocms_functions_'. $class . '.php' ))  {
				include_once( dirname(__FILE__) . '/morcegocms_functions_'. $class . '.php'  );
		
				if (  class_exists( 'morcegocms_functions_'. $class  )) {
					$userClass =  'morcegocms_functions_'. $class  ; 	
					
				}
			} else {
				$userClass =  'morcegocms_functions_'. $class  ; 	
			}
			
                    
                }
                if (!empty( $userClass ) &&  class_exists( $userClass )) {
                    // creamos el objeto si no existe+
				
                    if ( !isset( $GLOBALS['userObjects'][$class] ) ) {
		    
                        $GLOBALS['userObjects'][$class]  = new $userClass( $this ) ;
                    }
                    // determinamos el método
                    $classMethod = $aCadena[1];
                    $classMethodParameters =  implode( ':', array_slice(  $aCadena, 2 ));
                    // determinamos si existe, de no existir será action();
                    if (empty( $classMethod) || !method_exists( $GLOBALS['userObjects'][$class] , $classMethod )) {
                        if ( method_exists( $GLOBALS['userObjects'][$class] , 'action' ) ) {
                            $classMethod = 'action' ;
                            $classMethodParameters =  $cadena  ;
                        } else {
                            $classMethod = '';
                        }
                    }
                    if ( !empty( $classMethod )) {
                        if ( method_exists( $GLOBALS['userObjects'][$class] , 'init' ) ) {
                            $GLOBALS['userObjects'][$class]->init( $this ) ;
                        }
                        $resultado = $GLOBALS['userObjects'][$class]->$classMethod( $classMethodParameters );
                    }
                }
            }
        }    
        return $resultado ;
    }

    function bloques_code( $cadena) {
        /*  procesa el contenido de un bloque para que no sea ejecutado  */
        return str_replace( 
            array('{code}', '{/code}', '{', '}'), 
            array('', '', '%!-%' , '%-!%'),  
            stripslashes($cadena)) ;
    }
    
    /* Funciones de caché  */
    function idcache(){
        $archivo_objeto = $GLOBALS['statsCMS']->uniqid_cache( 
            md5($_SERVER['QUERY_STRING'] .  
                serialize( $_POST ) . serialize( $GLOBALS['statsCMS']->vars ) . 
                $GLOBALS['statsCMS']->User->idgroup ),
            'cache.html.' ,
            '.obj') ;
        return $archivo_objeto ;
    }

    function iniciar_cache() {
        $idcache = $this -> idcache();
        if ($this -> timming_cache > 0) {
            $fecha_cache = $this -> cache -> date_cache($idcache) ;
            if ($fecha_cache > $this -> timming_cache){
                ob_start();
            } else {
                $content = $this -> cache -> render_cache($idcache) ;
                $modificacion = date('r', $fecha_cache);
                header('Last-Modified: ' . $modificacion);
                header('ETag: ' . md5($modificacion));
                header('Content-Length: ' . strlen($content));
                echo $content ;
                die(); /* finalizamos el script */
            }
        } elseif (  $this -> timming_cache > 0 ) {
            ob_start();
        }
    }
    
    function finalizar_cache(){
        if ($this -> timming_cache > 0){
            $idcache = $this -> idcache();
            $contenido_cache = ob_get_contents();
            ob_end_clean();
            $this -> cache -> save_cache($idcache, $contenido_cache) ;
            $modificacion = date('r'); 
            header('Last-Modified: ' . $modificacion);
            header('ETag: ' . md5($modificacion));
            header('Content-Length: ' . strlen( $contenido_cache));
            die( $contenido_cache );
            /* cancelamos y mostramos la cache. */
        }
    }
}

/**
* La clase config_morcegoCMS será utilizada para leer la configuración del motor.
*
*
* Modificaciones:
* 2003.05.02 :  Se ha utilizado (condicion)? valor1  : valor2 ; para asignación de valores
*/

class config_morcegocms extends morcegocms_common  {
    var $version ;
    var $ruta_conf;
    var $variables = array();
    var $crypt; /*  objeto que encripta/desencripta  */
    var $DB; /*  objeto conexión a la base de datos  */
    var $log ;
    var $hoy ;
    /**
    * 
    * @return config _morcegoCMS
    * @param fichero $ = 'config.xml' string
    * @desc Constructor de la clase, leer el archivo y lo parsea
    */
    function config_morcegocms($fichero = 'config.ini.php'){
        $this->hoy = mktime( 23, 59, 59,  date('m') , date('d'), date('Y') );
        $this->variables = array();
        $this -> version = _MORCEGO_VERSION;
        $this -> crypt = new MorcegoCrypt;
        $this -> ruta_conf = $this -> get_ruta_conf();
        $this -> parse_conf(parse_ini_file($this -> ruta_conf . $fichero, TRUE));
        $this -> DB = ADONewConnection($this -> get_var('dbtipo'));
        $this -> DB -> Connect(
            $this -> get_var('dbservidor'),
            $this -> get_var('dbusuario'),
            $this -> get_var('dbpassword'),
            $this -> get_var('dbbasedatos'));
        $ObjectID = 'config';
        $Serial = (_MORCEGO_CACHE_OBJECTS ) ? $this->get_idobject( $ObjectID) : false;
        if(  $Serial != false ) {
            
            $clon =& $this ;
            $clon = unserialize( $Serial );
            
            unset( $Serial );
        } else {
            $this -> get_config_db();
            $this -> cargar_includes();
            $this->save_serialized( $ObjectID );
        }
   
    }
    function __wakeup() {
        /*  importante !!! ... esto se llama en el unserialize ... y aqui restauramos la conexión */
        $this -> DB -> Connect(
            $this -> get_var('dbservidor'),
            $this -> get_var('dbusuario'),
            $this -> get_var('dbpassword'),
            $this -> get_var('dbbasedatos'));
        $this -> cargar_includes();
         /*
        0.9.10 ... comprobamos si la fecha es diferente ... si es así ... borramos la cache interna
        Esto nos permitirá mostrar los contenidos nuevos con fecha = actual (si existen );
        */
        if ( $this->get_var('DateCache') != date('Ymd') ) {
            // borramos la cache de objetos
            $this->DB->execute( "delete from " . $this->get_var('dbprefijo') ."objects " );
            // establecemos el valor de dateCache a el nuevo 
            $this->set_var( 'DateCache', date('Ymd' ) ); 
            // grabamos el nuevo valor en la base de datos
            $this->DB->replace( 
              $this->get_var('dbprefijo') . 'config' ,
              array( 
                 'idconfig' => 'DateCache',
                 'configvalue' => date('Ymd'),
                 'date' => $this->DB->DBTimeStamp( time())
              ),
              'idconfig',
              true  );
        }


    }
    function get_config_db() {
        $comando_sql = "select idconfig, configvalue from " .
            $this->get_var( 'dbprefijo') . "config";
        $recordset = $this->DB->execute( $comando_sql ) ;
        while( !$recordset->EOF ) {
            $this->set_var( $recordset->fields['idconfig'], $recordset->fields['configvalue'] ); 
            $recordset->MoveNext();
        }
        unset( $recordset ) ;
    }
  
    /**
    * 
    * @return void 
    * @desc Nos muestra el XML (el archivo de configuración) ...
    */
    function parse_conf($ini){
        /* Leemos toda la configuración */
        $this -> set_var('dbtipo', $ini['datos']['dbtipo']);
        $this -> set_var('dbusuario', $ini['datos']['dbusuario']);
        $this -> set_var('dbservidor', $ini['datos']['dbservidor']);
        $this -> set_var('dbbasedatos', $ini['datos']['dbbasedatos']);
        $this -> set_var('dbpassword', $this -> crypt -> decrypt($ini['datos']['dbpassword']));
        $this -> set_var('dbprefijo', $ini['datos']['dbprefijo']);
    }
    function cargar_includes(){
        $includes = explode(';', $this -> get_var('includes'));
        foreach($includes as $include){
            $fichero = $this -> ruta_conf . $include ;
            if (!empty($include) && file_exists($fichero)){
                require_once($fichero);
            }
        }
    }
    function get_ruta_conf(){
        /* nos devuelve la ruta del fichero de configuración que será justo en el directorio padre de este */
        $resultado = dirname(__FILE__) . '/../';
        return $resultado;
    }
    /**
    * 
    * @return void 
    * @param variable $ string
    * @param valor $ string
    * @desc Establece el valor de una propiedad del objeto en cuestión...
    */
    function set_var($variable, $valor){
        $this -> variables["$variable"] = $valor ;
    }
    function get_var($variable){
        return (isset($this -> variables["$variable"])) ? $this -> variables["$variable"]: '' ;
    }
}

class MorcegoCrypt{
    function encrypt ($cadena) {
        $resultado = '';
        $crc_num = (crc32($cadena) % 255) ;
        for ($i = 0; $i < strlen($cadena); $i++) {
            $caracter_in = ord(substr($cadena, $i, 1));
            $resultado .= str_pad(base_convert("$caracter_in", 10, 20), 2, "0", STR_PAD_LEFT) ;
        }
        $resultado = strrev($resultado);
        $resultado = substr($resultado, 1, strlen($resultado) - 1) . substr($resultado, 0, 1) .
        str_pad(base_convert("$crc_num", 10, 16), 2, '0', STR_PAD_LEFT);
        return $resultado;
    }
    function decrypt ($cadena) {
        $resultado = '';
        $crc_1 = substr($cadena, strlen($cadena) -2 , 2) ;
        $cadena = substr($cadena, 0, strlen($cadena) - 2) ;
        $cadena = substr($cadena, strlen($cadena) - 1, 1) . substr($cadena, 0, strlen($cadena) - 1) ;
        $cadena = strrev($cadena);
        for ($i = 0; $i < strlen($cadena); $i = $i + 2) {
            $caracter_in = substr($cadena, $i, 2) ;
            $resultado .= chr(base_convert($caracter_in, 20 , 10));
        }
        $crc_num = crc32($resultado) % 255;
        $crc_2 = str_pad(base_convert("$crc_num", 10, 16), 2, '0', STR_PAD_LEFT) ;
        if ($crc_1 <> $crc_2) { 
            $resultado = 'Error en CRC - Cadena modificada' ;
        }
        return $resultado ;
    }

}
class cls_stats{
    var $http_host;
    var $http_referer;
    var $idpagina;
    var $ip_client;
    var $http_user_agent;
    var $peticion;
    var $cliente;
    var $ip_inet;
    /* la ip visible en internet */
    var $parametros;
    
    // variables de entorno!
    var $vars ;
    /*  Objeto usuario  */
    var $User;
    /**
    * array que contiene los parametros (explode / del query_string)
    * a iniciar cargaremos en las propiedades los valores de la página/petición actual
    * 
    * @parameters :  $register (boolean) {true => registrará en la BD el acceso} {false=>no lo registrará}      
    * este parámetro será utilizado en el futuro (por hacer).
    */
    function cls_stats($register = true){
        global $pagina;
        $this -> http_host = $this -> get_http_host();
        $this -> http_referer = $this -> get_http_referer();
        $this -> ip_client = $this -> get_ip_client();
        $this -> http_user_agent = $this -> get_http_user_agent();
        $this -> cliente = $this -> get_cliente();
        $this -> peticion = $this -> get_peticion();
        $this -> ip_inet = $this -> get_ip_inet();
	if ( empty( $_SERVER['QUERY_STRING'] ) ) {
		$_SERVER['QUERY_STRING']  = '';
	}	
        $_SERVER["QUERY_STRING"] = substr( $_SERVER["QUERY_STRING"], 0, 
            (strpos(  $_SERVER["QUERY_STRING"] , '&PHPSESSID' ) === false ) ?
            strlen( $_SERVER["QUERY_STRING"] ) :
            strpos( $_SERVER["QUERY_STRING"], '&PHPSESSID' ));
        $_SERVER["QUERY_STRING"] = substr( $_SERVER["QUERY_STRING"], 0, 
            (strpos(  $_SERVER["QUERY_STRING"] , 'PHPSESSID' ) === false ) ?
            strlen( $_SERVER["QUERY_STRING"] ) :
            strpos( $_SERVER["QUERY_STRING"], 'PHPSESSID' ));             
        /*
        Se podrán indicar variables de entorno en la url como variable!valor
        */
        $aparametros = explode('/', str_replace( array('{', '}'), '', urldecode($_SERVER["QUERY_STRING"])));
        $this->parametros= array();
        foreach ( $aparametros as $elemento ) {
            if (strpos( $elemento, '!') != false ) {
                $elemento2 = explode( '!', $elemento );
              /*
                *!* TODO: por ahora solo aceptaremos lang
              */
                if ( $elemento2[0] == 'lang' ) {
                    $_SESSION["_{$elemento2[0]}"] = $elemento2[1]  ;
                }
                
            } else {
                $this->parametros[] = $elemento ;
            }
        }
        if ( !isset( $this->parametros[0] )) {
            $this->parametros[0]='';
        }
        // generamos $vars con los valores de las variables de sesiòn!
        $this->vars = array();
        foreach( $_SESSION as $variable => $valor ) {
            if ( substr( $variable, 0, 1) == '_'  && $variable == '_lang')  {
                $this->vars[substr( $variable, 1 )] = $valor ;
            }
        }
        unset( $aparametros );
        $this->User = new cls_security ;
    }
    
    function uniqid_cache( $cadena, $prefijo = '', $extension = '' ) {
        $ruta_cache = dirname( __FILE__ )  . '/../../' . $GLOBALS['varsCMS'] -> path_repository  . '/'  ;
        return ($ruta_cache . $prefijo . md5(dirname( __FILE__ ) . $this->http_host . $cadena  ) . $extension );
    }
    function save_hit($idpagina){
        $ahora = $GLOBALS['DB']->DBTimeStamp( time());
        $comando_sql = "insert into {$GLOBALS['DB_prefijo']}stats (idtipo, idpagina, ".
            "peticion, referer, ip, ip_inet, cliente, http_user_agent, fecha) values (" .
            "1, '{$idpagina}', '{$this->peticion}', '{$this->http_referer}', " .
            " '{$this->ip_client}','{$this->ip_inet}', '{$this->cliente}', ". 
            "'{$this->http_user_agent}',  {$ahora} )";
        @$GLOBALS['DB'] -> execute($comando_sql);
    }
    function save_error($idpagina){
        $ahora = $GLOBALS['DB']->DBTimeStamp( time());
        $comando_sql = "insert into {$GLOBALS['DB_prefijo']}stats (idtipo, idpagina, " . 
            "peticion, referer, ip, ip_inet, cliente, http_user_agent, fecha) values (" .
            "2, '{$idpagina}', '{$this->peticion}', '{$this->http_referer}', " .
            " '{$this->ip_client}', '{$this->ip_inet}','{$this->cliente}', ".
            "'{$this->http_user_agent}',  {$ahora} )";
        @$GLOBALS['DB']->execute($comando_sql);
    }
    function get_http_host(){
        $resultado = getenv('HTTP_X_HOST') ;
        /* Para lycos y otros servidores */ 
        if (empty($resultado)){
            if (isset($HTTP_SERVER_VARS['HTTP_X_HOST'])){
                $resultado = $HTTP_SERVER_VARS['HTTP_X_HOST'];
            } elseif (isset($HTTP_SERVER_VARS['HTTP_HOST'])){
                $resultado = $HTTP_SERVER_VARS['HTTP_HOST'];
            } elseif (isset($_SERVER['HTTP_HOST'])){
                $resultado = $_SERVER['HTTP_HOST'];
            } else {
                $resultado = 'unknown';
            }
        }
        return $resultado;
    }
    function get_http_referer(){
        $resultado = getenv('HTTP_REFERER') ; 
        /* Para lycos y otros servidores */ 
        if (empty($resultado)){
            if (isset($HTTP_SERVER_VARS['HTTP_REFERER'])){
                $resultado = $HTTP_SERVER_VARS['HTTP_REFERER'];
            } elseif (isset($_SERVER['HTTP_REFERER'])){
                $resultado = $_SERVER['HTTP_REFERER'];
            } else {
                $resultado = 'Bookmark';
            }
        }
        if (ereg("http://{$this->http_host}", $resultado)){
            $resultado = '';
        }
        return $resultado;
    }
    function get_peticion(){
        $resultado = getenv('REQUEST_URI') ; 
        /* Para lycos y otros servidores */
        if (empty($resultado)){
            if (isset($HTTP_SERVER_VARS['REQUEST_URI'])){
                $resultado = $HTTP_SERVER_VARS['REQUEST_URI'];
            } elseif (isset($_SERVER['REQUEST_URI'])){
                $resultado = $_SERVER['REQUEST_URI'];
            } else {
                $resultado = '[INDEX]';
            }
        }
        return $resultado;
    }
    /**
    * Determinamos la ip del cliente ... miramos si está tras un proxy.
    */
    function get_ip_client(){
        if (isset($HTTP_SERVER_VARS['Client-IP'])){
            $resultado = $HTTP_SERVER_VARS['Client-IP'];
        } elseif (isset($_SERVER['Client-IP'])){
            $resultado = $_SERVER['Client-IP'];
        } elseif (isset($HTTP_SERVER_VARS['REMOTE_ADDR'])){
            $resultado = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])){
            $resultado = $_SERVER['REMOTE_ADDR'];
        } else {
            $resultado = 'Unknown';
        }
        return $resultado;
    }
    function get_ip_inet(){
        if (isset($HTTP_SERVER_VARS['REMOTE_ADDR'])){
            $resultado = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])){
            $resultado = $_SERVER['REMOTE_ADDR'];
        } else {
            $resultado = 'Unknown';
        }
        return $resultado;
    }
    /* Determinamos el cliente */
    function get_http_user_agent(){
        $resultado = getenv("HTTP_USER_AGENT"); 
        /* Para lycos y otros servidores */ 
        if (empty($resultado)){
            if (isset($HTTP_SERVER_VARS['HTTP_USER_AGENT'])){
                $resultado = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
            } elseif (isset($_SERVER['HTTP_USER_AGENT'])){
                $resultado = $_SERVER['HTTP_USER_AGENT'];
            } else {
                $resultado = 'Unknown';
            }
        }
        return $resultado;
    }
    function get_cliente(){
        $cadena = $this -> http_user_agent;
        $str_out = 'Unknown';
        if (ereg('^Googlebot', $cadena)){
            return "Bot";
        } elseif (ereg('^Gigabot', $cadena)){
            return 'Bot';
        } elseif (ereg('Slurp/cat', $cadena)){
            return 'Bot';
        } elseif (ereg('www.almaden.ibm.com', $cadena)){
            return 'Bot';
        } elseif (ereg('Girafabot', $cadena)){
            return 'Bot';
        } elseif (ereg('BlogBot', $cadena)){
            return 'Bot';
        } elseif (ereg('Linkbot', $cadena)){
            return 'Bot';
        } elseif (ereg('WebCrawler', $cadena)){
            return 'Bot';
        } elseif (ereg('ZyBorg', $cadena)){
            return 'Bot';
        } elseif (ereg('Grub', $cadena)){
            return 'Bot';
        } elseif (ereg('Indy Library', $cadena)){
            return 'SpamBot';
        } elseif (ereg('WebCopier', $cadena)){
            return 'Mirror';
        } elseif (ereg('MSIE 3', $cadena)){
            return 'Internet Explorer 3';
        } elseif (ereg('MSIE 4', $cadena)){
            return 'Internet Explorer 4';
        } elseif (ereg('MSIE 5', $cadena)){
            return 'Internet Explorer 5';
        } elseif (ereg('MSIE 6', $cadena)){
            return 'Internet Explorer 6';
        } elseif (ereg('Netscape/7', $cadena)){
            return 'Netscape 7';
        } elseif (ereg('Konqueror', $cadena)){
            return 'Konqueror';
        } elseif (ereg('K-Meleon', $cadena)){
            return 'K-Meleon';
        } elseif (ereg('Gecko', $cadena)){
            return 'Mozilla';
        } elseif (ereg('Lynx', $cadena)){
            return 'Lynx';
        }
         return $str_out;
    }
}


/**
* La clase cls_dbtemplates es el objeto que los devolverá el valor de una determinada plantilla,
* ha sido creada como capa de acceso a plantillas, por si en algun momento surge la necesidad de
* guardar las plantillas en un formato u origen diferente.
* 
* Modificaciones:
*   2003.05.02 :  Se ha utilizado (condicion)? valor1  : valor2 ; para asignación de valores
* 
* @package Core
* @author Antonio Cortés <zippie@dr-zippie.net> 
* @copyright Copyright &copy; 2003-2006 Antonio Cortés
* @license BSD
*/
class cls_dbtemplates{
    /**
    * Dado un idtemplate nos devuelve su contenido
    * 
    * @param string $idtemplate Identificador de plantilla.
    * @return string Contenido de la plantilla.
    */
    function read_template($idtemplate){
        $comando_sql = "select content from {$GLOBALS['DB_prefijo']}templates where idtemplate='{$idtemplate}'";
        
	$recordset = $GLOBALS['DB']->execute($comando_sql) ;
        return (isset($recordset -> fields['content'])) 
            ? $recordset -> fields['content'] 
            : '<!-- Template not Found -->';
    }    
    
    function read_template_header($idtemplate){
        $comando_sql = "select content_header from {$GLOBALS['DB_prefijo']}templates where " .
            "idtemplate='{$idtemplate}'";
        $recordset = $GLOBALS['DB']->execute($comando_sql) ;
        return (isset($recordset -> fields['content_header'])) 
            ? $recordset -> fields['content_header'] 
            : '<!-- Template not Found -->';
    }
    
    function read_template_footer($idtemplate){
        $comando_sql = "select content_footer from {$GLOBALS['DB_prefijo']}templates where " .
            "idtemplate='{$idtemplate}'";
        $recordset = $GLOBALS['DB']->execute($comando_sql) ;
        return (isset($recordset -> fields['content_footer'])) 
            ? $recordset -> fields['content_footer'] 
            : '<!-- Template not Found -->';
    }
    function read_descripcion($idtemplate){
        $comando_sql = "select descripcion from {$GLOBALS['DB_prefijo']}templates where ".
            "idtemplate='{$idtemplate}'";
        $recordset = $GLOBALS['DB']->execute($comando_sql) ;
        return (isset($recordset -> fields['descripcion'])) 
            ? $recordset -> fields['descripcion'] 
            : '';
    }
}

/**
* Esta clase contendrá los arrays y variables comunes a MorcegoCMS y funciones para su lectura
*/
class cls_vars{
    var $mimetypes;
    var $mimetypes_docs;
    var $mimetypes_editables;
    var $path_repository;
    var $mimetype_images ;
    function cls_vars(){
        $this -> path_repository = 'lar' ;
        $this -> mimetypes = array(
            'doc' => 'application/msword',
            'pdf' => 'application/pdf',
            'rtf' => 'application/rtf',
            'gz' => 'application/x-gzip',
            'zip' => 'application/zip',
            'wav' => 'audio/x-wav',
            'mid' => 'audio/x-midi',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpe' => 'image/pjpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/x-png',
            'avi' => 'video/x-msvideo',
            'exe' => 'application/octet-stream',
            'html' => 'text/html',
            'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/x-javascript',
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
	
	$this -> mimetypes_images = array(
            'image/gif', 
	    'image/jpeg', 
	    'image/pjpeg', 
	    'image/jpeg', 
	    'image/x-png',
	    'image/png'
        );
        $this -> mimetypes_editables = array('text/plain',
            'text/html',
            'text/css',
            'application/x-javascript',
            'text/xml');
        $this -> mimetypes_docs = array('application/msword',
            'application/pdf',
            'application/rtf',
            'application/zip',
            'application/vnd.ms-excel',
            'application/octet-stream');
    }
    function extension_from_mimetype($mimetype){
        $resultado = 'tmp';
        foreach($this -> mimetypes as $key => $value){
            if ($value == $mimetype){
                $resultado = $key;
            }
        }
        return $resultado;
    }
}


/*
*  CLASE DE LOGIN
*/
class cls_security {
    var $logged = false ;
    var $user_string = '';
    var $idgroup = -1;
    var $username = 'anonymous';
    var $idadminpaginas = 1;
    var $name = 'anonymous';
    function cls_security() {
        $this->idgroup = -1; // usuario sin validar ...
//        echo "inicio!<br>";
 //       echo $_SESSION['user_string'] ;
        if ( isset( $_SESSION['username']) && isset( $_SESSION['user_string'] )  && isset( $_SESSION['iduser'] ) ) {
            if ( $_SESSION['user_string'] == $this->show_user_string($_SESSION['username'], $_SESSION['iduser']) ) {
                $this->logged = true;
                $this->idgroup = $_SESSION['idgroup'];
                $this->name = $_SESSION['name'];
                $this->username = $_SESSION['username'];
                $this->idadminpaginas = $_SESSION['idadminpaginas'];
            }
        }
    }
    function islogged() {
        return $this->logged ;
    }
    /* Nos devuelve si somos administradores (idgroup >= 4)  */
    function isAdmin() {
        return ($this->logged && $this->idgroup >=4 );    }
    function show_user_string( $username, $iduser) {
        $username = rtrim( ltrim( $username ));
        $idsession = session_id();
        if ( empty( $username) ){
            $str_out = md5(uniqid(rand(),1));
        } else {
            $str_out =  md5(  $_SERVER["HTTP_USER_AGENT"] . 
                $_SERVER["REMOTE_ADDR"]  . 
                $_SERVER["SERVER_NAME"] .
                $username . $iduser . $idsession );
        }
        return $str_out;
    }
    function login_user( $username, $password) {
        
        $SQL = "select iduser, password, newpassword, idadminpaginas, idgroup, name, username  from {$GLOBALS['DB_prefijo']}users where username = '{$username}'";
        $result = $GLOBALS['DB']->execute($SQL);
        if (!$result->EOF) {
            $pass2 = $result->fields['password'] ;
            $newpass2 = $result->fields['newpassword'] ;
            $salt  = substr( $pass2, 0, 2) ;
            $pass1 = crypt( $password, $salt );
            $newsalt  = substr( $newpass2, 0, 2) ;
            $newpass1 = crypt( $password, $newsalt );
            if ( $pass2 == $pass1 || (!empty( $newpass2 ) && $newpass2 == $newpass1)   ) {
                $this->username = $result->fields['username'];
                $this->idgroup = $result->fields['idgroup'];
                $this->idadminpaginas  = $result->fields['idadminpaginas'];
                $this->name  = $result->fields['name'];
                $_SESSION['username'] = $username ;
                $_SESSION['user_string'] = $this->show_user_string($username, $result->fields['iduser']);
                $_SESSION['iduser'] = $result->fields['iduser'];
                $_SESSION['name'] = $result->fields['name'];
                $_SESSION['idgroup'] = $result->fields['idgroup'];
                $_SESSION['idadminpaginas'] = $result->fields['idadminpaginas'];
                $this->logged = true ; 
                morcegocms_utils::log( 'INFO;LOGIN;[' . $username . ']' );
            }
        }
    }
    function logout_user() {
        if ($this->idgroup != -1 ) { 
            morcegocms_utils::log('INFO;LOGOUT;' . $_SESSION['username']  );
        }
        session_destroy();
    }
}
class morcego_session {
    function morcego_session() {
        session_set_save_handler(
            array(& $this, 'open'       ),
            array(& $this, 'close'      ),
            array(& $this, 'read'       ),
            array(& $this, 'write'      ),
            array(& $this, 'destroy'    ),
            array(& $this, 'gc'         )
            );
    session_start(); 
    }
    function open ($save_path, $sess_name) {
        return(true);
    }
    function close() {
        return(true);
    }
    function read($id) {
        $comando_sql = 'select content from ' .
            $GLOBALS['configCMS'] -> get_var('dbprefijo') . 'sessions where idsession = "' .
            $id . '"' ;
        $recordset = $GLOBALS['DB']->execute( $comando_sql );
        return ($recordset->RecordCount() == 1) ? $recordset->fields['content'] : '';
    }
    function write ($id, $sess_data) {
        if (empty( $sess_data )) {
            return true;
        }
        $GLOBALS['DB']->Replace($GLOBALS['configCMS'] -> get_var('dbprefijo') . 'sessions ' , 
            array( 'idsession' => $id,
                'content' => $sess_data,
                'iddate' => $GLOBALS['DB']->DBTimeStamp( time()) ),
            'idsession',
            true    );
        return true ;
    }
    function destroy ($id) {
       if ( count($_SESSION ) > 0 ) {
            $recordset = $GLOBALS['DB']->execute( 'delete from ' .
                $GLOBALS['configCMS'] -> get_var('dbprefijo') . 'sessions ' .
                    " where idsession = \"$id\"" );
        }
        return true ;
    }
    function gc ($var ) {
        $ahora = $GLOBALS['DB']->DBTimeStamp( time() - _MORCEGO_MAX_SESSION_TIME );
        $recordset = $GLOBALS['DB']->execute( 'delete from ' .
            $GLOBALS['configCMS'] -> get_var('dbprefijo') . 'sessions ' .
            " where iddate < {$ahora} " );
        return true;
    }
}
function MorcegoCMS_ErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
 if ( $errno === 2048    ) { return ;}
   /* 
    if ( $GLOBALS['last_error'] === true  ) {
        return ;
    }
    */
    
    $dt = date("Y-m-d H:i:s (T)");
    $errortype = array (
        1   =>  "Error",
        2   =>  "Warning",
        4   =>  "Parsing Error",
        8   =>  "Notice",
        16  =>  "Core Error",
        32  =>  "Core Warning",
        64  =>  "Compile Error",
        128 =>  "Compile Warning",
        256 =>  "User Error",
        512 =>  "User Warning",
        1024=>  "User Notice"
        );
	$error['datetime'] 	= $dt;
	$error['requesturi'] 	= $_SERVER["REQUEST_URI"] ;
	$error['num'] 		= $errno ;
	$error['type'] 		=  ( (isset( $errortype[$errno] )) ?  $errortype[$errno] : 'desconocido' );
	$error['msg'] 		= $errmsg ;
	$error['scriptname']	=$filename;
	$error['linenum'] 		= $linenum ;
	$error['MorcegoCMS'] 	= _MORCEGO_VERSION ;
	$error['PHP'] 		= phpversion() ;  
    
	morcegocms_utils::log( 'ERROR;MorcegoCMS;' .  serialize( $error )  );
	// error_log(var_export( $error ) , 0);
	if (_MORCEGO_SEND_EMAIL_ERROR == true ) {
		$Headers = "X-Mailer: MorcegoCMS " . _MORCEGO_VERSION . " - PHP: " . phpversion(); 
		mail(_MORCEGO_EMAIL_ERROR ,"Error en MorcegoCMS " . _MORCEGO_VERSION , var_export( $error ) , $Headers );
	}
    $GLOBALS['last_error'] = true ;
}

class morcegocms_utils {
    // 
    function matheval($ecuacion){
        $resultado = 0;
        $cadena = $ecuacion ;
        $ecuacion = '';
        for( $i = 0; $i < strlen( $cadena ); $i++ ) {
            if( strstr(  "1234567890()/%*+-", $cadena[$i]) != false ) { $ecuacion .= $cadena[$i]; }
        }
        if ( $ecuacion == "" ) { $resultado = 0; } else { eval("\$resultado = " . $ecuacion . ";"); }
        return $resultado;
    }
    
    function log( $content) {
        $GLOBALS['DB']->Replace( $GLOBALS['configCMS'] -> get_var('dbprefijo') . 'log' , 
            array( 
                'idlog' => 'null',
                'date' => $GLOBALS['DB']->DBTimeStamp( time()),
                'iduser' => (isset( $_SESSION['iduser'] )) ? $_SESSION['iduser'] : '-1'  , 
                'ip' => $GLOBALS['statsCMS']->ip_inet ,
                'idpagina' => ( isset($GLOBALS['pagina'])  && is_object( $GLOBALS['pagina'] )) ? $GLOBALS['pagina']->Campos['idpagina'] : '' ,
                'content' => $content ),
                'idlog',
            true    );        
    }
    /*
         Borra todos los objetos del caché de objetos
    
    */
    
    function EmptyCacheObjects() {
        $GLOBALS['DB']->execute( "delete from " . $GLOBALS['configCMS']->get_var('dbprefijo') ."objects " );
    }
    /*
        Borra todos los archivos del cache con un determinado prefijo
    
    */
        function EmptyCacheFiles( $prefix = "") {
            $cache_path = dirname( __FILE__). '/../../' . $GLOBALS['varsCMS']->path_repository  ;
            $hd=opendir($cache_path );
            while ($file = readdir($hd)) {
                if ( substr( $file, 0, 6 + strlen( $prefix)) == 'cache.' . $prefix ) {
                    unlink( $cache_path . '/'. $file );
                } 
            }
            closedir( $hd );
    }

    
    
    
        /**
     * Dada un uid de página nos devuelve el uidroot de la página.
     */
    function uidrootfromuid($uid){
     $uidparent = $uid ;
     $idbusqueda = $uid;
     $resultado = $uid ;
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
     while ($uidparent != 0){
         $comando_sql = "select  uidparent from " . $GLOBALS['DB_prefijo'] . "paginas where uid = $idbusqueda";
         $recordset = $GLOBALS['DB']->execute($comando_sql) ;
         if ($recordset -> RecordCount() == 0){
             $resultado = $idbusqueda;
             $uidparent = 0 ;
             }else{
             $uidparent = $recordset -> fields['uidparent'];
             if (!$uidparent == 0){
                 $idbusqueda = $recordset -> fields['uidparent'];
                 }else{
                 $resultado = $idbusqueda;
                 }
             }
         }
     return $resultado;
    }
    
    function uidfromidpagina ( $idpagina ){
    /**
    * Nos devuelve el uid de una pagina dando como unico parámetro el idpagina
    */
    $comando_sql = "select  uid from " . $GLOBALS['DB_prefijo'] . "paginas where idpagina = \"{$idpagina}\"";
    $rs = $GLOBALS['DB']->execute(  $comando_sql ) ;
        return ( isset($rs->fields['uid'] )) ? $rs->fields['uid']  : false;
        
    }
    function fecha($valor){
     $resultado = "$valor";
     $atmp = explode(" ", $resultado);
     $resultado = $atmp[0];
     return $resultado ;
    }
    function fecha_actual(){
        /* 
        $formato = ( isset( $aCadena[2] ) && !empty($aCadena[2])) ? $aCadena[2] : 'd/m/Y';
	$resultado =  date( $formato , $this->pagina['fecha']  );
        */
         $dias = array ('0' => 'Domingo', '1' => 'Lunes',
         '2' => 'Martes', '3' => 'Miércoles', '4' => 'Jueves',
         '5' => 'Viernes', '6' => 'Sábado');
         $meses = array ('01' => 'Enero', '02' => 'Febrero',
         '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo',
         '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
         '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre',
         '12' => 'Diciembre');
         $fecha_actual = $dias[ date('w')] . " " . date('d') . ' de ' . $meses[ date('m') ] . ' de ' . date('Y');
         return $fecha_actual;
    }
    
    function titulofromuid($uid){
    /**
     * Dada una id nos devuelve el titulo
     */
     $comando_sql = "select titulo from " . $GLOBALS['DB_prefijo'] . "paginas where uid = $uid";
     $resultado = $GLOBALS['DB'] -> execute($comando_sql);
     $titulo = (isset( $resultado -> fields['titulo'] )) ? $resultado -> fields['titulo'] : '' ;
     return $titulo;
    }
    function titulofromidpagina($idpagina){
    /**
     * Dada una idpagina nos devuelve el titulo
     */
     $comando_sql = "select titulo from " . $GLOBALS['DB_prefijo'] . "paginas where idpagina = " .
         $GLOBALS['DB']->Quote($idpagina) ;
     $resultado = $GLOBALS['DB'] -> execute($comando_sql);
     $titulo = (isset( $resultado -> fields['titulo'] )) ? $resultado -> fields['titulo'] : '' ;
     return $titulo;
    }
    
    
    function idpaginafromuid($uid){
     if( empty( $uid )) $uid = 0;
     
    /**
     * Dada una id nos devuelve el titulo
     */
     $comando_sql = "select idpagina from " . $GLOBALS['DB_prefijo'] . "paginas where uid = $uid";
     $resultado = $GLOBALS['DB'] -> execute($comando_sql);
     return $resultado -> fields['idpagina'];
    }
    function make_seed(){
     list($usec, $sec) = explode(' ', microtime());
     return (float) $sec + ((float) $usec * 100000);
    }
    function contenidofichero($fichero){
     $str_out = '';
     $hf = fopen($fichero, 'r');
     if (!empty($hf)){
         while (!feof($hf)){
             $str_out .= fgets($hf, 2000);
             }
         fclose($hf);
         }
     return $str_out;
    }
    function WordCount( $string ) {
    // return a integer with the number of words in the String
        return (strlen( $string) -  strlen( ereg_replace( '[A-Z]', '' ,ucwords(strtolower($string))))); 
    }
    
}



class HTML_TAG {
    function IsClosedTag( $tag ) {
       $aTags = array(
            'img'       => 1,
            'link'      => 1,
            'meta'      => 1,
            'br'        => 1,
            'input'     => 1,
            'frame'     => 1
        );
        
        return (isset($aTags[$tag])) ? $aTags[$tag] : 0 ;
    }
}

class HtmlContainer{
    var $Elements = array() ;
    var $Tag = '';
    var $HtmlOut = '';
    var $Parameters = array();
    var $Content = '';
    var $UniqueTag = 0;
    function &add( $ObjectType, $Parameters=array(), $Content = '', $Object = '' ) {
       if ( is_object( $ObjectType )) {
          return $this->add_object( $ObjectType );
        } else {
          $order  = count( $this->Elements) + 1 ;
          if( is_object( $Object ) ) {
            $this->Elements[ $order ] =  new HtmlObject( strtolower(  $ObjectType ) ,  $Parameters, $Content); 
            $this->Elements[ $order ]->Elements[1] =  $Object  ;
          } else {
            $this->Elements[ $order ] =  new HtmlObject( strtolower(  $ObjectType ) ,  $Parameters, $Content ); 
          }
          return $this->Elements[ $order ];
        }
    }
    
    
    function set_value( $attribute, $value ) {
        $this->Parameters[$attribute] = $value ;

    }
    
   function render() {
        foreach ( $this->Elements as $Element ) {
            if( is_object( $Element) ) {
                $this->HtmlOut .= $Element->render();
            }
        }
        return $this->HtmlOut;
    }
    function data ( $ObjectType = '', $parameters = array(), $content = '') {
        $this->Parameters = $parameters;
        $this->Content = $content;
        $this->Tag = $ObjectType ;

    }
    function ClosedTag() {
        $str_out = '<' . strtolower( $this->Tag );
            if (is_array( $this->Parameters)) {
                foreach( $this->Parameters as $key => $val ) {
                    $key = strtolower( $key ) ;
                    $str_out .= ( $val === false ) ? '' :( ($val === true ) ? " {$key}" : " {$key}=\"{$val}\"" );
                }
            }
        $str_out .='/>';
        return $str_out ;
    
    }
    function OpenTag() {
        $str_out = '<' . strtolower( $this->Tag ) ;
            if (is_array( $this->Parameters)) {
                foreach( $this->Parameters as $key => $val ) {
                    $key = strtolower( $key ) ;
                    $str_out .= ( $val === false ) ? '' :( ($val === true ) ? " {$key}" : " {$key}=\"{$val}\"" );
                }
            }
        $str_out .='>';
        // $str_out .=">\n";
        return $str_out ;
    }
    function CloseTag() {
        return '</' . strtolower( $this->Tag ) . '>';
    
    }
    function &get_first_element( $type )  {
        $str_out = -1 ;
        for( $i = 1; $i <= count( $this->Elements); $i++) {

            if ( strtolower( $this->Elements[$i]->Tag)  ==  strtolower( $type )) {
                return $this->Elements[$i];
           }
        }
    }
    
    
   function &get_element( $type )  {
        $str_out = -1 ;
        for( $i = 1; $i <= count( $this->Elements); $i++) {

            if ( strtolower( $this->Elements[$i]->Tag)  ==  strtolower( $type )) {
                return $this->Elements[$i];
           } else {
		 $resultado =& $this->Elements[$i]->get_element( $type  );
		if ( is_object( $resultado )) {
                        return $resultado;
                 }
	   }
        }
    }
    
    function &get_element_by_id( $id )  {
        $str_out = -1 ;
        for( $i = 1; $i <= count( $this->Elements); $i++) {
            $Element =& $this->Elements[$i];
            if (  is_array( $Element->Parameters) && isset( $Element->Parameters['id']) && $Element->Parameters['id'] === $id ){
                return $this->Elements[$i] ;
            } else {
                if( is_object( $Element) ) {
                    $resultado =& $this->Elements[$i]->get_element_by_id( $id  );
                    if ( is_object( $resultado )) {
                        return $resultado;
                    }
                }
            }
        }
        return $str_out;

    }
       function &get_element_by_tag( $type )  {
       $type = strtolower( $type ) ;
        $str_out = -1 ;
        for( $i = 1; $i <= count( $this->Elements); $i++) {
            $Element =& $this->Elements[$i];
            if (  $this->Elements[$i]->Tag ==$type  ){
                return $this->Elements[$i] ;
            } else {
                if( is_object( $Element) ) {
                    $resultado =& $this->Elements[$i]->get_element_by_tag( $type  );
                    if ( is_object( $resultado )) {
                        return $resultado;
                    }
                }
            }
        }
        return $str_out;

    } 
    
    
    function get_last_element( $type )  {
        $str_out = -1 ;
        for( $i = 1; $i <= count( $this->Elements); $i++) {
            if ( $this->Elements[$i]->Tag ==$type ) {
                $str_out = $i;
           }
        }
        if( $str_out != -1) {
            return $this->Elements[$str_out];
        } else  {
            return $str_out; 
        }
    }
    function get_first_element_id( $type )  {
        $str_out = -1 ;
        for( $i = 1; $i <= count( $this->Elements); $i++) {
            if ( strtolower( $this->Elements[$i]->Tag)  ==  strtolower( $type )) {
                return $i ;
           }
        }
    }
    function get_last_element_id( $type )  {
        $str_out = -1 ;
        for( $i = 1; $i <= count( $this->Elements); $i++) {
            if ( $this->Elements[$i]->Tag ==$type ) {
                $str_out = $i;
           }
        }
        return $str_out; 
        
    }
    function &add_object( $object )  {
        // añade un objeto a el actual objeto 
        $order  = count( $this->Elements) + 1 ;
        $this->Elements[ $order ] =  $object ; 
        return $this->Elements[ $order ];
    }
    function add_text( $Text) {
        $this->add( '', '', $Text) ;
    }

    
    
}

class HtmlObject extends HtmlContainer { 
    var $aTags = array();
    function HtmlObject( $ObjectType  = '',$Parameters ='' , $Content='' , $Object = ''){ 
        $this->Tag = $ObjectType;
        $this->Parameters = $Parameters;
        $this->Content = $Content;
        if ( is_object( $Object ) ) {
          $this->add( $Object ) ;
        }
    }
    function render() {
        // echo "*{$this->Tag}*<BR>";
        if (empty($this->Tag)) {
            parent::render() ;
            return $this->Content;
        }
        if ( HTML_TAG::IsClosedTag($this->Tag)  == 0 ) {
            
            if ( $this->Tag == 'script' || $this->Tag == 'style' ) {
            return  $this->OpenTag() .
                    // (( !empty( $this->Content) ) ? "\n \\\\ <![CDATA[\n" . $this->Content . "\n \\\\ ]]>\n"  : '' ).
                    $this->Content . 
                    parent::render() . 
                    $this->CloseTag() ;
            
            } else {
                return  $this->OpenTag() . 
                    $this->Content . 
                    parent::render() . 
                    $this->CloseTag() ;
            }
        } else {
                return $this->ClosedTag() .
                parent::render() ;
        }
        
    }
}
 
/*
 *!* TODO: Debemos pasar la gestion de idiomas a la base de datos.
*/
class morcegocms_lang {
    function get_array_lang() {
        return array ( 
            'es' => 'Español',
            'gz'    => 'Gallego',
            'ct'    => 'Catalán',
            'va'    => 'Vasco',
            'en'    => 'Inglés',
            'de'    => 'Alemán',
            'fr'    => 'Francés');
    }
}
if ( !defined( '_MORCEGOCMS_ADMIN') ) {
	include('morcegocms_view.php' );
}












$GLOBALS['last_error'] = false;
/*  por problemas en sourceforge
 ini_set('include_path', './:' . @ini_get('include_path'));
*/
$GLOBALS['configCMS']           = new config_morcegocms; /* Configuración del motor */
if ( _MORCEGO_DB_LOG) { $GLOBALS['configCMS']->DB->LogSQL(); }
/**
* @var cls_vars 
*/
$GLOBALS['varsCMS']             = new cls_vars; /* Objeto con las variables del cliente actual */
$GLOBALS['DB']                  = & $GLOBALS['configCMS'] -> DB; /* Conexión a la base de datos */
$GLOBALS['session_handler']     = new morcego_session(); /* manejador de sesiones  */
$GLOBALS['statsCMS']            = new cls_stats;
$GLOBALS['DB_prefijo']          =  $GLOBALS['configCMS'] -> get_var('dbprefijo');
$GLOBALS['prefijo_funciones']   =  $GLOBALS['configCMS'] -> get_var('prefijofunciones');
$GLOBALS['configCMS']->DB->SetFetchMode(ADODB_FETCH_ASSOC);
$GLOBALS['oUser']               =& $GLOBALS['statsCMS']->User ;
error_reporting(E_ALL);  
if ( _MORCEGO_SESSIONS )  {
  set_error_handler("MorcegoCMS_ErrorHandler");
}

?>