<template>
    <v-container class="d-flex flex-wrap ga-4">
        <ressource-card v-for="ressource in ressources" :ressource="ressource"/>
    </v-container>
</template>
<script setup>
    const ressources = [
        { 
            title: {
                en: 'The Monty Hall Problem',
                fr: 'Le problème de Monty Hall',
            }, 
            slug: 'monty-hall',
            description: {
                en : '3 doors, 2 goats, 1 car. The famous Monty Hall game and its seemingly paradoxical conclusion. This simulation allows you to play the game. To go further, the "autoplay" function allows you to play hundreds or even thousands of game in a row while keeping track of the results. Perfect for illustrating the link between frenquency and probability',
                fr: '3 portes, 2 chèvres, 1 voiture. Le célèbre jeu de Monty Hall et sa conclusion apparemment paradoxale. Cette simulation vous permet de jouer au jeu. Pour aller plus loin, la fonction « autoplay » vous permet de jouer des centaines, voire des milliers de parties d\'affilée tout en suivant les résultats. Parfait pour illustrer le lien entre fréquence et probabilité.'
            },
            themes: [
                { en: 'Probabilities', fr: 'Probabilités' },
                { en: 'Cognitive Bias', fr: 'Biais cognitif' },
                { en: 'Simulation', fr: 'Simulation' }
            ]
        },
        {
            title: {
                fr: 'Ascenceur relatif',
                en: 'Relative elevator'
            },
            slug: 'relative-elevator',
            description: {
                en: '',
                fr: '',
            },
            themes: [
                { en: 'Relative numbers', fr: 'Nombres relatifs' },
                { en: 'Visualisation', fr: 'Visualisation' }
            ]
        }
    ];
</script>