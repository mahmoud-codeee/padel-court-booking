# Frees port 5173 (the frontend dev server's port) if something is still
# bound to it from a previous run. Targets ONLY the specific process ID
# actually listening on that exact port - never a broad "kill all node" sweep.

$conn = Get-NetTCPConnection -LocalPort 5173 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1

if ($conn) {
    $pid_ = $conn.OwningProcess
    $proc = Get-Process -Id $pid_ -ErrorAction SilentlyContinue
    $name = if ($proc) { $proc.ProcessName } else { "unknown" }
    Write-Output "Port 5173 is in use by PID $pid_ ($name) - stopping it."
    Stop-Process -Id $pid_ -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 1
} else {
    Write-Output "Port 5173 is free."
}
