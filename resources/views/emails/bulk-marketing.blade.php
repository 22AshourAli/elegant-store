<x-mail::message>
# مرحباً {{ $user->name }}

{{ $messageText }}

<x-mail::button :url="route('home')">
تسوق الآن
</x-mail::button>

شكراً لتسوقك معنا،<br>
{{ config('app.name') }}
</x-mail::message>
