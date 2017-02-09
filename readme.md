## CIUM API RESTFULL

[![Build Status](https://travis-ci.org/laravel/framework.svg)]
[![License](https://poser.pugx.org/laravel/framework/license.svg)]

<p style="text-align: justify;">
La arquitectura REST es muy útil para construir un cliente/servidor para aplicaciones en red. REST significa Representational State Transfer (Transferencia de Estado Representacional) de sus siglas en 
inglés. Una API REST es una API, o librería de funciones, a la cual accedemos mediante protocolo HTTP, ósea desde direcciones webs o URL mediante las cuales el servidor procesa una consulta a una 
base de datos y devuelve los datos del resultado en formato XML, JSON, texto plano, etc. (Para el proyecto CIUM nos enfocaremos en JSON) Mediante REST utilizas los llamados Verbos HTTP, que son 
GET (Mostrar), POST (Insertar), PUT (Agregar/Actualizar) y DELETE (Borrar).
</p>
## Official Documentation

 > - [Manual de usuario](public/api/Contents) [pdf](public/manual-usuario.pdf)

## Tecnología

* [APACHE]('http://www.apache.org/')
* [PHP 5.4]('https://secure.php.net/')  o superior 
* [MYSQL]('https://www.mysql.com/')
* [LARAVEL 5.0]('http://laravel.com/docs/master') o superios


## Instalación

> - Instalar previamente [Composer]('http://composer.io/') y ejecutar desde la consola el siguiente comando: `composer install` desde el directorio de 
> - Editar el archivo .env ubicado en la raiz del directorio de instalación

	APP_ENV=local
	APP_DEBUG=true
	APP_KEY=WZPQfr6VCLBhRg8KL8TA3Y3dwiXwwSgQ

	DB_HOST=localhost
	DB_DATABASE=cium
	DB_USERNAME=root
	DB_PASSWORD=***

	CACHE_DRIVER=file
	SESSION_DRIVER=file

	OAUTH_SERVER = servidor

	CLIENT_ID=1A2BCA76XY0
	CLIENT_SECRET=YESIDRUN
	
> ** ENV **

> - 1.- APP_KEY: Clave de encriptación para laravel
> - 2.- DB_HOST: Dominio de la conexión a la base de datos
> - 3.- DB_DATABASE: Nombre de la base de datos
> - 4.- DB_USERNAME: Usuario con permisos de lectura y escritura para la base de datos
> - 5.- DB_PASSWORD: Contraseña del usuario 
> - 6.- OAUTH_SERVER: Dirección del Servidor OAUTH
> - 7.- CLIENT_ID: ID del cliente con el que se dio de alta en el servidor OAUTH
> - 8.- CLIENT_SECRET: Clave secreta para optener el token para el clicnte desde el servidor OAUTH

### MYSQL

> - 1.- Crear la base de datos Cium	
> - 2.- Correr el script para generar los schemas


## Contributing

> - Secretaria de salud del estado de chiapas ISECH
> - Salud Mesoamerica 2015 SM2015
> - akira.redwolf@gmail.com 
> - h.cortes@gmail.com 
> - ramirez.esquinca@gmail.com

### License

The API CIUM es open-sourced software bajo licencia de [MIT license](http://opensource.org/licenses/MIT)