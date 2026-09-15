$ErrorActionPreference = 'Stop'
$base = 'http://localhost/website'

function Get-Csrf($html) {
    $m = [regex]::Match($html, 'name="csrf" value="([^"]+)"')
    if (-not $m.Success) { return $null }
    return $m.Groups[1].Value
}
function Req($session, $url, $method = 'GET', $body = $null) {
    return Invoke-WebRequest -Uri $url -WebSession $session -Method $method -Body $body -UseBasicParsing -MaximumRedirection 5
}

# customer login
$cust = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$r = Req $cust "$base/account.php"
$r = Req $cust "$base/account.php" 'POST' @{ csrf = (Get-Csrf $r.Content); action = 'login'; email = 'testshopper@example.com'; password = 'Test1234'; next = '' }
Write-Host "CUSTOMER LOGIN OK: $($r.Content -match 'My Account')"

# admin login
$adm = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$r = Req $adm "$base/account.php"
$r = Req $adm "$base/account.php" 'POST' @{ csrf = (Get-Csrf $r.Content); action = 'login'; email = 'admin@pastryhaven.local'; password = 'Admin123!'; next = '' }
Write-Host "ADMIN LOGIN OK: $($r.Content -match 'OPEN ADMIN')"

# admin: menu has no buy controls
$r = Req $adm "$base/menu.php"
Write-Host "ADMIN MENU HAS BUY_NOW BUTTON: $($r.Content -match 'value=""buy_now""')"
Write-Host "ADMIN MENU SHOWS NOTICE: $($r.Content -match 'Admins cannot buy products')"
Write-Host "ADMIN NAV HAS CART ICON: $($r.Content -match 'aria-label=""Cart""')"
Write-Host "ADMIN NAV HAS INBOX ICON: $($r.Content -match 'admin/inbox.php')"

# admin: product page
$r = Req $adm "$base/product.php?id=chocolate-cake"
Write-Host "ADMIN PRODUCT HAS BUY FORM: $($r.Content -match 'value=""buy_now""')"
Write-Host "ADMIN PRODUCT NOTICE: $($r.Content -match 'Admin accounts cannot buy products')"

# admin: forced POST to cart using a csrf token from the contact form
$r = Req $adm "$base/contact.php"
$csrf = Get-Csrf $r.Content
$r = Req $adm "$base/cart.php" 'POST' @{ csrf = $csrf; action = 'add'; id = 'chocolate-cake'; qty = '1'; return = 'menu.php' }
Write-Host "ADMIN FORCED ADD BLOCKED: $($r.Content -match 'Admin accounts cannot buy products')"
$r = Req $adm "$base/cart.php"
Write-Host "ADMIN CART PAGE NOTICE: $($r.Content -match 'Admin accounts cannot buy products')"
$r = Req $adm "$base/checkout.php"
Write-Host "ADMIN CHECKOUT REDIRECTED TO: $($r.BaseResponse.ResponseUri.AbsolutePath)"
$r = Req $adm "$base/inbox.php"
Write-Host "ADMIN /inbox.php REDIRECTS TO: $($r.BaseResponse.ResponseUri.AbsolutePath)"

# customer: sees admin reply, unread badge cleared after open
$r = Req $cust "$base/menu.php"
Write-Host "CUSTOMER NAV INBOX LINK: $($r.Content -match '>\s*INBOX')"
$r = Req $cust "$base/inbox.php"
Write-Host "CUSTOMER THREAD LIST: $($r.Content -match 'Order question')"
Write-Host "CUSTOMER UNREAD MARK: $($r.Content -match 'New reply')"
$r = Req $cust "$base/inbox.php?id=1"
Write-Host "CUSTOMER SEES REPLY: $($r.Content -match 'deliver on Sundays until')"
$r = Req $cust "$base/inbox.php"
Write-Host "CUSTOMER UNREAD CLEARED: $(-not ($r.Content -match 'New reply'))"

# customer: reply back, admin sees it
$r = Req $cust "$base/inbox.php?id=1"
$csrf = Get-Csrf $r.Content
$r = Req $cust "$base/inbox.php" 'POST' @{ csrf = $csrf; action = 'reply'; thread_id = '1'; body = 'Great, I will order Sunday morning.' }
Write-Host "CUSTOMER REPLY POSTED: $($r.Content -match 'order Sunday morning')"
$r = Req $adm "$base/admin/inbox.php"
Write-Host "ADMIN SEES NEW CUSTOMER MSG: $($r.Content -match 'admin-low')"

# customer: unchecking all items is rejected
$r = Req $cust "$base/cart.php"
$csrf = Get-Csrf $r.Content
$r = Req $cust "$base/cart.php" 'POST' @{ csrf = $csrf; action = 'checkout_selected' }
Write-Host "EMPTY SELECTION BLOCKED: $($r.Content -match 'Please check the items')"

# customer: direct checkout.php with no selection
$r = Req $cust "$base/checkout.php"
Write-Host "DIRECT CHECKOUT REDIRECT: $($r.BaseResponse.ResponseUri.AbsolutePath) / $($r.Content -match 'Please check the items')"
