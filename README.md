# EcooBytes 🌱💻

Sistema web de uma cooperativa de reciclagem e inclusão digital, que recondiciona equipamentos eletrônicos descartados e os destina a comunidades e escolas em situação de vulnerabilidade.

A plataforma reúne, em um único site:

- Apresentação institucional da cooperativa
- Simulador interativo de impacto ambiental e social
- Formulário de agendamento de doação de equipamentos
- Formulário de solicitação de assistência técnica

---

## 📋 Sumário

- [Sobre o Projeto](#sobre-o-projeto)
- [Ferramentas Utilizadas](#ferramentas-utilizadas)
- [Estrutura de Arquivos](#estrutura-de-arquivos)
- [Banco de Dados](#banco-de-dados)
- [Como Executar o Projeto](#como-executar-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Segurança](#segurança)
- [Melhorias Futuras](#melhorias-futuras)

---

## Sobre o Projeto

O EcooBytes foi construído com tecnologias web tradicionais — HTML, CSS e JavaScript no front-end, PHP no back-end e MySQL como banco de dados — priorizando simplicidade de manutenção, segurança básica contra ataques comuns e uma experiência visual moderna alinhada à identidade da cooperativa.

**Objetivos do sistema:**

- Apresentar a missão, o processo operacional e o impacto social da cooperativa.
- Permitir que doadores (pessoas físicas ou empresas) agendem a entrega ou recolha de equipamentos eletrônicos.
- Permitir que usuários solicitem orçamento/assistência técnica para seus equipamentos.
- Simular, de forma interativa, o impacto ambiental e social de uma doação com base no peso do material.
- Persistir as solicitações recebidas em um banco de dados relacional para acompanhamento pela equipe operacional.

---

## Ferramentas Utilizadas

| Ferramenta | Função no Projeto |
|---|---|
| **PHP Server** | Servidor local para interpretar e executar os scripts PHP (processamento de formulários, conexão com o banco de dados e geração do HTML dinâmico). |
| **MySQL Server** | Sistema gerenciador de banco de dados relacional responsável por armazenar os registros de chamados técnicos e de doações. |
| **Beekeeper Studio** | Cliente gráfico (GUI) utilizado para administrar o MySQL: criação das tabelas, inspeção e consulta dos dados inseridos pelos formulários. |
| **Visual Studio Code** | Editor de código utilizado para escrever e organizar os arquivos PHP, HTML, CSS e JavaScript do projeto. |

Esse conjunto forma um fluxo de trabalho típico de desenvolvimento PHP/MySQL local: o código é escrito no VS Code, executado através de um servidor PHP local, persistido em um banco MySQL e inspecionado/gerenciado visualmente pelo Beekeeper Studio.

---

## Estrutura de Arquivos

O projeto segue uma arquitetura simples de três camadas, sem uso de frameworks:

- **Apresentação:** arquivos HTML/CSS/JavaScript embutidos diretamente nas páginas PHP.
- **Lógica de negócio:** blocos PHP no topo de cada página, responsáveis por validar e processar os dados enviados via POST.
- **Persistência:** banco de dados MySQL, acessado via extensão `mysqli` com *prepared statements*.

| Arquivo | Descrição |
|---|---|
| `index.html` | Página institucional principal: hero de apresentação, simulador de impacto (peso → computadores recondicionados, CO² evitado, pessoas incluídas), seções "Quem Somos", "Como Funciona", "Nosso Impacto" e "Serviços Técnicos". |
| `doacao.php` | Formulário de agendamento de doação de equipamentos (pessoa física ou empresa), com escolha de método de entrega/recolha. Grava os dados na tabela `doacoes`. |
| `assistencia.php` | Formulário de solicitação de assistência técnica, com tabela de preços sociais dos serviços oferecidos. Grava os dados na tabela `chamados`. |

```
📁 ecoobytes/
├── index.html
├── doacao.php
├── assistencia.php
└── imagem/
    └── logo-pi.png
```

---

## Banco de Dados

Banco de dados MySQL criado com o nome `sistema`, composto por duas tabelas principais, ambas com chave primária autoincremento e carimbo de data/hora de criação do registro.

```sql
CREATE DATABASE IF NOT EXISTS sistema;
USE sistema;
```

### Tabela `chamados`

Armazena as solicitações de assistência técnica enviadas pelo formulário de `assistencia.php`.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | INT (PK, AUTO_INCREMENT) | Identificador único do chamado. |
| `nome` | VARCHAR(150) | Nome de quem solicitou o atendimento. |
| `whatsapp` | VARCHAR(30) | Telefone/WhatsApp para contato. |
| `equipamento` | VARCHAR(150) | Modelo/tipo do equipamento com problema. |
| `problema` | TEXT | Descrição detalhada do problema relatado. |
| `data_abertura` | TIMESTAMP | Data e hora de abertura, preenchida automaticamente. |

### Tabela `doacoes`

Armazena os agendamentos de doação enviados pelo formulário de `doacao.php`.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | INT (PK, AUTO_INCREMENT) | Identificador único da doação. |
| `nome` | VARCHAR(150) | Nome do doador ou razão social da empresa. |
| `email` | VARCHAR(150) | E-mail de contato do doador. |
| `telefone` | VARCHAR(30) | Telefone/WhatsApp de contato. |
| `cep` | VARCHAR(20) | Código postal/CEP informado (opcional). |
| `descricao` | TEXT | Descrição dos itens disponíveis para doação. |
| `metodo` | VARCHAR(20) | Método escolhido: `entrega` (ponto de coleta) ou `recolha` (domiciliar). |
| `data_envio` | TIMESTAMP | Data e hora do envio, preenchida automaticamente. |

---

## Como Executar o Projeto

1. **Instale** um ambiente PHP + MySQL local (ex.: XAMPP, WAMP, MAMP, ou PHP embutido + MySQL Server separado).
2. **Crie o banco de dados** executando o script SQL disponibilizado (criação de `sistema`/`ecoobytes` e das tabelas `chamados` e `doacoes`).
3. **Ajuste as credenciais de conexão** nos arquivos `assistencia.php` e `doacao.php`:
   ```php
   $conn = new mysqli(
       'localhost', // servidor
       'ecoo',      // usuário
       '1234',      // senha
       'ecoobytes'  // banco de dados
   );
   ```
4. **Coloque os arquivos** (`index.html`, `doacao.php`, `assistencia.php`, pasta `imagem/`) no diretório servido pelo seu servidor PHP local.
5. **Acesse** `index.html` pelo navegador através do servidor local (ex.: `http://localhost/index.html`).
6. Use o **Beekeeper Studio** para conectar ao MySQL e conferir os registros inseridos pelos formulários.

---

## Funcionalidades

### Fluxo de envio dos formulários (PHP)

- O bloco PHP só é executado quando o método da requisição é `POST`.
- É aberta uma conexão com o banco via `mysqli`, com tratamento do erro de conexão.
- Os campos de `$_POST` são lidos com `trim()` e o operador `?? ''`, evitando erros de índice indefinido.
- É feita validação obrigatória dos campos essenciais no servidor (em `doacao.php` também é validado o formato do e-mail com `filter_var()`).
- Os dados são inseridos com *prepared statements* (`bind_param`), evitando injeção de SQL.
- Em caso de sucesso, o sistema aplica o padrão **Post/Redirect/Get**, evitando reenvio duplicado do formulário ao atualizar a página.
- A mensagem de sucesso ou erro é exibida com `htmlspecialchars()`, evitando XSS.

### Validação no cliente (JavaScript)

- Validação de campos obrigatórios apenas para melhorar a experiência do usuário (a validação que protege o banco é sempre a do PHP).
- Máscara de telefone/WhatsApp no padrão `(DD) DDDDD-DDDD`.
- Máscara de CEP no padrão `00000-000`.
- Alternância visual das abas "Pessoa Física" / "Empresa" na página de doação.

### Simulador de Impacto (`index.html`)

Slider de peso (5 kg a 200 kg) que recalcula em tempo real:

- Computadores recondicionados estimados (`peso ÷ 7,5 kg`)
- CO² evitado (`peso × 1,8`) e equivalência em árvores plantadas (`CO² ÷ 9`)
- Pessoas incluídas digitalmente (`peso ÷ 1,9`)

### Design e responsividade

Tema escuro com verde como cor de destaque, tipografia Inter, ilustrações em SVG (computadores, roteador, folhas decorativas) e layout responsivo com menu hambúrguer em telas até 900px.

---

## Segurança

- ✅ *Prepared statements* (`mysqli`) em todas as inserções, prevenindo injeção de SQL.
- ✅ Escape de saída com `htmlspecialchars()` em todo dado do usuário reexibido na página, prevenindo XSS refletido.
- ✅ Validação de campos obrigatórios e de formato de e-mail no servidor.
- ✅ Padrão Post/Redirect/Get após inserções bem-sucedidas.
- ✅ Fechamento explícito de *statements* e conexões (`close()`) após cada operação no banco.

---

## Melhorias Futuras

- [ ] Mover as credenciais do banco de dados para variáveis de ambiente ou arquivo de configuração fora do controle de versão.
- [ ] Criar autenticação de administrador para consulta dos chamados e doações (hoje depende do acesso direto via Beekeeper Studio).
- [ ] Persistir no banco o tipo de doador (pessoa física/empresa), hoje apenas visual nas abas do formulário.

---

## 📄 Licença

Projeto desenvolvido pela **Cooperativa EcooBytes** para fins de inclusão digital e sustentabilidade ambiental.
