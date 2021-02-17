@component('mail::message')


{{ $mail['body'] }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
