<x-mail::message>
# New Task Created

Hello, {{ $userName }}!

A new task has been created for you:

**Title:** {{ $taskTitle }}

**Description:**
{{ $taskDescription }}

Thank you,
{{ config('app.name') }}
</x-mail::message>
