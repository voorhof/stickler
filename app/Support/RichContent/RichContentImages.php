<?php

namespace App\Support\RichContent;

use App\Models\Media;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

class RichContentImages
{
    /**
     * Map every rich-content <img> media UUID (data-id) to its alt text.
     *
     * @return array<string, string> uuid => alt
     */
    public static function altByUuid(?string $html): array
    {
        if (! is_string($html) || blank($html)) {
            return [];
        }

        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();

        $map = [];

        foreach ($document->getElementsByTagName('img') as $img) {
            $uuid = $img->getAttribute('data-id');
            $alt = $img->getAttribute('alt');

            // Only real media UUIDs; skip unsaved path-style ids like "attachments/....jpg".
            if (Str::isUuid($uuid) && filled($alt)) {
                $map[$uuid] = $alt;
            }
        }

        return $map;
    }

    /**
     * Remove any rich-content <img> tag matching the given media UUID (data-id).
     */
    public static function removeImageByUuid(?string $html, string $uuid): ?string
    {
        if (! is_string($html) || blank($html) || blank($uuid)) {
            return $html;
        }

        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query("//img[@data-id='$uuid']");

        $modified = false;

        foreach ($nodes as $img) {
            $parent = $img->parentNode;
            $img->parentNode->removeChild($img);
            $modified = true;

            // Optionally clean up empty wrapping <p> tags
            if ($parent && $parent->nodeName === 'p' && mb_trim($parent->textContent) === '' && $parent->childNodes->length === 0) {
                $parent->parentNode->removeChild($parent);
            }
        }

        if (! $modified) {
            return $html;
        }

        $wrapper = $document->getElementsByTagName('div')->item(0);
        $htmlOutput = '';

        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $htmlOutput .= $document->saveHTML($child);
            }
        }

        return $htmlOutput;
    }

    /**
     * Copy any rich-content media referenced in HTML that does not belong to the given model yet,
     * updating the <img> tags' data-id and src attributes with the new media UUID and URL.
     */
    public static function copyMediaForModel(?string $html, mixed $model): ?string
    {
        if (! is_string($html) || blank($html) || ! $model instanceof Model || ! $model instanceof HasMedia) {
            return $html;
        }

        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();

        $modified = false;

        foreach ($document->getElementsByTagName('img') as $img) {
            $uuid = $img->getAttribute('data-id');

            if (! Str::isUuid($uuid)) {
                continue;
            }

            /** @var Media|null $media */
            $media = Media::where('uuid', $uuid)->first();

            if (! $media) {
                continue;
            }

            // If the media already belongs to this model, no need to copy
            if ($media->model_type === $model->getMorphClass() && $media->model_id === $model->getKey()) {
                continue;
            }

            // Copy media to the new model (copies files and creates new media record)
            $newMedia = $media->copy($model, $media->collection_name ?? 'content');

            // Update data-id to new media uuid
            $img->setAttribute('data-id', $newMedia->uuid);

            // Update src attribute to new media URL
            $oldSrc = $img->getAttribute('src');
            if (filled($oldSrc)) {
                $img->setAttribute('src', $newMedia->getUrl());
            }

            $modified = true;
        }

        if (! $modified) {
            return $html;
        }

        $wrapper = $document->getElementsByTagName('div')->item(0);
        $htmlOutput = '';

        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $htmlOutput .= $document->saveHTML($child);
            }
        }

        return $htmlOutput;
    }
}
