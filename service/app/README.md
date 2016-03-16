Configurando o ambiente de desenvolvimento da aplicação
=======================================================

1. Versão do PHP e extensões necessárias
----------------------------------------

Utilizar o PHP versão 5.3.x ou posterior.

Habilitar / instalar as seguintes extensões:

  * mcrypt
  * PDO para uso com o Mysql

2. Clonar o repositório do framework
------------------------------------

No diretório onde deverá ser criado o projeto, digitar:

$ git clone git@github.com:inkuba@framework-php.git


3. Configuração de conexão ao banco de dados
--------------------------------------------

Se for usar MongoDb:

- Deve ser instalada a versão 2.4.x ou superior.
- Seguir os padrões de modelagem de dados descritos aqui: http://docs.mongodb.org/manual/applications/data-models/

Alterar as configurações relativas ao banco de dados (host, usuário, senha, ...) no arquivo: /app/config/dev.php

4. Instalando as bibliotecas de terceiros
-----------------------------------------

As bibliotecas de terceiros são adicionadas com o Composer.

Se o Composer não estiver instalado, executar no diretório raiz do projeto:

    $ curl http://getcomposer.org/installer | php

Para instalar as bibliotecas executar:

    $ php composer.phar install

5. Criar banco de dados e tabelas iniciais
------------------------------------------

Executar o comando abaixo no diretório raiz do projeto:

    $ php app/inkuba instala

6. Verificar IPs permitidos
---------------------------

- ***IP do servidor***: Por padrão está permitido para os IPs localhost. Para testar com VMs adicionar o IP da VM no array e ao passar para produção, adicionar o(s) IP(s) de produção permitidos. Para alterar os IPs permitidos, modificar o array na configuração 'ip.servidor' do arquivo /app/config/slim.php

- ***IPs de acesso a documentação http://localhost/doc ***: O acesso a documentação é restrito aos IPs definidos no arquivo .htaccess do diretório /doc (por padrão, localhost). Alterar o arquivo .htaccess, adicionando os IPs permitidos para acessar a documentação, no caso de VM, adicionar o IP da mesma na configuração Allow from, por exemplo, para adicionar o IP 10.10.10.10:

    Allow from 10.10.10.10

7. Gerar documentação
---------------------

Para gerar a documentação, executar o comando no diretório raiz do projeto:

    $ php app/inkuba geraDocs


Bibliotecas da aplicação
========================

As bibliotecas da aplicação contém a lógica que se repete em vários projetos e pode ser reaproveitada nos controladores.

As bibliotecas ficam localizadas no diretório /lib :

- ***Authentication***: relacionada a autenticação, incluindo autenticação com formulário (usuário e senha), Facebook, ...
- ***Db***: conexão e manipulação do banco de dados (crud)
- ***Exception***: classes para tratar exceções
- ***Log***: classes para log em banco de dados e em arquivos
- ***Maiden***: classes para gerenciar automatizações executadas via linha de comando (criar tabelas, gerar documentação, ...)
- ***Middleware***: classes de middleware do Slim
- ***Upload***: classe para gerenciar upload de arquivos

Quando pertinente, as bibliotecas são "injetadas" na instância da aplicação ($app) no arquivo de serviços: /app/bootstrap/Services.php,
para torná-las acessíveis para toda a aplicação. Por exemplo, para adicionar a biblioteca de upload:

    $this->app->container->singleton('uploader', function () use ($app, $config) {
        return new Uploader($app->arquivoCrud, $config, $app->logdb);
    });


Banco de dados
==============

Banco de dados de teste:

O script com uma estrutura e dados de teste/iniciais encontra-se no diretório */data*.

Requisitos:

Para o MySQL:
- PDO habilitado no php.ini

Para o MongoDB:
- MongoDB habilitado no php.ini - http://ca1.php.net/manual/en/mongo.installation.php

Para usar o MySQL alterar a configuração 'database' para 'mysql' no arquivo config/slim.php, se for MongoDB alterar para 'mongo'.

Status
------

Adicionada verificação automática da coluna 'status' nas classes CRUD, então, todas as tabelas precisam ter esta coluna e
ela deve ser setada com o padrão 1 no MySQL.

MongoDb
-------

Considerando a coleção $usuario instanciada em um determinado controller da seguinte forma (onde Usuario é a classe do modelo):

    $usuario = new Usuario(array(), $app->db);

***Busca***

Para trazer todos os usuários:

    $usuario->findAll();

Para trazer todos os usuários com nome igual a Maria:

    $usuario->find(array('nome' => 'Maria'));

Para trazer todos os usuários com nome igual a Maria, mas retornando somente as colunas nome e email:

    $usuario->find(array('nome' => 'Maria'), array('nome'=>1, 'email'=>1));

Para trazer todos os usuários com nome igual a Maria ou e-mail igual a user@exemplo.com:

    $usuario->find(array('$or' => array(
       array('nome'  => 'Maria'),
       array('email' => 'user@exemplo.com')
    )));

Para trazer somente um regisro com email igual a user@exemplo.com:

    $usuario->findOne(array('email' => 'user@exemplo.com'));

Referência comparativa entre SQL / MongoDb:
http://docs.mongodb.org/manual/reference/sql-comparison/

***Adição***

    $params = $app->request;

    $usuario->nome    = $params->post('nome');
    $usuario->email   = $params->post('email');

    $res = $usuario->create();

***Atualização***

    $params = $app->request;

    $usuario->idade  = $params->params('idade');

    $res = $usuario->save(array("_id" => new \MongoId($id))));

***Exclusão***

    $res = $usuario->delete(array('email' => 'user@exemplo.com'));

MySQL
-----

***Busca***

Somar os valores de um campo:

    $somaIdade = $pessoas->sum('idade');

Valor máximo de um campo:

    $maiorIdade = $pessoas->max('idade');

Valor mínimo de um campo:

    $menorIdade = $pessoas->min('idade');

***Atualização***

    $params = $app->request;

    $usuario->idade = $params->params('idade');

    // o(s) campo(s) são informados no formato array no primeiro parâmetro, sendo a chave o nome do(s) **placeholder(s)** para o bind de variáveis do mysql
    // a(s) condição(ões) são informadas no segundo parâmetro
    $res = $usuario->save(array('id' => $id), 'id = :id'); 

***Exclusão***

    // o(s) campo(s) são informados no formato array no primeiro parâmetro, sendo a chave o nome do(s) **placeholder(s)** para o bind de variáveis do mysql
    // a(s) condição(ões) são informadas no segundo parâmetro
    $res = $usuario->delete(array('id' => $id), 'id = :id');

Instanciar as classes CRUD nos controladores
--------------------------------------------

- Método 1: instanciar a classe no inicio do arquivo do controlador e passar a variável para as closures
    $cliente = new Cliente(array(), $app->db);

    nas closures:
    $app->patch('/cliente/:chave/:id', function ($chave, $id) use ($app, $cliente) {
        $params = $app->request;

        $cliente->nome = $params->params('nome');

        $res = $cliente->save(array("id" => $id), 'id = :id');

        echo json_encode($res);
    });

- Método 2: definir na inicialização da app (em Services.php) e acessar diretamente nas closures
    definição (em Services.php):
        $this->app->container->singleton('clienteCrud', function () use ($app) {
            return new Cliente(array(), $app->db);
        }); 
    
    acesso dentro da closure:
        $app->clienteCrud;


Configuração
============

As configurações da aplicação encontram-se no arquivo /app/config/slim.php

Debug
-----

Para ativar o debug, alterar a configuração ***debug*** para ***true***.


As configurações da conexão com o banco de dados encontram-se nos arquivos /app/config/dev.php e /app/config/prod.php


Funcionalidades
===============

Injeção de Dependência
----------------------

No arquivo *Services.php* os serviços podem ser definidos no escopo da aplicação, ficando disponíveis para todas as URLs (controladores).


Log
---

### No banco de dados:

Para as ações de usuário

Classe DbWriter

Uso:

    $app->logdb->write($usuario, $msg);


### No diretório log:

Para linha de comando.

Classe DateTimeFileWriter

Documentação
------------

Os arquivos html e de assets (js, css, imagens), que devem ser personalizados, ficam localizados no diretório /doc.
Os arquivos gerados a partir dos comentários no código, ficam dentro de /doc/api. Esses arquivos não devem ser modificados manualmente, pois a modificação será perdida sempre que for gerada novamente a documentação.

Segue a referência sobre os comentários a serem adicionados no código, para gerar a documentação:
http://zircote.com/swagger-php/


E-mail
------

Para o envio de e-mails é utilizada a biblioteca SwiftMailer.
Os templates dos e-mails devem ficar no diretório /templates no formato do Twig para adicionar código PHP.


Validação e filtragem de dados
------------------------------

A validação é feita com a classe ***Validacao*** que está disponível para a aplicação como o serviço: ***$app->validacao***

Segue um exemplo de validação e filtro:

    $valida = $app->validacao;
    $post   = $params->post();

    $valida->validation_rules(array(
        'titulo'    => 'required|max_len,10|min_len,6',
        'descricao' => 'required',
    ));

    $valida->filter_rules(array(
        'titulo'      => 'sanitize_string|trim',
        'descricao'   => 'trim'
    ));

    $dadosValidados = $valida->run($post);

    if($dadosValidados === false) {
        $app->response->setStatus(400);

        echo json_encode( array(
            'cod'   => 400,
            'res'   => 'Favor verificar os erros',
            'erros' => $valida->get_readable_errors()
        ));

        return;
    }

Mais exemplos de validações e filtros possíveis podem ser encontrados em https://github.com/Wixel/GUMP e na classe lib/validacao/validacao.php


Autenticação
------------

Aplicar hash na senha:

     $app->auth->hashPassword($senha);

Gerar token de confirmação de troca de senha:

     $app->auth->geraTokenConfirmacao();


Formato da resposta
-------------------

Por padrão, as respostas são definidas no formato JSON no método addDefaultHeaders da classe app/bootstrap/SlimBootstrap.php.

Para a interface administrativa (URLs que contém /admin) o formato é definido como HTML no middleware lib/middleware/HtmlResponse.php. Para retirar esse formato, remover a linha abaixo no método addMiddleware da classe app/bootstrap/SlimBootstrap.php:

    $app->add(new HtmlResponse());


Linha de Comando
================

Para verificar os comandos disponíveis digitar no diretório raiz do projeto:

    $ php app/inkuba

Os comandos sempre devem ser executados no diretório raiz do projeto.

O log da execução dos comandos fica registrado no diretório /log.

Preparar ambiente de desenvolvimento
------------------------------------

Criar banco de dados e tabelas iniciais:

    $ php app/inkuba instala


Gerar interface administrativa
------------------------------

    $ php app/inkuba geraAdmin


Gerar documentação
------------------

Usar o comando abaixo para gerar a documentação (ele também é automaticamente executado ao realizar um deploy):

    $ php app/inkuba geraDocs

A documentação pode ser acessada através da URL: http://localhost/framework-php/doc


Cria banco de dados, gera interface administrativa e documentação
-----------------------------------------------------------------

    $ php app/inkuba geraTudo


Segurança
=========

Autenticação da aplicação frontend
----------------------------------

A autenticação da aplicação frontend requisitando a API é realizada por endereço IP.

No arquivo de configuração **app/config/slim.php** na opção **'ip.servidor'** deve ser definido o IP do servidor que será
comparado com o da máquina requisitando a API e, caso forem diferentes será retornado o código 401 - "Você não está autorizado
a acessar esta página". Por padrão esta configuração está definida com o IP do servidor que rodando a aplicação backend: $_SERVER['SERVER_ADDR']


Autenticação do usuário
-----------------------

Existem dois tipos de autenticação possíveis:

1. ***Sessão***: informar 'sessao' na diretiva 'auth.type' em app/config/slim.php
2. ***Token (chave) de API***: informar 'chave' na diretiva 'auth.type' em app/config/slim.php

Além desses dois tipos é possível utilizar juntamente com um deles a autenticação com Facebook.

Pode ser utilizado o banco de dados MySQL ou MongoDb para as tabelas de segurança (usuario, grupo, rota). Definir no 
arquivo de configuração app/config/slim.php na diretiva 'provider' qual será o banco usado:

- ***MySQL***:   'provider' => 'PDO'
- ***MongoDb***: 'provider' => 'MongoDb'

As permissões do usuário serão gravadas:

- ***MySQL:*** Nas tabelas tbl_rota, tbl_usuario_grupo, tbl_usuario
- ***MongoDB:*** Nas coleções tbl_rota, tbl_usuario

Segue uma referência sobre modelagem de dados com o MongoDB: http://docs.mongodb.org/manual/applications/data-models/


Autenticação por sessão
-----------------------

Utilizada para a aplicação de administração. Funciona da seguinte forma:

1. O usuário realiza login e são gravadas em sessão as informações de autenticação

2. Todas as requisições subsequentes serão automaticamente autenticadas, sem a necessidade de informar novamente os dados de autenticação.


Autenticação por token (chave) de API
-------------------------------------

1. O usuário realiza login e recebe uma chave de autenticação (token). Para testar usar: POST /login

2. O frontend deverá guardar em uma variável essa chave de autenticação para fazer as próximas requisições (chamadas à API).

3. Se na requisição com chave, a chave for inválida ou inexistente, será retornada a mensagem: "404 - Recurso não foi encontrado"

4. Se o usuário, com chave válida, não possui acesso a URL sendo requisitada, será retornada a mensagem: "401 - Você não está autorizado a acessar esta página"

5. A URL /logout remove a chave informada no banco de dados.


Autenticação com Facebook
-------------------------

O login com Facebook precisa de um template na aplicação backend, localizado em /templates, que conterá a URL gerada.
A URL para teste é: GET /loginfb .


Gerar senha usando a função crypt com algoritmo bcypt (blowfish)
----------------------------------------------------------------

Exemplo para gerar hash no arquivo \lib\Authentication\Provider\MongoDb.php e no \lib\Authentication\Provider\PDO.php :

    public function hashPassword($password)
    {
        $salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); // MCRYPT_DEV_URANDOM - lê dados de /dev/urandom

        // Gera um hash utilizando bcrypt
        $hashSenha = crypt($password, '$2a$' . $this->custo . '$' . $salt . '$');

        return $hashSenha;
    }

Como verificar:

    crypt($senha, $usuario->hashSenha) === $usuario->hashSenha

onde:

    - $senha - é a senha sem criptografia, informada pelo usuário
    - $usuario->hashSenha - é a senha com criptografia, gravada no banco de dados


Gerador de Interface Administrativa
===================================

O gerador de interface administrativa é executado através do comando:

    $ php app/inkuba geraAdmin

As configurações usadas para gerar a interface administrativa são obtidas do arquivo: ***app/config/admin.php***

***Observações importantes:***

    - As URLs administrativas devem ser precedidas com ***/admin***
    - O tipo de autenticação deve ser ***sessao***
    - Os nomes das opções definidas no arquivo de configuração ***admin.php*** devem usar _ (underline) e não - (traço), pois o traço tem um significado especial no Twig, e se for usado aqui, ocasionará erros.

Opções no arquivo ***app/config/admin.php***
--------------------------------------------

  * Para definir um nickname personalizado para uma operação da documentação, adicionar a opção 'nickname' na 'closure'

  * Possíveis ações conforme cada método:
    POST - criar e editar
    GET  - unico e todos

  * URLs conforme método e ação:
    Ex. Novo cadastro: GET  /cases/novo
                       POST /cases/novo
                       (usada mesma closure)
    Ex. Editar cadastro: GET  /cases/1
                         POST /cases/1
                        (usadas closures diferentes)

  * A opção 'model' em 'controladores' indica o modelo principal a ser usado nas closures para as operações do BD

  * Informar na opção 'projeto' o nome do projeto que deve ser exibido no barra de navegação do topo

  * Para a página inicial usar o 'nome' do controlador como 'home' para carregar o template adequado

Opções para o menu do topo
--------------------------

As descrições e URLs do menu da barra de navegação do topo são definidas na opção 'admin.menu' do arquivo de configuração ***app/config/slim.php***.

Métodos HTTP
============

Quando usar:

    GET    - buscar um recurso
    POST   - criar um recurso ou executar ações personalizadas
    PUT    - atualizar todos os campos de um recurso (substituir um recurso) ou criar um novo recurso
    PATCH  - atualizar apenas alguns campos (um ou mais) de um recurso
    DELETE - excluir um recurso

OBS: Quando a edição de dados conter arquivos, utilizar o método POST ao invés de PUT/PATCH pois os cabeçalhos
da resposta não são atribuídos a $_POST e $_FILES ao trabalhar com multipart/form-data e o php://input também não
está disponível.

Ver referências:
- http://stackoverflow.com/questions/9464935/php-multipart-form-data-put-request
- http://stackoverflow.com/questions/4007969/application-x-www-form-urlencoded-or-multipart-form-data
- https://bugs.php.net/bug.php?id=55815
-  php://input is not available with enctype="multipart/form-data" 
    * http://www.php.net/manual/pt_BR/wrappers.php.php#wrappers.php.input 
    * também em: \vendor\slim\slim\Slim\Environment.php linha 165

A Definir
=========

Autenticação
------------

1. Autenticação com Facebook
   - verificar se serão definidos grupos no momento do login (onde será criado usuário no banco) ou usuário ficará com status pendende
   - como será diferenciado o usuário com login e apenas o allow para aplicação (configuração?)
