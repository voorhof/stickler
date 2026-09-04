<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Illuminate\Support\Facades\File;

it('has properly ordered keys in json files', function () {
    $files = [
        base_path('lang/en_US.json'),
        base_path('lang/nl_BE.json'),
    ];

    foreach ($files as $file) {
        if (! File::exists($file)) {
            continue;
        }

        $content = json_decode(File::get($file), true);
        if ($content === null) {
            continue;
        }

        $keys = array_keys($content);
        $sortedKeys = $keys;
        natcasesort($sortedKeys);

        expect(array_values($keys))->toBe(array_values($sortedKeys), 'File '.basename($file).' is not alphabetically ordered.');
    }
});

it('has properly ordered keys in php files', function () {
    $locales = ['en_US', 'nl_BE'];

    foreach ($locales as $locale) {
        $path = base_path("lang/$locale");
        if (! File::isDirectory($path)) {
            continue;
        }

        $files = File::allFiles($path);
        foreach ($files as $file) {
            if ($file->getFilename() === 'validation.php') {
                continue;
            }

            $translations = include $file->getRealPath();
            if (! is_array($translations)) {
                continue;
            }

            $keys = array_keys($translations);
            $sortedKeys = $keys;
            natcasesort($sortedKeys);

            expect(array_values($keys))->toBe(array_values($sortedKeys), "File {$file->getRelativePathname()} in locale $locale is not alphabetically ordered.");
        }
    }
});

it('has no missing translation keys', function () {
    // Check JSON files
    $enUsJson = json_decode(File::get(base_path('lang/en_US.json')), true);
    $nlBeJson = json_decode(File::get(base_path('lang/nl_BE.json')), true);

    if ($enUsJson !== null && $nlBeJson !== null) {
        $enUsKeys = array_keys($enUsJson);
        $nlBeKeys = array_keys($nlBeJson);

        $missingInNl = array_diff($enUsKeys, $nlBeKeys);
        $missingInEn = array_diff($nlBeKeys, $enUsKeys);

        expect($missingInNl)->toBeEmpty('Keys missing in nl_BE.json: '.implode(', ', $missingInNl))
            ->and($missingInEn)->toBeEmpty('Keys missing in en_US.json: '.implode(', ', $missingInEn));
    }

    // Check PHP files
    $checkPhpFiles = function ($sourceLocale, $targetLocale) {
        $sourcePath = base_path("lang/$sourceLocale");
        $files = File::allFiles($sourcePath);

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $targetFile = base_path("lang/$targetLocale/$relativePath");

            expect(File::exists($targetFile))->toBeTrue("File $relativePath exists in $sourceLocale but not in $targetLocale.");

            $sourceTranslations = include $file->getRealPath();
            $targetTranslations = include $targetFile;

            if (! is_array($sourceTranslations) || ! is_array($targetTranslations)) {
                continue;
            }

            $sourceKeys = array_keys($sourceTranslations);
            $targetKeys = array_keys($targetTranslations);

            $missingInTarget = array_diff($sourceKeys, $targetKeys);
            expect($missingInTarget)->toBeEmpty("Keys missing in $targetLocale/$relativePath: ".implode(', ', $missingInTarget));
        }
    };

    $checkPhpFiles('en_US', 'nl_BE');
    $checkPhpFiles('nl_BE', 'en_US');
});

it('checks vendor translations', function () {
    $vendorPath = base_path('lang/vendor');
    if (! File::isDirectory($vendorPath)) {
        return;
    }

    $packages = File::directories($vendorPath);

    foreach ($packages as $packagePath) {
        $packageName = basename($packagePath);
        $enUsPath = "$packagePath/en_US";
        $nlBePath = "$packagePath/nl_BE";

        $checkFiles = function ($sourcePath, $targetPath, $sourceLocale, $targetLocale) use ($packageName) {
            if (! File::isDirectory($sourcePath)) {
                return;
            }

            $files = File::allFiles($sourcePath);
            foreach ($files as $file) {
                $relativePath = $file->getRelativePathname();
                $targetFile = "$targetPath/$relativePath";

                expect(File::exists($targetFile))->toBeTrue("Vendor [$packageName] file $sourceLocale/$relativePath exists but $targetLocale/$relativePath is missing.");

                $sourceTranslations = include $file->getRealPath();
                $targetTranslations = include $targetFile;

                if (! is_array($sourceTranslations) || ! is_array($targetTranslations)) {
                    continue;
                }

                $sourceKeys = array_keys($sourceTranslations);
                $targetKeys = array_keys($targetTranslations);

                $missingInTarget = array_diff($sourceKeys, $targetKeys);
                expect($missingInTarget)->toBeEmpty("Vendor [$packageName] keys missing in $targetLocale/$relativePath: ".implode(', ', $missingInTarget));
            }
        };

        $checkFiles($enUsPath, $nlBePath, 'en_US', 'nl_BE');
        $checkFiles($nlBePath, $enUsPath, 'nl_BE', 'en_US');
    }
});
