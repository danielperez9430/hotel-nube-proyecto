# ☁️ Hotel Nube

> *Where your dreams will be as soft as the clouds* ✨

Web de reservas del hotel **Hotel Nube**, hecha en PHP, MySQL/MariaDB, Bootstrap y jQuery. Permite registrarse, iniciar sesión, consultar los tipos de habitación y gestionar las habitaciones desde un panel de administración.

## 📑 Índice

- [✨ Funcionalidades](#-funcionalidades)
- [🧰 Tecnologías](#-tecnologías)
- [🏗️ Arquitectura](#️-arquitectura)
- [✅ Requisitos](#-requisitos)
- [🚀 Puesta en marcha](#-puesta-en-marcha)
- [⚙️ Configuración (.env)](#️-configuración-env)
- [👥 Roles y usuarios](#-roles-y-usuarios)
- [📡 Endpoints internos](#-endpoints-internos)
- [🗂️ Estructura del proyecto](#️-estructura-del-proyecto)
- [📚 Documentación del código](#-documentación-del-código)
- [🔒 Seguridad](#-seguridad)
- [🤝 Contribuir](#-contribuir)

## ✨ Funcionalidades

- 🔐 **Registro e inicio de sesión** con contraseñas cifradas (`password_hash`).
- 🌍 **Selección de país, provincia y ciudad** en el registro, con datos de [hotelnube-api](https://github.com/danielperez9430/hotelnube-api).
- 📅 **Selector de fechas** de entrada y salida ([Litepicker](https://litepicker.com)).
- 🛏️ **Tipos de habitación** con descripción e imágenes.
- 🛠️ **Panel de administración** (solo rol `admin`): listado de habitaciones, detalle y borrado.
- ✉️ **Envío de correos** por SMTP con [PHPMailer](https://github.com/PHPMailer/PHPMailer).

## 🧰 Tecnologías

| Capa | Tecnología |
| ---- | ---------- |
| 🐘 Backend | PHP 8 + PDO |
| 🗄️ Base de datos | MariaDB / MySQL |
| 🎨 Frontend | Bootstrap 5.3, jQuery 3.7, Font Awesome 5, Litepicker 2 |
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

- 🐘 **PHP 8** con la extensión `pdo_mysql`
- 🗄️ **MariaDB** 10.x. El script SQL usa `GRANT … IDENTIFIED BY PASSWORD`, que **MySQL 8 ya no admite**; con MySQL 8 hay que crear los usuarios con `CREATE USER` antes de los `GRANT`.
- 🟢 **Node.js 18+** para ejecutar [hotelnube-api](https://github.com/danielperez9430/hotelnube-api)
- 📦 **Composer** (opcional: `vendor/` ya está en el repositorio)

## 🚀 Puesta en marcha

### 1️⃣ Clonar el proyecto

```bash
git clone https://github.com/danielperez9430/hotel-nube-proyecto.git
cd hotel-nube-proyecto
```

### 2️⃣ Crear la base de datos

```bash
mariadb -u root -p < config/db_hotel.sql
```

> [!WARNING]
> El script empieza con `DROP DATABASE IF EXISTS hotel`: **borra la base de datos `hotel` si ya existe**.

Crea las tablas, los datos de ejemplo (5 tipos y 30 habitaciones) y los usuarios de base de datos `admin`, `conexion` y `estandar`.

### 3️⃣ Configurar el `.env`

```bash
cp .env.example .env
```

Rellena las contraseñas y el resto de valores. Están todos explicados en [⚙️ Configuración](#️-configuración-env).

### 4️⃣ Arrancar hotelnube-api

```bash
git clone https://github.com/danielperez9430/hotelnube-api.git
cd hotelnube-api && npm ci && npm start
```

### 5️⃣ Arrancar la web

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
| `DB_USER_ADMIN` / `DB_PASSWORD_ADMIN` | — | Usuario con todos los permisos (registro y panel de administración) |
| `DB_USER_CONEXION` / `DB_PASSWORD_CONEXION` | — | Usuario de solo lectura (login) |
| `DB_USER_ESTANDAR` / `DB_PASSWORD_ESTANDAR` | — | Usuario de los visitantes |

### ✉️ Correo

| Variable | Por defecto | Descripción |
| -------- | ----------- | ----------- |
| `MAIL_HOST` | `smtp.gmail.com` | Servidor SMTP |
| `MAIL_PORT` | `587` | Puerto SMTP |
| `MAIL_ENCRYPTION` | `tls` | `tls` (STARTTLS) o `ssl` (SMTPS) |
| `MAIL_USERNAME` | — | Usuario SMTP |
| `MAIL_PASSWORD` | — | Contraseña SMTP. Con Gmail, una [contraseña de aplicación](https://support.google.com/accounts/answer/185833) |
| `MAIL_FROM_ADDRESS` | `MAIL_USERNAME` | Dirección del remitente |
| `MAIL_FROM_NAME` | `Hotel Nube` | Nombre del remitente |

### 🌍 API

| Variable | Por defecto | Descripción |
| -------- | ----------- | ----------- |
| `API_URL` | `http://localhost:8001` | URL base de hotelnube-api. La usan PHP y el JavaScript (a través de `window.API_URL`) |

> [!TIP]
> Si falta una variable obligatoria, el log de PHP lo indica con un mensaje del tipo `Falta la variable de configuración DB_USER_ADMIN (revisa el fichero .env)`.

## 👥 Roles y usuarios

Hay dos tipos de roles:

| Rol | Usuario de BD | Permisos en BD | Quién lo tiene |
| --- | ------------- | -------------- | -------------- |
| 🌐 `estandar` | `estandar` | `SELECT`, `INSERT` | Cualquier visitante sin sesión iniciada |
| 🔎 `conexion` | `conexion` | `SELECT` | Se usa para el login. Es el rol que reciben los usuarios nuevos al registrarse |
| 👑 `admin` | `admin` | Todos | Administradores: acceso a `adminPanel.php` |

Al iniciar sesión, la sesión adopta el rol del usuario guardado en la tabla `usuarios`.

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
| `api/getAllRooms.php` | `GET` | 👑 admin | Listado de todas las habitaciones |
| `api/removeRoom.php` | `POST` | 👑 admin | Borra una habitación (`roomId`) |

Las respuestas de login, registro y borrado tienen la forma `{"result": "success" | "fail", "code": n}`:

| `code` | Significado |
| ------ | ----------- |
| `0` | ✅ Correcto |
| `1` | ⚠️ Faltan datos en la petición |
| `2` | ❌ Datos incorrectos o la operación falló |
| `3` | 🚫 Sin permisos (HTTP 403) |

## 🗂️ Estructura del proyecto

```
hotel-nube-proyecto/
├── 📁 config/
│   └── db_hotel.sql          # Esquema, datos de ejemplo y usuarios de BD
├── 📁 docs/                  # Documentación generada con phpDocumentor
├── 📁 php/                   # Clases (autocargadas desde autoload.php)
│   ├── Config/               # Lectura del .env
│   ├── DB/                   # Acceso a la base de datos (PDO)
│   ├── Email/                # Envío de correos (PHPMailer)
│   ├── Habitacion/           # Modelos de habitación y servicios
│   ├── Session/              # Sesión y roles
│   └── Tools/                # Utilidades (contraseñas, validaciones)
├── 📁 public/                # Raíz pública del servidor web
│   ├── api/                  # Endpoints JSON
│   ├── src/                  # CSS, JS, imágenes e iconos
│   ├── index.php             # Página principal
│   ├── contact.php
│   └── adminPanel.php        # Panel de administración
├── 📁 views/                 # Cabecera y pie comunes
├── 📁 vendor/                # Dependencias de Composer (PHPMailer)
├── 📄 autoload.php           # Autocarga de las clases de php/
├── 📄 composer.json
└── 📄 .env.example           # Plantilla de configuración
```

## 📚 Documentación del código

La documentación de las clases está generada con [phpDocumentor](https://www.phpdoc.org) en [`docs/api/`](docs/api/). Abre `docs/api/index.html` en el navegador.

## 🔒 Seguridad

- 🔑 Contraseñas cifradas con `password_hash()` y comprobadas con `password_verify()`.
- 🧷 Consultas con sentencias preparadas de PDO.
- 🔄 El id de sesión se regenera al iniciar sesión.
- 👑 El panel y los endpoints de administración comprueban el rol en el servidor.
- 🤫 Las credenciales solo están en el `.env`, que no se sube al repositorio.
- 🌐 Todas las librerías del CDN se cargan con comprobación de integridad (SRI).

> [!CAUTION]
> Nunca subas el fichero `.env` al repositorio. Si alguna credencial llega a publicarse, cámbiala: aunque borres el fichero, sigue en el historial de git.

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
