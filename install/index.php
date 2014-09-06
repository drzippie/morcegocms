<?php
$oInstall = new install_morcegocms ;

class install_morcegocms {
    var $paso = '';
    var $servidor;
    var $dbpass;
    var $dbprefijo;
    var $dbuser;
    var $dbdb;
    var $dbpass_crypt;
    var $crear_tablas;
function install_morcegocms() {
        // $this->paso =& $_SERVER['QUERY_STRING'];
        $this->dbserver = (isset($_POST['dbserver'])) ? $_POST['dbserver'] : 'localhost';
        $this->dbpass = (isset($_POST['dbpass'])) ? $_POST['dbpass'] : '';
        $this->dbprefijo = (isset($_POST['dbprefijo'])) ? $_POST['dbprefijo'] : 'morcego_';
        $this->dbdb = (isset($_POST['dbdb'])) ? $_POST['dbdb'] : 'morcegocms';
        $this->dbuser = (isset($_POST['dbuser'])) ? $_POST['dbuser'] : 'root';
        $this->paso = (isset($_POST['accion'])) ? $_POST['accion'] : 'init';
        $this->crear_tablas = (isset($_POST['creartablas'])) ? true : false ;
        $this->dbpass_crypt = $this->crypt( $this->dbpass );
        $this->main();
    }
    function main() {
        $this->ini_html();
        switch ( $this->paso ) {
            case "init":
                $this->form_html();
                break ;
            case "comprobar":
                $resultado = $this->comprobar();
                if (!empty( $resultado ) ) {
                    $this->form_html( $resultado);
                } else {
                    $this->save_config() ;
                    if ( $this->crear_tablas) {
                        $this->make_data();
                    }
                    echo "<h4>MorcegoCMS ha sido instalado satisfactoriamente, para poder utilizarlo deberá borrar el directorio 'install'<BR>Para acceder a la administración logueese como morcego/m0rceg0<BR>Cambie la contraseña de administración en cuanto pueda</h4>";
                }
                break;
            default:
                $this->form_html();
                break ;
        }
        $this->fin_html();
    }
    function comprobar() {
        $str_out = '';
        $directorio_base = dirname(__FILE__) . '/../';
        // 1. Directorio Lar ... de escritura para el php
        $fh = fopen( "{$directorio_base}lar/cache.config.test", 'w');
        if (!$fh ) {
            $str_out .= '<LI>El directorio /lar (sistema de caché) debe tener permisos de escritura para el servidor web</LI>';
        } else {
           fclose( $fh);
            @unlink(  "{$directorio_base}lar/cache.config.test" ); 
        }
        // 2. includes/config.ini.php
        $fichero = "{$directorio_base}includes/config.ini.php";
        $fh = @fopen( $fichero, 'a');
        if (!$fh ) {
            $str_out .= '<LI>El fichero /includes/config.ini.php (valores de configuración) debe tener permisos de escritura para el servidor web, al menos en el momento de la instalación</LI>';
        } else {
            fclose( $fh);
        }
        $conexion = mysql_connect( $this->dbserver, $this->dbuser, $this->dbpass);
        if (!$conexion ) {
           $str_out .= '<LI>No se ha podido conectar al MySQL compruebe los datos de acceso: servidor, usuario y contraseña.</LI>';
        } else {
            $resultado = mysql_select_db( $this->dbdb, $conexion );
            if (!$resultado) {
                $str_out .= '<LI>La base de datos especificada [' . $this->dbdb . '] No existe en el servidor.</LI>';
            }
        }
        return (empty($str_out)) ? '' : '<OL class="error">' . $str_out . '</OL>' ;
    }



    function save_config() {
        $directorio_base = dirname(__FILE__) . '/../';
        $fichero = "{$directorio_base}includes/config.ini.php";
        $fh = fopen( $fichero, 'w');
            $cadena = "; <?php die('[Registro de Seguridad]:Intento de Acceso no autorizado registrado.'); /*
[datos]
dbtipo=\"MySQL\"
dbservidor=\"{$this->dbserver}\"
dbusuario=\"{$this->dbuser}\"
dbpassword=\"{$this->dbpass_crypt}\"
dbbasedatos=\"{$this->dbdb}\"
dbprefijo=\"{$this->dbprefijo}\"
?> ";
    fwrite( $fh, $cadena );
    fclose( $fh );
    }
    
    
    function form_html( $cadena = '') {
        echo "<h2>Instalación de MorcegoCMS</h2>
            <P>Mediante esta herramienta de instalación podrá crear de forma dinámica el archivo de configuración
            de su web y crear las tablas necesarias para empezar a trabajar con morcegoCMS</P>
            <P>En caso de alguna duda o sugerencia no deje de acudir a la web oficial 
            <a href=\"http://morcegocms.sourceforge.net\" target=\"_new\">Morcegocms.sourceforge.net</a>
            </P>
            <div>{$cadena}</div>
            <form action=\"index.php\" method=\"post\">
            <table border=\"0\">
            <tr>
            <td>Servidor MySQL: </td>
            <td><input type=\"text\" name=\"dbserver\" value=\"{$this->dbserver}\"> ". 
            $this->ico_ayuda('servidor' ).
            "<td>
            </tr>
            <tr>
            <td>Usuario de Acceso al MySQL: </td>
            <td><input type=\"text\" name=\"dbuser\" value=\"{$this->dbuser}\"> ". $this->ico_ayuda('dbuser') . 
            "<td>
            </tr>
            <tr>
            <td>Password de Acceso al MySQL: </td>
            <td><input type=\"password\" name=\"dbpass\" value=\"{$this->dbpass}\"> ". $this->ico_ayuda('dbpass') .
            "<td>
            </tr>
            <tr>
            <td>Base de Datos: </td>
            <td><input type=\"text\" name=\"dbdb\" value=\"{$this->dbdb}\"> ". $this->ico_ayuda('dbdb') . 
            
            "<td>
            </tr>            
            <tr>
            <td>Prefijo de las Tablas: </td>
            <td><input type=\"text\" name=\"dbprefijo\" value=\"{$this->dbprefijo}\"> ". 
            
            $this->ico_ayuda('dbprefijo') . "<td>
            </tr>
            <tr>
            <td>Crear tablas: </td>
            <td><input type=\"checkbox\" name=\"creartablas\" checked> <I>Si no está marcado solo creará el archivo de configuración</I><td>
            </tr>
            <tr>
            <td></td>
            <td><input type=\"submit\" value=\"Comprobar Configuración\"><td>
            </tr>
            </table>
            <input type=\"hidden\" value=\"comprobar\" name=\"accion\">
            </form>"  ;
            
            $this->divs_ayuda();
    }
    
    



    function ini_html( $titulo = '') {
        echo "<html><head><title>Instalación de MorcegoCMS :: {$titulo}</title>\n".
            "<link type=\"text/css\" href=\"estilo.css\" rel=\"stylesheet\">" .
            "<script type=\"text/javascript\" src=\"javascript.js\" language=\"javascript\"></script>".
            "\n</head>".
            "<body>\n" ;
    }
    function fin_html() {
        echo "\n</body></html>";
    }
    function make_data() {
        $conexion = mysql_connect( $this->dbserver, $this->dbuser, $this->dbpass);
        mysql_select_db( $this->dbdb, $conexion );
        $ArraySQL = file( 'morcegocms.sql');
        foreach ( $ArraySQL as $comandoSQL ) {
            $comandoSQL = str_replace( '%!%prefijo%!%', $this->dbprefijo, $comandoSQL);
            // echo $comandoSQL . '<BR>';
            mysql_query( $comandoSQL, $conexion );
        }
        /*
        Importamos los datos ahora ... 
        */
        $elementos  = new MorcegoCMS_Archiver( 'read', './base.dat' )  ;
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
          $comandoSQL = "insert into {$this->dbprefijo}{$table} ({$camposSQL}) values ($valoresSQL)";
         mysql_query(  $comandoSQL );
        }
        
    }
    function crypt ($cadena) {
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
    function divs_ayuda() {
    /*
        $this->servidor = (isset($_POST['servidor'])) ? $_POST['servidor'] : 'localhost';
        $this->dbpass = (isset($_POST['dbpass'])) ? $_POST['dbpass'] : '';
        $this->dbprefijo = (isset($_POST['dbprefijo'])) ? $_POST['dbprefijo'] : 'morcego_';
        $this->dbdb = (isset($_POST['dbdb'])) ? $_POST['dbdb'] : 'morcegocms';
        $this->dbuser = (isset($_POST['dbuser'])) ? $_POST['dbuser'] : 'root';
        $this->paso = (isset($_POST['accion'])) ? $_POST['accion'] : 'init';
        $this->crear_tablas = (isset($_POST['creartablas'])) ? true : false ;
        $this->dbpass_crypt = $this->crypt( $this->dbpass );
    */
        $aControles = array ( 
            "servidor" => array( 'Servidor MySQL', "Nombre o IP del servidor MySQL, si es el mismo que el servidor web se puede indicar: <I>localhost</I>" ),
            "dbpass" => array( 'Contraseña MySQL', "Contraseña de acceso al servidor MySQL" ),
            "dbprefijo" => array( 'Prefijo tablas', "Prefijo que tendrás las tablas de MorcegoCMS, mediante el uso de diferentes prefijos podremos tener varias webs con MorcegoCMS utilizando la misma base de datos de MySQL" ),
            "dbdb" => array( 'Base de datos', "Nombre de la base de datos a utilizar en el MySQL" ),
            "dbuser" => array( 'Usuario MySQL', "Usuario de acceso al MySQL" )
            );
        
        foreach( $aControles  as $control => $texto ) {
            echo '
            <div id="help' . $control . '" class="notaayuda" >
        <center>
        <table bgcolor="#F4F4AD" cellpadding="5" cellspacing="0" border="0" width="200">
        <tr>
        <td>
        <table bgcolor="#F4F4AD" cellpadding="0" cellspacing="0" border="0" width="200">
        <tr>
        <td>' . $texto[0] . ':</td><td align="right"><a href="javascript:hidenote(' .
        "'help{$control}'" .
        ');"><img src="images/x.gif" border="0"></a></td>
        </tr>
        <tr>
        <td colspan="2">
        <hr color="#FFFFFF">
        </td>
        </tr>
        <tr>
        <td colspan="2" >' .
        $texto[1] . 
        '</td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </center>
        </div> ';
        }

        }
        function ico_ayuda( $nombre_control ) {
            $str_out = "<a href=\"#\" onMouseDown=\"shownote(event, 'help{$nombre_control}');\" ><img src=\"images/help.gif\" border=\"0\"></a>" ;
           return $str_out ;
        }
}

class morcegoCMS_Archiver {
    var $elementos; // array con el contenido del archivo
    var $fichero;
    var $hf;
    var $fileContent = '';
    var $filesize;
    var $files ; // array con los archivos contenidos en el fichero (sólo para módo lectura );
    
    
    function morcegoCMS_Archiver( $accion = 'read',  $fichero = '') {
        $this->elementos = array();
        if ( !empty( $fichero)) {
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
        $posicion = 0 ;
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