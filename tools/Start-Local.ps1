param([int]$Port = 8000)
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location -LiteralPath $projectRoot
if (-not (Test-Path -LiteralPath '.env')) { throw 'Configure .env before starting. See README.md.' }
$envText = Get-Content -LiteralPath '.env' -Raw
$databaseHost = [regex]::Match($envText, '(?m)^DB_HOST=(.*)$').Groups[1].Value.Trim()
$databasePort = [regex]::Match($envText, '(?m)^DB_PORT=(.*)$').Groups[1].Value.Trim()
$connection = New-Object System.Net.Sockets.TcpClient
try { $connection.Connect($databaseHost, [int]$databasePort) } catch { throw 'MySQL is not running. Start MySQL from the XAMPP Control Panel first.' } finally { $connection.Dispose() }
Write-Host "PropertyME: http://127.0.0.1:$Port"
Write-Host "Admin:      http://127.0.0.1:$Port/admin/login"
php artisan serve --host=127.0.0.1 --port=$Port
