<?php declare(strict_types=1);

namespace Symplify\Statie\FlatWhite\Latte;

use Latte\Engine;
use Nette\Utils\Strings;

final class LatteRenderer
{
    /**
     * @var string
     */
    private const MATCH_CODE_BLOCKS_HTML = '#<code(?: class=\"[a-z-]+\")?>*(?:(?!<\/code>).)+<\/code>#ms';

    /**
     * @var string
     */
    private const MATCH_PLACEHOLDERS = '#' . self::PLACEHOLDER_PREFIX . '[0-9]+#m';

    /**
     * @var string
     */
    private const PLACEHOLDER_PREFIX = '___replace_block_';

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var DynamicStringLoader
     */
    private $dynamicStringLoader;

    public function __construct(LatteFactory $latteFactory, DynamicStringLoader $dynamicStringLoader)
    {
        $this->engine = $latteFactory->create();
        $this->dynamicStringLoader = $dynamicStringLoader;
    }

    /**
     * @param mixed[] $parameters
     */
    public function renderExcludingHighlightBlocks(string $content, array $parameters): string
    {
        $i = 0;
        $highlightedCodeBlocks = [];

        // due to StringLoader
        // make sure we have content and not file name
        $originalReference = $content;
        $content = $this->dynamicStringLoader->getContent($content);

        $contentWithPlaceholders = Strings::replace(
            $content,
            self::MATCH_CODE_BLOCKS_HTML,
            function (array $match) use (&$i, &$highlightedCodeBlocks) {
                $highlightedCodeBlock = $match[0];
                $placeholder = self::PLACEHOLDER_PREFIX . ++$i;
                $highlightedCodeBlocks[$placeholder] = $highlightedCodeBlock;

                return $placeholder;
            }
        );

        // due to StringLoader
        $this->dynamicStringLoader->changeContent($originalReference, $contentWithPlaceholders);
        $renderedContentWithPlaceholders = $this->engine->renderToString($originalReference, $parameters);

        return Strings::replace(
            $renderedContentWithPlaceholders,
            self::MATCH_PLACEHOLDERS,
            function (array $match) use ($highlightedCodeBlocks) {
                $placeholder = $match[0];

                return $highlightedCodeBlocks[$placeholder];
            }
        );
    }
}
