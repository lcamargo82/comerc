<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Created</title>
    </head>
    <body>
        <h1>Pedido Criado com Sucesso!</h1>
        <p>Pedido ID: {{ $order->id }}</p>
        <p>Cliente: {{ $order->client->user->name }}</p>
        <p>Total: R$ {{ $order->product->price}}</p>
        <p>Obrigado pela sua compra!</p>
    </body>
</html>

