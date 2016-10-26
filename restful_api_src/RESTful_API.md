# API Viajes Usaria
Todas deben incluir token. Este documento asume que se trabaja con Angular 1.
## Ejemplo de uso de $http para llamar un servicio
````javascript
var deferred = $q.defer();
$http({
    url: this.API_PATH, // url del servicio que se requiere
    method: "GET", //Puede ser GET, POST, PUT o DELETE
    headers:{
        "Accept": "application/json",
        "Authorization": localStorage.getItem( "usaria_token" )
    },
    data: {
        // en caso de ser POST PUT o DELETE
    },
    params:{
        // en caso de ser GET
    }
})
.then(
    function( respuesta ){
        return deferred.resolve( respuesta.data );
    },
    function( error ){
        return deferred.reject( error );
    }
);
return deferred.promise;
````

## Seleccion de vuelos y hoteles
URL:
* viajes.usaria.mx/api/solicitudes/_{id_solicitud}_/vuelos_seleccionados/
    ### Métodos habilitados
    #### POST
    ##### Ejemplo parcial
    ````javascript
    url: "viajes.usaria.mx/api/solicitudes/" + id_solicitud + "/vuelos_seleccionados/",
    method: "POST",
    headers:{
        "Accept": "application/json",
        "Authorization": localStorage.getItem( "usaria_token" )
    },
    data:{
        vuelo_seleccionado: {} // objeto con los datos de la tabla opcion_vuelo
    }
    ````
    Inserta a la tabla _vuelo_seleccionado_ para saber que vuelo es el que el viajero quiere comprar


<br>
<br>
<br>
<br>
<br>

Aquellos métodos que no esten en _métodos habilidatos_ para un URL espcífico deberán crearse para extender la funcionalidad del API.




































## 1. Todas las solicitudes
Url: /solicitudes/

### GET
Se obtienen todas las solicitudes
````javascript
$http({
    url: "www.viajes.usaria.mx/solicitudes",
    method: "GET", 
    params:{
        usaria_token: token
    }
})
````

## 2. Solicitudes en proceso
/solicitudes/en_proceso/
### GET
Se obtienen todas las solicitudes que se encuentren en el paso de cotizacion y/o seleccion
````javascript
$http({
    url: "www.viajes.usaria.mx/solicitudes/en_proceso/",
    method: "GET", 
    params:{
        usaria_token: token
    }
})
````

## 3. Solicitud individual
/solicitudes/{id_solicitud}
### GET
Se obtiene la informacion de la solicitud especificada
````javascript
$http({
    url: "www.viajes.usaria.mx/solicitudes/{id_solicitud}",
    method: "GET", 
    params:{
        usaria_token: token
    }
})
````

## 4. Hoteles
/api/solicitudes/{id_solicitud}/opciones_hoteles
### POST
Se inserta una opcion de hotel
````javascript
$http({
    url: "www.viajes.usaria.mx/api/solicitudes/{id_solicitud}/opciones_hoteles",
    method: "POST",
    params:{
        usaria_hoten: token,
        opcion_hotel: {opcionHotelObj}
    }
})
````







## /solicitudes/
### POST
* Inserta una nueva solicitud
    ````javascript
    /* Ejemplo angular 1 */
    $http({
        url: "http://www.viajes.usaria.mx/api/solicitudes/",
        method: "POST",
        headers:{
            "Accept": "application/json",
            "Authorization": "Bearer [usaria_token]"
        },
        data: {
            solicitud: [solicitud_object] // objeto json
        }
    })
    ````
## /solicitudes/pendientes
### GET
* Obtiene aquellas solicitudes que aun tienen un paso pendiente por completar
    ````javascript
     /* Ejemplo angular 1 */
    $http({
        url: "http://www.viajes.usaria.mx/api/solicitudes/pendientes/",
        method: "GET",
        headers:{
            "Accept": "application/json",
            "Authorization": "Bearer [usaria_token]"
        }
    })
    ````