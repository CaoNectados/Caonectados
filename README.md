# CãoNectados

## Descrição
O **CãoNectados** é uma plataforma web de intermediação de adoção de animais, desenvolvida para conectar adotantes, protetores, ONGs e administradores em um único ambiente digital. O sistema centraliza os dados dos animais, organiza o fluxo de solicitações de adoção e facilita a comunicação entre as partes envolvidas.

A proposta responde ao cenário de abandono animal na região de Foz do Iguaçu e fronteira, oferecendo uma solução tecnológica para ampliar a visibilidade dos animais disponíveis para adoção e dar mais estrutura ao processo de gestão das adoções.

## Público-Alvo
O sistema foi pensado para atender quatro perfis principais de usuários:

- **Adotantes**: pessoas interessadas em encontrar e adotar animais.
- **Protetores**: responsáveis pelo resgate, cuidado e divulgação de animais disponíveis para adoção.
- **ONGs**: organizações que atuam na proteção animal e que precisam gerenciar seus animais, solicitações e páginas institucionais.
- **Administradores**: usuários com acesso de gestão geral do sistema, responsáveis por cadastros, moderação e manutenção da base de dados.

## Stack Tecnológica
- **PHP 8** com arquitetura **MVC própria** (sem framework), autoload via **Composer/PSR-4**
- **MySQL** como SGBD, acesso via **PDO**
- **Tailwind CSS** para estilização da interface
- **PHPMailer** para envio de e-mails transacionais (verificação de conta, 2FA, recuperação de senha)
- **JavaScript vanilla** no front-end (sem framework de UI)
- **Figma** para prototipação e design de telas

## Arquitetura e Padrões do Projeto
O projeto segue uma arquitetura MVC em camadas, com uma regra central: **regra de negócio e validação nunca ficam na View, e o mínimo possível fica no Controller.**

```
Requisição
   │
   ▼
Router (app/core/Router.php)          resolve a rota e instancia o Controller
   │
   ▼
Controller (app/controllers/**)       lê a requisição (GET/POST/$_FILES), chama o Service
   │                                  correspondente e devolve JSON ou uma View — não
   │                                  contém regra de negócio nem SQL.
   ▼
Service (app/services/**)             orquestra a regra de negócio, valida os dados
   │                                  (delegando ao ValidationService), controla
   │                                  transações e chama um ou mais Repositories.
   ▼
Repository (app/repositories/**)      única camada que sabe falar com o banco (PDO);
                                       recebe/retorna Models ou arrays, sem regra de
                                       negócio.
```

- **`app/services/ValidationService.php`** é o ponto único de validação do back-end: campos obrigatórios, nome, maioridade, telefone, e-mail, força de senha, CPF/CNPJ, chave PIX, links de rede social e datas. Qualquer Controller ou Service que precise validar um dado de entrada deve reutilizar um método daqui em vez de reescrever `preg_match`/`filter_var` localmente.
- **`public/assets/js/validacoes.js`** (objeto `CaonectadosValidator`) é o equivalente no front-end: toda validação de formulário (CPF/CNPJ, e-mail, telefone, tamanho de arquivo, links sociais, chave PIX, datas) fica centralizada ali e é reutilizada pelas views via `CaonectadosValidator.<método>`. A validação client-side é só para feedback imediato do usuário — a validação de back-end no `ValidationService` é sempre a autoridade final.
- **Repositories** encapsulam todo o SQL; Services nunca montam queries diretamente. Um Service pode combinar dados de múltiplos Repositories (ex.: `PerfilService` lê de `UsuarioRepository`, `AdotanteRepository` e `PaginaRepository` para montar a tela de perfil).
- **Models** (`app/models/**`) são objetos de domínio simples (getters/setters), sem lógica própria.
- **Helpers** (`app/helpers/ViewHelper.php`) concentram pequenas funções utilitárias usadas diretamente nas views (ex.: escaping de saída).

### Estrutura de Pastas
- **app/core**: classes base do framework (Router, Controller, BaseRepository).
- **app/config**: configurações gerais da aplicação e conexão com o ambiente local.
- **app/controllers**: controladores responsáveis por receber requisições e coordenar o fluxo da aplicação.
- **app/models**: classes de domínio que representam as entidades do sistema.
- **app/repositories**: classes responsáveis pelo acesso e persistência dos dados.
- **app/services**: camada de regra de negócio, validação e coordenação entre controllers e repositories.
- **app/views**: arquivos de visualização da aplicação.
- **app/views/templates**: templates reutilizáveis de interface (header, footer, modais).
- **app/database**: estrutura do banco de dados, scripts SQL e inicialização.
- **app/helpers**: utilitários auxiliares usados pelas views.
- **public**: ponto de entrada da aplicação (`index.php`, com a definição das rotas) e recursos públicos.
- **public/assets/css**: estilos da interface.
- **public/assets/js**: scripts do front-end (`validacoes.js` centraliza as validações de formulário).
- **public/assets/img**: imagens e recursos visuais.

## Como Executar o Projeto Localmente
1. Clone o repositório para a pasta `htdocs` do XAMPP.
2. Abra o XAMPP Control Panel e garanta que o **Apache** e o **MySQL** estejam em execução.
3. Acesse o arquivo [app/config/config.php](app/config/config.php) e ajuste as constantes de conexão, se necessário (por padrão usa `localhost`, banco `caonectados`, usuário `root` sem senha, e `URL_BASE` apontando para a pasta `public`).
4. No phpMyAdmin (ou client MySQL de sua preferência), crie o banco `caonectados` e importe o script [app/database/scripts/scripts.sql](app/database/scripts/scripts.sql) para criar as tabelas.
5. Instale o [Composer](https://getcomposer.org/) (ele detecta automaticamente o PHP do XAMPP em `C:\xampp\php\php.exe`).
6. No terminal, na raiz do projeto, rode:
   ```bash
   composer install
   ```
7. Acesse a aplicação pelo navegador em `http://localhost/Caonectados/public` (ou pelo caminho equivalente ao nome da pasta do projeto dentro de `htdocs`).

## Membros da Equipe
- Ana Clara Cordeiro Batista
- Ana Júlia Souza Toledo
- Giovana Kassime de Souza Chaerki
- Leticia Correa de Araujo
