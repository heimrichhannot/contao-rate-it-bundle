<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\EventListener\Contao;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Contao\ModuleModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use HeimrichHannot\RateItBundle\Service\RateItAssetsManager;
use HeimrichHannot\RateItBundle\Twig\Runtime\RateItRuntime;
use simple_html_dom;

/**
 * Injects rating widgets into rendered FAQ output (former RateItFaq).
 *
 * NOTE: this integration post-processes the rendered FAQ HTML and therefore
 * depends on the markup produced by the Contao FAQ module. It should be
 * re-verified against the FAQ templates used in the target project.
 */
class GetContentElementListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly RateItRuntime $rateItRuntime,
        private readonly RateItAssetsManager $assetsManager,
    ) {
    }

    #[AsHook('getContentElement')]
    public function __invoke(ContentModel $element, string $buffer): string
    {
        if ('module' !== $element->type) {
            return $buffer;
        }

        $module = $this->connection->fetchAssociative(
            "SELECT * FROM tl_module WHERE id = ? AND type IN ('faqpage', 'faqreader')",
            [$element->module]
        );

        if (!$module) {
            return $buffer;
        }

        $categories = StringUtil::deserialize($module['faq_categories'], true);

        if (empty($categories)) {
            return $buffer;
        }

        $buffer = 'faqreader' === $module['type']
            ? $this->renderForFaqReader($categories, $buffer)
            : $this->renderForFaqPage($categories, $buffer);

        $this->assetsManager->register();

        return $buffer;
    }

    private function renderForFaqPage(array $categories, string $buffer): string
    {
        if (!class_exists(simple_html_dom::class)) {
            return $buffer;
        }

        $placeholders = implode(',', array_fill(0, \count($categories), '?'));
        $faqs = $this->connection->fetchAllAssociative(
            "SELECT * FROM tl_faq WHERE pid IN ($placeholders) AND published = '1'",
            $categories
        );

        if (empty($faqs)) {
            return $buffer;
        }

        $dom = new simple_html_dom();
        $dom->load($buffer);

        foreach ($faqs as $faq) {
            $rating = $this->renderSingle($faq);

            if ('' === $rating) {
                continue;
            }

            $matches = $dom->find('#'.$faq['alias']);

            if (\is_array($matches) && 1 === \count($matches)) {
                $section = $matches[0]->parent();

                if ('before' === $faq['rateit_position']) {
                    $section->innertext = $rating.$section->innertext;
                } elseif ('after' === $faq['rateit_position']) {
                    $section->innertext = $section->innertext.$rating;
                }
            }
        }

        $buffer = $dom->save();
        $dom->clear();

        return $buffer;
    }

    private function renderForFaqReader(array $categories, string $buffer): string
    {
        $this->framework->initialize();
        $input = $this->framework->getAdapter(Input::class);
        $item = $input->get('auto_item') ?: $input->get('items');

        if (!$item) {
            return $buffer;
        }

        $placeholders = implode(',', array_fill(0, \count($categories), '?'));
        $params = array_merge($categories, [is_numeric($item) ? (int) $item : 0, $item]);

        $faq = $this->connection->fetchAssociative(
            "SELECT * FROM tl_faq WHERE pid IN ($placeholders) AND (id = ? OR alias = ?) AND published = '1'",
            $params
        );

        if (!$faq) {
            return $buffer;
        }

        $rating = $this->renderSingle($faq);

        if ('before' === $faq['rateit_position']) {
            return $rating.$buffer;
        }

        if ('after' === $faq['rateit_position']) {
            return $buffer.$rating;
        }

        return $buffer;
    }

    private function renderSingle(array $faq): string
    {
        if (empty($faq['addRating'])) {
            return '';
        }

        $active = $this->connection->fetchOne(
            "SELECT active FROM tl_rateit_items WHERE rkey = ? AND typ = 'faq'",
            [(string) $faq['id']]
        );

        if ('1' !== (string) $active) {
            return '';
        }

        return $this->rateItRuntime->renderRating($faq['id'], 'faq');
    }
}
