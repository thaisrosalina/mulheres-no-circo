# Mulheres no Circo

Plataforma web para mapeamento, visibilidade e conexão de **mulheres das artes circenses** —
um diretório público de artistas com perfil profissional, portfólio de trabalhos e área de gestão.

## Stack
- PHP 8 (puro, sem framework) + PDO
- MySQL / MariaDB
- Bootstrap 5 + Bootstrap Icons
- Google Fonts (Fraunces + Inter)

## Estrutura de pastas
```
mulheres-no-circo/
├── index.php, diretorio.php, artista.php …   # Páginas/rotas públicas (raiz = web root)
├── login.php, cadastro.php, perfil.php …      # Autenticação e painel da artista
├── admin.php                                  # Área administrativa
├── config/
│   └── database.php                           # Conexão PDO (ajustar no deploy)
├── includes/
│   ├── auth.php, csrf.php, log.php, helpers.php   # Lógica/segurança
│   ├── header.php, footer.php                     # Layout
│   ├── seletor-modalidades.php                    # Componente reutilizável
│   └── modalidades.php, redes.php                 # Dados/config de conteúdo
├── assets/
│   ├── css/style.css                          # Tema (design tokens)
│   └── js/modalidades.js                      # JS do seletor de modalidades
├── uploads/                                   # Mídia enviada (avatars, trabalhos, apresentacoes)
├── sql/                                        # Scripts de banco e dump de deploy
└── .htaccess                                   # Segurança / DirectoryIndex
```

**Separação por responsabilidade:** páginas na raiz (o InfinityFree serve a partir de `htdocs`),
lógica/parciais em `includes/`, configuração em `config/`, estáticos em `assets/`,
mídia em `uploads/`, banco em `sql/`.

## Banco de dados
1. Crie o banco `mulheres_no_circo` (utf8mb4).
2. Importe `sql/mulheres_no_circo_utf8mb4.sql`.
   - Scripts auxiliares: `migracao-utf8mb4.sql`, `fase3-evolucao.sql`, `padronizar-areas.sql`.

## Ambiente local (XAMPP)
- Coloque a pasta em `C:\xampp\htdocs\mulheres-no-circo`.
- Inicie Apache + MySQL no painel do XAMPP.
- Acesse: `http://localhost/mulheres-no-circo`.
- Credenciais de teste: senha `12345678` (admin: thaisoliveira.rosalina@gmail.com).

## Configuração para deploy (InfinityFree)
- Edite `config/database.php` com host/usuário/senha do InfinityFree.
- Importe o dump utf8mb4 pelo phpMyAdmin.
- Os `.htaccess` já protegem `config/`, `includes/`, `sql/` e impedem execução em `uploads/`.

## Segurança implementada
- Senhas com `password_hash` (bcrypt).
- Prepared statements (PDO) em todas as consultas.
- Proteção CSRF nas ações de escrita.
- Rate limiting no login; `session_regenerate_id` ao autenticar.
- Validação de upload por extensão, tamanho e conteúdo real (`getimagesize`).
- Consentimento LGPD no cadastro.
