<?php

class morcegocms_functions_css  {
	 function href( $cadena ) {
		$strOut = '';
		$aCadena = explode(":", $cadena);
		$media =& $aCadena[0] ;

		if ( empty( $media)) {
			$mediaAttribute = '' ;
		} else {
			$mediaAttribute = ' media="' . $media . '" ' ;
		}
		$cssFiles = explode( ',' , implode(':', array_slice( $aCadena, 1 )) );
		foreach( $cssFiles as $cssFile ) {
			if (!empty( $cssFile )) {
				$strOut .= '<link href="' . $cssFile . '" rel="stylesheet" type="text/css"' . $mediaAttribute .' />' ; 
			}
		}
		return  $strOut ;
	}
}

?>