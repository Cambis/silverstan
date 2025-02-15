<?php

namespace {

    use App\Model\Block;
    use App\Model\Block\ContentBlock;
    use App\Page\Extension\AlternativeTitleExtension;
    use SilverStripe\CMS\Model\SiteTree;
    use SilverStripe\ORM\ArrayList;

    class Page extends SiteTree
    {
        private static array $has_many = [
            'Blocks' => Block::class,
        ];

        private static array $extensions = [
            AlternativeTitleExtension::class,
        ];

        public function getTitle(): string
        {
            if ($this->AlternativeTitle !== null && $this->AlternativeTitle !== '') {
                return $this->AlternativeTitle;
            }

            return parent::getTitle();
        }

        public function getContentBlocks(): ArrayList
        {
            return $this->Blocks()->filterByCallback(static function (Block $block): bool {
                return $block instanceof ContentBlock;
            });
        }
    }
}
