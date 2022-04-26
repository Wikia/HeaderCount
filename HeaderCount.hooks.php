<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

class HeaderCountHooks {
    public static function setupParser(Parser &$parser) {
        $parser->setFunctionHook('headcount', 'HeaderCountHooks::renderHeadCount');
    }

    public static function renderHeadCount(Parser &$parser, $page = '', $level = '') {
        if (empty($page)) {
            $title = $parser->getTitle();
        } else {
            $title = Title::newFromText($page);
            if (!$title->exists()) {
                return "'''$title does not exist.'''";
            }
        }

        $rev = MediaWikiServices::getInstance()
            ->getRevisionLookup()->getRevisionByTitle($title);
        if ($rev === null) {
            return "'''Could not retrieve revision from $title.'''";
        }

        $content = $rev->getContent(SlotRecord::MAIN);
        if ($content === null) {
            return "'''Could not extract text from $title.'''";
        }

        $level = empty($level) || !(($level >= 1) && ($level <= 6))
			? 2 : intval($level);
        $header = str_repeat('=', $level);
        $serialized = $content->serialize();
        $count = preg_match_all("/^$header" . "[^=]+" . "$header$/m", $serialized);

        return $count == false ? '0<!-- WARNING: LST loop detected -->' : $count;
    }
}
