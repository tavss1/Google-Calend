<!doctype html>
<html lang="pt-br" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Calendário</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --bg0: #05080f;
      --stroke: rgba(148, 163, 184, .12);
      --text: #e6edf7;
      --muted: #8ea0b8;
      --blue: #2563eb;
      --blue2: #3b82f6;
    }
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      color: var(--text);
      background:
        linear-gradient(112deg, transparent 42%, rgba(37, 99, 235, .18) 48%, rgba(29, 78, 216, .26) 58%, transparent 66%),
        linear-gradient(112deg, transparent 62%, rgba(30, 58, 138, .32) 72%, transparent 88%),
        radial-gradient(900px 520px at 85% 115%, rgba(59, 130, 246, .30), transparent 60%),
        radial-gradient(700px 420px at 8% -10%, rgba(30, 64, 175, .25), transparent 60%),
        var(--bg0);
      background-attachment: fixed;
    }
    .app {
      max-width: 1220px;
      margin: 28px auto;
      min-height: calc(100vh - 56px);
      display: flex;
      background: linear-gradient(160deg, rgba(15, 23, 42, .92), rgba(8, 13, 26, .97));
      border: 1px solid var(--stroke);
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 40px 90px -36px rgba(0, 0, 0, .85);
    }

    /* ---------- sidebar ---------- */
    .side {
      width: 76px;
      padding: 22px 0 18px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      border-right: 1px solid var(--stroke);
      background: rgba(255, 255, 255, .02);
    }
    .side .logo { color: var(--blue2); margin-bottom: 16px; }
    .side a {
      width: 42px; height: 42px;
      display: grid; place-items: center;
      color: #64748b; border-radius: 14px;
      text-decoration: none;
      transition: background .15s, color .15s;
    }
    .side a:hover { color: #cbd5e1; background: rgba(148, 163, 184, .1); }
    .side a.ativo { background: var(--blue); color: #fff; box-shadow: 0 10px 26px -10px rgba(37, 99, 235, .95); }
    .side .fill { flex: 1; }
    .side .perfil {
      width: 38px; height: 38px;
      border-radius: 50%;
      background: linear-gradient(135deg, #3b82f6, #7c3aed);
      color: #fff; font-weight: 700; font-size: .72rem;
      display: grid; place-items: center;
      border: 2px solid rgba(148, 163, 184, .25);
    }

    /* ---------- main ---------- */
    .main { flex: 1; padding: 26px 28px 26px; display: flex; flex-direction: column; gap: 20px; min-width: 0; }
    .topo { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
    .topo h1 { font-size: 1.4rem; font-weight: 800; margin: 0; letter-spacing: -.01em; }
    .topo .sub { color: var(--muted); margin: 3px 0 0; font-size: .84rem; text-transform: capitalize; }
    .busca {
      display: flex; align-items: center; gap: 9px;
      background: rgba(148, 163, 184, .08);
      border: 1px solid var(--stroke);
      padding: 9px 15px; border-radius: 999px; width: 300px; color: var(--muted);
    }
    .busca input { background: transparent; border: 0; outline: 0; color: var(--text); width: 100%; font-size: .85rem; }
    .busca input::placeholder { color: #64748b; }
    .sino {
      position: relative; width: 42px; height: 42px;
      display: grid; place-items: center;
      background: rgba(148, 163, 184, .08);
      border: 1px solid var(--stroke); border-radius: 50%;
      color: #94a3b8;
    }
    .sino .ponto { position: absolute; top: 9px; right: 10px; width: 7px; height: 7px; border-radius: 50%; background: #ef4444; border: 2px solid #0b1120; }

    .conteudo { display: grid; grid-template-columns: minmax(0, 1fr) 302px; gap: 18px; flex: 1; align-items: start; min-height: 0; }
    .cartao {
      background: rgba(13, 21, 38, .78);
      border: 1px solid var(--stroke);
      border-radius: 20px;
      padding: 16px;
    }

    /* ---------- calendário ---------- */
    .cal-topo { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
    .legenda { display: flex; align-items: center; gap: 14px; font-size: .74rem; color: var(--muted); }
    .legenda i { display: inline-block; width: .55rem; height: .55rem; border-radius: 50%; margin-right: .38rem; }

    .fc {
      --fc-border-color: transparent;
      --fc-page-bg-color: transparent;
      --fc-neutral-bg-color: transparent;
      --fc-neutral-text-color: #8ea0b8;
      --fc-today-bg-color: transparent;
      --fc-event-bg-color: #2563eb;
      --fc-event-border-color: #2563eb;
      --fc-event-text-color: #fff;
      --fc-button-bg-color: transparent;
      --fc-button-border-color: transparent;
      --fc-button-text-color: #94a3b8;
      --fc-button-hover-bg-color: rgba(148, 163, 184, .14);
      --fc-button-hover-border-color: transparent;
      --fc-button-active-bg-color: #2563eb;
      --fc-button-active-border-color: #2563eb;
      --fc-list-event-hover-bg-color: rgba(148, 163, 184, .1);
      font-size: .88rem;
    }
    .fc .fc-toolbar { align-items: center; gap: 6px; margin-bottom: 12px; }
    .fc .fc-toolbar-title { font-size: 1.18rem; font-weight: 800; letter-spacing: -.01em; text-transform: capitalize; }
    .fc .fc-button { border-radius: 11px; padding: .38rem .65rem; font-size: .78rem; font-weight: 600; box-shadow: none !important; }
    .fc .fc-button-active { color: #fff !important; }
    .fc .fc-prev-button, .fc .fc-next-button { border-radius: 50% !important; width: 32px; height: 32px; padding: 0; }
    .fc .fc-today-button { border: 1px solid var(--stroke) !important; border-radius: 999px !important; padding-left: .8rem; padding-right: .8rem; }
    .fc-theme-standard td, .fc-theme-standard th { border: 0; }
    .fc-theme-standard .fc-scrollgrid { border: 0; }
    .fc .fc-col-header-cell-cushion { color: #7c8ba3; font-size: .72rem; font-weight: 600; text-transform: capitalize; padding: 6px 0 8px; }
    .fc .fc-daygrid-day-frame { margin: 3px; border-radius: 14px; background: rgba(148, 163, 184, .05); min-height: 88px; transition: background .15s; }
    .fc .fc-daygrid-day:hover .fc-daygrid-day-frame { background: rgba(148, 163, 184, .1); }
    .fc .fc-daygrid-day-top { flex-direction: row; padding: 4px 0 0 3px; }
    .fc .fc-daygrid-day-number { font-size: .78rem; font-weight: 600; color: #8ea0b8; padding: 2px 6px; border-radius: 8px; }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-frame { background: linear-gradient(180deg, rgba(37, 99, 235, .4), rgba(37, 99, 235, .1)); }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number { color: #fff; background: var(--blue); }
    .fc .fc-daygrid-day.dia-sel .fc-daygrid-day-frame { outline: 1.5px solid rgba(96, 165, 250, .85); outline-offset: -1px; }
    .fc .fc-day-other .fc-daygrid-day-frame { background: transparent; }
    .fc .fc-day-other .fc-daygrid-day-number { opacity: .35; }
    .fc .fc-daygrid-dot-event { padding: 1px 5px; border-radius: 8px; }
    .fc .fc-daygrid-dot-event .fc-daygrid-event-dot { border-width: 5px; margin-right: 5px; }
    .fc .fc-daygrid-dot-event .fc-event-title { color: #b9c6da; font-size: .71rem; font-weight: 500; }
    .fc .fc-daygrid-block-event { border: 0; border-radius: 8px; padding: 1px 7px; font-size: .71rem; font-weight: 600; }
    .fc .fc-event.dim { opacity: .2; }
    .fc .fc-more-link { color: var(--blue2); font-size: .72rem; font-weight: 600; }
    .fc .fc-popover { background: #0e1628; border: 1px solid var(--stroke); border-radius: 14px; }
    .fc .fc-popover-header { background: transparent; color: var(--text); font-size: .8rem; }

    /* ---------- semana (timeGrid) ---------- */
    .fc .fc-timegrid-slot { height: 26px; }
    .fc .fc-timegrid-slot, .fc .fc-timegrid-slot-lane, .fc .fc-timegrid-axis,
    .fc .fc-timegrid-slot-label { border: 0 !important; }
    .fc .fc-timegrid-slot-label { vertical-align: top; }
    .fc .fc-timegrid-slot-label-cushion { color: #64748b; font-size: .68rem; font-weight: 600; padding: 0 10px 0 0; }
    .fc .fc-timegrid-col-frame {
      border: 0 solid transparent;
      border-width: 0 4px;
      border-radius: 14px;
      background-image: repeating-linear-gradient(to bottom, transparent 0 25px, rgba(148, 163, 184, .10) 25px 26px);
      background-color: rgba(148, 163, 184, .045);
      background-clip: padding-box;
      overflow: hidden;
    }
    .fc .fc-timegrid-col.fc-day-today .fc-timegrid-col-frame {
      background-image:
        repeating-linear-gradient(to bottom, transparent 0 25px, rgba(148, 163, 184, .12) 25px 26px),
        linear-gradient(180deg, rgba(37, 99, 235, .32), rgba(37, 99, 235, .05));
      background-color: transparent;
      background-clip: padding-box;
    }
    .fc .fc-timegrid-col.dia-sel .fc-timegrid-col-frame { box-shadow: inset 0 0 0 1.5px rgba(96, 165, 250, .85); }
    .fc .fc-timegrid-col.fc-day-today .fc-col-header-cell-cushion { color: #93c5fd; }
    .fc .fc-timegrid-now-indicator-line { border-color: #ef4444; border-width: 2px 0 0; }
    .fc .fc-timegrid-now-indicator-arrow { border-color: #ef4444; background: #ef4444; }
    .fc .fc-timegrid-event { border: 0; border-radius: 8px; padding: 2px 5px; box-shadow: 0 8px 18px -10px rgba(0, 0, 0, .8); }
    .fc .fc-timegrid-event .fc-event-main { font-size: .72rem; font-weight: 600; }
    .fc .fc-timegrid-event .fc-event-time { font-size: .68rem; opacity: .85; }
    .fc .fc-timegrid-allday .fc-daygrid-day-frame { min-height: 40px; }

    /* ---------- painel agendado ---------- */
    .painel { display: flex; flex-direction: column; gap: 12px; min-height: 400px; }
    .painel-topo { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .painel-topo h2 { font-size: 1rem; font-weight: 800; margin: 0; }
    .painel-topo p { margin: 2px 0 0; font-size: .72rem; color: var(--muted); }
    .painel-nav { display: flex; gap: 6px; }
    .painel-nav button {
      width: 30px; height: 30px; border-radius: 10px;
      background: rgba(148, 163, 184, .08);
      border: 1px solid var(--stroke); color: #94a3b8;
      display: grid; place-items: center; font-size: .95rem; line-height: 1;
    }
    .painel-nav button:hover { color: #fff; background: rgba(148, 163, 184, .16); }
    .lista-dia { display: flex; flex-direction: column; gap: 10px; overflow: auto; max-height: 620px; padding-right: 3px; }
    .lista-dia::-webkit-scrollbar { width: 6px; }
    .lista-dia::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, .2); border-radius: 6px; }
    .sc-card {
      position: relative;
      border-radius: 14px;
      background: rgba(148, 163, 184, .07);
      border: 1px solid var(--stroke);
      padding: 13px 12px 11px;
      cursor: pointer;
      overflow: hidden;
      transition: background .15s, transform .15s;
    }
    .sc-card:hover { background: rgba(148, 163, 184, .12); transform: translateY(-1px); }
    .sc-card::before {
      content: '';
      position: absolute; top: 0; left: 16px; right: 16px; height: 4px;
      border-radius: 0 0 6px 6px;
      background: var(--c, var(--blue2));
    }
    .sc-hora { display: flex; justify-content: space-between; align-items: center; font-size: .68rem; color: var(--muted); font-weight: 600; letter-spacing: .02em; }
    .sc-hora .dur { color: #64748b; font-weight: 500; }
    .sc-titulo { font-weight: 700; font-size: .86rem; margin-top: 3px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .sc-sub { font-size: .73rem; color: var(--muted); margin-top: 3px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .sc-meta { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: .72rem; color: var(--muted); min-height: 20px; }
    .sc-erro { margin-left: auto; color: #f87171; font-weight: 700; font-size: .66rem; }
    .sc-vazio { color: #64748b; font-size: .8rem; text-align: center; padding: 28px 8px; border: 1px dashed var(--stroke); border-radius: 14px; }
    .avatars { display: flex; align-items: center; }
    .avatars .av {
      width: 21px; height: 21px; border-radius: 50%;
      display: grid; place-items: center;
      font-size: .58rem; font-weight: 700; color: #fff;
      border: 2px solid #0d1526; margin-left: -6px;
    }
    .avatars .av:first-child { margin-left: 0; }
    .avatars .mais { font-size: .68rem; margin-left: 5px; color: var(--muted); }

    /* ---------- bootstrap overrides ---------- */
    .modal-content { background: #0e1628; border: 1px solid var(--stroke); border-radius: 18px; }
    .modal-header, .modal-footer { border-color: var(--stroke); }
    .form-control, .form-select {
      background: rgba(148, 163, 184, .07);
      border: 1px solid var(--stroke);
      color: var(--text);
      border-radius: 10px;
    }
    .form-control:focus, .form-select:focus {
      background: rgba(148, 163, 184, .1);
      border-color: var(--blue2);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, .2);
      color: var(--text);
    }
    .form-control::placeholder { color: #64748b; }
    .btn-primary { background: var(--blue); border-color: var(--blue); }
    .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
    .btn-light { background: rgba(148, 163, 184, .12); border-color: transparent; color: #cbd5e1; }
    .btn-light:hover { background: rgba(148, 163, 184, .2); color: #fff; }
    #toast { position: fixed; top: 18px; right: 18px; z-index: 2000; max-width: 24rem; display: flex; flex-direction: column; gap: 8px; }
    @media (max-width: 1080px) {
      .conteudo { grid-template-columns: 1fr; }
      .side { display: none; }
      .app { margin: 12px; border-radius: 20px; }
    }
  </style>
</head>
<body>
<div class="app">
  <aside class="side">
    <span class="logo" aria-hidden="true">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="4"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </span>
    <a href="#" title="Dashboard" aria-label="Dashboard">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
    </a>
    <a href="<?= site_url('calendario') ?>" class="ativo" title="Calendário" aria-label="Calendário">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="4"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </a>
    <a href="#" title="Pessoas" aria-label="Pessoas">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </a>
    <a href="#" title="Relatórios" aria-label="Relatórios">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    </a>
    <a href="#" title="Configurações" aria-label="Configurações">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
    </a>
    <span class="fill"></span>
    <a href="<?= site_url('logout') ?>" title="Sair" aria-label="Sair">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
    <a href="<?= site_url('logout') ?>" class="perfil" id="perfilIniciais" title="Sair"></a>
  </aside>

  <main class="main">
    <header class="topo">
      <div>
        <h1 id="saudacao">Olá!</h1>
        <p class="sub" id="dataHoje"></p>
      </div>
      <div class="d-flex align-items-center gap-3">
        <label class="busca">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input id="busca" placeholder="Buscar eventos..." autocomplete="off">
        </label>
        <button class="sino" title="Notificações" aria-label="Notificações">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="ponto"></span>
        </button>
      </div>
    </header>

    <div class="conteudo">
      <section class="cartao">
        <div class="cal-topo">
          <div class="legenda">
            <span><i style="background:#0d6efd"></i>Interno</span>
            <span><i style="background:#198754"></i>Externo</span>
            <span><i style="background:#fd7e14"></i>Aniversário</span>
          </div>
          <?php if ($isAdm): ?>
            <button class="btn btn-primary btn-sm" id="btnNovo">+ Novo evento</button>
          <?php endif; ?>
        </div>
        <div id="calendario"></div>
      </section>

      <aside class="cartao painel">
        <div class="painel-topo">
          <div>
            <h2>Agendado</h2>
            <p id="painelData"></p>
          </div>
          <div class="painel-nav">
            <button id="btnDiaPrev" title="Dia anterior" aria-label="Dia anterior">‹</button>
            <button id="btnDiaNext" title="Próximo dia" aria-label="Próximo dia">›</button>
          </div>
        </div>
        <div id="listaDia" class="lista-dia"></div>
      </aside>
    </div>
  </main>
</div>

<div id="toast"></div>

<!-- Detalhes (leitura: todos os usuários) -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h2 class="modal-title h5" id="detTitulo"></h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
    <div class="modal-body">
      <p class="mb-1"><span class="badge text-bg-secondary" id="detTipo"></span></p>
      <p class="mb-1" id="detQuando"></p>
      <p class="mb-1 d-none" id="detLocal"></p>
      <p class="mb-0 mt-3 d-none" id="detDescricao" style="white-space:pre-wrap"></p>
    </div>
  </div></div>
</div>

<!-- Formulário (somente ADM) -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h2 class="modal-title h5" id="formTitulo">Novo evento</h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
    <div class="modal-body">
      <div id="msg" class="alert alert-danger d-none" role="alert"></div>
      <input type="hidden" id="id">

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label" for="titulo">Título</label>
          <input class="form-control" id="titulo" maxlength="200">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="tipo">Tipo</label>
          <select class="form-select" id="tipo">
            <option value="interno">Evento interno</option>
            <option value="externo">Evento externo</option>
            <option value="aniversario">Aniversário</option>
          </select>
        </div>

        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="dia_inteiro">
            <label class="form-check-label" for="dia_inteiro">Dia inteiro</label>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="inicio" id="labelInicio">Início</label>
          <input class="form-control" type="datetime-local" id="inicio">
        </div>
        <div class="col-md-6" id="fimWrap">
          <label class="form-label" for="fim">Fim</label>
          <input class="form-control" type="datetime-local" id="fim">
        </div>

        <div class="col-12">
          <label class="form-label" for="local">Local</label>
          <input class="form-control" id="local" maxlength="255">
        </div>
        <div class="col-12">
          <label class="form-label" for="descricao">Descrição</label>
          <textarea class="form-control" id="descricao" rows="3"></textarea>
        </div>

        <div class="col-12" id="convWrap">
          <label class="form-label" for="convInput">Convidados</label>
          <input class="form-control" id="convInput" list="convLista" autocomplete="off"
                 placeholder="Busque por nome ou digite um e-mail e tecle Enter">
          <datalist id="convLista"></datalist>
          <div class="form-text">Quem não está cadastrado no sistema é tratado como convidado externo e recebe o convite por e-mail.</div>
          <div id="chips" class="d-flex flex-wrap gap-2 mt-2"></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline-danger me-auto d-none" id="btnExcluir">Excluir</button>
      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
      <button type="button" class="btn btn-primary" id="btnSalvar">Salvar evento</button>
    </div>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/pt-br.global.min.js"></script>
<script>
const URL_BASE = <?= json_encode(base_url('calendario')) ?>;
const CSRF_HEADER = <?= json_encode(csrf_header()) ?>;
let csrf = <?= json_encode(csrf_hash()) ?>;
const IS_ADM = <?= $isAdm ? 'true' : 'false' ?>;
const USUARIO = <?= json_encode($nome ?? '') ?>;
const TIPOS = { interno: 'Evento interno', externo: 'Evento externo', aniversario: 'Aniversário' };
const CORES = { interno: '#0d6efd', externo: '#198754', aniversario: '#fd7e14' };
const $ = (id) => document.getElementById(id);

const iso = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
const iniciais = (nome) => (nome || '?').trim().split(/\s+/).slice(0, 2).map((p) => p[0]).join('').toUpperCase();
const CORES_AV = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4'];
const corAv = (s) => CORES_AV[[...(s || '?')].reduce((a, c) => a + c.charCodeAt(0), 0) % CORES_AV.length];
const durTxt = (min) => min < 60 ? min + ' min' : Math.floor(min / 60) + ' h' + (min % 60 ? ' ' + (min % 60) + ' min' : '');
const hora = (d) => d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

let diaSelecionado = iso(new Date());
let buscaQ = '';

function saudacao() {
  const h = new Date().getHours();
  return h < 12 ? 'Bom dia' : h < 18 ? 'Boa tarde' : 'Boa noite';
}
$('saudacao').textContent = saudacao() + (USUARIO ? ', ' + USUARIO.split(' ')[0] : '') + '!';
$('dataHoje').textContent = 'Veja o que está na sua agenda hoje · ' + new Date().toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' });
$('perfilIniciais').textContent = iniciais(USUARIO);

function aviso(texto, tipo = 'success') {
  const d = document.createElement('div');
  d.className = 'alert alert-' + tipo + ' shadow-sm';
  d.textContent = texto;
  $('toast').appendChild(d);
  setTimeout(() => d.remove(), 5000);
}

async function api(path, body) {
  const res = await fetch(URL_BASE + path, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', [CSRF_HEADER]: csrf },
    body: JSON.stringify(body ?? {}),
  });
  const data = await res.json().catch(() => ({ erro: 'Erro inesperado (' + res.status + ').' }));
  if (data.csrf) csrf = data.csrf;
  if (!res.ok) throw data;
  return data;
}

// ---------- Calendário ----------
const cal = new FullCalendar.Calendar($('calendario'), {
  locale: 'pt-br',
  initialView: 'dayGridMonth',
  height: 'auto',
  dayMaxEvents: 3,
  headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listMonth' },
  events: URL_BASE + '/feed',
  eventClick(info) {
    info.jsEvent.preventDefault();
    abrirEvento(info.event);
  },
  dateClick(info) {
    diaSelecionado = info.dateStr.slice(0, 10);
    marcarDia(); renderDia(diaSelecionado, buscaQ);
    if (IS_ADM && window.abrirForm) window.abrirForm(null, info.dateStr.slice(0, 10));
  },
  eventsSet() { setTimeout(() => { marcarDia(); aplicarBusca(); renderDia(diaSelecionado, buscaQ); }, 0); },
  datesSet() { setTimeout(() => { marcarDia(); }, 0); },
  eventSourceFailure() { aviso('Não foi possível carregar os eventos.', 'danger'); },
});
cal.render();

function marcarDia() {
  document.querySelectorAll('.fc-daygrid-day[data-date], .fc-timegrid-col[data-date]').forEach((td) => {
    td.classList.toggle('dia-sel', td.getAttribute('data-date') === diaSelecionado);
  });
}

function mudarDia(delta) {
  const d = new Date(diaSelecionado + 'T12:00:00');
  d.setDate(d.getDate() + delta);
  diaSelecionado = iso(d);
  cal.gotoDate(diaSelecionado);
  marcarDia(); renderDia(diaSelecionado, buscaQ);
}
$('btnDiaPrev').onclick = () => mudarDia(-1);
$('btnDiaNext').onclick = () => mudarDia(1);

function mesmoTexto(ev) {
  const p = ev.extendedProps || {};
  return ((ev.title || '') + ' ' + (p.descricao || '') + ' ' + (p.local || '')).toLowerCase();
}
function aplicarBusca() {
  cal.getEvents().forEach((ev) => {
    if (!ev.el) return;
    ev.el.classList.toggle('dim', buscaQ !== '' && !mesmoTexto(ev).includes(buscaQ));
  });
}
$('busca').addEventListener('input', () => {
  buscaQ = $('busca').value.trim().toLowerCase();
  aplicarBusca();
  renderDia(diaSelecionado, buscaQ);
});

function cartaoDia(ev) {
  const p = ev.extendedProps || {};
  const el = document.createElement('div');
  el.className = 'sc-card';
  el.style.setProperty('--c', ev.backgroundColor || CORES[p.tipo] || '#6c757d');

  const topo = document.createElement('div');
  topo.className = 'sc-hora';
  const periodo = document.createElement('span');
  const d = document.createElement('span');
  d.className = 'dur';

  if (ev.allDay) {
    periodo.textContent = 'Dia inteiro';
  } else {
    const min = Math.max(1, Math.round(((ev.end || ev.start) - ev.start) / 60000));
    periodo.textContent = hora(ev.start) + (ev.end ? ' – ' + hora(ev.end) : '');
    d.textContent = durTxt(min);
  }
  topo.append(periodo, d);

  const titulo = document.createElement('div');
  titulo.className = 'sc-titulo';
  titulo.textContent = ev.title;
  el.append(topo, titulo);

  const sub = p.local || p.descricao;
  if (sub) {
    const s = document.createElement('div');
    s.className = 'sc-sub';
    s.textContent = sub;
    el.appendChild(s);
  }

  const meta = document.createElement('div');
  meta.className = 'sc-meta';
  if (p.convidados && p.convidados.length) {
    const avs = document.createElement('div');
    avs.className = 'avatars';
    p.convidados.slice(0, 4).forEach((c) => {
      const a = document.createElement('span');
      a.className = 'av';
      a.style.background = corAv(c.email);
      a.textContent = iniciais(c.nome || c.email);
      a.title = c.email;
      avs.appendChild(a);
    });
    meta.appendChild(avs);
    const t = document.createElement('span');
    t.textContent = p.convidados.length + (p.convidados.length === 1 ? ' convidado' : ' convidados');
    meta.appendChild(t);
  } else if (p.tipo === 'aniversario') {
    const t = document.createElement('span');
    t.textContent = 'Aniversário';
    meta.appendChild(t);
  }
  if (IS_ADM && p.sync === 'erro') {
    const se = document.createElement('span');
    se.className = 'sc-erro';
    se.title = 'Falha ao sincronizar com o Google — reprocesse com calendario:sync';
    se.textContent = 'sync';
    meta.appendChild(se);
  }
  if (meta.childNodes.length) el.appendChild(meta);

  el.onclick = () => abrirEvento(ev);
  return el;
}

function renderDia(dia, q) {
  const box = $('listaDia');
  box.replaceChildren();
  const inicio = new Date(dia + 'T00:00:00');
  const fim = new Date(inicio); fim.setDate(fim.getDate() + 1);
  const evs = cal.getEvents().filter((ev) => {
    if (!ev.start) return false;
    const e = ev.end || new Date(ev.start.getTime() + (ev.allDay ? 864e5 : 36e5));
    return e > inicio && ev.start < fim;
  }).sort((a, b) => a.start - b.start);
  const visiveis = q ? evs.filter((ev) => mesmoTexto(ev).includes(q)) : evs;

  $('painelData').textContent = inicio.toLocaleDateString('pt-BR', { day: 'numeric', month: 'long', year: 'numeric' });
  if (!visiveis.length) {
    const vazio = document.createElement('div');
    vazio.className = 'sc-vazio';
    vazio.textContent = q ? 'Nenhum evento para a busca.' : 'Nenhum evento neste dia.';
    box.appendChild(vazio);
    return;
  }
  visiveis.forEach((ev) => box.appendChild(cartaoDia(ev)));
}

function abrirEvento(ev) {
  diaSelecionado = iso(ev.start);
  marcarDia(); renderDia(diaSelecionado, buscaQ);
  const p = ev.extendedProps || {};
  if (IS_ADM && p.eventoId && window.abrirForm) window.abrirForm(p);
  else abrirDetalhes(ev);
}

// ---------- Detalhes ----------
const modalDet = new bootstrap.Modal($('modalDetalhes'));
function abrirDetalhes(ev) {
  const p = ev.extendedProps;
  $('detTitulo').textContent = p.titulo || ev.title;
  $('detTipo').textContent = TIPOS[p.tipo] || p.tipo;

  let quando;
  if (p.tipo === 'aniversario') {
    quando = ev.start.toLocaleDateString('pt-BR', { day: 'numeric', month: 'long' });
  } else if (p.dia_inteiro) {
    const fim = new Date(ev.end ?? ev.start); fim.setDate(fim.getDate() - (ev.end ? 1 : 0));
    const a = ev.start.toLocaleDateString('pt-BR', { dateStyle: 'full' });
    const b = fim.toLocaleDateString('pt-BR', { dateStyle: 'full' });
    quando = a === b ? a : a + ' até ' + b;
  } else {
    quando = ev.start.toLocaleString('pt-BR', { dateStyle: 'full', timeStyle: 'short' });
    if (ev.end) quando += ' até ' + ev.end.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
  }
  $('detQuando').textContent = quando;

  for (const [id, valor, prefixo] of [['detLocal', p.local, 'Local: '], ['detDescricao', p.descricao, '']]) {
    $(id).textContent = valor ? prefixo + valor : '';
    $(id).classList.toggle('d-none', !valor);
  }
  modalDet.show();
}

if (IS_ADM) {
  // ---------- Formulário ----------
  const modalForm = new bootstrap.Modal($('modalForm'));
  let convidados = [];
  const conhecidos = {};
  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function ajustarCampos() {
    const aniv = $('tipo').value === 'aniversario';
    if (aniv) $('dia_inteiro').checked = true;
    $('dia_inteiro').disabled = aniv;
    const dia = $('dia_inteiro').checked;
    for (const id of ['inicio', 'fim']) {
      const el = $(id), v = el.value;
      el.type = dia ? 'date' : 'datetime-local';
      el.value = v ? (dia ? v.slice(0, 10) : (v.length === 10 ? v + 'T09:00' : v)) : '';
    }
    $('fimWrap').classList.toggle('d-none', aniv);
    $('convWrap').classList.toggle('d-none', aniv);
    $('labelInicio').textContent = aniv ? 'Data (repete todo ano)' : 'Início';
  }
  $('tipo').addEventListener('change', ajustarCampos);
  $('dia_inteiro').addEventListener('change', ajustarCampos);

  function mostrarErro(e) {
    const m = e.erros ? Object.values(e.erros).join(' ') : (e.erro || 'Erro ao salvar.');
    $('msg').textContent = m;
    $('msg').classList.remove('d-none');
  }

  function renderChips() {
    const box = $('chips');
    box.replaceChildren();
    convidados.forEach((c, i) => {
      const s = document.createElement('span');
      s.className = 'badge text-bg-' + (c.externo ? 'success' : 'primary') + ' d-flex align-items-center gap-2 fw-normal';
      const t = document.createElement('span');
      t.textContent = (c.nome ? c.nome + ' · ' : '') + c.email + (c.externo ? ' (externo)' : '');
      const x = document.createElement('button');
      x.type = 'button'; x.className = 'btn-close btn-close-white'; x.style.fontSize = '.6rem';
      x.setAttribute('aria-label', 'Remover ' + c.email);
      x.onclick = () => { convidados.splice(i, 1); renderChips(); };
      s.append(t, x);
      box.appendChild(s);
    });
  }

  function addConvidado(email, nome, externo) {
    email = email.trim().toLowerCase();
    if (!EMAIL_RE.test(email)) return false;
    if (!convidados.some((c) => c.email === email)) {
      const k = conhecidos[email];
      convidados.push({ email, nome: nome || k?.nome || '', externo: externo ?? !k });
      renderChips();
    }
    return true;
  }

  let t;
  $('convInput').addEventListener('input', (ev) => {
    clearTimeout(t);
    const q = ev.target.value.trim();
    if (q.length < 2 || q.includes('@') && EMAIL_RE.test(q)) return;
    t = setTimeout(async () => {
      try {
        const rows = await (await fetch(URL_BASE + '/usuarios?q=' + encodeURIComponent(q))).json();
        $('convLista').replaceChildren(...rows.map((u) => {
          conhecidos[u.email.toLowerCase()] = { nome: u.nome };
          const o = document.createElement('option'); o.value = u.email; o.label = u.nome; return o;
        }));
      } catch (_) { /* autocomplete é opcional */ }
    }, 250);
  });
  $('convInput').addEventListener('keydown', (ev) => {
    if (ev.key === 'Enter' || ev.key === ',') {
      ev.preventDefault();
      if (addConvidado(ev.target.value)) ev.target.value = '';
      else if (ev.target.value.trim()) aviso('E-mail inválido.', 'warning');
    }
  });
  $('convInput').addEventListener('change', (ev) => { if (addConvidado(ev.target.value)) ev.target.value = ''; });

  function abrirForm(p, dataPadrao) {
    $('msg').classList.add('d-none');
    $('formTitulo').textContent = p ? 'Editar evento' : 'Novo evento';
    $('btnExcluir').classList.toggle('d-none', !p);
    convidados = [];

    $('id').value = p ? p.eventoId : '';
    $('titulo').value = p ? p.titulo : '';
    $('tipo').value = p ? p.tipo : 'interno';
    $('local').value = p?.local ?? '';
    $('descricao').value = p?.descricao ?? '';
    $('dia_inteiro').checked = p ? !!p.dia_inteiro : !!dataPadrao;
    $('inicio').value = ''; $('fim').value = '';
    ajustarCampos();

    const dia = $('dia_inteiro').checked;
    const fmt = (s) => (s ? (dia ? s.slice(0, 10) : s.replace(' ', 'T').slice(0, 16)) : '');
    $('inicio').value = p ? fmt(p.inicio) : (dataPadrao ? (dia ? dataPadrao : dataPadrao + 'T09:00') : '');
    $('fim').value = p ? fmt(p.fim) : '';

    (p?.convidados ?? []).forEach((c) => addConvidado(c.email, c.nome, c.externo));
    renderChips();
    $('convInput').value = '';
    modalForm.show();
  }
  window.abrirForm = abrirForm;
  $('btnNovo').onclick = () => abrirForm(null);

  $('btnSalvar').onclick = async () => {
    addConvidado($('convInput').value) && ($('convInput').value = '');
    const btn = $('btnSalvar'); btn.disabled = true;
    try {
      const r = await api('/salvar', {
        id: $('id').value || null,
        titulo: $('titulo').value,
        tipo: $('tipo').value,
        dia_inteiro: $('dia_inteiro').checked,
        inicio: $('inicio').value,
        fim: $('fim').value || null,
        local: $('local').value,
        descricao: $('descricao').value,
        convidados,
      });
      modalForm.hide(); cal.refetchEvents();
      r.aviso ? aviso(r.aviso, 'warning') : aviso('Evento salvo.');
    } catch (e) { mostrarErro(e); }
    btn.disabled = false;
  };

  $('btnExcluir').onclick = async () => {
    if (!confirm('Excluir este evento? Os convidados serão avisados do cancelamento.')) return;
    try {
      await api('/excluir/' + $('id').value);
      modalForm.hide(); cal.refetchEvents(); aviso('Evento excluído.');
    } catch (e) { mostrarErro(e); }
  };
}
</script>
</body>
</html>
