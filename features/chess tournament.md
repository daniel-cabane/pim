# TODO: Chess Tournament Feature Implementation

## General Idea
- Add a feature where users with admin role can organize chess tournaments. 
- The organiser can nominate other users as co-organisers. Co-organisers can terminate rounds.
- Teachers and students can apply to join the tournament. The organiser and co-organisers can also add and remove participants.
- The organizer should have the choice between all the usual rules to pair opponents (Swiss, knockout, etc.).
- Implement a strong and flawless logic for Swiss pairing, including the bye technique.
- The organizer can launch the tournament. Upon launch, the app closes the applications and it sets up the first round by pairing players according to the rules set. 
- Each player has to input the result of the match. 
- Everybody can see all matches and all results for all rounds. 
- Each match stays 'open' until both opponents agree on the result (1-0, 0-1 or 1/2-1/2). Once opponents agreed, the match 'closes' and is no longer editable by players.
- The organiser and co-organisers can edit all matches results. They can also 'close' or 're-open' a match.

- Once all matches of a round are closed, the organiser and co-organisers can terminate the round. The app automatically sets up the next round by pairing players etc.

## Tech stack
Use this application tech stack : 
- Use Laravel backend, all calls to the backend should go through the routes/api.php file
- Create models and controllers where relevant
- Create tests where relevant
- Use Vue 3 (with script setup)
- Use Vue router for navigating
- Use Pinia for state management, create new store where relevant
- Use Vuetify for all UI elements
- This is a SPA (do not create new laravel views, only use vue router views)
- Any call to the backend has to be done through the api route file
- Translations in English and French on the frontend with vue i18n using the en.json and fr.json files

## Specifics
- The "New Tournament" button should be in the admin's action menu
- UI should be consistent with the rest of the app and not too heavy
- Once launched, tournament url should be viewable by everyone (including guests)
- Tournament page should feature the tournament leaderboard, the matches for the current round, and a graph to show top players progress across the whole tournament
- If the user is logged in and is a player, the tournament page should display his current match in a highlight/overlay spot. It should also display a highlight of his statistics.
- Tournament history (all matches and their results, player statistics, evolution graphs...) should be available for everyone to see.

