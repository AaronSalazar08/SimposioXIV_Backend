<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>API Tester — {{ config('app.name') }}</title>
<style>
  :root {
    --bg: #0f172a;
    --bg-panel: #16213e;
    --bg-card: #1a2544;
    --bg-input: #0d1526;
    --border: #2b3a5c;
    --text: #e2e8f0;
    --text-dim: #94a3b8;
    --accent: #38bdf8;
    --get: #2563eb;
    --post: #16a34a;
    --put: #d97706;
    --delete: #dc2626;
    --ok: #22c55e;
    --err: #ef4444;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: var(--bg);
    color: var(--text);
    display: flex;
    min-height: 100vh;
  }
  code, pre, .mono, input, textarea { font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace; }

  /* ---------- Sidebar ---------- */
  #sidebar {
    width: 260px;
    flex-shrink: 0;
    background: var(--bg-panel);
    border-right: 1px solid var(--border);
    padding: 16px 0;
    overflow-y: auto;
    position: sticky;
    top: 0;
    height: 100vh;
  }
  #sidebar h1 {
    font-size: 15px;
    margin: 0 16px 4px;
    color: #fff;
  }
  #sidebar .subtitle {
    font-size: 11px;
    color: var(--text-dim);
    margin: 0 16px 16px;
  }
  #sidebar nav a {
    display: block;
    padding: 3px 16px;
    color: var(--text-dim);
    text-decoration: none;
    font-size: 12.5px;
    line-height: 1.6;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  #sidebar nav a:hover { color: var(--accent); }
  #sidebar nav .section-title {
    display: block;
    margin: 14px 16px 2px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #5b6b8c;
    font-weight: 600;
  }
  #sidebar nav a .m {
    display: inline-block;
    width: 38px;
    font-size: 10px;
    font-weight: 700;
  }

  /* ---------- Main ---------- */
  #main { flex: 1; min-width: 0; padding: 24px 32px 80px; }

  #topbar {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
  }
  #topbar label { font-size: 11px; color: var(--text-dim); display: block; margin-bottom: 3px; }
  #topbar input {
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: var(--text);
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 12.5px;
    min-width: 260px;
  }
  #session-badge {
    font-size: 12px;
    padding: 5px 10px;
    border-radius: 999px;
    background: #263457;
    color: var(--text-dim);
  }
  #session-badge.active { background: #14532d; color: #86efac; }
  #topbar button.ghost {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-dim);
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 12px;
    cursor: pointer;
  }
  #topbar button.ghost:hover { border-color: var(--accent); color: var(--accent); }

  section.group { margin-bottom: 36px; }
  section.group > h2 {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #5b6b8c;
    border-bottom: 1px solid var(--border);
    padding-bottom: 8px;
    margin: 0 0 14px;
  }

  details.card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-bottom: 10px;
    overflow: hidden;
  }
  details.card[open] { border-color: #3a4a72; }
  details.card summary {
    list-style: none;
    cursor: pointer;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  details.card summary::-webkit-details-marker { display: none; }
  .badge-method {
    font-size: 10.5px;
    font-weight: 800;
    padding: 3px 7px;
    border-radius: 5px;
    color: #fff;
    flex-shrink: 0;
    min-width: 52px;
    text-align: center;
  }
  .badge-method.GET { background: var(--get); }
  .badge-method.POST { background: var(--post); }
  .badge-method.PUT, .badge-method.PATCH { background: var(--put); }
  .badge-method.DELETE { background: var(--delete); }
  summary .path { font-size: 13px; color: #f1f5f9; }
  summary .desc { font-size: 12px; color: var(--text-dim); margin-left: auto; text-align: right; }
  summary .lock { font-size: 11px; color: #f59e0b; }

  .card-body { padding: 4px 16px 16px; border-top: 1px solid var(--border); }
  .field-row { display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0; }
  .field { flex: 1; min-width: 160px; }
  .field label {
    display: block;
    font-size: 11px;
    color: var(--text-dim);
    margin-bottom: 4px;
  }
  .field input {
    width: 100%;
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: var(--text);
    border-radius: 6px;
    padding: 7px 9px;
    font-size: 12.5px;
  }
  textarea.body-json {
    width: 100%;
    min-height: 110px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: #c7d2fe;
    border-radius: 6px;
    padding: 10px;
    font-size: 12.5px;
    resize: vertical;
  }
  .send-row { display: flex; align-items: center; gap: 12px; margin-top: 10px; }
  button.send {
    background: var(--accent);
    color: #04202e;
    border: none;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 12.5px;
    cursor: pointer;
  }
  button.send:hover { filter: brightness(1.08); }
  button.send:disabled { opacity: 0.6; cursor: wait; }
  .req-line { font-size: 11.5px; color: var(--text-dim); }

  .response-box { margin-top: 14px; display: none; }
  .response-box.shown { display: block; }
  .response-meta { display: flex; gap: 10px; align-items: center; margin-bottom: 6px; }
  .status-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
  }
  .status-pill.ok { background: #14532d; color: var(--ok); }
  .status-pill.err { background: #4c1414; color: var(--err); }
  .time-pill { font-size: 11px; color: var(--text-dim); }
  pre.response-json {
    background: #060b18;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
    font-size: 12px;
    line-height: 1.55;
    max-height: 340px;
    overflow: auto;
    white-space: pre-wrap;
    word-break: break-word;
  }
  .jk { color: #7dd3fc; }
  .js { color: #86efac; }
  .jn { color: #fca5a5; }
  .jb { color: #fcd34d; }
  .jnull { color: #94a3b8; }

  .empty-note { font-size: 11px; color: var(--text-dim); margin-top: 20px; }
  a.footer-link { color: var(--accent); }
</style>
</head>
<body>

<aside id="sidebar">
  <h1>🧪 API Tester</h1>
  <p class="subtitle">{{ config('app.name') }} — {{ app()->environment() }}</p>
  <nav id="sidebar-nav"></nav>
</aside>

<main id="main">
  <div id="topbar">
    <div class="field">
      <label>Base URL</label>
      <input id="base-url" type="text" value="">
    </div>
    <div class="field" style="min-width:320px;">
      <label>Token Bearer (se llena solo al hacer login)</label>
      <input id="token-input" type="text" placeholder="Pega un token o inicia sesión abajo">
    </div>
    <div>
      <label style="display:block;font-size:11px;color:var(--text-dim);margin-bottom:3px;">Sesión</label>
      <span id="session-badge">sin autenticar</span>
    </div>
    <button class="ghost" id="btn-clear-token">Limpiar token</button>
  </div>

  <div id="content"></div>

  <p class="empty-note">
    Esta vista solo debe estar disponible en entornos de desarrollo/pruebas
    (controlado por <code>API_TESTER_ENABLED</code> en <code>.env</code>).
    Ver <a class="footer-link" href="https://github.com" onclick="return false;">README del backend</a> para más contexto.
  </p>
</main>

<script>
(function () {
  'use strict';

  const TOKEN_KEY = 'api_tester_token';

  // ------------------------------------------------------------------
  // Definición de endpoints — una entrada por cada ruta real de la API.
  // Editar aquí para agregar/quitar endpoints del tester.
  // ------------------------------------------------------------------
  const ENDPOINTS = [
    {
      section: 'Salud del servidor',
      items: [
        {
          method: 'GET', path: '/api/health', auth: false,
          desc: 'Confirma que el backend levantó bien: PHP, Laravel, y conexión a base de datos.',
        },
      ],
    },
    {
      section: 'Autenticación',
      items: [
        {
          method: 'POST', path: '/api/login', auth: false,
          body: { identifier: 'C37190', password: 'password' },
          desc: 'Login con carnet o correo @ucr.ac.cr. Guarda el token automáticamente.',
        },
        { method: 'POST', path: '/api/logout', auth: true, desc: 'Revoca el token actual.' },
        { method: 'GET', path: '/api/me', auth: true, desc: 'Usuario autenticado actual.' },
      ],
    },
    {
      section: 'Contraseña (OTP)',
      items: [
        { method: 'POST', path: '/api/password/otp', auth: true, desc: 'Envía un código OTP de 6 dígitos al correo del usuario.' },
        {
          method: 'POST', path: '/api/password/otp/verificar', auth: true,
          body: { codigo: '123456' },
          desc: 'Verifica el código OTP recibido por correo.',
        },
        {
          method: 'PUT', path: '/api/password/cambiar', auth: true,
          body: { password: 'NuevaClave123!', password_confirmation: 'NuevaClave123!' },
          desc: 'Cambia la contraseña. Requiere OTP verificado antes.',
        },
      ],
    },
    {
      section: 'Eventos',
      items: [
        {
          method: 'GET', path: '/api/eventos', auth: true,
          query: [
            { name: 'dia', placeholder: '1, 2 o 3' },
            { name: 'tipo', placeholder: 'apertura | clausura | taller | charla' },
            { name: 'area_id', placeholder: 'id numérico' },
            { name: 'solo_disponibles', placeholder: 'true | false' },
          ],
          desc: 'Lista de eventos con filtros opcionales.',
        },
        { method: 'GET', path: '/api/eventos/{evento}', auth: true, params: ['evento'], desc: 'Detalle de un evento.' },
      ],
    },
    {
      section: 'Inscripciones',
      items: [
        {
          method: 'GET', path: '/api/inscripciones', auth: true,
          query: [{ name: 'estado', placeholder: 'confirmado | cancelado' }],
          desc: 'Inscripciones del usuario autenticado.',
        },
        {
          method: 'POST', path: '/api/inscripciones', auth: true,
          body: { evento_id: 1 },
          desc: 'Inscribe al usuario autenticado en un evento.',
        },
        { method: 'DELETE', path: '/api/inscripciones/{inscripcion}', auth: true, params: ['inscripcion'], desc: 'Cancela una inscripción propia.' },
      ],
    },
    {
      section: 'Admin · Usuarios',
      items: [
        { method: 'GET', path: '/api/admin/usuarios', auth: true, desc: 'Lista todos los usuarios.' },
        {
          method: 'POST', path: '/api/admin/usuarios', auth: true,
          body: { nombre: 'Nuevo Usuario', email: 'nuevo.usuario@ucr.ac.cr', carnet: 'A12345', password: null, tipo_usuario: 'participante' },
          desc: 'Crea un usuario. Con password null, el backend genera una automáticamente.',
        },
        { method: 'GET', path: '/api/admin/usuarios/{usuario}', auth: true, params: ['usuario'], desc: 'Detalle de un usuario.' },
        {
          method: 'PUT', path: '/api/admin/usuarios/{usuario}', auth: true, params: ['usuario'],
          body: { nombre: 'Nombre actualizado' },
          desc: 'Actualiza campos de un usuario (parcial — solo envía lo que quieras cambiar).',
        },
        { method: 'DELETE', path: '/api/admin/usuarios/{usuario}', auth: true, params: ['usuario'], desc: 'Elimina un usuario.' },
        { method: 'GET', path: '/api/admin/passwords/generar', auth: true, desc: 'Genera una contraseña aleatoria segura (no la asigna).' },
        { method: 'POST', path: '/api/admin/usuarios/{usuario}/enviar-correo', auth: true, params: ['usuario'], desc: 'Envía correo de bienvenida con credenciales.' },
        { method: 'POST', path: '/api/admin/correos/todos', auth: true, desc: 'Envía correo de bienvenida a todos los usuarios.' },
      ],
    },
    {
      section: 'Admin · Eventos',
      items: [
        { method: 'GET', path: '/api/admin/eventos', auth: true, desc: 'Lista todos los eventos (incluye inactivos).' },
        {
          method: 'POST', path: '/api/admin/eventos', auth: true,
          body: { titulo: 'Nuevo taller', descripcion: 'Descripción del evento', tipo: 'taller', capacidad: 30, esta_activo: true, horario_id: 1, ponente_ids: [1], area_ids: [1] },
          desc: 'Crea un evento. tipo: apertura | clausura | taller | charla.',
        },
        { method: 'GET', path: '/api/admin/eventos/{evento}', auth: true, params: ['evento'], desc: 'Detalle de un evento (vista admin).' },
        {
          method: 'PUT', path: '/api/admin/eventos/{evento}', auth: true, params: ['evento'],
          body: { titulo: 'Título actualizado' },
          desc: 'Actualiza un evento (parcial).',
        },
        { method: 'DELETE', path: '/api/admin/eventos/{evento}', auth: true, params: ['evento'], desc: 'Elimina un evento.' },
        { method: 'GET', path: '/api/admin/eventos/{evento}/inscritos', auth: true, params: ['evento'], desc: 'Usuarios inscritos en un evento.' },
      ],
    },
    {
      section: 'Admin · Horarios',
      items: [
        { method: 'GET', path: '/api/admin/horarios', auth: true, desc: 'Lista horarios.' },
        {
          method: 'POST', path: '/api/admin/horarios', auth: true,
          body: { aula_id: 1, numero_dia: 1, hora_inicio: '08:00', hora_fin: '09:00' },
          desc: 'Crea un horario. numero_dia: 1, 2 o 3.',
        },
        { method: 'GET', path: '/api/admin/horarios/{horario}', auth: true, params: ['horario'], desc: 'Detalle de un horario.' },
        {
          method: 'PUT', path: '/api/admin/horarios/{horario}', auth: true, params: ['horario'],
          body: { hora_inicio: '09:00', hora_fin: '10:00' },
          desc: 'Actualiza un horario (parcial).',
        },
        { method: 'DELETE', path: '/api/admin/horarios/{horario}', auth: true, params: ['horario'], desc: 'Elimina un horario.' },
      ],
    },
    {
      section: 'Admin · Aulas',
      items: [
        { method: 'GET', path: '/api/admin/aulas', auth: true, desc: 'Lista aulas.' },
        {
          method: 'POST', path: '/api/admin/aulas', auth: true,
          body: { numero: '101', edificio: 'Facultad de Ciencias Económicas', capacidad: 40 },
          desc: 'Crea un aula.',
        },
        { method: 'GET', path: '/api/admin/aulas/{aula}', auth: true, params: ['aula'], desc: 'Detalle de un aula.' },
        {
          method: 'PUT', path: '/api/admin/aulas/{aula}', auth: true, params: ['aula'],
          body: { capacidad: 50 },
          desc: 'Actualiza un aula (parcial).',
        },
        { method: 'DELETE', path: '/api/admin/aulas/{aula}', auth: true, params: ['aula'], desc: 'Elimina un aula.' },
      ],
    },
    {
      section: 'Admin · Ponentes',
      items: [
        { method: 'GET', path: '/api/admin/ponentes', auth: true, desc: 'Lista ponentes.' },
        {
          method: 'POST', path: '/api/admin/ponentes', auth: true,
          body: { nombre: 'Ana', apellidos: 'Jiménez Solano', educacion: 'Doctorado en Administración', grado_academico: 'PhD', descripcion: 'Especialista en transformación digital.' },
          desc: 'Crea un ponente.',
        },
        { method: 'GET', path: '/api/admin/ponentes/{ponente}', auth: true, params: ['ponente'], desc: 'Detalle de un ponente.' },
        {
          method: 'PUT', path: '/api/admin/ponentes/{ponente}', auth: true, params: ['ponente'],
          body: { descripcion: 'Descripción actualizada.' },
          desc: 'Actualiza un ponente (parcial).',
        },
        { method: 'DELETE', path: '/api/admin/ponentes/{ponente}', auth: true, params: ['ponente'], desc: 'Elimina un ponente.' },
      ],
    },
    {
      section: 'Admin · Áreas',
      items: [
        { method: 'GET', path: '/api/admin/areas', auth: true, desc: 'Lista áreas temáticas.' },
        {
          method: 'POST', path: '/api/admin/areas', auth: true,
          body: { nombre: 'Ciberseguridad', descripcion: 'Seguridad de la información', color: '#1E3A8A' },
          desc: 'Crea un área.',
        },
        { method: 'GET', path: '/api/admin/areas/{area}', auth: true, params: ['area'], desc: 'Detalle de un área.' },
        {
          method: 'PUT', path: '/api/admin/areas/{area}', auth: true, params: ['area'],
          body: { color: '#0EA5E9' },
          desc: 'Actualiza un área (parcial).',
        },
        { method: 'DELETE', path: '/api/admin/areas/{area}', auth: true, params: ['area'], desc: 'Elimina un área.' },
      ],
    },
  ];

  // ------------------------------------------------------------------
  // Estado: base URL + token
  // ------------------------------------------------------------------
  const baseUrlInput = document.getElementById('base-url');
  const tokenInput = document.getElementById('token-input');
  const sessionBadge = document.getElementById('session-badge');

  baseUrlInput.value = window.location.origin;

  function getToken() {
    return tokenInput.value.trim() || localStorage.getItem(TOKEN_KEY) || '';
  }
  function setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
    tokenInput.value = token;
  }
  function clearToken() {
    localStorage.removeItem(TOKEN_KEY);
    tokenInput.value = '';
    updateSessionBadge(null);
  }
  function updateSessionBadge(user) {
    if (user) {
      const rol = user.tipo_usuario ? ` · ${user.tipo_usuario}` : '';
      sessionBadge.textContent = `${user.email || user.nombre || 'autenticado'}${rol}`;
      sessionBadge.classList.add('active');
    } else {
      sessionBadge.textContent = 'sin autenticar';
      sessionBadge.classList.remove('active');
    }
  }

  const savedToken = localStorage.getItem(TOKEN_KEY);
  if (savedToken) {
    tokenInput.value = savedToken;
  }

  tokenInput.addEventListener('change', () => {
    if (tokenInput.value.trim()) {
      localStorage.setItem(TOKEN_KEY, tokenInput.value.trim());
    }
  });
  document.getElementById('btn-clear-token').addEventListener('click', clearToken);

  // ------------------------------------------------------------------
  // Render: sidebar + tarjetas
  // ------------------------------------------------------------------
  const nav = document.getElementById('sidebar-nav');
  const content = document.getElementById('content');

  function slug(text) {
    return text.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-');
  }

  ENDPOINTS.forEach((group, gi) => {
    const groupId = 'group-' + slug(group.section);

    const navTitle = document.createElement('span');
    navTitle.className = 'section-title';
    navTitle.textContent = group.section;
    nav.appendChild(navTitle);

    const section = document.createElement('section');
    section.className = 'group';
    section.id = groupId;
    const h2 = document.createElement('h2');
    h2.textContent = group.section;
    section.appendChild(h2);

    group.items.forEach((item, ii) => {
      const cardId = `${groupId}-${ii}`;

      const navLink = document.createElement('a');
      navLink.href = '#' + cardId;
      navLink.innerHTML = `<span class="m">${item.method}</span>${item.path}`;
      nav.appendChild(navLink);

      section.appendChild(buildCard(item, cardId, gi === 0 && ii === 0));
    });

    content.appendChild(section);
  });

  function buildCard(item, cardId, openByDefault) {
    const details = document.createElement('details');
    details.className = 'card';
    details.id = cardId;
    if (openByDefault) details.open = true;

    const summary = document.createElement('summary');
    summary.innerHTML = `
      <span class="badge-method ${item.method}">${item.method}</span>
      <span class="path">${item.path}</span>
      ${item.auth ? '<span class="lock" title="Requiere token Bearer">🔒</span>' : ''}
      <span class="desc">${item.desc || ''}</span>
    `;
    details.appendChild(summary);

    const body = document.createElement('div');
    body.className = 'card-body';

    // Path params
    (item.params || []).forEach((p) => {
      const row = document.createElement('div');
      row.className = 'field-row';
      row.innerHTML = `
        <div class="field">
          <label>Parámetro de ruta: {${p}}</label>
          <input type="text" data-param="${p}" placeholder="ID de ${p}">
        </div>
      `;
      body.appendChild(row);
    });

    // Query params
    if (item.query && item.query.length) {
      const row = document.createElement('div');
      row.className = 'field-row';
      row.innerHTML = item.query.map((q) => `
        <div class="field">
          <label>${q.name} (query, opcional)</label>
          <input type="text" data-query="${q.name}" placeholder="${q.placeholder || ''}">
        </div>
      `).join('');
      body.appendChild(row);
    }

    // Body JSON
    let bodyTextarea = null;
    if (item.body !== undefined) {
      const label = document.createElement('label');
      label.textContent = 'Cuerpo (JSON)';
      label.style.cssText = 'display:block;font-size:11px;color:var(--text-dim);margin:10px 0 4px;';
      body.appendChild(label);

      bodyTextarea = document.createElement('textarea');
      bodyTextarea.className = 'body-json';
      bodyTextarea.setAttribute('data-body', '1');
      bodyTextarea.value = JSON.stringify(item.body, null, 2);
      body.appendChild(bodyTextarea);
    }

    // Send row
    const sendRow = document.createElement('div');
    sendRow.className = 'send-row';
    const sendBtn = document.createElement('button');
    sendBtn.className = 'send';
    sendBtn.textContent = 'Enviar';
    sendRow.appendChild(sendBtn);

    const reqLine = document.createElement('span');
    reqLine.className = 'req-line';
    reqLine.textContent = item.auth ? 'Requiere Authorization: Bearer <token>' : 'Endpoint público';
    sendRow.appendChild(reqLine);

    body.appendChild(sendRow);

    // Response box
    const responseBox = document.createElement('div');
    responseBox.className = 'response-box';
    responseBox.innerHTML = `
      <div class="response-meta">
        <span class="status-pill" data-status></span>
        <span class="time-pill" data-time></span>
      </div>
      <pre class="response-json" data-response></pre>
    `;
    body.appendChild(responseBox);

    details.appendChild(body);

    sendBtn.addEventListener('click', () => sendRequest(item, details, sendBtn));

    return details;
  }

  // ------------------------------------------------------------------
  // Envío de peticiones
  // ------------------------------------------------------------------
  function escapeHtml(str) {
    return str.replace(/[&<>]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
  }

  function syntaxHighlight(json) {
    const text = typeof json === 'string' ? json : JSON.stringify(json, null, 2);
    const escaped = escapeHtml(text);
    return escaped.replace(
      /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false)\b|\bnull\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g,
      (match) => {
        let cls = 'jn';
        if (/^"/.test(match)) {
          cls = /:$/.test(match) ? 'jk' : 'js';
        } else if (/true|false/.test(match)) {
          cls = 'jb';
        } else if (/null/.test(match)) {
          cls = 'jnull';
        }
        return `<span class="${cls}">${match}</span>`;
      }
    );
  }

  async function sendRequest(item, cardEl, btn) {
    let path = item.path;
    let invalid = false;

    (item.params || []).forEach((p) => {
      const input = cardEl.querySelector(`[data-param="${p}"]`);
      const val = input.value.trim();
      if (!val) {
        input.style.borderColor = 'var(--err)';
        invalid = true;
      } else {
        input.style.borderColor = '';
      }
      path = path.replace(`{${p}}`, encodeURIComponent(val));
    });

    if (invalid) return;

    const qs = new URLSearchParams();
    (item.query || []).forEach((q) => {
      const input = cardEl.querySelector(`[data-query="${q.name}"]`);
      const val = input.value.trim();
      if (val !== '') qs.set(q.name, val);
    });
    const queryString = qs.toString();

    const headers = { Accept: 'application/json' };
    let body;

    if (item.body !== undefined) {
      const textarea = cardEl.querySelector('[data-body]');
      try {
        body = JSON.stringify(JSON.parse(textarea.value));
      } catch (e) {
        renderResponse(cardEl, 0, 0, { error: 'JSON inválido en el cuerpo: ' + e.message }, false);
        return;
      }
      headers['Content-Type'] = 'application/json';
    }

    if (item.auth) {
      const token = getToken();
      if (!token) {
        renderResponse(cardEl, 0, 0, { error: 'No hay token guardado. Inicia sesión primero con POST /api/login.' }, false);
        return;
      }
      headers.Authorization = `Bearer ${token}`;
    }

    const base = baseUrlInput.value.replace(/\/$/, '');
    const url = base + path + (queryString ? `?${queryString}` : '');

    btn.disabled = true;
    btn.textContent = 'Enviando…';

    const started = performance.now();
    try {
      const res = await fetch(url, { method: item.method, headers, body });
      const elapsed = Math.round(performance.now() - started);
      const text = await res.text();
      let json;
      try { json = text ? JSON.parse(text) : {}; } catch { json = text; }

      renderResponse(cardEl, res.status, elapsed, json, res.ok);

      if (item.path === '/api/login' && res.ok && json && json.token) {
        setToken(json.token);
        updateSessionBadge(json.user);
      }
      if (item.path === '/api/logout' && res.ok) {
        clearToken();
      }
      if (item.path === '/api/me' && res.ok && json) {
        updateSessionBadge(json.data || json);
      }
    } catch (e) {
      const elapsed = Math.round(performance.now() - started);
      renderResponse(cardEl, 0, elapsed, { error: 'No se pudo conectar: ' + e.message }, false);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Enviar';
    }
  }

  function renderResponse(cardEl, status, elapsed, json, ok) {
    const box = cardEl.querySelector('.response-box');
    const statusEl = box.querySelector('[data-status]');
    const timeEl = box.querySelector('[data-time]');
    const responseEl = box.querySelector('[data-response]');

    box.classList.add('shown');
    statusEl.textContent = status ? `HTTP ${status}` : 'Error de red';
    statusEl.className = 'status-pill ' + (ok ? 'ok' : 'err');
    timeEl.textContent = status ? `${elapsed} ms` : '';
    responseEl.innerHTML = syntaxHighlight(json);
  }

  // Si ya hay un token guardado, valida la sesión contra /api/me al cargar.
  (async function bootstrapSession() {
    const token = getToken();
    if (!token) return;
    try {
      const res = await fetch(baseUrlInput.value.replace(/\/$/, '') + '/api/me', {
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      if (res.ok) {
        const json = await res.json();
        updateSessionBadge(json.data || json);
      }
    } catch {
      // silencioso: si el backend no responde aún, el usuario lo verá al usar /api/health
    }
  })();
})();
</script>
</body>
</html>
