# CãoNectados

Plataforma web que aproxima pessoas interessadas em adotar animais de ONGs e protetores independentes. O sistema reúne os animais disponíveis, facilita o envio e acompanhamento de solicitações e oferece ferramentas de gestão para os responsáveis pelos perfis.

## Recursos principais

- Cadastro de adotantes, ONGs e protetores independentes.
- Cadastro de animais com galeria de fotos e informações de perfil.
- Busca e filtros de animais para adoção.
- Envio e acompanhamento de solicitações de adoção.
- Painel administrativo para gerenciar usuários, animais e cadastros.
- Interface adaptável para desktop e celular.

## Tecnologias

- PHP 8 e MySQL
- JavaScript
- Tailwind CSS
- Composer e PHPMailer

## Como executar localmente

1. Instale o [XAMPP](https://www.apachefriends.org/) e o [Composer](https://getcomposer.org/).
2. Coloque o projeto em `htdocs/Caonectados` e inicie o Apache e o MySQL pelo XAMPP.
3. Crie o banco de dados `caonectados` e importe `app/database/scripts/scripts.sql` pelo phpMyAdmin.
4. Confira as configurações locais em `app/config/config.php`.
5. Na pasta do projeto, instale as dependências:

   ```bash
   composer install
   ```

6. Acesse `http://localhost/Caonectados/` no navegador.

O envio de e-mails é opcional para executar o sistema localmente. Para habilitá-lo, configure as credenciais SMTP no ambiente local; não publique senhas ou chaves no repositório.

## Equipe

- Ana Clara Cordeiro Batista
- Ana Júlia Souza Toledo
- Giovana Kassime de Souza Chaerki
- Leticia Correa de Araujo
