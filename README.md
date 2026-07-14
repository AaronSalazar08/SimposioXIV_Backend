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
| GET | `/api/health` | No | — |
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

`php artisan serve` imprime, además del mensaje habitual de Laravel, un pequeño resumen con los enlaces útiles para arrancar el sistema completo (health check, API tester y la URL esperada del frontend):

```
  XIV Simposio — enlaces rápidos
    API health ..... http://127.0.0.1:8000/api/health
    API tester ..... http://127.0.0.1:8000/api-tester
    Frontend (SPA) . http://localhost:5173 (arrancar aparte: npm run dev en SimposioXIV_Frontend)

  INFO  Server running on [http://127.0.0.1:8000].
```

La URL del frontend que se muestra sale de `FRONTEND_URL` en `.env` (por defecto `http://localhost:5173`, el puerto de Vite) — ajústala si corrés el frontend en otro puerto.

La API queda arriba en `http://localhost:8000`. Con `--seed` se cargan datos de prueba: eventos, horarios, aulas, ponentes, áreas y estos usuarios de prueba (contraseña `password` salvo el admin):

| Identificador | Email | Rol |
|---|---|---|
| `C37190` | `aaron.salazarmata@ucr.ac.cr` | participante |
| `C23456` | `maria.rodriguez@ucr.ac.cr` | participante |
| `C34567` | `carlos.mora@ucr.ac.cr` | participante |
| — | `admin@ucr.ac.cr` | **admin** — password `Admin1234!` |

Más 10 usuarios aleatorios generados por factory.

### Verificar que el backend encendió bien

```bash
curl http://localhost:8000/api/health
```

Devuelve JSON con el estado de la app, versión de PHP/Laravel y si la base de datos está conectada:

```json
{
  "status": "ok",
  "app": "Laravel",
  "env": "local",
  "php_version": "8.5.5",
  "laravel_version": "13.6.0",
  "database": { "connected": true, "driver": "pgsql", "error": null },
  "timestamp": "2026-07-13T20:42:32+00:00"
}
```

### Consola de pruebas de la API (`/api-tester`)

Con el servidor corriendo, abre `http://localhost:8000/api-tester` en el navegador: una vista Blade autocontenida (sin build, sin dependencias externas) que lista **todos los endpoints de la API organizados por sección** (Salud, Autenticación, Contraseña/OTP, Eventos, Inscripciones, Admin · Usuarios/Eventos/Horarios/Aulas/Ponentes/Áreas). Por cada endpoint podés:

- Editar parámetros de ruta, query params y el cuerpo JSON (precargado con un ejemplo válido)
- Enviar la petición real contra la API y ver el código de estado, tiempo de respuesta y el JSON de respuesta formateado
- Hacer login una vez y el token Bearer queda guardado automáticamente (`localStorage`) para el resto de los endpoints protegidos, sin tener que copiarlo a mano

Es la forma más rápida de validar que el backend levantó bien y que cada endpoint responde como se espera, sin necesidad de Postman/Insomnia.

**Disponibilidad:** habilitada por defecto solo fuera de `APP_ENV=production` (variable `API_TESTER_ENABLED` en `.env`, ver `.env.example`). En producción devuelve `404` a menos que se fuerce explícitamente con `API_TESTER_ENABLED=true`. No añade ningún bypass de autenticación: cada request que dispara sigue respetando la autenticación real de la API (rutas `auth:sanctum`/`EsAdmin`), así que no expone nada que ya no pudieras hacer con un cliente HTTP cualquiera.

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
| `API_TESTER_ENABLED` | Déjala sin definir (queda `false` automáticamente por `APP_ENV=production`) — no la pongas en `true` en producción |
| `FRONTEND_URL` | URL pública del frontend en producción, ej. `https://sieguanacaste.com` (sin barra final). El botón "Ingresar al Simposio" del correo de bienvenida (`resources/views/emails/bienvenida.blade.php`) enlaza ahí — si queda en el default de desarrollo, ese botón manda a `localhost:5173` para todos los usuarios reales. |

### `env()` fuera de archivos de config: por qué rompe en producción

Toda lectura de variables de entorno en el código de la app (controllers, services, **vistas Blade**, mailables) debe pasar por `config('services.frontend.url')` y **nunca** por `env('FRONTEND_URL', ...)` directamente. Motivo: `php artisan config:cache` (parte del despliegue, ver abajo) hace que Laravel deje de leer el archivo `.env` en cada request — usa el array de config ya resuelto y cacheado. Si algo fuera de un archivo `config/*.php` llama a `env()` directamente, esa llamada **siempre devuelve el valor por defecto** (o `null`) una vez que el config está cacheado, sin importar lo que diga el `.env` real del servidor. Esto ya causó un bug real: el correo de bienvenida enlazaba a `http://localhost:5173` en producción porque la vista llamaba `env('FRONTEND_URL', ...)` en vez de `config('services.frontend.url')`. Si agregás una variable de entorno nueva:
1. Exponela en `config/services.php` (o el archivo de config que corresponda) con `env('MI_VAR', 'default')`.
2. Consumila en el resto del código con `config('services.mi_var')`, nunca con `env()` directo.

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

**Si solo cambiaste una variable de `.env`** (por ejemplo `FRONTEND_URL` o `MAIL_*`) sin tocar código, no alcanza con editar el archivo: el config y las vistas quedaron cacheados del despliegue anterior. Hay que refrescar el cache explícitamente:

```bash
php artisan config:clear && php artisan config:cache
php artisan view:clear
```

Checklist antes de exponerlo públicamente:
- [ ] `APP_DEBUG=false` y `APP_ENV=production`
- [ ] Base de datos con backups automáticos configurados
- [ ] `php artisan admin:seed` o equivalente ejecutado con una contraseña de admin que **no sea** `Admin1234!` (cambiar el seeder o rotar la contraseña tras el primer login)
- [ ] Certificado HTTPS activo en el dominio de la API
- [ ] `SANCTUM_STATEFUL_DOMAINS` apuntando al dominio real del frontend
- [ ] `FRONTEND_URL` apuntando al dominio real del frontend (revisa el correo de bienvenida en un envío de prueba: el botón debe apuntar ahí, no a `localhost:5173`)
- [ ] Rate limiting revisado (`throttle:5,1` en login, `throttle:60,1` en rutas autenticadas) — ajustar si el tráfico esperado lo requiere
- [ ] Worker de colas (`queue:work`) corriendo como servicio persistente para el envío de correos
- [ ] `/api-tester` devuelve `404` (verificar con `curl -I https://api.tudominio.com/api-tester`) — no forzar `API_TESTER_ENABLED=true` en producción

---

## Estructura de archivos

```
app/
├── Enums/                       TipoEvento, EstadoInscripcion, TipoUsuario
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php, PasswordController.php, HealthController.php
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

resources/views/api-tester/       Consola de pruebas de la API (/api-tester)

routes/
├── api.php                       Todas las rutas de la API (incluye /health)
└── web.php                       Vista welcome + /api-tester

tests/                             PHPUnit, feature tests
```
