<p align="center">
  <img src=".github/banner.png" alt="Hotel Nube — Where your dreams will be as soft as the clouds" width="100%">
</p>

<p align="center">
  <a href="https://github.com/danielperez9430/hotel-nube-proyecto/actions/workflows/ci.yml"><img src="https://github.com/danielperez9430/hotel-nube-proyecto/actions/workflows/ci.yml/badge.svg" alt="CI"></a>
</p>

# ☁️ Hotel Nube

> *Where your dreams will be as soft as the clouds* ✨

Web de reservas del hotel **Hotel Nube**, hecha en PHP, MySQL/MariaDB, Bootstrap y jQuery. Permite buscar habitaciones libres, reservarlas, registrarse y gestionar las habitaciones desde un panel de administración.

## 📑 Índice

- [✨ Funcionalidades](#-funcionalidades)
- [🧰 Tecnologías](#-tecnologías)
- [🏗️ Arquitectura](#️-arquitectura)
- [✅ Requisitos](#-requisitos)
- [🚀 Puesta en marcha](#-puesta-en-marcha)
- [⚙️ Configuración (.env)](#️-configuración-env)
- [🧪 Tests y calidad](#-tests-y-calidad)
- [👥 Roles y usuarios](#-roles-y-usuarios)
- [📡 Endpoints internos](#-endpoints-internos)
- [🗂️ Estructura del proyecto](#️-estructura-del-proyecto)
- [📚 Documentación del código](#-documentación-del-código)
- [🔒 Seguridad](#-seguridad)
- [🎨 Identidad visual](#-identidad-visual)
- [🤝 Contribuir](#-contribuir)

## ✨ Funcionalidades

- 🔐 **Registro e inicio de sesión** con contraseñas cifradas (`password_hash`).
- 🌍 **Selección de país, provincia y ciudad** en el registro, con datos de [hotelnube-api](https://github.com/danielperez9430/hotelnube-api).
- 📅 **Búsqueda de habitaciones libres** entre dos fechas ([Litepicker](https://litepicker.com)), con imágenes, servicios y precio total.
- 🛎️ **Reservas** con resumen, confirmación por correo y protección frente a reservas simultáneas.
- 🧳 **Mis reservas**: cada cliente consulta sus estancias desde el menú de usuario.
- 👤 **Menú de usuario** con nombre, email, logout y acceso al panel para administradores.
- ✉️ **Correos de bienvenida** por SMTP con [PHPMailer](https://github.com/PHPMailer/PHPMailer).
- 🛠️ **Panel de administración** para modificar y borrar habitaciones.

## 🧰 Tecnologías

| Capa | Tecnología |
| ---- | ---------- |
| 🐘 Backend | PHP 8 + PDO |
| 🗄️ Base de datos | MariaDB / MySQL |
| 🎨 Frontend | Bootstrap 5.3, jQuery 3.7, Font Awesome 5, Litepicker 2 (copias locales en `public/src/vendor`) |
| ✉️ Correo | PHPMailer 7 |
| 🌍 Datos y plantillas de correo | [hotelnube-api](https://github.com/danielperez9430/hotelnube-api) (Node.js) |

## 🏗️ Arquitectura

```
 Navegador ──► public/*.php ──► php/ (clases) ──► MariaDB
    │                                │
    │ fetch (países, provincias…)    │ file_get_contents (HTML del correo)
    └──────────► hotelnube-api ◄─────┘
                                     │
                                     └──► SMTP (PHPMailer)
```

## ✅ Requisitos

**Con Docker** 🐳: solo Docker con Docker Compose.

**Sin Docker**:
- 🐘 **PHP 8.3** o superior, con la extensión `pdo_mysql`
- 📦 **Composer**
- 🗄️ **MariaDB** 10.4+ o **MySQL** 8
- 🟢 **Node.js 18+** para ejecutar [hotelnube-api](https://github.com/danielperez9430/hotelnube-api)

## 🚀 Puesta en marcha

### 🐳 Opción rápida: Docker Compose

```bash
git clone https://github.com/danielperez9430/hotel-nube-proyecto.git
cd hotel-nube-proyecto
cp .env.example .env    # rellena DB_ROOT_PASSWORD y las DB_PASSWORD_*
docker compose up --build
```

| Servicio | URL |
| -------- | --- |
| 🏨 Web | <http://localhost:8000> |
| 🌍 hotelnube-api | <http://localhost:8001> |
| 📬 Mailpit (correos enviados) | <http://localhost:8025> |

La primera vez se crea la base de datos con los datos de ejemplo y los usuarios con las contraseñas del `.env`. Todos los correos se quedan en Mailpit, nunca salen a Internet. Para empezar de cero: `docker compose down -v`.

### 🛠️ Instalación manual

#### 1️⃣ Clonar el proyecto e instalar las dependencias

```bash
git clone https://github.com/danielperez9430/hotel-nube-proyecto.git
cd hotel-nube-proyecto
composer install          # en producción: composer install --no-dev
```

#### 2️⃣ Crear la base de datos

```bash
# Tablas y datos de ejemplo (5 tipos y 30 habitaciones)
mariadb -u root -p < config/db_hotel.sql

# Usuarios de base de datos: cambia antes las contraseñas CAMBIAR_* del fichero
mariadb -u root -p < config/db_usuarios.sql
```

> [!WARNING]
> `db_hotel.sql` empieza con `DROP DATABASE IF EXISTS hotel`: **borra la base de datos `hotel` si ya existe**.

#### 3️⃣ Configurar el `.env`

```bash
cp .env.example .env
```

Rellena las contraseñas y el resto de valores. Están todos explicados en [⚙️ Configuración](#️-configuración-env).

#### 4️⃣ Arrancar hotelnube-api

```bash
git clone https://github.com/danielperez9430/hotelnube-api.git
cd hotelnube-api && npm ci && npm start
```

#### 5️⃣ Arrancar la web

```bash
php -S localhost:8000 -t public
```

Abre 👉 <http://localhost:8000>. En producción, el *document root* del servidor web debe apuntar a la carpeta `public/`.

## ⚙️ Configuración (.env)

Toda la configuración está en el fichero `.env` de la raíz, que **no se sube al repositorio**. Las variables de entorno del sistema tienen prioridad sobre el `.env`, así que en producción también se pueden definir en el servidor.

### 🗄️ Base de datos

| Variable | Por defecto | Descripción |
| -------- | ----------- | ----------- |
| `DB_HOST` | `localhost` | Servidor de la base de datos |
| `DB_PORT` | `3306` | Puerto |
| `DB_NAME` | `hotel` | Nombre de la base de datos |
| `DB_USER_ADMIN` / `DB_PASSWORD_ADMIN` | — | Usuario de los administradores (`hotel_admin`) |
| `DB_USER_CONEXION` / `DB_PASSWORD_CONEXION` | — | Usuario del login (`hotel_conexion`) |
| `DB_USER_ESTANDAR` / `DB_PASSWORD_ESTANDAR` | — | Usuario de visitantes y clientes (`hotel_estandar`) |

### ✉️ Correo

| Variable | Por defecto | Descripción |
| -------- | ----------- | ----------- |
| `MAIL_HOST` | — | Servidor SMTP. Si está vacío no se envían correos (se anota en el log) |
| `MAIL_PORT` | `587` | Puerto SMTP |
| `MAIL_ENCRYPTION` | `tls` | `tls` (STARTTLS), `ssl` (SMTPS) o vacío (sin cifrado, para servidores de pruebas) |
| `MAIL_USERNAME` | — | Usuario SMTP. Si está vacío no hay autenticación |
| `MAIL_PASSWORD` | — | Contraseña SMTP. Con Gmail, una [contraseña de aplicación](https://support.google.com/accounts/answer/185833) |
| `MAIL_FROM_ADDRESS` | `MAIL_USERNAME` | Dirección del remitente |
| `MAIL_FROM_NAME` | `Hotel Nube` | Nombre del remitente |

### 🌐 Web

| Variable | Por defecto | Descripción |
| -------- | ----------- | ----------- |
| `SITE_URL` | `http://localhost:8000` | URL pública de la web. Se usa para las imágenes de los correos |
| `TIMEZONE` | `Europe/Madrid` | Zona horaria del hotel, para validar las fechas de las reservas |

### 🌍 API

| Variable | Por defecto | Descripción |
| -------- | ----------- | ----------- |
| `API_URL` | `http://localhost:8001` | URL base de hotelnube-api tal como la ve el navegador (`window.API_URL`) |
| `API_INTERNAL_URL` | `API_URL` | URL de la API desde el servidor PHP, si es distinta (en Docker, `http://api:8001`) |

### 🐳 Docker Compose

| Variable | Descripción |
| -------- | ----------- |
| `DB_ROOT_PASSWORD` | Contraseña de root del contenedor de MariaDB |

Docker Compose fija el resto de valores de conexión (`DB_HOST`, `MAIL_HOST`, URLs…) para que los contenedores se encuentren entre sí.

> [!TIP]
> Si falta una variable obligatoria, el log de PHP lo indica con un mensaje del tipo `Falta la variable de configuración DB_USER_ADMIN (revisa el fichero .env)`.

## 🧪 Tests y calidad

```bash
composer lint       # sintaxis de todos los .php
composer analyse    # análisis estático con PHPStan (nivel 5)
composer test       # tests con PHPUnit
npm ci && npm run lint   # ESLint para el JavaScript de public/src/js
```

Los **tests de integración** se ejecutan contra una base de datos real creada con `db_hotel.sql` y `db_usuarios.sql`:

```bash
DB_TESTS=1 DB_HOST=127.0.0.1 \
DB_USER_ADMIN=hotel_admin DB_PASSWORD_ADMIN=... \
DB_USER_CONEXION=hotel_conexion DB_PASSWORD_CONEXION=... \
DB_USER_ESTANDAR=hotel_estandar DB_PASSWORD_ESTANDAR=... \
composer test
```

> [!WARNING]
> Los tests de integración **borran los usuarios y las reservas** de esa base de datos. Usa una base de datos de pruebas.

GitHub Actions ejecuta todo lo anterior en cada push y pull request (PHP 8.3 y 8.4, con MariaDB), y además construye la imagen Docker de la web.

## 👥 Roles y usuarios

Cada rol de la aplicación usa su propio usuario de base de datos, con los permisos mínimos y solo sobre la base de datos `hotel`:

| Rol | Usuario de BD | Permisos en BD | Quién lo tiene |
| --- | ------------- | -------------- | -------------- |
| 🌐 `estandar` | `hotel_estandar` | Lee el catálogo, se registra y reserva | Visitantes sin sesión y clientes registrados |
| 🔎 `conexion` | `hotel_conexion` | Solo lee `usuarios` y `roles` | Proceso de login |
| 👑 `admin` | `hotel_admin` | `SELECT`, `INSERT`, `UPDATE`, `DELETE` (sin cambiar la estructura) | Administradores: acceso a `adminPanel.php` |

Al registrarse, los usuarios reciben el rol `estandar`. Al iniciar sesión, la sesión adopta el rol guardado en la tabla `usuarios`.

**Crear un administrador:** regístrate desde la web y luego cambia su rol en la base de datos.

```sql
UPDATE hotel.usuarios SET rol_usuario = 1 WHERE email = 'tu@email.com';
```

## 📡 Endpoints internos

Todos están en `public/api/` y responden JSON.

| Ruta | Método | Requiere | Descripción |
| ---- | ------ | -------- | ----------- |
| `api/login.php` | `POST` | — | Inicia sesión (`email`, `password`) |
| `api/logout.php` | `POST` | — | Cierra la sesión |
| `api/register.php` | `POST` | — | Registra un usuario |
| `api/room_type.php` | `GET` | — | Tipos de habitación con su descripción |
| `api/getRoomTypes.php` | `GET` | — | Nombres de los tipos de habitación |
| `api/searchRooms.php` | `GET` | — | Habitaciones libres entre `checkin` y `checkout` (`DD-MM-YYYY` o `YYYY-MM-DD`) |
| `api/bookRoom.php` | `POST` | 👤 sesión | Reserva una habitación (`roomId`, `checkin`, `checkout`). `409` si ya está ocupada |
| `api/myBookings.php` | `GET` | 👤 sesión | Reservas del usuario |
| `api/getAllRooms.php` | `GET` | 👑 admin | Listado de todas las habitaciones |
| `api/updateRoom.php` | `POST` | 👑 admin | Modifica una habitación (`roomId`, `tipo`, `m2`, `ventana`, `internet`, `limpieza`, `precio`) |
| `api/removeRoom.php` | `POST` | 👑 admin | Borra una habitación (`roomId`) |

Las respuestas de login, registro y borrado tienen la forma `{"result": "success" | "fail", "code": n}`:

| `code` | Significado |
| ------ | ----------- |
| `0` | ✅ Correcto |
| `1` | ⚠️ Faltan datos en la petición |
| `2` | ❌ Datos incorrectos o la operación falló |
| `3` | 🚫 Sin permisos (HTTP 403) |
| `4` | 🛡️ Token CSRF ausente o incorrecto (HTTP 403) |
| `5` | ⏳ Demasiados intentos de login fallidos (HTTP 429) |
| `6` | 👤 Hace falta iniciar sesión (HTTP 401) |

Las peticiones `POST` deben llevar la cabecera `X-CSRF-Token` con el token de la página (`<meta name="csrf-token">`). `src/js/csrf.js` la añade automáticamente a todas las peticiones de jQuery.

## 🗂️ Estructura del proyecto

```
hotel-nube-proyecto/
├── 📁 .github/               # CI (workflows) y banner del README
├── 📁 config/
│   ├── db_hotel.sql          # Esquema y datos de ejemplo
│   └── db_usuarios.sql       # Usuarios de BD con permisos mínimos
├── 📁 docker/                # Imagen de la web y script de usuarios de MariaDB
├── 📁 docs/
│   ├── api/                  # Documentación generada con phpDocumentor
│   └── recursos/             # Fuentes y scripts de la documentación (sin CDNs)
├── 📁 php/                   # Clases (autocargadas desde autoload.php)
│   ├── Config/               # Lectura del .env
│   ├── DB/                   # Acceso a la base de datos (PDO)
│   ├── Email/                # Envío de correos (PHPMailer)
│   ├── Habitacion/           # Modelos de habitación y servicios
│   ├── Session/              # Sesión y roles
│   └── Tools/                # Utilidades (contraseñas, validaciones)
├── 📁 public/                # Raíz pública del servidor web
│   ├── api/                  # Endpoints JSON
│   ├── src/                  # CSS, JS, imágenes, iconos y logo
│   │   └── vendor/           # Bootstrap, jQuery, Litepicker y Font Awesome
│   ├── index.php             # Página principal y búsqueda de habitaciones
│   ├── contact.php           # Contacto
│   ├── privacy.php           # Privacidad, cookies y cancelación
│   ├── adminPanel.php        # Panel de administración
│   └── favicon.svg / .ico
├── 📁 scripts/               # generar-docs.sh
├── 📁 tests/                 # Tests unitarios y de integración (PHPUnit)
├── 📁 views/                 # Cabecera, pie y diálogo de login comunes
├── 📄 autoload.php           # Autocarga de las clases de php/
├── 📄 composer.json / .lock  # Dependencias PHP (vendor/ no se versiona)
├── 📄 docker-compose.yml     # Web + MariaDB + API + Mailpit
└── 📄 .env.example           # Plantilla de configuración
```

## 📚 Documentación del código

La documentación de las clases está generada con [phpDocumentor](https://www.phpdoc.org) en [`docs/api/`](docs/api/). Abre `docs/api/index.html` en el navegador.

Para regenerarla después de cambiar el código:

```bash
curl -LO https://phpdoc.org/phpDocumentor.phar
scripts/generar-docs.sh
```

El script sustituye las fuentes y los scripts que la plantilla carga de CDNs por las copias de `docs/recursos`.

## 🔒 Seguridad

- 🔑 Contraseñas cifradas con `password_hash()` y comprobadas con `password_verify()`.
- 🧷 Consultas con sentencias preparadas de PDO.
- 🗝️ Usuarios de base de datos con permisos mínimos, limitados a la base de datos `hotel`.
- 🔄 El id de sesión se regenera al iniciar sesión, y la cookie es `HttpOnly` y `SameSite=Lax` (y `Secure` con HTTPS).
- 🛡️ Protección CSRF en todas las peticiones `POST`.
- ⏳ Límite de intentos de login: 5 fallos por email o 20 por IP en 15 minutos. Además, el tiempo de respuesta es el mismo exista o no el usuario.
- 👑 El panel y los endpoints de administración comprueban el rol en el servidor.
- 🤫 Las credenciales solo están en el `.env`, que no se sube al repositorio.
- 🏠 Sin recursos de terceros: librerías, fuentes e iconos se sirven desde `public/src/vendor`, así que ninguna visita llega a un CDN.

> [!CAUTION]
> Nunca subas el fichero `.env` al repositorio. Si alguna credencial llega a publicarse, cámbiala: aunque borres el fichero, sigue en el historial de git.

## 🎨 Identidad visual

Estética de hotel de lujo art déco: fondos oscuros, detalles dorados y tipografía serif.

| Color | Hex | Uso |
| ----- | --- | --- |
| ⬛ Carbón | `#1F2929` | Fondo de la marca, barra de navegación y pie |
| 🌑 Noche | `#111717` | Fondos profundos y degradados |
| 🟨 Oro | `#DCB004` | Logo, iconos y detalles |
| 🟫 Oro suave | `#C9A44C` | Textos secundarios y líneas finas |
| 🤍 Marfil | `#F5EFE0` | Textos sobre fondo oscuro |

| Recurso | Fichero |
| ------- | ------- |
| Logo vectorial | `public/src/logo/hotel_nube.svg` |
| Logo PNG (correos) | `public/src/logo/hotel_nube.png` |
| Favicon | `public/favicon.svg`, `public/favicon.ico`, `public/apple-touch-icon.png` |
| Banner del README | `.github/banner.png` |

Tipografías del banner: *Cormorant Garamond* y *Montserrat* (licencia OFL).

## 🤝 Contribuir

1. 🌿 Crea una rama con un prefijo descriptivo: `feat/`, `hotfix/`, `chore/`, `docs/`, `refactor/`…
2. ✍️ Escribe los commits siguiendo [Conventional Commits](https://www.conventionalcommits.org/es/v1.0.0/):
   ```
   feat(config): añade la lectura de configuración desde .env
   fix: corrige la sesión, el login y el panel de administración
   ```
3. 🔀 Abre un Pull Request contra `master` explicando el cambio y cómo se ha probado.

---

© 2021 Hotel Nube · Daniel Pérez Fernández
