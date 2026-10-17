<?php

/** @noinspection PhpUndefinedMethodInspection */

namespace App\Traits;

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait RenderResponsiveRichContent
{
    /**
     * Render rich content with responsive image support.
     */
    public function renderResponsiveRichContent(string $attributeName = 'content'): string
    {
        $attribute = $this->getRichContentAttribute($attributeName);
        $renderer = $attribute ? $attribute->getRenderer() : RichContentRenderer::make($this->getAttribute($attributeName));

        $renderer->processNodesUsing(function (object $node): void {
            if (($node->type ?? null) !== 'image') {
                return;
            }

            $uuid = $node->attrs->id ?? null;

            if (! $uuid) {
                return;
            }

            /** @var Media|null $media */
            $media = $this->media->firstWhere('uuid', $uuid) ?? Media::where('uuid', $uuid)->first();

            if (! $media) {
                return;
            }

            // Extract TipTap image attributes
            $alt = e($node->attrs->alt ?? $media->name ?? '');
            $title = isset($node->attrs->title) ? ' title="'.e($node->attrs->title).'"' : '';
            $class = isset($node->attrs->class) ? ' class="'.e($node->attrs->class).'"' : ' class="img-fluid"';
            $loading = e($node->attrs->loading ?? 'lazy');

            $hasWebpConversion = $media->hasGeneratedConversion('webp');
            $conversion = $hasWebpConversion ? 'webp' : '';

            $src = $hasWebpConversion ? $media->getUrl('webp') : $media->getUrl();

            if ($media->hasResponsiveImages($conversion)) {
                $srcset = $media->getSrcset($conversion);
                $responsiveFile = $media->responsiveImages($conversion)->files->first();
            } elseif ($media->hasResponsiveImages()) {
                $srcset = $media->getSrcset();
                $responsiveFile = $media->responsiveImages()->files->first();
            } else {
                $srcset = null;
                $responsiveFile = null;
            }

            // Dimensions
            $width = $responsiveFile?->width() ?? $node->attrs->width ?? $media->getCustomProperty('width');
            $height = $responsiveFile?->height() ?? $node->attrs->height ?? $media->getCustomProperty('height');

            $dimensions = ($width && $height) ? ' width="'.$width.'" height="'.$height.'"' : '';

            // Clean responsive markup without sizes="1px"
            if ($srcset) {
                // Set sizes appropriate for your content layout (e.g. 100vw or container max-width)
                $sizes = 'sizes="(max-width: 768px) 100vw, 1200px"';
                $html = '<img'.$class.' src="'.$src.'" srcset="'.$srcset.'" '.$sizes.' alt="'.$alt.'"'.$title.$dimensions.' loading="'.$loading.'">';
            } else {
                $html = '<img'.$class.' src="'.$src.'" alt="'.$alt.'"'.$title.$dimensions.' loading="'.$loading.'">';
            }

            // Replace node with custom rendered HTML
            $node->type = 'renderedCustomBlock';
            $node->html = $html;
            unset($node->attrs);
        });

        return $renderer->toHtml();
    }
}
