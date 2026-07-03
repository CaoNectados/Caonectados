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

## Tecnologias Utilizadas
O projeto utiliza as seguintes tecnologias e ferramentas:

- **PHP** com arquitetura **MVC puro**
- **MySQL** como SGBD
- **Tailwind CSS** para estilização da interface
- **Figma** para prototipação e design de telas

## Estrutura do Projeto
A organização do projeto segue uma arquitetura MVC customizada, com separação clara entre responsabilidades:

- **app/core**: classes base do framework, como autoload, router, controller e repositório base.
- **app/config**: configurações gerais da aplicação e conexão com o ambiente local.
- **app/controllers**: controladores responsáveis por receber requisições e coordenar o fluxo da aplicação.
- **app/models**: classes de domínio que representam as entidades do sistema.
- **app/repositories**: classes responsáveis pelo acesso e persistência dos dados.
- **app/services**: camada de regra de negócio e coordenação entre controllers e repositories.
- **app/views**: arquivos de visualização da aplicação.
- **app/views/templates**: templates reutilizáveis de interface.
- **app/database**: estrutura do banco de dados, scripts SQL e inicialização.
- **app/helpers**: utilitários e validações auxiliares.
- **public**: ponto de entrada da aplicação e recursos públicos.
- **public/assets/css**: estilos da interface.
- **public/assets/js**: scripts do front-end.
- **public/assets/img**: imagens e recursos visuais.

## Como Executar o Projeto Localmente
1. Clone o repositório para sua máquina local.
2. Abra o projeto no XAMPP e garanta que o Apache e o MySQL estejam em execução.
3. Acesse o arquivo [app/config/config.php](app/config/config.php) e ajuste as constantes de conexão, se necessário.
4. Verifique se o banco está configurado com:
	- host local do MySQL
	- banco com o nome `caonectados`
	- usuário `root`
	- senha vazia
	- URL base apontando para a pasta `public` do projeto
5. No MySQL, importe o arquivo [app/database/scripts/scripts.sql](app/database/scripts/scripts.sql) para criar o banco e as tabelas.
6. Configure o projeto para ser executado pela pasta `public` como diretório de entrada.
7. Acesse a aplicação pelo navegador usando o endereço local configurado no XAMPP.

## Membros da Equipe
- Ana Clara Cordeiro Batista
- Ana Júlia Souza Toledo
- Giovana Kassime de Souza Chaerki
- Leticia Correa de Araujo