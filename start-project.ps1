# Simple project starter (Windows PowerShell 5+ and PowerShell 7+)
# Usage:
#   .\start-project.ps1
#   .\start-project.ps1 -FixCacheTables

param(
    [switch]$FixCacheTables
)

$ErrorActionPreference = 'Stop'

$projectRoot = $PSScriptRoot
$frontendRoot = Join-Path $projectRoot 'frontend'
$xamppMySqlExe = 'C:\xampp\mysql\bin\mysqld.exe'

function Assert-Command {
    param(
        [Parameter(Mandatory = $true)][string]$Name
    )

    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw ("Required command not found in PATH: " + $Name)
    }
}

function Invoke-Checked {
    param(
        [Parameter(Mandatory = $true)][string]$Exe,
        [Parameter(Mandatory = $true)][string[]]$Args,
        [Parameter(Mandatory = $true)][string]$Description
    )

    Write-Host ("-> " + $Description) -ForegroundColor Cyan
    & $Exe @Args
    if ($LASTEXITCODE -ne 0) {
        throw ("Command failed: " + $Exe + " " + ($Args -join ' '))
    }
}

function Get-UsersCount {
    Push-Location $projectRoot
    try {
        $output = & php artisan tinker --execute="echo app('db')->table('users')->count();" 2>&1
        $statusCode = $LASTEXITCODE
    }
    finally {
        Pop-Location
    }

    if ($statusCode -ne 0) {
        throw ('Unable to read users count. ' + (($output | Out-String).Trim()))
    }

    $match = [regex]::Match(($output | Out-String), '\d+')
    if (-not $match.Success) {
        throw 'Unable to parse users count from artisan output.'
    }

    return [int]$match.Value
}

function Start-XamppMySql {
    if (-not (Test-Path $xamppMySqlExe -PathType Leaf)) {
        Write-Host "WARNING: XAMPP MySQL executable not found at C:\xampp\mysql\bin\mysqld.exe" -ForegroundColor Yellow
        Write-Host "Start MySQL manually from XAMPP Control Panel if needed." -ForegroundColor Yellow
        return
    }

    $existing = Get-Process -Name mysqld -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($null -ne $existing) {
        Write-Host "MySQL already running (mysqld)." -ForegroundColor DarkGray
        return
    }

    Write-Host "-> Starting MySQL (XAMPP)..." -ForegroundColor Cyan
    Start-Process -FilePath $xamppMySqlExe -ArgumentList @('--standalone') -WorkingDirectory (Split-Path $xamppMySqlExe -Parent) | Out-Null
}

function Get-TerminalHostExe {
    $pwsh = Get-Command pwsh -ErrorAction SilentlyContinue
    if ($null -ne $pwsh) {
        return $pwsh.Source
    }

    $powershell = Get-Command powershell -ErrorAction SilentlyContinue
    if ($null -ne $powershell) {
        return $powershell.Source
    }

    throw 'No PowerShell executable found to open new terminals.'
}

function Start-TerminalCommand {
    param(
        [Parameter(Mandatory = $true)][string]$Command
    )

    $shellExe = Get-TerminalHostExe
    Start-Process -FilePath $shellExe -ArgumentList @('-NoExit', '-Command', $Command) | Out-Null
}

try {
    Assert-Command -Name php
    Assert-Command -Name npm

    Write-Host "=== Step 1: Start MySQL (XAMPP) ===" -ForegroundColor Yellow
    Start-XamppMySql

    Write-Host "=== Step 2: Backend Laravel (migrate + seed) ===" -ForegroundColor Yellow
    Push-Location $projectRoot
    try {
        Invoke-Checked -Exe 'php' -Args @('artisan', 'migrate', '--force') -Description 'Running migrations'

        $usersCount = Get-UsersCount
        if ($usersCount -eq 0) {
            Invoke-Checked -Exe 'php' -Args @('artisan', 'db:seed', '--force') -Description 'Running seeders'
        }
        else {
            Write-Host ("Users already present (" + $usersCount + "), skipping db:seed.") -ForegroundColor DarkGray
        }

        if ($FixCacheTables) {
            Write-Host "=== Step 4: Fix cache/session tables ===" -ForegroundColor Yellow
            Invoke-Checked -Exe 'php' -Args @('artisan', 'cache:table') -Description 'Generating cache table migration'
            Invoke-Checked -Exe 'php' -Args @('artisan', 'session:table') -Description 'Generating sessions table migration'
            Invoke-Checked -Exe 'php' -Args @('artisan', 'migrate', '--force') -Description 'Applying new cache/session migrations'
        }
    }
    finally {
        Pop-Location
    }

    Write-Host "=== Step 2b: Start backend server in a new terminal ===" -ForegroundColor Yellow
    $backendCmd = "Set-Location '$projectRoot'; php artisan serve --port=8000"
    Start-TerminalCommand -Command $backendCmd

    Write-Host "=== Step 3: Start frontend in a new terminal ===" -ForegroundColor Yellow
    $frontendCmd = "Set-Location '$frontendRoot'; npm install; npm run dev"
    Start-TerminalCommand -Command $frontendCmd

    Write-Host ""
    Write-Host "Project startup commands launched." -ForegroundColor Green
    Write-Host "Backend URL : http://127.0.0.1:8000" -ForegroundColor Green
    Write-Host "Frontend URL: http://localhost:5173" -ForegroundColor Green
    Write-Host ""
    Write-Host "If you still get cache table SQL errors, run:" -ForegroundColor Yellow
    Write-Host "  .\start-project.ps1 -FixCacheTables" -ForegroundColor Yellow
}
catch {
    Write-Host ("ERROR: " + $_.Exception.Message) -ForegroundColor Red
    exit 1
}
