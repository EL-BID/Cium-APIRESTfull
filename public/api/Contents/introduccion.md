# Introducción

<p style="text-align: justify;">Actualmente, las API REST están realmente de moda: parece que cualquier aplicación deba proporcionar su “API REST”. Pero...¿qué significa realmente una API REST?

REST deriva de "REpresentational State Transfer", que traducido vendría a ser “transferencia de representación de estado”, lo que tampoco aclara mucho, pero contiene la clave de lo que significa. Porque la clave de REST es que un servicio REST no tiene estado (es stateless), lo que quiere decir que, entre dos llamadas cualesquiera, el servicio pierde todos sus datos. Esto es, que no se puede llamar a un servicio REST y pasarle unos datos (p. ej. un usuario y una contraseña) y esperar que “nos recuerde” en la siguiente petición. De ahí el nombre: el estado lo mantiene el cliente y por lo tanto es el cliente quien debe pasar el estado en cada llamada. Si quiero que un servicio REST me recuerde, debo pasarle quien soy en cada llamada. Eso puede ser un usuario y una contraseña, un token o cualquier otro tipo de credenciales, pero debo pasarlas en cada llamada. Y lo mismo aplica para el resto de información.
</p>

## Objetivo

<p style="text-align: justify;">
El siguiente manual es para explicar el uso y el llamado de cada método con el cuerpo (datos) y cabeceras explicitas en cada petición para lograr una repuesta de parte del servidor.

El servicio API es consumido tanto por la aplicación web como los dispositivos Android o IOS.
</p>
![](images/esquema_api.gif)

## Obtención del token

<p style="text-align: justify;">
La API está protegido por el servidor de credenciales de la secretaria de salud SALUID por lo que la aplicación necesita estar registrada para poder acceder a los recursos y contar con un usuario dueño de la aplicación para poder obtener el token de acceso, este token es muy importante ya que sin él no podremos tener acceso a ningún recurso de la aplicación API por lo que siempre en primer lugar debemos de obtenerlo.

Ejemplo de obtención del token via CURL de php.</p>

Petición:

	<?php

		$curl = curl_init();
		
		$post_request = ‘grant_type=password’
		.’&client_id=’.’CLIENT_ID’
		.’&client_secret=’.’CLIENT_SECRET’
		.’&username=’.’email’
		.’&password=’.’password’;
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => “http://187.217.219.54/oauth/access_token“,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => “”,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST =>$post_request,
		));
		response=curl_exec(curl);
		err=curl_error(curl);
		curl_close($curl);
		if ($err) {
			echo “cURL Error #:” . $err;
		} else {
			echo $response;
		}

	?>

Respuesta:

	{
		"access_token": "yTl379mxyVpGgmjREZs6UF9p6VILca4e33WN8wtd",
		"token_type": "Bearer",
		"expires_in": 604800,
		"refresh_token": "CJwknO1nGnqjKG82VaYkr4XU3sqXQ8ARUj1VxX7I"
	}

<p style="text-align: justify;">
El access_token se necesita para todas las peticiones a la API, el refresh\_token se utilizara para obtener un nuevo token en un dado caso el que obtuvimos primero caduque.
</p>

## Cabeceras (Headers)

<p style="text-align: justify;">
Para la comunicación entre la API y el CLIENTE en cada petición se necesita las cabeceras siguientes para identificar el usuario y los permisos de acceso a métodos y a servicios.
</p>

Authorization : Bearer access_token

X-Usuario: [*email@usuario.com*]

>**Ejemplo:**
<code><br>
	Authorization : Bearer yTl379mxyVpGgmjREZs6UF9p6VILca4e33WN8wtd</code>
	<code><br>X-Usuario: ramirez.esquinca@gmail.com
</code>
	
<p style="text-align: justify;">
Con el token y las cabeceras ya estamos listos para empezar el llamado a los métodos de las rutas de la API.<br>
En los siguientes capitulos se explican las rutas y las llamadas a sus métodos respectivos.
</p>