<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# nasa-api-wrapper

Este proyecto es una API que consume y procesa datos del proyecto DONKI de la NASA.

## Requisitos

-   PHP 8.4.1 o superior
-   Composer 2.8.3
-   Laravel 5.10

## Instalación

1. Clonar el repositorio:

```

git clone [https://github.com/jwoodleybolivard/nasa-api-wrapper.git](https://github.com/jwoodleybolivard/nasa-api-wrapper.git)

cd nasa-donki-api
```

2. Instalar las dependencias:

```plaintext
composer install
```

3. Instalar Dependencias de NPM:

```plaintext
npm install
```

4. Copiar el archivo de configuración:

```
cp .env.example .env
```

5. Configurar la API key de la NASA en el archivo .env:

```
NASA_API_KEY=your_api_key_here
```

6. Generar la clave de la aplicación:

```
php artisan key:generate
```

7. Iniciar el servidor de desarrollo:

```
php artisan serve
```

## Uso

La API expone las siguientes rutas:

-   GET /api/instruments: Retorna todos los instrumentos usados por DONKI.
-   GET /api/activity-ids: Retorna todas las IDs de actividades existentes.
-   GET /api/instrument-usage-percentages: Retorna el porcentaje de uso de cada instrumento.
-   POST /api/instrument-activity-percentage: Retorna el porcentaje de uso de las actividades para un instrumento específico.

## Colección de Postman

Puedes importar la colección de Postman desde el archivo `NASA_DONKI_API.postman_collection.json` incluido en este repositorio.

## Despliegue

Este proyecto se desplegó en vercel

## Arquitectura

Este proyecto utiliza una arquitectura hexagonal (también conocida como puertos y adaptadores) para mantener una clara separación de responsabilidades y facilitar la prueba y el mantenimiento del código.

## Principios SOLID

El código sigue los principios SOLID:

-   Single Responsibility: Cada clase tiene una única responsabilidad.
-   Open/Closed: Las clases están abiertas para la extensión pero cerradas para la modificación.
-   Liskov Substitution: Las implementaciones pueden ser sustituidas por sus abstracciones.
-   Interface Segregation: Se utilizan interfaces específicas en lugar de una interfaz general.
-   Dependency Inversion: Las dependencias se invierten, dependiendo de abstracciones en lugar de implementaciones concretas.

## Contribución

Las contribuciones son bienvenidas. Por favor, abre un issue para discutir los cambios propuestos antes de hacer un pull request.

## Licencia

Este proyecto no está licenciado

#### Desarrollado con ❤️ por JEAN Woodley BOLIVARD
