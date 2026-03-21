# ============================================================
#  AYKA ORIGINALS - NativePHP Mobile App Setup
#  Builds real Android APK + iOS IPA from your Laravel app
#  Based on: https://nativephp.com/docs/mobile/3
#  Run: powershell -ExecutionPolicy Bypass -File ayka_nativephp.ps1
# ============================================================

$ProjectPath = "C:\laragon\www\ayka-originals"
Set-Location $ProjectPath

$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

function Write-File($Path, $Content) {
    $Dir = Split-Path $Path -Parent
    if (!(Test-Path $Dir)) { New-Item -ItemType Directory -Path $Dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($Path, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $Path" -ForegroundColor Green
}

function Check-Command($cmd) {
    return [bool](Get-Command $cmd -ErrorAction SilentlyContinue)
}

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  AYKA ORIGINALS - NativePHP Mobile Build Setup" -ForegroundColor Cyan
Write-Host "  Android APK + iOS IPA from your Laravel app" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

# -- STEP 1: Check Prerequisites -------------------------------
Write-Host "[STEP 1] Checking prerequisites..." -ForegroundColor Yellow
Write-Host ""

$allGood = $true

# PHP version
$phpVer = & $phpExe -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>$null
Write-Host "  PHP: $phpVer" -ForegroundColor $(if ($phpVer -ge "8.3") {"Green"} else {"Red"})
if ($phpVer -lt "8.3") {
    Write-Host "  [WARN] PHP 8.3+ required. You have $phpVer" -ForegroundColor Red
    Write-Host "         Download PHP 8.3 from: https://windows.php.net/download" -ForegroundColor Yellow
    $allGood = $false
}

# Composer
if (Check-Command "composer") {
    Write-Host "  Composer: FOUND" -ForegroundColor Green
} else {
    Write-Host "  Composer: NOT FOUND" -ForegroundColor Red
    Write-Host "  Download: https://getcomposer.org/download/" -ForegroundColor Yellow
    $allGood = $false
}

# Node.js
if (Check-Command "node") {
    $nodeVer = node --version
    Write-Host "  Node.js: $nodeVer" -ForegroundColor Green
} else {
    Write-Host "  Node.js: NOT FOUND" -ForegroundColor Red
    Write-Host "  Download: https://nodejs.org (LTS version)" -ForegroundColor Yellow
    $allGood = $false
}

# Java
if (Check-Command "java") {
    $javaVer = java -version 2>&1 | Select-String "version" | Select-Object -First 1
    Write-Host "  Java: $javaVer" -ForegroundColor Green
} else {
    Write-Host "  Java: NOT FOUND" -ForegroundColor Red
    Write-Host "  Download: https://www.microsoft.com/openjdk (JDK 17)" -ForegroundColor Yellow
    $allGood = $false
}

# 7-Zip (required for Windows Android builds)
$sevenZip = Test-Path "C:\Program Files\7-Zip\7z.exe"
if ($sevenZip) {
    Write-Host "  7-Zip: FOUND" -ForegroundColor Green
} else {
    Write-Host "  7-Zip: NOT FOUND (required for Android on Windows)" -ForegroundColor Red
    Write-Host "  Download: https://www.7-zip.org/" -ForegroundColor Yellow
    $allGood = $false
}

# Android Studio / ADB
$adbPath = "$env:LOCALAPPDATA\Android\Sdk\platform-tools\adb.exe"
if (Test-Path $adbPath) {
    Write-Host "  Android SDK: FOUND" -ForegroundColor Green
} else {
    Write-Host "  Android SDK: NOT FOUND" -ForegroundColor Yellow
    Write-Host "  Download Android Studio: https://developer.android.com/studio" -ForegroundColor Yellow
    Write-Host "  (Required to build APK)" -ForegroundColor Yellow
}

Write-Host ""

if (-not $allGood) {
    Write-Host "======================================================" -ForegroundColor Red
    Write-Host "  MISSING PREREQUISITES - Install them first!" -ForegroundColor Red
    Write-Host "======================================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "  Install in this order:" -ForegroundColor White
    Write-Host "  1. PHP 8.3+     https://windows.php.net/download" -ForegroundColor Gray
    Write-Host "  2. Composer     https://getcomposer.org/download/" -ForegroundColor Gray
    Write-Host "  3. Node.js LTS  https://nodejs.org" -ForegroundColor Gray
    Write-Host "  4. JDK 17       https://www.microsoft.com/openjdk" -ForegroundColor Gray
    Write-Host "  5. 7-Zip        https://www.7-zip.org/" -ForegroundColor Gray
    Write-Host "  6. Android Studio https://developer.android.com/studio" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  After installing all of the above, run this script again." -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# -- STEP 2: Set Android environment variables -----------------
Write-Host "[STEP 2] Setting Android environment variables..." -ForegroundColor Yellow

$androidHome = "$env:LOCALAPPDATA\Android\Sdk"
$javaHome = ""

# Try to find Java 17
$javaPaths = @(
    "C:\Program Files\Microsoft\jdk-17.0.8.7-hotspot",
    "C:\Program Files\Eclipse Adoptium\jdk-17*",
    "C:\Program Files\Java\jdk-17*"
)
foreach ($p in $javaPaths) {
    $found = Get-ChildItem $p -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($found) { $javaHome = $found.FullName; break }
    if (Test-Path $p) { $javaHome = $p; break }
}

if ($javaHome) {
    $env:JAVA_HOME = $javaHome
    Write-Host "  JAVA_HOME set to: $javaHome" -ForegroundColor Green
} else {
    Write-Host "  [WARN] Could not auto-detect JAVA_HOME. Set it manually if build fails." -ForegroundColor Yellow
}

if (Test-Path $androidHome) {
    $env:ANDROID_HOME = $androidHome
    $env:PATH = "$env:PATH;$androidHome\platform-tools;$androidHome\tools;$androidHome\emulator"
    Write-Host "  ANDROID_HOME set to: $androidHome" -ForegroundColor Green
} else {
    Write-Host "  [WARN] Android SDK not found at $androidHome" -ForegroundColor Yellow
    Write-Host "         Open Android Studio > Tools > SDK Manager to install it" -ForegroundColor Yellow
}

# -- STEP 3: Add Windows Defender exclusions -------------------
Write-Host ""
Write-Host "[STEP 3] Adding Windows Defender exclusions (speeds up build)..." -ForegroundColor Yellow
try {
    Add-MpPreference -ExclusionPath "C:\temp" -ErrorAction SilentlyContinue
    Add-MpPreference -ExclusionPath $ProjectPath -ErrorAction SilentlyContinue
    Write-Host "  [OK] Exclusions added" -ForegroundColor Green
} catch {
    Write-Host "  [SKIP] Could not add Defender exclusions (run as Admin for this)" -ForegroundColor Gray
}

# -- STEP 4: Install NativePHP Mobile package -----------------
Write-Host ""
Write-Host "[STEP 4] Installing NativePHP Mobile package..." -ForegroundColor Yellow
& $phpExe -r "
if (!file_exists('vendor/nativephp/mobile')) {
    echo 'needs_install';
} else {
    echo 'already_installed';
}
" | ForEach-Object {
    if ($_ -eq "needs_install") {
        Write-Host "  Running: composer require nativephp/mobile" -ForegroundColor Gray
        composer require nativephp/mobile
    } else {
        Write-Host "  [SKIP] nativephp/mobile already installed" -ForegroundColor Gray
    }
}

# -- STEP 5: Add NativePHP config to .env ---------------------
Write-Host ""
Write-Host "[STEP 5] Configuring .env for NativePHP..." -ForegroundColor Yellow

$envPath = Join-Path $ProjectPath ".env"
$envContent = [System.IO.File]::ReadAllText($envPath)

$nativeConfig = @"

# -- NativePHP Mobile ------------------------------------------
NATIVEPHP_APP_ID=com.aykaoriginals.app
NATIVEPHP_APP_VERSION=1.0.0
NATIVEPHP_APP_VERSION_CODE=1
NATIVEPHP_DEEPLINK_SCHEME=ayka
"@

if ($envContent -notmatch "NATIVEPHP_APP_ID") {
    $envContent += $nativeConfig
    [System.IO.File]::WriteAllText($envPath, $envContent, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] NativePHP env vars added" -ForegroundColor Green
} else {
    Write-Host "  [SKIP] NativePHP env vars already present" -ForegroundColor Gray
}

# -- STEP 6: Run NativePHP installer --------------------------
Write-Host ""
Write-Host "[STEP 6] Running NativePHP installer..." -ForegroundColor Yellow
Write-Host "  This downloads PHP binaries and sets up Android/iOS projects." -ForegroundColor Gray
Write-Host "  It will ask: 'Install ICU-enabled PHP binaries?' - press N (default)" -ForegroundColor Gray
Write-Host ""
& $phpExe artisan native:install

# -- STEP 7: Add nativephp to .gitignore ----------------------
Write-Host ""
Write-Host "[STEP 7] Updating .gitignore..." -ForegroundColor Yellow
$gitignorePath = Join-Path $ProjectPath ".gitignore"
$gitignore = [System.IO.File]::ReadAllText($gitignorePath)
if ($gitignore -notmatch "/nativephp") {
    $gitignore += "`n/nativephp`n"
    [System.IO.File]::WriteAllText($gitignorePath, $gitignore, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] /nativephp added to .gitignore" -ForegroundColor Green
} else {
    Write-Host "  [SKIP] Already in .gitignore" -ForegroundColor Gray
}

# -- DONE ------------------------------------------------------
Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  SETUP COMPLETE!" -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  YOUR NEXT COMMANDS:" -ForegroundColor White
Write-Host ""
Write-Host "  Run on Android emulator:" -ForegroundColor Cyan
Write-Host "    php artisan native:run android" -ForegroundColor White
Write-Host ""
Write-Host "  Run on iOS simulator (Mac only):" -ForegroundColor Cyan
Write-Host "    php artisan native:run ios" -ForegroundColor White
Write-Host ""
Write-Host "  Build release APK for Android:" -ForegroundColor Cyan
Write-Host "    php artisan native:build android" -ForegroundColor White
Write-Host "    APK will be at: nativephp/android/app/build/outputs/apk/release/" -ForegroundColor Gray
Write-Host ""
Write-Host "  Build release IPA for iOS (Mac only):" -ForegroundColor Cyan
Write-Host "    php artisan native:build ios" -ForegroundColor White
Write-Host ""
Write-Host "  OR use Bifrost cloud builder (no Mac needed for iOS!):" -ForegroundColor Cyan
Write-Host "    https://bifrost.nativephp.com" -ForegroundColor White
Write-Host ""
Write-Host "  IMPORTANT NOTES:" -ForegroundColor Yellow
Write-Host "  - Make sure Android Studio has at least one Virtual Device (AVD)" -ForegroundColor Gray
Write-Host "    Open Android Studio > Device Manager > Create Device" -ForegroundColor Gray
Write-Host "  - iOS builds require a Mac. On Windows, use Bifrost cloud builder." -ForegroundColor Gray
Write-Host "  - First build takes 5-15 mins (downloads Gradle + dependencies)" -ForegroundColor Gray
Write-Host "  - Your existing app, DB, routes stay exactly the same" -ForegroundColor Gray
Write-Host ""
Write-Host "  DOCS: https://nativephp.com/docs/mobile/3" -ForegroundColor Gray
Write-Host ""
