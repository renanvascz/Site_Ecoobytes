<?php
// ============================================================
// PROCESSAMENTO DO FORMULÁRIO (só executa quando o método é POST,
// ou seja, quando o usuário realmente clicou em "enviar")
// ============================================================

$mensagem = '';
$tipoMsg  = ''; // 'sucesso' ou 'erro'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli(
    'localhost', // servidor
    'ecoo',      // usuário
    '1234',      // senha
    'ecoobytes'  // banco de dados
    );

    if ($conn->connect_error) {
        $mensagem = 'Não foi possível conectar ao banco de dados. Tente novamente mais tarde.';
        $tipoMsg  = 'erro';
    } else {

        // Pega os dados do formulário com segurança (evita "Undefined array key")
        $nome      = trim($_POST['nome'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telefone  = trim($_POST['telefone'] ?? '');
        $cep       = trim($_POST['cep'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $metodo    = trim($_POST['metodo'] ?? 'entrega');

        // Validação no servidor (nunca confie só na validação do JavaScript)
        if ($nome === '' || $email === '' || $telefone === '' || $descricao === '') {
            $mensagem = 'Por favor, preencha todos os campos obrigatórios (*).';
            $tipoMsg  = 'erro';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'Informe um e-mail válido.';
            $tipoMsg  = 'erro';
        } else {

            // Prepared statement: evita injeção de SQL (nunca concatene $_POST direto na query)
            $stmt = $conn->prepare(
                'INSERT INTO doacoes (nome, email, telefone, cep, descricao, metodo) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ssssss', $nome, $email, $telefone, $cep, $descricao, $metodo);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                // Padrão Post/Redirect/Get: evita reenvio duplicado se o usuário atualizar a página
                header('Location: doacao.php?status=sucesso');
                exit;
            } else {
                $mensagem = 'Ocorreu um erro ao salvar sua doação. Tente novamente.';
                $tipoMsg  = 'erro';
            }
            $stmt->close();
        }
        $conn->close();
    }
}

// Mensagem de sucesso após o redirecionamento
if (isset($_GET['status']) && $_GET['status'] === 'sucesso') {
    $mensagem = 'Agendamento enviado com sucesso! Nossa equipe entrará em contato em breve.';
    $tipoMsg  = 'sucesso';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EcooBytes — Agendar Doação</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:       #090d1a;
      --bg-nav:   #0b0f1e;
      --bg-card:  #0e1428;
      --bg-input: #111829;
      --green:    #35d46a;
      --green-lt: #7dffad;
      --green-dk: #1fa34a;
      --white:    #ffffff;
      --muted:    #b0bac8;
      --border:   rgba(255,255,255,0.09);
    }

    html, body {
      background: var(--bg);
      color: var(--white);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
    }

    /* ── NAV ── */
    header { background: var(--bg-nav); position: sticky; top: 0; z-index: 100; }
    nav {
      display: flex; align-items: center; justify-content: space-between;
      max-width: 1280px; margin: 0 auto; padding: 0 40px; height: 72px;
    }
    .nav-line {
      height: 1.5px;
      background: linear-gradient(90deg, transparent, var(--green), transparent);
      opacity: 0.55;
    }

    /* logo */
    .logo-box { display: flex; align-items: center; }
    .logo-box a { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .logo-img { width: 55px; height: 55px; object-fit: contain; }
    .logo-text { display: flex; flex-direction: column; justify-content: center; line-height: 1; margin-top: 2px; }
    .logo-name { font-size: 26px; font-weight: 700; letter-spacing: 1px; white-space: nowrap; text-shadow: 0 0 15px rgba(53,212,106,.18); }
    .logo-name .ecoo {
      background: linear-gradient(135deg,#7dffad 0%,#35d46a 45%,#1fa34a 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .logo-name .byte { color:#fff; }
    .logo-coop {
      background: linear-gradient(135deg,#7dffad 0%,#35d46a 45%,#1fa34a 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      font-size: 12px; font-weight: 500; letter-spacing: 2px; margin-top: 2px;
      width: 100%; text-align: center;
    }

    /* nav links */
    .nav-links { flex: 1; display: flex; justify-content: center; gap: 36px; list-style: none; }
    .nav-links a { color: var(--white); text-decoration: none; font-size: 15px; font-weight: 500; opacity: .88; transition: opacity .2s, color .2s; }
    .nav-links a:hover { opacity: 1; color: var(--green); }

    .nav-actions { display: flex; align-items: center; gap: 14px; }

    .btn-nav {
      background: var(--green); color: #0a0e1a; font-size: 15px; font-weight: 700;
      padding: 11px 28px; border-radius: 8px; border: none; cursor: pointer;
      text-decoration: none; transition: filter .2s, transform .15s; white-space: nowrap;
    }
    .btn-nav:hover { filter: brightness(1.1); transform: translateY(-1px); }

    /* hamburger toggle (mobile only) */
    .nav-toggle {
      display: none;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 5px;
      width: 40px;
      height: 40px;
      background: transparent;
      border: 1.5px solid rgba(53,212,106,0.35);
      border-radius: 8px;
      cursor: pointer;
      flex-shrink: 0;
    }
    .nav-toggle span {
      display: block;
      width: 18px;
      height: 2px;
      background: var(--green-lt);
      border-radius: 2px;
      transition: transform .25s ease, opacity .2s ease;
    }
    .nav-toggle[aria-expanded="true"] span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .nav-toggle[aria-expanded="true"] span:nth-child(2) { opacity: 0; }
    .nav-toggle[aria-expanded="true"] span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

    /* ── PAGE CONTENT ── */
    .page-wrap {
      max-width: 900px;
      margin: 0 auto;
      padding: 80px 40px 100px;
    }

    .page-header {
      text-align: center;
      margin-bottom: 52px;
    }
    .page-header h1 {
      font-size: clamp(30px, 5vw, 52px);
      font-weight: 900;
      letter-spacing: -0.8px;
      margin-bottom: 16px;
    }
    .page-header p {
      font-size: 15px;
      color: var(--muted);
      line-height: 1.65;
      max-width: 520px;
      margin: 0 auto;
    }

    /* ── ALERT (mensagem de sucesso/erro do PHP) ── */
    .alert {
      max-width: 900px;
      margin: 0 auto 28px;
      padding: 16px 20px;
      border-radius: 10px;
      font-size: 14.5px;
      font-weight: 600;
      text-align: center;
    }
    .alert-sucesso {
      background: rgba(53,212,106,0.12);
      border: 1.5px solid rgba(53,212,106,0.45);
      color: var(--green-lt);
    }
    .alert-erro {
      background: rgba(255,90,90,0.1);
      border: 1.5px solid rgba(255,90,90,0.4);
      color: #ff9a9a;
    }

    /* ── FORM CARD ── */
    .form-card {
      background: var(--bg-card);
      border: 1.5px solid rgba(53,212,106,0.25);
      border-radius: 20px;
      padding: 40px 44px 44px;
    }

    /* type tabs */
    .type-tabs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 36px;
    }

    .tab-btn {
      padding: 16px;
      border-radius: 10px;
      border: 1.5px solid rgba(255,255,255,0.15);
      background: transparent;
      color: var(--white);
      font-family: 'Inter', sans-serif;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: background .2s, border-color .2s, color .2s;
    }
    .tab-btn.active {
      background: var(--green);
      border-color: var(--green);
      color: #0a0e1a;
    }
    .tab-btn:not(.active):hover {
      border-color: rgba(53,212,106,0.5);
      color: var(--green);
    }

    /* form rows */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 24px;
    }
    .form-row.single { grid-template-columns: 1fr; }

    .field { display: flex; flex-direction: column; gap: 8px; }

    .field label {
      font-size: 13.5px;
      font-weight: 600;
      color: var(--white);
    }
    .field label .req { color: var(--green); margin-left: 2px; }

    .field input,
    .field textarea {
      background: var(--bg-input);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 10px;
      color: var(--white);
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      padding: 14px 16px;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      width: 100%;
    }
    .field input::placeholder,
    .field textarea::placeholder { color: rgba(176,186,200,0.45); }
    .field input:focus,
    .field textarea:focus {
      border-color: rgba(53,212,106,0.5);
      box-shadow: 0 0 0 3px rgba(53,212,106,0.08);
    }
    .field textarea { resize: vertical; min-height: 110px; line-height: 1.6; }

    /* section label */
    .section-label {
      font-size: 13.5px;
      font-weight: 600;
      color: var(--white);
      margin-bottom: 12px;
    }
    .section-label .req { color: var(--green); margin-left: 2px; }

    /* method radio cards */
    .method-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 36px;
    }

    .method-option { position: relative; }
    .method-option input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }

    .method-label {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      padding: 18px 20px;
      background: var(--bg-input);
      border: 1.5px solid rgba(255,255,255,0.09);
      border-radius: 12px;
      cursor: pointer;
      transition: border-color .2s, background .2s;
    }
    .method-option input[type="radio"]:checked + .method-label {
      border-color: rgba(53,212,106,0.5);
      background: rgba(53,212,106,0.05);
    }
    .method-label:hover { border-color: rgba(53,212,106,0.3); }

    .radio-dot {
      width: 16px; height: 16px; min-width: 16px;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,0.3);
      margin-top: 2px;
      position: relative;
      transition: border-color .2s;
    }
    .method-option input[type="radio"]:checked + .method-label .radio-dot {
      border-color: var(--green);
    }
    .method-option input[type="radio"]:checked + .method-label .radio-dot::after {
      content: '';
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%,-50%);
      width: 7px; height: 7px;
      border-radius: 50%;
      background: var(--green);
    }

    .method-text { display: flex; flex-direction: column; gap: 4px; }
    .method-text strong { font-size: 14px; font-weight: 600; color: var(--white); }
    .method-text span { font-size: 12px; color: var(--muted); opacity: 0.75; line-height: 1.4; }

    /* submit */
    .btn-submit {
      width: 100%;
      background: var(--green);
      color: #0a0e1a;
      font-family: 'Inter', sans-serif;
      font-size: 17px;
      font-weight: 800;
      padding: 20px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      letter-spacing: 0.2px;
      transition: filter .2s, transform .15s;
    }
    .btn-submit:hover { filter: brightness(1.1); transform: translateY(-2px); }

    /* ── FOOTER ── */
    .footer-wrapper { border-top: 1px solid rgba(255,255,255,0.07); background: #0b0f1e; }
    footer {
      max-width: 1280px; margin: 0 auto;
      padding: 64px 40px 40px;
      display: grid;
      grid-template-columns: 1.8fr 1fr 1fr;
      gap: 60px;
    }
    .footer-brand .footer-desc {
      font-size: 13.5px; color: var(--muted); line-height: 1.7;
      max-width: 340px; margin-top: 20px; opacity: .8;
    }
    .footer-col h5 {
      font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
      background: linear-gradient(135deg,var(--green-lt) 0%,var(--green) 60%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 22px;
    }
    .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
    .footer-col ul li a { font-size: 14px; color: var(--muted); text-decoration: none; opacity: .8; transition: color .2s, opacity .2s; }
    .footer-col ul li a:hover { color: var(--white); opacity: 1; }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,.06); text-align: center; padding: 22px 40px; font-size: 13px; color: var(--muted); opacity: .6; }

    @media (max-width: 900px) {
      .nav-toggle { display: flex; }

      .nav-links {
        position: absolute;
        top: 100%;
        left: 0;
        flex: none;
        justify-content: flex-start;
        width: 100%;
        flex-direction: column;
        gap: 0;
        background: var(--bg-nav);
        border-bottom: 1px solid rgba(53,212,106,0.2);
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height .35s ease, opacity .25s ease;
        pointer-events: none;
      }
      .nav-links.is-open {
        max-height: 420px;
        opacity: 1;
        pointer-events: auto;
        box-shadow: 0 16px 32px rgba(0,0,0,0.35);
      }
      .nav-links li { width: 100%; }
      .nav-links a {
        display: block;
        padding: 16px 28px;
        font-size: 16px;
        border-top: 1px solid rgba(255,255,255,0.06);
      }
      .nav-links li:first-child a { border-top: none; }
    }

    @media (max-width: 700px) {
      .page-wrap { padding: 48px 20px 70px; }
      .form-card { padding: 28px 22px 32px; }
      .form-row { grid-template-columns: 1fr; }
      .type-tabs { grid-template-columns: 1fr; }
      .method-grid { grid-template-columns: 1fr; }
      nav { padding: 0 20px; }
      footer { grid-template-columns: 1fr; gap: 32px; padding: 40px 20px 28px; }
    }

    @media (max-width: 600px) {
      nav { min-height: 68px; height: auto; }
      .logo-img { width: 46px; height: 46px; }
      .logo-name { font-size: 20px; }
      .logo-coop { font-size: 10px; letter-spacing: 1.5px; }
      .btn-nav { padding: 9px 18px; font-size: 13.5px; }
      .nav-toggle { width: 36px; height: 36px; }
    }

    @media (max-width: 380px) {
      .logo-name { font-size: 17px; }
      .logo-coop { display: none; }
      .btn-nav { padding: 8px 14px; font-size: 12.5px; }
    }
  </style>
</head>
<body>

  <!-- ── HEADER ── -->
  <header>
    <nav>
      <div class="logo-box">
        <a href="index.html">
          <img src="imagem/logo-pi.png" alt="Logo Ecoobytes" class="logo-img">
          <div class="logo-text">
            <div class="logo-name">
              <span class="ecoo">ECOO</span><span class="byte">BYTES</span>
            </div>
            <span class="logo-coop">COOPERATIVA</span>
          </div>
        </a>
      </div>

      <ul class="nav-links" id="navLinksList">
        <li><a href="index.html">Inicio</a></li>
        <li><a href="index.html#sobre-nos">Sobre Nós</a></li>
        <li><a href="index.html#como-funciona">Como Funciona</a></li>
        <li><a href="index.html#nosso-impacto">Nosso Impacto</a></li>
        <li><a href="index.html#servicos">Serviços Técnicos</a></li>
      </ul>

      <div class="nav-actions">
        <a href="doacao.php" class="btn-nav">Doar Agora</a>
        <button type="button" class="nav-toggle" id="navToggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="navLinksList">
          <span></span><span></span><span></span>
        </button>
      </div>
    </nav>
    <div class="nav-line"></div>
  </header>

  <script>
    (function () {
      const navToggle = document.getElementById('navToggle');
      const navLinks  = document.getElementById('navLinksList');

      navToggle.addEventListener('click', () => {
        const isOpen = navLinks.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });

      navLinks.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
          navLinks.classList.remove('is-open');
          navToggle.setAttribute('aria-expanded', 'false');
        });
      });

      window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
          navLinks.classList.remove('is-open');
          navToggle.setAttribute('aria-expanded', 'false');
        }
      });
    })();
  </script>

  <!-- ── MAIN ── -->
  <main>
    <div class="page-wrap">

      <div class="page-header">
        <h1>Ficha de Agendamento de Coleta</h1>
        <p>Ao preencher os dados, a nossa equipe operacional fará a classificação e entrará em contato para definir o recebimento.</p>
      </div>

      <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipoMsg === 'sucesso' ? 'sucesso' : 'erro'; ?>">
          <?php echo htmlspecialchars($mensagem); ?>
        </div>
      <?php endif; ?>

      <div class="form-card">

        <!-- TYPE TABS -->
        <div class="type-tabs">
          <button type="button" class="tab-btn active" id="tabFisica" onclick="switchTab('fisica')">Pessoa Física</button>
          <button type="button" class="tab-btn"        id="tabEmpresa" onclick="switchTab('empresa')">Empresa / Instituição</button>
        </div>

        <!-- FORM -->
        <form method="POST" action="doacao.php" id="formDoacao" onsubmit="return validarFormulario(event)">

          <div class="form-row">
            <div class="field">
              <label for="nome">Nome do Doador / Empresa <span class="req">*</span></label>
              <input type="text" id="nome" name="nome" placeholder="Nome Social ou Razão Social" required
                     value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"/>
            </div>
            <div class="field">
              <label for="email">E-mail de Contato <span class="req">*</span></label>
              <input type="email" id="email" name="email" placeholder="Seuemail@gmail.com" required
                     value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"/>
            </div>
          </div>

          <div class="form-row">
            <div class="field">
              <label for="telefone">Telefone / WhatsApp <span class="req">*</span></label>
              <input type="tel" id="telefone" name="telefone" placeholder="DDD + TELEFONE" required
                     value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>"/>
            </div>
            <div class="field">
              <label for="cep">Código Postal / CEP</label>
              <input type="text" id="cep" name="cep" placeholder="EX: 0000 - 000 ou CEP"
                     value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>"/>
            </div>
          </div>

          <div class="form-row single" style="margin-bottom:28px;">
            <div class="field">
              <label for="descricao">Descrição dos Itens Disponíveis para Doar <span class="req">*</span></label>
              <textarea id="descricao" name="descricao" placeholder="EX: 2 Computadores antigos (Ligam), 1 Monitor CRT,  Varios cabos e memórias antigas soltas" required><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
            </div>
          </div>

          <!-- METHOD -->
          <p class="section-label">Método de Envio / Coleta</p>
          <div class="method-grid">
            <div class="method-option">
              <input type="radio" name="metodo" id="metEntrega" value="entrega"
                     <?php echo (($_POST['metodo'] ?? 'entrega') === 'entrega') ? 'checked' : ''; ?>/>
              <label class="method-label" for="metEntrega">
                <span class="radio-dot"></span>
                <span class="method-text">
                  <strong>Vou entregar num ponto de coleta</strong>
                  <span>Leve diretamente ao nosso galpão de triagem.</span>
                </span>
              </label>
            </div>
            <div class="method-option">
              <input type="radio" name="metodo" id="metRecolha" value="recolha"
                     <?php echo (($_POST['metodo'] ?? '') === 'recolha') ? 'checked' : ''; ?>/>
              <label class="method-label" for="metRecolha">
                <span class="radio-dot"></span>
                <span class="method-text">
                  <strong>Solicito recolha domiciliar</strong>
                  <span>para grandes volumes ou necessidades específicas.</span>
                </span>
              </label>
            </div>
          </div>

          <button type="submit" class="btn-submit">Concluir Agendamento ou Doação</button>
        </form>

      </div>
    </div>
  </main>

  <!-- ── FOOTER ── -->
  <div class="footer-wrapper">
    <footer>
      <div class="footer-brand">
        <div class="logo-box">
          <a href="index.html" style="text-decoration:none;">
            <img src="imagem/logo-pi.png" alt="Logo Ecoobytes" class="logo-img">
            <div class="logo-text">
              <div class="logo-name">
                <span class="ecoo">ECOO</span><span class="byte">BYTES</span>
              </div>
              <span class="logo-coop">COOPERATIVA</span>
            </div>
          </a>
        </div>
        <p class="footer-desc">
          Associação sem fins lucrativos que recupera lixo tecnológico, transforma
          em ferramentas de inclusão social e oferece assistência técnica de impacto.
        </p>
      </div>
      <div class="footer-col">
        <h5>Explorar</h5>
        <ul>
          <li><a href="index.html">Página Principal</a></li>
          <li><a href="doacao.php">Doar Computador</a></li>
          <li><a href="index.html#servicos">Serviços Técnicos</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Contato &amp; Endereço</h5>
        <ul>
          <li><a href="mailto:ecoobytesbr@gmail.com">ecoobytesbr@gmail.com</a></li>
          <li><a href="https://www.ecoobytes.com" target="_blank">www.ecoobytes.com</a></li>
          <li><a href="#">@ecoobytes.tech</a></li>
        </ul>
      </div>
    </footer>
    <div class="footer-bottom">
      © 2026 Cooperativa Ecoobytes. Todos os direitos reservados.
    </div>
  </div>

  <script>
    /* ── TAB SWITCH (visual apenas; não é salvo no banco atualmente) ── */
    function switchTab(type) {
      const fisica   = document.getElementById('tabFisica');
      const empresa  = document.getElementById('tabEmpresa');
      const nomeInp  = document.getElementById('nome');

      if (type === 'fisica') {
        fisica.classList.add('active');
        empresa.classList.remove('active');
        nomeInp.placeholder = 'Nome Social ou Razão Social';
      } else {
        empresa.classList.add('active');
        fisica.classList.remove('active');
        nomeInp.placeholder = 'Razão Social / Nome da Instituição';
      }
    }

    /* ── PHONE MASK ── */
    document.getElementById('telefone').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 6) {
      v = `(${v.slice(0,2)}) ${v.slice(2,7)}-${v.slice(7)}`;
    } else if (v.length > 2) {
      v = `(${v.slice(0,2)}) ${v.slice(2)}`;
    }
      e.target.value = v;
    });

    /* ── CEP MASK ── */
    document.getElementById('cep').addEventListener('input', function(e) {
      let v = e.target.value.replace(/\D/g, '').slice(0, 8);
      if (v.length > 5) v = v.slice(0,5) + '-' + v.slice(5);
      e.target.value = v;
    });

    /* ── VALIDAÇÃO NO CLIENTE (apenas melhora a experiência;
       a validação que realmente protege o banco é a do PHP) ── */
    function validarFormulario(e) {
      const nome     = document.getElementById('nome').value.trim();
      const email    = document.getElementById('email').value.trim();
      const telefone = document.getElementById('telefone').value.trim();
      const desc     = document.getElementById('descricao').value.trim();

      if (!nome || !email || !telefone || !desc) {
        e.preventDefault();
        alert('Por favor, preencha todos os campos obrigatórios (*).');
        return false;
      }

      // Se passou na validação, deixa o form seguir o envio normal (POST para o PHP)
      return true;
    }
  </script>

</body>
</html>
