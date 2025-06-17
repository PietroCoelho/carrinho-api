# Carrinho de Compras

Este é um projeto de sistema de carrinho de compras em PHP que oferece funcionalidades de cálculo de pagamento com diferentes métodos de pagamento.

## Requisitos

- PHP 8.0 ou superior
- Composer (Gerenciador de dependências PHP)
- Docker e Docker Compose (opcional)

## Instalação

### Usando Docker (Recomendado)

1. Clone o repositório:

2. Inicie os containers:
```bash
docker compose up -d
```

3. Acesse a aplicação em `http://localhost:8000`

### Instalação Manual

1. Clone o repositório:

2. Instale as dependências usando o Composer:
```bash
composer install
```

3. Inicie o servidor PHP:
```bash
php -S localhost:8000 -t public
```

## Estrutura do Projeto

O projeto está organizado da seguinte forma:

- `app/Services/CartService.php`: Serviço principal que gerencia o carrinho de compras
- `app/Enums/PaymentMethod.php`: Enumeração dos métodos de pagamento disponíveis
- `Dockerfile`: Configuração do container Docker
- `docker-compose.yml`: Configuração dos serviços Docker

## Funcionalidades

O sistema oferece as seguintes funcionalidades:

- Cálculo de subtotal dos itens
- Aplicação de desconto para pagamentos à vista (PIX ou cartão de crédito em 1x)
- Cálculo de juros para parcelamento em cartão de crédito

## Exemplo de Uso

```php
use App\Services\CartService;
use App\Enums\PaymentMethod;

$cartService = new CartService();

$items = [
    [
        'name' => 'Produto 1',
        'unitPrice' => 100.00,
        'quantity' => 2
    ]
];

$result = $cartService->calculatePayment(
    items: $items,
    paymentMethod: PaymentMethod::PIX
);
```

## Métodos de Pagamento Suportados

- PIX (pagamento à vista com 10% de desconto)
- Cartão de Crédito
  - À vista (1x com 10% de desconto)
  - Parcelado (até 12x com juros)

## Validações

O sistema realiza as seguintes validações:

- Dados dos itens (nome, preço e quantidade)
- Dados do cartão de crédito (número, data de validade e CVV)
- Número de parcelas (máximo de 12x)

## Comandos Docker Úteis

- Iniciar os containers: `docker-compose up -d`
- Parar os containers: `docker-compose down`
- Ver logs: `docker-compose logs -f`
- Executar composer: `docker-compose exec carrinho-app-1 composer install`
- Acessar o shell do container: `docker exec -it carrinho-app-1 bash`

## Comandos Para Executar os testes

- Para executar todos os testes: `composer test-all`
Obs:Se estiver utilizando docker, entre no container para executar o comando