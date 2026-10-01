# Acesso sem /public

A configuração local lida em 01/10/2026 usa Apache 2.4.58, PHP 8.2.12,
DocumentRoot `C:/xampp/htdocs`, mod_rewrite carregado e AllowOverride All no htdocs.
O arquivo de VirtualHosts contém apenas exemplos comentados. Foram encontrados
o `.htaccess` de public e, após esta alteração, o da raiz do projeto; não havia
regra de projeto na raiz nem .htaccess nos diretórios pais consultados.

URL local: **http://localhost/Caonectados/**. Login: **http://localhost/Caonectados/login**.

## Solução aplicada ao projeto

A raiz encaminha internamente a requisição para public. Na segunda passagem,
public/.htaccess preserva arquivos e diretórios existentes e encaminha as rotas
para index.php. Não há redirecionamento externo para public nem RewriteBase fixo.
As consultas e o corpo POST são preservados. DirectorySlash Off em public evita
que o Apache revele o caminho interno ao pedir `/assets` sem barra final;
diretórios de recursos não são listáveis.

Router e Controller usam o caminho de URL_BASE, com limite de segmento, em vez
de SCRIPT_NAME (que passa a conter public após a reescrita). URL_BASE aceita
configuração pelo ambiente e tem o padrão `http://localhost/Caonectados`.
Cabeçalho, links, formulários e recursos continuam usando essa constante.

Arquivos internos, arquivos ocultos e comprovantes são bloqueados. Os comprovantes
legados continuam no local existente, mas só são entregues pelo endpoint autenticado
`/admin/solicitacoes/documento?id=ID`, que aplica a autorização administrativa,
restringe o caminho e valida o tipo. Não foram movidos arquivos internos para public.

## Opção preferida quando o ambiente permitir

Configuração para o administrador aplicar e revisar, **não aplicada ao servidor**:

```apache
<VirtualHost *:80>
    ServerName caonectados.local
    DocumentRoot "C:/xampp/htdocs/Caonectados/public"
    SetEnv URL_BASE "http://caonectados.local"
    <Directory "C:/xampp/htdocs/Caonectados/public">
        Options -Indexes -MultiViews +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Habilitar mod_rewrite, ajustar DNS/hosts para o nome escolhido e validar/recarregar
o Apache conforme o ambiente. Com esse VirtualHost, usar `http://caonectados.local/`.
Em produção, configurar domínio, HTTPS e URL_BASE correspondentes. Se hospedado
em outra subpasta, URL_BASE deve conter exatamente esse caminho externo, sem public.
SetEnv requer mod_env. O .htaccess de public também usa Options e DirectorySlash:
uma política de AllowOverride restrita deve permitir essas diretivas ou movê-las
para o bloco Directory. AllowOverride None não executa esta solução por .htaccess.

## Validação efetivamente executada

28 verificações de HTTP no Apache local: home/login 200; navegação direta;
POST inválido de login com query e resposta AJAX 400; 404 HTML e JSON;
JavaScript, imagem, fixture CSS e upload público 200; arquivos internos e comprovante
existente de teste 403; proteção de rotas autenticadas; ausência de `/public` em
Location e ausência de encadeamento para public/public.
O acesso sem barra final ao projeto acrescenta uma barra (301) e termina em 200
sem public. CSS e upload de teste foram criados temporariamente e removidos;
o estilo normal do projeto usa Tailwind e CSS embutido, sem uma pasta assets/css.

Três testes do Router exercitaram POST com consulta na raiz, em `/Caonectados` e
em `/sub/pasta`, com SCRIPT_NAME interno diferente do caminho externo.
Isso valida a normalização em PHP, não um VirtualHost real nessas outras bases.

Não foram executados login válido, POST autenticado com gravação ou SMTP no banco
real. O VirtualHost proposto e a implantação em outro servidor precisam de testes
HTTP próprios, inclusive de autorização, uploads, AJAX e concorrência MySQL.

Para repetir o teste local sem dados reais: `python tests/http_apache.py`.
