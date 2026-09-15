$ErrorActionPreference = 'Stop'
$base = 'http://localhost/website'

function Get-Csrf($html) {
    $m = [regex]::Match($html, 'name="csrf" value="([^"]+)"')
    if (-not $m.Success) { throw 'csrf not found' }
    return $m.Groups[1].Value
}

function Req($session, $url, $method = 'GET', $body = $null) {
    return Invoke-WebRequest -Uri $url -WebSession $session -Method $method -Body $body -UseBasicParsing -MaximumRedirection 5
}

# ---------- customer ----------
$cust = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$r = Req $cust "$base/account.php?mode=signup"
$csrf = Get-Csrf $r.Content
$r = Req $cust "$base/account.php?mode=signup" 'POST' @{
    csrf = $csrf; action = 'register'; name = 'Test Shopper';
    email = 'testshopper@example.com'; phone = '09171234567';
    password = 'Test1234'; confirm_password = 'Test1234'; next = ''
}
Write-Host "REGISTER/LOGIN status: $($r.StatusCode)"

# if already exists, log in instead
if ($r.Content -match 'already') {
    $r = Req $cust "$base/account.php"
    $csrf = Get-Csrf $r.Content
    $r = Req $cust "$base/account.php" 'POST' @{
        csrf = $csrf; action = 'login'; email = 'testshopper@example.com'; password = 'Test1234'; next = ''
    }
    Write-Host "LOGIN status: $($r.StatusCode)"
}

# add two products to cart
$r = Req $cust "$base/menu.php"
$csrf = Get-Csrf $r.Content
$ids = [regex]::Matches($r.Content, 'name="id" value="([^"]+)"') | ForEach-Object { $_.Groups[1].Value } | Select-Object -Unique -First 2
Write-Host "PRODUCT IDS: $($ids -join ', ')"
foreach ($id in $ids) {
    $r = Req $cust "$base/cart.php" 'POST' @{ csrf = $csrf; action = 'add'; id = $id; qty = '1'; return = 'menu.php' }
}

$r = Req $cust "$base/cart.php"
$boxes = [regex]::Matches($r.Content, 'name="keys\[\]" value="([^"]+)"') | ForEach-Object { $_.Groups[1].Value }
Write-Host "CART CHECKBOXES: $($boxes.Count)"
Write-Host "HAS SELECT ALL: $($r.Content -match 'cart-select-all')"
Write-Host "CART TOTAL LINE: $([regex]::Match($r.Content, 'Total\s*\S?[0-9][0-9,\.]*').Value)"

# checkout only first item
$csrf = Get-Csrf $r.Content
$r = Req $cust "$base/cart.php" 'POST' @{ csrf = $csrf; action = 'checkout_selected'; 'keys[]' = $boxes[0] }
Write-Host "CHECKOUT PAGE TOTAL: $([regex]::Match($r.Content, 'Total:\s*\S?[0-9][0-9,\.]*').Value)"

# send inbox message
$r = Req $cust "$base/inbox.php?compose=1"
$csrf = Get-Csrf $r.Content
$r = Req $cust "$base/inbox.php" 'POST' @{ csrf = $csrf; action = 'compose'; subject = 'Order question'; body = 'Do you deliver on Sundays?' }
Write-Host "CUSTOMER INBOX AFTER SEND: $([regex]::Match($r.Content, 'Order question').Value)"
$threadId = [regex]::Match($r.BaseResponse.ResponseUri.Query, 'id=(\d+)').Groups[1].Value
Write-Host "THREAD ID: $threadId"

# ---------- admin ----------
$adm = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$r = Req $adm "$base/account.php"
$csrf = Get-Csrf $r.Content
$r = Req $adm "$base/account.php" 'POST' @{
    csrf = $csrf; action = 'login'; email = 'admin@pastryhaven.local'; password = 'Admin123!'; next = ''
}
Write-Host "ADMIN LOGIN: $($r.StatusCode)"

$r = Req $adm "$base/admin/inbox.php"
Write-Host "ADMIN SEES THREAD: $($r.Content -match 'Order question')"

$r = Req $adm "$base/admin/inbox.php?id=$threadId"
$csrf = Get-Csrf $r.Content
$r = Req $adm "$base/admin/inbox.php" 'POST' @{ csrf = $csrf; thread_id = $threadId; body = 'Yes, we deliver on Sundays until 4 PM.' }
Write-Host "ADMIN REPLY SENT: $($r.Content -match 'deliver on Sundays until')"

# admin tries to buy
$r = Req $adm "$base/menu.php"
$csrf = Get-Csrf $r.Content
Write-Host "ADMIN MENU HAS ADD BUTTON: $($r.Content -match 'value=""buy_now""')"
Write-Host "ADMIN MENU NOTICE: $($r.Content -match 'Admins cannot buy products')"
$r = Req $adm "$base/cart.php" 'POST' @{ csrf = $csrf; action = 'add'; id = $ids[0]; qty = '1'; return = 'menu.php' }
Write-Host "ADMIN ADD BLOCKED MSG: $($r.Content -match 'Admin accounts cannot buy products')"

# ---------- customer sees reply ----------
$r = Req $cust "$base/inbox.php?id=$threadId"
Write-Host "CUSTOMER SEES ADMIN REPLY: $($r.Content -match 'deliver on Sundays until')"
$r = Req $cust "$base/inbox.php"
Write-Host "CUSTOMER THREAD LIST OK: $($r.Content -match 'Order question')"
