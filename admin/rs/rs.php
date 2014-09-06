<?php 
/*
	Funciones en PHP para uso general en la administracion sin necesidad de estar validados
*/
include  dirname( __FILE__ ) . '/../includes/core_admin.php';
include_once  dirname( __FILE__ ) . '/drzippie-rs.php';
class procesos_admin extends drzippie_rs {
        function send_password( $parameters )  {
            global $DB, $DB_prefijo;
            $username =& $_POST['username'];
            $str_out = '---';
            $newpassword = substr( md5(uniqid(  rand())), 0, 10 ) ;
            $newpassword_crypt = crypt( $newpassword,  $newpassword );
            $comando_sql ="select email from {$DB_prefijo}users where username = \"{$username}\" ";
            $recordset = $DB->execute( $comando_sql ) ;
            if ( $recordset->EOF ) {
                unset($oLog);morcegocms_utils::log(  'ERROR;LOGIN;PASSWORD REMINDER: Usuario no existe [' . $username . ']' );
                $str_out = 'El usuario especificado no existe ';
            } else  {
                $email =  $recordset->fields['email'] ;
                if ( empty( $email)) {
                    morcegocms_utils::log(  'ERROR;LOGIN;PASSWORD REMINDER: Email no v�lido [' . $username . ']' );
                    $str_out = 'No se ha podido enviar una nueva contrase�a, el usuario no tiene un email v�lido';
                } else {
                   $resultado = @mail( $email, 'MorcegoCMS :: Nueva contrase�a', 
                        "Se ha creado una nueva contrase�a para su acceso a la administraci�n\n" .
                        "Contrase�a: " . $newpassword );
			
                    if ( $resultado ) {
                        $comando_sql ="update {$DB_prefijo}users  set newpassword=\"{$newpassword_crypt}\" where username = \"{$username}\" ";
                        $recordset = $DB->execute( $comando_sql ) ;
                        morcegocms_utils::log( 'INFO;LOGIN;PASSWORD REMINDER: Nueva Contrase�a enviada a [' . $username . ']' );
                        $str_out = 'Su nueva contrase�a ha sido enviado por correo electr�nico';
                    } else {
                        morcegocms_utils::log(  'ERROR;LOGIN;PASSWORD REMINDER: Error enviando email a [' . $username . ']' );
                        $str_out = 'No se ha podido enviar una nueva contrase�a, el usuario no tiene un email v�lido';
                    }
                }
            }
            
            return  urlencode(  $str_out  ) ;
        }
        function login_user($parameters) {
            $username = $_POST['user'];
            $password = $_POST['password'];
            $str_out = '0:ok';
            $GLOBALS['oUser']->login_user( $username, $password);
            if (!$GLOBALS['oUser']->isLogged()) {
                morcegocms_utils::log(  'ERROR;LOGIN;FAILED LOGIN: User/Pass invalido para [' . $username . ']' );
                unset($oLog);
                $this->return_error( "Nombre de usuario/Contrase�a incorrectos", '1' ); ;
            } else {
                morcegocms_utils::log( 'INFO;LOGIN;[' . $username . ']' );

            }
            $this->return_ok( '' );
        }
    }


    $oRS = new procesos_admin (  array( 
        "send_password",
        "login_user"
        ));
    $oRS->action();
    
   
    
?>