<table>
    <thead>
        <tr>
            <th>
                <p> Ticket #{{ $ticketId }} ({{ $ticketSubject }}) </p>
                <p> Submitted: {{ $createdAt }} </p>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><td></td></tr>
        @foreach($ticketMessage as $message)
            <tr>
                <td> ({{ $message->created_at }}) {{ $message->user->name }}: {{ $message->message }} </td>
            </tr>
        @endforeach
    </tbody>
</table>
