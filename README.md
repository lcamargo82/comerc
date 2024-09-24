# Projeto Comerc

Este projeto é uma API desenvolvida em Laravel. A seguir, estão as instruções para configurar e executar o projeto em um ambiente Docker.

## Pré-requisitos

- Docker
- Docker Compose

## Instruções

### 1. Clone o repositório do GitHub

Clone o repositório para sua máquina local:

```bash
git clone git@github.com:lcamargo82/comerc.git
```

### 2. Acesse a pasta do projeto

Acesse a pasta do projeto para configurar o env:

```bash
cd comerc/dexian_comerc/comerc_api
```

### 3. Configure o .env

Copie o arquivo env.exemple para .env:

```bash
cp .env.example .env
```

### 4. Fazer o build do container

Faça o buid do container e suba a aplicação:

```bash
docker-compose up --build
```

### 5. Instalação das dependências do composer

Instale as dependências da aplicação:

```bash
docker-compose exec app composer install --no-interaction
```

### 6. Incia o server

Incia o server da aplicação Laravel:
```bash
docker-compose exec app php artisan serve --host=0.0.0.0 --port=8000
```

### 7. Rodar os teste automatizados

Incia os testes unitários:
```bash
docker-compose exec app php artisan test
```

## Acesso à API
A API estará disponível em http://localhost.

## Observações
- Certifique-se de que suas portas no Docker não estejam em conflito com outras aplicações.
- Configure as variáveis de ambiente no arquivo .env conforme necessário para sua aplicação.