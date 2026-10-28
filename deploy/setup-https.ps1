# Thiết lập https://cinebooking.com cho máy local (nginx + mkcert).
# Chạy trong PowerShell *Administrator*:  .\deploy\setup-https.ps1
# Tự dùng nginx đang chạy (hoặc có trong PATH); chỉ thêm conf.d\cinebooking.conf, không sửa nginx.conf.
# Tham số: -NginxDir 'D:\Servers\nginx'  -MkcertPath 'D:\Servers\mkcert\mkcert.exe'
param([string]$NginxDir, [string]$MkcertPath)

$ErrorActionPreference = 'Stop'
$domain = 'cinebooking.com'
$certDir = Join-Path $PSScriptRoot 'nginx\certs'

# --- nginx ---
if (-not $NginxDir) {
    $proc = Get-Process nginx -ErrorAction SilentlyContinue | Where-Object Path | Select-Object -First 1
    if ($proc) { $NginxDir = Split-Path $proc.Path }
    elseif (Get-Command nginx -ErrorAction SilentlyContinue) { $NginxDir = Split-Path (Get-Command nginx).Source }
    else { throw 'Không tìm thấy nginx. Truyền -NginxDir <thư mục chứa nginx.exe>.' }
}
$nginx = Join-Path $NginxDir 'nginx.exe'
if (-not (Test-Path $nginx)) { throw "Không có $nginx" }
$mainConf = Join-Path $NginxDir 'conf\nginx.conf'
if (-not (Select-String -Path $mainConf -Pattern '^\s*include\s+conf\.d/\*\.conf' -Quiet)) {
    throw "$mainConf chưa có dòng 'include conf.d/*.conf;' trong khối http. Thêm dòng đó rồi chạy lại."
}

# --- mkcert ---
if (-not $MkcertPath) {
    $cmd = Get-Command mkcert -ErrorAction SilentlyContinue
    if ($cmd) { $MkcertPath = $cmd.Source }
    elseif (Test-Path 'D:\Servers\mkcert\mkcert.exe') { $MkcertPath = 'D:\Servers\mkcert\mkcert.exe' }
    else { throw 'Không tìm thấy mkcert. Truyền -MkcertPath <đường dẫn mkcert.exe>.' }
}

# 1. CA local + chứng chỉ
& $MkcertPath -install
& $MkcertPath -cert-file "$certDir\$domain.pem" -key-file "$certDir\$domain-key.pem" $domain "www.$domain"

# 2. hosts
$hosts = "$env:SystemRoot\System32\drivers\etc\hosts"
if (-not (Select-String -Path $hosts -Pattern "\s$([regex]::Escape($domain))(\s|$)" -Quiet)) {
    Add-Content $hosts "`n127.0.0.1 $domain www.$domain"
    Write-Host "Đã thêm $domain vào hosts."
}

# 3. conf.d + reload
$confD = Join-Path $NginxDir 'conf\conf.d'
New-Item -ItemType Directory -Force $confD | Out-Null
$tpl = Get-Content (Join-Path $PSScriptRoot 'nginx\cinebooking.conf') -Raw
$projectDir = (Split-Path $PSScriptRoot -Parent).Replace('\', '/')
$tpl.Replace('{{CERT_DIR}}', $certDir.Replace('\', '/')).Replace('{{PROJECT_DIR}}', $projectDir) | Set-Content (Join-Path $confD 'cinebooking.conf') -Encoding ASCII
& $nginx -p "$NginxDir\" -t
if (Get-Process nginx -ErrorAction SilentlyContinue) { & $nginx -p "$NginxDir\" -s reload }
else { Start-Process $nginx -ArgumentList '-p', "$NginxDir\" -WindowStyle Hidden -WorkingDirectory $NginxDir }

Write-Host "`nXong. Chạy app:  php -S 127.0.0.1:8100 -t public public/index.php"
Write-Host "Rồi mở https://$domain"
