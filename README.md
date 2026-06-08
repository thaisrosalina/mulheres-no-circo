<h1 align="center">🎪 Mulheres no Circo</h1>

<p align="center">
  <img src="assets/img/logo.svg" width="90" alt="Logo Mulheres no Circo">
</p>

<p align="center">
  <b>Plataforma de mapeamento, visibilidade e conexão para mulheres das artes circenses no Brasil.</b><br>
  Um diretório público de artistas com perfil profissional, portfólio de trabalhos e área para produtores(as)/curadores(as).
</p>

<p align="center">
  🔗 <b>Demo ao vivo:</b> <a href="https://mulheresnocirco.rf.gd">mulheresnocirco.rf.gd</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8-777BB4?logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white">
  <img src="https://img.shields.io/badge/sem%20framework-PHP%20puro%20%2B%20PDO-2b2533">
</p>

---

## ✨ Funcionalidades

**Para o público**
- 🏠 Página inicial e seção institucional (Sobre, Apoie, Contato, Política de Privacidade)
- 🔍 **Diretório de artistas** com busca e filtros (área, cidade, disponibilidade) e paginação
- 👩‍🎤 **Perfil público** da artista com biografia, especialidades, redes e portfólio de trabalhos (foto, vídeo do YouTube, PDF)
- 📨 Formulário de **contato** funcional e botão flutuante de **WhatsApp**

**Para as artistas**
- 🔐 Cadastro/login com consentimento **LGPD**
- 🧰 **Painel** com perfil em modo leitura + edição sob demanda
- 🎯 **Catálogo de modalidades circenses** (10 categorias, ~150 habilidades) selecionável em chips
- 🖼️ Gestão de **trabalhos** (imagem, vídeo, PDF) e materiais para curadoria

**Para produtores(as) / curadores(as)**
- 🪪 Cadastro próprio e **Área do Produtor**
- ⬇️ **Download de materiais** das artistas (protegido por login) e **ficha de perfil em PDF** (estilo LinkedIn)
- 🔖 **Curadoria salva** (favoritar artistas) + recebimento de novidades (consentimento por e‑mail/WhatsApp)

**Administração**
- 🛠️ Painel admin com gestão de usuárias, moderação de trabalhos, mensagens de contato e logs

---

## 🛠️ Tecnologias
- **PHP 8** puro com **PDO** (sem framework)
- **MySQL / MariaDB** (charset `utf8mb4`)
- **Bootstrap 5** + **Bootstrap Icons**
- **Google Fonts** (Fraunces + Inter)
- Design system próprio com *tokens* CSS (tema "Coral & Turquesa")

## 🔒 Boas práticas de segurança
- Senhas com **`password_hash`** (bcrypt)
- **Prepared statements** (PDO) em todas as consultas
- Proteção **CSRF** nas ações de escrita
- **Rate limiting** no login + `session_regenerate_id`
- Validação de upload por extensão, tamanho e **conteúdo real** (`getimagesize`)
- **Download protegido** (sem hotlink) e `.htaccess` bloqueando `config/`, `includes/` e execução em `uploads/`
- Credenciais do banco **fora do versionamento** (`config/db-credentials.php` no `.gitignore`)
- Consentimento **LGPD** no cadastro

---

## 📁 Estrutura
```
mulheres-no-circo/
├── *.php                       # Páginas/rotas (raiz = web root)
├── config/                     # Conexão PDO (credenciais separadas)
├── includes/                   # Lógica, layout e componentes reutilizáveis
├── assets/css · assets/js · assets/img
├── uploads/                    # Mídia enviada (avatars, trabalhos, apresentações)
├── sql/                        # Scripts de banco e dump utf8mb4
└── .htaccess                   # Segurança / DirectoryIndex
```

## ▶️ Rodando localmente (XAMPP)
1. Coloque a pasta em `C:\xampp\htdocs\mulheres-no-circo`.
2. Suba **Apache** e **MySQL**.
3. Crie o banco `mulheres_no_circo` (utf8mb4) e importe `sql/mulheres_no_circo_utf8mb4.sql`.
4. Acesse `http://localhost/mulheres-no-circo`.

> Credenciais locais padrão (XAMPP): `root` / sem senha. Para produção, crie `config/db-credentials.php` a partir do `db-credentials.example.php`.

## 🚀 Deploy
Compatível com qualquer hospedagem **PHP + MySQL** (publicado no InfinityFree).
Basta importar o dump, ajustar `config/db-credentials.php` e enviar os arquivos para o `htdocs`.

---

## 👩‍💻 Autoria
Idealização e desenvolvimento: **Thais Oliveira** — Central de Produção Cultural.
Realização: **Circoteca** · **Cia Gêmea** · **Tô Feito Estúdio Criativo**.

<p align="center"><i>Dar nome, rosto e voz às mulheres do circo é um ato de memória e de justiça. 🎪</i></p>
