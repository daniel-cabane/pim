<x-mail::message>
# {{ $this->options->language == 'fr' ? $this->options->title_fr : $this->options->title_en }}

{{ $this->options->language == 'fr' ? $this->options->description_fr : $this->options->description_en }}

<x-mail::button :url="''">
# {{ $this->options->language == 'fr' ? 'Répondre au questionnaire' : 'Take survey' }}
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
