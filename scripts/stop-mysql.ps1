# Stops the project's standalone MySQL 8.4 instance.

$mysqladmin = "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqladmin.exe"
& $mysqladmin -u root -pPadelDev_2026! --port=3307 --protocol=tcp shutdown
Write-Output "Shutdown requested."
