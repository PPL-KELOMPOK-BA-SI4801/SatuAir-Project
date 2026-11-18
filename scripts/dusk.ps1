param(
    [string]$Filter = '',
    [string]$Group = '',
    [ValidateRange(0, 10000)][int]$Delay = 650,
    [ValidateRange(0, 1000)][int]$TypeDelay = 0,
    [ValidateRange(1024, 65535)][int]$Port = 8002,
    [switch]$Headless
)
$ErrorActionPreference = 'Stop'
$taskRoot = Split-Path -Parent $PSScriptRoot
$phpBinary = (Get-Command php -ErrorAction Stop).Source
$testArtifacts = Join-Path $taskRoot 'tests/Browser/artifacts'
$testRuntime = Join-Path $taskRoot 'storage/framework/testing/dusk'
New-Item -ItemType Directory -Force $testArtifacts, $testRuntime, (Join-Path $testRuntime 'sessions') | Out-Null
$runLock = [System.IO.File]::Open((Join-Path $testRuntime 'run.lock'), 'OpenOrCreate', 'ReadWrite', 'None')
$serverProcess = $null
$savedVariables = @{}
$runStamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$exitCode = 1
Push-Location $taskRoot
try {
    # Process-scoped environment only: .env and the application on port 8000 are untouched.
    if ((Test-Path '.env.dusk') -or (Test-Path '.env.dusk.dusk')) {
        throw 'Runner tidak memakai penukaran .env. Pindahkan konfigurasi .env.dusk yang ada sebelum menjalankan runner ini.'
    }
    $listener = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Loopback, $Port)
    try { $listener.Start() } finally { $listener.Stop() }
    $keyBytes = New-Object byte[] 32
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    try { $rng.GetBytes($keyBytes) } finally { $rng.Dispose() }
    $variables = @{
        APP_ENV='dusk'; APP_DEBUG='false'; APP_KEY=('base64:'+[Convert]::ToBase64String($keyBytes))
        APP_URL="http://127.0.0.1:$Port"; APP_CONFIG_CACHE='bootstrap/cache/config.dusk.php'
        APP_ROUTES_CACHE='bootstrap/cache/routes.dusk.php'; APP_EVENTS_CACHE='bootstrap/cache/events.dusk.php'
        DB_CONNECTION='mysql'; DB_HOST='127.0.0.1'; DB_PORT='3306'; DB_DATABASE='satuair_dusk_jira'; DB_URL=''
        DB_USERNAME=$(if ($env:DUSK_DB_USERNAME) { $env:DUSK_DB_USERNAME } else { 'root' })
        DB_PASSWORD=$(if ($env:DUSK_DB_PASSWORD) { $env:DUSK_DB_PASSWORD } else { '' })
        DUSK_TESTING='1'; DUSK_HEADLESS=$Headless.IsPresent.ToString().ToLowerInvariant()
        DUSK_STEP_DELAY=$Delay.ToString(); DUSK_TYPE_DELAY=$TypeDelay.ToString()
        SESSION_DRIVER='file'; SESSION_COOKIE='satuair_dusk_jira_session'; CACHE_STORE='array'
        BCRYPT_ROUNDS='4'; MAIL_MAILER='array'; QUEUE_CONNECTION='sync'
        LOG_CHANNEL='single'; LOG_LEVEL='error'; VIEW_COMPILED_PATH=(Join-Path $testRuntime 'views')
    }
    New-Item -ItemType Directory -Force $variables.VIEW_COMPILED_PATH | Out-Null
    foreach ($key in $variables.Keys) {
        $savedVariables[$key] = [Environment]::GetEnvironmentVariable($key, 'Process')
        [Environment]::SetEnvironmentVariable($key, $variables[$key], 'Process')
    }
    & $phpBinary 'tests/Browser/Support/create-database.php'
    if ($LASTEXITCODE -ne 0) { throw 'Gagal membuat database Dusk terpisah.' }
    if (-not (Test-Path 'vendor/laravel/dusk/bin/chromedriver-win.exe')) {
        & $phpBinary artisan dusk:chrome-driver --detect
        if ($LASTEXITCODE -ne 0) { throw 'ChromeDriver belum tersedia.' }
    }
    $serverProcess = Start-Process -FilePath $phpBinary -ArgumentList @('-S', "127.0.0.1:$Port", '-t', 'public', 'tests/Browser/Support/server.php') -WorkingDirectory $taskRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $testArtifacts "server-$runStamp.out.log") -RedirectStandardError (Join-Path $testArtifacts "server-$runStamp.err.log")
    $ready = $false
    for ($attempt = 0; $attempt -lt 30; $attempt++) {
        try {
            $response = Invoke-WebRequest "$($variables.APP_URL)/login" -UseBasicParsing -TimeoutSec 3
            if ($response.Headers['X-SatuAir-Dusk-Database'] -eq 'satuair_dusk_jira') { $ready = $true; break }
        } catch { Start-Sleep -Milliseconds 300 }
        if ($serverProcess.HasExited) { break }
    }
    if (-not $ready) { throw "Server Dusk gagal dimulai. Periksa tests/Browser/artifacts/server-$runStamp.err.log" }
    Write-Host "Dusk: $($variables.APP_URL) | DB satuair_dusk_jira | jeda $Delay ms | headless $Headless"
    $testArguments = @('artisan', 'dusk', '--without-tty', '--testdox', '--colors=never', '--log-junit', "tests/Browser/artifacts/junit-$runStamp.xml")
    if (-not $Headless) { $testArguments += '--browse' }
    if ($Filter) { $testArguments += @('--filter', $Filter) }
    if ($Group) { $testArguments += @('--group', $Group) }
    & $phpBinary @testArguments 2>&1 | Tee-Object -FilePath (Join-Path $testArtifacts "run-$runStamp.txt")
    $exitCode = $LASTEXITCODE
} finally {
    if ($serverProcess -and -not $serverProcess.HasExited) { Stop-Process -Id $serverProcess.Id }
    foreach ($key in $savedVariables.Keys) { [Environment]::SetEnvironmentVariable($key, $savedVariables[$key], 'Process') }
    Pop-Location
    $runLock.Dispose()
}
exit $exitCode
