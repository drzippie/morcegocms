function confirmar( mensaje, destino) {    if (confirm(mensaje)) {        document.location = destino ;    }}
function shownote(evnt,idElemento) {    objeto = document.getElementById(idElemento);    objeto.style.visibility="visible";    if (document.all) {	objeto.style.top = evnt.y + document.body.scrollTop - 15;        objeto.style.left = evnt.x + document.body.scrollLeft ;    } else {	objeto.style.top = evnt.pageY ;	        objeto.style.left = evnt.pageX ;	    }}
function hidenote(idElemento) {    objeto = document.getElementById(idElemento);    objeto.style.visibility = "hidden";	}