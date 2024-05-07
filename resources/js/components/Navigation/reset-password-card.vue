<template>
    <v-card width="350">
        <v-card-title>{{ $t('Reset password') }}</v-card-title>
        <v-card-text>
            <v-text-field
            class="mt-2"
            :rules="[rules.required, rules.minLength]"
            type="password"
            v-model="form.password"
            :label="$t('Password')" 
            variant="outlined"
            validate-on="blur"
            />
            <v-text-field
                class="mt-2"
                :rules="[rules.matchPassword]"
                type="password"
                v-model="form.password_confirmation"
                :label="$t('Confirm password')" 
                variant="outlined"
                validate-on="blur"
            />
        
            <div class="mt-3" style="display:flex">
                <v-spacer/>
                <v-btn color="primary" width="150" @click="submit">{{ $t('Sumbit') }}</v-btn>
            </div>
        </v-card-text>
    </v-card>
</template>
<script setup>
    const props = defineProps({token: String, email: String});
    import { useI18n } from 'vue-i18n';
    const { t } = useI18n();

    let form = reactive({ email: props.email, token: props.token, password: '', password_confirmation: '' });

    let rules = {
        required: value => !!value || t('Required'),
        minLength: value => value.length >= 8 || t('The password must at least 8 characters long'),
        matchPassword: value => value == form.password || t('Does not match the password')
    };

    const submit = async () => {
        let res = await axios.post('/reset-password', form);
        if(res.status == 200){
            addAlert({text: t('password updated'), type: 'success'});
        } else {
            addAlert({ text: t('We encountered an error, please try again'), type: 'error' });
        }
    }
</script>