<x-mail::message>
# {{ $email->language == 'fr' ? $email->subject_fr : $email->subject_en }}

{!! $email->language == 'fr' ? $email->data->body_fr : $email->data->body_en !!}

@if(isset($email->data->url))
<x-mail::button :url="url($email->data->url)">
{{ $email->language == 'fr' ? $email->data->buttonText_fr : $email->data->buttonText_en }}
</x-mail::button>
@endif

@if(isset($email->data->students))
| Nom | Classe |
| :--- | :---: |
@foreach ($email->data->students as $student)
| {{ $student->name }} | {{ $student->className }} |
@endforeach
@endif

<br>
{!! $email->language == 'fr' ? $email->data->closing_fr : $email->data->closing_en !!},<br>
{{ config('app.name') }}
<br>

@if(isset($email->data->ps_fr) || isset($email->data->ps_en))
<b>PS : </b>{!! $email->language == 'fr' ? $email->data->ps_fr : $email->data->ps_en !!}
@endif
</x-mail::message>
