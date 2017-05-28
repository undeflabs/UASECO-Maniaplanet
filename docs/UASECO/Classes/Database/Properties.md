# Class Database
###### Documentation of includes/core/database.class.php

This Class extends the [Class mysqli](http://php.net/manual/en/class.mysqli.php), so all the Properties and Methods this Class provides are available too.



## [Properties](_#Properties)


| Members								| Description
|-----------------------------------------------------------------------|------------
| `$aseco->db->debug`							| Holds the boolean debug status from connection
| `$aseco->db->settings`						| Holds what the database has returned with the query `SHOW VARIABLES`
| `$aseco->db->table_prefix`						| Holds the default table prefix `uaseco_`
| `$aseco->db->type`							| Holds the current database type, possible values are MySQL or MariaDB
| `$aseco->db->version`							| Holds the current database version (short), e.g. `5.7.16`
| `$aseco->db->version_full`						| Holds the current database version (full), e.g. `10.0.27-MariaDB-0ubuntu0.16.04.1`
