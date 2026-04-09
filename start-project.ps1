# Project launcher for Laravel backend and Vite frontend
# Compatible with Windows PowerShell 5+ and PowerShell 7+

$ErrorActionPreference = 'Stop'

# Resolve project paths relative to this script location
$projectRoot = $PSScriptRoot
$frontendRoot = Join-Path $projectRoot 'frontend'
$envExamplePath = Join-Path $projectRoot '.env.example'
$envPath = Join-Path $projectRoot '.env'
$vendorPath = Join-Path $projectRoot 'vendor'
$frontendNodeModulesPath = Join-Path $frontendRoot 'node_modules'
$laravelPidPath = Join-Path $projectRoot '.laravel.pid'
$vitePidPath = Join-Path $projectRoot '.vite.pid'

# Validate required command-line tools and return resolved executable paths
function Resolve-RequiredCommands {
    $required = @('php', 'composer', 'node', 'npm')
    $resolved = @{}
    $missing = @()

    foreach ($name in $required) {
        $command = Get-Command $name -ErrorAction SilentlyContinue | Select-Object -First 1

        if ($null -eq $command) {
            $missing += $name
            continue
        }

        if ([string]::IsNullOrWhiteSpace($command.Path)) {
            $resolved[$name] = $name
        }
        else {
            $resolved[$name] = $command.Path
        }
    }

    if ($missing.Count -gt 0) {
        Write-Host 'ERROR: Missing required tools:' -ForegroundColor Red
        foreach ($name in $missing) {
            Write-Host (" - " + $name + " (install it and ensure it is available in PATH)") -ForegroundColor Red
        }
        exit 1
    }

    return $resolved
}

# Run an external command and stop on non-zero exit code
function Invoke-CheckedCommand {
    param(
        [Parameter(Mandatory = $true)][string]$FilePath,
        [Parameter(Mandatory = $true)][string[]]$Arguments,
        [Parameter(Mandatory = $true)][string]$WorkingDirectory,
        [Parameter(Mandatory = $true)][string]$Description
    )

    Write-Host ("-> " + $Description) -ForegroundColor Cyan

    Push-Location $WorkingDirectory
    try {
        & $FilePath @Arguments
        if ($LASTEXITCODE -ne 0) {
            throw ("Command failed: " + $FilePath + " " + ($Arguments -join ' '))
        }
    }
    finally {
        Pop-Location
    }
}

# Check if APP_KEY has a non-empty value in .env
function Test-AppKeyPresent {
    param(
        [Parameter(Mandatory = $true)][string]$EnvFilePath
    )

    if (-not (Test-Path $EnvFilePath -PathType Leaf)) {
        return $false
    }

    $line = Get-Content $EnvFilePath | Where-Object { $_ -match '^APP_KEY=' } | Select-Object -First 1
    if ($null -eq $line) {
        return $false
    }

    $value = ($line -replace '^APP_KEY=', '').Trim()
    $value = $value.Trim('"')
    $value = $value.Trim("'")

    return -not [string]::IsNullOrWhiteSpace($value)
}

# Determine whether migrations need to run
function Test-MigrationNeeded {
    param(
        [Parameter(Mandatory = $true)][string]$PhpCommand,
        [Parameter(Mandatory = $true)][string]$WorkingDirectory
    )

    Push-Location $WorkingDirectory
    try {
        $statusOutput = & $PhpCommand artisan migrate:status --no-interaction 2>&1
        $statusCode = $LASTEXITCODE
    }
    finally {
        Pop-Location
    }

    if ($statusCode -ne 0) {
        return $true
    }

    $statusText = $statusOutput | Out-String
    $hasPending = $statusText -match '\|\s+No\s+\|'
    $hasApplied = $statusText -match '\|\s+Yes\s+\|'

    if ($hasPending) {
        return $true
    }

    if (-not $hasApplied) {
        return $true
    }

    return $false
}

# Read users count from the database through Laravel
function Get-UserCount {
    param(
        [Parameter(Mandatory = $true)][string]$PhpCommand,
        [Parameter(Mandatory = $true)][string]$WorkingDirectory
    )

    Push-Location $WorkingDirectory
    try {
        $countOutput = & $PhpCommand artisan tinker --execute='echo \App\Models\User::count();' 2>&1
        $statusCode = $LASTEXITCODE
    }
    finally {
        Pop-Location
    }

    if ($statusCode -ne 0) {
        throw 'Unable to query users count from the database.'
    }

    $match = [regex]::Match(($countOutput | Out-String), '\d+')
    if (-not $match.Success) {
        throw 'Unable to parse users count from command output.'
    }

    return [int]$match.Value
}

# Stop process by PID file and remove the PID file
function Stop-ManagedProcess {
    param(
        [Parameter(Mandatory = $true)][string]$PidFilePath,
        [Parameter(Mandatory = $true)][string]$Label
    )

    if (-not (Test-Path $PidFilePath -PathType Leaf)) {
        return
    }

    $pidText = (Get-Content $PidFilePath -Raw).Trim()
    $pidValue = 0

    if (-not [int]::TryParse($pidText, [ref]$pidValue)) {
        Remove-Item $PidFilePath -Force -ErrorAction SilentlyContinue
        return
    }

    $process = Get-Process -Id $pidValue -ErrorAction SilentlyContinue
    if ($null -ne $process) {
        try {
            Stop-Process -Id $pidValue -ErrorAction Stop
        }
        catch {
            taskkill /PID $pidValue /T /F *> $null
        }
    }

    Remove-Item $PidFilePath -Force -ErrorAction SilentlyContinue
}

# Start a process in the background and persist its PID
function Start-ManagedProcess {
    param(
        [Parameter(Mandatory = $true)][string]$FilePath,
        [Parameter(Mandatory = $true)][string[]]$Arguments,
        [Parameter(Mandatory = $true)][string]$WorkingDirectory,
        [Parameter(Mandatory = $true)][string]$PidFilePath,
        [Parameter(Mandatory = $true)][string]$Label
    )

    $process = Start-Process -FilePath $FilePath -ArgumentList $Arguments -WorkingDirectory $WorkingDirectory -PassThru
    Set-Content -Path $PidFilePath -Value $process.Id -Encoding ASCII
    return $process
}

$commands = Resolve-RequiredCommands
$phpCommand = $commands['php']
$composerCommand = $commands['composer']
$npmCommand = $commands['npm']

# Backend setup section
Write-Host '=== Backend setup ===' -ForegroundColor Yellow

if (-not (Test-Path $envPath -PathType Leaf)) {
    if (-not (Test-Path $envExamplePath -PathType Leaf)) {
        throw '.env.example is missing. Cannot create .env.'
    }

    Copy-Item -Path $envExamplePath -Destination $envPath
    Write-Host 'Created .env from .env.example' -ForegroundColor Green
}
else {
    Write-Host '.env already exists, skipping copy.' -ForegroundColor DarkGray
}

if (-not (Test-Path $vendorPath -PathType Container)) {
    Invoke-CheckedCommand -FilePath $composerCommand -Arguments @('install', '--no-interaction') -WorkingDirectory $projectRoot -Description 'Installing backend dependencies with Composer'
}
else {
    Write-Host 'vendor directory exists, skipping composer install.' -ForegroundColor DarkGray
}

if (-not (Test-AppKeyPresent -EnvFilePath $envPath)) {
    Invoke-CheckedCommand -FilePath $phpCommand -Arguments @('artisan', 'key:generate') -WorkingDirectory $projectRoot -Description 'Generating APP_KEY'
}
else {
    Write-Host 'APP_KEY already set, skipping key generation.' -ForegroundColor DarkGray
}

if (Test-MigrationNeeded -PhpCommand $phpCommand -WorkingDirectory $projectRoot) {
    Invoke-CheckedCommand -FilePath $phpCommand -Arguments @('artisan', 'migrate', '--force') -WorkingDirectory $projectRoot -Description 'Running database migrations'
}
else {
    Write-Host 'Database migrations are already up to date, skipping migrate.' -ForegroundColor DarkGray
}

$userCount = Get-UserCount -PhpCommand $phpCommand -WorkingDirectory $projectRoot
if ($userCount -eq 0) {
    Invoke-CheckedCommand -FilePath $phpCommand -Arguments @('artisan', 'db:seed', '--force') -WorkingDirectory $projectRoot -Description 'Seeding database'
}
else {
    Write-Host ('Users table contains ' + $userCount + ' row(s), skipping seed.') -ForegroundColor DarkGray
}

# Frontend setup section
Write-Host '=== Frontend setup ===' -ForegroundColor Yellow

if (-not (Test-Path $frontendRoot -PathType Container)) {
    throw 'frontend directory is missing.'
}

if (-not (Test-Path $frontendNodeModulesPath -PathType Container)) {
    Invoke-CheckedCommand -FilePath $npmCommand -Arguments @('install') -WorkingDirectory $frontendRoot -Description 'Installing frontend dependencies with npm'
}
else {
    Write-Host 'frontend/node_modules exists, skipping npm install.' -ForegroundColor DarkGray
}

# Ensure stale PID files do not block a fresh launch
Stop-ManagedProcess -PidFilePath $laravelPidPath -Label 'Laravel backend'
Stop-ManagedProcess -PidFilePath $vitePidPath -Label 'Vite frontend'

# Start backend and frontend in background
Write-Host '=== Starting servers ===' -ForegroundColor Yellow
Start-ManagedProcess -FilePath $phpCommand -Arguments @('artisan', 'serve', '--port=8000') -WorkingDirectory $projectRoot -PidFilePath $laravelPidPath -Label 'Laravel backend' | Out-Null
Start-ManagedProcess -FilePath $npmCommand -Arguments @('run', 'dev') -WorkingDirectory $frontendRoot -PidFilePath $vitePidPath -Label 'Vite frontend' | Out-Null

# Print startup summary and test credentials
Write-Host ''
Write-Host '✅ Backend running at  http://127.0.0.1:8000' -ForegroundColor Green
Write-Host '✅ Frontend running at http://localhost:5173' -ForegroundColor Green
Write-Host ''
Write-Host 'Test accounts:' -ForegroundColor Cyan
Write-Host 'admin@school.com   / password'
Write-Host 'teacher@school.com / password'
Write-Host 'student@school.com / password'
Write-Host 'parent@school.com  / password'
Write-Host ''

try {
    Read-Host 'Press Enter to stop both servers'
}
finally {
    Stop-ManagedProcess -PidFilePath $laravelPidPath -Label 'Laravel backend'
    Stop-ManagedProcess -PidFilePath $vitePidPath -Label 'Vite frontend'
    Write-Host 'Servers stopped.' -ForegroundColor Yellow
}
