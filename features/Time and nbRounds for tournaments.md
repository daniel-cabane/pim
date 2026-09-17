# Update for tournaments

Add a 'preferences' column json column to the tournaments.
Update the Tournament model (fillable, ...)
This column defaults to { time: { title: '5min', subtitle: 'Blitz' }, nbRounds: 6 }

In the tournament edit form, under Settings, add two text fields to edit time.title and time.subtitle (max 25 char for each) and a number field to edit nbRounds. Also update the tournament update pipeline to save these changes to db.

In the tournament page, replace the top cards :
- 'Rounds' number replaced nbRounds
- 'Players' replaced by the time title and subtitle

Same changes for the tournament home card.