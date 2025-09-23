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
                en: 'This app uses an elevator powered by black and white tokens to help students understand the concept of adding and subtracting relative numbers.<br/>Various challenges are available to put this understanding to practice.',
                fr: 'Cette application utilise un ascenseur alimenté par des jetons noirs et blancs pour aider les élèves à comprendre le concept de l\'addition et de la soustraction de nombres relatifs.<br/>Divers défis sont disponibles pour mettre cette compréhension en pratique.',
            },
            themes: [
                { en: 'Relative numbers', fr: 'Nombres relatifs' },
                { en: 'Visualisation', fr: 'Visualisation' }
            ]
        },
        { 
            title: {
                en: 'The biased dice',
                fr: 'Les dés truqués',
            }, 
            slug: 'biased-dice',
            description: {
                en: 'Throw dice a large number of time and try to find out if they are balanced or biased. With this application, you can illustrate the law of large numbers and discuss mathematical tools such as confidence intervals (eg Bienaymé–Chebyshev inequality)',
                fr: "Lancez des dés un grand nombre de fois et essayez de déterminer s'ils sont équilibrés ou biaisés. Avec cette application, vous pouvez illustrer la loi des grands nombres et discuter des outils mathématiques tels que les intervalles de confiance (par exemple, l'inégalité de Bienaymé–Tchebychev).",
            },
            themes: [
                { en: 'Probabilities', fr: 'Probabilités' },
                { en: 'Confidence interval', fr: 'Intervalle de confiance' },
                { en: 'Simulation', fr: 'Simulation' }
            ]
        },
    ];
</script>