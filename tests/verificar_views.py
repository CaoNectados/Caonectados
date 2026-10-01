"""Renderiza dados sintéticos e compila JavaScript. Não conecta ao banco real."""
import subprocess, re, json, os
php=os.environ.get('PHP_BIN','C:/xampp/php/php.exe')
node=os.environ.get('NODE_BIN','C:/Users/giova/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node.exe')
views=['feed/feed','pagina/editar','pagina/publica','solicitacao/detalhes','solicitacao/minhas','solicitacao/painel','admin/classificacao_protetor','admin/gerenciar_usuarios']
for view in views:
    result=subprocess.run([php,'tests/render_views.php',view],capture_output=True,text=True,encoding='utf-8')
    if result.returncode: raise RuntimeError(view+': '+result.stderr)
    for js in re.findall(r'<script\b[^>]*>([\s\S]*?)</script>',result.stdout):
        if not js.strip(): continue
        check=subprocess.run([node,'-e','new (require("vm").Script)('+json.dumps(js)+');'],capture_output=True,text=True)
        if check.returncode: raise RuntimeError(view+': '+check.stderr)
    assert 'alert(1)</script>' not in result.stdout, view
    print('OK renderização e sintaxe JavaScript:',view)
for base in ['', '/Caonectados','/sub/pasta']:
    result=subprocess.run([php,'tests/roteamento.php',base],capture_output=True,text=True)
    assert result.returncode==0,result.stderr
    print(result.stdout.strip())
