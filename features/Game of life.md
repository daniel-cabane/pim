# New ressources : Game of life

# Preparation
Study the ressources/js.components/Ressources folder to understand how ressources are coded in this app.
Study the 'Game of life' (aka Conway's game of life) : https://en.wikipedia.org/wiki/Conway's_Game_of_Life

# Tech
Use Vuetify components for all menus, buttons, inputs etc.
Respect the methods used for the other ressources (presentation, file names, etc.)
Don't forget the tutorial and the bilingual support

# Implementation
Create an implementation of Conway's game of life as a ressource for this app.
The game board should be as large as possible on the page and it should seem infinite.
While the game is stopped, left click on the board adds cells and right click removes them.
Underneath the game board, there should be a div with the controls :
- A button to start/stop the game.
- A slider for the time between turns
- "next step" and "previsous step" buttons to play the game step by step
- A slider for the zoom (maximum zoom = each cell is 150px by 150px, minimum zoom = each cell is 2px by 2px)
- A clear board button (with modal confirmation)

# Pattern menu (addon)
Add a menu which lists the Game of Lif most famous non stable patterns. When a pattern is picked, its shape follows the mouse on the board. When the mouse is clicked, the pattern is then fixed at this location on the board.
