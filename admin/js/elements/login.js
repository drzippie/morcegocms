function checkLogin( ) {
		url = 'rs/rs.php';
    	var opt = {
		method: 'post',
		postBody: 'accion=login_user&user='+$F( 'username' )+'&password='+ $F( 'password') ,
		onSuccess: function(t) {
			resultado = t.responseText.URLDecode() ;
			if ( resultado.split(':')[0] == '0' ) {
				document.location.href= document.location.href ;
			} else {
				alert( resultado.split(':')[1] );
			} 
        	},
		on404: function(t) {
			alert('Error 404: location "' + t.statusText + '" was not found.');
		},
		onFailure: function(t) {
			alert('Error ' + t.status + ' -- ' + t.statusText);
		}
		
	}
	new Ajax.Request( 'rs/rs.php', opt);
	return false
}
function send_password() {
		url = 'rs/rs.php';
		var opt = {
		method: 'post',
		postBody: 'accion=send_password&username='+ $F( 'username' )  ,
		onSuccess: function(t) {
			alert( 'ok' );
			alert( t.responseText.split(":")[1].URLDecode()	 ) ; 
			},
		on404: function(t) {
			alert('Error 404: location "' + t.statusText + '" was not found.');
		},
		onFailure: function(t) {
			alert('Error ' + t.status + ' -- ' + t.statusText);
		}
	}
	new Ajax.Request( 'rs/rs.php', opt);
	return false
	}