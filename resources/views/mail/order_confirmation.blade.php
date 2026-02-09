@use(App\Enum\CountryCode)

<p>Congratulation, You have WON!!!</p>
<p>You won your entry <strong>{{ $order->raffleEntry->entry_code }}</strong> for <strong>{{ $order->product->name }}</strong> raffle.</p>
<br/>
<p>Order Summary:</p>
<ul>
    <li>Order Code: {{ $order->order_code }}</li>
    <li>Product: {{ $order->product->name }}</li>
    <li>Total: {{ number_format($order->amount, 2, '.', ',') }}</li>
</ul>
<br/>
<p>Delivery Address:</p>
<ul>
    <li>Full Name: {{ $order->user->name }}</li>
    <li>Street: {{ $order->address->street }}</li>
    <li>City: {{ $order->address->city }}</li>
    <li>Country: {{ CountryCode::fullName($order->address->country_code) }}</li>
</ul>