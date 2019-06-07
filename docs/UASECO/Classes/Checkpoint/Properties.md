# Class Checkpoint
###### Documentation of includes/core/checkpoint.class.php


***


Stores checkpoint information for Players.



## [Properties](_#Properties)


| Members								| Description
|-----------------------------------------------------------------------|------------
| `$checkpoint->tracking['local_records']`				| <code>-1 = off<br>0 = own/last rec<br>1-max = rec #1-max</code>
| `$checkpoint->tracking['dedimania_records']`				| <code>-1 = off<br>0 = own/last rec<br>1-30 = rec #1-30</code>
| `$checkpoint->spectators`						| array
| `$checkpoint->best['timestamp']`					| integer when the best time was made
| `$checkpoint->best['finish']`						| If it is `PHP_INT_MAX`, then the Player did not finish this Map yet
| `$checkpoint->best['cps']`						| array of Checkpoint times
| `$checkpoint->current['finish']`					| If it is `PHP_INT_MAX`, then the Player did not finish this Map yet
| `$checkpoint->current['cps']`						| array of Checkpoint times
