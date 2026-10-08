param(
    [string]$Php = 'C:\wamp64\bin\php\php8.3.14\php.exe',
    [ValidateSet('create-admin','reset-password')][string]$Action = 'create-admin'
)
$cmsEmail = Read-Host 'Administrator email'
$cmsName = if ($Action -eq 'create-admin') { Read-Host 'Display name' } else { '' }
$cmsPassword = Read-Host 'Password (12 to 72 bytes)' -AsSecureString
$cmsPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($cmsPassword)
try {
    $cmsPlaintext = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($cmsPointer)
    $cmsInput = @{email=$cmsEmail; display_name=$cmsName; password=$cmsPlaintext} | ConvertTo-Json -Compress
    $cmsPreviousEncoding = $OutputEncoding
    $OutputEncoding = [Text.UTF8Encoding]::new($false)
    $cmsInput | & $Php -d xdebug.mode=off (Join-Path $PSScriptRoot 'cms-setup.php') $Action
} finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($cmsPointer)
    $cmsPlaintext = $null; $cmsInput = $null; $cmsPassword.Dispose()
    if ($cmsPreviousEncoding) { $OutputEncoding = $cmsPreviousEncoding }
}
