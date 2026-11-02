<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Turns the exam print page into a real PDF with headless Chrome / Edge, so the preview shows
 * exactly what will be printed (same page breaks, margins, first-page header and footer).
 */
class ExamPdf
{
    private static ?string $resolved = null;

    /** Path to a Chrome, Chromium or Edge executable, or null when none is installed. */
    public static function browserPath(): ?string
    {
        if (self::$resolved !== null) {
            return self::$resolved !== '' ? self::$resolved : null;
        }

        $configured = config('services.chrome.path');
        if ($configured) {
            self::$resolved = is_file($configured) ? $configured : '';

            return self::$resolved !== '' ? self::$resolved : null;
        }

        $join = fn (?string $base, string $rest) => $base ? rtrim($base, '\\/').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $rest) : null;
        $candidates = array_filter([
            $join(getenv('ProgramFiles') ?: null, 'Google/Chrome/Application/chrome.exe'),
            $join(getenv('ProgramFiles(x86)') ?: null, 'Google/Chrome/Application/chrome.exe'),
            $join(getenv('LOCALAPPDATA') ?: null, 'Google/Chrome/Application/chrome.exe'),
            $join(getenv('ProgramFiles(x86)') ?: null, 'Microsoft/Edge/Application/msedge.exe'),
            $join(getenv('ProgramFiles') ?: null, 'Microsoft/Edge/Application/msedge.exe'),
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                self::$resolved = $candidate;

                return $candidate;
            }
        }

        $finder = new ExecutableFinder();
        foreach (['google-chrome', 'google-chrome-stable', 'chromium', 'chromium-browser', 'microsoft-edge'] as $command) {
            $found = $finder->find($command);
            if ($found) {
                self::$resolved = $found;

                return $found;
            }
        }

        self::$resolved = '';

        return null;
    }

    public static function available(): bool
    {
        return self::browserPath() !== null;
    }

    /**
     * Embed a public image as a data: URI. The PDF engine then never has to call back into the
     * web server (which would hang a single-threaded `php artisan serve`).
     */
    public static function dataUri(string $relative): string
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        if ($relative === '' || str_contains($relative, '..')) {
            return '';
        }

        $files = [public_path($relative)];
        if (str_starts_with($relative, 'storage/')) {
            $files[] = storage_path('app/public/'.substr($relative, 8));
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                $mime = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'svg' => 'image/svg+xml',
                    default => 'application/octet-stream',
                };

                return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($file));
            }
        }

        return '';
    }

    /** Print a complete HTML document to PDF and return the PDF bytes. */
    public static function fromHtml(string $html): string
    {
        $browser = self::browserPath();
        if (! $browser) {
            throw new RuntimeException('No Chrome, Chromium or Edge was found. Install one, or set CHROME_PATH in .env.');
        }

        $dir = storage_path('app/pdf-tmp');
        File::ensureDirectoryExists($dir);

        $id = bin2hex(random_bytes(6));
        $htmlFile = $dir.DIRECTORY_SEPARATOR."exam-$id.html";
        $pdfFile = $dir.DIRECTORY_SEPARATOR."exam-$id.pdf";
        $profile = $dir.DIRECTORY_SEPARATOR."profile-$id";

        file_put_contents($htmlFile, $html);

        $command = [
            $browser,
            '--headless=new',
            '--disable-gpu',
            '--no-first-run',
            '--no-default-browser-check',
            '--disable-extensions',
            '--user-data-dir='.$profile,      // separate profile: never clashes with a Chrome that is already open
            '--no-pdf-header-footer',         // Chrome 112+
            '--print-to-pdf-no-header',        // older Chrome
            '--print-to-pdf='.$pdfFile,
        ];
        if (config('services.chrome.no_sandbox')) {
            $command[] = '--no-sandbox';
        }
        $command[] = self::fileUrl($htmlFile);

        $process = new Process($command);
        $process->setTimeout(60);

        try {
            $process->run();

            if (! is_file($pdfFile) || filesize($pdfFile) === 0) {
                $why = trim($process->getErrorOutput() ?: $process->getOutput());
                throw new RuntimeException('The PDF engine did not produce a file.'.($why !== '' ? ' '.mb_substr($why, 0, 400) : ''));
            }

            return (string) file_get_contents($pdfFile);
        } finally {
            @unlink($htmlFile);
            @unlink($pdfFile);
            try {
                File::deleteDirectory($profile);
            } catch (\Throwable $e) {
                // Windows may still hold a lock for a moment; the folder is harmless temp data.
            }
        }
    }

    private static function fileUrl(string $path): string
    {
        $segments = explode('/', str_replace('\\', '/', $path));
        $segments = array_map(fn ($s) => preg_match('/^[A-Za-z]:$/', $s) ? $s : rawurlencode($s), $segments);

        return 'file:///'.ltrim(implode('/', $segments), '/');
    }
}
