<?php

class morcegocms_functions_js  {

  function href( $cadena ) {
	$strOut = '';
	$jsFiles = explode( ',', $cadena  );
	foreach( $jsFiles as $jsFile ) {
		if (!empty( $jsFile )) {
			$strOut .= '<script src="' . $jsFile . '" type="text/javascript"></script>';
		}
	}
	return  $strOut ;
  }




}

?>