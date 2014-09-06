<?php
/**
* clase para la generación dinámica de botones.
* 
* 
*
* @package Core
* @author Antonio Cortés <zippie@dr-zippie.net> 
* @copyright Copyright &copy; 2003 Antonio Cortés
* @license BSD
*/
class cls_base_boton{
    var $aBoton;
    var $texto ;
    var $cache ;
    var $path_imagen ;
    var $url_imagen ;
    var $tmp_imagen ;
    /**
    * Constructor de la clase
    * 
    * @param array $aBoton Contiene una matriz con la definición del botón.
    * @param string $texto Texto del botón.
    * @param string $alt Texto para el alt del tag del botón
    * @param string $align Alineado del botón
    */
    function cls_base_boton($aBoton, $texto , $path){
        $this -> aBoton = $aBoton;
        /*  verificamos y arreglamos valores */
        $this->aBoton['color_transparente'] = str_pad( 
            trim($this->aBoton['color_transparente']), 6, '0' , STR_PAD_LEFT);
        $this->aBoton['color_fondo'] = str_pad( 
            trim($this->aBoton['color_fondo']),6,  '0' , STR_PAD_LEFT); 
        $this->aBoton['color_texto'] = str_pad( 
            trim($this->aBoton['color_texto']),6,  '0', STR_PAD_LEFT);
        $this -> texto = $texto;
        $imagen_tmp = 'cache.boton.' .  md5( $this -> aBoton['nombre'] .  $texto) . '.png' ;
        $this -> tmp_imagen = $imagen_tmp;
        $this -> path_imagen = dirname(__FILE__) . '/../../' . 
            $GLOBALS['varsCMS'] -> path_repository . '/' . $imagen_tmp;
        $this->url_imagen = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') 
            ? $GLOBALS['configCMS']->get_var('rutaweb') .  "botones/" . 
                md5( $this -> aBoton['nombre'] .  $texto) . ".png" 
            : ((empty($path)) 
                ? $GLOBALS['configCMS']->get_var('rutaweb') .  $GLOBALS['varsCMS'] -> path_repository 
                : $path ) . '/' . $imagen_tmp ; 

    }
    /*
    * Nos genera el botón como una imagen y la guarda en disco, devolviendo un array con nombre_fichero, width y height.
    * 
    * @return array
    */
    function generar_boton(){
        if ( $GLOBALS['configCMS']->get_var('GD2') === 'true' )  {
            $imagecreate = 'imagecreatetruecolor';
        } else {
            $imagecreate = 'imagecreate';
        }
        $correccion_x = (isset($this -> aBoton['correccion_x'])) ?
            $this -> aBoton['correccion_x'] : 0 ;
        $correccion_y = (isset($this -> aBoton['correccion_y'])) ?
            $this -> aBoton['correccion_y'] : 0 ;
        $aSize = imagettfbbox($this -> aBoton['ttf_size'], 0,
        $this -> aBoton['ttf'], $this -> texto);
        $alto_texto = abs(max($aSize[1] - $aSize[7], $aSize[3] - $aSize[5])) ;
        $ancho_texto = abs($aSize[2] - $aSize[0]);
        if ($this -> aBoton['grafico'] == 1){
            $im_derecha = imagecreatefrompng($this -> aBoton['derecha']) ;
            $im_centro = imagecreatefrompng($this -> aBoton['centro']) ;
            $im_izquierda = imagecreatefrompng($this -> aBoton['izquierda']) ;
        } else {
            $im_derecha = $imagecreate(5, $alto_texto + 10) ;
            $im_centro = $imagecreate(5, $alto_texto + 10) ;
            $im_izquierda = $imagecreate(5, $alto_texto + 10) ;
            $color_transparente_derecha = ImageColorAllocate($im_derecha,
                hexdec(substr($this -> aBoton['color_transparente'], 0, 2)),
                hexdec(substr($this -> aBoton['color_transparente'], 2, 2)),
                hexdec(substr($this -> aBoton['color_transparente'], 4, 2)));
            $color_transparente_izquierda = ImageColorAllocate($im_izquierda,
                hexdec(substr($this -> aBoton['color_transparente'], 0, 2)),
                hexdec(substr($this -> aBoton['color_transparente'], 2, 2)),
                hexdec(substr($this -> aBoton['color_transparente'], 4, 2)));
            $color_transparente_centro = ImageColorAllocate($im_centro,
                hexdec(substr($this -> aBoton['color_transparente'], 0, 2)),
                hexdec(substr($this -> aBoton['color_transparente'], 2, 2)),
                hexdec(substr($this -> aBoton['color_transparente'], 4, 2)));
            imagefilledrectangle ($im_derecha, 0, 0, 5 , imagesy($im_derecha),
                $color_transparente_derecha);
            imagefilledrectangle ($im_centro, 0, 0, 5 , imagesy($im_centro),
                $color_transparente_centro);
            imagefilledrectangle ($im_izquierda, 0, 0, 5 , imagesy($im_izquierda),
                $color_transparente_izquierda);
        }
        $alto = imagesy($im_centro);
        $ancho_derecha = imagesx($im_derecha) ;
        $ancho_izquierda = imagesx($im_izquierda);
        if ($this -> aBoton['ancho'] == 0){
            $ancho_centro = $ancho_texto;
            $ancho = $ancho_centro + $ancho_izquierda + $ancho_derecha ;
        } else {
            $ancho = $this -> aBoton['ancho'];
            $ancho_centro = $ancho - ($ancho_derecha + $ancho_izquierda) ;
        }
        $imagen_destino = $imagecreate($ancho, $alto);
        $color_transparente = ImageColorAllocate($imagen_destino,
            hexdec(substr($this -> aBoton['color_transparente'], 0, 2)),
            hexdec(substr($this -> aBoton['color_transparente'], 2, 2)),
            hexdec(substr($this -> aBoton['color_transparente'], 4, 2)));
        $color_fondo = ImageColorAllocate($imagen_destino,
            hexdec(substr($this -> aBoton['color_fondo'], 0, 2)),
            hexdec(substr($this -> aBoton['color_fondo'], 2, 2)),
            hexdec(substr($this -> aBoton['color_fondo'], 4, 2)));
        $color_texto = ImageColorAllocate($imagen_destino,
            hexdec(substr($this -> aBoton['color_texto'], 0, 2)),
            hexdec(substr($this -> aBoton['color_texto'], 2, 2)),
            hexdec(substr($this -> aBoton['color_texto'], 4, 2)));
        if (isset($this -> aBoton['color_sombra'])){
            $color_sombra = ImageColorAllocate($imagen_destino,
                hexdec(substr($this -> aBoton['color_sombra'], 0, 2)),
                hexdec(substr($this -> aBoton['color_sombra'], 2, 2)),
                hexdec(substr($this -> aBoton['color_sombra'], 4, 2)));
        }
        imagecopyresized ($imagen_destino, $im_centro, $ancho_izquierda -2 ,
            0 , 0, 0 , $ancho_centro + 4 , $alto, imagesx($im_centro), $alto);
        imagecopyresized ($imagen_destino, $im_izquierda, 0 , 0 , 0, 0 ,
            $ancho_izquierda , $alto, $ancho_izquierda, $alto);
        imagecopyresized ($imagen_destino, $im_derecha, $ancho_izquierda + $ancho_centro , 0 , 0, 0 ,
            $ancho_derecha , $alto, $ancho_derecha, $alto);
        if ( $correccion_x == 0 ) {
            $ttf_x = $ancho_izquierda + (($ancho_centro / 2) - ($ancho_texto / 2)) ;
        } else {
            $ttf_x = $ancho_izquierda + $correccion_x;
        }
        if ( $correccion_y != 0 ) {
            $ttf_y =  $correccion_y ;
        } else {
            $ttf_y = ($alto / 2) + ($alto_texto / 2)  ;
        }
        ImageTTFText($imagen_destino, $this -> aBoton['ttf_size'], 0, $ttf_x ,
            $ttf_y , $color_transparente, $this -> aBoton['ttf'], $this -> texto);
        if (isset($color_sombra)){
            ImageTTFText($imagen_destino, $this -> aBoton['ttf_size'], 0, $ttf_x + 1,
                $ttf_y + 1 , - $color_sombra, $this -> aBoton['ttf'], $this -> texto);
        }
        ImageTTFText($imagen_destino, $this -> aBoton['ttf_size'], 0, $ttf_x ,
            $ttf_y , $color_texto, $this -> aBoton['ttf'], $this -> texto);
        ImageColorTransparent($imagen_destino, $color_transparente);
        imagePNG($imagen_destino, $this -> path_imagen);
        return array( $this->url_imagen, $alto, $ancho );
    }
}


/**
* cls_boton: clase para la generación dinámica de botones.
* 
* Modificaciones:
*   2003.05.02 :  Se ha utilizado (condicion)? valor1  : valor2 ; para asignación de valores
* 
* @package Core
* @author Antonio Cortés <zippie@dr-zippie.net> 
* @copyright Copyright &copy; 2003 Antonio Cortés
* @license BSD
*/
class cls_boton{
    var $url_imagen;
    var $alto ;
    var $ancho ;
    var $alt;
    var $align;
    /**
    * Constructor de la clase
    * 
    * @param array $aBoton Contiene una matriz con la definición del botón.
    * @param string $texto Texto del botón.
    * @param string $alt Texto para el alt del tag del botón
    * @param string $align Alineado del botón
    */
    function cls_boton($aBoton, $texto, $alt = '', $align = '', $path = ''){
        $oBoton = new cls_base_boton( $aBoton, $texto, $path );
        $aBoton = $oBoton->generar_boton();
        unset( $oBoton ); 
        $this->url_imagen = $aBoton[0];
        $this->alto = $aBoton[1];
        $this->ancho = $aBoton[2];
        $this->alt = $alt;
        $this->align = $align;
    }
    /**
    * Nos genera el botón como una imagen y la guarda en disco, devolviendo el tag img completo de este botón
    * 
    * @return string tag img del botón resultante
    */
    function render_boton(){
        $str_out = "<img src=\"{$this->url_imagen}\" height=\"{$this->alto}\" width=\"{$this->ancho}\" alt=\"{$this->alt}\" border=\"0\"/>";
        return $str_out ;
    }
}

/**
* cls_rollover: clase para la generación dinámica de botones (rollover).
* 
* Modificaciones:
*   2003.05.02 :  Se ha utilizado (condicion)? valor1  : valor2 ; para asignación de valores
* 
* @package Core
* @author Antonio Cortés <zippie@dr-zippie.net> 
* @copyright Copyright &copy; 2003-2006 Antonio Cortés
* @license BSD
*/
class cls_rollover{
    var $url_imagen_on;
    var $url_imagen_off;
    var $alto ;
    var $ancho ;
    var $alt;
    var $align;
    /**
    * Constructor de la clase
    * 
    * @param array $aBoton Contiene una matriz con la definición del botón.
    * @param string $texto Texto del botón.
    * @param string $alt Texto para el alt del tag del botón
    * @param string $align Alineado del botón
    */
    function cls_rollover($aBoton_on, $aBoton_off,  $texto, $alt = '', $align = '', $path = ''){
        $oBoton_on  = new cls_base_boton( $aBoton_on, $texto, $path );
        $aBoton_on  = $oBoton_on->generar_boton();
        unset( $oBoton_on ); 
        $oBoton_off  = new cls_base_boton( $aBoton_off, $texto, $path );
        $aBoton_off  = $oBoton_off->generar_boton();
        unset( $oBoton_off ); 
        $this->url_imagen_on = $aBoton_on[0];
        $this->url_imagen_off = $aBoton_off[0];
        $this->alto = ( $aBoton_on[1] > $aBoton_off[1] ) ? $aBoton_on[1] : $aBoton_off[1];
        $this->ancho = ( $aBoton_on[2] > $aBoton_off[2] ) ? $aBoton_on[2] : $aBoton_off[2];;
        $this->alt = $alt;
        $this->align = $align;
    }
    /**
    * Nos genera el botón como una imagen y la guarda en disco, devolviendo el tag img completo de este botón
    * 
    * @return string tag img del botón resultante
    */
    function render_boton(){
        $str_out =  sprintf( '<img src="%s" name="rollover_%s" ' .
            "onmouseover=\"this.src='%s'\" ".
            "onmouseout=\"this.src='%s'\" ".
            ' border="0" align="%s" alt="%s" width="%s" height="%s"/>',
        $this->url_imagen_off,
            md5( $this->url_imagen_on ), 
            $this->url_imagen_on,
            $this->url_imagen_off,
            $this->align,
            $this->alt,
            $this->ancho,
            $this->alto);
        return $str_out ;
    }
}


?>