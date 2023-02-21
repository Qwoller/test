<?
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;

Loader::includeModule("iblock");

$filter = [
    "IBLOCK_ID" => 12,
    ">=ACTIVE_FROM" => new DateTime('01.01.2015 00:00:00', 'd.m.Y H:i:s'),
    "<=ACTIVE_FROM" => new DateTime('31.12.2015 23:59:59', 'd.m.Y H:i:s')
];

$allnews = \Bitrix\Iblock\Elements\ElementNewsTable::getList([
    'select' => [
        'ID',
        'NAME',
        'PREVIEW_TEXT',
        'PREVIEW_PICTURE',
		'ACTIVE_FROM',
		'CODE',
		'TAGS',
		'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL',
		'AUTHOR_ID' => 'AUTHOR.IBLOCK_GENERIC_VALUE',
		'AUTHOR_NAME' =>'AUTHOR_ELEMENT.NAME',
		'IBLOCK_SECTION_ID',
		'SECTION_NAME' => 'SECTION.NAME'
    ],
    "filter" => $filter,
    "order" => ["ACTIVE_FROM" => "DESC"],
    "runtime" => [
        new \Bitrix\Main\Entity\ReferenceField(
            'AUTHOR_ELEMENT',
            'Bitrix\Iblock\ElementTable',
            [
				'=this.AUTHOR_ID' => 'ref.ID',
			]
        ),
        new \Bitrix\Main\Entity\ReferenceField(
            'SECTION',
            'Bitrix\Iblock\SectionTable',
            [
				'=this.IBLOCK_SECTION_ID' => 'ref.ID',
			]
        ),
    ]
])->fetchAll();
foreach ($allnews as $news) {
    $items[] = array(
        "id" => $news["ID"],
        "url" => \CIBlock::ReplaceDetailUrl($news["DETAIL_PAGE_URL"], $news, true, "E"),
		"image" => CFile::GetPath($news['PREVIEW_PICTURE']) ?? false,
        "name" => $news["NAME"],
        "sectionName" => $news['SECTION_NAME'],
        "date" => \FormatDate("j F Y H:i", \MakeTimeStamp($news["ACTIVE_FROM"])),
        "author" => $news['AUTHOR_NAME'],
        "tags" => $news['TAGS'],
    );
}

$context = Context::getCurrent();
$context->getResponse()->addHeader("Content-type", "application/json; charset=utf-8");
echo Json::encode($items);
