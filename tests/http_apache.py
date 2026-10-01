"""Aceitação local sem autenticar usuários reais ou gravar no banco."""
from pathlib import Path
from urllib.request import Request, build_opener, HTTPRedirectHandler
from urllib.error import HTTPError
import json

BASE = 'http://localhost/Caonectados'
class SemRedirect(HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs): return None
opener = build_opener(SemRedirect)
def request(path, method='GET', data=None, headers=None):
    req=Request(BASE+path,data=data,method=method,headers=headers or {})
    try: response=opener.open(req,timeout=10)
    except HTTPError as error: response=error
    return response.code, response.headers, response.read()

public=Path(__file__).resolve().parents[1]/'public'
probes={public/'assets/_url-probe.css':b'body { color: black; }',
        public/'assets/uploads/_url-probe.png':(public/'assets/img/logo.png').read_bytes(),
        public/'assets/uploads/comprovantes/_url-probe.pdf':b'%PDF-1.4\n'}
try:
    for path, content in probes.items():
        if path.exists(): raise RuntimeError('Fixture já existe: '+str(path))
        path.parent.mkdir(parents=True,exist_ok=True)
        path.write_bytes(content)
    checks={'/':200,'/login':200,'/home?origem=teste&a=1':200,
            '/assets/js/menu.js':200,'/assets/img/logo.png':200,'/assets/_url-probe.css':200,
            '/assets/uploads/_url-probe.png':200,'/rota-inexistente':404,
            '/app/config/config.php':403,'/vendor/autoload.php':403,'/.git/config':403,
            '/composer.json':403,'/app/database/scripts/scripts.sql':403,
            '/assets/uploads/comprovantes/_url-probe.pdf':403,
            '/public/assets/uploads/comprovantes/_url-probe.pdf':403,'/assets':403,
            '/public/public/login':404,
            '/chats':404,'/admin/denuncias':404,'/relatorios':404,'/admin/relatorios/exportar-csv':404}
    for path, expected in checks.items():
        code,headers,body=request(path)
        assert code==expected,(path,code,expected)
        assert '/public' not in headers.get('Location',''),(path,headers.get('Location'))
        print('OK',path,code)
    for path in ['/feed','/solicitacoes','/minhas-solicitacoes','/admin/solicitacoes/documento?id=1']:
        code,headers,body=request(path)
        assert code==302 and headers.get('Location')==BASE+'/login',(path,code,headers)
        print('OK proteção de acesso',path)
    code,headers,body=request('/login?origem=teste','POST',b'email=&senha=',{'Accept':'application/json','Content-Type':'application/x-www-form-urlencoded'})
    assert code==400 and json.loads(body)['status']=='erro'
    print('OK POST/AJAX com query; erro 400 JSON')
    code,headers,body=request('/inexistente?x=1',headers={'Accept':'application/json'})
    assert code==404 and json.loads(body)['status']=='erro'
    print('OK 404 AJAX com query')
    code,headers,body=request('/pagina-perfil/atualizar','POST',b'descricao=teste',{'Accept':'application/json'})
    assert code==403 and json.loads(body)['status']=='erro'
    print('OK POST sem CSRF rejeitado')
    print('28 verificações HTTP passaram no Apache local. Fluxos autenticados reais não foram executados.')
finally:
    for path,content in probes.items():
        if path.exists() and path.read_bytes()==content: path.unlink()
