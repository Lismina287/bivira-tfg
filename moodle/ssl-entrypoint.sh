#!/bin/bash
a2ensite bivira-ssl.conf
service apache2 reload
exec "$@"
