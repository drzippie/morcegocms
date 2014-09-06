<?php
include_once( './includes/core_admin.php' );
global $oUser;
if ( !$oUser->isAdmin() ) {
  header('Location: ./index.php?q=login/1' );
  die();
}
/* 
creamos el objeto con el html base de la p�gina  
*/
$html = html_admin::ObjectPage();
$peticion = &$_SERVER["QUERY_STRING"] ;
$aArgumentos = explode("/", $peticion) ;
global $accion;
$accion = $aArgumentos[0];
switch ( $accion ){
	case 'paginas':
		require_once( './includes/class_admpaginas.php' );
		$objadmin = new adm_paginas( $html ) ;
		break;
	/* Gesti�n de logs */
	case 'logs':
		require_once('./includes/class_admlogs.php');
		$objadmin = new adm_logs( $html ) ;
		break;
	/* Gesti�n de la configuraci�n */
	case 'edit_config':
		require_once('./includes/class_admconfig.php');
		$objadmin = new adm_config( $html ) ;
		break;
  
  /* Gesti�n de usuarios */ 
  case 'users': 
    require_once('./includes/class_admusuarios.php');
    $objadmin = new adm_usuarios($html );
    break;
  
  /* Gesti�n de estad�sticas */ 
  case 'stats':
    require_once('./includes/class_admstats.php');
    $objadmin = new adm_stats($html );
    break;

  /* Gesti�n de utilidades  */ 
  case 'utils':
    require_once('./includes/class_utils.php');
    $objadmin = new adm_utils( $html );
    break;                              
  
  /* Gesti�n de plantillas */ 
  case 'templates':
    require_once('./includes/class_admtemplates.php');
    $objadmin = new adm_templates($html );
    break;                              

  /* Gesti�n de archivos */ 
  case 'files':
    require_once('./includes/class_admfiles.php');
    $objadmin = new adm_files($html);
    break;                  

  /* Gesti�n de sesiones */
  case 'sesiones':
    require_once('./includes/class_admsesiones.php');
    $objadmin = new adm_sesiones($html);
    break;                  

  /* Utilidad de limpieza de html */
  case 'htmlcleaner':
    require_once('./includes/class_htmlcleaner.php');
    $objadmin = new adm_htmlcleaner($html );
    break;                          
  
  /* Utilidad de exportaci�n/importacion */
  case 'export':
    require_once('./includes/class_export.php');
    $objadmin = new adm_export($html );
    break;                              

  /* Gesti�n de botones */ 
  case 'botones':
    require_once('./includes/class_admbotones.php');
    $objadmin = new adm_botones($html );
    break; 

  /* Si no se indica nada se mostrar� la pantalla principal de la administraci�n */ 
  default:
    html_admin::IndexAdmin();
    break;
}

?>