# Wikimedia CH Cronos Calendar web form

This is the Wikimedia CH Cronos Calendar web form. This project implements this web form:

https://wmch.toolforge.org/cronos/

Read more information about Wikimedia CH Cronos Calendar:

https://meta.wikimedia.org/wiki/Meta:Cronos

## Features

* simple user interface
* quick OAuth login with Meta-wiki

## Installation

Clone these repositories:

```
# clone this repository
git clone https://phabricator.wikimedia.org/diffusion/WCCF/wikimedia-ch-wmch-cronos-form.git

# clone the OAuth repository
git clone ssh://gerrit.wikimedia.org:29418/mediawiki/oauthclient-php

# clone the website framework
git clone https://gitpull.it/source/boz-mw.git
```

Then create your OAuth consumer:

https://meta.wikimedia.org/wiki/Special:OAuthConsumerRegistration/propose

Enter in the `wikimedia-ch-wmch-cronos-form` directory and copy the file `load-example.php` as `load.php` and fill your OAuth tokens.

## License

Copyright (C) 2020 Valerio Bozzolan

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
