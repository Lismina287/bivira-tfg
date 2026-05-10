#!/bin/bash
FECHA=$(date +%Y%m%d_%H%M%S)
DESTINO="/srv/bivira/backups"
mkdir -p $DESTINO

docker exec moodle_db mysqldump -u azubivira -pbivira moodle > $DESTINO/bivira_backup_$FECHA.sql

# Mantener solo los últimos 7 backups
ls -t $DESTINO/bivira_backup_*.sql | tail -n +8 | xargs -r rm

echo "Backup completado: bivira_backup_$FECHA.sql"
