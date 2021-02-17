@component('mail::message')
# Introduction

The body of your message.

{{ $mail['body'] }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
