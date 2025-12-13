<?php
// scripts/backup_database.php
$backupFile = 'backup-' . date('Y-m-d-His') . '.sql';
$command = "mysqldump --user=root --password= --host=localhost arewa_conservation_db > storage/app/backups/{$backupFile}";
system($command);
echo "Backup created: {$backupFile}";