<?php
$date = date('Y-m-d_H-i-s');
$backupFile = __DIR__ . "/storage/backups/galak_backup_$date.sql";
$command = "\"C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe\" -u root database_galak > \"$backupFile\"";
exec($command);
echo "Backup selesai: $backupFile";
?>