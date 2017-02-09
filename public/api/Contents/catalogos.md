# Catálogos


<p style="text-align: justify;">
En términos generales, un catálogo es la lista ordenada o clasificada que se hará sobre los datos que necesita el sistema para operar. Normalmente estos catálogos no cambian su contenido. Se crean una sola vez y después se utilizan muchas veces en operaciones y reportes.
<br>
Todos las peticiones se hacen via http con las cabeceras que se explicaron en el capitulo anterior. 
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
	

## Acciones


<p style="text-align: justify;">
Acciones contiene todos los datos a seleccionar cuando en una evaluación se encuentra un hallazgo. 
</p>


<HR>
### GET (INDEX)


Muestra una lista de las acciones según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Accion*](http://187.217.219.55/cium/api/v1/Accion)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Accion?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Accion?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Accion?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Accion?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
					{
						"id": 1,
						"nombre": "Se surte",
						"tipo": "R",
						"creadoAl": "2015-03-09 23:55:24",
						"modificadoAl": "2015-08-26 19:23:41",
						"borradoAl": null
					}
				] ,
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

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Accion/{ID}*](http://187.217.219.55/cium/api/v1/Accion/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Accion/1*](http://187.217.219.55/cium/api/v1/Accion/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Se surte",
			"tipo": "R",
			"creadoAl": "2015-03-09 23:55:24",
			"modificadoAl": "2015-08-26 19:23:41",
			"borradoAl": null
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
		"nombre": "ejemplo",
		"tipo": "R"
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/Accion*](http://187.217.219.55/cium/api/v1/Accion)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"nombre": "ejemplo",
			"tipo": "R",
			"modificadoAl": "2015-10-06 23:27:46",
			"creadoAl": "2015-10-06 23:27:46",
			"id": 8
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
		"nombre": "ejemplo UPDATE",
		"tipo": "S"
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/Accion/{ID}*](http://187.217.219.55/cium/api/v1/Accion/%7bID%7d) 

>**Ejemplo actualizar el registro con id 8**

> - [*http://187.217.219.55/cium/api/v1/Accion/8*](http://187.217.219.55/cium/api/v1/Accion/8)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 8,
			"nombre": "ejemplo UPDATE",
			"tipo": "S",
			"creadoAl": "2015-10-06 23:27:46",
			"modificadoAl": "2015-10-07 00:32:30",
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

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/Accion/{ID}*](http://187.217.219.55/cium/api/v1/Accion/%7bID%7d) 

>**Ejemplo eliminar el registro con id 8**

> - [*http://187.217.219.55/cium/api/v1/Accion/8*](http://187.217.219.55/cium/api/v1/Accion/8)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 8,
			"nombre": "ejemplo UPDATE",
			"tipo": "S",
			"creadoAl": "2015-10-06 23:27:46",
			"modificadoAl": "2015-10-07 00:32:30",
			"borradoAl": "2015-10-07 18:46:29"
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>

## Alertas


<p style="text-align: justify;">
Alerta contiene todos los datos a seleccionar para identifcar las alertas por el valor que tome los indicadores en las evaluaciones. 
</p>


<HR>
### GET (INDEX)


Muestra una lista de las alertas según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Alerta*](http://187.217.219.55/cium/api/v1/Alerta)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Alerta?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Alerta?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Alerta?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Alerta?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{		
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
					{
					  "id": 1,
					  "nombre": "Rojo",
					  "color": "hsla(0, 95%, 49%, 0.62)",
					  "creadoAl": "2015-10-11  00:00:00",
					  "modificadoAl": "2015-10-11  00:00:00",
					  "borradoAl": null
					}
				],
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

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Alerta/{ID}*](http://187.217.219.55/cium/api/v1/Alerta/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Alerta/1*](http://187.217.219.55/cium/api/v1/Alerta/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			 "id": 1,
			"nombre": "Rojo",
			"color": "hsla(0, 95%, 49%, 0.62)",
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
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar

	{
		"id": 1,
		"nombre": "Rojo",
		"color": "hsla(0, 95%, 49%, 0.62)"
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/Alerta*](http://187.217.219.55/cium/api/v1/Alerta)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"nombre": "ejemplo",
			"tipo": "R",
			"modificadoAl": "2015-10-06 23:27:46",
			"creadoAl": "2015-10-06 23:27:46",
			"id": 1
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
		"nombre": "Rojo",
		"color": "hsla(0, 95%, 49%, 0.62)"
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/Alerta/{ID}*](http://187.217.219.55/cium/api/v1/Alerta/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Alerta/1*](http://187.217.219.55/cium/api/v1/Alerta/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Rojo",
			"color": "hsla(0, 95%, 49%, 0.62)",
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

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/Alerta/{ID}*](http://187.217.219.55/cium/api/v1/Alerta/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Alerta/1*](http://187.217.219.55/cium/api/v1/Alerta/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Rojo",
			"color": "hsla(0, 95%, 49%, 0.62)",
			"creadoAl": "2015-10-11  00:00:00",
			"modificadoAl": "2015-10-11  00:00:00",
			"borradoAl": "2015-10-07 18:46:29"
		}
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>

## Clues


<p style="text-align: justify;">
Clues contiene un listado de todas las unidades medicas con su informacion de la ficha tecnica. Este catálogo esta relacionado con CONEs
</p>


<HR>
### GET (INDEX)


Muestra una lista de las clues según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Clues*](http://187.217.219.55/cium/api/v1/Clues)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Clues?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Clues?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Clues?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Clues?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
					{
						"clues": "CSSSA000226",
						"nombre": "COL. ADOLFO LÓPEZ MATEOS",
						"domicilio": "A UN COSTADO DE TIENDA DICONSA",
						"codigoPostal": 29700,
						"entidad": "CHIAPAS",
						"municipio": "AMATÁN",
						"localidad": "LOS AMATES",
						"jurisdiccion": "PICHUCALCO",
						"institucion": "SSA",
						"tipoUnidad": "CONSULTA EXTERNA",
						"estatus": "EN OPERACIÓN",
						"estado": "1",
						"tipologia": "CASA DE SALUD",
						"cone": "Ambulatorio con doctor",
						"cone_clues": {
							"clues": "CSSSA000226",
							"idCone": 1
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
> - <code>cone_clues</code> Relación de la clues con la tabla ConeClues 

<HR>	
### GET/ {ID} (SHOW)


Devuelve la información del registro especificado.

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Clues/{ID}*](http://187.217.219.55/cium/api/v1/Clues/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = CSSSA000226**

> - [*http://187.217.219.55/cium/api/v1/Clues/CSSSA000226*](http://187.217.219.55/cium/api/v1/Clues/CSSSA000226) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
					"clues": "CSSSA000226",
					"nombre": "COL. ADOLFO LÓPEZ MATEOS",
					"domicilio": "A UN COSTADO DE TIENDA DICONSA",
					"codigoPostal": 29700,
					"entidad": "CHIAPAS",
					"municipio": "AMATÁN",
					"localidad": "LOS AMATES",
					"jurisdiccion": "PICHUCALCO",
					"institucion": "SSA",
					"tipoUnidad": "CONSULTA EXTERNA",
					"estatus": "EN OPERACIÓN",
					"estado": "1",
					"tipologia": "CASA DE SALUD",
					"cone": {
						"clues": "CSSSA000226",
						"idCone": 1,
						"cone": {
							"id": 1,
							"nombre": "Ambulatorio con doctor",
							"creadoAl": "2015-10-11  00:00:00",
							"modificadoAl": "2015-10-11  00:00:00",
							"borradoAl": null
						}
					},
					"cone_clues": {
						"clues": "CSSSA000226",
						"idCone": 1
					}
				}
		}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>cone</code> Nivel de cone de la clues 
> - <code>cone_clues</code> Relación de la clues con la tabla ConeClues 

<HR>
### GET 

Este método regresa el listado de jurisdicciones.

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/jurisdiccion*](http://187.217.219.55/cium/api/v1/jurisdiccion)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"jurisdiccion": "PICHUCALCO",
				"entidad": "CHIAPAS"
			},
			{
				"jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
				"entidad": "CHIAPAS"
			},
			{
				"jurisdiccion": "OCOSINGO",
				"entidad": "CHIAPAS"
			},
			{
				"jurisdiccion": "PALENQUE",
				"entidad": "CHIAPAS"
			}
		],
		"total": 4
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>total</code> Total de registros devueltos

<HR>
### GET


Este método regresa el listado de clues pertenecientes a los permisos del usuario que hace la petición.


<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/CluesUsuario*](http://187.217.219.55/cium/api/v1/CluesUsuario) 


Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
			"zona": "EXCLUSIVO CHAMULA",
			"cone": "Básico",
			"clues": "CSSSA019481",
			"nombre": "H. B. C. DE SAN JUAN CHAMULA",
			"domicilio": "A UN COSTADO DE LA U.M.R.",
			"codigoPostal": 29320,
			"entidad": "CHIAPAS",
			"municipio": "CHAMULA",
			"localidad": "Chamula",
			"jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
			"institucion": "SSA",
			"tipoUnidad": "HOSPITAL",
			"estatus": "EN OPERACIÓN",
			"estado": "1",
			"tipologia": "HOSPITAL INTEGRAL (COMUNITARIO)"
			}
		],
		"total": 1
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>total</code> Total de registros devueltos

<hr>
<hr>


## Cone


<p style="text-align: justify;">
Cone (Cuidado obstétrico y neonatal esencial) este catálogo agrupa las unidades medicas, es de suma importancia ya que todo los criterios dependen del nivel de cone de cada unidad médica. 
</p>


<HR>
### GET (INDEX)


Muestra una lista de lo CONEs según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Cone*](http://187.217.219.55/cium/api/v1/Cone)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Cone?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Cone?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Cone?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Cone?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 1,
				"nombre": "Ambulatorio con doctor",
				"creadoAl": "2015-03-17 16:19:40",
				"modificadoAl": "2015-03-17 16:19:40",
				"borradoAl": null
			},
			{
				"id": 2,
				"nombre": "Ambulatorio sin doctor",
				"creadoAl": "2015-03-17 16:20:05",
				"modificadoAl": "2015-03-17 16:20:05",
				"borradoAl": null
			},
			{
				"id": 3,
				"nombre": "Básico",
				"creadoAl": "2015-03-17 16:20:20",
				"modificadoAl": "2015-03-17 16:20:20",
				"borradoAl": null
			},
			{
				"id": 4,
				"nombre": "Completo",
				"creadoAl": "2015-03-17 16:20:30",
				"modificadoAl": "2015-03-17 16:20:30",
				"borradoAl": null
			}
		],
		"total": 4
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

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Cone/{ID}*](http://187.217.219.55/cium/api/v1/Cone/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Cone/1*](http://187.217.219.55/cium/api/v1/Cone/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Ambulatorio con doctor",
			"creadoAl": "2015-10-11  00:00:00",
			"modificadoAl": "2015-10-11  00:00:00",
			"borradoAl": null,
			"ConeClues": [
				{
					"clues": "CSSSA019744",
					"nombre": "CENTRO DE SALUD MICROREGIONAL FILADELFIA",
					"jurisdiccion": "OCOSINGO",
					"municipio": "CHILÓN",
					"localidad": "PALESTINA"
				}
			]
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>ConeClues</code> Obejto con la lista de unidades medicas que pertenecen al nivel de CONE

<HR>
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar.

	{
		"id": 1,
		"nombre": "Ambulatorio con doctor",		
		"ConeClues": [
			{
				"clues": "CSSSA019744",
				"nombre": "CENTRO DE SALUD MICROREGIONAL FILADELFIA",
				"jurisdiccion": "OCOSINGO",
				"municipio": "CHILÓN",
				"localidad": "PALESTINA"
			}
		]
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/Cone*](http://187.217.219.55/cium/api/v1/Cone)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 1,
			"nombre": "Ambulatorio con doctor",
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
		"nombre": "Ambulatorio con doctor",
		"creadoAl": "2015-10-11  00:00:00",
		"modificadoAl": "2015-10-11  00:00:00",
		"borradoAl": null,
		"ConeClues": [
			{
				"clues": "CSSSA019744",
				"nombre": "CENTRO DE SALUD MICROREGIONAL FILADELFIA",
				"jurisdiccion": "OCOSINGO",
				"municipio": "CHILÓN",
				"localidad": "PALESTINA"
			}
		]
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/Cone/{ID}*](http://187.217.219.55/cium/api/v1/Cone/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Cone/1*](http://187.217.219.55/cium/api/v1/Cone/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 8,
			"nombre": "ejemplo UPDATE",
			"tipo": "S",
			"creadoAl": "2015-10-06 23:27:46",
			"modificadoAl": "2015-10-07 00:32:30",
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

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/Cone/{ID}*](http://187.217.219.55/cium/api/v1/Cone/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Cone/1*](http://187.217.219.55/cium/api/v1/Cone/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Ambulatorio con doctor",
			"creadoAl": "2015-10-11  00:00:00",
			"modificadoAl": "2015-10-11  00:00:00",
			"borradoAl": "2015-10-09 02:49:01"
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>


## Criterio


<p style="text-align: justify;">
Criterio este catálogo contiene todos los puntos a evaluar se relaciona con indicador, cone y lugar de verificación para obtener el listado correspondiente a cada de las unidades médicas, el lugar de verificación sirve para agrupar los criterios. 
</p>


<HR>
### GET (INDEX)


Muestra una lista de los criterios según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Criterio*](http://187.217.219.55/cium/api/v1/Criterio)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Criterio?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Criterio?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Criterio?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Criterio?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 1,
				"nombre": "Paquetes o sobres de sales de rehidratación oral  o sobres de vida",
				"creadoAl": null,
				"modificadoAl": null,
				"borradoAl": null,
				"indicadores": [
					{
						"id": 1,
						"codigo": "7010",
						"nombre": "Atención del niño",
						"color": "hsla(197, 84%, 67%, 0.62)",
						"categoria": "RECURSO",
						"creadoAl": "2015-10-11  00:00:00",
						"modificadoAl": "2015-10-11  00:00:00",
						"borradoAl": null,
						"cones": [
							{
								"id": 1,
								"idCone": 1,
								"idIndicadorCriterio": 1,
								"creadoAl": "0000-00-00 00:00:00",
								"modificadoAl": "2015-10-09 02:49:01",
								"borradoAl": null,
								"nombre": "Ambulatorio con doctor"
							},
							{
								"id": 2,
								"idCone": 2,
								"idIndicadorCriterio": 1,
								"creadoAl": "0000-00-00 00:00:00",
								"modificadoAl": "0000-00-00 00:00:00",
								"borradoAl": null,
								"nombre": "Ambulatorio sin doctor"
							},
							{
								"id": 3,
								"idCone": 3,
								"idIndicadorCriterio": 1,
								"creadoAl": "0000-00-00 00:00:00",
								"modificadoAl": "0000-00-00 00:00:00",
								"borradoAl": null,
								"nombre": "Básico"
							},
							{
								"id": 4,
								"idCone": 4,
								"idIndicadorCriterio": 1,
								"creadoAl": "0000-00-00 00:00:00",
								"modificadoAl": "0000-00-00 00:00:00",
								"borradoAl": null,
								"nombre": "Completo"
							}
						],
						"lugarVerificacion": {
							"id": 1,
							"nombre": "Farmacia",
							"creadoAl": "2015-10-11  00:00:00",
							"modificadoAl": "2015-10-11  00:00:00",
							"borradoAl": null
						},
						"pivot": {
							"idCriterio": 1,
							"idIndicador": 1,
							"id": 1,
							"idLugarVerificacion": 1
						}
					}
				]
			}
		],
		"total": 1
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>indicadores</code> Contiene los indicadores, lugarVerificacion y sus niveles de cone 
> - <code>cone</code> Niveles de cones
> - <code>pivot</code> Resumen de los ids que se relacionan
> - <code>total</code> Total de registros devueltos

<HR>	
### GET/ {ID} (SHOW)


Devuelve la información del registro especificado.

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Criterio/{ID}*](http://187.217.219.55/cium/api/v1/Criterio/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Criterio/1*](http://187.217.219.55/cium/api/v1/Criterio/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Paquetes o sobres de sales de rehidratación oral  o sobres de vida",
			"creadoAl": null,
			"modificadoAl": null,
			"borradoAl": null,
			"indicadores": [
				{
					"id": 1,
					"codigo": "7010",
					"nombre": "Atención del niño",
					"color": "hsla(197, 84%, 67%, 0.62)",
					"categoria": "RECURSO",
					"creadoAl": "2015-10-11  00:00:00",
					"modificadoAl": "2015-10-11  00:00:00",
					"borradoAl": null,
					"cones": [
						{
							"id": 1,
							"idCone": 1,
							"idIndicadorCriterio": 1,
							"creadoAl": "0000-00-00 00:00:00",
							"modificadoAl": "2015-10-09 02:49:01",
							"borradoAl": null,
							"nombre": "Ambulatorio con doctor"
						},
						{
							"id": 2,
							"idCone": 2,
							"idIndicadorCriterio": 1,
							"creadoAl": "0000-00-00 00:00:00",
							"modificadoAl": "0000-00-00 00:00:00",
							"borradoAl": null,
							"nombre": "Ambulatorio sin doctor"
						},
						{
							"id": 3,
							"idCone": 3,
							"idIndicadorCriterio": 1,
							"creadoAl": "0000-00-00 00:00:00",
							"modificadoAl": "0000-00-00 00:00:00",
							"borradoAl": null,
							"nombre": "Básico"
						},
						{
							"id": 4,
							"idCone": 4,
							"idIndicadorCriterio": 1,
							"creadoAl": "0000-00-00 00:00:00",
							"modificadoAl": "0000-00-00 00:00:00",
							"borradoAl": null,
							"nombre": "Completo"
						}
					],
					"lugarVerificacion": {
						"id": 1,
						"nombre": "Farmacia",
						"creadoAl": "2015-10-11  00:00:00",
						"modificadoAl": "2015-10-11  00:00:00",
						"borradoAl": null
					},
					"pivot": {
						"idCriterio": 1,
						"idIndicador": 1,
						"id": 1,
						"idLugarVerificacion": 1
					}
				}
			]
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>indicadores</code> Contiene los indicadores, lugarVerificacion y sus niveles de cone 
> - <code>cone</code> Niveles de cones
> - <code>pivot</code> Resumen de los ids que se relacionan

<HR>
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar.

	{
		"nombre": "Paquetes o sobres de sales de rehidratación oral  o sobres de vida",
		"indicadores": [
			{
				"id": "1",
				"idLugarVerificacion": 1,
				"cones": [
					{
						"id": "1"
					},
					{
						"id": "2"
					},
					{
						"id": "3"
					},
					{
						"id": "4"
					}
				]
			}
		]
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/Criterio*](http://187.217.219.55/cium/api/v1/Criterio)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 1,
			"nombre": "Paquetes o sobres de sales de rehidratación oral  o sobres de vida",
			"creadoAl": 2015-10-06,
			"modificadoAl": 2015-10-06,
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
		"nombre": "Paquetes o sobres de sales de rehidratación oral  o sobres de vida",
		"indicadores": [
			{
				"id": "1",
				"idLugarVerificacion": 1,
				"cones": [
					{
						"id": "1"
					},
					{
						"id": "2"
					},
					{
						"id": "3"
					},
					{
						"id": "4"
					}
				]
			}
		]
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/Criterio/{ID}*](http://187.217.219.55/cium/api/v1/Criterio/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Criterio/1*](http://187.217.219.55/cium/api/v1/Criterio/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Paquetes o sobres de sales de rehidratación oral  o sobres de vida",
			"creadoAl": 2015-10-06,
			"modificadoAl": 2015-10-06,
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

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/Criterio/{ID}*](http://187.217.219.55/cium/api/v1/Criterio/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Criterio/1*](http://187.217.219.55/cium/api/v1/Criterio/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Paquetes o sobres de sales de rehidratación oral  o sobres de vida",
			"creadoAl": null,
			"modificadoAl": null,
			"borradoAl": "2015-10-11 16:04:25"
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>


## Indicador


<p style="text-align: justify;">
Indicador este catálogo contiene todos los indicadores para generar las evaluaciones. Se relaciona con alertaIndicador para generar los colores segun el porcentaje obtenido.
</p>


<HR>
### GET (INDEX)


Muestra una lista de los indicadores según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Indicador*](http://187.217.219.55/cium/api/v1/Indicador)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Indicador?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Indicador?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Indicador?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Indicador?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 1,
				"codigo": "7010",
				"nombre": "Atención del niño",
				"color": "hsla(197, 84%, 67%, 0.62)",
				"categoria": "RECURSO",
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null,
				"indicador_alertas": [
					{
						"id": 9,
						"minimo": "0",
						"maximo": "50",
						"idAlerta": 1,
						"idIndicador": 1,
						"creadoAl": "2015-10-11  00:00:00",
						"modificadoAl": "2015-10-11  00:00:00",
						"borradoAl": null
					},
					{
						"id": 10,
						"minimo": "51",
						"maximo": "70",
						"idAlerta": 3,
						"idIndicador": 1,
						"creadoAl": "2015-10-11  00:00:00",
						"modificadoAl": "2015-10-11  00:00:00",
						"borradoAl": null
					},
					{
						"id": 11,
						"minimo": "71",
						"maximo": "100",
						"idAlerta": 2,
						"idIndicador": 1,
						"creadoAl": "2015-10-11  00:00:00",
						"modificadoAl": "2015-10-11  00:00:00",
						"borradoAl": null
					}
				]
			}
		},
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

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Indicador/{ID}*](http://187.217.219.55/cium/api/v1/Indicador/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Indicador/1*](http://187.217.219.55/cium/api/v1/Indicador/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"codigo": "7010",
			"nombre": "Atención del niño",
			"color": "hsla(197, 84%, 67%, 0.62)",
			"categoria": "RECURSO",
			"creadoAl": "2015-10-11  00:00:00",
			"modificadoAl": "2015-10-11  00:00:00",
			"borradoAl": null,
			"indicador_alertas": [
				{
					"id": 9,
					"minimo": "0",
					"maximo": "50",
					"idAlerta": 1,
					"idIndicador": 1,
					"creadoAl": "2015-10-11  00:00:00",
					"modificadoAl": "2015-10-11  00:00:00",
					"borradoAl": null
				},
				{
					"id": 10,
					"minimo": "51",
					"maximo": "70",
					"idAlerta": 3,
					"idIndicador": 1,
					"creadoAl": "2015-10-11  00:00:00",
					"modificadoAl": "2015-10-11  00:00:00",
					"borradoAl": null
				},
				{
					"id": 11,
					"minimo": "71",
					"maximo": "100",
					"idAlerta": 2,
					"idIndicador": 1,
					"creadoAl": "2015-10-11  00:00:00",
					"modificadoAl": "2015-10-11  00:00:00",
					"borradoAl": null
				}
			]
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
		"codigo": "7010",
		"nombre": "Atención del niño",
		"color": "hsla(197, 84%, 67%, 0.62)",
		"categoria": "RECURSO",
		"indicador_alertas": [
			{
				"id": 9,
				"minimo": "0",
				"maximo": "50",
				"idAlerta": "1",
				"idIndicador": 1,
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null
			},
			{
				"id": 10,
				"minimo": "51",
				"maximo": "70",
				"idAlerta": "3",
				"idIndicador": 1,
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null
			},
			{
				"id": 11,
				"minimo": "71",
				"maximo": "100",
				"idAlerta": "2",
				"idIndicador": 1,
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null
			}
		]
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/Indicador*](http://187.217.219.55/cium/api/v1/Indicador)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 1,
			"codigo": "7010",
			"nombre": "Atención del niño",
			"color": "hsla(197, 84%, 67%, 0.62)",
			"categoria": "RECURSO",
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
		"codigo": "7010",
		"nombre": "Atención del niño",
		"color": "hsla(197, 84%, 67%, 0.62)",
		"categoria": "RECURSO",
		"creadoAl": "2015-10-11  00:00:00",
		"modificadoAl": "2015-10-11  00:00:00",
		"borradoAl": null,
		"indicador_alertas": [
			{
				"id": 9,
				"minimo": "0",
				"maximo": "50",
				"idAlerta": "1",
				"idIndicador": 1,
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null
			},
			{
				"id": 10,
				"minimo": "51",
				"maximo": "70",
				"idAlerta": "3",
				"idIndicador": 1,
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null
			},
			{
				"id": 11,
				"minimo": "71",
				"maximo": "100",
				"idAlerta": "2",
				"idIndicador": 1,
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null
			}
		]
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/Indicador/{ID}*](http://187.217.219.55/cium/api/v1/Indicador/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Indicador/1*](http://187.217.219.55/cium/api/v1/Indicador/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"codigo": "7010",
			"nombre": "Atención del niño",
			"color": "hsla(197, 84%, 67%, 0.62)",
			"categoria": "RECURSO",
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

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/Indicador/{ID}*](http://187.217.219.55/cium/api/v1/Indicador/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Indicador/1*](http://187.217.219.55/cium/api/v1/Indicador/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"codigo": "7010",
			"nombre": "Atención del niño",
			"color": "hsla(197, 84%, 67%, 0.62)",
			"categoria": "RECURSO",
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



## Lugar de Verificación


<p style="text-align: justify;">
LugarVerificacion este catálogo contiene los lugares de verificacion para agrupar los criterios de cada indicador para las unidades médicas.
</p>


<HR>
### GET (INDEX)


Muestra una lista de los luagares de verificación según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/LugarVerificacion*](http://187.217.219.55/cium/api/v1/LugarVerificacion)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/LugarVerificacion?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/LugarVerificacion?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/LugarVerificacion?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/LugarVerificacion?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 1,
				"nombre": "Farmacia",
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null
			}
		],
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

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/LugarVerificacion/{ID}*](http://187.217.219.55/cium/api/v1/LugarVerificacion/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/LugarVerificacion/1*](http://187.217.219.55/cium/api/v1/LugarVerificacion/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Farmacia",
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
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar.

	{
		"id": 1,
		"nombre": "Farmacia"
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/LugarVerificacion*](http://187.217.219.55/cium/api/v1/LugarVerificacion)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 1,
			"nombre": "Farmacia",
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
		"nombre": "Farmacia",
		"creadoAl": "2015-10-11  00:00:00",
		"modificadoAl": "2015-10-11  00:00:00",
		"borradoAl": null
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/LugarVerificacion/{ID}*](http://187.217.219.55/cium/api/v1/LugarVerificacion/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/LugarVerificacion/1*](http://187.217.219.55/cium/api/v1/LugarVerificacion/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Farmacia",
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

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/LugarVerificacion/{ID}*](http://187.217.219.55/cium/api/v1/LugarVerificacion/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/LugarVerificacion/1*](http://187.217.219.55/cium/api/v1/LugarVerificacion/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "Farmacia",
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


## Plazo acción


<p style="text-align: justify;">
PlazoAccion este catálogo contiene los lugares de verificacion para agrupar los criterios de cada indicador para las unidades médicas.
</p>


<HR>
### GET (INDEX)


Muestra una lista de los plazo por acción según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/PlazoAccion*](http://187.217.219.55/cium/api/v1/PlazoAccion)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/PlazoAccion?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/PlazoAccion?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/PlazoAccion?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/PlazoAccion?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 1,
				"nombre": "4 Días",
				"tipo": "d",
				"valor": "4",
				"creadoAl": "2015-10-11  00:00:00",
				"modificadoAl": "2015-10-11  00:00:00",
				"borradoAl": null
			}
		],
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

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/PlazoAccion/{ID}*](http://187.217.219.55/cium/api/v1/PlazoAccion/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/PlazoAccion/1*](http://187.217.219.55/cium/api/v1/PlazoAccion/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "4 Días",
			"tipo": "d",
			"valor": "4",
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
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar.

	{
		"id": 1,
		"nombre": "4 Días",
		"tipo": "d",
		"valor": "4"
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/PlazoAccion*](http://187.217.219.55/cium/api/v1/PlazoAccion)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 1,
			"nombre": "4 Días",
			"tipo": "d",
			"valor": "4",
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
		"nombre": "4 Días",
		"tipo": "d",
		"valor": "4",
		"creadoAl": "2015-10-11  00:00:00",
		"modificadoAl": "2015-10-11  00:00:00",
		"borradoAl": null
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/PlazoAccion/{ID}*](http://187.217.219.55/cium/api/v1/PlazoAccion/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/PlazoAccion/1*](http://187.217.219.55/cium/api/v1/PlazoAccion/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "4 Días",
			"tipo": "d",
			"valor": "4",
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

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/PlazoAccion/{ID}*](http://187.217.219.55/cium/api/v1/PlazoAccion/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/PlazoAccion/1*](http://187.217.219.55/cium/api/v1/PlazoAccion/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "4 Días",
			"tipo": "d",
			"valor": "4",
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


## Zona


<p style="text-align: justify;">
Zona este catálogo contiene las zonas y las unidades médicas que la conforman, Debido a que cada zona puede tener muchas unidades médiacas y para no hacer tardio la carga del listado se recomienda descargar el listado y depues hacer un recorrido de cada uno de los resultados para extraer los datos del metodo show.
</p>


<HR>
### GET (INDEX)


Muestra una lista de las zonas según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Zona*](http://187.217.219.55/cium/api/v1/Zona)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Zona?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Zona?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Zona?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Zona?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 1,
				"nombre": "EXCLUSIVO CHAMULA",
				"creadoAl": "-0001-11-30 00:00:00",
				"modificadoAl": "-0001-11-30 00:00:00",
				"borradoAl": null
			}
		],
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

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Zona/{ID}*](http://187.217.219.55/cium/api/v1/Zona/%7bID%7d) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Zona/1*](http://187.217.219.55/cium/api/v1/Zona/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "EXCLUSIVO CHAMULA",
			"creadoAl": "-0001-11-30 00:00:00",
			"modificadoAl": "-0001-11-30 00:00:00",
			"borradoAl": null,
			"ZonaClues": [
				{
					"id": 1,
					"idZona": 1,
					"clues": "CSSSA019481",
					"jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
					"nombre": "H. B. C. DE SAN JUAN CHAMULA",
					"domicilio": "A UN COSTADO DE LA U.M.R.",
					"codigoPostal": 29320,
					"entidad": "CHIAPAS",
					"municipio": "CHAMULA",
					"localidad": "ACACOYAGUA",
					"institucion": "SSA",
					"tipoUnidad": "HOSPITAL",
					"estatus": "EN OPERACIÓN",
					"estado": "1",
					"tipologia": "HOSPITAL INTEGRAL (COMUNITARIO)"
				}
			]
		}
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>ZonaClues</code> Objeto con todas las unidades médicas

<HR>
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

Recibe un input request tipo json de los datos a almacenar.

	{
		"id": 1,
		"nombre": "EXCLUSIVO CHAMULA",
		"ZonaClues": [
			{
				"id": 1,
				"idZona": 1,
				"clues": "CSSSA019481",
				"jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
				"nombre": "H. B. C. DE SAN JUAN CHAMULA",
				"domicilio": "A UN COSTADO DE LA U.M.R.",
				"codigoPostal": 29320,
				"entidad": "CHIAPAS",
				"municipio": "CHAMULA",
				"localidad": "ACACOYAGUA",
				"institucion": "SSA",
				"tipoUnidad": "HOSPITAL",
				"estatus": "EN OPERACIÓN",
				"estado": "1",
				"tipologia": "HOSPITAL INTEGRAL (COMUNITARIO)"
			}
		]
	}

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/Zona*](http://187.217.219.55/cium/api/v1/Zona)

Respuesta

	{
		"status": 201,
		"messages": "Creado",
		"data": {
			"id": 1,
			"nombre": "EXCLUSIVO CHAMULA",
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
		"nombre": "EXCLUSIVO CHAMULA",
		"creadoAl": "2015-10-11  00:00:00",
		"modificadoAl": "2015-10-11  00:00:00",
		"borradoAl": null,
		"ZonaClues": [
			{
				"id": 1,
				"idZona": 1,
				"clues": "CSSSA019481",
				"jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
				"nombre": "H. B. C. DE SAN JUAN CHAMULA",
				"domicilio": "A UN COSTADO DE LA U.M.R.",
				"codigoPostal": 29320,
				"entidad": "CHIAPAS",
				"municipio": "CHAMULA",
				"localidad": "ACACOYAGUA",
				"institucion": "SSA",
				"tipoUnidad": "HOSPITAL",
				"estatus": "EN OPERACIÓN",
				"estado": "1",
				"tipologia": "HOSPITAL INTEGRAL (COMUNITARIO)"
			}
		]
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/Zona/{ID}*](http://187.217.219.55/cium/api/v1/Zona/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Zona/1*](http://187.217.219.55/cium/api/v1/Zona/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "EXCLUSIVO CHAMULA",
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

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/Zona/{ID}*](http://187.217.219.55/cium/api/v1/Zona/%7bID%7d) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/Zona/1*](http://187.217.219.55/cium/api/v1/Zona/1)

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"id": 1,
			"nombre": "EXCLUSIVO CHAMULA",
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