# Simposio XIV — Backend (API)

API REST del **XIV Simposio de Informática Empresarial** (Universidad de Costa Rica): autenticación, catálogo de eventos, inscripciones de participantes y panel de administración (eventos, horarios, aulas, ponentes, áreas y usuarios).

Este repositorio es **la mitad de un sistema de dos proyectos**. No sirve HTML de producción propio (solo la vista de bienvenida por defecto de Laravel): toda la interfaz la sirve el frontend, que consume esta API vía HTTP.

```
SimposioXIV_Backend   (este repo)   Laravel 13 / PHP 8.5 / PostgreSQL
        ↑  API REST  /api/*  (Bearer token, Sanctum)
SimposioXIV_Frontend                React 19 / Vite / TanStack Query
```

> Repo hermano: [`SimposioXIV_Frontend`](../SimposioXIV_Frontend/README.md) — léelo también si vas a levantar el sistema completo, ahí está la guía de la SPA que consume esta API.

---

## Stack

| Componente | Versión / Detalle |
|---|---|
| PHP | 8.5 |
| Laravel Framework | v13 |
| Base de datos | PostgreSQL 14+ (SQLite en memoria para tests) |
| Laravel Sanctum | v4 — autenticación por token Bearer (no cookies/SPA-session) |
| Correo | SMTP (envío de OTP para reseteo de contraseña y correos de bienvenida con credenciales) |
| Laravel Pint | v1 — formateador de código |
| PHPUnit | v12 |
| Vite + Tailwind | Solo para la vista `welcome` por defecto de Laravel (no crítico para la API) |

## Dominio de datos

```
User
  ├── nombre, email (@ucr.ac.cr), carnet (6 caracteres), password
  └── tipo_usuario  → admin | participante

Evento
  ├── titulo, descripcion, tipo (apertura|clausura|taller|charla)
  ├── capacidad, numero_inscritos, esta_activo
  ├── horario_id  → Horario (numero_dia 1-3, hora_inicio, hora_fin, aula_id → Aula)
  ├── ponente_id  → Ponente
  └── areas[]     → Area (N:M vía evento_area)

Inscripcion
  ├── user_id, evento_id
  ├── estado  → Confirmado | Cancelado
  └── enrolled_at
```

**Reglas de negocio clave:**
- Login acepta `identifier` como email `@ucr.ac.cr` o carnet (6 caracteres alfanuméricos).
- Inscribirse requiere evento activo, cupo disponible, sin duplicados y sin choque de horario con otra inscripción confirmada del mismo usuario.
- `numero_inscritos` se actualiza dentro de `DB::transaction` + `lockForUpdate` (evita condiciones de carrera al inscribir/cancelar).
- Cancelar una inscripción ya cancelada devuelve `409`; solo el dueño puede cancelar la suya.
- El reseteo de contraseña usa un flujo OTP de 6 dígitos enviado por correo (`/api/password/otp` → `/api/password/otp/verificar` → `/api/password/cambiar`).
- Las rutas bajo `/api/admin/*` requieren `tipo_usuario = admin` (middleware `EsAdmin`).

## Endpoints principales

| Método | Ruta | Auth | Rol |
|---|---|---|---|
| POST | `/api/login` | No | — |
| POST | `/api/logout` | Sí | cualquiera |
| GET | `/api/me` | Sí | cualquiera |
| POST/PUT | `/api/password/otp`, `/otp/verificar`, `/cambiar` | Sí | cualquiera |
| GET | `/api/eventos`, `/api/eventos/{evento}` | Sí | cualquiera |
| GET/POST/DELETE | `/api/inscripciones` | Sí | cualquiera |
| CRUD | `/api/admin/usuarios`, `/eventos`, `/horarios`, `/aulas`, `/ponentes`, `/areas` | Sí | admin |
| GET | `/api/admin/eventos/{evento}/inscritos` | Sí | admin |

Filtros de `GET /api/eventos`: `dia` (1-3), `tipo`, `area_id`, `solo_disponibles` (bool).

Ver el listado completo y actualizado con `php artisan route:list --path=api`.

---

## Levantar en local

### Requisitos

- PHP 8.5 con extensiones estándar de Laravel (`pdo_pgsql`, `mbstring`, `openssl`, etc.)
- Composer 2
- PostgreSQL 14+ (o Docker para levantarlo)
- Node.js 20 (opcional — solo si vas a compilar la vista `welcome` por defecto)

### Pasos

```bash
git clone <url-del-repo> SimposioXIV_Backend
cd SimposioXIV_Backend

composer install

cp .env.example .env
php artisan key:generate
```

Edita `.env` y ajusta al menos:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=backend_simposio
DB_USERNAME=<tu_usuario>
DB_PASSWORD=<tu_password>
```

Crea la base de datos (`createdb backend_simposio` o equivalente) y luego:

```bash
php artisan migrate --seed
php artisan serve
```

La API queda arriba en `http://localhost:8000`. Con `--seed` se cargan datos de prueba: eventos, horarios, aulas, ponentes, áreas y estos usuarios de prueba (contraseña `password` salvo el admin):

| Identificador | Email | Rol |
|---|---|---|
| `C37190` | `aaron.salazarmata@ucr.ac.cr` | participante |
| `C23456` | `maria.rodriguez@ucr.ac.cr` | participante |
| `C34567` | `carlos.mora@ucr.ac.cr` | participante |
| — | `admin@ucr.ac.cr` | **admin** — password `Admin1234!` |

Más 10 usuarios aleatorios generados por factory.

### Servir junto con el frontend

Con el backend en `http://localhost:8000`, apunta el `.env` del frontend a `VITE_API_URL=http://localhost:8000/api` (ver [README del frontend](../SimposioXIV_Frontend/README.md)) y arráncalo con `npm run dev`. CORS ya viene habilitado por defecto de Laravel para rutas `/api/*` sin configuración adicional.

### Comando todo-en-uno (server + queue + vite)

```bash
composer run dev
```

### Correo (OTP y bienvenida)

Los flujos de "olvidé mi contraseña" y "correo de bienvenida al crear usuario" (panel admin) envían correo real. En local puedes:
- Dejar `MAIL_MAILER=log` para que los correos se escriban en `storage/logs/laravel.log` en vez de enviarse, o
- Usar [Mailtrap](https://mailtrap.io) / [Mailpit](https://github.com/axllent/mailpit) con SMTP local para verlos en un buzón de pruebas.

**No uses las credenciales SMTP reales del `.env` de producción en tu entorno local.**

---

## Testing y calidad

```bash
php artisan test --compact     # PHPUnit — corre contra SQLite en memoria, no toca tu Postgres local
vendor/bin/pint --dirty        # Formatear solo archivos modificados
vendor/bin/pint                # Formatear todo el proyecto
```

CI (`.github/workflows/laravel.yml`) corre en cada push/PR a `main`: `pint --test` + suite completa de PHPUnit contra SQLite.

---

## Poner en producción

### Stack recomendado

- PHP-FPM 8.5 detrás de Nginx (o [Laravel Cloud](https://cloud.laravel.com/) / [Forge](https://forge.laravel.com/) para no gestionar servidores)
- PostgreSQL 14+ gestionado (RDS, Supabase, Neon, DigitalOcean Managed DB, etc.)
- Redis recomendado para `CACHE_STORE`, `QUEUE_CONNECTION` y `SESSION_DRIVER` en vez de `database` (menor carga en Postgres)
- Proveedor SMTP transaccional real (el actual usa el SMTP institucional de la UCR) para los correos de OTP y bienvenida
- Worker de colas corriendo `php artisan queue:work` bajo Supervisor (o el equivalente gestionado si usas Laravel Cloud/Forge)
- HTTPS terminado en el proxy/balanceador — Sanctum y las cookies de sesión (usadas solo internamente) esperan `APP_URL` con `https://`

### Variables de entorno críticas a cambiar respecto a `.env.example`

| Variable | Producción |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | dominio real de la API (`https://api.tudominio.com`) |
| `APP_KEY` | generada con `php artisan key:generate` (nunca reutilizar la de dev) |
| `DB_*` | credenciales del Postgres gestionado |
| `MAIL_*` | proveedor SMTP real |
| `SANCTUM_STATEFUL_DOMAINS` | dominio(s) del frontend en producción, sin protocolo (ej. `simposio.ucr.ac.cr`) |
| `SESSION_DOMAIN` | dominio del frontend si necesitas cookies entre subdominios; déjalo en `null` si solo usas Bearer token |
| `CACHE_STORE`, `QUEUE_CONNECTION`, `SESSION_DRIVER` | `redis` en vez de `database` bajo carga real |
| `LOG_LEVEL` | `error` o `warning` (no `debug`) |

### Gestión de secretos

El `.env` local **nunca** debe ser el mismo archivo que usa producción, y sus valores reales (contraseña de base de datos, credenciales SMTP, `APP_KEY`) no deben viajar por Slack, correo ni quedar pegados en un `.env` de un servidor compartido. Reglas generales, válidas para cualquier plataforma de despliegue:

1. **El repo nunca contiene secretos reales** — `.env` y `.env.*` (excepto `.env.example`) ya están en `.gitignore`; no lo cambies para "guardar una copia".
2. **Los secretos de producción viven solo en el mecanismo de env vars de la plataforma elegida**, nunca en un archivo dentro del repo desplegado:
   - *Laravel Cloud*: variables de entorno del proyecto en el dashboard (cifradas en reposo).
   - *Forge / VPS propio*: variables vía el panel de Forge, o un `.env` en el servidor con permisos `600`, fuera de cualquier carpeta servida públicamente y nunca commiteado.
   - *Docker / contenedores*: inyectadas como variables de entorno del contenedor en tiempo de despliegue (Docker secrets, AWS Secrets Manager, GCP Secret Manager, etc.), no horneadas en la imagen.
3. **Rota cualquier credencial que haya existido en texto plano en una máquina de desarrollo** antes de ir a producción — no reutilices la contraseña SMTP/DB de tu `.env` local como la de producción, aunque el archivo nunca se haya subido a git.
4. Si ya tenías una credencial de un servicio real (SMTP institucional, etc.) en tu `.env` local para probar el envío de correos, considérala expuesta y pide que la roten en el servicio antes de usar esa cuenta en producción.

### Despliegue

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Si vas a servir la vista `welcome` (opcional, no la usa el frontend):

```bash
npm install --ignore-scripts
npm run build
```

Checklist antes de exponerlo públicamente:
- [ ] `APP_DEBUG=false` y `APP_ENV=production`
- [ ] Base de datos con backups automáticos configurados
- [ ] `php artisan admin:seed` o equivalente ejecutado con una contraseña de admin que **no sea** `Admin1234!` (cambiar el seeder o rotar la contraseña tras el primer login)
- [ ] Certificado HTTPS activo en el dominio de la API
- [ ] `SANCTUM_STATEFUL_DOMAINS` apuntando al dominio real del frontend
- [ ] Rate limiting revisado (`throttle:5,1` en login, `throttle:60,1` en rutas autenticadas) — ajustar si el tráfico esperado lo requiere
- [ ] Worker de colas (`queue:work`) corriendo como servicio persistente para el envío de correos

---

## Estructura de archivos

```
app/
├── Enums/                       TipoEvento, EstadoInscripcion, TipoUsuario
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php, PasswordController.php
│   │   ├── EventoController.php, InscripcionController.php
│   │   └── Admin/                CRUD de administración (eventos, horarios, aulas, ponentes, áreas, usuarios)
│   ├── Middleware/EsAdmin.php
│   ├── Requests/                 FormRequests de validación por endpoint
│   └── Resources/                Transformadores JSON (Area, Aula, Evento, Horario, Inscripcion, Ponente, User)
├── Models/
├── Services/                     AuthService, OtpService
└── Providers/

database/
├── factories/
├── migrations/
└── seeders/                      UserSeeder, AdminSeeder, AulaSeeder, AreaSeeder, PonenteSeeder, HorarioSeeder, EventoSeeder

routes/api.php                    Todas las rutas de la API
tests/                             PHPUnit, feature tests
```
