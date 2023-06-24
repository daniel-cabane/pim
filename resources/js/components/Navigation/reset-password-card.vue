<template>
    <v-card width="350">
        <v-card-title>Reset password</v-card-title>
        <v-card-text>
            <v-text-field
            class="mt-2"
            :rules="[rules.required, rules.minLength]"
            type="password"
            v-model="form.password"
            label="Password" 
            variant="outlined"
            validate-on="blur"
            />
            <v-text-field
                class="mt-2"
                :rules="[rules.matchPassword]"
                type="password"
                v-model="form.password_confirmation"
                label="Confirm password" 
                variant="outlined"
                validate-on="blur"
            />
        
            <div class="mt-3" style="display:flex">
                <v-spacer/>
                <v-btn color="primary" width="150" @click="submit">Sumbit</v-btn>
            </div>
        </v-card-text>
    </v-card>
</template>
<script setup>
    import { reactive, defineProps } from 'vue';

    const props = defineProps({token: String, email: String});
    console.log(props.token, props.email);

    let form = reactive({ email: props.email, token: props.token, password: '', password_confirmation: '' });

    let rules = {
        required: value => !!value || 'Required.',
        minLength: value => value.length >= 8 || 'The password must at least 8 characters long',
        matchPassword: value => value == form.password || 'Does not match the password'
    };

    const submit = async () => {
        let res = await axios.post('/reset-password', form);
        console.log(res.data);
        if(res.status == 200){
            console.log('password updated');
        } else {
            console.log('error');
        }
    }
</script>