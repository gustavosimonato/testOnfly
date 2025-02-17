# Teste técnico back-end para Onfly

## Instalação e Configuração

Siga as instruções abaixo para configurar e executar o projeto localmente.

### Pré-requisitos

- PHP >= 8.2
- Composer - [Instalação do Composer](https://getcomposer.org/download/)
- MySQL ou outro banco de dados de sua escolha
- Docker Desktop (opcional, mas recomendado) - [Instalação do Docker](https://www.docker.com/products/docker-desktop)
  [este passo-a-passo utiliza o Docker Desktop]

### Passos de Instalação

1. Clone o repositório:

```
git clone https://github.com/gustavosimonato/testOnfly.git
```

2. Acesse o diretório do projeto:

```
cd testOnfly
```

3. Instale as dependências do Composer:

```
composer install
```

4. Copie o arquivo de ambiente:

```
cp .env.example .env
```

5. Inicie o ambiente Sail:

```
./vendor/bin/sail up -d
```

6. Execute as migrations:

```
./vendor/bin/sail artisan migrate
```

7. Execute os workers:

```
./vendor/bin/sail artisan queue:work
```

8. Importe o arquivo `NewCorban.postman_collection.json` no Postman.

Sistema rodando em http://127.0.0.1:80
