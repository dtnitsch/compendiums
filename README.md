- Lists
	- Single
	- Probability (must add up to 100 - default is 0, aka: all equal)
	- Always a textbox, newline for new items, comma for probability (last comma for probability, numeric only)
	- Generate a "key" for referencing list
	- Combine / Build off of existing list?
		- Keep reference to other people?
		- Build "static" list (use others, but take snapshot)
		- Build "Dynamic" list (if others change, yours does as well)
- Compendiums (list of list)
	- Cannot custom build list from here, must use list key
	- Add key, ajax to get title
	- show "number of rolls" (2 - 6 items from list)
	- Reorder?
- Users
	- "My lists"
	- "My Favorites"
	- Fun stats
		- reports for how many others are using your lists (compendiums)
		- Report for last time lists were looked up
		- Last time list was used (datetimes)


Big Question:
	- Chain Lists
		- Max levels deep?
		- UI?


API - make public?



DB
--
- Assets: title, alias (unique list of all items), datetime
- Lists: Name of list, alias, user id, datetime, description 
- Asset List Map: asset id, list id, datetime
- Compendiums: title, alias, desc, user, datetime
- Compendium List Map: list_id, list description (custom to here - label?), number_to_display (show 2 - 6 items from list)

# gm_tools
