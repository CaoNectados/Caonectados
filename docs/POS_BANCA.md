# Integração pós-banca — 01/10/2026

Branch local: `pos-banca`, criada da main atualizada, sem merge completo da referência.

| Fonte Git | Hash confirmado após fetch |
|---|---|
| main / origin/main | 043ca071e0c3c9252cd38d6151dd83f7fb297337 |
| origin/requisitos-restantes | b3ea9b02c3d2d9cee9c248b91e1ea719709060fa |
| ancestral comum | 44dbe99c2099aa99fb8e6c31815b26d466ddd7f5 |

Fontes lidas, sem edição:
[Pos-banca CAONECTADOS](https://docs.google.com/document/d/1_MhxRawgnm_58vF22N4QjAXO3umznKMb0Hufdz8cEwg/edit),
quadros 2–4 e divergências com os protótipos;
[Caonectados_Controle](https://docs.google.com/spreadsheets/d/1lq7d9bYdcfjdYZvQ1T3Wu-7AkDilKVJ7ZF_1mAsr85g/edit),
abas Desenvolvimento e Requisitos. O atalho da pasta falhou; a planilha original
foi localizada e lida pela busca do Drive.

## O que foi integrado

- RF06: views de página da referência, rotas de consulta e edição, redes sociais
  com métodos faltantes e preservação de WhatsApp/outros ao sincronizar Instagram/Facebook.
  Backend da main preservado, com checagem de responsável habilitado e validação de tamanho. Corrigido o tipo do parâmetro de estado no catálogo e excluídos animais removidos.
- RF10: feed e filtros da referência; pesquisa textual integrada ao catálogo em
  `/pesquisar` e no filtro do feed, sem pesquisa administrativa ampliada.
  Animais de responsáveis não aprovados/contas desativadas/perfis desativados ficam
  fora do catálogo; interesses ativos escondem o animal somente para aquele adotante.
- RF08/RF09: telas e consultas pertinentes reaproveitadas; serviço adaptado para
  transações, locks, propriedade, maioridade, conta ativa, limite de 10/dia,
  repetição idempotente, transições, justificativa e cancelamento de concorrentes enviados.
  Decisão e histórico ficam na mesma transação. Não é criado chat.
  Detalhes do animal da main receberam apenas link do responsável e botão de Petisco.
- RF11: e-mails existentes preservados; serviço de eventos de adoção após commit,
  com destinatários envolvidos e falhas registradas. Sem fila/agendamento novo e sem
  mensagem afirmando envio não confirmado. Novos e-mails: interesse ao responsável;
  análise, aprovação, recusa e cancelamento aos envolvidos. Retentativa da mesma
  operação não gera novo histórico ou novo evento.
- RF14: controles de conta e perfil da main preservados. Classificação administrativa
  manual/reversível de inadimplência e histórico preparados, sem sanção automática
  nem ligação a denúncias. Interface pela gestão de usuários. Requer migração.
- RF15: fluxo existente mantido. Solicitação adicional preserva dados pessoais e
  perfil de adotante dentro da transação, sem restauração posterior à gravação;
  bloqueia duplicidade no serviço, controla reenvio e mantém aprovação/alternância.
  Motivo de recusa preparado para persistência. Comprovantes protegidos e entregues
  apenas a administrador. A recusa sem migração falha explicitamente, sem falso sucesso.

## Contratos e limites

Persistidos: `pendente` → Enviada; `em_analise` → Em análise; `aprovada` → Aprovada;
`reprovada` → Recusada; `cancelada` → Cancelada. Não foi criado estado concluída.
Petisco: POST `/solicitacoes/criar`, `animal_id` e `csrf_token`, resposta JSON
com `status`, `mensagem`, `solicitacao_id`. UI confirma/anima somente com HTTP de
sucesso e confirmação do servidor; respeita movimento reduzido.

Locks usam a mesma conexão PDO dos repositories e do histórico. Há proteção contra
falhas parciais; deadlock/erro do MySQL resulta em rollback e erro para repetir,
não em sucesso. A concorrência real deve ser ensaiada no MySQL antes da entrega.

## Remoções e preservação

Rotas, menus, botões, imports e dependências de chat, mensagens, denúncias,
contestações, relatórios/exportações, advertências automáticas e central de
notificações não fazem parte dos fluxos integrados. Arquivos exclusivos de denúncia
e relatório da própria main foram removidos após busca de consumidores.
Nenhuma tabela/dado foi apagado. O script SQL legado não foi substituído/executado;
tabelas antigas permanecem para compatibilidade e retenção. Históricos de solicitações
e animais permanecem utilizados. Os módulos excluídos não aparecem nas 104 rotas ativas.

A comparação com o ancestral mostrou seis arquivos modificados pela main:
PaginaController, ProtetorRepository, PaginaService, detalhes do animal e as duas
views de página. A base desses backends foi mantida; as views da referência
substituem os placeholders pertinentes e o detalhe do animal recebeu acréscimos,
preservando os campos, autorização de edição e exibição de saúde/descrição da main.
Usuário, login, animais, regiões, espécies/raças, reativação e alternância permanecem.

## Banco e configuração

Migração incremental: `app/database/migrations/20261001_pos_banca.sql`.
Adiciona motivo de recusa, flag/motivo de inadimplência, histórico dessa decisão
e dois índices. Não altera os ENUMs persistidos, não recria banco e não carrega exemplos.
Reversão comentada no arquivo; exportar os novos motivos/históricos antes de remover
essas estruturas. Revisar índices/colunas existentes antes de executar uma única vez.
Não aplicada em nenhum banco compartilhado/real. A leitura local confirmou InnoDB
nas sete tabelas do fluxo e ausência de motivo_recusa.

Inicialização automática do banco agora exige opção explícita do ambiente
(`ALLOW_DATABASE_INITIALIZATION=1`), para uma aplicação não criar/carregar banco
ao receber uma requisição. A tarefa não habilitou essa opção.

SMTP: `SMTP_HOST`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_FROM`; URL_BASE pelo ambiente.
Credencial SMTP fixa foi externalizada. Revogar/substituir a credencial exposta no
histórico Git; seu valor não foi copiado para documentação. Configuração Apache
e URL de acesso em [APACHE.md](APACHE.md). O servidor não foi reconfigurado.

## Testes executados

- 26 verificações em SQLite exclusivamente em memória: criação/evento após commit,
  repetição, dono, recusa com motivo, estados, aprovação/cancelamento de concorrente,
  segunda aprovação, reativação, futuro/menor, bloqueio/não aprovado, limite diário,
  rollback de criação e de aprovação e falha SMTP, além de preservação de contatos.
- 2 verificações adicionais do catálogo da página com Models reais: estado, dono e exclusão lógica.
- 104 contratos de rota: classes/métodos existentes, incluindo resolução de nomes
  com o autoload; sem módulos excluídos registrados.
- 28 verificações HTTP no Apache local (detalhadas em APACHE.md).
- Renderização PHP de oito views com dados sintéticos, sem acesso ao banco real,
  incluindo texto hostil; JavaScript embutido compilado pelo Node sem erro.
- Normalização do Router na raiz e duas subpastas, POST e query.
- Verificação de sintaxe PHP e ausência de erro de whitespace no diff.

SQLite não exercita locks, isolamento nem duas conexões simultâneas do MySQL.
Para repetir: `php tests/adocao.php`, `php tests/pagina_catalogo.php`,
`php tests/contratos.php`, `python tests/verificar_views.py` e `python tests/http_apache.py`.
O teste de views aceita ajuste de PHP_BIN/NODE_BIN para outros ambientes.

Renderização/sintaxe não comprovam layout em navegador ou acessibilidade.
Na entrega inicial não foram executados SMTP real, upload multipart autenticado, fluxo RF15 completo,
alternância com sessão real nem o caminho ponta a ponta em banco compartilhado.
Consulta ao banco local foi somente leitura; nenhum requisito foi marcado concluído.

## Revisão por responsável

| RF | Responsável da planilha | Código integrado | Revisão de frontend | Revisão de backend | Banco/permissões | Testes pendentes | Situação |
|---|---|---|---|---|---|---|---|
| RF06 | Leticia Correa | Views, rotas, redes; backend main | Layout, cropper, contatos, vazio, celular | Propriedade, visibilidade, salvamento | Upload; somente dono edita | Persistir/recarregar; outro usuário; imagens | Integrado; execução autenticada não validada |
| RF10 | Leticia Correa | Feed/filtros; busca no catálogo | Scroll, filtros combinados, teclado, estados de falha | Elegibilidade; interesse ativo; paginação | Responsável aprovado e perfil ativo | Dados reais, animais ocultos, paginação após Petisco | Integrado; execução autenticada não validada |
| RF08 | Ana Júlia | Petisco, acompanhamento, cancelamento | Feedback/animação só após sucesso; timeout | Idempotência, maioridade, 10/dia, dono | PDO compartilhado; CSRF; locks | 11º clique simultâneo; timeout real; POST autenticado | Integrado; regras testadas em banco isolado |
| RF09 | Ana Júlia | Painel, detalhes, análise, decisão | Dados da ficha, justificativa, filtros | Transições, aprovação e histórico atômicos | Locks; dono; envolvidos habilitados | Duas aprovações MySQL; falha intermediária; painel real | Integrado; concorrentes em análise dependem de decisão |
| RF11 | Giovana Kassime | Eventos depois do commit; e-mails main | Links e textos de aviso | Destinatários, SMTP false/exceção e logs | Configurar credenciais novas | Cada evento com SMTP controlado; reenvio após falha | Integrado; SMTP não validado; reenvio pendente |
| RF14 | Ana Clara | Bloqueios main; classificação manual e histórico | Classificação e reversão; estado exibido | Alcance da inadimplência e restrições definitivas | Migração; só administrador | Sessão antiga, alternância, reversão e logs | Classificação preparada; alcance bloqueado por decisão |
| RF15 | Giovana Kassime | Revisão do fluxo original; documentos; motivo | Solicitar/reenvio/status e motivo | Duplicidade/atomicidade/dados preservados | Migração do motivo; documento só admin | Adotante→pedido→decisão→alternância; rollback de upload | Integrado; execução real não validada |

## Decisões pendentes e limitações explícitas

1. Página pública versus RF16 exigir login: preservado o comportamento explicitamente
   definido no controller da main para páginas; feed/detalhes exigem autenticação.
2. Concluída versus aprovada: nenhuma nova transição; aprovação marca animal adotado.
3. Outro pedido em análise quando um é aprovado: aprovação impede esse caso até
   finalizar a análise. Só concorrentes enviados são cancelados automaticamente.
4. Solicitação em análise não marca automaticamente o animal em análise. Cancelamento
   não desfaz uma adoção; se não houver mais pedidos ativos e o animal estiver em análise,
   ele volta a disponível. Confirmar essa distinção com a equipe.
5. Inadimplência versus bloqueio global: classificação não dispara punição. Alcance,
   critérios e restrições continuam pendentes de definição; a conta global continua
   sendo bloqueada/desativada pela ação existente da main.
6. RF15 independente versus ONG e comprovante “quando exigido”: preservado o fluxo
   existente que aceita ambos e exige comprovante inicial; não ampliado/redefinido.
7. PIX e animais adotados na página: preservados os dados/comportamento do backend
   já existente, sujeitos à confirmação de escopo.
8. SMTP sem confirmação não informa envio. Não há retentativa automática nova;
   definir reenvio controlado e monitoramento operacional das falhas.
9. Busca fica no catálogo; não foi importada pesquisa administrativa ampliada.

Esta branch prepara a sequência feed → detalhes → Petisco → acompanhamento → painel
do responsável → análise/decisão → eventos de e-mail. Não equivale à conclusão de
todos os RFs: migração, SMTP, decisões acima e aceitação autenticada/MySQL/navegador
continuam com as responsáveis. Sem push, publicação ou merge na main.

## Lista exata de arquivos da entrega

### Criados

- `.htaccess`
- `app/controllers/geral/SolicitacaoAdocaoController.php`
- `app/database/migrations/20261001_pos_banca.sql`
- `app/repositories/HistoricoSolicitacaoRepository.php`
- `app/services/AdocaoEmailService.php`
- `app/services/AdocaoEstados.php`
- `app/services/ClassificacaoProtetorService.php`
- `app/views/admin/classificacao_protetor.php`
- `app/views/solicitacao/detalhes.php`
- `app/views/solicitacao/minhas.php`
- `app/views/solicitacao/painel.php`
- `docs/APACHE.md`
- `docs/POS_BANCA.md`

### Alterados

- `README.md`
- `app/config/config.php`
- `app/controllers/admin/DashboardController.php`
- `app/controllers/admin/SolicitacaoProtetorController.php`
- `app/controllers/admin/UsuarioController.php`
- `app/controllers/geral/FeedController.php`
- `app/controllers/geral/PaginaController.php`
- `app/controllers/onboarding/OnBoardingController.php`
- `app/core/Controller.php`
- `app/core/Router.php`
- `app/database/ConnectionFactory.php`
- `app/repositories/AnimalRepository.php`
- `app/repositories/FeedRepository.php`
- `app/repositories/ProtetorRepository.php`
- `app/repositories/RedeRepository.php`
- `app/repositories/SolicitacaoAdocaoRepository.php`
- `app/services/MailService.php`
- `app/services/OnBoardingService.php`
- `app/services/PaginaService.php`
- `app/services/SolicitacaoAdocaoService.php`
- `app/services/SolicitacaoService.php`
- `app/services/UploadService.php`
- `app/services/ValidationService.php`
- `app/views/admin/dashboard.php`
- `app/views/admin/gerenciar_usuarios.php`
- `app/views/admin/solicitacoes_detalhes.php`
- `app/views/animal/detalhes.php`
- `app/views/feed/feed.php`
- `app/views/onboarding/adotante_onboarding.php`
- `app/views/onboarding/protetor_onboarding.php`
- `app/views/pagina/editar.php`
- `app/views/pagina/publica.php`
- `app/views/perfil/perfil.php`
- `app/views/templates/header.php`
- `public/.htaccess`
- `public/index.php`

### Removidos

- `app/controllers/admin/DenunciaController.php`
- `app/controllers/admin/RelatorioController.php`
- `app/controllers/geral/RelatorioController.php`
- `app/repositories/DenunciaRepository.php`
- `app/repositories/RelatorioRepository.php`
- `app/services/RelatorioService.php`
- `app/views/admin/denuncias.php`
- `app/views/admin/relatorios.php`
- `app/views/relatorios/relatorios.php`


## Correção posterior: e-mail e administrador exclusivo

A dependência exclusiva das variáveis SMTP foi corrigida: a configuração privada existente foi preservada em `app/config/smtp.local.php`, ignorado pelo Git e inacessível por HTTP. Ambiente tem prioridade; TLS/587 e SSL/465 são configuráveis. Nenhuma credencial foi adicionada ao código versionado.

Recuperação de senha agora envia o link da rota `/redefinir-senha`, com validade de 30 minutos, em vez do código de outro fluxo. Nome nulo não causa erro de tipo. Falhas retornam indisponibilidade do envio e ficam registradas no log.

Administrador tem apenas perfil administrativo na sessão, inclusive quando dados antigos misturam perfis. O login aplica a identificação administrativa antes do segundo fator. O perfil não renderiza botão/modal de alternância; troca, onboarding e aprovação/ativação de perfis comuns para administradores são bloqueados. Não foram modificados registros reais para normalizar dados antigos.

Validação adicional:
- Conexão TLS e autenticação no SMTP real passaram, sem enviar mensagens.
- 104 rotas HTTP sem login: nenhuma falha 500 ou aviso PHP.
- 168 verificações GET autenticadas via execução PHP com banco MySQL local em transação somente leitura, para adotante, protetor e ONG: nenhuma falha de servidor. Excluídos reenvio de e-mail, logout e reativação; não há administrador ativo neste banco.
- Sete verificações de rotas administrativas com SQLite isolado: sessão administrativa exclusiva, perfil sem alternância e bloqueio 403 de troca/onboarding.
- Os testes anteriores de fluxo, catálogo, HTTP Apache, renderização, normalização e sintaxe também passaram.

`tests/` é material de verificação local, ignorado pelo Git e retirado dos commits desta branch ainda não publicados. Não integra a entrega ao GitHub. Os números acima não comprovam todos os formulários com gravação, entrega de mensagens ao destinatário ou fluxos simultâneos em MySQL. Migração e aceitação completa continuam pendentes conforme descrito acima.


## Revisão funcional após erro de redefinição de senha

Corrigido o campo `email` ausente no POST de `/redefinir-senha/processar`. O formulário agora envia email/código, exige as duas senhas, informa “Salvar Senha” e não restaura dados de recuperação pelo autosave.

A bateria ampliada reproduziu e corrigiu outras falhas:
- `salvarNovoUsuario` descartava o status ativo definido após confirmação do email. Agora persiste o status; onboarding também aceita o estado transitório usuario/pendente de cadastros antigos, sem liberar contas bloqueadas ou excluídas.
- Fotos recortadas da página e de animais eram ignoradas quando `$_FILES` continha um campo vazio. O conteúdo recortado agora tem prioridade, mantendo as validações do upload.
- `/aguardando-aprovacao` não recuperava o motivo de recusa persistido. A leitura usa primeiro o banco e foi verificada após novo login.
- Autosave restaurava tokens CSRF antigos. Tokens/códigos não são salvos nem restaurados; dados comuns continuam sendo recuperados. Teste reproduziu o erro antes e passou após a correção.

Validação com gravações foi executada em banco MySQL separado, com dados fictícios, estrutura/índices originais, 29 chaves estrangeiras reproduzidas e migração pós-banca aplicada exclusivamente nesse banco. O servidor HTTP de teste usou a aplicação real, com captura local dos emails para impedir envios a pessoas reais. A autenticação SMTP real já havia sido validada separadamente; entrega ao destinatário não foi testada.

Resultados:
- Recuperação completa: solicitação, campos renderizados, redefinição, login com nova senha e rejeição do link reutilizado.
- Cadastro/confirmar email, segundo fator administrativo, troca de senha/email do perfil.
- 224 acessos GET (56 por perfil de adotante, protetor, ONG e administrador), sem falhas de servidor; reenvio, logout e reativação ficaram fora dessa varredura e foram tratados nos testes de fluxo aplicáveis.
- 37 verificações no primeiro grupo funcional; segundo grupo concluiu CRUD de região/espécie/raça/animal, propriedade de animais, redes, imagens públicas, comprovantes privados, onboarding inicial de adotante/ONG/protetor, perfis adicionais, alternância, recusa/reenvio/aprovação e bloqueio/reativação de conta.
- 172 POSTs com campos ausentes e sessão válida, sem exceção ou aviso PHP não tratado. Exclusão de conta ficou fora dessa varredura destrutiva.
- Cinco verificações em duas conexões MySQL: criação repetida idempotente, interessados distintos, disputa de aprovações, estados finais e animal adotado.
- 26 verificações transacionais em memória, dois testes de catálogo, 104 contratos de rota, 28 verificações no Apache local e oito views/JavaScript continuaram passando.
- Tela de redefinição inspecionada no navegador com os quatro campos corretos, autosave desabilitado e nenhum erro JavaScript; não houve alteração de senha real pelo navegador.

O banco isolado, servidor de teste e arquivos enviados durante a bateria foram removidos ao concluir a revisão. Testes continuam locais e ignorados pelo Git. Nenhuma senha/conta real foi alterada e a migração não foi aplicada ao banco original.

Antes de promover a branch: aplicar a migração revisada no ambiente de destino após backup e validar a entrega de email nesse ambiente. As decisões funcionais pendentes listadas na revisão original não foram inventadas durante os testes. Os testes comprovam os cenários enumerados, sem prometer cobertura de todas as combinações possíveis de dados e ambiente.
