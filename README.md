# ProperDuels
ProperDuels is a plugin that adds a duel system into your server. No need to worry about any glitches unlike other duel plugins!
 
## Features
- Customizable kits
- Customizable messages
- Multi arena support
- Simple commands
- SQL Database
- Queue system

## Commands

### /arena
Manage arenas for duel matches.
- /arena create <arena: string> <firstSpawnPos: x y z> <secondSpawnPos: x y z> [kit: string]
- /arena delete <arena: string>
- /arena list

### /duel
Duel players and queue to a match.
- /duel <player: string> [arena: string]
- /duel accept <player: string>
- /duel deny <player: strint>
- /duel queue [arena: string]

### /kit
Manage kits for duel matches.
- /kit create <kit: string>
- /kit delete <kit: string>
- /kit list
