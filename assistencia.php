<?php
// ============================================================
// PROCESSAMENTO DO FORMULÁRIO (só executa quando o método é POST)
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
        $nome        = trim($_POST['nome'] ?? '');
        $whatsapp    = trim($_POST['whatsapp'] ?? '');
        $equipamento = trim($_POST['equipamento'] ?? '');
        $problema    = trim($_POST['problema'] ?? '');

        // Validação no servidor (a do JavaScript é só para conforto do usuário)
        if ($nome === '' || $whatsapp === '' || $problema === '') {
            $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
            $tipoMsg  = 'erro';
        } else {

            // Prepared statement: evita injeção de SQL
            $stmt = $conn->prepare(
                'INSERT INTO chamados (nome, whatsapp, equipamento, problema) VALUES (?, ?, ?, ?)'
            );
            $stmt->bind_param('ssss', $nome, $whatsapp, $equipamento, $problema);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                // Padrão Post/Redirect/Get: evita reenvio duplicado ao atualizar a página
                header('Location: assistencia.php?status=sucesso');
                exit;
            } else {
                $mensagem = 'Ocorreu um erro ao enviar seu chamado. Tente novamente.';
                $tipoMsg  = 'erro';
            }
            $stmt->close();
        }
        $conn->close();
    }
}

// Mensagem de sucesso após o redirecionamento
if (isset($_GET['status']) && $_GET['status'] === 'sucesso') {
    $mensagem = 'Chamado enviado com sucesso! Nossa equipe entrará em contato em breve.';
    $tipoMsg  = 'sucesso';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EcooBytes — Assistência Técnica</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:       #090d1a;
      --bg-nav:   #0b0f1e;
      --bg-card:  #0e1428;
      --bg-input: #111829;
      --bg-inner: #0a0d1c;
      --green:    #35d46a;
      --green-lt: #7dffad;
      --green-dk: #1fa34a;
      --white:    #ffffff;
      --muted:    #b0bac8;
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

    .logo-box { display: flex; align-items: center; }
    .logo-box a { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .logo-img { width: 55px; height: 55px; object-fit: contain; }
    .logo-text { display: flex; flex-direction: column; justify-content: center; line-height: 1; margin-top: 2px; }
    .logo-name { font-size: 26px; font-weight: 700; letter-spacing: 1px; white-space: nowrap; }
    .logo-name .ecoo {
      background: linear-gradient(135deg,#7dffad 0%,#35d46a 45%,#1fa34a 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .logo-name .byte { color: #fff; }
    .logo-coop {
      background: linear-gradient(135deg,#7dffad 0%,#35d46a 45%,#1fa34a 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      font-size: 12px; font-weight: 500; letter-spacing: 2px; margin-top: 2px;
      width: 100%; text-align: center;
    }

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

    /* ── PAGE ── */
    .page-wrap {
      max-width: 1180px;
      margin: 0 auto;
      padding: 70px 40px 100px;
    }

    /* ── ALERT (mensagem de sucesso/erro do PHP) ── */
    .alert {
      max-width: 1180px;
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

    /* ── GRID ── */
    .assist-grid {
      display: grid;
      grid-template-columns: 380px 1fr;
      gap: 28px;
      align-items: start;
    }

    /* ── LEFT CARD: price table ── */
    .price-card {
      background: var(--bg-card);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 20px;
      padding: 32px 28px 28px;
    }

    .price-card-title {
      font-size: 16px;
      font-weight: 800;
      background: linear-gradient(135deg, var(--green-lt) 0%, var(--green) 60%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 28px;
    }

    .price-list { display: flex; flex-direction: column; }

    .price-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 17px 0;
      border-bottom: 1px solid rgba(255,255,255,0.07);
      gap: 12px;
    }
    .price-item:last-child { border-bottom: none; }

    .price-item-info { display: flex; flex-direction: column; gap: 4px; }
    .price-item-name { font-size: 14px; font-weight: 700; color: var(--white); }
    .price-item-sub  { font-size: 12px; color: var(--muted); opacity: .75; }

    .price-badge {
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
      background: linear-gradient(135deg, var(--green-lt) 0%, var(--green) 60%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }

    /* info box */
    .info-box {
      background: var(--bg-inner);
      border-radius: 12px;
      padding: 18px 20px;
      margin-top: 20px;
    }
    .info-box .info-title {
      font-size: 13px;
      font-weight: 700;
      background: linear-gradient(135deg, var(--green-lt) 0%, var(--green) 60%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 8px;
    }
    .info-box p {
      font-size: 12.5px;
      color: var(--muted);
      line-height: 1.65;
      opacity: .85;
    }

    /* ── RIGHT CARD: form ── */
    .form-card {
      background: var(--bg-card);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 20px;
      padding: 36px 36px 40px;
    }

    .form-card h2 {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.3px;
      margin-bottom: 32px;
    }

    /* field groups */
    .field-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 22px;
    }

    .field-group label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--muted);
    }

    .field-group input,
    .field-group textarea {
      background: var(--bg-inner);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 10px;
      color: var(--white);
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      padding: 14px 16px;
      outline: none;
      width: 100%;
      transition: border-color .2s, box-shadow .2s;
    }
    .field-group input::placeholder,
    .field-group textarea::placeholder { color: rgba(176,186,200,0.35); }
    .field-group input:focus,
    .field-group textarea:focus {
      border-color: rgba(53,212,106,0.45);
      box-shadow: 0 0 0 3px rgba(53,212,106,0.07);
    }
    .field-group textarea { resize: vertical; min-height: 100px; line-height: 1.6; }

    .form-row-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    /* submit */
    .btn-submit {
      width: 100%;
      background: var(--green);
      color: #0a0e1a;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      font-weight: 800;
      padding: 19px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      letter-spacing: 0.2px;
      margin-bottom: 28px;
      transition: filter .2s, transform .15s;
    }
    .btn-submit:hover { filter: brightness(1.1); transform: translateY(-2px); }
    .btn-submit:disabled { opacity: .7; cursor: not-allowed; transform: none; }

    .divider {
      border: none;
      border-top: 1px solid rgba(255,255,255,0.07);
      margin-bottom: 22px;
    }

    .whatsapp-hint {
      text-align: center;
      font-size: 13.5px;
      color: var(--muted);
      margin-bottom: 14px;
      opacity: .8;
    }

    .btn-whatsapp {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      background: transparent;
      border: 1.5px solid var(--green);
      color: var(--green);
      font-family: 'Inter', sans-serif;
      font-size: 14.5px;
      font-weight: 700;
      padding: 16px;
      border-radius: 12px;
      text-decoration: none;
      transition: background .2s, color .2s, transform .15s;
    }
    .btn-whatsapp:hover {
      background: var(--green);
      color: #0a0e1a;
      transform: translateY(-2px);
    }
    .btn-whatsapp .wa-dot {
      width: 10px; height: 10px; border-radius: 50%;
      background: var(--green);
      flex-shrink: 0;
      transition: background .2s;
    }
    .btn-whatsapp:hover .wa-dot { background: #0a0e1a; }

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
      background: linear-gradient(135deg, var(--green-lt) 0%, var(--green) 60%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 22px;
    }
    .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
    .footer-col ul li a { font-size: 14px; color: var(--muted); text-decoration: none; opacity: .8; transition: color .2s, opacity .2s; }
    .footer-col ul li a:hover { color: var(--white); opacity: 1; }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,.06); text-align: center; padding: 22px 40px; font-size: 13px; color: var(--muted); opacity: .6; }

    @media (max-width: 900px) {
      .assist-grid { grid-template-columns: 1fr; }
      .page-wrap { padding: 48px 20px 70px; }

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
    @media (max-width: 640px) {
      .form-row-2 { grid-template-columns: 1fr; }
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

      <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipoMsg === 'sucesso' ? 'sucesso' : 'erro'; ?>">
          <?php echo htmlspecialchars($mensagem); ?>
        </div>
      <?php endif; ?>

      <div class="assist-grid">

        <!-- ── LEFT: PRICE TABLE ── -->
        <div class="price-card">
          <p class="price-card-title">Tabela de Preços Sociais</p>

          <div class="price-list">
            <div class="price-item">
              <div class="price-item-info">
                <span class="price-item-name">Formatação Completa</span>
                <span class="price-item-sub">Instalação de OS + Softwares básicos</span>
              </div>
              <span class="price-badge">Preço Social</span>
            </div>
            <div class="price-item">
              <div class="price-item-info">
                <span class="price-item-name">Upgrade com SSD</span>
                <span class="price-item-sub">Instalação física + Transferência de dados</span>
              </div>
              <span class="price-badge">Sob Consulta</span>
            </div>
            <div class="price-item">
              <div class="price-item-info">
                <span class="price-item-name">Limpeza Física &amp; Pasta Térmica</span>
                <span class="price-item-sub">Instalação física + Transferência de dados</span>
              </div>
              <span class="price-badge">Preço Social</span>
            </div>
            <div class="price-item">
              <div class="price-item-info">
                <span class="price-item-name">Montagem de Computador</span>
                <span class="price-item-sub">Organização de peças e cabeamento</span>
              </div>
              <span class="price-badge">Preço Social</span>
            </div>
          </div>

          <div class="info-box">
            <p class="info-title">Como funciona?</p>
            <p>O valor cobrado é integralmente revertido para a capacitação dos cooperados e para os insumos operacionais necessários aos testes técnicos de caridade.</p>
          </div>
        </div>

        <!-- ── RIGHT: FORM ── -->
        <div class="form-card">
          <h2>Solicitar Orçamento / Assistência</h2>

          <form id="assistForm" method="POST" action="assistencia.php" onsubmit="return validarFormulario(event)" novalidate>

            <div class="field-group">
              <label for="nome">O seu nome</label>
              <input type="text" id="nome" name="nome" placeholder="Como podemos lhe chamar?" required
                     value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"/>
            </div>

            <div class="form-row-2">
              <div class="field-group">
                <label for="whatsapp">WhatsApp para contato</label>
                <input type="tel" id="whatsapp" name="whatsapp" placeholder="DDD + TELEFONE" required
                       value="<?php echo htmlspecialchars($_POST['whatsapp'] ?? ''); ?>"/>
              </div>
              <div class="field-group">
                <label for="equipamento">Equipamento / Modelo</label>
                <input type="text" id="equipamento" name="equipamento" placeholder="Ex: Portátil Asus X550"
                       value="<?php echo htmlspecialchars($_POST['equipamento'] ?? ''); ?>"/>
              </div>
            </div>

            <div class="field-group">
              <label for="problema">Descreva o problema</label>
              <textarea id="problema" name="problema" placeholder="Diga-nos o que está acontecendo (ex: Liga mas não dá imagem, fica muito lento na formatação...)" required><?php echo htmlspecialchars($_POST['problema'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
              Enviar Chamado para Análise
            </button>

            <hr class="divider"/>

            <p class="whatsapp-hint">Prefere contato direto em tempo real?</p>

            <a href="https://wa.me/5500000000000" target="_blank" class="btn-whatsapp">
              <span class="wa-dot"></span>
              Chame diretamente no Whatsapp
            </a>

          </form>
        </div>

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
    /* phone mask */
    document.getElementById('whatsapp').addEventListener('input', function(e) {
      let v = e.target.value.replace(/\D/g,'').slice(0,11);
      if (v.length > 6)      v = `(${v.slice(0,2)}) ${v.slice(2,7)}-${v.slice(7)}`;
      else if (v.length > 2) v = `(${v.slice(0,2)}) ${v.slice(2)}`;
      else if (v.length > 0) v = `(${v}`;
      e.target.value = v;
    });

    /* ── VALIDAÇÃO NO CLIENTE (apenas melhora a experiência;
       a validação que realmente protege o banco é a do PHP) ── */
    function validarFormulario(e) {
      const nome     = document.getElementById('nome').value.trim();
      const whatsapp = document.getElementById('whatsapp').value.trim();
      const problema = document.getElementById('problema').value.trim();

      if (!nome || !whatsapp || !problema) {
        e.preventDefault();
        alert('Por favor, preencha os campos obrigatórios.');
        return false;
      }

      const btn = document.getElementById('submitBtn');
      btn.textContent = 'Enviando...';
      btn.disabled = true;

      // Passou na validação: deixa o form seguir o envio normal (POST para o PHP)
      return true;
    }
  </script>

</body>
</html>
