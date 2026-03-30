# Proyecto Drupal - Exito

## Descripción

Proyecto Drupal preparado como entorno reproducible para evaluación técnica.

El repositorio incluye:

* código fuente del proyecto
* configuración de entorno con DDEV
* base de datos exportada
* archivos del sitio

Esto permite levantar el sitio completamente funcional, con usuarios, eventos y contenido ya configurado, sin necesidad de ejecutar la instalación manual de Drupal.

---

## Requisitos

* Git
* Docker Desktop
* DDEV
* Composer

---

## Puesta en marcha

Clonar el repositorio:

```bash
git clone https://github.com/GoLuisGo/exito.git
cd exito
```
Instalar drupal11:
```bash
ddev config --project-type=drupal --docroot=web
```

Levantar el entorno:

```bash
ddev start
```

Instalar dependencias:

```bash
ddev composer install
```

Importar la base de datos:

```bash
ddev import-db --src=.ddev/db/exito.sql.gz
```

Abrir el sitio:

```bash
ddev launch
```

---

## Resultado esperado

Al ejecutar los pasos anteriores, el sitio debe cargar directamente con:

* Drupal ya instalado
* usuarios previamente creados
* eventos y contenido disponibles
* entorno completamente funcional

No es necesario ejecutar `site:install`.

---

## Credenciales de acceso (entorno de demostración)

Usuario administrador:

* Usuario: [luisgomezarango@gmail.com](mailto:luisgomezarango@gmail.com)
* Contraseña: 123

Usuario de prueba:

* Usuario: [prueba@exito.com](mailto:prueba@exito.com)
* Contraseña: 123

Nota: Estas credenciales se incluyen únicamente con fines de evaluación técnica.

## Decisiones técnicas

* Uso de DDEV para garantizar un entorno reproducible y estandarizado.
* Gestión de dependencias mediante Composer, evitando versionar `vendor`.
* Inclusión de la base de datos para conservar el estado real del sitio (usuarios, eventos y configuración).
* Inclusión de archivos del sitio para mantener la integridad del contenido.
* Separación entre código y estado del sistema, facilitando portabilidad y despliegue controlado.

---

## Comandos útiles

Validar estado del entorno:

```bash
ddev list
ddev describe
```

Reiniciar entorno:

```bash
ddev restart
```

Detener entorno:

```bash
ddev stop
```

Acceder a Drupal por consola:

```bash
ddev drush
```

---

## Consideraciones

* Este proyecto está orientado a demostración y evaluación técnica.
* Incluye base de datos y archivos únicamente para facilitar la reproducción del entorno.
* No está diseñado para uso productivo en su estado actual.

---
