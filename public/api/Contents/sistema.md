# Sistema


<p style="text-align: justify;">
En este apartado esta todo lo relacionado con el usuario, roles y permisos. se pueden configurara permisos por acción lo que hace mas seguro y mas robusto el modelo de permisos.
<br>
Todos las peticiones se hacen via http con las cabeceras que se explicaron en el capitulo 1. 
<br>
Aca un ejemplo de como hacer una peticion http con cURL de PHP
</p>

Ejemplo cURL (PHP)

	<?php
		//cabeceras incluidas en la petición necesarias para el acceso a la API
		$headers = array(
			"Content-type: application/json;charset=\"utf-8\"",
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Authorization: Bearer yTl379mxyVpGgmjREZs6UF9p6VILca4e33WN8wtd",
			"X-Usuario: ramirez.esquinca@gmail.com"
		);
		
		// url en el caso de PUT, DELETE y GET(show) agregar el parametro /{id}
		$url = "http://187.217.219.55/cium/api/v1/Accion";
		
		// Metodo para el llamado de la url,
		$metodo = array("POST","GET","PUT","DELETE");
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo[0]);
		
		//para los metodos PUT y POST que envian json
		if(count($json_data)>0){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_data));  
		}

		$datos = curl_exec($ch); 
		// si existe error retornarlo en caso contrario regresar el valor devuelto de la API
		if (curl_errno($ch)) {
			return curl_errno($ch);
		}else {
			curl_close($ch);
			return $datos;
		}
	?>
	


## Grupo


<p style="text-align: justify;">
Grupo creacion y administracion de los gurpos y configuracion de las acceso a modulos por accion. 
</p>


<HR>
### GET (INDEX)


Muestra una lista de los grupos según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Grupo*](http://187.217.219.55/cium/api/v1/Grupo)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Grupo?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Grupo?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Grupo?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Grupo?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
			  "id": 1,
			  "name": "Admin",
			  "permissions": {
					"IndicadorController.index": 1,
					"IndicadorController.show": 1,
					"IndicadorController.store": 1,
					"IndicadorController.update": 1,
					"IndicadorController.destroy": 1
				}
			}
		],
		"total": 1
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>total</code> Total de registros devueltos
> - <code>permissions</code> Objeto con las acciones por modulo

<HR>	
### GET/ {ID} (SHOW)


Devuelve la información del registro especificado.

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Grupo/{ID}*](http://187.217.219.55/cium/api/v1/Grupo/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Grupo/1*](http://187.217.219.55/cium/api/v1/Grupo/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"name": "Admin",
			"permissions": {
				"IndicadorController.index": 1,
				"IndicadorController.show": 1,
				"IndicadorController.store": 1,
				"IndicadorController.update": 1,
				"IndicadorController.destroy": 1
			}
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar.

	{
		"id": 1,
		"name": "Admin",
		"permissions": {
			"IndicadorController.index": 1,
			"IndicadorController.show": 1,
			"IndicadorController.store": 1,
			"IndicadorController.update": 1,
			"IndicadorController.destroy": 1
		}
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/Grupo*](http://187.217.219.55/cium/api/v1/Grupo)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 1,
			"name": "Admin",
			"permissions": {
				"IndicadorController.index": 1,
				"IndicadorController.show": 1,
				"IndicadorController.store": 1,
				"IndicadorController.update": 1,
				"IndicadorController.destroy": 1
			},
			"creadoAl": "2015-10-11  00:00:00",
			"modificadoAl": "2015-10-11  00:00:00",
			"borradoAl": null
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>
### PUT/{ID} (UPDATE)


Actualizar el registro especificado en el la base de datos

REQUEST (SEND)

Recibe un Input Request con el json de los datos

	{
		"id": 1,
		"name": "Admin",
		"creadoAl": "2015-10-11  00:00:00",
		"modificadoAl": "2015-10-11  00:00:00",
		"borradoAl": null,
		"permissions": {
			"IndicadorController.index": 1,
			"IndicadorController.show": 1,
			"IndicadorController.store": 1,
			"IndicadorController.update": 1,
			"IndicadorController.destroy": 1
		}
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/Grupo/{ID}*](http://187.217.219.55/cium/api/v1/Grupo/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Grupo/1*](http://187.217.219.55/cium/api/v1/Grupo/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"name": "Admin",
			"permissions": {
				"IndicadorController.index": 1,
				"IndicadorController.show": 1,
				"IndicadorController.store": 1,
				"IndicadorController.update": 1,
				"IndicadorController.destroy": 1
			},
			"creadoAl": "2015-10-11  00:00:00",
			"modificadoAl": "2015-10-11  00:00:00",
			"borradoAl": null
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>

### DELETE/{ID} (DELETE)


Borra el registro especificado en el la base de datos


Petición

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/Grupo/{ID}*](http://187.217.219.55/cium/api/v1/Grupo/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Grupo/1*](http://187.217.219.55/cium/api/v1/Grupo/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"name": "Admin",
			"permissions": {
				"IndicadorController.index": 1,
				"IndicadorController.show": 1,
				"IndicadorController.store": 1,
				"IndicadorController.update": 1,
				"IndicadorController.destroy": 1
			},
			"creadoAl": "2015-10-11  00:00:00",
			"modificadoAl": "2015-10-11  00:00:00",
			"borradoAl": "2015-10-11 16:19:03"
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>


## Modulo


<p style="text-align: justify;">
SysModulo administra los modulos y controladores de laravel para crear las acciones por modulo, se usa para generar los permisos por grupo. 
</p>


<HR>
### GET (INDEX)


Muestra una lista de los modulos según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/SysModulo*](http://187.217.219.55/cium/api/v1/SysModulo)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/SysModulo?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/SysModulo?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/SysModulo?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/SysModulo?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 1,
				"idPadre": 0,
				"nombre": "Dashboard",
				"controladorLaravel": "DashboardController",
				"vista": "1",
				"creadoAl": "2015-03-09 18:43:36",
				"modificadoAl": "2015-07-15 18:56:05",
				"borradoAl": null,
				"padres": null
			}
		]
		"total": 1
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>total</code> Total de registros devueltos

<HR>	
### GET/ {ID} (SHOW)


Devuelve la información del registro especificado.

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/SysModulo/{ID}*](http://187.217.219.55/cium/api/v1/SysModulo/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 3**

> - [*http://187.217.219.55/cium/api/v1/SysModulo/3*](http://187.217.219.55/cium/api/v1/SysModulo/3) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 3,
			"idPadre": 2,
			"nombre": "Indicadores",
			"controladorLaravel": "IndicadorController",
			"vista": "1",
			"creadoAl": "2015-03-09 18:49:13",
			"modificadoAl": "2015-03-09 20:15:56",
			"borradoAl": null,
			"padres": {
				"id": 2,
				"idPadre": 0,
				"nombre": "Catalogos",
				"controladorLaravel": "",
				"vista": "0",
				"creadoAl": "2015-03-09 18:48:06",
				"modificadoAl": "2015-04-10 18:52:30",
				"borradoAl": null
			}
		},
		"metodos": [
			{
				"id": 6,
				"idModulo": 3,
				"nombre": "Listar",
				"recurso": "index",
				"metodo": "get",
				"creadoAl": "2015-03-10 11:30:59",
				"modificadoAl": "2015-03-10 11:30:59",
				"borradoAl": null
			},
			{
				"id": 7,
				"idModulo": 3,
				"nombre": "Ver",
				"recurso": "show",
				"metodo": "get",
				"creadoAl": "2015-03-10 18:44:45",
				"modificadoAl": "2015-03-10 18:44:45",
				"borradoAl": null
			},
			{
				"id": 8,
				"idModulo": 3,
				"nombre": "Guardar",
				"recurso": "store",
				"metodo": "post",
				"creadoAl": "2015-03-10 18:47:44",
				"modificadoAl": "2015-03-10 18:47:44",
				"borradoAl": null
			},
			{
				"id": 9,
				"idModulo": 3,
				"nombre": "Modificar",
				"recurso": "update",
				"metodo": "put",
				"creadoAl": "2015-03-10 18:48:44",
				"modificadoAl": "2015-03-10 18:48:44",
				"borradoAl": null
			},
			{
				"id": 10,
				"idModulo": 3,
				"nombre": "Eliminar",
				"recurso": "destroy",
				"metodo": "delete",
				"creadoAl": "2015-03-10 18:49:04",
				"modificadoAl": "2015-03-10 18:49:04",
				"borradoAl": null
			}
		]
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar.

	{
		"id": 3,
		"idPadre": 2,
		"nombre": "Indicadores",
		"controladorLaravel": "IndicadorController",
		"vista": "1"
		"padres": {
			"id": 2,
			"idPadre": 0,
			"nombre": "Catalogos",
			"controladorLaravel": "",
			"vista": "0"			
		},
		"metodos": [
			{
				"id": 6,
				"idModulo": 3,
				"nombre": "Listar",
				"recurso": "index",
				"metodo": "get",
				"creadoAl": "2015-03-10 11:30:59",
				"modificadoAl": "2015-03-10 11:30:59",
				"borradoAl": null
			}
		]
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/SysModulo*](http://187.217.219.55/cium/api/v1/SysModulo)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 3,
			"idPadre": 2,
			"nombre": "Indicadores",
			"controladorLaravel": "IndicadorController",
			"vista": "1",
			"creadoAl": "2015-03-09 18:49:13",
			"modificadoAl": "2015-03-09 20:15:56",
			"borradoAl": null
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>
### PUT/{ID} (UPDATE)


Actualizar el registro especificado en el la base de datos

REQUEST (SEND)

Recibe un Input Request con el json de los datos

	{
		"id": 3,
		"idPadre": 2,
		"nombre": "Indicadores",
		"controladorLaravel": "IndicadorController",
		"vista": "1",
		"creadoAl": "2015-03-09 18:49:13",
		"modificadoAl": "2015-03-09 20:15:56",
		"borradoAl": null,
		"padres": {
			"id": 2,
			"idPadre": 0,
			"nombre": "Catalogos",
			"controladorLaravel": "",
			"vista": "0"			
		},
		"metodos": [
			{
				"id": 6,
				"idModulo": 3,
				"nombre": "Listar",
				"recurso": "index",
				"metodo": "get",
				"creadoAl": "2015-03-10 11:30:59",
				"modificadoAl": "2015-03-10 11:30:59",
				"borradoAl": null
			}
		]
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/SysModulo/{ID}*](http://187.217.219.55/cium/api/v1/SysModulo/%7bID%7d) 

>**Ejemplo actualizar el registro con id 3**

> - [*http://187.217.219.55/cium/api/v1/SysModulo/3*](http://187.217.219.55/cium/api/v1/SysModulo/3)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 3,
			"idPadre": 2,
			"nombre": "Indicadores",
			"controladorLaravel": "IndicadorController",
			"vista": "1",
			"creadoAl": "2015-03-09 18:49:13",
			"modificadoAl": "2015-03-09 20:15:56",
			"borradoAl": null
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>

### DELETE/{ID} (DELETE)


Borra el registro especificado en el la base de datos


Petición

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/SysModulo/{ID}*](http://187.217.219.55/cium/api/v1/SysModulo/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/SysModulo/1*](http://187.217.219.55/cium/api/v1/SysModulo/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 3,
			"idPadre": 2,
			"nombre": "Indicadores",
			"controladorLaravel": "IndicadorController",
			"vista": "1",
			"creadoAl": "2015-03-09 18:49:13",
			"modificadoAl": "2015-03-09 20:15:56",				
			"borradoAl": "2015-10-11 16:19:03"
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>


## Usuario


<p style="text-align: justify;">
Usuario administra los usuarios que provienen de OAUTH2.0 para que puedan tener acceso al sistema. 
</p>


<HR>
### GET (INDEX)


Muestra una lista de los usuarios según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/SysUsuario*](http://187.217.219.55/cium/api/v1/SysUsuario)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/SysUsuario?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/SysUsuario?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/SysUsuario?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/SysUsuario?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 1,
				"username": "root",
				"email": "root@localhost",
				"permissions": [],
				"activated": true,
				"activated_at": null,
				"last_login": "2015-03-10 16:26:16",
				"nombres": "root",
				"apellidoPaterno": "super",
				"apellidoMaterno": "usuario",
				"cargo": "Root admin",
				"nivel": null,
				"telefono": "",
				"avatar": null,
				"creadoAl": "2014-06-13 18:53:24",
				"modificadoAl": "2015-08-04 20:29:17",
				"borradoAl": null,
				"throttles": {
					"id": 1,
					"user_id": 1,
					"ip_address": null,
					"attempts": 0,
					"suspended": false,
					"banned": false,
					"last_attempt_at": null,
					"suspended_at": null,
					"banned_at": null
				}
			}
		]
		"total": 1
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>total</code> Total de registros devueltos
> - <code>permissions</code> Para denegar o asingar permisos especiales sobre un grupo
> - <code>throttles</code> Si el usuario esta baneado, no se usa

<HR>	
### GET/ {ID} (SHOW)


Devuelve la información del registro especificado.

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/SysUsuario/{ID}*](http://187.217.219.55/cium/api/v1/SysUsuario/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 3**

> - [*http://187.217.219.55/cium/api/v1/SysUsuario/3*](http://187.217.219.55/cium/api/v1/SysUsuario/3) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 3,
			"username": "Admin",
			"email": "ramirez.esquinca@gmail.com",
			"permissions": [],
			"activated": true,
			"activated_at": null,
			"last_login": "2015-10-11 19:05:39",
			"nombres": "Eliecer",
			"apellidoPaterno": "ramirez",
			"apellidoMaterno": "esquinca",
			"cargo": "Admin",
			"nivel": 1,
			"telefono": "",
			"avatar": "http://localhost/SSA_MATERIAL/CIUM/assets/img/user.png",
			"creadoAl": "2015-03-13 17:50:38",
			"modificadoAl": "2015-10-11 19:05:39",
			"borradoAl": null,
			"UsuarioZona": [],
			"grupos": [
				{
					"id": 1,
					"name": "Admin",
					"permissions": {
						"IndicadorController.index": 1,
						"IndicadorController.show": 1,
						"IndicadorController.store": 1,
						"IndicadorController.update": 1,
						"IndicadorController.destroy": 1
					}
					"creadoAl": "2015-03-10 17:36:15",
					"modificadoAl": "2015-09-01 14:23:18",
					"borradoAl": null,
					"pivot": {
						"usuario_id": 3,
						"grupo_id": 1
					}
				}
			]
		}
	}

>**Nota**

> - <code>"UsuarioZona": []</code> Nivel 1 estatal
> - <code>"UsuarioZona": [{
    "id": "PICHUCALCO",
    "nombre": "PICHUCALCO"
  }]</code> Nivel 2 jurisdiccional
> - <code>"UsuarioZona": [{
        "id": 1,
        "idZona": 1,
        "idUsuario": 3,
        "nombre": "EXCLUSIVO CHAMULA",
        "creadoAl": "0000-00-00 00:00:00",
        "modificadoAl": "0000-00-00 00:00:00",
        "borradoAl": null
      }]</code> Nivel 3 zonal

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar.

	{
		"id": 3,
		"email": "ramirez.esquinca@gmail.com",
		"permissions": {},
		"nivel": 1,
		"avatar": "http://localhost/SSA_MATERIAL/CIUM/assets/img/user.png",
		"UsuarioZona": [],
		"grupos": [1]
	}

>**Nota**

> - <code>"UsuarioZona": []</code> Nivel 1 estatal
> - <code>"UsuarioZona": [{
    "id": "PICHUCALCO",
    "nombre": "PICHUCALCO"
  }]</code> Nivel 2 jurisdiccional
> - <code>"UsuarioZona": [{
        "id": 1,
        "idZona": 1,
        "idUsuario": 3,
        "nombre": "EXCLUSIVO CHAMULA",
        "creadoAl": "0000-00-00 00:00:00",
        "modificadoAl": "0000-00-00 00:00:00",
        "borradoAl": null
      }]</code> Nivel 3 zonal
	  
Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/SysUsuario*](http://187.217.219.55/cium/api/v1/SysUsuario)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 3,
			"username": "Admin",
			"email": "ramirez.esquinca@gmail.com",
			"permissions": [],
			"activated": true,
			"activated_at": null,
			"last_login": "2015-10-11 19:19:57",
			"nombres": "Eliecer",
			"apellidoPaterno": "ramirez",
			"apellidoMaterno": "esquinca",
			"cargo": "Admin",
			"nivel": 1,
			"telefono": "",
			"avatar": "http://localhost/SSA_MATERIAL/CIUM/assets/img/user.png",
			"creadoAl": "2015-03-13 17:50:38",
			"modificadoAl": "2015-10-11 19:19:57",
			"borradoAl": null
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>
### PUT/{ID} (UPDATE)


Actualizar el registro especificado en el la base de datos

REQUEST (SEND)

Recibe un Input Request con el json de los datos

	{
		"id": 3,
		"email": "ramirez.esquinca@gmail.com",
		"permissions": {},
		"nivel": 1,
		"avatar": "http://localhost/SSA_MATERIAL/CIUM/assets/img/user.png",
		"creadoAl": "2015-03-13 17:50:38",
		"modificadoAl": "2015-10-11 19:05:39",
		"borradoAl": null,
		"UsuarioZona": [],
		"grupos": [1]
	}

>**Nota**

> - <code>"UsuarioZona": []</code> Nivel 1 estatal
> - <code>"UsuarioZona": [{
    "id": "PICHUCALCO",
    "nombre": "PICHUCALCO"
  }]</code> Nivel 2 jurisdiccional
> - <code>"UsuarioZona": [{
        "id": 1,
        "idZona": 1,
        "idUsuario": 3,
        "nombre": "EXCLUSIVO CHAMULA",
        "creadoAl": "0000-00-00 00:00:00",
        "modificadoAl": "0000-00-00 00:00:00",
        "borradoAl": null
      }]</code> Nivel 3 zonal
	  
Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/SysUsuario/{ID}*](http://187.217.219.55/cium/api/v1/SysUsuario/%7bID%7d) 

>**Ejemplo actualizar el registro con id 3**

> - [*http://187.217.219.55/cium/api/v1/SysUsuario/3*](http://187.217.219.55/cium/api/v1/SysUsuario/3)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 3,
			"username": "Admin",
			"email": "ramirez.esquinca@gmail.com",
			"permissions": [],
			"activated": true,
			"activated_at": null,
			"last_login": "2015-10-11 19:19:57",
			"nombres": "Eliecer",
			"apellidoPaterno": "ramirez",
			"apellidoMaterno": "esquinca",
			"cargo": "Admin",
			"nivel": 1,
			"telefono": "",
			"avatar": "http://localhost/SSA_MATERIAL/CIUM/assets/img/user.png",
			"creadoAl": "2015-03-13 17:50:38",
			"modificadoAl": "2015-10-11 19:19:57",
			"borradoAl": null
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>

### DELETE/{ID} (DELETE)


Borra el registro especificado en el la base de datos


Petición

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/SysUsuario/{ID}*](http://187.217.219.55/cium/api/v1/SysUsuario/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/SysUsuario/1*](http://187.217.219.55/cium/api/v1/SysUsuario/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 3,
			"username": "Admin",
			"email": "ramirez.esquinca@gmail.com",
			"permissions": [],
			"activated": true,
			"activated_at": null,
			"last_login": "2015-10-11 19:19:57",
			"nombres": "Eliecer",
			"apellidoPaterno": "ramirez",
			"apellidoMaterno": "esquinca",
			"cargo": "Admin",
			"nivel": 1,
			"telefono": "",
			"avatar": "http://localhost/SSA_MATERIAL/CIUM/assets/img/user.png",
			"creadoAl": "2015-03-13 17:50:38",
			"modificadoAl": "2015-10-11 19:19:57",			
			"borradoAl": "2015-10-11 16:19:03"
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>