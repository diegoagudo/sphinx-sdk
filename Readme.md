# Sphinx Sdk

### Instalação
##### Passo 01
Abra seu `composer.json` e na seção `repositores` adicione:
  ```json
"repositories": [
   {
     "url": "https://github.com/plataforma13/sphinx-sdk.git",
     "type": "git"
    }
 ],
```
Em seguida na seção `require` adicione:
```json
"require": {
	"plataforma13/sphinx-sdk": "master"
}
```
##### Passo 02
No console, digite:
```bash
# composer require plataforma13/sphinx-sdk
```
para instalar o pacote.

##### Passo 03
Verifique no seu `config/app.php` na seção de `providers` se foi adicionado de forma automática a chamada do provider do Sphinx Sdk, caso não, adicione:
```php	
Plataforma13\SphinxSdk\Providers\SphinxSdkProvider::class,
```
### Pós Instalação
Caso tenha ocorrido tudo certo com a instalação, ao digitar `php artisan route:list` irá aparecer a rota:
| Método  | URL | Classe |
|--|--|--|
| GET\|HEAD | auth/callback | Plataforma13\SphinxSdk\Http\Controllers\SphinxController@callback |
esta é a url de callback que o Sphinx irá chamar após autorização e autenticação do Login.

### Configuração
Crie um arquivo chamado `sphinx.php` dentro do `config` com as seguintes informações:
```php
return [  
  'client_id' => '36b51c1d-0952-444e-9b6d-ee98a83ac1bf',  
  'redirect_url' => 'http://auth.sphinx.docker/auth/callback',  
  'host' => 'auth.sphinx.docker',  
  'secret' => '8SeybVMMvmE2zBzSvbaAhHoj7D7GxaUBFketmMHb',  
];
```
lembre-se de atualizar o cache da config.

