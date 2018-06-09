Architecture
============



Key-value storage
-----------------

###Connector (general):

Can be both email ( {key}@trafficrobot.tk ) or webform/API

* `{userid}_keys = {key1}|{key2}|{key3} ; get keys of user`

* `{key1}_userid = {userid} ; get user whom key belongs to (reverse lookup)`

* `{key1}_name = {name} ; optional string comment (up to 20 chars) about that connector`



### Email connector

Can be only email ( {key}@trafficrobot.tk )

Has prefix for all storage keys:

`email`


Thus:


* `email{userid}_keys = {key1}|{key2}|{key3} ; get keys of user`

* `email{key1}_userid = {userid} ; get user whom key belongs to (reverse lookup)`
