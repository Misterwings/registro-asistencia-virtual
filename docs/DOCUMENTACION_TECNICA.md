# Documentación técnica

## 1. Identificación del proyecto

**Nombre funcional:** Sistema de Registro de Asistencia (`Attendance Record`).

**Propósito:** permitir que un organizador cree eventos, publique un enlace o código QR, recopile datos de asistentes junto con una firma digital y descargue reportes institucionales.

**Idioma de la interfaz:** español.

**Estado de la documentación:** elaborado a partir del código fuente y la configuración disponible en el repositorio.

**Punto de entrada del código:** `src/`.

## 2. Alcance funcional

| Área | Capacidades implementadas |
| --- | --- |
| Página pública | Landing page en `/` con acceso al panel cuando existe una sesión autenticada. |
| Eventos | Alta, consulta, edición y eliminación de eventos propios. Cada evento tiene un slug público único. |
| Acceso público | Formulario en `/evento/{slug}` para registrar nombres, identificación, cargo, sede y firma. |
| Código QR | Generación de un SVG que apunta al enlace público del evento. |
| Vencimiento | Cierre opcional del enlace público por fecha, inclusive durante todo el día de vencimiento en la zona horaria de Bogotá. |
| Catálogos | Administración de cargos, sedes y motivos, con activación o desactivación lógica mediante `is_active`. |
| Asistencias | Consulta de registros asociados a los eventos del administrador autenticado, visualización de firmas y eliminación masiva. |
| Reportes | Descarga filtrada por evento y una o varias sedes en formatos XLSX o PDF. |
| Adjuntos | Carga opcional de PDF o imágenes para mostrarlos desde el formulario público. |
| Usuarios | Autenticación de Filament y control de acceso al panel mediante `users.is_admin`. No existe un CRUD de usuarios en el panel. |

## 3. Arquitectura general

La aplicación utiliza una arquitectura web monolítica basada en Laravel:

```text
Navegador del administrador       Navegador del asistente
            |                                  |
            | HTTPS/HTTP                       | HTTPS/HTTP
            v                                  v
        Nginx :80 ------------------------> Laravel
            |                                  |
            +------------ PHP-FPM :9000 -------+
                                               |
                                               v
                                        MySQL / SQLite
                                               |
                                               v
                                  storage/app/public (adjuntos)
```

En el entorno Docker local, Nginx se publica en `localhost:8080`, MySQL en `localhost:3307` y phpMyAdmin en `localhost:8081`. La comunicación entre contenedores usa el nombre del servicio; por ello Laravel debe usar `DB_HOST=db` y `DB_PORT=3306`, no el puerto publicado `3307`.

En producción con Coolify, Nginx y PHP-FPM permanecen internos y Coolify se encarga de exponer el servicio web. La base de datos y los archivos públicos se conservan en volúmenes persistentes.

## 4. Stack tecnológico

### Backend

- PHP requerido por Composer: `^8.3`.
- Imágenes Docker del proyecto: PHP `8.4-FPM`.
- Laravel: `^13.7`.
- Filament: `^5.0`.
- Eloquent ORM y migraciones Laravel.
- Autenticación de sesión con el proveedor Eloquent.
- `bacon/bacon-qr-code` para generar códigos QR SVG.
- FPDF para el reporte PDF.
- DOMPDF y PhpSpreadsheet están declarados como dependencias, aunque el flujo actual de reportes usa FPDF y el escritor interno `App\Services\XlsxWriter`.
- `laravel/tinker` para tareas operativas de usuarios.

### Frontend

- Vite.
- Tailwind CSS v4 mediante `@tailwindcss/vite`.
- `signature_pad` `^5.1.3` para firmas dibujadas.
- Fuentes cargadas mediante la integración de Bunny CDN de Laravel Vite:
  - Cuerpo: Instrument Sans.
  - Títulos: DM Serif Display.
- Dos entradas CSS independientes:
  - `src/resources/css/app.css` para páginas públicas.
  - `src/resources/css/filament/admin/theme.css` para Filament.

### Infraestructura

- Docker y Docker Compose.
- Nginx Alpine como servidor web.
- MySQL `8.4` en Compose.
- phpMyAdmin `5.2-apache` únicamente en el Compose local.
- Imagen productiva multietapa definida en `Dockerfile.prod`.

## 5. Estructura del repositorio

```text
.
├── docker-compose.yml              # Entorno local
├── docker-compose.coolify.yml      # Entorno de despliegue en Coolify
├── Dockerfile.prod                 # Build productivo PHP + Vite + Nginx
├── docker/
│   ├── nginx/default.conf          # Front controller y FastCGI
│   ├── node/Dockerfile             # Imagen auxiliar de Node/pnpm
│   └── php/
│       ├── Dockerfile               # PHP-FPM local
│       ├── entrypoint.prod.sh       # Migraciones productivas opcionales
│       └── php.ini                  # Límites de PHP
├── docs/                            # Documentación del proyecto
└── src/
    ├── app/
    │   ├── Filament/Resources/      # Panel administrativo
    │   ├── Http/Controllers/        # Controladores web
    │   ├── Models/                  # Modelos Eloquent
    │   ├── Providers/               # Configuración de Laravel y Filament
    │   └── Services/                # Reglas de negocio y exportaciones
    ├── database/
    │   ├── migrations/              # Esquema y evoluciones de base de datos
    │   ├── seeders/                 # Datos iniciales y alta de usuarios
    │   └── factories/
    ├── resources/
    │   ├── css/                     # Tema público y tema Filament
    │   ├── js/app.js                # Firma y comportamiento del frontend
    │   └── views/                   # Blade público, Filament y reportes
    ├── routes/web.php               # Rutas web de la aplicación
    ├── tests/                       # Suites Unit y Feature
    ├── composer.json
    ├── package.json
    └── .env.example
```

## 6. Componentes de aplicación

### 6.1 Controladores

| Clase | Responsabilidad |
| --- | --- |
| `PublicAttendanceController` | Resolver el evento por slug, comprobar disponibilidad, mostrar el formulario y almacenar asistencias. |
| `ReportController` | Mostrar el formulario de exportación y validar la propiedad del evento antes de delegar la generación del archivo. |
| `Controller` | Clase base de controladores Laravel. |

### 6.2 Servicios

| Clase | Responsabilidad |
| --- | --- |
| `EventService` | Generar slugs, localizar eventos, evaluar vencimiento y resolver adjuntos públicos. |
| `AttendanceService` | Normalizar nombres e identificaciones, detectar duplicados, crear asistencias y consultar registros por evento o administrador. |
| `QrCodeService` | Construir la URL pública y generar su representación SVG de 300 px. |
| `ReportService` | Generar exportaciones CSV internas, XLSX y PDF. Solo XLSX y PDF están conectados a una ruta web. |
| `XlsxWriter` | Crear el paquete XLSX con `ZipArchive`, XML de hoja, cadenas compartidas y estilos básicos. |

### 6.3 Panel Filament

El panel se configura en `src/app/Providers/Filament/AdminPanelProvider.php`:

- Identificador: `admin`.
- Ruta base: `/admin`.
- Marca visible: `Asistencia`.
- Login de Filament habilitado.
- Color primario: rojo.
- Recursos descubiertos automáticamente bajo `src/app/Filament/Resources`.
- Middleware de sesión, protección CSRF, autenticación y sustitución de bindings.

Los recursos visibles son:

| Recurso | Operaciones disponibles |
| --- | --- |
| Eventos | Listar, crear, ver, editar y eliminar. La consulta se limita a eventos dirigidos por el usuario autenticado. |
| Asistencias | Listar, buscar, ver detalles/firma y eliminar. No se expone creación desde el recurso. Solo se muestran asistencias de eventos propios. |
| Cargos | Listar, crear, editar, activar/desactivar y eliminar. |
| Sedes | Listar, crear, editar, activar/desactivar y eliminar. |
| Motivos | Listar, crear, editar, activar/desactivar y eliminar. El nombre es único. |

## 7. Rutas web

| Método | Ruta | Nombre | Middleware | Comportamiento |
| --- | --- | --- | --- | --- |
| `GET` | `/` | - | Público | Landing page. |
| `GET` | `/evento/{slug}` | `event.show` | Público | Formulario público del evento o respuesta `410` si está vencido. |
| `POST` | `/evento/{slug}` | `event.register` | Público + CSRF | Valida y registra una asistencia. |
| `GET` | `/admin/reportes/asistencias` | `attendances.export` | `auth` | Muestra el formulario si faltan parámetros o descarga XLSX/PDF si están completos. |
| `GET` | `/admin/reportes` | `reports.form` | `auth` | Usa el mismo método de exportación y abre el formulario cuando no hay parámetros. |
| `GET` | `/up` | Ruta de salud Laravel | Framework | Comprueba que la aplicación está disponible. |

El panel Filament agrega sus propias rutas bajo `/admin`, incluyendo login y los recursos descubiertos. Sus URLs concretas dependen de los slugs de Filament, normalmente `/admin/events`, `/admin/attendances`, `/admin/positions`, `/admin/headquarters` y `/admin/reasons`.

No hay una ruta web declarada para `ReportController::exportCsv` ni para `ReportService::exportCsv`.

## 8. Autenticación y autorización

### 8.1 Acceso al panel

El modelo `User` implementa `FilamentUser` y autoriza el panel únicamente cuando:

```php
$user->is_admin === true
```

La autorización no se basa en una tabla de roles adicional. Todos los usuarios administradores comparten el mismo conjunto de recursos.

### 8.2 Aislamiento de información

- La lista de eventos Filament aplica `where('directed_by_id', auth()->id())`.
- La lista de asistencias solo incluye registros cuyo evento pertenece al administrador autenticado.
- El formulario de reportes solo carga los eventos dirigidos por el usuario autenticado.
- Antes de exportar, `ReportController` vuelve a consultar el evento con el propietario autenticado y utiliza `firstOrFail()`.
- Las rutas de reportes tienen middleware `auth`, pero no declaran un middleware explícito de `is_admin`. En la operación normal el acceso se realiza desde Filament, cuyo panel sí aplica `canAccessPanel`. Si se habilita otra autenticación para usuarios no administradores, conviene añadir una autorización específica a estas rutas.

### 8.3 Gestión de usuarios

No hay registro público, CRUD de usuarios ni recuperación de contraseña personalizada dentro de la aplicación. Las cuentas se gestionan mediante seeders y Artisan. Los detalles operativos están en la sección de usuarios del [Manual de usuario](MANUAL_DE_USUARIO.md).

## 9. Flujos de negocio

### 9.1 Creación de un evento

1. Un administrador abre Eventos > Crear evento.
2. El formulario recibe fecha, tema, horario, lugar, motivo, cargo de quien dirige y un adjunto opcional.
3. `directed_by_id` se fuerza al usuario autenticado, aunque el formulario contiene un campo oculto.
4. `EventService::generateSlug()` crea un slug a partir del tema y la fecha/hora actual en formato `YmdHi`.
5. Si el slug ya existe, se añade `-1`, `-2`, etc., hasta obtener uno único.
6. El slug no se regenera al editar el tema. El enlace público y el QR originales siguen siendo válidos.

### 9.2 Consulta de un enlace público

```text
GET /evento/{slug}
        |
        +-- Buscar evento por slug
        |       |
        |       +-- No existe -> 404
        |
        +-- Evaluar vencimiento
        |       |
        |       +-- No disponible -> vista de enlace vencido + HTTP 410
        |
        +-- Cargar cargos y sedes con is_active = true
        +-- Resolver adjunto y si puede visualizarse
        +-- Renderizar formulario
```

Los adjuntos aceptados por el formulario son PDF, JPEG, PNG, GIF y WEBP, con un límite de 10 MB en Filament. El enlace de almacenamiento es público cuando se utiliza el disco `public`.

### 9.3 Registro de asistencia

El `POST /evento/{slug}` realiza, en orden, estas operaciones:

1. Vuelve a localizar el evento y verifica que el enlace siga disponible.
2. Normaliza la identificación antes de validar:
   - elimina espacios, puntos y guiones que estén entre dígitos;
   - conserva el valor como texto para no perder ceros iniciales;
   - posteriormente exige que solo contenga dígitos.
3. Valida:
   - nombres obligatorios, texto, máximo 255 caracteres;
   - apellidos obligatorios, texto, máximo 255 caracteres;
   - identificación obligatoria, máximo 50 caracteres y solo números después de normalizar;
   - cargo activo existente;
   - sede activa existente;
   - firma como cadena requerida.
4. Comprueba si la misma identificación normalizada ya está registrada en el evento.
5. Crea el registro con nombres y apellidos normalizados, `full_name`, `event_id` y `registered_at`.
6. Si se produce una colisión de unicidad concurrente en la base de datos, responde con el mismo mensaje de duplicado.
7. Redirige al formulario con el mensaje `¡Asistencia registrada exitosamente!`.

La restricción de base de datos `UNIQUE(event_id, id_number)` garantiza que la identificación solo pueda registrarse una vez por evento. La misma persona sí puede registrarse en eventos diferentes.

### 9.4 Vencimiento del enlace

- `has_expiration = false`: el enlace no vence automáticamente.
- `has_expiration = true` y sin fecha: el enlace se considera no disponible.
- `has_expiration = true` con fecha: se permite el acceso mientras la fecha actual en `America/Bogota` sea menor o igual a `expiration_date`.
- El mismo control se aplica al `GET` y al `POST`.
- Un evento vencido responde HTTP `410 Gone` y no muestra el formulario.
- La fecha del evento (`date`) no cierra por sí sola el enlace; el cierre depende del campo de vencimiento.

### 9.5 Reportes

1. El usuario autenticado entra a `/admin/reportes` o pulsa Descargar Reporte desde Asistencias.
2. Elige uno de sus eventos.
3. Opcionalmente marca una o varias sedes.
4. Si no marca ninguna sede, se incluyen todas las asistencias del evento.
5. Elige XLSX o PDF.
6. El controlador valida el evento, formato y sedes, y vuelve a comprobar la propiedad del evento.
7. `ReportService` filtra, ordena por `registered_at` ascendente y genera la descarga.

## 10. Modelo de datos

### 10.1 Diagrama lógico

```text
users (1) -------------------- (N) events
                                  |
                                  | event_id
                                  v
                            attendances (N)
                              |       |
                    position_id       headquarter_id
                              |       |
                              v       v
                         positions  headquarters

reasons es un catálogo independiente usado como datalist para reason.
```

### 10.2 Tabla `users`

| Campo | Tipo/condición | Descripción |
| --- | --- | --- |
| `id` | bigint, PK | Identificador. |
| `name` | string | Nombre visible del usuario. |
| `email` | string, único | Correo de inicio de sesión. |
| `email_verified_at` | timestamp, nullable | Verificación opcional. |
| `password` | string | Hash de contraseña; el modelo usa cast `hashed`. |
| `is_admin` | boolean, default `false` | Permite entrar al panel Filament. |
| `remember_token` | string, nullable | Sesión recordada. |
| `created_at`, `updated_at` | timestamps | Auditoría básica. |

Relación: un usuario tiene muchos eventos mediante `events.directed_by_id`.

### 10.3 Tabla `events`

| Campo | Tipo/condición | Descripción |
| --- | --- | --- |
| `id` | bigint, PK | Identificador. |
| `date` | date | Fecha del evento. |
| `topic` | string | Tema o nombre del evento. |
| `start_time` | time | Hora de inicio. |
| `end_time` | time | Hora final. |
| `place` | string | Lugar. |
| `reason` | text | Motivo escrito o sugerido desde el catálogo. |
| `directed_by_id` | FK a `users`, requerido | Usuario que dirige/crea el evento. `cascadeOnDelete`. |
| `directed_by_position` | string | Cargo o área de quien dirige. |
| `attachment_path` | string, nullable | Ruta en el disco público. |
| `slug` | string, único | Identificador del enlace público. |
| `has_expiration` | boolean, default `false` | Activa la expiración del enlace. |
| `expiration_date` | date, nullable | Fecha límite inclusive. |
| `created_at`, `updated_at` | timestamps | Auditoría básica. |

Relaciones: pertenece a `User` mediante `directedBy` y tiene muchas `Attendance`.

### 10.4 Tabla `attendances`

| Campo | Tipo/condición | Descripción |
| --- | --- | --- |
| `id` | bigint, PK | Identificador. |
| `event_id` | FK a `events`, requerido | Evento de la asistencia. `cascadeOnDelete`. |
| `full_name` | string | Nombre completo derivado. |
| `first_names` | string, nullable | Nombres ingresados. El formulario público los exige. |
| `last_names` | string, nullable | Apellidos ingresados. El formulario público los exige. |
| `id_number` | string | Identificación normalizada. |
| `position_id` | FK a `positions`, nullable | Cargo seleccionado. `nullOnDelete`. |
| `position_custom` | string, nullable | Compatibilidad con datos personalizados/anteriores. El formulario público actual no lo utiliza. |
| `headquarter_id` | FK a `headquarters`, nullable | Sede seleccionada. `nullOnDelete`. |
| `headquarter_custom` | string, nullable | Compatibilidad con datos personalizados/anteriores. El formulario público actual no lo utiliza. |
| `signature` | long text | Data URI, normalmente PNG en base64. |
| `registered_at` | datetime | Fecha/hora de registro escrita por el servicio. |
| `created_at`, `updated_at` | timestamps | Auditoría básica. |

Restricción: `UNIQUE(event_id, id_number)`.

Nota técnica: el esquema usa `datetime` para `registered_at`, pero `Attendance::$casts` actualmente declara `date`. Las pantallas intentan mostrar fecha y hora; si la hora exacta es requisito operativo, conviene revisar ese cast y agregar una prueba de regresión.

### 10.5 Tablas de catálogo

`positions` y `headquarters` tienen la misma estructura:

| Campo | Tipo/condición | Descripción |
| --- | --- | --- |
| `id` | bigint, PK | Identificador. |
| `name` | string | Nombre visible. |
| `is_active` | boolean, default `true` | Define si aparece en nuevos formularios. |
| `created_at`, `updated_at` | timestamps | Auditoría. |

`reasons` agrega una restricción `UNIQUE(name)` y también tiene `is_active`. Los motivos se ofrecen como datalist al crear un evento, no como una relación foránea.

### 10.6 Tablas de soporte Laravel

Las migraciones iniciales también crean `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs` y `job_batches` según la versión de Laravel. La configuración productiva usa sesiones, caché y cola con almacenamiento en base de datos.

### 10.7 Comportamiento al eliminar

- Eliminar un evento elimina sus asistencias por `cascadeOnDelete`.
- Eliminar un usuario elimina sus eventos y, por cascada, sus asistencias.
- Eliminar un cargo o una sede deja el registro de asistencia, pero puede dejar la relación nula por `nullOnDelete`; por ello es preferible desactivar el catálogo en vez de borrarlo.
- No hay `SoftDeletes` en los modelos. Las eliminaciones del panel son permanentes salvo recuperación desde una copia de seguridad.

## 11. Formatos de reportes

### 11.1 XLSX

Nombre de archivo:

```text
asistencias-{slug-del-evento}-{YYYYMMDD}.xlsx
```

Contenido:

- Primera fila: título del evento.
- Segunda fila: `Nombre Completo`, `Número de Identificación`, `Cargo`, `Sede`, `Fecha de Registro`.
- Filas siguientes: una asistencia por fila.
- Orden: `registered_at` ascendente.
- Incluye filtro opcional de sedes.
- No incluye la imagen de la firma.

El XLSX es generado por `XlsxWriter` como un paquete Open XML mínimo usando `ZipArchive`; no depende de una biblioteca de hojas de cálculo para este flujo.

### 11.2 PDF

Nombre de archivo:

```text
asistencias-{slug-del-evento}-{YYYYMMDD}.pdf
```

Características:

- Orientación horizontal, tamaño A4.
- Encabezado institucional con logo si existe en alguna ruta conocida bajo `public/images` o `public/assets`.
- Información del evento: fecha, tema, horas, dirección, cargo/área, lugar y motivo.
- Tabla con número, nombre completo, identificación, cargo, sede y firma.
- La firma se convierte desde el data URI a una imagen temporal PNG.
- El encabezado de tabla se repite en páginas posteriores.
- Incluye el aviso de privacidad en el pie de página.
- El motivo se marca en una de cuatro opciones si coincide con `INDUCCIÓN CORPORATIVA`, `REINDUCCIÓN`, `CAPACITACIÓN` o `DIVULGACIÓN DE INFORMACIÓN`; cualquier otro valor se muestra como `OTRO`.
- Los textos largos se recortan para caber en las celdas del formato.

### 11.3 CSV no expuesto

Existen métodos de exportación CSV en `ReportController` y `ReportService`, pero no existe una ruta que los invoque y no hay opción CSV en la interfaz. La funcionalidad documentada y disponible para usuarios es XLSX y PDF.

## 12. Configuración de entorno

### 12.1 Dos archivos `.env`

Hay dos niveles de configuración:

1. El `.env` en la raíz del repositorio es leído por Docker Compose para sustituir variables como `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `APP_USER`, `UID` y `GID`.
2. `src/.env` es leído por Laravel dentro del contenedor, porque `src/` se monta como `/var/www`.

El repositorio incluye `src/.env.example`; no incluye un `.env.example` equivalente en la raíz. No se deben subir archivos `.env` reales.

### 12.2 Variables relevantes de Laravel

| Variable | Valor de referencia | Uso |
| --- | --- | --- |
| `APP_NAME` | `Sistema de Asistencia` | Nombre mostrado en títulos y branding. |
| `APP_ENV` | `local` / `production` | Entorno. |
| `APP_KEY` | Generada por Artisan | Cifrado de sesiones y datos Laravel. Debe mantenerse estable en producción. |
| `APP_DEBUG` | `true` solo local | Detalle de errores. Debe ser `false` en producción. |
| `APP_URL` | `http://localhost:8080` local | Base para URLs, enlaces públicos y QR. Para asistentes móviles debe ser una URL accesible desde sus dispositivos. |
| `APP_LOCALE` | `es` | Idioma. |
| `DB_CONNECTION` | `mysql` con Docker / `sqlite` en el ejemplo | Motor de base de datos. |
| `DB_HOST` | `db` dentro de Compose | Host MySQL del contenedor. |
| `DB_PORT` | `3306` dentro de Compose | Puerto interno de MySQL. |
| `DB_DATABASE` | `laravel` | Base de datos. |
| `DB_USERNAME` | `laravel` | Usuario de base de datos. |
| `DB_PASSWORD` | Configurado localmente | Contraseña de base de datos. |
| `SESSION_DRIVER` | `database` | Sesiones. La migración crea la tabla correspondiente. |
| `CACHE_STORE` | `database` | Caché productivo. |
| `QUEUE_CONNECTION` | `database` | Cola productiva. |
| `FILESYSTEM_DISK` | `local` por defecto | El adjunto de evento fuerza el disco `public` desde el formulario Filament. |

### 12.3 Configuración mínima para Docker local

Después de copiar `src/.env.example` a `src/.env`, ajustar al menos:

```dotenv
APP_NAME="Sistema de Asistencia"
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=laravel
```

Los valores de `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` deben coincidir con los valores que Compose utiliza para inicializar MySQL. Si no se crea un `.env` en la raíz, el Compose local usa por defecto `laravel` / `laravel` y contraseña root `root`.

El archivo de ejemplo de Laravel parte de SQLite. Esto es válido para ejecutar Laravel fuera de Docker o para pruebas, pero no es la configuración recomendada cuando se desea usar el servicio MySQL del Compose local.

## 13. Instalación local con Docker

Los comandos siguientes se ejecutan desde la raíz del repositorio.

### 13.1 Requisitos

- Docker Engine con Docker Compose.
- Git.
- Para compilar assets localmente: Node.js 20 o superior y pnpm, o un entorno equivalente.
- Para producción: dominio o URL pública, HTTPS y almacenamiento persistente.

### 13.2 Preparación

```bash
cp src/.env.example src/.env
```

Editar `src/.env` con la configuración de la sección anterior. Generar una clave antes de usar sesiones cifradas:

```bash
docker compose up -d --build
docker compose run --rm app php artisan key:generate
```

### 13.3 Base de datos y catálogos

```bash
docker compose run --rm app php artisan migrate
docker compose run --rm app php artisan db:seed
docker compose run --rm app php artisan storage:link
```

`db:seed` ejecuta `DatabaseSeeder`, que llama a:

- `AdminUserSeeder`.
- `PositionSeeder`.
- `HeadquarterSeeder`.
- `ReasonSeeder`.

Los datos iniciales incluidos por los seeders son:

- **Cargos:** 47 opciones, entre ellas Analista Contable, Aprendiz, Auxiliar Administrativo, Coordinador(a) de SST, Director(a) Administrativo(a), Mesero(a), Operario(a) de Producción, Supervisor(a) de Planta, Tesorero(a), CAPACITADOR EXTERNO y ASESOR EXTERNO.
- **Sedes:** Bochalema, Chipichape, Ciudad Jardín, Despensa, Flora, Granada, Jardín Plaza, Limonar, Llanogrande, Oficina, Pance, San Fernando, Unicentro y ENTIDAD EXTERNA.
- **Motivos:** INDUCCIÓN CORPORATIVA, REINDUCCIÓN, CAPACITACIÓN, DIVULGACIÓN DE INFORMACIÓN y COMITÉ PRIMARIO.

Los seeders de catálogos usan `firstOrCreate`, por lo que pueden ejecutarse de nuevo sin duplicar los nombres iniciales. `AdminUserSeeder` tampoco sobrescribe una cuenta existente.

En desarrollo, `storage:link` permite que los adjuntos del disco público sean accesibles mediante `/storage`. La imagen productiva crea el enlace durante el build, pero en el Compose local debe verificarse explícitamente.

### 13.4 Assets frontend

El servicio `vite` está comentado en `docker-compose.yml`; por tanto, `docker compose up` no inicia un servidor Vite ni expone automáticamente el puerto `5173`. Para compilar los assets en un equipo con Node/pnpm:

```bash
cd src
corepack enable
pnpm install --frozen-lockfile
pnpm run build
```

Para desarrollo con recarga en caliente:

```bash
cd src
pnpm run dev -- --host 0.0.0.0 --port 5173
```

La imagen productiva construida con `Dockerfile.prod` instala dependencias frontend y ejecuta `pnpm run build` durante la etapa de build.

### 13.5 Accesos locales

| Servicio | URL | Observación |
| --- | --- | --- |
| Aplicación | `http://localhost:8080` | Nginx. |
| Panel | `http://localhost:8080/admin` | Login de Filament. |
| phpMyAdmin | `http://localhost:8081` | Servidor `db`, puerto interno `3306`. |
| Vite opcional | `http://localhost:5173` | Solo si se inicia manualmente el proceso dev. |
| Health check | `http://localhost:8080/up` | Estado de Laravel. |

Credenciales seed iniciales:

```text
Correo:    admin@example.com
Contraseña: admin123
```

Cambiar esta contraseña inmediatamente fuera de un entorno local.

## 14. Comandos operativos

### Docker Compose local

```bash
docker compose up -d
docker compose down
docker compose ps
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f db
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:status
docker compose run --rm app php artisan test
```

`docker compose down` no elimina el volumen `db_data` salvo que se solicite expresamente la eliminación de volúmenes. No usar `docker compose down -v` en un entorno con datos que se quieran conservar.

### Artisan y Composer

```bash
docker compose run --rm app php artisan [comando]
docker compose run --rm app composer [comando]
```

Comandos útiles:

```bash
docker compose run --rm app php artisan config:clear
docker compose run --rm app php artisan cache:clear
docker compose run --rm app php artisan storage:link
docker compose run --rm app php artisan db:seed --class=UserSeeder
docker compose run --rm app php artisan db:seed --class=AdminUserSeeder
```

## 15. Despliegue en Coolify

### 15.1 Compose productivo

Coolify debe utilizar `docker-compose.coolify.yml`. El build usa `Dockerfile.prod` y sus etapas son:

1. `php-base`: PHP 8.4, extensiones, dependencias del sistema y soporte para MySQL.
2. `vendor`: instalación Composer sin dependencias de desarrollo.
3. `frontend`: Node 20, pnpm y build de Vite.
4. `app`: PHP-FPM con el código, `vendor`, assets compilados y entrypoint de migraciones.
5. `nginx`: imagen web con los archivos públicos de la aplicación.

### 15.2 Variables obligatorias

Configurar en Coolify, no en el repositorio:

```text
APP_KEY
APP_URL
DB_DATABASE
DB_USERNAME
DB_PASSWORD
DB_ROOT_PASSWORD
```

Además, validar:

```text
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
RUN_MIGRATIONS=true
```

`APP_URL` debe ser la URL pública real, preferiblemente HTTPS. Si contiene `localhost`, los QR generados no funcionarán para otros dispositivos.

### 15.3 Migraciones de despliegue

El entrypoint `docker/php/entrypoint.prod.sh` ejecuta:

```bash
php artisan migrate --force --no-interaction
```

si `RUN_MIGRATIONS` es `true`, valor predeterminado en el Compose productivo. Establecerlo en `false` solo si las migraciones se ejecutan desde un paso de despliegue separado.

El entrypoint no ejecuta `db:seed`. La creación del administrador y los catálogos debe realizarse como operación inicial controlada, no automáticamente en cada reinicio.

### 15.4 Persistencia

Conservar los dos volúmenes:

| Volumen | Contenido |
| --- | --- |
| `db_data` | Base de datos MySQL. |
| `public_storage` | Adjuntos cargados en `storage/app/public`, principalmente bajo `events/`. |

Perder `public_storage` rompe los enlaces de adjuntos aunque la base de datos siga disponible.

### 15.5 Nginx y límites

- Nginx acepta solicitudes de hasta 100 MB (`client_max_body_size`).
- PHP configura `upload_max_filesize=100M` y `post_max_size=100M`.
- El formulario Filament limita el adjunto de evento a 10 MB.
- Nginx entrega assets estáticos con caché pública de 30 días.
- Las solicitudes PHP se envían a `app:9000`.

## 16. Pruebas y verificación

PHPUnit está configurado en `src/phpunit.xml` con:

- Suite `Unit`: `tests/Unit`.
- Suite `Feature`: `tests/Feature`.
- SQLite en memoria (`DB_DATABASE=:memory:`).
- Sesiones en array y colas síncronas.

Ejecutar:

```bash
docker compose run --rm app php artisan test
```

Las pruebas funcionales existentes verifican, entre otros casos:

- asociación de cargo y sede por ID;
- rechazo de valores personalizados no permitidos en el formulario público;
- obligatoriedad de nombres y apellidos;
- normalización de identificaciones;
- rechazo de letras en la identificación;
- duplicado por identificación dentro del mismo evento;
- reutilización de identificación en otro evento;
- disponibilidad durante el día de expiración;
- rechazo de enlaces vencidos tanto en consulta como en registro;
- selección de múltiples sedes en reportes;
- inclusión de todas las sedes cuando no se selecciona ninguna;
- rechazo de sedes inexistentes.

Como verificación adicional antes de una entrega:

```bash
docker compose run --rm app php artisan config:clear
docker compose run --rm app php artisan route:list
cd src
pnpm run build
```

## 17. Copias de seguridad y recuperación

### 17.1 Base de datos MySQL local

Los siguientes ejemplos son para una shell compatible con Unix y deben ejecutarse desde la raíz. Guardar las copias fuera del repositorio:

```bash
mkdir -p backups
docker compose exec db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > backups/attendance-$(date +%Y%m%d-%H%M%S).sql
```

Restauración:

```bash
cat backups/attendance-YYYYMMDD-HHMMSS.sql | docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'
```

Verificar el archivo y el destino antes de restaurar. Una restauración puede sobrescribir datos existentes.

### 17.2 Archivos adjuntos

Respaldar también `src/storage/app/public` en local o el volumen `public_storage` en producción. La base de datos conserva la ruta, no una copia separada del archivo cargado.

### 17.3 Seguridad de las copias

- Cifrar las copias que contengan identificaciones o firmas.
- Restringir su acceso al personal autorizado.
- Probar periódicamente una restauración.
- Definir una política de retención acorde con la política de tratamiento de datos personales.

## 18. Seguridad y privacidad

La aplicación trata identificaciones, nombres y firmas. Recomendaciones mínimas:

- usar HTTPS en cualquier entorno accesible por asistentes;
- cambiar `admin@example.com` / `admin123` inmediatamente;
- mantener `APP_DEBUG=false` en producción;
- mantener `APP_KEY` estable, privada y fuera del repositorio;
- no publicar phpMyAdmin en Internet;
- limitar el acceso a la base de datos y a los volúmenes;
- hacer copias cifradas y controlar su retención;
- conservar el aviso de privacidad institucional y actualizarlo cuando cambie la política legal;
- recordar que el enlace público actúa como un mecanismo de acceso: cualquier persona que lo reciba puede intentar registrar una asistencia mientras esté vigente;
- revisar la autorización de las rutas de reportes si se agregan nuevos tipos de usuarios.

La interfaz pública y el pie del PDF incluyen una referencia a la Ley 1581 de 2012, el Decreto 1074 de 2015 y la Política de Tratamiento de Datos Personales. El código no implementa una casilla de consentimiento separada.

## 19. Limitaciones y diferencias conocidas

Estas observaciones son importantes para soporte y futuras mejoras:

1. **CSV no disponible para usuarios:** existen métodos internos, pero no una ruta ni una opción en la interfaz. Los formatos utilizables son XLSX y PDF.
2. **Servicio Vite local desactivado:** el bloque `vite` de `docker-compose.yml` está comentado. El puerto `5173` no queda activo salvo que se inicie Vite manualmente.
3. **Variables de Laravel y Compose separadas:** copiar solo un archivo `.env` en la raíz no configura necesariamente Laravel; la aplicación lee `src/.env` dentro del volumen montado.
4. **SQLite en el archivo de ejemplo:** `src/.env.example` usa SQLite, mientras que el Compose local define MySQL. Para Docker hay que cambiar `DB_CONNECTION`, host, puerto y credenciales.
5. **Gestión de usuarios fuera de Filament:** no existe un recurso de usuarios, recuperación de contraseña propia ni área privada para usuarios no administradores.
6. **Motivos como datalist:** los motivos activos son sugerencias y se puede escribir texto personalizado; no hay relación foránea entre `events` y `reasons`.
7. **Cuatro opciones visuales en el formato:** motivos como `COMITÉ PRIMARIO` se almacenan, pero el formulario institucional los muestra como `OTRO` porque el PDF solo reconoce cuatro etiquetas.
8. **Hora de registro:** `registered_at` es `datetime` en la base de datos, pero el cast del modelo es `date`; debe revisarse si se necesita precisión de hora en todas las consultas.
9. **Sin papelera:** eventos, asistencias y catálogos se eliminan de forma permanente desde el panel; la recuperación depende de backups.
10. **Archivos públicos:** los adjuntos del evento se sirven desde el disco público. No deben cargarse documentos que requieran control de acceso distinto al del enlace del evento.

## 20. Mantenimiento y extensibilidad

### Agregar un campo al evento

1. Crear una migración en `src/database/migrations`.
2. Agregar el campo a `Event::$fillable` y a `casts` si corresponde.
3. Incorporarlo en `EventForm`.
4. Actualizar la vista pública y la vista de detalle si debe mostrarse.
5. Considerar el impacto en el PDF y XLSX.
6. Agregar pruebas de creación, edición y renderizado.

### Agregar un catálogo

1. Crear migración y modelo.
2. Crear seeder si existe un conjunto inicial.
3. Crear Resource, schema, tabla y páginas Filament.
4. Aplicar `is_active` si se necesita desactivación sin borrar históricos.
5. Validar que el formulario público solo acepte registros activos.

### Cambiar el vencimiento

Mantener el criterio de fecha inclusiva en `EventService::isPublicLinkAvailable()` y sus pruebas. Si se cambia la zona horaria, actualizar código y pruebas simultáneamente.

### Cambiar el QR

El QR no contiene datos de asistencia; codifica `url('/evento/' . $event->slug)`. Cambiar `APP_URL` afecta los nuevos SVG generados, pero el slug y la ruta relativa del evento se mantienen.

## 21. Referencia rápida de archivos

| Necesidad | Archivo principal |
| --- | --- |
| Rutas públicas y reportes | `src/routes/web.php` |
| Registro público | `src/app/Http/Controllers/PublicAttendanceController.php` |
| Formulario público | `src/resources/views/public/event-register.blade.php` |
| Firma y selección de sedes | `src/resources/js/app.js` |
| Eventos en Filament | `src/app/Filament/Resources/Events/` |
| Asistencias en Filament | `src/app/Filament/Resources/Attendances/` |
| Catálogo de cargos | `src/app/Filament/Resources/Positions/` |
| Catálogo de sedes | `src/app/Filament/Resources/Headquarters/` |
| Catálogo de motivos | `src/app/Filament/Resources/Reasons/` |
| Generación de QR | `src/app/Services/QrCodeService.php` |
| Reglas de registro | `src/app/Services/AttendanceService.php` |
| Expiración y adjuntos | `src/app/Services/EventService.php` |
| XLSX y PDF | `src/app/Services/ReportService.php` y `XlsxWriter.php` |
| Esquema de base de datos | `src/database/migrations/` |
| Datos iniciales | `src/database/seeders/` |
| Tema público | `src/resources/css/app.css` |
| Tema administrativo | `src/resources/css/filament/admin/theme.css` |
| Compose local | `docker-compose.yml` |
| Compose Coolify | `docker-compose.coolify.yml` |

## 22. Checklist de entrega

- [ ] `APP_KEY` configurada y no compartida públicamente.
- [ ] `APP_URL` accesible desde el dispositivo que escaneará el QR.
- [ ] `APP_DEBUG=false` en producción.
- [ ] DB configurada con host interno correcto (`db` en Compose).
- [ ] Migraciones ejecutadas.
- [ ] Catálogos cargados.
- [ ] Usuario administrador creado y contraseña inicial cambiada.
- [ ] Assets compilados.
- [ ] `storage:link` y almacenamiento persistente verificados.
- [ ] HTTPS configurado.
- [ ] Backup de base de datos y adjuntos probado.
- [ ] Pruebas automatizadas ejecutadas.
- [ ] Evento de prueba creado y registro completo verificado desde un teléfono.
