<table class="table">
    <thead>
        <tr>
            <th scope="col">Players</th>
            <th scope="col">Amount</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($bids as $bid)
        <tr>
            <td>{{ $bid->name }}</td>
            <td>${{ $bid->amount }}</td>
        </tr>
    @endforeach
    </tbody>
</table>