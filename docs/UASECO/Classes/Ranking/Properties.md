# Class Ranking
###### Documentation of includes/core/ranking.class.php

Structure of ranking.



## [Properties](_#Properties)


| Members								| Example data or description
|-----------------------------------------------------------------------|----------------------------
| `$ranking->rank`							| Current Rank
| `$ranking->pid`							| [Player Id](/Development/Classes/Player.php) at the dedicated Server
| `$ranking->login`							| [Players Login](/Development/Classes/Player.php)
| `$ranking->nickname`							| [Players Nickname](/Development/Classes/Player.php)
| `$ranking->time`							| Players best time
| `$ranking->score`							| Players current score
| `$ranking->cps`							| Array of Checkpoint times from the best time
| `$ranking->team`							| TeamId of that Team the Player is member from, -1 = no Team
| `$ranking->spectator`							| Boolean indicator
| `$ranking->away`							| Boolean indicator
