# Starts the project's standalone MySQL 8.4 instance (not a Windows service).
# No admin rights required. Run this before working on the backend.
# DB: padel_booking | user: padel / PadelApp_2026! | root: PadelDev_2026! | port: 3307

$mysqld = "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqld.exe"
$dataDir = "C:\Users\Acer\AppData\Local\padel-mysql\data"
$logFile = "C:\Users\Acer\AppData\Local\padel-mysql\mysqld.log"
$pidFile = "C:\Users\Acer\AppData\Local\padel-mysql\mysqld.pid"

if (Get-NetTCPConnection -LocalPort 3307 -ErrorAction SilentlyContinue) {
    Write-Output "MySQL already running on port 3307."
} else {
    Start-Process -FilePath $mysqld -ArgumentList `
        "--datadir=`"$dataDir`"", "--port=3307", "--socket=padel_mysql", `
        "--log-error=`"$logFile`"", "--pid-file=`"$pidFile`""
    Write-Output "Starting MySQL on port 3307... check with: Get-NetTCPConnection -LocalPort 3307"
}
