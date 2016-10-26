<?php

declare(strict_types=1);

/*
 * This file is part of Symplify
 * Copyright (c) 2016 Tomas Votruba (http://tomasvotruba.cz).
 */

namespace Symplify\PHP7_Sculpin\Renderable\Routing\Route;

use Symplify\PHP7_Sculpin\Contract\Renderable\Routing\Route\RouteInterface;
use Symplify\PHP7_Sculpin\Renderable\File\AbstractFile;

final class NotHtmlRoute implements RouteInterface
{
    public function matches(AbstractFile $file) : bool
    {
        return in_array(
            $file->getPrimaryExtension(),
            ['xml', 'rss', 'json', 'atom', 'css']
        );
    }

    public function buildOutputPath(AbstractFile $file) : string
    {
        if (in_array($file->getExtension(), ['latte', 'md'])) {
            return $file->getBaseName();
        }

        return $file->getBaseName() . '.' . $file->getPrimaryExtension();
    }

    public function buildRelativeUrl(AbstractFile $file) : string
    {
        return $this->buildOutputPath($file);
    }
}