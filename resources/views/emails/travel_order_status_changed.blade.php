<h1>Travel Request {{ $status }}</h1>

<p>Your travel request to <strong>{{ $travelRequest->destination }}</strong> has been <strong>{{ $status }}</strong>.
</p>

<p><strong>Departure:</strong> {{ $travelRequest->departure_date->format('Y-m-d') }}<br>
    <strong>Return:</strong> {{ $travelRequest->return_date->format('Y-m-d') }}</p>

<p>Thanks,<br>
    {{ config('app.name') }}</p>
