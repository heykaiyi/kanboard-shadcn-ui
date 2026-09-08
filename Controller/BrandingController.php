<?php

namespace Kanboard\Plugin\Shadcn\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Plugin\Shadcn\Model\BrandingModel;

/**
 * Serves the two uploaded images.
 *
 * They live in the data directory, which is not reachable over HTTP on any
 * correct install, so a route is the only way to draw them. This one is
 * public — the login screen has a mark and a tab icon too, and nobody is
 * signed in there yet.
 *
 * Everything served is immutable: the URL carries a hash that changes on
 * every upload, so a year of caching is safe and a replaced logo appears
 * at once, in the browser and in Gmail's image proxy alike.
 */
class BrandingController extends BaseController
{
    const CACHE_DURATION = 365 * 86400;

    public function image()
    {
        $type = $this->request->getStringParam('image');
        $format = $this->request->getStringParam('format');

        if (! in_array($type, array(BrandingModel::LOGO, BrandingModel::FAVICON), true)) {
            $this->response->status(404);
            return;
        }

        $image = $this->shadcnBrandingModel->getImage($type);

        if ($image === array()) {
            $this->response->status(404);
            return;
        }

        // The hash is the cache key, so a request carrying the wrong one is
        // asking for a version that no longer exists.
        if ($this->request->getStringParam('hash') !== $image['hash']) {
            $this->response->status(404);
            return;
        }

        // The tab icon is asked for as an SVG whatever was uploaded, so that
        // it out-ranks the SVG icon Kanboard declares above our hook. An
        // upload that is already an SVG is simply served as it is.
        $wrapped = $format === 'svg' && $image['mime'] !== 'image/svg+xml';

        if ($this->request->getHeader('If-None-Match') === '"'.$image['hash'].'"') {
            $this->response->status(304);
            return;
        }

        try {
            $blob = $wrapped ? $this->shadcnBrandingModel->getSvgImage($type) : $this->shadcnBrandingModel->getBlob($type);
        } catch (\Exception $e) {
            $this->logger->error('Shadcn: unable to read '.$image['path'].': '.$e->getMessage());
            $this->response->status(404);
            return;
        }

        $this->response
            ->withCache(self::CACHE_DURATION, $image['hash'])
            ->withContentType($wrapped ? 'image/svg+xml' : $image['mime'])
            // An uploaded SVG is a document that could otherwise run script on
            // this origin. It is admins who upload, and the file is screened
            // on the way in — this is the lock that does not depend on either.
            ->withHeader('Content-Security-Policy', "default-src 'none'; img-src data:; style-src 'unsafe-inline'")
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('Content-Disposition', 'inline')
            ->withBody($blob)
            ->send();
    }
}
