# code.babab.nl

A simple webapplication for fetching and displaying my git projects
hosted on Github and Bitbucket.

The project data is gathered by requesting from both Github's and
Bitbucket's API, combining the projects with matching names to single
project containers.

See it in action at: http://code.babab.nl/

## Stuff used

Back:

- PHP 5
- cURL
- Github API v3.0
- Bitbucket API v2.0

Front:

- Twitter Bootstrap 3
- Font Awesome
- Open Sans font

## Run in docker

    docker build -t code.babab.nl .
    docker run -d -p 8000:80 --name code.babab.nl code.babab.nl:latest

    docker stop code.babab.nl
    docker start code.babab.nl


## License

Copyright (c) 2014 Benjamin Althues <benjamin@babab.nl>

Permission to use, copy, modify, and distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
