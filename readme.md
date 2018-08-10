*Esta herramienta digital forma parte del catálogo de herramientas del **Banco Interamericano de Desarrollo**. Puedes conocer más sobre la iniciativa del BID en [code.iadb.org](code.iadb.org)*

## API del Sistema CIUM (Captura de Indicadores en Unidades Médicas).

  

[![Build Status](https://travis-ci.org/laravel/framework.svg)]
[![License](https://poser.pugx.org/laravel/framework/license.svg)]

### Descripción y contexto
---
<p style="text-align: justify;">
La secretaria de salud (ISECH) a través de la Dirección de Informática, con el apoyo del Proyecto Salud Mesoamérica 2015 (SM2015), Ponen en marcha la mejora de los sistemas de medición de calidad y abastos en las unidades médicas (etab y cium), esto con el fin de proporcionar las herramientas de monitoreo de una forma sencilla y a la vez más completa que permitan la correcta toma de decisiones y acciones en base a la medición de sus indicadores.
</p>

### Guía de usuario
---
##### Manual de Usuario:
Para guiar y ser mas explicito a cualquier usuario encargado para trabajar con CIUM

 > - [Manual de usuario](public/api/Contents) [pdf](public/manual-usuario.pdf)

  
##### Manual Técnico:

Para la continuidad en el desarrollo de CIUM se brinda un Manual Técnico:

[ver](public/doc)

### Guía de instalación
---
#### Requisitos
##### Software
Para poder instalar y utilizar esta API, deberá asegurarse de que su servidor cumpla con los siguientes requisitos:

* [APACHE]('http://www.apache.org/')
* [PHP 5.6]('https://secure.php.net/')  o superior 
* [MYSQL]('https://www.mysql.com/')
* [LARAVEL 5.0]('http://laravel.com/docs/master') o superios

* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension
* [Composer](https://getcomposer.org/) es una librería de PHP para el manejo de dependencias.
* Opcional [Postman](https://www.getpostman.com/) que permite el envío de peticiones HTTP REST sin necesidad de desarrollar un cliente.

#### Instalación
Guia de Instalación Oficial de Laravel 5.4 [Aquí](https://laravel.com/docs/5.4/installation)
##### Proyecto (API)
El resto del proceso es sencillo.
1. Clonar el repositorio con: `git clone https://github.com/checherman/Cium-APIRESTfull.git`
2. Instalar dependencias: `composer install`
3. Renombrar el archivo `base.env` a `.env` ubicado en la raiz del directorio de instalación y editarlo.
       
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
       
    
* **APP_KEY**: Clave de encriptación para laravel.
* **APP_DEBUG**: `true` o `false` dependiento si desea o no tener opciones de debug en el código.
* **DB_HOST**: Dominio de la conexión a la base de datos.
* **DB_DATABASE**: Nombre de la base de datos.
* **DB_USERNAME**: Usuario con permisos de lectura y escritura para la base de datos.
* **DB_PASSWORD**: Contraseña del usuario.

* **CLIENT_ID**: ID de la cliente para conexion con salud id.
* **CLIENT_SECRET**: Llave para el proyecto salud id.

##### Base de Datos del proyecto
> - 1.- Crear la base de datos Cium	[ver](database)
> - 2.- Correr el script para generar los schemas 
> - 3.- Instalar la libreria sudo apt-get install php5-mysqlnd o yum install php56w-mysqlnd para el retorno de tipos de datos de mysql
> - 4.- Una vez configurado el proyecto se inicia con `php artisan serve` y nos levanta un servidor: 
    * `http://127.0.0.1:8000` o su equivalente `http://localhost:8000`

### Cómo contribuir
---
Si deseas contribuir con este proyecto, por favor lee las siguientes guías que establece el [BID](https://www.iadb.org/es "BID"):

* [Guía para Publicar Herramientas Digitales](https://el-bid.github.io/guia-de-publicacion/ "Guía para Publicar") 
* [Guía para la Contribución de Código](https://github.com/EL-BID/Plantilla-de-repositorio/blob/master/CONTRIBUTING.md "Guía de Contribución de Código")

### Código de conducta 
---
Puedes ver el código de conducta para este proyecto en el siguiente archivo [CODEOFCONDUCT.md](https://github.com/EL-BID/Supervision-SISBEN-ML/blob/master/CODEOFCONDUCT.md).

### Autor/es
---
> - Secretaria de salud del estado de chiapas ISECH
> - Salud Mesoamerica 2015 SM2015
> - akira.redwolf@gmail.com 
> - h.cortes@gmail.com 
> * **[Eliecer Ramirez Esquinca](https://github.com/checherman "Github")**

### Información adicional
---
Para usar el sistema completo con una interfaz web y/o movil y no solo realizar las peticiones HTTP REST, debe tener configurado el siguiente proyecto:
* **[Cliente WEB CIUM](https://github.com/checherman/Cium-Cliente-Web "Proyecto WEB que complenta el sistema")**
* **[Cliente ANDROID CIUM](https://github.com/joramdeveloper/CIUM_movil "Proyecto WEB que complenta el sistema")**

### Licencia 
---
Los detalles de licencia para este código fuente se encuentran en el archivo  [LICENSE.md](https://github.com/checherman/Cium-APIRESTfull/blob/master/LICENSE.md)

## Limitación de responsabilidades

El BID no será responsable, bajo circunstancia alguna, de daño ni indemnización, moral o patrimonial; directo o indirecto; accesorio o especial; o por vía de consecuencia, previsto o imprevisto, que pudiese surgir:

i. Bajo cualquier teoría de responsabilidad, ya sea por contrato, infracción de derechos de propiedad intelectual, negligencia o bajo cualquier otra teoría; y/o

ii. A raíz del uso de la Herramienta Digital, incluyendo, pero sin limitación de potenciales defectos en la Herramienta Digital, o la pérdida o inexactitud de los datos de cualquier tipo. Lo anterior incluye los gastos o daños asociados a fallas de comunicación y/o fallas de funcionamiento de computadoras, vinculados con la utilización de la Herramienta Digital.
