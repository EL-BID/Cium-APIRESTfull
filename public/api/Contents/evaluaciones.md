# Evaluaciones


<p style="text-align: justify;">
En este apartado se encuentra las parte transaccional de la aplicación es decir la parte donde se generan y procesan los datos recabados de las evaluaciones.
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
	


## Recursos


<p style="text-align: justify;">
Recursos es la evaluación que en las unidades medicas se encarga de monitoriar los indicadores de medición del tipo abasto y equipos, asi como los recursos humanos.
</p>


<HR>
### GET (INDEX)


Muestra una lista de las evaluaciones de recursos según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": [
			{
				"id": 2,
				"idUsuario": "3",
				"clues": "CSSSA019481",
				"fechaEvaluacion": "2015-10-11 02:20:47",
				"cerrado": null,
				"firma": "",
				"responsable": "",
				"creadoAl": "2015-10-11 02:20:47",
				"modificadoAl": "2015-10-11 02:20:47",
				"borradoAl": null,
				"cone": {
					"clues": "CSSSA019481",
					"idCone": 3
				},
				"usuarios": {
					"id": 3,
					"username": "Admin",
					"email": "ramirez.esquinca@gmail.com",
					"permissions": [],
					"activated": true,
					"activated_at": null,
					"last_login": "2015-10-11 19:28:45",
					"nombres": "Eliecer",
					"apellidoPaterno": "ramirez",
					"apellidoMaterno": "esquinca",
					"cargo": "Admin",
					"nivel": 1,
					"telefono": "",
					"avatar": "http://localhost/SSA_MATERIAL/CIUM/assets/img/user.png",
					"creadoAl": "2015-03-13 17:50:38",
					"modificadoAl": "2015-10-11 19:28:45",
					"borradoAl": null
				},
				"cluess": {
					"clues": "CSSSA019481",
					"nombre": "H. B. C. DE SAN JUAN CHAMULA",
					"domicilio": "A UN COSTADO DE LA U.M.R.",
					"codigoPostal": 29320,
					"entidad": "CHIAPAS",
					"municipio": "CHAMULA",
					"localidad": "ACACOYAGUA",
					"jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
					"institucion": "SSA",
					"tipoUnidad": "HOSPITAL",
					"estatus": "EN OPERACIÓN",
					"estado": "1",
					"tipologia": "HOSPITAL INTEGRAL (COMUNITARIO)"
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

<HR>	
### GET/ {ID} (SHOW)


Devuelve la información del registro especificado. para este metodo hay que llamar tambien al metodo de criterios

Petición

Obtener la ficha técnica

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso/{ID}*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso/%7bID%7d) 

obtener el detalle para la vista ver

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/{ID}*](http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/%7bID%7d) 

obtener el detalle para la vista update

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/CriterioEvaluacionRecurso/{cone}/{indicador}/{evaluacion*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso/3/2/1) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso/1*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso/1) 

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

>**Ejemplo Obtener los detalles en ver id = 1**
> - [*http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/1*](http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/1) 

Respuesta

	{
		"status": 200,
		"messages": "Operación realizada con exito",
		"data": {
			"7050": {
				"0": {
					"idCriterio": 31,
					"idIndicador": 5,
					"idCone": 2,
					"idlugarVerificacion": 10,
					"creadoAl": null,
					"modificadoAl": null,
					"criterio": "Condón masculino",
					"lugarVerificacion": "Métodos de planificación familiar",
					"aprobado": 1
				},
				"1": {
					"idCriterio": 32,
					"idIndicador": 5,
					"idCone": 2,
					"idlugarVerificacion": 10,
					"creadoAl": null,
					"modificadoAl": null,
					"criterio": "Cualquier tipo de hormonal oral",
					"lugarVerificacion": "Métodos de planificación familiar",
					"aprobado": 1
				},
				"2": {
					"idCriterio": 33,
					"idIndicador": 5,
					"idCone": 2,
					"idlugarVerificacion": 10,
					"creadoAl": null,
					"modificadoAl": null,
					"criterio": "Cualquier tipo de hormonal inyectable",
					"lugarVerificacion": "Métodos de planificación familiar",
					"aprobado": 1
				},
				"3": {
					"idCriterio": 53,
					"idIndicador": 5,
					"idCone": 2,
					"idlugarVerificacion": 14,
					"creadoAl": null,
					"modificadoAl": null,
					"criterio": "Set para inserción de DIU (espéculo vaginal, pinza de pozzio o tentáculo, pinza Forester o de anillos)",
					"lugarVerificacion": "Equipo",
					"aprobado": 1
				},
				"4": {
					"idCriterio": 80,
					"idIndicador": 5,
					"idCone": 2,
					"idlugarVerificacion": 10,
					"creadoAl": null,
					"modificadoAl": null,
					"criterio": "Dispositivo intrauterino",
					"lugarVerificacion": "Métodos de planificación familiar",
					"aprobado": 0
				},
				"indicador": {
					"id": 5,
					"color": "hsla(226, 40%, 49%, 0.62)",
					"codigo": "7050",
					"nombre": "Planificación familiar"
				},
				"hallazgo": {
					"idIndicador": 5,
					"idAccion": 1,
					"idPlazoAccion": null,
					"resuelto": 0,
					"descripcion": "no hay",
					"tipo": "R",
					"accion": "Se surte"
					}
			}
		},
		"estadistica": {
			"7050": {
				"id": 5,
				"color": "hsla(226, 40%, 49%, 0.62)",
				"codigo": "7050",
				"nombre": "Planificación familiar",
				"indicadores":{
					"totalCriterios": 5,
					"totalAprobados": 4,
					"totalPorciento": "80.00",
					"totalColor": "hsla(130, 90%, 38%, 0.62)"
				}
			}
		}
	}

>**Ejemplo Obtener los detalles en upadate id = 1**
> - [*http://187.217.219.55/cium/api/v1/CriterioEvaluacionRecurso/3/2/1*](http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/3/2/1) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"0": {
		  "idCriterio": 8,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Estetoscopio (PROPIEDAD DE LA UNIDAD)",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"1": {
		  "idCriterio": 16,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 5,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Multivitamínico o Hierro + Ácido fólico ",
		  "lugarVerificacion": "Farmacia: (EN CASO DE CARAVANAS SE MEDIRÁ EN SU SEDE)"
		},
		"2": {
		  "idCriterio": 17,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 5,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Vacuna antitetánica o toxoide tetánico diftérico (SOLAMENTE UNIDADES QUE TIENEN REFRIGERADORA FUNCIONANDO Y GUARDAN VACUNAS POR MAS DE 7 DIAS )",
		  "lugarVerificacion": "Farmacia: (EN CASO DE CARAVANAS SE MEDIRÁ EN SU SEDE)"
		},
		"3": {
		  "idCriterio": 18,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 5,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Paletas (Espátulas)  de Ayre (para examen de citología cervical) o hisopos",
		  "lugarVerificacion": "Farmacia: (EN CASO DE CARAVANAS SE MEDIRÁ EN SU SEDE)"
		},
		"4": {
		  "idCriterio": 19,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 5,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Láminas porta objetos",
		  "lugarVerificacion": "Farmacia: (EN CASO DE CARAVANAS SE MEDIRÁ EN SU SEDE)"
		},
		"5": {
		  "idCriterio": 20,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 5,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Nitrofurantoina tabletas o suspensión",
		  "lugarVerificacion": "Farmacia: (EN CASO DE CARAVANAS SE MEDIRÁ EN SU SEDE)"
		},
		"6": {
		  "idCriterio": 21,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 5,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Eritromicina, Amoxicilina o Penicilina Benzatinica",
		  "lugarVerificacion": "Farmacia: (EN CASO DE CARAVANAS SE MEDIRÁ EN SU SEDE)"
		},
		"7": {
		  "idCriterio": 22,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Báscula o balanza de pie (Báscula de piso)",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"8": {
		  "idCriterio": 23,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": " Carnet perinatal o tarjeta",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"9": {
		  "idCriterio": 24,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Estadímetro",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"10": {
		  "idCriterio": 26,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Cinta obstétrica del CLAP o cinta métrica",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"11": {
		  "idCriterio": 27,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": "0000-00-00 00:00:00",
		  "criterio": "Lámpara de chicote o lámpara de mano",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"12": {
		  "idCriterio": 28,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Esfigmomanómetro (Baumanómetro)",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"13": {
		  "idCriterio": 30,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Historia clínica materno perinatal",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"14": {
		  "idCriterio": 34,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Mesa para exploración ginecológica ",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"15": {
		  "idCriterio": 41,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 1,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Cefalexina ",
		  "lugarVerificacion": "Farmacia"
		},
		"16": {
		  "idCriterio": 42,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Kit para pruebas rápidas de sífilis o Microscopio de campos oscuros, o Equipo para inmunoensayos de enzimas",
		  "lugarVerificacion": "Laboratorio"
		},
		"17": {
		  "idCriterio": 43,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Kit para pruebas rápidas de VIH/SIDA o Microscopio Fluorescente ",
		  "lugarVerificacion": "Laboratorio"
		},
		"18": {
		  "idCriterio": 44,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Tiras reactivas para proteina en orina o Equipo para uroanálisis",
		  "lugarVerificacion": "Laboratorio"
		},
		"19": {
		  "idCriterio": 45,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Tiras reactivas para glucosa en sangre o Glucómentro",
		  "lugarVerificacion": "Laboratorio"
		},
		"20": {
		  "idCriterio": 46,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Hemocue o Contador automático de células",
		  "lugarVerificacion": "Laboratorio"
		},
		"21": {
		  "idCriterio": 47,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Microcubetas ",
		  "lugarVerificacion": "Laboratorio"
		},
		"22": {
		  "idCriterio": 48,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Kit para pruebas de embarazo",
		  "lugarVerificacion": "Laboratorio"
		},
		"23": {
		  "idCriterio": 49,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 12,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Reactivo para sífilis",
		  "lugarVerificacion": "Reactivos: Si se observa equipo para inmunoensayos de enzimas"
		},
		"24": {
		  "idCriterio": 50,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 12,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Reactivo para VIH/SIDA",
		  "lugarVerificacion": "Reactivos: Si se observa equipo para inmunoensayos de enzimas"
		},
		"25": {
		  "idCriterio": 51,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 11,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Anticuerpo de tipo de sangre",
		  "lugarVerificacion": "Reactivos: En cualquier caso buscar"
		},
		"26": {
		  "idCriterio": 52,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 11,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Anticuerpo de factor RH",
		  "lugarVerificacion": "Reactivos: En cualquier caso buscar"
		},
		"27": {
		  "idCriterio": 53,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 6,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Set para inserción de DIU (espéculo vaginal, pinza de pozzio o tentáculo, pinza Forester o de anillos)",
		  "lugarVerificacion": "Sitio de atención pre y post natal"
		},
		"28": {
		  "idCriterio": 82,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 1,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Nitrofurantoina ",
		  "lugarVerificacion": "Farmacia"
		},
		"29": {
		  "idCriterio": 83,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Microscopio óptico",
		  "lugarVerificacion": "Laboratorio"
		},
		"30": {
		  "idCriterio": 84,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Equipo para uroanálisis",
		  "lugarVerificacion": "Laboratorio"
		},
		"31": {
		  "idCriterio": 85,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Glucómetro",
		  "lugarVerificacion": "Laboratorio"
		},
		"32": {
		  "idCriterio": 86,
		  "idIndicador": 2,
		  "idCone": 3,
		  "idlugarVerificacion": 9,
		  "creadoAl": null,
		  "modificadoAl": null,
		  "criterio": "Contador automático de células ",
		  "lugarVerificacion": "Laboratorio"
		},
		"noAplica": [],
		"aprobado": [
		  41,
		  82,
		  8,
		  16,
		  17,
		  18,
		  19,
		  20,
		  21,
		  22,
		  23,
		  24,
		  26,
		  27,
		  28,
		  30,
		  34,
		  42,
		  43,
		  44,
		  45,
		  46,
		  47,
		  48,
		  49,
		  50,
		  51,
		  52,
		  83,
		  84,
		  85,
		  86
		],
		"noAprobado": [
		  53
		]
	  },
	  "total": 36,
	  "hallazgo": {
		"idIndicador": 2,
		"idAccion": 1,
		"idPlazoAccion": null,
		"resuelto": 0,
		"descripcion": "no hay diu",
		"tipo": "R"
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

<p style="text-align: justify;">
Recibe un input request tipo json de los datos a almacenar. Se quita la opcion NA solo es 1 o 0 para la evaluacion. Se genera un hallazgo si hay un no en cualquier criterio del indicador el hallazgo es uno por todos los criterios no cumplidos en un indicador.
Si la evaluacion ya se va a terminar se pone cerrado = 1
</p>

	{
	  "evaluaciones": [
		{
		  "id": 2,
		  "clues": "CSSSA019481",
		  "fechaEvaluacion": "2015-10-11 02:20:47",
		  "cerrado": null, 
		  "criterios": [
			{
			  "idCriterio": "8",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "16",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "17",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "18",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "19",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "20",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "21",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "22",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "23",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "24",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "26",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "27",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "28",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "30",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "34",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "41",
			  "idIndicador": "2",
			  "aprobado": 1
			},
			{
			  "idCriterio": "42",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "43",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "44",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "45",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "46",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "47",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "48",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "49",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "50",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "51",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "52",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "53",
			  "idIndicador": "2",
			  "aprobado": "0"
			},
			{
			  "idCriterio": "82",
			  "idIndicador": "2",
			  "aprobado": 1
			},
			{
			  "idCriterio": "83",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "84",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "85",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "86",
			  "idIndicador": "2",
			  "aprobado": "1"
			}
		  ],
		  "hallazgos": [
			{
			  "descripcion": "no hay diu",
			  "idAccion": 1,
			  "idIndicador": "2"
			}
		  ]
		}
	  ]
	}
	

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso)

Respuesta

	{
	  "status": 201,
	  "messages": "Creado",
	  "data": {
		"id": 1,
		"idUsuario": 3,
		"clues": "CSSSA019481",
		"fechaEvaluacion": "2015-10-11 02:20:47",
		"cerrado": null,
		"firma": "",
		"responsable": "",
		"creadoAl": "2015-10-11 02:20:47",
		"modificadoAl": "2015-10-11 02:20:47",
		"borradoAl": null
	  }
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>
### PUT/{ID} (UPDATE)

<p style="text-align: justify;">
Actualizar el registro especificado en el la base de datos
Si la evaluacion ya se va a terminar se pone cerrado = 1
</p>

REQUEST (SEND)

Recibe un Input Request con el json de los datos

	{
	  "evaluaciones": [
		{
		  "id": 2,
		  "clues": "CSSSA019481",
		  "fechaEvaluacion": "2015-10-11 02:20:47",
		  "cerrado": null, 
		  "criterios": [
			{
			  "idCriterio": "8",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "16",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "17",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "18",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "19",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "20",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "21",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "22",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "23",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "24",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "26",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "27",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "28",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "30",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "34",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "41",
			  "idIndicador": "2",
			  "aprobado": 1
			},
			{
			  "idCriterio": "42",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "43",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "44",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "45",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "46",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "47",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "48",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "49",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "50",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "51",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "52",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "53",
			  "idIndicador": "2",
			  "aprobado": "0"
			},
			{
			  "idCriterio": "82",
			  "idIndicador": "2",
			  "aprobado": 1
			},
			{
			  "idCriterio": "83",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "84",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "85",
			  "idIndicador": "2",
			  "aprobado": "1"
			},
			{
			  "idCriterio": "86",
			  "idIndicador": "2",
			  "aprobado": "1"
			}
		  ],
		  "hallazgos": [
			{
			  "descripcion": "no hay diu",
			  "idAccion": 1,
			  "idIndicador": "2"
			}
		  ]
		}
	  ]
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso/{ID}*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso/1*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso/1)

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"id": 1,
		"idUsuario": 3,
		"clues": "CSSSA019481",
		"fechaEvaluacion": "2015-10-11 02:20:47",
		"cerrado": 1,
		"firma": "",
		"responsable": "",
		"creadoAl": "2015-10-11 02:20:47",
		"modificadoAl": "2015-10-11 02:20:47",
		"borradoAl": null
	  }
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>

### DELETE/{ID} (DELETE)


Borra el registro especificado en el la base de datos. Tambien se puede borrar un idnidcador de una evaluacion 


Petición borrar evaluación

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso/{ID}*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso/%7bID%7d) 

Petición borrar indicador de una evaluación

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/{ID}?idIndicador=id*](http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/%7bID%7d?idIndicador=id) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionRecurso/2*](http://187.217.219.55/cium/api/v1/EvaluacionRecurso/2)

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"id": 2,
		"idUsuario": "3",
		"clues": "CSSSA019481",
		"fechaEvaluacion": "2015-10-11 02:20:47",
		"cerrado": null,
		"firma": "",
		"responsable": "",
		"creadoAl": "2015-10-11 02:20:47",
		"modificadoAl": "2015-10-11 02:20:47",
		"borradoAl": "2015-10-11 20:50:38"
	  }
	}

>**Ejemplo eliminar el indicador con id 15**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/2?idIndicador=15*](http://187.217.219.55/cium/api/v1/EvaluacionRecursoCriterio/2?idIndicador=15)

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "id": 39,
		  "idEvaluacionRecurso": 2,
		  "idCriterio": 163,
		  "idIndicador": 15,
		  "aprobado": 1,
		  "creadoAl": "2015-10-11 20:45:06",
		  "modificadoAl": "2015-10-11 20:45:06",
		  "borradoAl": null
		}
	  ]
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>

## Calidad


<p style="text-align: justify;">
Calidad es la evaluación que en las unidades medicas se encarga de monitoriar los indicadores de medición que tienen que ver con la calidad de la atención.
</p>


<HR>
### GET (INDEX)


Muestra una lista de las evaluaciones de calidad según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad?pagina=1&limite=5&order=-id) orden DESC

	
Respuesta:
	
	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "id": 1,
		  "idUsuario": "3",
		  "clues": "CSSSA019522",
		  "fechaEvaluacion": "2015-10-10 18:51:35",
		  "cerrado": 1,
		  "firma": "",
		  "responsable": "",
		  "creadoAl": "2015-10-10 23:51:08",
		  "modificadoAl": "2015-10-10 23:51:35",
		  "borradoAl": null,
		  "cone": {
			"clues": "CSSSA019522",
			"idCone": 2
		  },
		  "usuarios": {
			"id": 3,
			"username": "Admin",
			"email": "ramirez.esquinca@gmail.com",
			"permissions": [],
			"activated": true,
			"activated_at": null,
			"last_login": "2015-10-11 21:41:44",
			"nombres": "Eliecer",
			"apellidoPaterno": "ramirez",
			"apellidoMaterno": "esquinca",
			"cargo": "Admin",
			"nivel": 1,
			"telefono": "",
			"avatar": "http://localhost/SSA_MATERIAL/CIUM/assets/img/user.png",
			"creadoAl": "2015-03-13 17:50:38",
			"modificadoAl": "2015-10-11 21:41:44",
			"borradoAl": null
		  },
		  "cluess": {
			"clues": "CSSSA019522",
			"nombre": "ALDAMA",
			"domicilio": "CAMINO A LA LOCALIDAD DE COTZILNAM",
			"codigoPostal": 29877,
			"entidad": "CHIAPAS",
			"municipio": "ALDAMA",
			"localidad": "ACACOYAGUA",
			"jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
			"institucion": "SSA",
			"tipoUnidad": "CONSULTA EXTERNA",
			"estatus": "EN OPERACIÓN",
			"estado": "1",
			"tipologia": "CENTROS DE SALUD CON SERVICIOS AMPLIADOS"
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

<HR>	
### GET/ {ID} (SHOW)


Devuelve la información del registro especificado. para este metodo hay que llamar tambien al metodo de criterios

Petición

Obtener la ficha técnica

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad/{ID}*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad/%7bID%7d) 

obtener el detalle para la vista ver

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/{ID}*](http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/%7bID%7d) 

obtener el detalle para la vista update

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/CriterioEvaluacionCalidad/{cone}/{indicador}/{evaluacion*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad/3/2/1) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad/1*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad/1) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"zona": "LARRAINZAR",
		"nombres": "Eliecer",
		"apellidoPaterno": "ramirez",
		"apellidoMaterno": "esquinca",
		"firma": "",
		"responsable": "",
		"fechaEvaluacion": "2015-10-10 18:51:35",
		"cerrado": 1,
		"id": 1,
		"clues": "CSSSA019522",
		"nombre": "ALDAMA",
		"domicilio": "CAMINO A LA LOCALIDAD DE COTZILNAM",
		"codigoPostal": 29877,
		"entidad": "CHIAPAS",
		"municipio": "ALDAMA",
		"localidad": "ACACOYAGUA",
		"jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
		"institucion": "SSA",
		"tipoUnidad": "CONSULTA EXTERNA",
		"estatus": "EN OPERACIÓN",
		"estado": "1",
		"tipologia": "CENTROS DE SALUD CON SERVICIOS AMPLIADOS",
		"nivelCone": "Ambulatorio sin doctor",
		"idCone": 2
	  }
	}

>**Ejemplo Obtener los detalles en ver id = 1**
> - [*http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/1*](http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/1) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"criterios": {
		  "3035B": [
			{
			  "id": 150,
			  "nombre": "Cumplimiento de 5 visitas prenatales",
			  "lugarVerificacion": "Expediente"
			}
		  ]
		},
		"indicadores": {
		  "3035B": {
			"clr": "hsla(34, 92%, 32%, 0.62)",
			"id": 14,
			"codigo": "3035B",
			"indicador": "Servicios de atención prenatal 5 visitas",
			"cone": "Ambulatorio sin doctor",
			"idCone": 2,
			"columnas": {
			  "11": {
				"total": 1,
				"expediente": "11",
				"color": "hsla(130, 90%, 38%, 0.62)"
			  },
			  "22": {
				"total": 0,
				"expediente": "22",
				"color": "hsla(0, 95%, 49%, 0.62)"
			  }
			},
			"aprobado": 1,
			"noAprobado": 1,
			"totalCriterio": 2,
			"totalColumnas": 2,
			"sumaCriterio": 1,
			"porciento": "50.00",
			"color": "hsla(0, 95%, 49%, 0.62)"
		  }
		},
		"datos": {
		  "3035B": {
			"11": [
			  {
				"id": 1,
				"aprobado": 1,
				"idCriterio": 150,
				"nombre": "Cumplimiento de 5 visitas prenatales"
			  }
			],
			"22": [
			  {
				"id": 2,
				"aprobado": 0,
				"idCriterio": 150,
				"nombre": "Cumplimiento de 5 visitas prenatales"
			  }
			]
		  }
		}
	  },
	  "total": 1,
	  "hallazgos": {
		"3035B": {
		  "idIndicador": 14,
		  "idAccion": 7,
		  "idPlazoAccion": null,
		  "resuelto": 0,
		  "descripcion": "aca",
		  "tipo": "R",
		  "accion": "Capacitación"
		}
	  }
	}

>**Ejemplo Obtener los detalles en upadate id = 1**
> - [*http://187.217.219.55/cium/api/v1/CriterioEvaluacionCalidad/3/2/1*](http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/3/2/1) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"11": {
		  "id": 3,
		  "idEvaluacionCalidad": 2,
		  "idIndicador": 14,
		  "columna": 1,
		  "expediente": "11",
		  "cumple": 1,
		  "promedio": "100.00",
		  "totalCriterio": 1,
		  "creadoAl": "2015-10-11 21:58:00",
		  "modificadoAl": "2015-10-11 21:58:00",
		  "borradoAl": null,
		  "aprobado": {
			"150": 1
		  }
		}
	  },
	  "criterios": [
		{
		  "idCriterio": 150,
		  "idIndicador": 14,
		  "idCone": 2,
		  "idlugarVerificacion": 16,
		  "creadoAl": "0000-00-00 00:00:00",
		  "modificadoAl": "0000-00-00 00:00:00",
		  "criterio": "Cumplimiento de 5 visitas prenatales",
		  "lugarVerificacion": "Expediente"
		}
	  ],
	  "total": 1,
	  "totalCriterio": 1,
	  "hallazgo": 0,
	  "tiene": 1
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados


<HR>
### POST (STORE)


Crear un nuevo registro en la base de datos con los datos enviados

REQUEST (SEND)

<p style="text-align: justify;">
Recibe un input request tipo json de los datos a almacenar. Se quita la opcion NA solo es 1 o 0 para la evaluacion. Se genera un hallazgo si hay un no en cualquier criterio del indicador el hallazgo es uno por todos los criterios no cumplidos en un indicador.
Si la evaluacion ya se va a terminar se pone cerrado = 1
</p>

	{
	  "evaluaciones": [
		{
		  "id": 2,
		  "clues": "CSSSA000284",
		  "fechaEvaluacion": "2015-10-11 21:48:00",
		  "cerrado": null,
		  "registros": [
			{
			  "idIndicador": "14",
			  "expediente": "11",
			  "columna": 1,
			  "cumple": 1,
			  "promedio": 100,
			  "totalCriterio": 1,
			  "criterios": [
				{
				  "idCriterio": "150",
				  "idIndicador": "14",
				  "aprobado": 1
				}
			  ]
			},
			{
			  "idIndicador": "14",
			  "expediente": "22",
			  "columna": 2,
			  "cumple": 0,
			  "promedio": 0,
			  "totalCriterio": 1,
			  "criterios": [
				{
				  "idCriterio": "150",
				  "idIndicador": "14",
				  "aprobado": "0"
				}
			  ]
			}
		  ],
		  "hallazgos": [
			{
			  "descripcion": "solo hay 2 expedientes",
			  "idAccion": 5,
			  "idIndicador": "14"
			}
		  ]
		}
	  ]
	}
	

Petición

<CODE>MÉTODO POST</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad)

Respuesta

	{
	  "status": 201,
	  "messages": "Creado",
	  "data": {
		"id": 2,
		"idUsuario": 3,
		"clues": "CSSSA000284",
		"fechaEvaluacion": "2015-10-11 21:48:00",
		"cerrado": null,
		"firma": "",
		"responsable": "",
		"creadoAl": "2015-10-11 21:48:00",
		"modificadoAl": "2015-10-11 21:48:00",
		"borradoAl": null
	  }
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>
### PUT/{ID} (UPDATE)

<p style="text-align: justify;">
Actualizar el registro especificado en el la base de datos
Si la evaluacion ya se va a terminar se pone cerrado = 1
</p>

REQUEST (SEND)

Recibe un Input Request con el json de los datos

	{
	  "evaluaciones": [
		{
		  "id": 2,
		  "clues": "CSSSA000284",
		  "fechaEvaluacion": "2015-10-11 21:48:00",
		  "cerrado": null,
		  "registros": [
			{
			  "idIndicador": "14",
			  "expediente": "11",
			  "columna": 1,
			  "cumple": 1,
			  "promedio": 100,
			  "totalCriterio": 1,
			  "criterios": [
				{
				  "idCriterio": "150",
				  "idIndicador": "14",
				  "aprobado": 1
				}
			  ]
			},
			{
			  "idIndicador": "14",
			  "expediente": "22",
			  "columna": 2,
			  "cumple": 0,
			  "promedio": 0,
			  "totalCriterio": 1,
			  "criterios": [
				{
				  "idCriterio": "150",
				  "idIndicador": "14",
				  "aprobado": "0"
				}
			  ]
			}
		  ],
		  "hallazgos": [
			{
			  "descripcion": "solo hay 2 expedientes",
			  "idAccion": 5,
			  "idIndicador": "14"
			}
		  ]
		}
	  ]
	}

Petición

<CODE>MÉTODO PUT</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad/{ID}*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad/%7bID%7d) 

>**Ejemplo actualizar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad/1*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad/1)

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"id": 2,
		"idUsuario": 3,
		"clues": "CSSSA000284",
		"fechaEvaluacion": "2015-10-11 21:48:00",
		"cerrado": null,
		"firma": "",
		"responsable": "",
		"creadoAl": "2015-10-11 21:48:00",
		"modificadoAl": "2015-10-11 21:48:00",
		"borradoAl": null
	  }
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>

### DELETE/{ID} (DELETE)


Borra el registro especificado en el la base de datos. Tambien se puede borrar un idnidcador de una evaluacion 


Petición borrar evaluación

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad/{ID}*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad/%7bID%7d) 

Petición borrar indicador de una evaluación

<CODE>MÉTODO DELETE</CODE> [*http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/{ID}?idIndicador=id*](http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/%7bID%7d?idIndicador=id) 

>**Ejemplo eliminar el registro con id 1**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionCalidad/2*](http://187.217.219.55/cium/api/v1/EvaluacionCalidad/2)

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"id": 1,
		"idUsuario": "3",
		"clues": "CSSSA000284",
		"fechaEvaluacion": "2015-10-11 21:48:00",
		"cerrado": null,
		"firma": "",
		"responsable": "",
		"creadoAl": "2015-10-11 21:48:00",
		"modificadoAl": "2015-10-11 21:48:00",
		"borradoAl": "2015-10-11 22:38:08"
	  }
	}

>**Ejemplo eliminar el indicador con id 14**

> - [*http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/2?idIndicador=14*](http://187.217.219.55/cium/api/v1/EvaluacionCalidadCriterio/2?idIndicador=14)

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "id": 3,
		  "idEvaluacionCalidad": 2,
		  "idCriterio": 150,
		  "idIndicador": 14,
		  "aprobado": 1,
		  "idEvaluacionCalidadRegistro": 3,
		  "creadoAl": "2015-10-11 21:58:00",
		  "modificadoAl": "2015-10-11 21:58:00",
		  "borradoAl": null
		},
		{
		  "id": 4,
		  "idEvaluacionCalidad": 2,
		  "idCriterio": 150,
		  "idIndicador": 14,
		  "aprobado": 0,
		  "idEvaluacionCalidadRegistro": 4,
		  "creadoAl": "2015-10-11 22:00:22",
		  "modificadoAl": "2015-10-11 22:00:22",
		  "borradoAl": null
		}
	  ]
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>

## Hallazgo


<p style="text-align: justify;">
Hallazgos módulo que muestra de entrada los problemas principales actuales o historico segun el filtro por unidad médica y por criterio.
</p>


<HR>
### GET (INDEX)


Muestra una lista de los hallazgos según los parámetros a procesar en la petición.

Parametros

Paginación

: <kbd>$pagina</kbd> número del puntero(offset) para la sentencia limit

: <kbd>$limite</kbd> número de filas a mostrar por página

Busqueda

: <kbd>$valor</kbd> string con el valor para hacer la busqueda

: <kbd>$order</kbd> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Hallazgo*](http://187.217.219.55/cium/api/v1/Hallazgo)

>**Petición con parametros:**

> - [*http://187.217.219.55/cium/api/v1/Hallazgo?pagina=1&limite=5&order=id*](http://187.217.219.55/cium/api/v1/Hallazgo?pagina=1&limite=5&order=id) orden ASC

> - [*http://187.217.219.55/cium/api/v1/Hallazgo?pagina=1&limite=5&order=-id*](http://187.217.219.55/cium/api/v1/Hallazgo?pagina=1&limite=5&order=-id) orden DESC

Muestra el listado de unidades médicas con detalle
	
Respuesta:
	
	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "clues": "CSSSA019522",
		  "nombre": "ALDAMA",
		  "jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
		  "municipio": "ALDAMA",
		  "cone": "Ambulatorio sin doctor"
		}
	  ],
	  "indicadores": [
		{
		  "color": "hsla(34, 92%, 32%, 0.62)",
		  "codigo": "3035B",
		  "indicador": "Servicios de atención prenatal 5 visitas",
		  "categoria": "CALIDAD",
		  "total": 1
		},
		{
		  "color": "hsla(226, 40%, 49%, 0.62)",
		  "codigo": "7050",
		  "indicador": "Planificación familiar",
		  "categoria": "RECURSO",
		  "total": 1
		}
	  ],
	  "totalIndicador": 2,
	  "total": 1
	}
	
Petición criterios

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/indexCriterios*](http://187.217.219.55/cium/api/v1/indexCriterios)

Respuesta
	
	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"RECURSO": {
		  "80": {
			"codigo": "7050",
			"color": "hsla(226, 40%, 49%, 0.62)",
			"indicador": "Planificación familiar",
			"aprobado": 0,
			"idCriterio": 80,
			"idIndicador": 5,
			"idlugarVerificacion": 10,
			"criterio": "Dispositivo intrauterino",
			"lugarVerificacion": "Métodos de planificación familiar",
			"total": 1
		  }
		},
		"CALIDAD": {
		  "150": {
			"clues": "CSSSA019522",
			"codigo": "3035B",
			"color": "hsla(34, 92%, 32%, 0.62)",
			"indicador": "Servicios de atención prenatal 5 visitas",
			"aprobado": 0,
			"idCriterio": 150,
			"idIndicador": 14,
			"idlugarVerificacion": 16,
			"criterio": "Cumplimiento de 5 visitas prenatales",
			"lugarVerificacion": "Expediente",
			"exp": 1,
			"total": 1
		  }
		}
	  },
	  "total": 2
	}

Para estos metodo hay que pasar un json por la url con el nombre filtro y contiene los filtros siguientes

	{
	  "historial": false,
	  "indicador": [
		"7050",
		"3035B"
	  ],
	  "visualizar": "tiempo",
	  "anio": "2015",
	  "um": {
		"tipo": "municipio",
		"cone": [
		  "Ambulatorio%20sin%20doctor"
		],
		"jurisdiccion": [
		  "SAN%20CRISTÓBAL%20DE%20LAS%20CASAS"
		],
		"municipio": [
		  "ALDAMA"
		]
	  },
	  "clues": [],
	  "verTodosIndicadores": false,
	  "verTodosUM": false,
	  "verTodosClues": true,
	  "bimestre": "9%20and%2010"
	}

>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados
> - <code>total</code> Total de registros devueltos

<HR>	
### GET/ {CLUES}?filtro (SHOW)

Devuelve el listado de los indicadores que tienen detalle para la unidad médica. 

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Hallazgo/{CLUEs}?filtro={json}*](http://187.217.219.55/cium/api/v1/Hallazgo/{CLUEs}?filtro={json}) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Hallazgo/CSSSA019522?filtro={"historial":false,"indicador":["7050","3035B"],"visualizar":"tiempo","anio":"2015","um":{"tipo":"municipio","cone":["Ambulatorio%20sin%20doctor"],"jurisdiccion":["SAN%20CRISTÓBAL%20DE%20LAS%20CASAS"],"municipio":["ALDAMA"]},"clues":[],"verTodosIndicadores":false,"verTodosUM":false,"verTodosClues":true,"bimestre":"9%20and%2010","umActiva":"CSSSA019522","nivel":1}*](http://187.217.219.55/cium/api/v1/Hallazgo/CSSSA019522?filtro={"historial":false,"indicador":["7050","3035B"],"visualizar":"tiempo","anio":"2015","um":{"tipo":"municipio","cone":["Ambulatorio%20sin%20doctor"],"jurisdiccion":["SAN%20CRISTÓBAL%20DE%20LAS%20CASAS"],"municipio":["ALDAMA"]},"clues":[],"verTodosIndicadores":false,"verTodosUM":false,"verTodosClues":true,"bimestre":"9%20and%2010","umActiva":"CSSSA019522","nivel":1}) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "color": "hsla(34, 92%, 32%, 0.62)",
		  "codigo": "3035B",
		  "indicador": "Servicios de atención prenatal 5 visitas",
		  "categoria": "CALIDAD"
		},
		{
		  "color": "hsla(226, 40%, 49%, 0.62)",
		  "codigo": "7050",
		  "indicador": "Planificación familiar",
		  "categoria": "RECURSO"
		}
	  ]
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados


<HR>	
### GET/ {indicador}?filtro (SHOW)

Devuelve el listado de las evaluaciones que tienen detalle en el indicador para la unidad médica. 

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Hallazgo/{indicador}?filtro={json}*](http://187.217.219.55/cium/api/v1/Hallazgo/{indicador}?filtro={json}) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Hallazgo/7050?filtro={"historial":false,"indicador":["7050","3035B"],"visualizar":"tiempo","anio":"2015","um":{"tipo":"municipio","cone":["Ambulatorio%20sin%20doctor"],"jurisdiccion":["SAN%20CRISTÓBAL%20DE%20LAS%20CASAS"],"municipio":["ALDAMA"]},"clues":[],"verTodosIndicadores":false,"verTodosUM":false,"verTodosClues":true,"bimestre":"9%20and%2010","umActiva":"CSSSA019522","nivel":1}*](http://187.217.219.55/cium/api/v1/Hallazgo/7050?filtro={"historial":false,"indicador":["7050","3035B"],"visualizar":"tiempo","anio":"2015","um":{"tipo":"municipio","cone":["Ambulatorio%20sin%20doctor"],"jurisdiccion":["SAN%20CRISTÓBAL%20DE%20LAS%20CASAS"],"municipio":["ALDAMA"]},"clues":[],"verTodosIndicadores":false,"verTodosUM":false,"verTodosClues":true,"bimestre":"9%20and%2010","umActiva":"CSSSA019522","nivel":1}) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "color": "hsla(226, 40%, 49%, 0.62)",
		  "codigo": "7050",
		  "indicador": "Planificación familiar",
		  "categoria": "RECURSO",
		  "clues": "CSSSA019522",
		  "nombre": "ALDAMA",
		  "jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
		  "fechaEvaluacion": "2015-10-10 23:46:55",
		  "idEvaluacion": 1
		}
	  ]
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados


<HR>	
### GET/ {indicador}?filtro (SHOW)

Devuelve el detalle de la evaluacion que tienen detalle. 

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Hallazgo/{indicador}?filtro={json}*](http://187.217.219.55/cium/api/v1/Hallazgo/{indicador}?filtro={json}) 

>**Ejemplo Obtener los datos del registro con id = 1**

> - [*http://187.217.219.55/cium/api/v1/Hallazgo/7050?filtro={"historial":false,"indicador":["7050","3035B"],"visualizar":"tiempo","anio":"2015","um":{"tipo":"municipio","cone":["Ambulatorio%20sin%20doctor"],"jurisdiccion":["SAN%20CRISTÓBAL%20DE%20LAS%20CASAS"],"municipio":["ALDAMA"]},"clues":[],"verTodosIndicadores":false,"verTodosUM":false,"verTodosClues":true,"bimestre":"9%20and%2010","umActiva":"CSSSA019522","nivel":1}*](http://187.217.219.55/cium/api/v1/Hallazgo/7050?filtro={"historial":false,"indicador":["7050","3035B"],"visualizar":"tiempo","anio":"2015","um":{"tipo":"municipio","cone":["Ambulatorio%20sin%20doctor"],"jurisdiccion":["SAN%20CRISTÓBAL%20DE%20LAS%20CASAS"],"municipio":["ALDAMA"]},"clues":[],"verTodosIndicadores":false,"verTodosUM":false,"verTodosClues":true,"bimestre":"9%20and%2010","umActiva":"CSSSA019522","nivel":1}) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "color": "hsla(226, 40%, 49%, 0.62)",
		  "codigo": "7050",
		  "indicador": "Planificación familiar",
		  "categoria": "RECURSO",
		  "clues": "CSSSA019522",
		  "nombre": "ALDAMA",
		  "jurisdiccion": "SAN CRISTÓBAL DE LAS CASAS",
		  "fechaEvaluacion": "2015-10-10 23:46:55",
		  "idEvaluacion": 1
		}
	  ]
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados



<hr>
<hr>

## Dashboard


<p style="text-align: justify;">
En este tema se explica el llamado de todos los metodos de cada uno de los gráficos. para todos se maneja el envio del filtro por la url en una variable llamada filtro y contiene el siguiente json
</p>
	
	{
	  "top": "10",
	  "tipo": "Calidad",
	  "indicador": [
		"7050",
		"3035B"
	  ],
	  "visualizar": "tiempo",
	  "anio": "2015",
	  "um": {
		"tipo": "municipio",
		"cone": [
		  "Ambulatorio%20sin%20doctor"
		],
		"jurisdiccion": [
		  "SAN%20CRISTÓBAL%20DE%20LAS%20CASAS"
		],
		"municipio": [
		  "ALDAMA"
		]
	  },
	  "clues": [],
	  "verTodosIndicadores": false,
	  "verTodosUM": false,
	  "verTodosClues": true,
	  "bimestre": "9%20and%2010"
	}


>**Filtro**

> - top = para los graficos de TOP elije el numero del top 5,10 etc
> - tipo = para los graficos que se separan en uno  mismo por calidad y recurso
> - indicador = lista de indicadores
> - visualizar = tiempo o por parametro(jurisdiccion,municipio,zona,cone,clues)
> - anio = año a filtrar
> - um = objeto tipo = zona o municipio. cone = nombre de los niveles de cone. jurisidccion = listado de jurisdicciones, municipio o zona
> - clues = lista de clues a filtrar uno o mas
> - verTodosIndicadores = true/false respetar o no el objeto indicador para el filtro
> - verTodosUM = true/false respetar o no respetar el filtro por um
> - verTodosClues = true/false respetar o no el listado de clues 


<HR>	
### GET ? filtro (gaugeRecurso)

Devuelve los datos para el grafico hallazgos recurso y calidad segun el filtro tipo.

Petición

filtro={"tipo":"Recurso"}

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/hallazgoGauge?filtro={json}*](http://187.217.219.55/cium/api/v1/hallazgoGauge?filtro={json}) 

filtro={"tipo":"Calidad"}

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/hallazgoGauge?filtro={json}*](http://187.217.219.55/cium/api/v1/hallazgoGauge?filtro={json}) 

>**Ejemplo Obtener los datos del registro **

> - [*http://187.217.219.55/cium/api/v1/hallazgoGauge?filtro={filtro}*](http://187.217.219.55/cium/api/v1/hallazgoGauge?filtro={json}) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "total": 287
		}
	  ],
	  "valor": 1,
	  "rangos": [
		{
		  "min": 0,
		  "max": 71.75,
		  "color": "#DDD"
		},
		{
		  "min": 71.75,
		  "max": 143.5,
		  "color": "#FDC702"
		},
		{
		  "min": 143.5,
		  "max": 215.25,
		  "color": "#FF7700"
		},
		{
		  "min": 215.25,
		  "max": 287,
		  "color": "#C50200"
		}
	  ],
	  "indicadores": [
		{
		  "codigo": "3035B",
		  "color": "hsla(34, 92%, 32%, 0.62)",
		  "indicador": "Servicios de atención prenatal 5 visitas"
		}
	  ],
	  "total": 287
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>	
### GET ? filtro (recurso)

Devuelve los datos para crear la grafica de barras de recursos

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/Hallazgo/{indicador}?filtro={json}*](http://187.217.219.55/cium/api/v1/Hallazgo/{indicador}?filtro={json}) 

>**Ejemplo Obtener los datos **

> - [*http://187.217.219.55/cium/api/v1/recurso?filtro={filtro}*](http://187.217.219.55/cium/api/v1/recurso?filtro={json}) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"datasets": [
		  {
			"label": "Planificación familiar",
			"fillColor": "hsla(226, 40%, 49%, 0.62)",
			"strokeColor": "hsla(130, 90%, 38%, 0.62)",
			"highlightFill": "hsla(226, 40%, 49%,0.30)",
			"highlightStroke": "hsla(130, 90%, 38%, 0.62)",
			"data": [
			  "80.00"
			]
		  }
		],
		"labels": [
		  "October"
		]
	  },
	  "total": 2
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados


<HR>	
### GET ? filtro (calidad)

Devuelve los datos para crear el grafico de barras de calidad. 

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/calidad?filtro={json}*](http://187.217.219.55/cium/api/v1/calidad?filtro={json}) 

>**Ejemplo Obtener los datos**

> - [*http://187.217.219.55/cium/api/v1/calidad?filtro={json}*](http://187.217.219.55/cium/api/v1/calidad?filtro={json}) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"datasets": [
		  {
			"label": "Servicios de atención prenatal 5 visitas",
			"fillColor": "hsla(34, 92%, 32%, 0.62)",
			"strokeColor": "hsla(0, 95%, 49%, 0.62)",
			"highlightFill": "hsla(34, 92%, 32%,0.30)",
			"highlightStroke": "hsla(0, 95%, 49%, 0.62)",
			"data": [
			  "50.00"
			]
		  }
		],
		"labels": [
		  "October"
		]
	  },
	  "total": 2
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados


<HR>	
### GET ? filtro (pie)

Devuelve los datos para crear el grafico de paste de recurso y calidad segun filtro{ tipo : }. 

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/pieVista?filtro={json}*](http://187.217.219.55/cium/api/v1/pieVista?filtro={json}) 

>**Ejemplo Obtener los datos**

> - [*http://187.217.219.55/cium/api/v1/pieVista?filtro={json}*](http://187.217.219.55/cium/api/v1/pieVista?filtro={json}) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "value": 286,
		  "color": "hsla(1, 100%, 50%, 0.62)",
		  "highlight": "hsla(1, 100%, 50%, 0.32)",
		  "label": "No Visitado"
		},
		{
		  "value": 1,
		  "color": "hsla(107, 100%, 50%, 0.62)",
		  "highlight": "hsla(107, 100%, 50%, 0.32)",
		  "label": "Visitado"
		}
	  ],
	  "total": 1
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>	
### GET ? filtro (indicadores)

Devuelve los datos para listar el avance de los indicadores de recurso y calidad segun filtro{ tipo : }. 

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/alertaDash?filtro={json}*](http://187.217.219.55/cium/api/v1/alertaDash?filtro={json}) 

>**Ejemplo Obtener los datos**

> - [*http://187.217.219.55/cium/api/v1/alertaDash?filtro={json}*](http://187.217.219.55/cium/api/v1/alertaDash?filtro={json})

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": [
		{
		  "codigo": "7050",
		  "nombre": "Planificación familiar",
		  "color": "hsla(130, 90%, 38%, 0.62)",
		  "porcentaje": "80.00"
		}
	  ],
	  "total": 1
	}
	
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>	
### GET ? filtro (top recurso)

Devuelve los datos para listar el TOP de las mejores y peores UM de recurso

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/TopRecursoGlobal?filtro={json}*](http://187.217.219.55/cium/api/v1/TopRecursoGlobal?filtro={json}) 

>**Ejemplo Obtener los datos**

> - [*http://187.217.219.55/cium/api/v1/TopRecursoGlobal?filtro={json}*](http://187.217.219.55/cium/api/v1/TopRecursoGlobal?filtro={json})

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"TOP_MAS": [
		  {
			"clues": "CSSSA019522",
			"nombre": "ALDAMA",
			"porcentaje": "80.0000"
		  }
		],
		"TOP_MENOS": [
		  {
			"clues": "CSSSA019522",
			"nombre": "ALDAMA",
			"porcentaje": "80.0000"
		  }
		]
	  },
	  "total": 2
	}
		
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<HR>	
### GET ? filtro (top calidad)

Devuelve los datos para listar el TOP de las mejores y peores UM de calida

Petición

<CODE>MÉTODO GET</CODE> [*http://187.217.219.55/cium/api/v1/TopCalidadGlobal?filtro={json}*](http://187.217.219.55/cium/api/v1/TopCalidadGlobal?filtro={json}) 

>**Ejemplo Obtener los datos**

> - [*http://187.217.219.55/cium/api/v1/TopCalidadGlobal?filtro={json}*](http://187.217.219.55/cium/api/v1/TopCalidadGlobal?filtro={json}) 

Respuesta

	{
	  "status": 200,
	  "messages": "Operación realizada con exito",
	  "data": {
		"TOP_MAS": [
		  {
			"clues": "CSSSA019522",
			"nombre": "ALDAMA",
			"porcentaje": "50.000000"
		  }
		],
		"TOP_MENOS": [
		  {
			"clues": "CSSSA019522",
			"nombre": "ALDAMA",
			"porcentaje": "50.000000"
		  }
		]
	  },
	  "total": 2
	}
		
>**Nota**

> - <code>status</code> Estado de la petición 
> - <code>messages</code> Mensaje de confirmacion o error
> - <code>data</code> Objeto con todos los resultados

<hr>
<hr>
