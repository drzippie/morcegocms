<?
include_once( dirname( __FILE__ ) . '/class_usersmodel.php' );
class adm_usuarios  extends adm_class {
    
    var $model ;
    var $aNiveles = array( '0'  => '0 - Usuario Registrado',
            '1'         => '1 - ',
            '2'         => '2 - ',
            '3'         => '3 - ',
            '4'         => '4 - Administrador',
            '5'         => '5 - Administrador [root]');
     var $aAdminPaginas = array( '1'  => '1 - Normal',
            '2'         => '2 - Solapas ' /* ,
            '3'         => '3 - Árbol de Páginas' */ );            
    
    
    function adm_usuarios( $html  = '' ) {
	$this->model = new usersmodel( &$GLOBALS['DB'], $GLOBALS['DB_prefijo'] );
	
        $this->parameters =& $GLOBALS['aArgumentos'] ;
        if ( !is_object( $html ) ) { 
            $this->oHtml = html_admin::ObjectPage();
        } else {
            $this->oHtml = $html;
        }
        if (!isset( $this->parameters[1] )) {
            $this->parameters[1] = '' ;
        }
        switch ( $this->parameters[1] ) {
            case 'list':
                $this->show_list();
                break;
            case 'edit':
                $this->show_form($this->parameters[2], 'edit');
                break;
            case 'add':
                $this->show_form('', 'add');
                break;
            case 'edit_ok':
                $this->show_edit_ok($this->parameters[2]);
                break;
            case 'add_ok':
                $this->show_add_ok($this->parameters[2]);
                break;
            case 'delete':
                $this->show_del_user($this->parameters[2] );
                
                break;
            default:
                $this->show_list();
        }
    }
    
    
    
    
    
    
    
    function show_list() {
	$listaUsuarios = $this->model->findAll();
        $html = new htmlcontainer();
        $html->add('br');
        $table =& $html->add('table', array(
       
            'class' => 'ruler wide'));
        $tr =& $table->add( 'tr' );
            $tr->add('th', '', '&nbsp;');
            $tr->add('th', '', 'Login');
            $tr->add('th', '', 'Nombre completo');
            $tr->add('th', '', 'nivel');
            $tr->add('th', '', '&nbsp;');
		$tdClass = 'non';
	foreach ( $listaUsuarios as $usuario ) {
            $iduser    	= $usuario['iduser'];
            $username  	= $usuario['username'];
            $name      	= $usuario['name'];
            $idgroup   	= $usuario['idgroup'];
            $notas     	= $usuario['notas'];
            $idadminpaginas     = $usuario['idadminpaginas'];
 		   	$tdClass = ($tdClass === 'non') ? false : 'non' ;

            $tr =& $table->add( 'tr' , array('class' => $tdClass ));
                $td =& $tr->add('td');
                $td->add_object( CustomHTML::Boton16x16( 
                    _ADM_BOTON_EDITAR , 
                    "admin.php?q=users/edit/{$iduser}",  
                    'Modificar usuario' ));
                $tr->add('td', '', $username );
                $tr->add('td', '', $name );
                $tr->add('td', '', $this->aNiveles["$idgroup"] );
                $td =& $tr->add('td');
                $td->add_object( CustomHTML::boton16x16( 
                    _ADM_BOTON_BORRAR , 
                    '#',  
                    'Borrar Usuario',
                    '', 
                    "confirmar('¿ Desea Borrar el usuario, esta acción no se podrá deshacer".
                        " ?','admin.php?q=users/delete/{$iduser}');"
                    ));
        }
        $html->add(CustomHTML::botonAdmin('Añadir usuario', "Añadir usuario", "admin.php?q=users/add") );
        $this->render( $html, 'Listado de usuarios', '');
    }
    

function show_form( $iduser, $accion = 'add',  $msg_error = '' )  {
        // 
        $html = new htmlcontainer();
        if ( $accion === 'edit') {
	    $data = $this->model->read( $iduser ) ;
            if ( ! is_array( $data ) ) {
                header( 'Location: admin.php?q=users/list');
                die();
            }
        } else {
	    $data = $this->model->emptyFields() ;
        }
           $iduser    =& $data['iduser'];
           $username  =& $data['username'];
           $name      =& $data['name'];
           $idgroup   =& $data['idgroup'];
           $notas     =& $data['notas'];
	   $email     =& $data['email']; 
	    
            $html->add('br');
            
            $form =& $html->add('form',array(
                'name' => 'formulario',
                'method' => 'post',
                'action' => 'admin.php?q=users/' . $accion . '_ok/' . $iduser ));
            $divc =& $form->add('div', array(
                'class' => 'body',
                'style' => 'width: 340px;'));
            $div =& $divc->add( 'div', array(
                'class' => 'contenido odd'));
            if (!empty( $msg_error )) {
                $div->add('h5', array(
                    'class' => 'error'),
                    $msg_error );
            }
			
			$div->add( customHTML::inputLine( 'Nombre Completo',  	new htmlobject( 'input', array( 'type' => 'text', 'name' => 'name',  'id' => 'name', 'value' => $name ) ))  );
			$div->add( customHTML::inputLine( 'Nombre Usuario',  	new htmlobject( 'input', array( 'type' => 'text', 'name' => 'username',  'id' => 'username', 'value' => $username ) ))  );
			$div->add( customHTML::inputLine( 'Contraseña',  	new htmlobject( 'input', array( 'type' => 'password', 'name' => 'password',  'id' => 'password', 'value' => '' ) ))  );
			$div->add( customHTML::inputLine( 'Repetir contraseña',  	new htmlobject( 'input', array( 'type' => 'password', 'name' => 'password2',  'id' => 'password2', 'value' => '' ) ))  );
 			$div->add( customHTML::inputLine( 'Correo Electrónico',  	new htmlobject( 'input', array( 'type' => 'text', 'name' => 'email',  'id' => 'email', 'value' => $email ) ))  );
			$div->add( customHTML::inputLine( 'Nivel',   $this->html_select_nivel( $idgroup ) ) );
			$div->add( customHTML::inputLine( 'Notas',  	new htmlobject( 'textarea', array(  'name' => 'notas',  'id' => 'notas' ), $notas ) ));
 			
			$botones = new htmlcontainer();
			$botones->add(CustomHTML::botonAdmin('Modificar', "Modificar Datos", '', "document.formulario.submit()"));
		    $botones->add( CustomHTML::botonAdmin('Cancelar', "Cancelar y salir",'',  "history.go( -1 );") ) ;

			$div->add( customHTML::inputLine( '',  $botones )  );
            $titulo = ( $accion === 'add' ) ? 'Alta de usuarios' : 'Edición de usuarios' ;
        
        $this->render( $html, $titulo , '');
       
    }
function show_add_ok() {
        if ( $_POST['password'] != $_POST['password2'] || empty($_POST['password'] ) ) {
            $this->show_form( '', 'add', 'Ambas contraseñas deben ser iguales y no estar vacias');
        } else {
            $comando_sql = 'select max(iduser + 1) as iduser from ' . $GLOBALS['DB_prefijo'] . 'users' ;
            $recordset = $GLOBALS['DB']->execute( $comando_sql );
		if (get_magic_quotes_gpc() == 1 ) {
			$_POST = array_map( 'stripslashes', $_POST);
		}


		$cambios = array( 
                'username'  => $_POST['username'],
                'idgroup'   => $_POST['idgroup'],
                'name'      => $_POST['name'],
                'email'     => $_POST['email'],
                'notas'     => $_POST['notas'],
                'password'  => crypt($_POST['password'], substr(md5(uniqid(rand(),1)),0,2)),
                'iduser'    => $recordset->fields['iduser']);
            $GLOBALS['DB']->replace( 
                $GLOBALS['DB_prefijo'] . 'users',
                $cambios,
                'iduser',
                true);
            Header( 'Location: admin.php?q=users/list' );
            die();
        }
    }
    function show_edit_ok( $iduser ) {
        if ( !empty($_POST['password']) && $_POST['password'] == $_POST['password2'] ) {
            $cryp_password = crypt($_POST['password'], substr(md5(uniqid(rand(),1)),0,2));
            $sql_password = "password = '{$cryp_password}', " ;
        } else {
            $sql_password = '';
        }
	if (get_magic_quotes_gpc() == 1 ) {
			$_POST = array_map( 'stripslashes', $_POST);
		}

        $cambios = array( 
            'username'  => $_POST['username'],
            'idgroup'   => $_POST['idgroup'],
            'name'      => $_POST['name'],
            'email'     => $_POST['email'],
            'notas'     => $_POST['notas'],
            'iduser'    => $iduser );
        if ( !empty($_POST['password']) && $_POST['password'] == $_POST['password2'] ) {
            $cambios['password'] =  crypt($_POST['password'], substr(md5(uniqid(rand(),1)),0,2));
        }
        $GLOBALS['DB']->replace(
            $GLOBALS['DB_prefijo'].'users',
            $cambios,
            'iduser',
            true );
            Header( 'Location: ./admin.php?q=users/list' );
            die();

    }
    
    function show_del_user( $iduser ) {
        global  $DB, $DB_prefijo;
	
        $comando_sql = "delete from {$GLOBALS['DB_prefijo']}users where iduser = {$iduser}";
        $GLOBALS['DB']->execute( $comando_sql );
	Header( 'Location: admin.php?q=users/list' );
	die();
    }

    function html_select_nivel( $value ) {
        reset( $this->aNiveles);
        $html = new htmlobject( 'select', array('name' => 'idgroup', 'id'=>'idgroup'));
         while (list ($key, $val) = each ($this->aNiveles)) {
			$html->add('option', array( 'value' => $key,  'selected' => (($value == $key) ? true: false )  ), $val);
        }
        return $html;
    }
      function html_select_adminpaginas( $value ) {
        reset( $this->aNiveles);
        $str_out = '<select name="idadminpaginas">';
        while (list ($key, $val) = each ($this->aAdminPaginas)) {
            $str_out .= "<option value='{$key}'";
            if ( "$value" == $key ) {
                $str_out .= ' selected ';
            }
            $str_out .= ">{$val}</option>";
        }
        return $str_out;
    }
    
}



?>