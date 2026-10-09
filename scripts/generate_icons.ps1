Add-Type -AssemblyName System.Drawing

$sourcePath = (Resolve-Path 'assets\images\app_logo.png').Path
$src = [System.Drawing.Bitmap]::FromFile($sourcePath)

function Resize-Image($srcImg, $width, $height, $destPath) {
    $dest = New-Object System.Drawing.Bitmap($width, $height, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($dest)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
    $g.Clear([System.Drawing.Color]::Transparent)
    $g.DrawImage($srcImg, 0, 0, $width, $height)
    $g.Dispose()
    $dest.Save($destPath, [System.Drawing.Imaging.ImageFormat]::Png)
    $dest.Dispose()
    Write-Host "Generated: $destPath ($width x $height)"
}

function Generate-Maskable($srcImg, $size, $destPath) {
    $dest = New-Object System.Drawing.Bitmap($size, $size, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($dest)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
    
    # Fill solid dark theme background for safe maskable area
    $brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(255, 9, 9, 11))
    $g.FillRectangle($brush, 0, 0, $size, $size)
    $brush.Dispose()
    
    # Draw logo in safe zone (80% of total size centered)
    $pad = [int]($size * 0.1)
    $innerSize = $size - (2 * $pad)
    $g.DrawImage($srcImg, $pad, $pad, $innerSize, $innerSize)
    
    $g.Dispose()
    $dest.Save($destPath, [System.Drawing.Imaging.ImageFormat]::Png)
    $dest.Dispose()
    Write-Host "Generated Maskable: $destPath"
}

$sizes = @(
    @{ w=72;  h=72;  path='assets\images\icons\icon-72x72.png' },
    @{ w=96;  h=96;  path='assets\images\icons\icon-96x96.png' },
    @{ w=128; h=128; path='assets\images\icons\icon-128x128.png' },
    @{ w=144; h=144; path='assets\images\icons\icon-144x144.png' },
    @{ w=152; h=152; path='assets\images\icons\icon-152x152.png' },
    @{ w=180; h=180; path='assets\images\icons\apple-touch-icon.png' },
    @{ w=192; h=192; path='assets\images\icons\icon-192x192.png' },
    @{ w=384; h=384; path='assets\images\icons\icon-384x384.png' },
    @{ w=512; h=512; path='assets\images\icons\icon-512x512.png' },
    @{ w=64;  h=64;  path='assets\images\favicon.png' },
    @{ w=32;  h=32;  path='assets\images\favicon-32x32.png' },
    @{ w=16;  h=16;  path='assets\images\favicon-16x16.png' }
)

foreach ($s in $sizes) {
    Resize-Image $src $s.w $s.h $s.path
}

Generate-Maskable $src 512 'assets\images\icons\maskable-icon-512x512.png'

# Create favicon.ico from 32x32 bitmap
$icoBmp = New-Object System.Drawing.Bitmap(32, 32, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
$icoG = [System.Drawing.Graphics]::FromImage($icoBmp)
$icoG.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$icoG.DrawImage($src, 0, 0, 32, 32)
$icoG.Dispose()

$hIcon = $icoBmp.GetHicon()
$icon = [System.Drawing.Icon]::FromHandle($hIcon)
$fs = [System.IO.File]::Create((Resolve-Path 'favicon.ico').Path)
$icon.Save($fs)
$fs.Close()
$icon.Dispose()
$icoBmp.Dispose()

Write-Host "Generated: favicon.ico"

# Update favicon.svg with base64 embedded app_logo
$pngBytes = [System.IO.File]::ReadAllBytes((Resolve-Path 'assets\images\favicon.png').Path)
$base64Png = [System.Convert]::ToBase64String($pngBytes)
$svg = @"
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64">
  <image width="64" height="64" href="data:image/png;base64,$base64Png" />
</svg>
"@
[System.IO.File]::WriteAllText((Resolve-Path 'assets\images\favicon.svg').Path, $svg)
Write-Host "Generated: assets\images\favicon.svg"

$src.Dispose()
Write-Host "All icons and favicons regenerated successfully from app_logo.png!"
