<template>
    <div>
        Youpi
        {{ t('hello') }}
        {{ t('bye') }}

    </div>
</template>
<script setup>
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n({
    inheritLocale: true,
    useScope: 'local'
})

</script>

<i18n>
{
  "en": {
    "language": "Language",
    "hello": "hello, world!"
  },
  "fr": {
    "language": "Lg",
    "hello": "Bonjour"
  }
}
</i18n>

